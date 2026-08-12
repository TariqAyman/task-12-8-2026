<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreCustomerRequest;
use App\Http\Requests\Api\V1\UpdateCustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use App\Services\CustomerService;
use App\Support\Queries\CustomerQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CustomerController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(protected CustomerService $customers) {}

    /**
     * Display a paginated listing of customers.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        return CustomerResource::collection(
            $this->customers->paginate(CustomerQuery::fromRequest($request)),
        );
    }

    /**
     * Store a newly created customer.
     */
    public function store(StoreCustomerRequest $request): JsonResponse
    {
        $customer = $this->customers->create($request->validated());

        return CustomerResource::make($customer)
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the given customer.
     */
    public function show(Customer $customer): CustomerResource
    {
        return CustomerResource::make($this->customers->show($customer));
    }

    /**
     * Update the given customer.
     */
    public function update(UpdateCustomerRequest $request, Customer $customer): CustomerResource
    {
        return CustomerResource::make(
            $this->customers->update($customer, $request->validated()),
        );
    }

    /**
     * Remove the given customer along with its services.
     */
    public function destroy(Customer $customer): JsonResponse
    {
        $this->customers->delete($customer);

        return response()->json([
            'message' => 'Customer deleted successfully.',
        ]);
    }
}
