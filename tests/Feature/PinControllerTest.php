<?php

namespace Tests\Feature;

use App\Models\Action;
use App\Models\Decision;
use App\Models\File as StoredFile;
use App\Models\Note;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PinControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_pin_route_requires_authentication(): void
    {
        $project = Project::query()->create([
            'title' => 'Projet prive',
            'slug' => 'projet-prive',
            'status' => 'idee',
            'priority' => 'normale',
        ]);

        $this->post(route('pin.toggle', ['type' => 'project', 'id' => $project->id]))
            ->assertRedirect('/login');
    }

    public function test_supported_content_types_can_be_pinned_and_unpinned(): void
    {
        $this->actingAs($this->user());

        foreach ($this->pinnableItems() as $type => $item) {
            $this->from('/dashboard')
                ->post(route('pin.toggle', ['type' => $type, 'id' => $item->id]))
                ->assertRedirect('/dashboard')
                ->assertSessionHas('status');

            $this->assertTrue((bool) $item->fresh()->is_pinned, $type.' should be pinned.');

            $this->from('/dashboard')
                ->post(route('pin.toggle', ['type' => $type, 'id' => $item->id]))
                ->assertRedirect('/dashboard')
                ->assertSessionHas('status');

            $this->assertFalse((bool) $item->fresh()->is_pinned, $type.' should be unpinned.');
        }
    }

    public function test_unsupported_pin_type_redirects_back_with_validation_error(): void
    {
        $this->actingAs($this->user());

        $this->from('/dashboard')
            ->post(route('pin.toggle', ['type' => 'unknown', 'id' => 1]))
            ->assertRedirect('/dashboard')
            ->assertSessionHasErrors('pin');
    }

    /**
     * @return array<string, \Illuminate\Database\Eloquent\Model>
     */
    private function pinnableItems(): array
    {
        $project = Project::query()->create([
            'title' => 'Projet favori',
            'slug' => 'projet-favori',
            'status' => 'idee',
            'priority' => 'normale',
        ]);

        return [
            'project' => $project,
            'note' => Note::query()->create([
                'project_id' => $project->id,
                'title' => 'Note favorite',
                'content' => 'Contenu',
                'type' => 'note_brute',
                'status' => 'brouillon',
            ]),
            'decision' => Decision::query()->create([
                'project_id' => $project->id,
                'title' => 'Decision favorite',
                'decision' => 'Decision',
                'status' => 'proposee',
            ]),
            'action' => Action::query()->create([
                'project_id' => $project->id,
                'title' => 'Action favorite',
                'description' => 'Action',
                'status' => 'a_faire',
                'priority' => 'normale',
            ]),
            'file' => StoredFile::query()->create([
                'project_id' => $project->id,
                'original_name' => 'favori.pdf',
                'stored_name' => 'favori.pdf',
                'path' => 'files/favori.pdf',
                'mime_type' => 'application/pdf',
                'extension' => 'pdf',
            ]),
        ];
    }

    private function user(): User
    {
        return User::query()->create([
            'name' => 'Test User',
            'email' => 'pin@example.test',
            'password' => Hash::make('password'),
        ]);
    }
}
