Add-Type -AssemblyName System.Drawing

$src = "Icon/lOGO.png"
$bmp = New-Object System.Drawing.Bitmap $src
$w = $bmp.Width
$threshold = 245
$yStart = 50
$yEnd = 855

$minX = $w
$maxX = 0
for ($x = 0; $x -lt $w; $x += 2) {
    $has = $false
    for ($y = $yStart; $y -lt $yEnd; $y += 4) {
        $p = $bmp.GetPixel($x, $y)
        if ($p.R -lt $threshold -or $p.G -lt $threshold -or $p.B -lt $threshold) { $has = $true; break }
    }
    if ($has) {
        if ($x -lt $minX) { $minX = $x }
        if ($x -gt $maxX) { $maxX = $x }
    }
}
"minX=$minX maxX=$maxX"
$bmp.Dispose()
