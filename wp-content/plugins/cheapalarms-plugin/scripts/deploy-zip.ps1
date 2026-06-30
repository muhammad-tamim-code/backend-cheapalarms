# WordPress plugin upload zip — layout MUST be: cheapalarms-plugin/cheapalarms-plugin.php

$ErrorActionPreference = 'Stop'

$pluginRoot = Split-Path -Parent $PSScriptRoot
$pluginsDir = Split-Path -Parent $pluginRoot
$staging    = Join-Path $pluginsDir 'cheapalarms-plugin-zip-tmp'
$wrapDir    = Join-Path $staging 'cheapalarms-plugin'
$zipPath    = Join-Path $pluginRoot 'deploy\cheapalarms-plugin-wp-upload.zip'
$zipCopy    = Join-Path $pluginsDir 'cheapalarms-plugin.zip'

if (Test-Path $staging) {
    Remove-Item -Recurse -Force $staging
}

New-Item -ItemType Directory -Path $wrapDir -Force | Out-Null
New-Item -ItemType Directory -Path (Split-Path $zipPath) -Force | Out-Null

robocopy $pluginRoot $wrapDir /E `
    /XD node_modules cheapalarms-plugin-zip-tmp deploy scripts .git tests `
    /XF cheapalarms-plugin.zip *.log .gitignore `
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

Copy-Item -Force $zipPath $zipCopy

$sizeMb = [math]::Round((Get-Item $zipPath).Length / 1MB, 2)
Write-Host "OK - cheapalarms-plugin zip ready ($sizeMb MB)"
Write-Host "  $zipPath"
