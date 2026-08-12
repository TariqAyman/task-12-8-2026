<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UpdateServiceRequest;
use App\Http\Resources\ServiceResource;
use App\Models\Service;
use App\Services\ServiceService;
use App\Support\Queries\ServiceQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(protected ServiceService $services) {}

    /**
     * Display a paginated listing of every service.
     */
    public function index(Request $request): JsonResponse
    {
        return response()->json(
            $this->services->cachedListing(ServiceQuery::fromRequest($request)),
        );
    }

    /**
     * Display the given service.
     */
    public function show(Service $service): ServiceResource
    {
        return ServiceResource::make($this->services->show($service));
    }

    /**
     * Update the given service.
     */
    public function update(UpdateServiceRequest $request, Service $service): ServiceResource
    {
        return ServiceResource::make(
            $this->services->update($service, $request->validated()),
        );
    }

    /**
     * Remove the given service.
     */
    public function destroy(Service $service): JsonResponse
    {
        $this->services->delete($service);

        return response()->json([
            'message' => 'Service deleted successfully.',
        ]);
    }
}
