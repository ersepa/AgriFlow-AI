$path = "resources\views\ai-analysis\index.blade.php"

if (-not (Test-Path $path)) {
    Write-Error "File not found: $path. Run this script from the AgriFlow project root."
    exit 1
}

$backup = "$path.step32-backup"
Copy-Item $path $backup -Force

$content = Get-Content $path -Raw

$replacements = @(
    @('Remaining Days', 'Recorded Time Remaining'),
    @('Waste Probability', 'Operational Risk Index'),
    @('Predicted Spoilage Curve', 'Operational Risk Trend'),
    @('Estimated degradation rate over remaining shelf-life days.', 'Projected operational risk index under current shipment conditions.'),
    @("label: 'Spoilage Risk (%)'", "label: 'Operational Risk Index'"),
    @('Overall AI Confidence Index', 'Driver Impact Coverage'),
    @('This factor contributed the most to the overall AI prediction model.', 'This factor contributes the most to the current operational risk model.'),
    @('<p class="text-indigo-400 text-xs font-black uppercase tracking-widest mt-1">HIGH PRIORITY</p>', '<p class="text-indigo-400 text-xs font-black uppercase tracking-widest mt-1">{{ session(''priority_score'', 0) >= 80 ? ''CRITICAL PRIORITY'' : (session(''priority_score'', 0) >= 60 ? ''HIGH PRIORITY'' : (session(''priority_score'', 0) >= 40 ? ''MEDIUM PRIORITY'' : ''LOW PRIORITY'')) }}</p>'),
    @('<p class="text-lg font-black text-emerald-400 mt-1">Dispatch Now</p>', '<p class="text-lg font-black text-emerald-400 mt-1">{{ session(''recommended_action'', ''Review shipment'') }}</p>')
)

foreach ($pair in $replacements) {
    $content = $content.Replace($pair[0], $pair[1])
}

$content = $content.Replace(
    "{{ `$shipmentData['remaining_days'] }} Days",
    "{{ number_format(`$shipmentData['remaining_days'], 2) }} Days"
)

Set-Content -Path $path -Value $content -Encoding UTF8

Write-Host "Step 3.2 AI Analysis index cleanup applied."
Write-Host "Backup: $backup"
