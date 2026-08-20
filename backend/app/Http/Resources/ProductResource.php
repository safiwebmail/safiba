<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'description' => $this->description,
            'price' => (float) $this->price,
            'sale_price' => $this->sale_price !== null ? (float) $this->sale_price : null,
            'effective_price' => (float) $this->effective_price,
            'size' => $this->size,
            'color' => $this->color,
            'fabric' => $this->fabric,
            'featured' => $this->featured,
            'status' => $this->status,
            'shop_id' => $this->shop_id,
            'category_id' => $this->category_id,
            'category' => $this->whenLoaded('category', fn () => new CategoryResource($this->category)),
            'images' => $this->whenLoaded('images', fn () => $this->images->map(fn ($img) => url('storage/' . $img->path))->values()),
            'main_image' => $this->main_image ? url('storage/' . $this->main_image) : null,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
