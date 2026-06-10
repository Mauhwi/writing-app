<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use Illuminate\Support\Facades\Storage;

class ProjectController extends Controller
{
    public function store(Request $request)
    {
        $this->authorize('create', Project::class);

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
        $this->authorize('delete', $project);

        $project->delete();

        return redirect()->back()->with('success', 'Project '.$project->title .' deleted.');
    }

    public function show(Request $request, Project $project)
    {
        $this->authorize('view', $project);

        $project->load([
            'parts',
            'chapters'
        ]);

        $project->chapters->transform(function ($chapter) {
            $chapter->updated_at_human = $chapter->updated_at->diffForHumans();
            if ($chapter->content)
                $chapter->word_count = str_word_count(strip_tags($chapter->content));
            return $chapter;
        });

        $project->word_count = 0;
        foreach ($project->chapters as $chapter) {
            if ($chapter->word_count)
                $project->word_count += $chapter->word_count;
        }

        $project->updated_at_human = $project->updated_at->diffForHumans(); 

        return inertia('Projects/Show', [
            'project' => $project
        ]);
    }

    public function update(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $action = $request->input('action');

        switch ($action) {
            case 'update_details':
                return $this->updateDetails($request, $project);

            case 'create_part':
                return $this->createPart($request, $project);

            case 'rename_part':
                return $this->renamePart($request, $project);

            case 'delete_part':
                return $this->deletePart($request, $project);

            default:
                abort(400, 'Unknown action.');
        }
    }

    public function updateDetails(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);
        $project->update($validated);

        return back();
    }

    private function createPart(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
        ]);

        $project->parts()->create([
            'title' => $validated['title'],
        ]);

        return back();
    }

    private function renamePart(Request $request, Project $project)
    {
        $validated = $request->validate([
            'part_id' => ['required', 'integer'],
            'title' => ['required', 'string', 'max:255'],
        ]);

        $part = $project->parts()
            ->where('id', $validated['part_id'])
            ->firstOrFail();

        $part->update([
            'title' => $validated['title'],
        ]);

        return back();
    }

    private function deletePart(Request $request, Project $project)
    {
        $validated = $request->validate([
            'part_id' => ['required', 'integer'],
        ]);

        $part = $project->parts()
            ->where('id', $validated['part_id'])
            ->firstOrFail();
            
        $part->chapters()->update([
            'part_id' => null,
        ]);

        $part->delete();

        return back();
    }

    public function updateCover(Request $request, Project $project)
    {
        $this->authorize('update', $project);

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
        $this->authorize('update', $project);

        if ($project->cover_image) {
            Storage::disk('public')->delete($project->cover_image);
        }

        $project->update([
            'cover_image' => null,
        ]);

        return back();
    }
}
