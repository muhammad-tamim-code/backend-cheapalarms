# WordPress plugin upload zip — layout MUST be: site-blocks/site-blocks.php
# Do NOT zip the inner files only (that breaks WP and causes "Plugin file does not exist").

$ErrorActionPreference = 'Stop'

$pluginRoot = Split-Path -Parent $PSScriptRoot
$pluginsDir = Split-Path -Parent $pluginRoot
$staging    = Join-Path $pluginsDir 'site-blocks-zip-tmp'
$wrapDir    = Join-Path $staging 'site-blocks'
$zipPath    = Join-Path $pluginRoot 'deploy\site-blocks-wp-upload.zip'
$zipCopy    = Join-Path $pluginsDir 'site-blocks.zip'

if (Test-Path $staging) {
    Remove-Item -Recurse -Force $staging
}

New-Item -ItemType Directory -Path $wrapDir -Force | Out-Null
New-Item -ItemType Directory -Path (Split-Path $zipPath) -Force | Out-Null

robocopy $pluginRoot $wrapDir /E `
    /XD node_modules site-blocks-zip-tmp site-blocks-deploy deploy scripts src `
    /XF site-blocks.zip *.log package.json package-lock.json postcss.config.js tailwind.config.js .gitignore `
    /NFL /NDL /NJH /NJS /nc /ns /np

if ($LASTEXITCODE -ge 8) {
    throw "robocopy failed with exit code $LASTEXITCODE"
}

$mainFile = Join-Path $wrapDir 'site-blocks.php'
if (-not (Test-Path $mainFile)) {
    throw "Missing site-blocks.php in staging folder - zip would be invalid for WordPress."
}

if (Test-Path $zipPath) {
    Remove-Item -Force $zipPath
}

Push-Location $staging
try {
    tar -a -cf $zipPath site-blocks
} finally {
    Pop-Location
}

Remove-Item -Recurse -Force $staging

$entries = tar -tf $zipPath
$mainInZip = $entries | Where-Object { $_ -eq 'site-blocks/site-blocks.php' }
if (-not $mainInZip) {
    throw "Zip verification failed: site-blocks/site-blocks.php not found at archive root."
}

$nested = $entries | Where-Object { $_ -match '^site-blocks/site-blocks/' }
if ($nested) {
    throw "Zip verification failed: double-nested site-blocks/site-blocks/ detected."
}

Copy-Item -Force $zipPath $zipCopy

$sizeMb = [math]::Round((Get-Item $zipPath).Length / 1MB, 2)
Write-Host ""
Write-Host "OK - WordPress plugin zip ready ($sizeMb MB)" -ForegroundColor Green
Write-Host "  $zipPath"
