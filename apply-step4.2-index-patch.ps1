$path = "resources\views\ai-analysis\index.blade.php"

if (-not (Test-Path $path)) {
    Write-Error "Cannot find $path. Run this script from the AgriFlow project root."
    exit 1
}

$backup = "$path.step42-backup"
Copy-Item $path $backup -Force

$content = Get-Content $path -Raw -Encoding UTF8
$startMarker = "{{-- Recommendations Footer --}}"
$endMarker = "            </div>`r`n        @endif"

if (-not $content.Contains($startMarker)) {
    $endMarker = "            </div>`n        @endif"
}

$start = $content.IndexOf($startMarker)
if ($start -lt 0) {
    Write-Error "Could not find the legacy Recommendations Footer marker. No changes were written."
    exit 1
}

$end = $content.IndexOf($endMarker, $start)
if ($end -lt 0) {
    Write-Error "Could not find the end of the analysis result card. No changes were written."
    exit 1
}

$replacement = "{{-- Step 4.2 Intervention Recommendations --}}`r`n                @include('ai-analysis.partials.intervention-recommendations')`r`n`r`n"
$content = $content.Substring(0, $start) + $replacement + $content.Substring($end)

$content = $content.Replace("AI Actionable Recommendations", "Recommended Operational Actions")
$content = $content.Replace("Recomendations", "Recommendations")

Set-Content $path $content -Encoding UTF8

Write-Host "Step 4.2 AI Analysis recommendation patch applied."
Write-Host "Backup created: $backup"
