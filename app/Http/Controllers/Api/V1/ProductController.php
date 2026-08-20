<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\AuditLog;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $products = Product::with(['category', 'images'])
            ->when($request->query('search'), fn ($q, $s) => $q->where(fn ($q) => $q->where('name', 'ilike', "%{$s}%")->orWhere('sku', 'ilike', "%{$s}%")->orWhere('description', 'ilike', "%{$s}%")))
            ->when($request->query('category'), fn ($q, $c) => $q->where('category_id', $c))
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->when($request->query('featured'), fn ($q) => $q->where('featured', true))
            ->when($user && $user->isShopManager() && !$user->isAdmin(), fn ($q) => $q->where(fn ($q) => $q->where('shop_id', $user->shop_id)->orWhereNull('shop_id')))
            ->orderBy($request->query('sort', 'created_at'), $request->query('order', 'desc'))
            ->paginate($request->query('per_page', 12));

        return $this->success(ProductResource::collection($products), 'Success');
    }

    public function store(StoreProductRequest $request)
    {
        $data = $request->validated();
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']) . '-' . Str::lower(Str::random(4));
        $data['shop_id'] = $request->user()->isAdmin() ? ($data['shop_id'] ?? null) : $request->user()->shop_id;

        $product = Product::create($data);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $i => $image) {
                $product->images()->create([
                    'path' => $image->store('products', 'public'),
                    'is_primary' => $i === 0,
                    'sort_order' => $i,
                ]);
            }
        }

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'product.create',
            'entity_type' => 'product',
            'entity_id' => $product->id,
            'description' => "Product '{$product->name}' created",
        ]);

        return $this->success(new ProductResource($product->load('category', 'images')), 'Product created', 201);
    }

    public function show(Request $request, $slugOrId)
    {
        $product = Product::with(['category', 'images'])
            ->where('slug', $slugOrId)
            ->orWhere('id', $slugOrId)
            ->firstOrFail();

        if (!$request->user() || !$request->user()->isShopManager()) {
            abort_if($product->status !== 'active', 404);
        }

        return $this->success(new ProductResource($product), 'Success');
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $this->authorize('update', $product);

        $data = $request->validated();

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $i => $image) {
                $product->images()->create([
                    'path' => $image->store('products', 'public'),
                    'is_primary' => !$product->images()->exists() && $i === 0,
                    'sort_order' => $product->images()->count() + $i,
                ]);
            }
        }

        $product->update($data);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'product.update',
            'entity_type' => 'product',
            'entity_id' => $product->id,
            'description' => "Product '{$product->name}' updated",
        ]);

        return $this->success(new ProductResource($product->load('category', 'images')), 'Product updated');
    }

    public function destroy(Request $request, Product $product)
    {
        $this->authorize('delete', $product);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'product.delete',
            'entity_type' => 'product',
            'entity_id' => $product->id,
            'description' => "Product '{$product->name}' deleted",
        ]);

        $product->delete();

        return $this->success(null, 'Product deleted');
    }
}
