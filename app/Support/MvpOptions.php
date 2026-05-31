<?php

namespace App\Support;

class MvpOptions
{
    public const PROJECT_STATUSES = [
        'idee' => 'idee',
        'a_explorer' => 'a explorer',
        'en_cours' => 'en cours',
        'en_pause' => 'en pause',
        'valide' => 'valide',
        'abandonne' => 'abandonne',
        'termine' => 'termine',
        'archive' => 'archive',
    ];

    public const PRIORITIES = [
        'basse' => 'basse',
        'normale' => 'normale',
        'haute' => 'haute',
        'critique' => 'critique',
    ];

    public const NOTE_TYPES = [
        'idee' => 'idee',
        'brouillon' => 'brouillon',
        'synthese_ia' => 'synthese IA',
        'reflexion' => 'reflexion',
        'compte_rendu' => 'compte rendu',
        'procedure' => 'procedure',
        'hypothese' => 'hypothese',
        'probleme' => 'probleme',
        'solution' => 'solution',
        'documentation' => 'documentation',
        'recherche' => 'recherche',
        'retour_experience' => 'retour d experience',
        'note_brute' => 'note brute',
    ];

    public const NOTE_STATUSES = [
        'brouillon' => 'brouillon',
        'active' => 'active',
        'archivee' => 'archivee',
    ];

    public const DECISION_STATUSES = [
        'proposee' => 'proposee',
        'validee' => 'validee',
        'annulee' => 'annulee',
        'remplacee' => 'remplacee',
        'a_revoir' => 'a revoir',
    ];

    public const ACTION_STATUSES = [
        'a_faire' => 'a faire',
        'en_cours' => 'en cours',
        'bloquee' => 'bloquee',
        'faite' => 'faite',
        'abandonnee' => 'abandonnee',
    ];

    public const QUICK_ADD_TYPES = [
        'note' => 'note',
        'decision' => 'decision',
        'action' => 'action',
    ];

    public const FILE_INDEXING_STATUSES = [
        'en_attente',
        'en_cours',
        'indexe',
        'erreur',
        'non_supporte',
    ];

    public const FILE_EXTRACTION_STATUSES = [
        'en_attente',
        'en_cours',
        'extrait',
        'erreur',
        'non_supporte',
        'non_traite',
    ];

    public const ALLOWED_FILE_EXTENSIONS = [
        'pdf',
        'jpg',
        'jpeg',
        'png',
        'webp',
        'txt',
        'md',
        'markdown',
        'docx',
        'xlsx',
        'ods',
        'csv',
        'zip',
        'drawio',
        'excalidraw',
        'fcstd',
        'py',
        'ps1',
        'sh',
        'js',
        'ts',
        'php',
        'css',
        'html',
        'json',
        'xml',
        'yml',
        'yaml',
    ];
}
