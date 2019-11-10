<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * Class ProductController
 * All product works
 *
 * @package App\Http\Controllers
 */
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        // if user is not admin then return failed message
        if (!Auth::user()->is_admin) {
            return response()->json(['status' => 'failed', 'message' => 'Not authorized!'], 401);
        }
        // validate the input
        $validator = $this->validator($request->all());
        // If validation fails then return with status failed and validation error message
        if ($validator->fails()) {
            return response()->json(['status' => 'failed', 'validationErrors' => $validator->errors()]);
        }

        // Creating new product
        $product = new Product();
        $this->saveProduct($product, $request);

        return response()->json(['status' => 'success', 'message' => 'Successfully saved!']);
    }

    /**
     * Update a product
     *
     * @param Request $request
     * @param integer $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        // if user is not admin then return failed message
        if (!Auth::user()->is_admin) {
            return response()->json(['status' => 'failed', 'message' => 'Not authorized!'], 401);
        }
        // validate the input
        $validator = $this->validator($request->all());
        // If validation fails then return with status failed and validation error message
        if ($validator->fails()) {
            return response()->json(['status' => 'failed', 'validationErrors' => $validator->errors()]);
        }
        // get the product
        $product = Product::with('children')->findOrFail($id);
        // if there are children of the product then make parent id null of the children
        if (!empty($product->children)) {
            Product::where('parent_product_id', $id)->update(['parent_product_id' => null]);
        }

        // update the product
        $this->saveProduct($product, $request);

        return response()->json(['status' => 'success', 'message' => 'Successfully saved!']);
    }

    /**
     * Save/Update process of the product
     *
     * @param Product $product
     * @param Request $request
     */
    private function saveProduct($product, $request)
    {
        $product->name = $request->name;
        $product->price = $request->price;
        $product->description = $request->description;
        $product->size = $request->size;
        $product->color = $request->color;
        $product->discount_price = $request->discount_price;
        // if there is no discount price inputted then discount percent can be inserted
        if (empty($request->discount_price)) {
            $product->discount_percent = $request->discount_percent;
        }
        // if the product is a bundle of products
        if ($request->is_bundle) {
            $product->is_bundle = $request->is_bundle;
        } else {
            // if product is no a bundle product, only then parent product id can be inserted
            $product->parent_product_id = $request->parent_product_id;
        }
        $product->save();

        // update all children product with the parent product id
        if (!empty($request->children)) {
            Product::whereIn('id', $request->children)->update(['parent_product_id' => $product->id]);
        }
    }

    /**
     * Delete any product
     *
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function delete($id)
    {
        // if user is not admin then return with failed message
        if (!Auth::user()->is_admin) {
            return response()->json(['status' => 'failed', 'message' => 'Not authorized!'], 401);
        }
        // get the product with it's children product
        $product = Product::with('children')->findOrFail($id);
        // if there are children then update parent id of all children with null value
        if (!empty($product->children)) {
            Product::where('parent_product_id', $id)->update(['parent_product_id' => null]);
        }
        // delete the product
        $product->delete();

        return response()->json(['status' => 'success', 'message' => 'Successfully deleted!']);
    }

    /**
     * Set validation rules
     *
     * @param array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
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
