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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
                    /*
                     * sozdanie dopolnilnoy chasti sluga
                     */
                    $additional_for_slug = [];
                    $variation_attribute_options = AttributeOption::whereIn('id', $variation['options'])->get()->sortBy('id');
                    foreach($variation_attribute_options as $option) {
                        $additional_for_slug[] = Str::slug($option->name[$this->main_lang], '-');
                    }
                    $additional_for_slug = implode('-', $additional_for_slug);
                    $item = Product::create([
                        'info_id' => $product_info->id,
                        'model' => $request->model ?? null,
                        'price' => intval($variation['price']),
                        'status' => $variation['status'],
                        'is_popular' => $variation['is_popular'],
                        'product_of_the_day' => $variation['product_of_the_day'],
                        'is_available' => 1,
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
            ->select('id', 'name', 'desc', 'brand_id', 'category_id', 'is_active')
            ->with('brand', 'category', 'category.parent')
            ->first();

        $variations = $product->products;
        $variation_images = [];

        foreach($variations as $variation) {
            foreach($variation->images as $image) {
                $variation_images[] = $image;
            }
        }
        $variation_images = array_map(function($item) {
            return $item->pivot->product_image_id;
        }, $variation_images);
        $variation_images = array_values(array_unique($variation_images));

        $result = [];

        $counter = 0;
        while(!empty($variation_images)) {
            $result[$counter]['variations'] = Product::whereHas('images', function($q) use ($variation_images) {
                $q->whereIn('product_images.id', $variation_images);
            })->get();
            if(isset($result[$counter]['variations'][0])) {
                $product_images_ids = Product::find($result[$counter]['variations'][0]->id)->images->pluck('id')->toArray();
                $result[$counter]['images'] = ProductImage::whereIn('id', $product_images_ids)
                    ->get();
                $result[$counter]['variations'] = Product::whereHas('images', function($q) use ($product_images_ids) {
                    $q->whereIn('product_images.id', $product_images_ids);
                })
                    ->with('characteristic_options', 'characteristic_options.characteristic', 'attribute_options', 'attribute_options.attribute')
                    ->get();
                $variation_images = array_filter($variation_images, function($item) use ($product_images_ids) {
                    return !in_array($item, $product_images_ids);
                });
                $variation_images = array_values($variation_images);
            }

            $counter ++;
        }
        

        return response([
            'products' => $result,
            'info' => $product,
        ]);
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
            'name' => 'required|array',
            'name.' . $this->main_lang => 'required',
            'model' => 'nullable|max:255',
            'is_active' => 'required|integer',
            'desc' => 'required|array',
            'brand_id' => 'nullable|integer',
            'category_id' => 'required|integer',
            'products' => 'required|array',
            'products.*.images' => 'nullable|array',
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
        $data = $request->all();

        DB::beginTransaction();
        try {
            /*
             * product info save
             */
            $default_product_id = null;
            $product->update([
                'name' => $data['name'],
                'desc' => $data['desc'],
                'for_search' => isset($data['name']['ru'])
                                ? (
                                    $data['name']['ru'] . (
                                                            isset($data['desc']['ru'])
                                                            ? ' ' . $data['desc']['ru'] 
                                                            : ''
                                                        )
                                )
                                : null,
                'brand_id' => $data['brand_id'],
                'category_id' => $data['category_id'],
                'is_active' => $data['is_active']
            ]);


            /*
             * products save
             */
            $not_saved_products_id = []; // massiv nesushestvuyushix variaciy
            foreach($data['products'] as $variations) {
                /*
                 * product images save
                 */
                if(!empty($variations['images'])) {
                    $images_ids = [];
                    foreach($variations['images'] as $image) {
                        if($image['id'] == 0 && Storage::disk('public')->exists('/uploads/temp/' . explode('/', $image['img'])[count(explode('/', $image['img'])) - 1])) {
                            $img = explode('/', $image['img']);
                            Storage::disk('public')->move('/uploads/temp/' . $img[count($img) - 1], '/uploads/products/' . $img[count($img) - 1]);
                            Storage::disk('public')->move('/uploads/temp/200/' . $img[count($img) - 1], '/uploads/products/200/' . $img[count($img) - 1]);
                            Storage::disk('public')->move('/uploads/temp/600/' . $img[count($img) - 1], '/uploads/products/600/' . $img[count($img) - 1]);

                            $img = ProductImage::create([
                                'img' => $img[count($img) - 1]
                            ]);
                            $images_ids[] = $img->id;
                        }
                    }
                    $qolgan_rasmlar = array_filter($variations['images'], function ($i) {
                        return $i['id'] != 0;
                    });
                    $qolgan_rasmlar = array_map(function($i) {
                        return $i['id'];
                    }, $qolgan_rasmlar);
                } else {
                    $images_ids = [];
                }

                /*
                 * variations save
                 */
                foreach($variations['variations'] as $variation) {
                    /*
                     * sozdanie dopolnilnoy chasti sluga
                     */
                    $additional_for_slug = [];
                    foreach($variation['options'] as $option) {
                        if(AttributeOption::find($option)) {
                            $additional_for_slug[] = Str::slug(AttributeOption::find($option)->name[$this->main_lang], '-');
                        }
                    }
                    $additional_for_slug = implode('-', $additional_for_slug);

                    if($variation['id'] != 0) {
                        $variation_model = Product::find($variation['id']);
                        if(!$variation_model) $not_saved_products_id[] = $variation['id'];
                        $variation_model->update([
                            'info_id' => $product->id,
                            'price' => intval($variation['price']),
                            'is_popular' => $variation['is_popular'],
                            'product_of_the_day' => $variation['product_of_the_day'],
                            'status' => $variation['status'],
                            'slug' => $this->product_slug_create($product, $additional_for_slug, $variation_model->id)
                        ]); // model, c_id, is_available ne izpolzuyetsya
                    } else {
                        $variation_model = Product::create([
                            'info_id' => $product->id,
                            'c_id' => null,
                            'model' => $data['model'],
                            'price' => intval($variation['price']),
                            'is_popular' => $variation['is_popular'],
                            'product_of_the_day' => $variation['product_of_the_day'],
                            'status' => $variation['status'],
                            'slug' => $this->product_slug_create($product, $additional_for_slug, 0)
                        ]); // is_available ne izpolzuyetsya
                    }
                    $variation_model->attribute_options()->sync($variation['options']);
                    $variation_model->characteristic_options()->sync($variation['characteristics']);

                    /*
                     * sync images
                     */
                    $old_images = $variation_model->images; 
                    $old_images_ids = $old_images->pluck('id')->toArray();
                    $delete_images_ids = array_diff($old_images_ids, $qolgan_rasmlar);
                    $delete_images_ids = array_values($delete_images_ids);

                    foreach($delete_images_ids as $image) {
                        $variation_model->images()->detach($image);
                    }
                    $variation_model->images()->sync($images_ids);

                    if($variation['is_default']) $default_product_id = $variation_model->id;
                }
            }

            if(!$default_product_id) {
                return response([
                    'message' => 'Ne vibran produkt po umolchaniyu'
                ]);
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

        return response([
            'product' => $product,
            'message' => 'Success',
            'not_found_products' => $not_saved_products_id,
        ]);
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
            udalim variacii pri obnovlenii informacii produkta
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
