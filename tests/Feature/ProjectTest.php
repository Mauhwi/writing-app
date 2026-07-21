<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Project;
use App\Models\Chapter;
use App\Models\CommentThread;
use App\Models\CommentMessage;

class ProjectTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_project_can_be_deleted_by_author()
    {
        $user = User::factory()->create([
            'role' => 'author',
        ]);

        $project = Project::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->delete("/projects/{$project->id}");

        $response->dump();
        $response->assertRedirect();

        $this->assertDatabaseMissing('projects', [
            'id' => $project->id,
        ]);
        
    }

    public function test_project_cant_be_deleted_by_default_user()
    {
        $user = User::factory()->create();

        $project = Project::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->delete("/projects/{$project->id}");

        $response->assertStatus(403);

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
        ]);
        
    }

    public function test_deleting_last_comment_deletes_comment_thread()
    {
        $user = User::factory()->create([
            'role' => 'author',
        ]);

        $project = Project::factory()->create([
            'user_id' => $user->id,
        ]);

        $chapter = $project->chapters()->create([
            'title' => 'Chapter 1',
            'content' => 'This is the first chapter.',
            'order' => 1,
        ]);

        $commentThread = CommentThread::factory()->create([
            'chapter_id' => $chapter->id,
            'created_by' => $user->id,
        ]);

        $commentMessage = CommentMessage::factory()->create([
            'thread_id' => $commentThread->id,
            'user_id' => $user->id,
            'body' => 'This is a comment.',
        ]);

        $this->assertDatabaseHas('comment_messages', [
            'id' => $commentMessage->id,
        ]);

        $this->assertDatabaseHas('comment_threads', [
            'id' => $commentThread->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->delete("/comment-messages/{$commentMessage->id}");

        $response->dump();

        $this->assertDatabaseMissing('comment_messages', [
            'id' => $commentMessage->id,
        ]);

        $this->assertDatabaseMissing('comment_threads', [
            'id' => $commentThread->id,
        ]);
    }
}
