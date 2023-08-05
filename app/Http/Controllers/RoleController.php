<?php

namespace App\Http\Controllers;

use App\Models\Roles\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $roles = Role::latest()
            ->get();

        return response([
            'roles' => $roles
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
            'name' => 'required|max:255',
            'permissions' => 'required|array',
            'permissions.*' => 'required|integer',
            'permission_groups' => 'array',
            'permission_groups.*' => 'integer'
        ]);
        $data = $request->all();

        DB::beginTransaction();
        try {
            $role = Role::create($data);
            $role->permission_groups()->sync($data['permission_groups']);
            $role->permissions()->sync($data['permissions']);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return response([
                'message' => $e->getMessage()
            ]);
        }

        return response([
            'role' => $role
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Roles\Role  $role
     * @return \Illuminate\Http\Response
     */
    public function show(Role $role)
    {
        $role = Role::where('id', $role->id)
            ->with('permission_groups', 'permissions')
            ->get();

        return response([
            'roles' => $role
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Roles\Role  $role
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|max:255',
            'permissions' => 'array',
            'permissions.*' => 'integer',
            'permission_groups' => 'array',
            'permission_groups.*' => 'integer'
        ]);
        $data = $request->all();

        DB::beginTransaction();
        try {
            $role->update($data);
            $role->permission_groups()->sync($data['permission_groups']);
            $role->permissions()->sync($data['permissions']);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return response([
                'message' => $e->getMessage()
            ]);
        }

        return response([
            'role' => $role
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Roles\Role  $role
     * @return \Illuminate\Http\Response
     */
    public function destroy(Role $role)
    {
        DB::beginTransaction();
        try {
            $role->permission_groups()->detach();
            $role->permissions()->detach();
            $role->delete();

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
