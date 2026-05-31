param(
    [string]$OutputDir = "backups",
    [string]$ComposeService = "postgres",
    [string]$Database = "second_cervel",
    [string]$Username = "second_cervel"
)

$ErrorActionPreference = "Stop"

$timestamp = Get-Date -Format "yyyyMMdd-HHmmss"
$resolvedOutputDir = Resolve-Path -Path $OutputDir -ErrorAction SilentlyContinue

if (-not $resolvedOutputDir) {
    New-Item -ItemType Directory -Path $OutputDir | Out-Null
    $resolvedOutputDir = Resolve-Path -Path $OutputDir
}

$dumpFile = Join-Path $resolvedOutputDir "postgres-$timestamp.sql"
$containerDumpFile = "/tmp/postgres-$timestamp.sql"

Write-Host "Creating PostgreSQL dump: $dumpFile"
docker compose exec -T $ComposeService sh -lc "pg_dump -U '$Username' -d '$Database' --clean --if-exists --no-owner --no-privileges > '$containerDumpFile'"
docker compose cp "${ComposeService}:${containerDumpFile}" $dumpFile
docker compose exec -T $ComposeService sh -lc "rm -f '$containerDumpFile'"

Write-Host "PostgreSQL backup complete."
Write-Host $dumpFile
