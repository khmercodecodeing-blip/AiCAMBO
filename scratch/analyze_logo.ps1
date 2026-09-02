Add-Type -AssemblyName System.Drawing

$src = "Icon/lOGO.png"
$bmp = New-Object System.Drawing.Bitmap $src
$w = $bmp.Width
$h = $bmp.Height

function Test-RowHasContent($bmp, $y, $w, $threshold) {
    for ($x = 0; $x -lt $w; $x += 3) {
        $p = $bmp.GetPixel($x, $y)
        if ($p.R -lt $threshold -or $p.G -lt $threshold -or $p.B -lt $threshold) { return $true }
    }
    return $false
}

$threshold = 245
$rows = @()
for ($y = 0; $y -lt $h; $y += 4) {
    $has = Test-RowHasContent $bmp $y $w $threshold
    $rows += [PSCustomObject]@{ Y = $y; Has = $has }
}

# Find gaps (transitions from content to blank) to locate the boundary between icon and text
$prev = $false
foreach ($r in $rows) {
    if ($r.Has -ne $prev) {
        Write-Output "Transition at y=$($r.Y): now $($r.Has)"
        $prev = $r.Has
    }
}
$bmp.Dispose()
