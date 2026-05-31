<?php

namespace Database\Seeders;

use App\Models\Action;
use App\Models\ActivityLog;
use App\Models\Decision;
use App\Models\File;
use App\Models\Note;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class MvpDemoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->where('role', 'admin')->first();

        $project = Project::query()->updateOrCreate(
            ['slug' => 'memoire-projet-privee'],
            [
                'title' => 'Memoire projet privee',
                'summary' => 'Socle MVP pour centraliser projets, notes, decisions, actions et fichiers.',
                'description' => 'Projet de reference cree par le seeder pour valider le modele de donnees MVP.',
                'status' => 'en_cours',
                'priority' => 'haute',
                'category' => 'application_web',
            ],
        );

        $inboxProject = Project::query()->updateOrCreate(
            ['slug' => 'bac-a-idees'],
            [
                'title' => 'Bac a idees',
                'summary' => 'Projet de test pour les notes a explorer.',
                'description' => 'Espace de demonstration pour verifier les relations Eloquent.',
                'status' => 'a_explorer',
                'priority' => 'normale',
                'category' => 'inbox',
            ],
        );

        $note = Note::query()->updateOrCreate(
            ['title' => 'Synthese MVP initiale', 'project_id' => $project->id],
            [
                'content' => 'Le MVP doit commencer par authentification, projets, notes, decisions, actions, fichiers et recherche simple.',
                'type' => 'synthese_ia',
                'status' => 'active',
                'source_type' => 'manuel',
                'source_detail' => 'Donnees de demonstration.',
            ],
        );

        $idea = Note::query()->updateOrCreate(
            ['title' => 'Idee a trier', 'project_id' => $inboxProject->id],
            [
                'content' => 'Exemple de note brute pour verifier les filtres par projet et type.',
                'type' => 'idee',
                'status' => 'brouillon',
                'source_type' => 'manuel',
            ],
        );

        $decision = Decision::query()->updateOrCreate(
            ['title' => 'Commencer sans IA locale', 'project_id' => $project->id],
            [
                'source_note_id' => $note->id,
                'decision' => 'Construire d abord le socle applicatif et la recherche avant tout module IA.',
                'justification' => 'Le cahier des charges donne la priorite a une memoire fiable, auto-hebergeable et recherchable.',
                'alternatives' => 'Demarrer par une IA locale, un OCR avance ou une base vectorielle.',
                'risks' => 'Risque de surcomplexite si les modules avances arrivent trop tot.',
                'impact' => 'Le projet reste livrable par petits lots testables.',
                'status' => 'validee',
            ],
        );

        Action::query()->updateOrCreate(
            ['title' => 'Creer les migrations MVP', 'project_id' => $project->id],
            [
                'note_id' => $note->id,
                'decision_id' => $decision->id,
                'description' => 'Ajouter les tables principales et leurs relations Eloquent.',
                'status' => 'faite',
                'priority' => 'haute',
                'completed_at' => now(),
            ],
        );

        Action::query()->updateOrCreate(
            ['title' => 'Preparer la recherche simple', 'project_id' => $project->id],
            [
                'note_id' => $note->id,
                'description' => 'Lot suivant possible apres validation du modele MVP.',
                'status' => 'a_faire',
                'priority' => 'normale',
            ],
        );

        $demoPath = 'projects/demo/demo-placeholder.txt';
        $demoContent = 'Fichier de demonstration pour verifier le telechargement securise du MVP.';

        Storage::disk('uploads')->put($demoPath, $demoContent);

        File::query()->updateOrCreate(
            ['stored_name' => 'demo-placeholder.txt'],
            [
                'project_id' => $project->id,
                'note_id' => $note->id,
                'original_name' => 'demo-placeholder.txt',
                'path' => $demoPath,
                'mime_type' => 'text/plain',
                'extension' => 'txt',
                'size' => strlen($demoContent),
                'hash' => hash('sha256', $demoContent),
                'description' => 'Entree fichier de demonstration avec fichier physique associe.',
                'extracted_text' => 'Exemple de contenu extrait pour les futurs tests de recherche.',
                'indexing_status' => 'en_attente',
                'extraction_status' => 'en_attente',
                'ocr_status' => 'non_supporte',
            ],
        );

        ActivityLog::query()->firstOrCreate(
            [
                'action' => 'demo_seeded',
                'entity_type' => Project::class,
                'entity_id' => $project->id,
            ],
            [
                'user_id' => $admin?->id,
                'metadata' => ['scope' => 'mvp'],
            ],
        );

        ActivityLog::query()->firstOrCreate(
            [
                'action' => 'demo_seeded',
                'entity_type' => Note::class,
                'entity_id' => $idea->id,
            ],
            [
                'user_id' => $admin?->id,
                'metadata' => ['scope' => 'mvp'],
            ],
        );
    }
}
