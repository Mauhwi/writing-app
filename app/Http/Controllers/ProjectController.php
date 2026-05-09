<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use Inertia\Inertia;

class ProjectController extends Controller
{
    public function make(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        // 2. Create the project
        $request->user()->projects()->create($validated);
        //Project::create($validated);

        // 3. Redirect back (Inertia automatically handles the refresh)
        return redirect()->back()->with('success', 'Project created.');
        //return Inertia::flash('success', 'Project created.')->back();
    }

    public function delete(Request $request, Project $project)
    {
        if ($project->user_id !== $request->user()->id) {
            abort(403);
        }

        $project->delete();

        return redirect()->back()->with('success', 'Project '.$project->title .' deleted.');
    }

    public function show(Request $request, Project $project)
    {
        if ($project->user_id !== $request->user()->id) {
            abort(403);
        }

        $project->load([
            'parts',
            'chapters'
        ]);

        return inertia('Projects/Show', [
            'project' => $project
        ]);
    }
}
