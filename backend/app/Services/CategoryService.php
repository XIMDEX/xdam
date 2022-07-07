<?php

namespace App\Services;

use App\Enums\ResourceType;
use App\Models\Category;
use App\Models\DamResource;
use App\Services\Solr\SolrService;
use Exception;

class CategoryService
{

    private SolrService $solr;

    public function __construct(SolrService $solr)
    {
        $this->solr = $solr;
    }

    /**
     * @param $name
     * @return string|string[]
     */
    private function satinizeCategoryName($name)
    {
        return str_replace(" ", "_", $name);
    }

    private function updateCategoryResources(Category $category, string $newCategoryName): void
    {
        foreach ($category->resources->lazy() as $resource) {
            $data = $resource->data;

            $data->description->categories = array_map(
                function ($categoryName) use ($category, $newCategoryName) 
                {
                    return $categoryName == $category->name ? $newCategoryName : $category->name;
                },
                $data->description->categories
            );

            $resource->update(['data' => $data]);
            $this->solr->saveOrUpdateDocument($resource);
        }
    }

    /**
     * @return Category[]
     */
    public function getAll()
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

        if(!$updated) {
            throw new Exception("Category with id: $category->id was unable to be updated");
        }

        if($category->name !== $data["name"]) {
            $this->updateCategoryResources($category, $data["name"]);
        }

        return Category::find($category->id);
    }

    /**
     * @param $params
     * @return Category
     * @throws \BenSampo\Enum\Exceptions\InvalidEnumKeyException
     */
    public function store($params) : Category
    {
        return Category::create([
            'name' => $this->satinizeCategoryName($params["name"]),
            'type' => ResourceType::fromKey($params["type"])->value,
        ]);
    }

    /**
     * @param Category $category
     * @throws \Exception
     */
    public function delete(Category $category)
    {
        $category->delete();
    }
}
