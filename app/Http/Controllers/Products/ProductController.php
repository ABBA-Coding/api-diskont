<?php

namespace App\Http\Controllers\Products;

use App\Models\Attributes\AttributeOption;
use App\Models\Category;
use App\Models\Characteristics\Characteristic;
use App\Models\Characteristics\CharacteristicOption;
use App\Models\Products\{
    Product,
    ProductInfo,
    ProductImage,
};
use App\Models\Promotions\Promotion;
use App\Traits\CategoryTrait;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
	use CategoryTrait;
	
    protected $PAGINATE = 16;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $products = Product::latest();

        $data = $request->all();
        if(isset($data['search']) && $data['search'] != '') {
            $products = $products->where('name', 'like', '%'.$data['search'].'%')->orWhere('for_search', 'like', '%'.$data['search'].'%')
                ->with('info.category', 'info.brand', 'info.products', 'info.products.images');
        } else {
            $products = $products->with('info', 'info.category', 'info.products', 'info.products.images');
        }
        if(isset($data['status']) && $data['status'] != '') $products = $products->where('status', trim($data['status']));

        $products = $products->paginate($this->PAGINATE);

        // if (!Cache::store('redis')->get('products/index')) {
        //     Cache::store('redis')->put('products/index', $products, now()->addMinutes(10));
        // }

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
            'products.*.images' => 'required|array',
            'products.*.variations' => 'required|array',
            'products.*.variations.*' => 'required|array',
            'products.*.variations.*.options' => 'required|array',
            'products.*.variations.*.options.*' => 'required|integer',
            'products.*.variations.*.characteristics' => 'required|array',
            'products.*.variations.*.characteristics.*' => 'required',
            'products.*.variations.*.characteristics.*.characteristic_id' => 'required|integer',
            'products.*.variations.*.characteristics.*.name' => 'required',
            'products.*.variations.*.price' => 'required|numeric',
            'products.*.variations.*.dicoin' => 'nullable|integer|min:1|max:99',
            // 'products.*.variations.*.promotions' => 'array|required',
            // 'products.*.variations.*.promotions.*' => 'required|integer',
            'products.*.variations.*.is_default' => 'required|boolean',
            'products.*.variations.*.is_popular' => 'nullable|boolean',
            'products.*.variations.*.product_of_the_day' => 'nullable|boolean',
        ]);
        /*
         * Category check
         */
        $category = Category::find($request->category_id);
        if(!$category) return response([
            'message' => 'Category not found',
        ], 404);
        if(!$category->parent) return response([
            'message' => 'Nelzya dobavit na glavnuyu kategoriyu'
        ], 500);

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
                    $item->promotions()->sync($variation['promotions']);

                    $characteristics = [];
                    foreach ($variation['characteristics'] as $characteristicOption) {
                        $savedCharacteristicOption = CharacteristicOption::create([
                            'name' => $characteristicOption['name'],
                            'characteristic_id' => $characteristicOption['characteristic_id']
                        ]);

                        $characteristics[] = $savedCharacteristicOption->id;
                    }
                    $item->characteristic_options()->sync($characteristics);

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
    public function show(Product $product)
    {
        $info = ProductInfo::where('id', $product->info->id)
            ->select('id', 'name', 'desc', 'brand_id', 'category_id', 'is_active')
            ->with('brand', 'category', 'category.parent')
            ->first();

    	// $info->category->children = $this->get_children($info->category);

        $variations = $info->products;
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
                    ->with('characteristic_options', 'characteristic_options.characteristic', 'attribute_options', 'attribute_options.attribute', 'promotions')
                    ->get();
                $variation_images = array_filter($variation_images, function($item) use ($product_images_ids) {
                    return !in_array($item, $product_images_ids);
                });
                $variation_images = array_values($variation_images);
            }

            $counter ++;
        }

        if(empty($result)) {
            $result[0]['variations'][0] = $info->products()->where('id', $info->products[0]->id)->with('attribute_options', 'characteristic_options', 'promotions')->first();
            // $result[0]['images'][0] = [];
        }

        $product = Product::where('id', $product->id)
            ->with('info.category.parent')
            ->first();

        return response([
            'products' => $result,
            'info' => $info,
            'product' => $product,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|array',
            'name.' . $this->main_lang => 'required',
            'model' => 'nullable|max:255',
            // 'is_active' => 'required|integer',
            'status' => 'required|in:active,inactive',
            'desc' => 'required|array',
            'brand_id' => 'nullable|integer',
            'category_id' => 'required|integer',
            'products' => 'required|array',
            'products.*.images' => 'required|array',
            'products.*.variations' => 'required|array',
            'products.*.variations.*' => 'required|array',
            'products.*.variations.*.options' => 'required|array',
            'products.*.variations.*.options.*' => 'required|integer',
            // 'products.*.variations.*.characteristics' => 'required|array',
            // 'products.*.variations.*.characteristics.*' => 'required',
            // 'products.*.variations.*.characteristics.*.characteristic_id' => 'required|integer',
            // 'products.*.variations.*.characteristics.*.name' => 'required',
            'products.*.variations.*.price' => 'required|numeric',
            'products.*.variations.*.dicoin' => 'nullable|integer|min:1|max:99',
            'products.*.variations.*.promotions' => 'array',
            'products.*.variations.*.promotions.*' => 'integer',
            'products.*.variations.*.is_default' => 'required|boolean',
            'products.*.variations.*.is_popular' => 'nullable|boolean',
            'products.*.variations.*.product_of_the_day' => 'nullable|boolean',
        ]);
        $data = $request->all();

        DB::beginTransaction();
        try {
            /*
             * product name save
             */
            $product->update([
                'name' => $data['name'],
                'status' => 'active',
                'for_search' => $data['name']['ru'] ?? ''
            ]);
            $product = $product->info;
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
                // 'brand_id' => $data['brand_id'],
                // 'category_id' => $data['category_id'],
                'is_active' => 1,
            ]);


            // proverit variacii na sootvetstviya brenda i kategorii
            foreach ($data['products'] as $variations) {
                foreach ($variations['variations'] as $variation) {
                    $temp_product = Product::find($variation['id']);
                    if(!$temp_product) return response([
                        'message' => 'Net takogo produkta'
                    ], 500);

                    if($temp_product->info->category_id != $product->category_id || $temp_product->info->brand_id != $product->brand_id) return response([
                        'message' => 'Kategoriya ili brand produkta ne sootvetstvuyet'
                    ], 500);
                }
            }


            /*
             * products save
             */
            $counter = 0;
            $boshqa_ids = [];
            $not_saved_products_id = []; // massiv nesushestvuyushix variaciy
            foreach($data['products'] as $variations) {
                /*
                 * product images save
                 */
                $deleted_all_imgs = false; // buni ham tekshirish kerak
                if(!empty($variations['images'])) {
                    $qolgan_rasmlar = array_values(array_filter($variations['images'], function($i) {
                        return $i['id'] != 0;
                    }));
                    // return response($qolgan_rasmlar);
                    $yangi_rasmlar = array_values(array_filter($variations['images'], function($i) {
                        return $i['id'] == 0;
                    }));

                    $new_images_ids = [];
                    foreach($yangi_rasmlar as $image) {
                        if(Storage::disk('public')->exists('/uploads/temp/' . explode('/', $image['img'])[count(explode('/', $image['img'])) - 1])) {
                            $img = explode('/', $image['img']);
                            Storage::disk('public')->move('/uploads/temp/' . $img[count($img) - 1], '/uploads/products/' . $img[count($img) - 1]);
                            Storage::disk('public')->move('/uploads/temp/200/' . $img[count($img) - 1], '/uploads/products/200/' . $img[count($img) - 1]);
                            Storage::disk('public')->move('/uploads/temp/600/' . $img[count($img) - 1], '/uploads/products/600/' . $img[count($img) - 1]);

                            $img = ProductImage::create([
                                'img' => $img[count($img) - 1]
                            ]);
                            $new_images_ids[] = $img->id;
                        }
                    }
                } else {
                    $deleted_all_imgs = true;
                    $new_images_ids = [];
                }

                // $yangi_variaciyalar = array_values(array_filter($variations['variations'], function($i) {
                //     return $i['id'] == 0;
                // }));
                // $qolgan_variaciyalar = array_values(array_filter($variations['variations'], function($i) {
                //     return $i['id'] != 0;
                // }));
                $qolgan_variaciyalar = [];
                foreach($data['products'] as $jkl) {
                    foreach($jkl['variations'] as $kl) {
                        if($kl['id'] != 0) $qolgan_variaciyalar[] = $kl;
                    }
                }
                $qolgan_variaciyalar_ids = array_map(function($i) {
                    return $i['id'];
                }, $qolgan_variaciyalar);
                $qolgan_variaciyalar_ids = array_merge($qolgan_variaciyalar_ids, $boshqa_ids);

                /*
                 * o'chirilgan variaciyalarni o'chirish (1c integraciya bo'lmaganida uncomment qilish kerak)
                 */
                // $kerakmas_variaciyalar = $product->products()->whereNotIn('id', $qolgan_variaciyalar_ids)->get();
                // foreach($kerakmas_variaciyalar as $variation) {
                //     // $variation->images()->delete(); // bu yerda rasmning filelarini ham o'chirihs kerak
                //     $variation->delete();
                // }

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

                        // 1c integraciya uchun
                        if($variation_model->info->id != $product->id) {
                            $variation_model->info->delete();
                        }

                        if(!$variation_model) $not_saved_products_id[] = $variation['id'];
                        $variation_model->update([
                            'info_id' => $product->id,
                            // 'price' => intval($variation['price']),
                            'is_popular' => $variation['is_popular'],
                            'product_of_the_day' => $variation['product_of_the_day'],
                            'status' => $variation_model->status,
                            'dicoin' => $variation['dicoin'],
                            'slug' => $this->product_slug_create($product, $additional_for_slug, $variation_model->id)
                        ]); // model, c_id, is_available ne izpolzuyetsya

                        /*
                         * sync images
                         */
                        $qolgan_rasmlar_ids = array_map(function($i) {
                            return $i['id'];
                        }, $qolgan_rasmlar);

                        // rasmlarni sinxronizaciya qilamiz
                        $variation_model->images()->sync($qolgan_rasmlar_ids);
                        /*
                         * kerakmas rasmlarni o'chiramiz
                         */
                        $variation_model->images()->whereNotIn('product_images.id', $qolgan_rasmlar_ids)->delete();
                    } else {
                        $variation_model = Product::create([
                            'info_id' => $product->id,
                            'c_id' => null,
                            'model' => $data['model'],
                            'price' => $variation['price'],
                            'is_popular' => $variation['is_popular'],
                            'product_of_the_day' => $variation['product_of_the_day'],
                            'status' => $variation['status'],
                            'dicoin' => $variation['dicoin'],
                            'slug' => $this->product_slug_create($product, $additional_for_slug, 0)
                        ]); // is_available ne ispolzuyetsya

                        $qolgan_rasmlar_ids = array_map(function($i) {
                            return $i['id'];
                        }, $qolgan_rasmlar);
                        foreach($qolgan_rasmlar_ids as $qolgan_rasmlar_id) {
                            $variation_model->images()->attach($qolgan_rasmlar_id);
                        }
                    }
                    $variation_model->promotions()->sync($variation['promotions']);

                    // attribute options
                    // if(!isset($variation['options'])) $variation['options'] = []; 
                    // $attributes = [];
                    // foreach ($variation['options'] as $attributeOption) {
                    //     $savedAttributeOption = AttributeOption::create([
                    //         'name' => [
                    //             'ru' => $attributeOption['name']
                    //         ],
                    //         'attribute_id' => $attributeOption['attribute_id']
                    //     ]);

                    //     $attributes[] = $savedAttributeOption->id;
                    // }
                    $variation_model->attribute_options()->sync($variation['options']);

                    // characteristic options
                    $characteristics = [];
                    foreach ($variation['characteristics'] as $characteristicOption) {
                        $savedCharacteristicOption = CharacteristicOption::create([
                            'name' => [
                            	'ru' => $characteristicOption['name']
                            ],
                            'characteristic_id' => $characteristicOption['characteristic_id']
                        ]);

                        $characteristics[] = $savedCharacteristicOption->id;
                    }
                    $variation_model->characteristic_options()->sync($characteristics);

                    /*
                     * yangi rasmlarni save qilamiz
                     */
                    if(count($new_images_ids) != 0) {
                         foreach($new_images_ids as $new_images_id) {
                            $variation_model->images()->attach($new_images_id);
                        }
                    }

                    /*
                     * detach bo'ldima? tekshirib keyin o'chirib tashiman
                     */
                    // $old_images_ids = $old_images->pluck('id')->toArray();
                    // $delete_images_ids = array_diff($old_images_ids, $qolgan_rasmlar);
                    // $delete_images_ids = array_values($delete_images_ids);

                    // foreach($delete_images_ids as $image) {
                    //     $variation_model->images()->detach($image);
                    // }

                    // $variation_model->images()->sync($images_ids);

                    if($variation['is_default']) $default_product_id = $variation_model->id;

                    /*
                     * obrabotka qilingan variaciyalar
                     */
                     $boshqa_ids[] = $variation_model->id;
                     $qolgan_rasmlar_ids = [];
                }

                unset($temp_counter);

                $counter ++;
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

    public function get_undone_variations(Request $request)
    {
        $request->validate([
            'search' => 'required|max:255',
            'brand' => 'required|integer',
            'category' => 'required|integer',
        ]);

        $products = Product::where(function($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('for_search', 'like', '%'.$request->search.'%');
            })
            // ->whereDoesntHave('images')
            ->whereHas('info', function ($q) use ($request) {
                $q->where([
                    ['category_id', $request->category],
                    ['brand_id', $request->brand],
                ]);
            })
            ->with('info', 'images')
            ->orderBy('id', 'DESC')
            ->limit(16)
            ->get();

        return response([
            'products' => $products
        ]);
    }

    public function variation_delete(Request $request, $id)
    {
        $product = Product::find($id);

        if(!$product) return response([
            'message' => 'Product not found'
        ], 404);

        DB::beginTransaction();
        try {

            if(!$product->info) return response([
                'message' => 'Ne sushestvuet info obyekt dlya etogo produkta'
            ], 404);

            $info = [
                'name' => $product->name,
                'for_search' => $product->for_search,
                'desc' => [
                    'ru' => null
                ],
                'brand_id' => $product->info->brand_id,
                'category_id' => $product->info->category_id,
                'is_active' => 1,
                'default_product_id' => $id,
            ];
            $product_new_info = ProductInfo::create($info);

            $product->update([
                'status' => 'inactive',
                'info_id' => $product_new_info->id
            ]);

            $product->images()->detach();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return response([
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
