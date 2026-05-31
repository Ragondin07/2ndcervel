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
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InboxTest extends TestCase
{
    use RefreshDatabase;

    public function test_inbox_lists_all_unclassified_content_types(): void
    {
        $this->actingAs($this->user());

        Note::query()->create([
            'title' => 'Note sans projet',
            'content' => 'Contenu a trier',
            'type' => 'note_brute',
            'status' => 'brouillon',
        ]);
        Decision::query()->create([
            'title' => 'Decision sans projet',
            'decision' => 'Decision a classer',
            'status' => 'proposee',
        ]);
        Action::query()->create([
            'title' => 'Action sans projet',
            'description' => 'Action a classer',
            'status' => 'a_faire',
            'priority' => 'normale',
        ]);
        StoredFile::query()->create([
            'original_name' => 'inbox.pdf',
            'stored_name' => 'inbox.pdf',
            'path' => 'inbox/inbox.pdf',
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
        ]);

        $this->get('/inbox')
            ->assertOk()
            ->assertSee('Note sans projet')
            ->assertSee('Decision sans projet')
            ->assertSee('Action sans projet')
            ->assertSee('inbox.pdf')
            ->assertSee('Recemment ajoutes sans classement');
    }

    public function test_assigning_project_removes_note_from_inbox(): void
    {
        $this->actingAs($this->user());
        $project = Project::query()->create([
            'title' => 'Projet cible',
            'slug' => 'projet-cible',
            'status' => 'en_cours',
            'priority' => 'normale',
        ]);
        $note = Note::query()->create([
            'title' => 'Note a classer',
            'content' => 'Contenu',
            'type' => 'note_brute',
            'status' => 'brouillon',
        ]);

        $this->patch(route('inbox.assign-project', ['type' => 'note', 'id' => $note->id]), [
            'project_id' => $project->id,
        ])->assertRedirect();

        $this->assertSame($project->id, $note->fresh()->project_id);
        $this->get('/inbox')->assertOk()->assertDontSee('Note a classer');
    }

    public function test_note_can_be_retyped_and_converted_to_decision(): void
    {
        $this->actingAs($this->user());
        $note = Note::query()->create([
            'title' => 'Arbitrage technique',
            'content' => 'Choisir PostgreSQL.',
            'type' => 'note_brute',
            'status' => 'brouillon',
        ]);

        $this->patch(route('inbox.notes.type', $note), [
            'type' => 'hypothese',
        ])->assertRedirect();

        $this->assertSame('hypothese', $note->fresh()->type);

        $this->post(route('inbox.notes.convert-decision', $note))->assertRedirect(route('inbox'));

        $this->assertDatabaseHas('decisions', [
            'source_note_id' => $note->id,
            'title' => 'Arbitrage technique',
            'decision' => 'Choisir PostgreSQL.',
            'status' => 'proposee',
        ]);
        $this->assertNotNull($note->fresh()->archived_at);
    }

    public function test_note_can_be_converted_to_action_and_items_can_be_archived_or_deleted(): void
    {
        $this->actingAs($this->user());
        $note = Note::query()->create([
            'title' => 'Faire le tri',
            'content' => 'Classer les notes restantes.',
            'type' => 'note_brute',
            'status' => 'brouillon',
        ]);
        $action = Action::query()->create([
            'title' => 'Action a archiver',
            'description' => 'Ancienne action',
            'status' => 'a_faire',
            'priority' => 'normale',
        ]);
        Storage::fake('uploads');
        Storage::disk('uploads')->put('inbox/remove.txt', 'demo');
        $file = StoredFile::query()->create([
            'original_name' => 'remove.txt',
            'stored_name' => 'remove.txt',
            'path' => 'inbox/remove.txt',
            'mime_type' => 'text/plain',
            'extension' => 'txt',
        ]);

        $this->post(route('inbox.notes.convert-action', $note))->assertRedirect(route('inbox'));
        $this->assertDatabaseHas('actions', [
            'note_id' => $note->id,
            'title' => 'Faire le tri',
            'status' => 'a_faire',
        ]);
        $this->assertNotNull($note->fresh()->archived_at);

        $this->patch(route('inbox.archive', ['type' => 'action', 'id' => $action->id]))->assertRedirect();
        $this->assertSame('abandonnee', $action->fresh()->status);
        $this->assertNotNull($action->fresh()->archived_at);

        $this->delete(route('inbox.destroy', ['type' => 'file', 'id' => $file->id]))->assertRedirect();
        $this->assertDatabaseMissing('files', ['id' => $file->id]);
        Storage::disk('uploads')->assertMissing('inbox/remove.txt');
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
