<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;

class CustomerController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', auth()->user());

        $search = $request->query('search');

        $cacheKey = 'customers:'.md5((string) $search);

        return CustomerResource::collection(Cache::remember($cacheKey, 600, function () use ($search) {
            return User::query()
                ->with('customers')
                ->with('projects')
                ->when($search, function ($query, $search) {
                    $query->where('name', 'like', "%{$search}%");
                })
                ->get();
        }));
    }

    public function store(CustomerRequest $request)
    {
        Gate::authorize('store', auth()->user());

        return new CustomerResource(Customer::create($request->validate()));
    }

    public function show(Customer $customer)
    {
        Gate::authorize('view', auth()->user(), $customer);

        return new CustomerResource($customer->with('projects'));
    }

    public function update(CustomerRequest $request, Customer $customer)
    {
        Gate::authorize('update', auth()->user(), $customer);

        $customer->update($request->validate());

        return response()->noContent();
    }

    public function delete(Customer $customer)
    {
        Gate::authorize('delete', auth()->user(), $customer);

        Cache::forgot('customers');
        $customer->delete();

        return response()->noContent();
    }
}
