<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function getAllProducts()
    {
        $products = Product::all();

        return ['status' => 'success', 'products' => $products];
    }

    public function getAllProductGroups()
    {
        $products = Product::with('parent')->get();
        dd($products);
    }

    public function create(Request $request)
    {
        $validator = \Validator::make($request->all(),
            [
                'name' => 'bail|required|string',
                'price' => 'bail|required|numeric|gt:0',
                'discount_price' => 'bail|nullable|numeric|gt:0',
                'discount_percent' => 'bail|nullable|numeric|gt:0',
                'parent_product_id' => 'bail|nullable|numeric',
            ]
        );
        //dd($validator->errors());
        if ($validator->fails()) {
            return ['status' => 'failed', 'validationErrors' => $validator->errors()];
        }

        $product = new Product();
        $product->name = $request->name;
        $product->price = $request->price;
        $product->discount_price = $request->discount_price;
        $product->discount_percent = $request->discount_percent;
        $product->parent_product_id = $request->parent_product_id;
        $product->save();

        return ['status' => 'success', 'message' => 'Successfully saved'];
    }
}
