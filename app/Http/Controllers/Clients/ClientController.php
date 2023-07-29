<?php

namespace App\Http\Controllers\Clients;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ClientController extends Controller
{
	protected $PAGINATE = 16;

    public function index()
    {
    	$clients = User::latest()
    		->paginate($this->PAGINATE);

    	return response([
    		'clients' => $clients
    	]);
    }

    public function show($id)
    {
    	$client = User::where('id', $id)
    		->with('addresses', 'addresses.region', 'addresses.district','addresses.village', 'orders')
    		->first();
    	if(!$client) return response([
    		'message' => 'Client not found'
    	]);

		return response([
			'client' => $client
		]);
    }
}
