<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Customer::class);

        return CustomerResource::collection(Customer::query()->when($request->query('search'), fn ($query, $search) => $query->where('company_name', 'like', "%{$search}%"))->get());
    }

    public function store(CustomerRequest $request)
    {
        Gate::authorize('store', Customer::class);

        return new CustomerResource(Customer::create([...$request->validated(), 'user_id' => $request->user()->id]));
    }

    public function show(Customer $customer)
    {
        Gate::authorize('view', $customer);

        return new CustomerResource($customer);
    }

    public function update(CustomerRequest $request, Customer $customer)
    {
        Gate::authorize('update', $customer);
        $customer->update($request->validated());

        return response()->noContent();
    }

    public function destroy(Customer $customer)
    {
        Gate::authorize('delete', $customer);
        // Preserve project history by requiring projects to be removed first.
        if ($customer->projects()->exists()) {
            return response()->json(['message' => 'Delete the customer’s projects first.'], 409);
        }
        $customer->delete();

        return response()->noContent();
    }
}
