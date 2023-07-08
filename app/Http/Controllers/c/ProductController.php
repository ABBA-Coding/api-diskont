<?php

namespace App\Http\Controllers\c;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\LogC;
use App\Models\Products\Product;
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
                $brand = [
//                    'c_id' => $item['brand']['id'],
                    'name' => $item['brand']['name'],
                    'logo' => null,
                    'slug' => Str::slug($item['brand']['name']),
                    'is_top' => 0
                ];
                Brand::updateOrCreate(
                    ['c_id' => $item['brand']['id']],
                    $brand
                );

                $category = $item['category'];
                $parent = $category['parent'];
                do {
                    if($parent) {
                        $parent_c_id = $parent['id'];
                    } else {
                        $parent_c_id = $parent;
                    }

                    $category_1 = [
//                        'c_id' => $item['category']['id'],
                        'name' => [
                            'ru' => $category['name']
                        ],
                        'parent_c_id' => $parent_c_id,
                        'is_popular' => 0,
                        'position' => 0,
                        'slug' => Str::slug($category['name'])
                    ];
                    Category::updateOrCreate(
                        ['c_id' => $parent_c_id],
                        $category_1
                    );

                    if($parent) {
                        $category = $parent;
                        $parent = $category['parent'];
                    }
                } while ($parent);

                $product = [
                    'info_id' => null,
//                    'c_id' => $item['id'],
                    'price' => $item['price'],
                    'is_popular' => 0,
                    'product_of_the_day' => 0,
                    'status' => 'inactive',
                    'is_available' => 1,
                    'slug' => Str::slug($item['id'], '-'),

                    'name' => $item['name'],
                    'desc' => $item['desc'],
                ];
                Product::updateOrCreate(
                    ['c_id' => $item['id']],
                    $product
                );
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
