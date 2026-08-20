<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index(Request $request)
    {
        $products = $request->user()->wishlist()->with('category', 'images')->get();

        return $this->success(ProductResource::collection($products), 'Success');
    }

    public function toggle(Request $request, Product $product)
    {
        $exists = $request->user()->wishlist()->where('product_id', $product->id)->exists();

        if ($exists) {
            $request->user()->wishlist()->detach($product->id);
            return $this->success(['wishlisted' => false], 'Removed from wishlist');
        }

        $request->user()->wishlist()->attach($product->id);
        return $this->success(['wishlisted' => true], 'Added to wishlist');
    }
}
