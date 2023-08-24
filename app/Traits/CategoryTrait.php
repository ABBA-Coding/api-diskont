<?php

namespace App\Traits;

use App\Models\Category;
use App\Models\Products\Product;

trait CategoryTrait {

    public function getAllCategoriesWithChildren($without_lang = 0)
    {
        $result = collect();

        $category_with_children = Category::select('id', 'name', 'parent_id', 'is_popular', 'desc', 'icon', 'icon_svg', 'img', 'position', 'slug', 'is_active')
            ->where('is_active', 1)
            ->orderBy('position')
            ->get();

        if($without_lang == 1) $this->without_lang($category_with_children);

        $only_parents = $category_with_children->filter(function ($item) {
            return is_null($item->parent_id);
        });
        $only_children = $category_with_children->diff($only_parents);

        // 1-urovendagi childlar
        foreach ($only_parents as $parent_key => $parent_value) {

            $children = collect();
            foreach ($only_children as $child_key => $child_value) {
                if($child_value->parent_id == $parent_value->id) {
                    $children[] = $child_value;
                    $only_children->pull($child_key);
                }
            }
            $parent_value->children = $children;
        }

        // 2-urovendagi childlar
        foreach ($only_parents as $parent_key => $parent_value) {
            foreach ($parent_value->children as $child_key => $child_value) {
                
                $children = collect();
                foreach ($only_children as $only_child_key => $only_child_value) {
                    if($only_child_value->parent_id == $child_value->id) {
                        $children[] = $only_child_value;
                        $only_children->pull($only_child_key);
                    }
                }
                $child_value->children = $children;
            }
        }

        $this->set_products_count($only_parents);

        return [
            'categories' => array_values($only_parents->toArray())
        ];
    }

    public function paginateAllCategoriesWithChildren($without_lang = 0, $page, $is_popular = 0, $search = null, $is_active = 1)
    {
        $result = collect();

        $category_with_children = Category::select('id', 'name', 'parent_id', 'is_popular', 'desc', 'icon', 'icon_svg', 'img', 'position', 'slug', 'is_active');

        if($is_active) $category_with_children = $category_with_children->where('is_active', $is_active);

        $category_with_children = $category_with_children->orderBy('position')
            ->get();

        if($without_lang == 1) $this->without_lang($category_with_children);

        $only_parents = $category_with_children->filter(function ($item) {
            return is_null($item->parent_id);
        });
        $only_children = $category_with_children->diff($only_parents);

        // 1-urovendagi childlar
        foreach ($only_parents as $parent_key => $parent_value) {

            $children = collect();
            foreach ($only_children as $child_key => $child_value) {
                if($child_value->parent_id == $parent_value->id) {
                    $children[] = $child_value;
                    $only_children->pull($child_key);
                }
            }
            $parent_value->children = $children;
        }

        // 2-urovendagi childlar
        foreach ($only_parents as $parent_key => $parent_value) {
            foreach ($parent_value->children as $child_key => $child_value) {
                
                $children = collect();
                foreach ($only_children as $only_child_key => $only_child_value) {
                    if($only_child_value->parent_id == $child_value->id) {
                        $children[] = $only_child_value;
                        $only_children->pull($only_child_key);
                    }
                }
                $child_value->children = $children;
            }
        }

        $only_parents = $only_parents
            ->where('is_popular', $is_popular)
            ->skip(($page-1)*$this->PAGINATE)
            ->take($this->PAGINATE);

        $this->set_products_count($only_parents);

        return [
            'current_page' => $page,
            'data' => array_values($only_parents->toArray())
        ];
    }

    public function get_children(Category $category, $without_lang = 0, $all = 0): \Illuminate\Support\Collection
    {
        $result = collect();
        $category_with_children = Category::select('id', 'name', 'parent_id', 'is_popular', 'desc', 'icon', 'icon_svg', 'img', 'position', 'slug', 'is_active')->get();

        if($without_lang == 1) $this->without_lang($category_with_children);

        $only_parents = $category_with_children->filter(function ($item) {
            return is_null($item->parent_id);
        });
        $only_children = $category_with_children->diff($only_parents);

        // 1-urovendagi childlar
        foreach ($only_parents as $parent_key => $parent_value) {

            $children = collect();
            foreach ($only_children as $child_key => $child_value) {
                if($child_value->parent_id == $parent_value->id) {
                    $children[] = $child_value;
                    $only_children->pull($child_key);
                }
            }
            $parent_value->children = $children;

            if ($parent_value->id == $category->id) $result = $children;
        }

        // 2-urovendagi childlar
        foreach ($only_parents as $parent_key => $parent_value) {
            foreach ($parent_value->children as $child_key => $child_value) {
                
                $children = collect();
                foreach ($only_children as $only_child_key => $only_child_value) {
                    if($only_child_value->parent_id == $child_value->id) {
                        $children[] = $only_child_value;
                        $only_children->pull($only_child_key);
                    }
                }
                $child_value->children = $children;

                if (!empty($result) && $category->id == $parent_value->id) {
                    $result->where('id', $child_value->id)->children = $children;
                } else if ($category->id == $child_value->id) {
                    $result = $children;
                }

            }
        }

        return !$all ? $result : $only_parents;
    }

    public function get_products_count(Category $category): int
    {
        $children = $category->get_children($category);
        $ids = $children->pluck('id')->toArray();

        foreach($children as $child) {
            if($child->children) {
                foreach ($child->children->pluck('id')->toArray() as $value) {
                    $ids[] = $value;
                }
            }
        }
        $ids[] = $category->id;
        $products_count = Product::where('status', 'active')
            ->whereHas('info', function ($q) use ($ids) {
                $q->where('is_active', 1)
                    ->whereIn('category_id', $ids);
            })
            ->count();

        return $products_count;
    }

    public function set_products_count($categories)
    {
        foreach ($categories as $category_key => $category) {
            $ids = [$category->id];

            if($category->children) {
                foreach ($category->children as $child_key => $child) {
                    $ids[] = $child->id;

                    if($child->children) {
                        $ids = array_merge($ids, $child->children->pluck('id')->toArray());
                    }
                }
            }

            $category->products_count = Product::where('status', 'active')
                ->whereHas('info', function ($q) use ($ids) {
                    $q->where('is_active', 1)
                        ->whereIn('category_id', $ids);
                })
                ->count();
        }
    }


    
    function category_reverse($categories): array
    {
        $result = [];
        foreach ($categories as $key => $category) {
            $this->parent_without_lang($category);

            $to_1_lvl = [];
            $counter = 0;
            while($category) {
                $to_1_lvl[$counter]['id'] = $category->id;
                $to_1_lvl[$counter]['name'] = $category->name;
                $to_1_lvl[$counter]['desc'] = $category->desc;
                $to_1_lvl[$counter]['icon'] = $category->icon;
                $to_1_lvl[$counter]['icon_svg'] = $category->icon_svg;
                $to_1_lvl[$counter]['sm_img'] = $category->sm_img;
                $to_1_lvl[$counter]['md_img'] = $category->md_img;
                $to_1_lvl[$counter]['lg_img'] = $category->lg_img;
                $to_1_lvl[$counter]['slug'] = $category->slug;

                $category = $category->parent;

                $counter ++;
            }
            unset($counter);
            $to_1_lvl = array_reverse($to_1_lvl);

            $counter = 0;
            foreach ($to_1_lvl as $item) {
                if($counter == 0) $result[$key] = $item;
                else if (isset($result[$key]['children'][0]['children'][0])){
                    $result[$key]['children'][0]['children'][0]['children'][0] = $item;
                }
                else if (isset($result[$key]['children'][0])){
                    $result[$key]['children'][0]['children'][0] = $item;
                }
                else if (!isset($result[$key]['children'])) {
                    $result[$key]['children'][0] = $item;
                }

                $counter ++;
            }
            unset($counter);
        }

        return $result;
    }

    public function parent_without_lang($category)
    {
        while($category->parent) {
            $this->without_lang([$category->parent]);
            $this->without_lang($category->parent->attributes);
            foreach ($category->parent->attributes as $attribute) {
                $this->without_lang($attribute->options);
            }
            return self::parent_without_lang($category->parent);
        }
    }
}