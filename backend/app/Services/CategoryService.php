<?php

namespace App\Services;

use App\Enums\ResourceType;
use App\Models\Category;

class CategoryService
{

    private function satinizeCategoryName($name)
    {
        return str_replace(" ", "_", $name);
    }

    public function getAll()
    {
        return Category::all();
    }

    public function get(Category $category)
    {
        return $category;
    }

    public function getResources(Category $category)
    {
        return $category->resources;
    }

    public function update(Category $category, $data)
    {
        $updated = $category->update([
            'name' => $this->satinizeCategoryName($data["name"]),
            'type' => ResourceType::fromKey($data["type"])->value
        ]);
        if ($updated){
            return $category;
        }
        return false;
    }

    public function store($params) : Category
    {
        return Category::create([
            'name' => $this->satinizeCategoryName($params["name"]),
            'type' => ResourceType::fromKey($params["type"])->value,
        ]);
    }

    public function delete(Category $category)
    {
        $category->delete();
    }
}
