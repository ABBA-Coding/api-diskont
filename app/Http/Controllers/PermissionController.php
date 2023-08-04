<?php

namespace App\Http\Controllers;

use App\Models\Roles\Permission;
use App\Models\Roles\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
            'permissions' => 'required|array',
            'permissions.*.name' => 'required|max:255',
            'permissions.*.url' => 'required|max:255',
            'permissions.*.id' => 'required|integer',
        ]);
        $data = $request->all();

        DB::beginTransaction();
        try {
            $ids = [];
            foreach ($data['permissions'] as $permission) {
                $permission = Permission::updateOrCreate(
                    ['id' => $permission['id']],
                    $permission
                );
                $ids[] = $permission->id;
            }

            Permission::whereNotIn('id', $ids)
                ->delete();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return response([
                'message' => $e->getMessage()
            ]);
        }

        return response([
            'message' => 'Successfully saved'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Roles\Permission  $permission
     * @return \Illuminate\Http\Response
     */
    public function show(Permission $permission)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Roles\Permission  $permission
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Permission $permission)
    {
//        $request->validate([
//            'permissions' => 'required|array',
//            'permissions.*.name' => 'required|max:255',
//            'permissions.*.url' => 'required|max:255',
//            'permissions.*.id' => 'required|integer',
//        ]);
//        $data = $request->all();
//
//        DB::beginTransaction();
//        try {
//            foreach ($data['permissions'] as $permission) {
//                Permission::updateOrCreate(
//                    ['id' => $permission['id']],
//                    $permission
//                );
//            }
//
//            DB::commit();
//        } catch (\Exception $e) {
//            DB::rollBack();
//
//            return response([
//                'message' => $e->getMessage()
//            ]);
//        }
//
//        return response([
//            'message' => 'Successfully saved'
//        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Roles\Permission  $permission
     * @return \Illuminate\Http\Response
     */
    public function destroy(Permission $permission)
    {
        //
    }
}
