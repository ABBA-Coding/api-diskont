<?php

namespace App\Http\Controllers\Translates;

use App\Http\Controllers\Controller;
use App\Models\Translate\TranslateGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TranslateGroupController extends Controller
{
    protected $PAGINATE = 16;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $groups = TranslateGroup::with('translates')
            ->paginate($this->PAGINATE);

        return response([
            'groups' => $groups
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
            'sub_text' => 'required|max:255',
            'title' => 'required',
        ]);

        $group = TranslateGroup::create($request->all());

        return response([
            'group' => $group
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Translate\TranslateGroup  $translateGroup
     * @return \Illuminate\Http\Response
     */
    public function show(TranslateGroup $translateGroup)
    {
        $group = TranslateGroup::where('id', $translateGroup->id)
            ->with('translates')
            ->first();

        return response([
            'group' => $group
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Translate\TranslateGroup  $translateGroup
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TranslateGroup $translateGroup)
    {
        $request->validate([
            'sub_text' => 'required|max:255',
            'title' => 'required',
        ]);

        $translateGroup->update($request->all());

        return response([
            'group' => $translateGroup
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Translate\TranslateGroup  $translateGroup
     * @return \Illuminate\Http\Response
     */
    public function destroy(TranslateGroup $translateGroup)
    {
        DB::beginTransaction();
        try {
            $translateGroup->translates()->delete();
            $translateGroup->delete();

            Db::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return response([
                'message' => $e->getMessage()
            ], 500);
        }

        return response([
            'message' => 'Successfully deleted'
        ]);
    }

    public function all()
    {
        return response([
            'translates' => TranslateGroup::latest()->get()
        ]);
    }
}
