<?php

namespace App\Http\Controllers\Translates;

use App\Http\Controllers\Controller;
use App\Models\Translate\Translate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TranslateController extends Controller
{
    protected $PAGINATE = 16;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if($request->per_page && $request->per_page != '' && $request->per_page < 51 && $request->per_page > 0) $this->PAGINATE = $request->per_page;

        $translates = Translate::latest()
            ->with('group');
        if(isset($request->search) && $request->search != '') $translates = $translates->where('for_search', 'like', '%'.$request->search.'%')->orWhere('val', 'like', '%'.$request->search.'%')->orWhere('key', 'like', '%'.$request->search.'%');
        $translates = $translates->paginate($this->PAGINATE);

        return response([
            'translates' => $translates
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
            'translate_group_id' => 'required|integer',
            'translates' => 'array',
            'translates.*.key' => 'required|max:255',
            'translates.*.val' => 'required'
        ]);
        $data = $request->all();

        DB::beginTransaction();
        try {
            foreach($data['translates'] as $translate) {
                Translate::create([
                    'translate_group_id' => $data['translate_group_id'],
                    'key' => Str::slug($translate['key'], '-'),
                    'val' => $translate['val'],
                    'for_search' => $translate['val']['ru'] ?? ''
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->res_error($e);
        }

        return response([
            'message' => 'Successfully created'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Translate\Translate  $translate
     * @return \Illuminate\Http\Response
     */
    public function show(Translate $translate)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Translate\Translate  $translate
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Translate $translate)
    {
        $request->validate([
            'translate_group_id' => 'required|integer',
            'key' => 'required|max:255',
            'val' => 'required'
        ]);

        $translate->update($request->all());

        return response([
            'translate' => $translate
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Translate\Translate  $translate
     * @return \Illuminate\Http\Response
     */
    public function destroy(Translate $translate)
    {
        $translate->delete();

        return response([
            'message' => 'Successfully deleted'
        ]);
    }

    public function multiple_update(Request $request)
    {
        $request->validate([
            'translate_group_id' => 'required|integer',
            'translates' => 'array',
            'translates.*.key' => 'required|max:255',
            'translates.*.val' => 'required',
            'translates.*.id' => 'required|integer',
        ]);
        $data = $request->all();

        DB::beginTransaction();
        try {
            foreach($data['translates'] as $translate) {
                Translate::updateOrCreate(
                    ['id' => $translate['id']],
                    [
                        'translate_group_id' => $data['translate_group_id'],
                        'key' => Str::slug($translate['key'], '-'),
                        'val' => $translate['val'],
                    ]
                );
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->res_error($e);
        }

        return response([
            'message' => 'Successfully saved'
        ]);
    }
}
