<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;

class ProjectController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', auth()->user());

        $search = $request->query('search');

        $cacheKey = 'projects:'.md5((string) $search);

        return ProjectResource::collection(Cache::remember($cacheKey, 600, function () use ($search) {
            return Project::query()
                ->when($search, function ($query, $search) {
                    $query->where('name', 'like', "%{$search}%");
                })
                ->get();
        }));
    }

    public function store(ProjectRequest $request)
    {
        Gate::authorize('store', auth()->user());

        StoreProjectMailJob::dispatch(Customer::first('customer_id', $request->customer_id));

        return new ProjectResource(Project::create($request->validate()));
    }

    public function show(Project $project)
    {
        Gate::authorize('view', auth()->user(), $project);

        return new ProjectResource($project);
    }

    public function update(ProjectRequest $request, Project $project)
    {
        Gate::authorize('update', auth()->user(), $project);

        $project->update($request->validate());

        return response()->noContent();
    }

    public function destroy(Project $project)
    {
        Gate::authorize('delete', auth()->user(), $project);
        Cache::forgot('projects');

        DeleteProjectMailJob::dispatch(Customer::first('customer_id', $request->customer_id));

        $project->delete();

        return response()->noContent();
    }
}
