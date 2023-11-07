<?php

namespace App\Http\Controllers\web;

use App\Models\Orders\Order;
use App\Models\UserAddress;
use App\Models\Products\Product;
use App\Models\Comment;
use App\Models\Dicoin\DicoinHistory;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
            'surname' => 'nullable|max:255',
            'current_password' => [Rule::requiredIf(function () use ($request) {
                return auth('sanctum')->user()->password_updated && $request->input('password') !== null;
            }), 'min:6', 'max:255'],
            'password' => 'nullable|confirmed|min:6|max:255',
//            'phone_number' => 'required|min:998000000001|max:999999999998|numeric',
            // 'region_id' => 'nullable|integer',
            // 'district_id' => 'nullable|integer',
            // 'address' => 'nullable',
            'email' => 'nullable|max:255|email',
            'postcode' => 'nullable|max:255',
            'subscriber' => 'nullable|boolean',
        ]);

        if(!auth('sanctum')->user()) return response([
            'message' => 'Unauthorized'
        ], 401);

         if($request->input('current_password') !== null && !(auth('sanctum')->user()->password_updated && Hash::check(trim($request->input('current_password')), auth('sanctum')->user()->password))) return response([
             'message' => 'Nepravilniy parol'
         ], 400);

         DB::beginTransaction();
         try {
             auth('sanctum')->user()->update([
                 'name' => $request->input('name'),
                 'surname' => $request->input('last_name'),
                 'password' => $request->input('password') ? Hash::make($request->input('password')) : auth('sanctum')->user()->password,
                 'password_updated' => 1,
                 // 'address' => $request->address,
                 // 'region_id' => $request->region_id,
                 // 'district_id' => $request->district_id,
                 'postcode' => $request->input('postcode'),
                 'email' => $request->input('email'),
                 'subscriber' => $request->input('subscriber') ?? null
             ]);

             auth('sanctum')->user()->tokens()->delete();

             DB::commit();
         } catch (\Exception $e) {
             DB::rollBack();

            return response([
                'message' => $e->getMessage()
            ], 500);
         }


        return response([
            'user' => auth('sanctum')->user(),
            'new_token' => auth('sanctum')->user()->createToken('auth-token', ['client'])->plainTextToken,
            'message' => 'Saved'
        ]);
    }

    public function edit_name(Request $request)
    {
        $request->validate([
            'name' => 'required'
        ]);

        if(!auth('sanctum')->user()) return response([
            'message' => 'Unauthorized'
        ], 401);

        auth('sanctum')->user()->update([
            'name' => $request->name
        ]);

        return response([
            'user' => auth('sanctum')->user(),
            'message' => 'Saved'
        ]);
    }

    public function me()
    {
        $user = auth('sanctum')->user();

        $orders = Order::where('user_id', $user->id)
            ->get();
        $user->orders = $orders;

        $addresses = UserAddress::where('user_id', $user->id)
            ->with('region', 'district')
            ->latest()
            ->get();

        $comments = Comment::where('user_id', $user->id)
            ->with('product_info', 'product_info.products', 'product_info.products.images')
            ->paginate(16);

        $user->comments = $comments;

        foreach ($addresses as $address) {
            $this->without_lang([$address->region]);
            $this->without_lang([$address->district]);
        }
        $user->addresses = $addresses;

        foreach($user->orders as $order) {
            /*
             * privyazka produkta k zakazu
             */
            $products = $order->products;

            foreach($products as $key => $product) {
                $new_arr = $product;

                $new_arr['product'] = Product::with('info', 'images')->find($product['product_id']);

                $products[$key] = $new_arr;
            }

            $order->products = $products;
        }

        return response([
            'user' => $user
        ]);
    }

    public function dicoins(Request $request)
    {
        $dicoins = DicoinHistory::where([
                ['user_id', auth('sanctum')->id()]
            ])
            ->get();
        $in = $dicoins->where('type', 'plus');
        $out = $dicoins->where('type', 'minus');

        return response([
            'in' => $in,
            'out' => $out,
        ]);
    }
}
