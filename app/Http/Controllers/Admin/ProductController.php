<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::all();

        return $products;
    }

    public function create(Request $request)
    {
        $this->validate($request,
            [
                'name'=> 'required',
                
            ]
        );
    }
}
