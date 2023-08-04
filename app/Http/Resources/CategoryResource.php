<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'is_popular' => $this->is_popular,
            'desc' => $this->desc,
            'icon' => $this->icon,
            'icon_svg' => $this->icon_svg,
            'img' => $this->img,
            'sm_img' => $this->sm_img,
            'md_img' => $this->md_img,
            'lg_img' => $this->lg_img,
            'slug' => $this->slug,
            'products_count' => $this->products_count,
            'children' => $this->children,
        ];
    }
}
