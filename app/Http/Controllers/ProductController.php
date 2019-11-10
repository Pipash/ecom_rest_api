<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * Get all products as single and bundle wise
     *
     * @return array
     */
    public function getAllProducts()
    {
        // Get all single products
        $singleProducts = Product::where('is_bundle', false)->paginate(20);

        // Calculate discounted price and keep in another key total_price
        if (!empty($singleProducts)) {
            foreach ($singleProducts as $product) {
                $totalPrice = $product->price;
                if ($product->discount_price) {
                    $totalPrice = $product->price - $product->discount_price;
                } elseif ($product->discount_percent) {
                    $totalPrice = $product->price - ($product->price * $product->discount_percent / 100);
                }
                $product->total_price = $totalPrice;
            }
        }


        // Get all bundle products
        $bundleProducts = Product::with('children')->where('is_bundle', true)->paginate(20);
        // Calculate discounted price and keep in another key total_price
        if (!empty($bundleProducts)) {
            foreach ($bundleProducts as $product) {
                $totalPrice = $product->price;
                if ($product->discount_price) {
                    $totalPrice = $product->price - $product->discount_price;
                } elseif ($product->discount_percent) {
                    $totalPrice = $product->price - ($product->price * $product->discount_percent / 100);
                }
                $product->total_price = $totalPrice;
            }
        }


        return response()->json(['status' => 'success','single_products' => $singleProducts, 'bundle_products' => $bundleProducts]);
    }

    /**
     * Get single product with it's children product if any
     *
     * @param integer $id
     * @return array
     */
    public function getSingleProduct($id)
    {
        $product = Product::with('parent', 'children')->findOrFail($id);
        // Calculate discounted price and keep in another key total_price
        if ($product->discount_price) {
            $product->total_price = $product->price - $product->discount_price;
        } elseif ($product->discount_percent) {
            $product->total_price = $product->price - ($product->price * $product->discount_percent / 100);
        }

        return response()->json(['status' => 'success', 'products' => $product]);
    }

    /**
     * Add a new Product
     *
     * @param Request $request
     * @return array
     */
    public function create(Request $request)
    {
        if (!Auth::user()->is_admin) {
            return response()->json(['status' => 'failed', 'message' => 'Not authorized!'], 401);
        }
        // validate the input
        $validator = $this->validator($request->all());
        //dd($validator->errors());
        // If validation fails then return with status failed and validation error message
        if ($validator->fails()) {
            return response()->json(['status' => 'failed', 'validationErrors' => $validator->errors()]);
        }

        // Creating new product
        $product = new Product();
        $this->saveProduct($product, $request);
        /*$product->name = $request->name;
        $product->price = $request->price;
        $product->discount_price = $request->discount_price;
        // if discount price is not set only then discount percent can be set
        if (empty($request->discount_price)) {
            $product->discount_percent = $request->discount_percent;
        }
        // if the product is a bundle product
        $product->is_bundle = $request->is_bundle;

        // if no
        if (empty($request->children) && !$request->is_bundle) {
            $product->parent_product_id = $request->parent_product_id;
        }
        $product->save();
        if ($request->is_bundle) {
            $children_product_ids = '';
            if (!empty($request->children)) {
                foreach ($request->children as $child) {
                    $children_product_ids .= $child.',';
                }
                Product::whereIn('id', $children_product_ids)->update(['parent_product_id', $product->id]);
            }
        }*/

        return response()->json(['status' => 'success', 'message' => 'Successfully saved!']);
        //return ['status' => 'success', 'message' => 'Successfully saved!'];
    }

    public function update(Request $request, $id)
    {
        if (!Auth::user()->is_admin) {
            return response()->json(['status' => 'failed', 'message' => 'Not authorized!'], 401);
        }
        // validate the input
        $validator = $this->validator($request->all());
        //dd($validator->errors());
        // If validation fails then return with status failed and validation error message
        if ($validator->fails()) {
            return ['status' => 'failed', 'validationErrors' => $validator->errors()];
        }
        $product = Product::with('children')->findOrFail($id);
        if (!empty($product->children)) {
            Product::where('parent_product_id', $id)->update(['parent_product_id' => null]);
        }

        $this->saveProduct($product, $request);

        return response()->json(['status' => 'success', 'message' => 'Successfully saved!']);
        //return ['status' => 'success', 'message' => 'Successfully saved!'];
    }

    private function saveProduct($product, $request)
    {
        $product->name = $request->name;
        $product->price = $request->price;
        $product->description = $request->description;
        $product->size = $request->size;
        $product->color = $request->color;
        $product->discount_price = $request->discount_price;
        if (empty($request->discount_price)) {
            $product->discount_percent = $request->discount_percent;
        }
        if ($request->is_bundle) {
            $product->is_bundle = $request->is_bundle;
        } else {
            $product->parent_product_id = $request->parent_product_id;
        }
        $product->save();

        if (!empty($request->children)) {
            Product::whereIn('id', $request->children)->update(['parent_product_id' => $product->id]);
        }
    }

    public function delete($id)
    {
        if (!Auth::user()->is_admin) {
            return response()->json(['status' => 'failed', 'message' => 'Not authorized!'], 401);
        }
        $product = Product::with('children')->findOrFail($id);
        if (!empty($product->children)) {
            Product::where('parent_product_id', $id)->update(['parent_product_id' => null]);
        }
        $product->delete();

        return response()->json(['status' => 'success', 'message' => 'Successfully deleted!']);
        //return ['status' => 'success', 'message' => 'Successfully deleted!'];
    }

    private function validator($data)
    {
        $validator = Validator::make($data,
            [
                'name' => 'bail|required|string',
                'price' => 'bail|required|numeric|gt:0',
                'discount_price' => 'bail|nullable|numeric|gt:0',
                'discount_percent' => 'bail|nullable|numeric|gt:0',
                'parent_product_id' => 'bail|nullable|numeric',
                'description' => 'bail|nullable|string',
                'size' => 'bail|nullable|string',
                'color' => 'bail|nullable|string',
                'children' => 'nullable|array',
                'is_bundle' => 'bail|boolean',
            ]
        );

        return $validator;
    }
}
