# WordPress plugin upload zip - layout MUST be: cheapalarms-plugin/cheapalarms-plugin.php
#
# Staging workflow: include config/secrets.php so WP Admin upload does not wipe keys.
# secrets.php stays gitignored; do not commit or share the zip.

$ErrorActionPreference = 'Stop'

$pluginRoot = Split-Path -Parent $PSScriptRoot
$pluginsDir = Split-Path -Parent $pluginRoot
$repoRoot   = (Resolve-Path (Join-Path $pluginRoot '..\..\..\..')).Path
$staging    = Join-Path $pluginsDir 'cheapalarms-plugin-zip-tmp'
$wrapDir    = Join-Path $staging 'cheapalarms-plugin'
$zipPath    = Join-Path $pluginRoot 'deploy\cheapalarms-plugin-wp-upload.zip'
$zipCopy    = Join-Path $pluginsDir 'cheapalarms-plugin.zip'
$zipCanonical = Join-Path $repoRoot 'cheapalarms-plugin.zip'
$secretsSrc = Join-Path $pluginRoot 'config\secrets.php'

if (-not (Test-Path $secretsSrc)) {
    throw 'Missing config/secrets.php - create it before building the deploy zip.'
}

if (Test-Path $staging) {
    Remove-Item -Recurse -Force $staging
}

New-Item -ItemType Directory -Path $wrapDir -Force | Out-Null
New-Item -ItemType Directory -Path (Split-Path $zipPath) -Force | Out-Null

robocopy $pluginRoot $wrapDir /E `
    /XD node_modules cheapalarms-plugin-zip-tmp deploy scripts .git tests `
    /XF cheapalarms-plugin.zip *.log .gitignore instance.php `
    /NFL /NDL /NJH /NJS /nc /ns /np

if ($LASTEXITCODE -ge 8) {
    throw "robocopy failed with exit code $LASTEXITCODE"
}

$mainFile = Join-Path $wrapDir 'cheapalarms-plugin.php'
if (-not (Test-Path $mainFile)) {
    throw 'Missing cheapalarms-plugin.php in staging folder.'
}

$secretsInStaging = Join-Path $wrapDir 'config\secrets.php'
if (-not (Test-Path $secretsInStaging)) {
    throw 'secrets.php was not copied into the zip staging folder.'
}

if (Test-Path $zipPath) {
    Remove-Item -Force $zipPath
}

Push-Location $staging
try {
    tar -a -cf $zipPath cheapalarms-plugin
} finally {
    Pop-Location
}

Remove-Item -Recurse -Force $staging

$entries = tar -tf $zipPath
$mainInZip = $entries | Where-Object { $_ -eq 'cheapalarms-plugin/cheapalarms-plugin.php' }
if (-not $mainInZip) {
    throw 'Zip verification failed: cheapalarms-plugin/cheapalarms-plugin.php not found at archive root.'
}

$secretInZip = $entries | Where-Object { $_ -eq 'cheapalarms-plugin/config/secrets.php' }
if (-not $secretInZip) {
    throw 'Zip verification failed: config/secrets.php missing from archive.'
}

Copy-Item -Force $zipPath $zipCopy
Copy-Item -Force $zipPath $zipCanonical

$sizeMb = [math]::Round((Get-Item $zipPath).Length / 1MB, 2)
Write-Host ("OK - cheapalarms-plugin zip ready ({0} MB)" -f $sizeMb)
Write-Host "  Canonical: $zipCanonical"
Write-Host "  Also: $zipPath"
Write-Host "  Also: $zipCopy"
Write-Host '  Includes: config/secrets.php (do not commit or share this zip)'
