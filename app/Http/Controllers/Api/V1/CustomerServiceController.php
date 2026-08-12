<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreServiceRequest;
use App\Http\Resources\ServiceResource;
use App\Models\Customer;
use App\Services\ServiceService;
use App\Support\Queries\ServiceQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerServiceController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(protected ServiceService $services) {}

    /**
     * Display a paginated listing of the given customer's services.
     */
    public function index(Request $request, Customer $customer): JsonResponse
    {
        return response()->json(
            $this->services->cachedListing(ServiceQuery::fromRequest($request, $customer)),
        );
    }

    /**
     * Store a newly created service for the given customer.
     */
    public function store(StoreServiceRequest $request, Customer $customer): JsonResponse
    {
        $service = $this->services->createForCustomer($customer, $request->validated());

        return ServiceResource::make($service)
            ->response()
            ->setStatusCode(201);
    }
}
