<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function placeOrder(Request $request)
    {
        // validate the input
        $validator = $this->validator($request->all());
        // If validation fails then return with status failed and validation error message
        if ($validator->fails()) {
            return response()->json(['status' => 'failed', 'validationErrors' => $validator->errors()]);
        }
        $order = new Order();
        $order->user_id = Auth::user()->id;
        $order->amount = $request->order_amount;
        $order->description = $request->description;
        $order->order_date = Carbon::now();
        $data = array();
        if (!empty($request->products)) {
            foreach ($request->products as $key => $productId) {
                $data[$productId] = ['product_quantity' => $request->product_quantity[$key]];
            }
        }

        DB::beginTransaction();
        $orderQuery = $order->save();
        $productQuery = false;
        if (!empty($data)) {
            $productQuery = $order->products()->attach($data);
        }

        if ($orderQuery != true || $productQuery != null) {
            DB::rollBack();
        }
        DB::commit();

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully placed order!'
        ], 201);
    }

    private function validator($data)
    {
        $validator = Validator::make($data,
            [
                'order_amount' => 'bail|required|numeric|gt:0',
                'products' => 'required|array',
                'product_quantity' => 'required|array',
                //'description' => 'bail|required|string',
                //'estimated_delivery_date' => 'bail|nullable|date',
            ]
        );

        return $validator;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function signup(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'name' => 'required|string',
                'email' => 'required|string|email|unique:users',
                'password' => 'required|string|confirmed',
                'is_admin' => 'bail|boolean',
                'shipping_address' => 'bail|required|string',
            ]
        );
        // If validation fails then return with status failed and validation error message
        if ($validator->fails()) {
            return response()->json(['status' => 'failed', 'validationErrors' => $validator->errors()]);
        }

        $user = new User([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'is_admin' => ($request->is_admin) ? $request->is_admin : 0,
            'shipping_address' => $request->shipping_address,
        ]);
        // If validation fails then return with status failed and validation error message
        if ($validator->fails()) {
            return response()->json(['status' => 'failed', 'validationErrors' => $validator->errors()]);
        }

        $user->save();
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully created user!'
        ], 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'email' => 'required|string|email',
                'password' => 'required|string',
                'remember_me' => 'boolean'
            ]
        );
        // If validation fails then return with status failed and validation error message
        if ($validator->fails()) {
            return response()->json(['status' => 'failed', 'validationErrors' => $validator->errors()]);
        }
        $credentials = request(['email', 'password']);
        if(!Auth::attempt($credentials))
            return response()->json([
                'status' => 'failed',
                'message' => 'Unauthorized'
            ], 401);
        $user = $request->user();
        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;
        if ($request->remember_me)
            $token->expires_at = Carbon::now()->addWeeks(1);
        $token->save();
        return response()->json([
            'status' => 'success',
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out'
        ]);
    }

    public function user()
    {
        $user = Auth::user();
        return response()->json(['status' => 'success','user' => $user]);
    }
}
