param(
    [string]$OutputRoot = "backups",
    [string]$AppService = "app",
    [string]$ComposeService = "postgres",
    [string]$Database = "second_cervel",
    [string]$Username = "second_cervel"
)

$ErrorActionPreference = "Stop"

$timestamp = Get-Date -Format "yyyyMMdd-HHmmss"
$backupDir = Join-Path $OutputRoot "backup-$timestamp"

New-Item -ItemType Directory -Path $backupDir -Force | Out-Null

Write-Host "Starting full MVP backup in: $backupDir"

& "$PSScriptRoot/backup-postgres.ps1" `
    -OutputDir $backupDir `
    -ComposeService $ComposeService `
    -Database $Database `
    -Username $Username

& "$PSScriptRoot/backup-uploads.ps1" `
    -OutputDir $backupDir `
    -AppService $AppService

Copy-Item -Path ".env.example" -Destination (Join-Path $backupDir ".env.example") -Force
Copy-Item -Path "docker-compose.yml" -Destination (Join-Path $backupDir "docker-compose.yml") -Force

if (Test-Path "docs/backup.md") {
    Copy-Item -Path "docs/backup.md" -Destination (Join-Path $backupDir "backup.md") -Force
}

$manifest = @"
Backup date: $(Get-Date -Format "yyyy-MM-dd HH:mm:ss")
Database: $Database
PostgreSQL service: $ComposeService
Includes:
- PostgreSQL dump
- Uploaded files archive
- .env.example
- docker-compose.yml
- restoration documentation when available

Meilisearch index is not included in the MVP backup. It is rebuildable from the database and uploaded files.
"@

$manifest | Out-File -FilePath (Join-Path $backupDir "MANIFEST.txt") -Encoding utf8

Write-Host "Full MVP backup complete."
Write-Host $backupDir
