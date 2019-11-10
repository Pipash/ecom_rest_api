<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * Class UserController
 * To manage user actions
 *
 * @package App\Http\Controllers
 */
class UserController extends Controller
{
    /**
     * To Place order from users
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function placeOrder(Request $request)
    {
        // validate the input
        $validator = $this->validator($request->all());
        // If validation fails then return with status failed and validation error message
        if ($validator->fails()) {
            return response()->json(['status' => 'failed', 'validationErrors' => $validator->errors()]);
        }

        // initializing new order object
        $order = new Order();
        $order->user_id = Auth::user()->id;
        $order->amount = $request->order_amount;
        $order->description = $request->description;
        $order->order_date = Carbon::now();
        $data = array();

        // preparing data to insert in order_product table
        if (!empty($request->products)) {
            foreach ($request->products as $key => $productId) {
                $data[$productId] = ['product_quantity' => $request->product_quantity[$key]];
            }
        }

        // start data insertion
        DB::beginTransaction();
        // insert order data
        $orderQuery = $order->save();
        $productQuery = false;
        if (!empty($data)) {
            // insert order_product data
            $productQuery = $order->products()->attach($data);
        }

        // if data is not inserted in both of the tables then no insertion will be executed
        if ($orderQuery != true || $productQuery != null) {
            DB::rollBack();
        }
        DB::commit();
        // end of data insertion process

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully placed order!'
        ], 201);
    }

    /**
     * Validate data
     *
     * @param $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
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
     * Signup users
     *
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

        // data insertion in user table
        $user = new User([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'is_admin' => ($request->is_admin) ? $request->is_admin : 0,
            'shipping_address' => $request->shipping_address,
        ]);
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully created user!'
        ], 201);
    }

    /**
     * Login user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        // setting up validation rules
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

        // getting request data
        $credentials = request(['email', 'password']);

        // check the credentials
        if(!Auth::attempt($credentials)) {
            // if credentials fails then respond failed message
            return response()->json([
                'status' => 'failed',
                'message' => 'Unauthorized'
            ], 401);
        }

        // get the user
        $user = $request->user();
        // get token
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

    /**
     * Logout user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        // destroying token
        $request->user()->token()->revoke();

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out'
        ]);
    }

    /**
     * Get user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function user()
    {
        $user = Auth::user();

        return response()->json(['status' => 'success','user' => $user]);
    }
}
