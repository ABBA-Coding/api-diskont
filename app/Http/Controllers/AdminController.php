<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Roles\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
	protected $PAGINATE = 16;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = Admin::latest()
        	->paginate($this->PAGINATE)
        	->except(1);

    	return response([
    		'users' => $users
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
            'username' => 'required|max:255',
            'password' => 'required|max:255',
            'role_id' => 'required|integer'
        ]);
        $data = $request->all();
        $data['password'] = Hash::make($data['password']);

        if(!Role::find($data['role_id'])) return response([
            'message' => 'Resource not found'
        ], 400);

        $user = Admin::create($data);

        return response([
            'user' => $user
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Admin  $admin
     * @return \Illuminate\Http\Response
     */
    public function show(Admin $admin)
    {
        return response([
        	'user' => $admin
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Admin  $admin
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Admin $admin)
    {
    	$request->validate([
            'username' => 'required|max:255',
            'password' => 'required|max:255',
            'role_id' => 'required|integer'
        ]);
        $data = $request->all();
        if($admin->id == 1) $data['role_id'] = null;
        $data['password'] = Hash::make($data['password']);

        if(!Role::find($data['role_id'])) return response([
            'message' => 'Resource not found'
        ], 400);

        $admin->update($data);

        return response([
            'user' => $admin
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Admin  $admin
     * @return \Illuminate\Http\Response
     */
    public function destroy(Admin $admin)
    {
        if($admin->id == 1) return response([
        	'message' => 'Forbidden'
        ], 403);

    	$admin->delete();

    	return response([
    		'message' => 'Successfully deleted'
    	]);
    }
}
