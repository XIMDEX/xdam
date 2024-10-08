<?php

namespace App\Services;

use App\Enums\ResourceType;
use App\Models\Category;

class CategoryService
{

    /**
     * @param $name
     * @return string|string[]
     */
    private function satinizeCategoryName($name)
    {
        return str_replace(" ", "_", $name);
    }

    /**
     * @return Category[]
     */
    public static function getAll()
    {
        return Category::all();
    }

    /**
     * @param Category $category
     * @return Category
     */
    public function get(Category $category)
    {
        return $category;
    }

    /**
     * @param Category $category
     * @return mixed
     */
    public function getResources(Category $category, $active)
    {
        $active = $active == null ? 1 : $active;
        return $category->resources()->where('active', $active)->get();
    }

    /**
     * @param Category $category
     * @param $data
     * @return Category|false
     * @throws \BenSampo\Enum\Exceptions\InvalidEnumKeyException
     */
    public function update(Category $category, $data)
    {
        $updated = $category->update([
            'name' => $this->satinizeCategoryName($data["name"]),
            'type' => ResourceType::fromKey($data["type"])->value
        ]);
        return $category;
    }

    /**
     * @param $params
     * @return Category
     * @throws \BenSampo\Enum\Exceptions\InvalidEnumKeyException
     */
    public function store($params) : Category
    {
        $category = Category::firstOrCreate([
            'name' => $this->satinizeCategoryName($params["name"]), 
            'type' => $params["type"]
        ]);
        return $category;
    }

    /**
     * @param Category $category
     * @throws \Exception
     */
    public function delete(Category $category)
    {
        $category->delete();
    }

    public static function where($column, $value, $operator = '=')
    {
        return Category::where($column, $value, $operator)->get();
    }
}
