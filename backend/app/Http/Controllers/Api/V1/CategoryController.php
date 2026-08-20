<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::withCount('products')
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return $this->success(CategoryResource::collection($categories), 'Success');
    }

    public function store(StoreCategoryRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('categories', 'public');
        }
        $data['slug'] = $data['slug'] ?? \Str::slug($data['name']);

        $category = Category::create($data);

        return $this->success(new CategoryResource($category), 'Category created', 201);
    }

    public function show(Category $category)
    {
        return $this->success(new CategoryResource($category), 'Success');
    }

    public function update(StoreCategoryRequest $request, Category $category)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        $category->update($data);

        return $this->success(new CategoryResource($category), 'Category updated');
    }

    public function destroy(Request $request, Category $category)
    {
        if (!$request->user()->isAdmin()) {
            return $this->error('Unauthorized', 403);
        }

        $category->delete();

        return $this->success(null, 'Category deleted');
    }
}
