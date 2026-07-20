# Fetch portfolio logos into assets/images/portfolio/
# Run: powershell -NoProfile -ExecutionPolicy Bypass -File scripts/fetch-portfolio-logos.ps1

$ErrorActionPreference = 'SilentlyContinue'
$root = Split-Path -Parent (Split-Path -Parent $PSScriptRoot)
$dir  = Join-Path $root 'assets\images\portfolio'
New-Item -ItemType Directory -Force -Path $dir | Out-Null

$brands = [ordered]@{
    'woolworths'          = 'woolworths.com.au'
    'kfc'                 = 'kfc.com.au'
    'australia-post'      = 'auspost.com.au'
    'nab'                 = 'nab.com.au'
    'rathdrum-properties' = 'rathdrum.com.au'
    'sbm'                 = 'sbm.com.au'
    'storageplus'         = 'storageplus.com.au'
    'freechoice'          = 'freechoice.com.au'
    'timezone'            = 'timezonegames.com'
    'zone-bowling'        = 'zonebowling.com'
    'kingpin'             = 'kingpinbowling.com.au'
    'jas-forwarding'      = 'jas.com'
}

foreach ($entry in $brands.GetEnumerator()) {
    $slug   = $entry.Key
    $domain = $entry.Value
    $out    = Join-Path $dir "$slug.png"
    $ok     = $false
    foreach ($url in @(
            "https://logo.clearbit.com/$domain",
            "https://www.google.com/s2/favicons?domain=$domain&sz=128"
        )) {
        curl.exe -fsSL -A 'Mozilla/5.0' -o $out $url 2>$null
        if ((Test-Path $out) -and ((Get-Item $out).Length -gt 500)) {
            Write-Host "OK  $slug ($domain)"
            $ok = $true
            break
        }
        Remove-Item $out -Force -ErrorAction SilentlyContinue
    }
    if (-not $ok) { Write-Host "MISSING $slug ($domain)" }
}

# Specific Freight (WordPress asset on live site)
$sf = Join-Path $dir 'specific-freight.png'
curl.exe -fsSL -L -o $sf 'https://specificfreight.com.au/wp-content/uploads/2020/01/logo.png' 2>$null
if ((Test-Path $sf) -and ((Get-Item $sf).Length -gt 400)) {
    Write-Host 'OK  specific-freight (direct URL)'
} else {
    Write-Host 'MISSING specific-freight'
}

Write-Host "Done. Output: $dir"
