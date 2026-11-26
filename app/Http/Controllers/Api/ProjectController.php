<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Jobs\DeleteProjectMailJob;
use App\Jobs\StoreProjectMailJob;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Project::class);

        return ProjectResource::collection(Project::query()->when($request->query('search'), fn ($query, $search) => $query->where('project_name', 'like', "%{$search}%"))->get());
    }

    public function store(ProjectRequest $request)
    {
        Gate::authorize('store', Project::class);
        $project = Project::create([...$request->validated(), 'user_id' => $request->user()->id]);
        if ($project->customer?->mail_address) {
            StoreProjectMailJob::dispatch($project->customer);
        }

        return new ProjectResource($project);
    }

    public function show(Project $project)
    {
        Gate::authorize('view', $project);

        return new ProjectResource($project);
    }

    public function update(ProjectRequest $request, Project $project)
    {
        Gate::authorize('update', $project);
        // Only administrators may transfer a project to another customer.
        if ($request->user()->role !== 'admin' && (int) $request->validated('customer_id') !== $project->customer_id) {
            abort(403);
        }
        $project->update($request->validated());

        return response()->noContent();
    }

    public function destroy(Project $project)
    {
        Gate::authorize('delete', $project);
        $customer = $project->customer;
        $project->delete();
        if ($customer?->mail_address) {
            DeleteProjectMailJob::dispatch($customer);
        }

        return response()->noContent();
    }
}
