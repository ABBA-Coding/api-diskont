<?php

namespace App\Http\Controllers;

use App\Models\Roles\Permission;
use App\Models\Roles\PermissionGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PermissionGroupController extends Controller
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
            'name' => 'required|max:255',
            'permissions' => 'required|array',
            'permissions.*' => 'required|integer',
        ]);
        $data = $request->all();

        DB::beginTransaction();
        try {
            $group = PermissionGroup::create($data);
            $group->permissions()->sync($data['permissions']);

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
     * @param  \App\Models\Roles\PermissionGroup  $permissionGroup
     * @return \Illuminate\Http\Response
     */
    public function show(PermissionGroup $permissionGroup)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Roles\PermissionGroup  $permissionGroup
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PermissionGroup $permissionGroup)
    {
        $request->validate([
            'name' => 'required|max:255',
            'permissions' => 'required|array',
            'permissions.*' => 'required|integer',
        ]);
        $data = $request->all();

        DB::beginTransaction();
        try {
            $permissionGroup->update($data);
            $permissionGroup->permissions()->sync($data['permissions']);

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
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Roles\PermissionGroup  $permissionGroup
     * @return \Illuminate\Http\Response
     */
    public function destroy(PermissionGroup $permissionGroup)
    {
        DB::beginTransaction();
        try {
            $permissionGroup->delete();
            $permissionGroup->permissions()->detach();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return response([
                'message' => $e->getMessage()
            ]);
        }

        return response([
            'message' => 'Successfully deleted'
        ]);
    }
}
