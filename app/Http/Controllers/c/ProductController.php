<?php

namespace App\Http\Controllers\c;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\LogC;
use App\Models\Products\Product;
use App\Models\Products\ProductInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'data' => 'required|array',
            'data.*' => 'required|array',
            'data.*.id' => 'required',
            'data.*.name' => 'required',
            'data.*.desc' => 'required',
            'data.*.brand' => 'required|array',
            'data.*.brand.id' => 'required',
            'data.*.brand.name' => 'required',
            'data.*.category.id' => 'required',
            'data.*.category.name' => 'required',
            'data.*.category.parent' => 'nullable|array',
            'data.*.price' => 'required',
            'data.*.stock' => 'required|integer'
        ]);

        DB::beginTransaction();
        try {
            $data = $request->all();
            foreach ($data['data'] as $item) {
                // dd($item);
                $brand = [
//                    'c_id' => $item['brand']['id'],
                    'name' => $item['brand']['name'],
                    'logo' => null,
                    'slug' => Str::slug($item['brand']['name']),
                    'is_top' => 0
                ];
                $saved_brand = Brand::updateOrCreate(
                    ['c_id' => $item['brand']['id']],
                    $brand
                );


                $category_parent = $item['category']['parent'];
                $category = [
                    // 'c_id' => $item['category']['id'],
                    'name' => [
                        'ru' => $item['category']['name']
                    ],
                    'parent_c_id' => $category_parent ? $category_parent['id'] : null,
                    'is_popular' => 0,
                    'position' => 0,
                    'slug' => Str::slug($item['category']['name'])
                ];
                $saved_category = Category::updateOrCreate(
                    ['c_id' => $item['category']['id']],
                    $category
                );
                if($category_parent) {
                    while($category_parent) {
                        $category = [
                            // 'c_id' => $item['category']['id'],
                            'name' => [
                                'ru' => $category_parent['name']
                            ],
                            'parent_c_id' => $category_parent['parent'] ? $category_parent['parent']['id'] : null,
                            'is_popular' => 0,
                            'position' => 0,
                            'slug' => Str::slug($category_parent['name'])
                        ];
                        $parent_categroy = Category::updateOrCreate(
                            ['c_id' => $category_parent['id']],
                            $category
                        );
                        // set prev categroy parent_id
                        $saved_category->update(['parent_id' => $parent_categroy->id]);

                        $category_parent = $category_parent['parent'];
                    }
                }


                $product = [
                   'c_id' => $item['id'],
                    'price' => $item['price'],
                    'is_popular' => 0,
                    'product_of_the_day' => 0,
                    'status' => 'inactive',
                    'is_available' => 1,
                    'slug' => Str::slug($item['id'], '-'),

//                    'name' => $item['name'],
//                    'desc' => $item['desc'],
                ];
                if(!Product::where('c_id', $item['id'])->exists()) {
                    $saved_product = Product::create($product);

                    $product_info = [
                        'name' => [
                            'ru' => $item['name'],
                        ],
                        'desc' => [
                            'ru' => $item['desc'],
                        ],
                        'for_search' => $item['name'].' '.$item['desc'],
                        'brand_id' => $saved_brand->id,
                        'category_id' => $saved_category->id,
                        'default_product_id' => $saved_product->id,
                    ];
                    $saved_product_info = ProductInfo::create($product_info);
                    // set product info_id
                    $saved_product->update(['info_id' => $saved_product_info->id]);
                } else {
                    $saved_product = Product::where('c_id', $item['id'])->first()->update($product);

                    $product_info = [
                        'name' => [
                            'ru' => $item['name'],
                        ],
                        'desc' => [
                            'ru' => $item['desc'],
                        ],
                        'for_search' => $item['name'].' '.$item['desc'],
                        'brand_id' => $saved_brand->id,
                        'category_id' => $saved_category->id,
                        'default_product_id' => $saved_product->id,
                    ];
                    $saved_product->info->update($product_info);
                }
            }

            // save log
            $log = [
                'req' => $request->url(),
                'method' => $request->method(),
                'res' => '',
                'body' => json_encode($request->all())
            ];
            LogC::create($log);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return response([
                'message' => $e->getMessage()
            ], 500);
        }

        return response([
            'message' => 'Success'
        ]);
    }

    public function delete(Request $request)
    {
        $data = [
            'req' => $request->url(),
            'method' => $request->method(),
            'res' => '',
            'body' => json_encode($request->all())
        ];

        // obnovlenie i save qilish kerak
        // type code

        LogC::create($data);

        return response([
            'message' => 'Success'
        ]);
    }
}
