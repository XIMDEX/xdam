<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoleAbility\AbilityRequest;
use App\Http\Resources\AbilityResource;
use App\Services\AbilityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

class AbilityController extends Controller
{
    /**
     * @var AbilityService
     */
    private $abilityService;

    /**
     * RoleController constructor.
     * @param abilityService $abilityService
     */
    public function __construct(AbilityService $abilityService)
    {
        $this->abilityService = $abilityService;
    }

    public function store(AbilityRequest $abilityRequest): JsonResponse
    {
        $ability = $this->abilityService->store($abilityRequest->name, $abilityRequest->title);
        return (new AbilityResource($ability))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function index(): JsonResponse
    {
        $abilites = $this->abilityService->index();
        return (new JsonResource($abilites))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function get(Request $request): JsonResponse
    {
        $ability = $this->abilityService->get($request->id);
        return (new AbilityResource($ability))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function update(Request $request): JsonResponse
    {
        $ability = $this->abilityService->update($request->id);
        //update logic
        return (new AbilityResource($ability))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function delete(Request $request): JsonResponse
    {
        $ability = $this->abilityService->delete($request->id);
        return (new JsonResource($ability))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }
}
