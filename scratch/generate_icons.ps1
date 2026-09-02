Add-Type -AssemblyName System.Drawing

function New-AppIcon {
    param([int]$Size, [string]$Path, [int]$Radius, [bool]$Padded = $false)

    $bmp = New-Object System.Drawing.Bitmap $Size, $Size
    $g = [System.Drawing.Graphics]::FromImage($bmp)
    $g.SmoothingMode = [System.Drawing.Drawing2D.SmoothingMode]::AntiAlias
    $g.Clear([System.Drawing.Color]::Transparent)

    $inset = if ($Padded) { [int]($Size * 0.1) } else { 0 }
    $boxSize = $Size - ($inset * 2)
    $d = $Radius * 2

    $gp = New-Object System.Drawing.Drawing2D.GraphicsPath
    $gp.AddArc($inset, $inset, $d, $d, 180, 90)
    $gp.AddArc($inset + $boxSize - $d, $inset, $d, $d, 270, 90)
    $gp.AddArc($inset + $boxSize - $d, $inset + $boxSize - $d, $d, $d, 0, 90)
    $gp.AddArc($inset, $inset + $boxSize - $d, $d, $d, 90, 90)
    $gp.CloseFigure()

    $rect = New-Object System.Drawing.Rectangle $inset, $inset, $boxSize, $boxSize
    $brush = New-Object System.Drawing.Drawing2D.LinearGradientBrush($rect, [System.Drawing.Color]::FromArgb(255,59,130,246), [System.Drawing.Color]::FromArgb(255,6,182,212), 45)
    $g.FillPath($brush, $gp)

    $fontSize = [int]($boxSize * 0.52)
    $font = New-Object System.Drawing.Font("Arial", $fontSize, [System.Drawing.FontStyle]::Bold)
    $sf = New-Object System.Drawing.StringFormat
    $sf.Alignment = [System.Drawing.StringAlignment]::Center
    $sf.LineAlignment = [System.Drawing.StringAlignment]::Center
    $g.DrawString("C", $font, [System.Drawing.Brushes]::White, (New-Object System.Drawing.RectangleF($inset, $inset, $boxSize, $boxSize)), $sf)

    $bmp.Save($Path, [System.Drawing.Imaging.ImageFormat]::Png)
    $g.Dispose()
    $bmp.Dispose()
}

$dir = "public/assets/images/icons"
New-AppIcon -Size 512 -Path "$dir/icon-512.png" -Radius 96
New-AppIcon -Size 192 -Path "$dir/icon-192.png" -Radius 36
New-AppIcon -Size 180 -Path "$dir/apple-touch-icon.png" -Radius 34
New-AppIcon -Size 32  -Path "$dir/favicon-32.png" -Radius 6
New-AppIcon -Size 512 -Path "$dir/icon-512-maskable.png" -Radius 96 -Padded $true

"Icons generated in $dir"
