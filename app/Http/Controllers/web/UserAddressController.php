<?php

namespace App\Http\Controllers\web;

use App\Models\User;
use App\Models\UserAddress;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserAddressController extends Controller
{
    public function store(Request $request)
    {
    	$request->validate([
    		'region_id' => 'required|integer',
    		'district_id' => 'required|integer',
    		'village_id' => 'required|integer',
    		'address' => 'required',
    	]);
    	$data = $request->all();

    	$user = auth('sanctum')->user();
    	$data['user_id'] = $user->id;

    	// Proverit kolichestvo adresov
    	if(count($user->addresses) > 2) return response([
    		'message' => 'Uje imeetsya 3 adresa'
    	], 500);

    	$address = UserAddress::create($data);

    	return response([
            'address' => $address,
    		'message' => 'Successfully stored'
    	]);
    }

    public function update(Request $request, $id)
    {
    	$request->validate([
    		'region_id' => 'required|integer',
    		'district_id' => 'required|integer',
    		'village_id' => 'required|integer',
    		'address' => 'required',
    	]);
    	$data = $request->all();
    	$data['user_id'] = auth('sanctum')->id();

    	$address = UserAddress::find($id);
    	if(!$address) return response([
    		'message' => 'Address not found'
    	], 404);

    	// Proverka polzovatelya
    	$user = User::find($address->user_id);
    	if(!$user || auth('sanctum')->id() != $user->id) return response([
    		'message' => 'Polzovatel ne sootvetstvuyet'
    	], 500);

    	$address->update($data);

    	return response([
            'address' => $address,
    		'message' => 'Successfully updated'
    	]);
    }

    public function destroy($id)
    {
        $user = auth('sanctum')->user();
        $address = UserAddress::find($id);

        if(!$address) return response([
            'message' => 'Address not found'
        ], 404);

        if($user->id != $address->user_id) return response([
            'message' => 'Polzovatel ne sootvetstvuyet'
        ], 500);

        $address->delete();

        return response([
            'message' => 'Successfully deleted'
        ]);
    }
}
