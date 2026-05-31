param(
    [string]$OutputDir = "backups",
    [string]$AppService = "app",
    [string]$UploadsPath = "/var/www/html/storage/app/uploads"
)

$ErrorActionPreference = "Stop"

$timestamp = Get-Date -Format "yyyyMMdd-HHmmss"
$resolvedOutputDir = Resolve-Path -Path $OutputDir -ErrorAction SilentlyContinue

if (-not $resolvedOutputDir) {
    New-Item -ItemType Directory -Path $OutputDir | Out-Null
    $resolvedOutputDir = Resolve-Path -Path $OutputDir
}

$stagingDir = Join-Path $resolvedOutputDir "uploads-staging-$timestamp"
$archiveFile = Join-Path $resolvedOutputDir "uploads-$timestamp.zip"

New-Item -ItemType Directory -Path $stagingDir -Force | Out-Null

Write-Host "Creating uploads archive: $archiveFile"
docker compose exec -T $AppService sh -lc "mkdir -p '$UploadsPath'"
docker compose cp "${AppService}:${UploadsPath}/." $stagingDir

$stagingContent = Join-Path $stagingDir "*"

if ((Get-ChildItem -Path $stagingDir -Force | Measure-Object).Count -eq 0) {
    $placeholder = Join-Path $stagingDir ".empty"
    "No uploaded files at backup time." | Out-File -FilePath $placeholder -Encoding utf8
}

Compress-Archive -Path $stagingContent -DestinationPath $archiveFile -Force
Remove-Item -Path $stagingDir -Recurse -Force

Write-Host "Uploads backup complete."
Write-Host $archiveFile
