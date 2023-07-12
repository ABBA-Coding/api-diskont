<?php

namespace App\Http\Controllers\c;

use App\Http\Controllers\Controller;
use App\Models\Orders\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class OrderController extends Controller
{
    const BASE_URL = 'http://80.80.212.224:8080/Diskont/hs/web';
    const USERNAME = 'web_admin';
    const PASSWORD = 'gO7ziwyk';
    public function create_client()
    {
        $url = self::BASE_URL;
        $body = [
            'method' => 'create_client',
            'passport' => [
                'jshir' => '123456789',
                'number' => 'AA1234567',
                'date' => '01.01.1990',
            ],
            'client' => [
                'jshir' => '900010101',
                'fio' => 'test',
                'adress' => 'test',
            ],
            'card' => [
                'number' => '1234 5678 1234 5678',
                'date' => '01/26',
            ]
        ];

        $res = Http::withBasicAuth(self::USERNAME, self::PASSWORD)->post($url, $body);

        dd($res->status());
    }
}
