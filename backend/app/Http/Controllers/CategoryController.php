<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryCollection;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ResourceCollection;
use App\Models\Category;
use App\Services\CategoryService;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends Controller
{
    /**
     * @var CategoryService
     */
    private $categoryService;

    /**
     * CategoryController constructor.
     * @param CategoryService $categoryService
     */
    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    public function getAll()
    {
       $categories =  $this->categoryService->getAll();
       return (new CategoryCollection($categories))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function get(Category $category)
    {
        $category =  $this->categoryService->get($category);
        return (new CategoryResource($category))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function getResources(Category $category)
    {
        $resources =  $this->categoryService->getResources($category);
        return (new ResourceCollection($resources))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function update(Category $category, UpdateCategoryRequest $request)
    {
        $category =  $this->categoryService->update($category, $request->all());
        return (new CategoryResource($category))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function store(StoreCategoryRequest $request)
    {
        $category = $this->categoryService->store($request->all());
        return (new CategoryResource($category))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function delete(Category $category)
    {
        $this->categoryService->delete($category);
        return response(null, Response::HTTP_NO_CONTENT);
    }
}
