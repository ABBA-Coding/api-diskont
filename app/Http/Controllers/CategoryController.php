<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Traits\CategoryTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    use CategoryTrait;

    protected $PAGINATE = 16;
    protected $PAGE = 1;

    protected function setPage($page)
    {
        $this->PAGE = $page;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // set page
        if(isset($request->page) && $request->page != '') $this->setPage($request->page);

        // search
        $search = null;
        if(isset($request->search) && $request->search != '') $search = $request->search;


        // $categories = $categories->with('attributes', 'attributes.options', 'characteristic_groups', 'characteristic_groups.characteristics', 'characteristic_groups.characteristics.options')
        //     ->paginate($this->PAGINATE);

        return response([
            'categories' => $this->paginateAllCategoriesWithChildren(0, $this->PAGE, 0, $search, 0)
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
            'name.'.$this->main_lang => 'required|max:255',
            'parent_id' => 'nullable|integer',
            // 'attributes' => 'nullable|array',
            'group_characteristics' => 'required|array',
            'icon' => 'nullable|max:255',
            'icon_svg' => 'nullable',
            'img' => 'nullable|max:255',
            'is_popular' => 'required|boolean',
            'desc' => 'required|array',
            'is_active' => 'required|boolean',
        ]);

        if($request->icon && Storage::disk('public')->exists('/uploads/temp/' . explode('/', $request->icon)[count(explode('/', $request->icon)) - 1])) {
            $explode_icon = explode('/', $request->icon);
            Storage::disk('public')->move('/uploads/temp/' . $explode_icon[count($explode_icon) - 1], '/uploads/categories/icons/' . $explode_icon[count($explode_icon) - 1]);
            Storage::disk('public')->move('/uploads/temp/200/' . $explode_icon[count($explode_icon) - 1], '/uploads/categories/icons/200/' . $explode_icon[count($explode_icon) - 1]);
            Storage::disk('public')->move('/uploads/temp/600/' . $explode_icon[count($explode_icon) - 1], '/uploads/categories/icons/600/' . $explode_icon[count($explode_icon) - 1]);
            $icon = $explode_icon[count($explode_icon) - 1];
        }
        if($request->img && Storage::disk('public')->exists('/uploads/temp/' . explode('/', $request->img)[count(explode('/', $request->img)) - 1])) {
            $explode_img = explode('/', $request->img);
            Storage::disk('public')->move('/uploads/temp/' . $explode_img[count($explode_img) - 1], '/uploads/categories/images/' . $explode_img[count($explode_img) - 1]);
            Storage::disk('public')->move('/uploads/temp/200/' . $explode_img[count($explode_img) - 1], '/uploads/categories/images/200/' . $explode_img[count($explode_img) - 1]);
            Storage::disk('public')->move('/uploads/temp/600/' . $explode_img[count($explode_img) - 1], '/uploads/categories/images/600/' . $explode_img[count($explode_img) - 1]);
            $img = $explode_img[count($explode_img) - 1];
        }

        DB::beginTransaction();
        try {
            $category = Category::create([
                'name' => $request->name,
                'parent_id' => $request->parent_id,
                'is_popular' => $request->is_popular,
                'position' => $request->position ?? 1000,
                'desc' => $request->desc,
                'icon' => $request->icon ? $icon : null,
                'icon_svg' => $request->icon_svg,
                'img' => $request->img ? $img : null,
                'for_search' => $this->for_search($request, ['name', 'desc']),
                'slug' => $this->to_slug($request, Category::class, 'name'),
            ]);

            if($request->input('attributes') != null) $category->attributes()->sync($request->input('attributes')); // buni qarab qo'yish kerak
            $category->characteristic_groups()->sync($request->group_characteristics);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return response([
                'message' => $e->getMessage()
            ], 500);
        }

        return response([
            'category' => $category
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function show(Category $category)
    {
        $category = Category::where('id', $category->id)
            ->select('id', 'name', 'is_popular', 'desc', 'parent_id', 'img', 'icon', 'slug', 'is_active', 'meta_keywords', 'meta_desc')
            ->with('parent', 'attributes', 'attributes.options', 'characteristic_groups', 'characteristic_groups.characteristics', 'characteristic_groups.characteristics.options') // children
            ->first();

        $category->children = $this->get_children($category);

        return response([
            'category' => $category
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|array',
            'name.ru' => 'required|max:255',
            'parent_id' => 'nullable|integer',
            // 'attributes' => 'required|array',
            'group_characteristics' => 'array',
            'icon' => 'nullable|max:255',
            'icon_svg' => 'nullable',
            'img' => 'nullable|max:255',
            'is_popular' => 'required|boolean',
            'desc' => 'required|array',
            'meta_keywords' => 'nullable|array',
            'meta_desc' => 'nullable|array',
//             'slug' => 'required|max:255',
             'slug' => ['required', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'unique:categories,slug,'.$category->id],
            'is_active' => 'required',
        ]);

        if($request->input('icon')) {
            if(Storage::disk('public')->exists('/uploads/temp/' . explode('/', $request->input('icon'))[count(explode('/', $request->input('icon'))) - 1])) {
                $explode_icon = explode('/', $request->input('icon'));
                Storage::disk('public')->move('/uploads/temp/' . $explode_icon[count($explode_icon) - 1], '/uploads/categories/icons/' . $explode_icon[count($explode_icon) - 1]);
                Storage::disk('public')->move('/uploads/temp/200/' . $explode_icon[count($explode_icon) - 1], '/uploads/categories/icons/200/' . $explode_icon[count($explode_icon) - 1]);
                Storage::disk('public')->move('/uploads/temp/600/' . $explode_icon[count($explode_icon) - 1], '/uploads/categories/icons/600/' . $explode_icon[count($explode_icon) - 1]);
                $icon = $explode_icon[count($explode_icon) - 1];
            } else if(Storage::disk('public')->exists('/uploads/categories/icons/' . explode('/', $request->input('icon'))[count(explode('/', $request->input('icon'))) - 1])) {
                $icon = $category->icon;
            }
        }
        if($request->input('img')) {
            if(Storage::disk('public')->exists('/uploads/temp/' . explode('/', $request->input('img'))[count(explode('/', $request->input('img'))) - 1])) {
                $explode_img = explode('/', $request->input('img'));
                Storage::disk('public')->move('/uploads/temp/' . $explode_img[count($explode_img) - 1], '/uploads/categories/images/' . $explode_img[count($explode_img) - 1]);
                Storage::disk('public')->move('/uploads/temp/200/' . $explode_img[count($explode_img) - 1], '/uploads/categories/images/200/' . $explode_img[count($explode_img) - 1]);
                Storage::disk('public')->move('/uploads/temp/600/' . $explode_img[count($explode_img) - 1], '/uploads/categories/images/600/' . $explode_img[count($explode_img) - 1]);
                $img = $explode_img[count($explode_img) - 1];
            } else if(Storage::disk('public')->exists('/uploads/categories/images/' . explode('/', $request->input('img'))[count(explode('/', $request->input('img'))) - 1])) {
                $img = $category->img;
            }
        }

        DB::beginTransaction();
        try {
            $category->update([
                'name' => $request->input('name'),
                'parent_id' => $request->input('parent_id'),
                'is_popular' => $request->input('is_popular'),
                'position' => $request->position ?? 1000,
                'desc' => $request->input('desc'),
                'meta_keywords' => $request->input('meta_keywords'),
                'meta_desc' => $request->input('meta_desc'),
                'is_active' => $request->input('is_active'),
                'icon_svg' => $request->input('icon_svg'),
                'icon' => $icon ?? $request->input('icon'),
                'img' => $img ?? $request->input('img'),
                'for_search' => $this->for_search($request, ['name', 'desc']),
                'slug' => $request->input('slug')
//                'slug' => $this->to_slug($request, Category::class, 'name', $this->main_lang, $category->id),
            ]);

            $category->attributes()->sync($request->input('attributes'));
            $category->characteristic_groups()->sync($request->input('group_characteristics'));

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return response([
                'message' => $e->getMessage()
            ], 500);
        }

        return response([
            'category' => $category
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function destroy(Category $category)
    {
        DB::beginTransaction();
        try {
            $category->attributes()->detach();
            $category->characteristic_groups()->detach();

            // udalit fayli iz faylovoy sistemi
            $this->delete_files([
                public_path('/uploads/categories/icons/200/' . $brand->logo),
                public_path('/uploads/categories/icons/600/' . $brand->logo),
                public_path('/uploads/categories/icons/' . $brand->logo),
                public_path('/uploads/categories/images/200/' . $brand->logo),
                public_path('/uploads/categories/images/600/' . $brand->logo),
                public_path('/uploads/categories/images/' . $brand->logo),
            ]);
            $category->delete();

            DB::commit();
        } catch(\Exception $e) {
            DB::rollBack();

            return reponse([
                'message' => $e->getMessage()
            ], 500);
        }

        return response([
            'message' => __('messages.successfully_deleted')
        ]);
    }

    public function for_search(Request $request, $fields)
    {
        $result = '';

        if(count($fields) == 0) return '';

        foreach($fields as $field) {
            $result .= isset($request->$field['ru']) ? ($request->$field['ru'] . ' ') : '';
        }

        return $result;
    }
}
