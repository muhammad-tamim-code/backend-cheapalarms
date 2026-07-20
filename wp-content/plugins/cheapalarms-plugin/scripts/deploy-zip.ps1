# WordPress plugin upload zip — layout MUST be: cheapalarms-plugin/cheapalarms-plugin.php
# Never ship secrets.php / instance.php in the upload zip.

$ErrorActionPreference = 'Stop'

$pluginRoot = Split-Path -Parent $PSScriptRoot
$pluginsDir = Split-Path -Parent $pluginRoot
$repoRoot   = (Resolve-Path (Join-Path $pluginRoot '..\..\..\..')).Path
$staging    = Join-Path $pluginsDir 'cheapalarms-plugin-zip-tmp'
$wrapDir    = Join-Path $staging 'cheapalarms-plugin'
$zipPath    = Join-Path $pluginRoot 'deploy\cheapalarms-plugin-wp-upload.zip'
$zipCopy    = Join-Path $pluginsDir 'cheapalarms-plugin.zip'
$zipCanonical = Join-Path $repoRoot 'cheapalarms-plugin.zip'

if (Test-Path $staging) {
    Remove-Item -Recurse -Force $staging
}

New-Item -ItemType Directory -Path $wrapDir -Force | Out-Null
New-Item -ItemType Directory -Path (Split-Path $zipPath) -Force | Out-Null

robocopy $pluginRoot $wrapDir /E `
    /XD node_modules cheapalarms-plugin-zip-tmp deploy scripts .git tests `
    /XF cheapalarms-plugin.zip *.log .gitignore secrets.php instance.php `
    /NFL /NDL /NJH /NJS /nc /ns /np

if ($LASTEXITCODE -ge 8) {
    throw "robocopy failed with exit code $LASTEXITCODE"
}

$mainFile = Join-Path $wrapDir 'cheapalarms-plugin.php'
if (-not (Test-Path $mainFile)) {
    throw "Missing cheapalarms-plugin.php in staging folder."
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
    throw "Zip verification failed: cheapalarms-plugin/cheapalarms-plugin.php not found at archive root."
}

$secretInZip = $entries | Where-Object { $_ -match 'secrets\.php$' -and $_ -notmatch 'example|template' }
if ($secretInZip) {
    throw "Zip contains secrets.php - refused."
}

Copy-Item -Force $zipPath $zipCopy
Copy-Item -Force $zipPath $zipCanonical

$sizeMb = [math]::Round((Get-Item $zipPath).Length / 1MB, 2)
Write-Host "OK - cheapalarms-plugin zip ready ($sizeMb MB)"
Write-Host "  Canonical (same every time): $zipCanonical"
Write-Host "  Also: $zipPath"
Write-Host "  Also: $zipCopy"
Write-Host "  Note: secrets.php and instance.php excluded - keep those on the server only."
