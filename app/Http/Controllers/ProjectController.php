<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use Illuminate\Support\Facades\Storage;

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

        $project->chapters->transform(function ($chapter) {
            $chapter->updated_at_human = $chapter->updated_at->diffForHumans();
            if ($chapter->content)
                $chapter->word_count = str_word_count($chapter->content);
            return $chapter;
        });

        return inertia('Projects/Show', [
            'project' => $project
        ]);
    }

    public function updateCover(Request $request, Project $project)
    {
        if ($project->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'cover_image' => ['required', 'image', 'max:4096'],
        ]);

        if ($project->cover_image) {
            Storage::disk('public')->delete($project->cover_image);
        }

        $path = $request->file('cover_image')->store('covers', 'public');

        $project->update([
            'cover_image' => $path,
        ]);

        return back();
    }

    public function deleteCover(Request $request, Project $project)
    {
        if ($project->user_id !== $request->user()->id) {
            abort(403);
        }

        if ($project->cover_image) {
            Storage::disk('public')->delete($project->cover_image);
        }

        $project->update([
            'cover_image' => null,
        ]);

        return back();
    }
}
