<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryCollection;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ResourceCollection;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\Request;
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

    /**
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function getAll()
    {
       $categories =  $this->categoryService->getAll();
       return (new CategoryCollection($categories))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * @param Category $category
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function get(Category $category)
    {
        $category =  $this->categoryService->get($category);
        return (new CategoryResource($category))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * @param Category $category
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function getResources(Request $request, Category $category)
    {
        $resources =  $this->categoryService->getResources($category, $request->active);
        return (new ResourceCollection($resources))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * @param Category $category
     * @param UpdateCategoryRequest $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function update(Category $category, UpdateCategoryRequest $request)
    {
        $category =  $this->categoryService->update($category, $request->all());
        return (new CategoryResource($category))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * @param StoreCategoryRequest $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function store(StoreCategoryRequest $request)
    {
        $category = $this->categoryService->store($request->all());
        return (new CategoryResource($category))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * @param Category $category
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function delete(Category $category)
    {
        $this->categoryService->delete($category);
        return response(null, Response::HTTP_NO_CONTENT);
    }
}
