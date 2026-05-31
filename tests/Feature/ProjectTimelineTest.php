<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProjectTimelineTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_page_shows_timeline_with_decisions_and_files(): void
    {
        Queue::fake();
        Storage::fake('uploads');
        $this->actingAs($this->user());

        $this->post(route('projects.store'), [
            'title' => 'Refonte atelier',
            'summary' => 'Projet de refonte',
            'description' => 'Suivre les arbitrages et livrables.',
            'status' => 'idee',
            'priority' => 'normale',
            'category' => 'web',
        ])->assertRedirect();

        $project = Project::query()->where('title', 'Refonte atelier')->firstOrFail();

        $this->post(route('decisions.store'), [
            'project_id' => $project->id,
            'source_note_id' => null,
            'title' => 'Choisir Laravel',
            'decision' => 'Utiliser Laravel pour le MVP.',
            'justification' => 'Equipe deja autonome.',
            'alternatives' => null,
            'risks' => null,
            'impact' => null,
            'status' => 'validee',
        ])->assertRedirect();

        $this->post(route('files.store'), [
            'project_id' => $project->id,
            'note_id' => null,
            'description' => 'Compte rendu initial',
            'uploads' => [UploadedFile::fake()->createWithContent('atelier.txt', 'contenu de test')],
        ])->assertRedirect();

        $this->get(route('projects.show', $project))
            ->assertOk()
            ->assertSee('Timeline du projet')
            ->assertSee('Projet cree')
            ->assertSee('Decision creee')
            ->assertSee('Choisir Laravel')
            ->assertSee('Fichier ajoute')
            ->assertSee('atelier.txt')
            ->assertSee('Changements de statut');

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'decision_created',
            'entity_type' => Project::class,
            'entity_id' => $project->id,
        ]);
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'file_created',
            'entity_type' => Project::class,
            'entity_id' => $project->id,
        ]);
    }

    public function test_project_timeline_can_be_filtered_by_status_changes(): void
    {
        $this->actingAs($this->user());
        $project = Project::query()->create([
            'title' => 'Projet filtre',
            'slug' => 'projet-filtre',
            'status' => 'idee',
            'priority' => 'normale',
        ]);

        ActivityLog::query()->create([
            'action' => 'note_created',
            'entity_type' => Project::class,
            'entity_id' => $project->id,
            'metadata' => ['title' => 'Note hors filtre'],
        ]);

        $this->patch(route('projects.update', $project), [
            'title' => 'Projet filtre',
            'summary' => null,
            'description' => null,
            'status' => 'en_cours',
            'priority' => 'normale',
            'category' => null,
        ])->assertRedirect();

        $this->get(route('projects.show', ['project' => $project, 'timeline' => 'status']))
            ->assertOk()
            ->assertSee('Statut modifie')
            ->assertSee('idee -&gt; en_cours', false)
            ->assertDontSee('Note hors filtre');
    }

    private function user(): User
    {
        return User::query()->create([
            'name' => 'Test User',
            'email' => 'test@example.test',
            'password' => Hash::make('password'),
        ]);
    }
}
