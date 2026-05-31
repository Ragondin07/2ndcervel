param(
    [Parameter(Mandatory = $true)]
    [string]$DumpFile,
    [string]$ComposeService = "postgres",
    [string]$Database = "second_cervel",
    [string]$Username = "second_cervel"
)

$ErrorActionPreference = "Stop"

if (-not (Test-Path $DumpFile)) {
    throw "Dump file not found: $DumpFile"
}

Write-Host "Restoring PostgreSQL database '$Database' from: $DumpFile"
Write-Host "This command is intended for a test instance or an explicitly approved restore."

$containerDumpFile = "/tmp/restore-postgres.sql"
docker compose cp $DumpFile "${ComposeService}:${containerDumpFile}"
docker compose exec -T $ComposeService sh -lc "psql -U '$Username' -d '$Database' < '$containerDumpFile'"
docker compose exec -T $ComposeService sh -lc "rm -f '$containerDumpFile'"

Write-Host "PostgreSQL restore complete."
