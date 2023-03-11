<?php

namespace App\Http\Controllers\Products;

use App\Models\Attributes\AttributeOption;
use App\Models\Products\{
    Product,
    ProductInfo,
    ProductImage,
};
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use DB;
use Storage;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected $PAGINATE = 16;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $products = ProductInfo::select('id', 'name', 'desc', 'brand_id', 'category_id', 'default_product_id', 'is_active')
            ->with('category', 'brand', 'products', 'products.images', 'category.characteristic_groups', 'category.characteristic_groups.characteristics')
            ->latest()
            ->paginate($this->PAGINATE);

        return response([
            'products' => $products
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|array',
            'name.' . $this->main_lang => 'required',
            'model' => 'nullable|max:255',
            'is_active' => 'required',
            'desc' => 'required|array',
            'brand_id' => 'nullable|integer',
            'status' => 'required|in:active,inactive',
            'category_id' => 'required|integer',
            'products' => 'required|array',
            'products.images' => 'nullable|array',
            'products.*.variations' => 'required|array',
            'products.*.variations.*' => 'required|array',
            'products.*.variations.*.options' => 'required|array',
            'products.*.variations.*.options.*' => 'required|integer',
            'products.*.variations.*.characteristics' => 'required|array',
            'products.*.variations.*.characteristics.*' => 'required|integer',
            'products.*.variations.*.price' => 'required|numeric',
            'products.*.variations.*.is_default' => 'required|boolean',
            'products.*.variations.*.is_popular' => 'nullable|boolean',
            'products.*.variations.*.product_of_the_day' => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            $product_info = ProductInfo::create([
                'name' => $request->name,
                'for_search' => $request->name[$this->main_lang],
                'desc' => $request->desc,
                'brand_id' => $request->brand_id ?? null,
                'category_id' => $request->category_id,
            ]);
            $default_product_id = null;
            foreach($request->products as $product) {
                if(!empty($product['images'])) {
                    $images_ids = [];
                    foreach($product['images'] as $image) {

                        if(Storage::disk('public')->exists('/uploads/temp/' . explode('/', $image)[count(explode('/', $image)) - 1])) {
                            $explode_icon = explode('/', $image);
                            Storage::disk('public')->move('/uploads/temp/' . $explode_icon[count($explode_icon) - 1], '/uploads/products/' . $explode_icon[count($explode_icon) - 1]);
                            Storage::disk('public')->move('/uploads/temp/200/' . $explode_icon[count($explode_icon) - 1], '/uploads/products/200/' . $explode_icon[count($explode_icon) - 1]);
                            Storage::disk('public')->move('/uploads/temp/600/' . $explode_icon[count($explode_icon) - 1], '/uploads/products/600/' . $explode_icon[count($explode_icon) - 1]);
                            $icon = $explode_icon[count($explode_icon) - 1];

                            $img = ProductImage::create([
                                'img' => $explode_icon[count($explode_icon) - 1]
                            ]);
                            $images_ids[] = $img->id;
                        }
                    }
                }

                foreach($product['variations'] as $variation) {
                    $additional_for_slug = [];
                    foreach($variation['options'] as $option) {
                        if(AttributeOption::find($option)) {
                            $additional_for_slug[] = Str::slug(AttributeOption::find($option)->name[$this->main_lang], '-');
                        }
                    }
                    $additional_for_slug = implode('-', $additional_for_slug);
                    $item = Product::create([
                        'info_id' => $product_info->id,
                        'model' => $request->model ?? null,
                        'price' => $variation['price'],
                        'status' => $request->status,
                        'is_popular' => $variation['is_popular'],
                        'product_of_the_day' => $variation['product_of_the_day'],
                        'slug' => $this->product_slug_create($product_info, $additional_for_slug),
                    ]);

                    if($variation['is_default']) $default_product_id = $item->id;

                    $item->attribute_options()->sync($variation['options']);
                    $item->characteristic_options()->sync($variation['characteristics']);

                    if(!empty($product['images'])) $item->images()->sync($images_ids);
                }
            }

            $product_info->update([
                'default_product_id' => $default_product_id
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return response([
                'message' => $e->getMessage()
            ], 500);
        }

        return response($product_info);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(ProductInfo $product)
    {
        $product = ProductInfo::where('id', $product->id)
            ->with('brand', 'category', 'category.characteristic_groups', 'category.characteristic_groups.characteristics', 'products', 'products.images')
            ->first();

        return response($product);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ProductInfo $product)
    {
        $request->validate([
            'name' => "required|array",
            'name.' . $this->main_lang => "required",
            'model' => "nullable|max:255",
            'is_active' => 'required',
            'desc' => 'required|array',
            'brand_id' => 'nullable|integer',
            'status' => 'required|in:active,inactive',
            'category_id' => 'required|integer',
            'products' => 'required|array',
            'products.images' => 'nullable|array',
            'products.*.variations' => 'required|array',
            'products.*.variations.*' => 'required|array',
            'products.*.variations.*.options' => 'required|array',
            'products.*.variations.*.options.*' => 'required|integer',
            'products.*.variations.*.price' => 'required|numeric',
            'products.*.variations.*.is_default' => 'required|boolean',
            'products.*.variations.*.is_popular' => 'nullable|boolean',
            'products.*.variations.*.product_of_the_day' => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            $product->update([
                'name' => $request->name,
                'for_search' => $request->name[$this->main_lang],
                'desc' => $request->desc,
                'brand_id' => $request->brand_id ?? null,
                'category_id' => $request->category_id,
            ]);

            $default_product_id = $product->default_product_id;

            $this->delete_product_variations($product, $request);

            foreach($request->products as $req_product) {
                $old_images = array_filter($req_product['images'], function($v) {
                    return $v['id'] != 0;
                });
                $old_images_ids = [];
                foreach($old_images as $old_img) {
                    $old_images_ids[] = $old_img['id'];
                }
                $new_images = array_filter($req_product['images'], function($v) {
                    return $v['id'] == 0;
                });
                $new_images_pathes = [];
                foreach($new_images as $new_img) {
                    $new_images_pathes[] = $new_img['img'];
                }

                $images_ids = $old_images_ids;
                if(!empty($new_images_pathes)) {
                    foreach($new_images_pathes as $image) {
                        if(Storage::disk('public')->exists('/uploads/temp/' . explode('/', $image)[count(explode('/', $image)) - 1])) {
                            $explode_icon = explode('/', $image);
                            Storage::disk('public')->move('/uploads/temp/' . $explode_icon[count($explode_icon) - 1], '/uploads/products/' . $explode_icon[count($explode_icon) - 1]);
                            Storage::disk('public')->move('/uploads/temp/200/' . $explode_icon[count($explode_icon) - 1], '/uploads/products/200/' . $explode_icon[count($explode_icon) - 1]);
                            Storage::disk('public')->move('/uploads/temp/600/' . $explode_icon[count($explode_icon) - 1], '/uploads/products/600/' . $explode_icon[count($explode_icon) - 1]);
                            $icon = $explode_icon[count($explode_icon) - 1];

                            $img = ProductImage::create([
                                'img' => $explode_icon[count($explode_icon) - 1]
                            ]);
                            $images_ids[] = $img->id;
                        }
                    }
                } else {
                    $var = Product::find($req_product['variations'][0]['id']);
                    if($var) $var->images()->delete();
                }

                foreach($req_product['variations'] as $variation) {
                    $additional_for_slug = [];
                    foreach($variation['options'] as $option) {
                        if(AttributeOption::find($option)) {
                            $additional_for_slug[] = Str::slug(AttributeOption::find($option)->name[$this->main_lang], '-');
                        }
                    }
                    $additional_for_slug = implode('-', $additional_for_slug);

                    if($variation['id'] == 0) {
                        $item = Product::create([
                            'info_id' => $product->id,
                            'model' => $request->model ?? null,
                            'price' => $variation['price'],
                            'status' => $request->status,
                            'is_popular' => $variation['is_popular'],
                            'product_of_the_day' => $variation['product_of_the_day'],
                            'slug' => $this->product_slug_create($product, $additional_for_slug),
                        ]);
                    } else {
                        $item = Product::find($variation['id']);
                        $item->update([
                            'model' => $request->model ?? null,
                            'price' => $variation['price'],
                            'status' => $request->status,
                            'is_popular' => $variation['is_popular'],
                            'product_of_the_day' => $variation['product_of_the_day'],
                            'slug' => $this->product_slug_create($product, $additional_for_slug, $item->id),
                        ]);
                    }

                    if($variation['is_default']) $default_product_id = $item->id;

                    $item->attribute_options()->sync($variation['options']);
                    $item->characteristic_options()->sync($variation['characteristics']);
                    
                    if(!empty($images_ids)) $item->images()->sync($images_ids);
                }
            }

            $product->update([
                'default_product_id' => $default_product_id
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return response([
                'message' => $e->getMessage()
            ], 500);
        }

        return response($product);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(ProductInfo $product)
    {
        DB::beginTransaction();
        try {
            foreach($product->products as $item) {
                $item->attribute_options()->detach();
                $item->characteristic_options()->detach();
                foreach($item->images as $image) {
                    $this->delete_files([
                        public_path('/uploads/products/200/' . $image->img),
                        public_path('/uploads/products/600/' . $image->img),
                        public_path('/uploads/products/' . $image->img),
                    ]);
                }
                $item->images()->detach();
            }
            $product->products()->delete();
            $product->delete();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return response([
                'message' => $e->getMessage()
            ]);
        }

        return response([
            'message' => __('messages.successfully_deleted')
        ]);
    }

    private function delete_product_variations(ProductInfo $info, Request $request)
    {
        /*
         * udalim variacii pri obnovlenii informacii produkta 
        */
        
        $req_products = $request->products;
        $remaining_products_ids = [];

        foreach($req_products as $req_product) {
            foreach($req_product['variations'] as $variation) {
                if($variation['id'] != 0) $remaining_products_ids[] = $variation['id'];
            }
        }

        $info->products()->whereNotIn('id', $remaining_products_ids)->delete();
    }
}
