$path = "resources\views\ai-analysis\index.blade.php"

if (-not (Test-Path $path)) {
    Write-Error "Cannot find $path. Run this script from the AgriFlow project root."
    exit 1
}

$backup = "$path.step4-backup"
Copy-Item $path $backup -Force

$content = Get-Content $path -Raw -Encoding UTF8

$marker = "{{-- AI Explainability Drivers Section --}}"
$include = "@include('ai-analysis.partials.risk-engine-result')`r`n`r`n                "

if ($content.Contains($marker) -and -not $content.Contains("ai-analysis.partials.risk-engine-result")) {
    $content = $content.Replace($marker, $include + $marker)
}

$content = $content.Replace("AI Explainability", "Operational Risk Explainability")
$content = $content.Replace("Why did AI produce this prediction?", "Why is this shipment at this risk level?")
$content = $content.Replace("Decision Engine Core", "Step 4 Risk Engine")
$content = $content.Replace("Overall AI Confidence Index", "Weighted Risk Contribution Sum")
$content = $content.Replace("Driver Impact Coverage", "Weighted Risk Contribution Sum")
$content = $content.Replace("Predicted Spoilage Risk", "Operational Risk Trend")
$content = $content.Replace("Spoilage Risk (%)", "Operational Risk Index")
$content = $content.Replace("Waste Probability", "Operational Risk Index")

# Step 4 explainability impact is weighted contribution points, not percentages.
$content = $content.Replace("{{ `$driver['impact'] }}%", "{{ number_format(`$driver['impact'], 1) }} pts")
$content = $content.Replace("{{ min(100,`$totalImpact) }}%", "{{ number_format(`$totalImpact, 1) }} pts")

# Existing visual severity thresholds were designed for old heuristic percentages.
$content = $content.Replace("if(`$driver['impact'] >= 30){", "if(`$driver['impact'] >= 20){")
$content = $content.Replace("}elseif(`$driver['impact'] >= 20){", "}elseif(`$driver['impact'] >= 10){")

Set-Content $path $content -Encoding UTF8

Write-Host "Step 4 AI Analysis index patch applied."
Write-Host "Backup created: $backup"
