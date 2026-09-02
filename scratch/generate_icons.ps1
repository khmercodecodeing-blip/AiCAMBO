Add-Type -AssemblyName System.Drawing

$srcPath = "Icon/lOGO.png"
$src = New-Object System.Drawing.Bitmap $srcPath

# Tight square crop around just the circular "Ai" mark (excludes "AI CAMBO" text + temple silhouette below)
$cropRect = New-Object System.Drawing.Rectangle 203, 42, 820, 820
$iconCrop = New-Object System.Drawing.Bitmap 820, 820
$gCrop = [System.Drawing.Graphics]::FromImage($iconCrop)
$gCrop.DrawImage($src, (New-Object System.Drawing.Rectangle 0, 0, 820, 820), $cropRect, [System.Drawing.GraphicsUnit]::Pixel)
$gCrop.Dispose()
$src.Dispose()

function New-RoundedIcon {
    param([System.Drawing.Bitmap]$Source, [int]$Size, [string]$Path, [int]$Radius)

    $out = New-Object System.Drawing.Bitmap $Size, $Size
    $g = [System.Drawing.Graphics]::FromImage($out)
    $g.SmoothingMode = [System.Drawing.Drawing2D.SmoothingMode]::AntiAlias
    $g.InterpolationMode = [System.Drawing.Drawing2D.InterpolationMode]::HighQualityBicubic
    $g.Clear([System.Drawing.Color]::Transparent)

    $d = $Radius * 2
    $gp = New-Object System.Drawing.Drawing2D.GraphicsPath
    if ($Radius -le 0) {
        $gp.AddRectangle((New-Object System.Drawing.Rectangle 0, 0, $Size, $Size))
    } else {
        $gp.AddArc(0, 0, $d, $d, 180, 90)
        $gp.AddArc($Size - $d, 0, $d, $d, 270, 90)
        $gp.AddArc($Size - $d, $Size - $d, $d, $d, 0, 90)
        $gp.AddArc(0, $Size - $d, $d, $d, 90, 90)
        $gp.CloseFigure()
    }

    $g.SetClip($gp)
    $g.FillRectangle([System.Drawing.Brushes]::White, 0, 0, $Size, $Size)
    $g.DrawImage($Source, 0, 0, $Size, $Size)
    $g.ResetClip()

    $out.Save($Path, [System.Drawing.Imaging.ImageFormat]::Png)
    $g.Dispose()
    $out.Dispose()
}

$dir = "public/assets/images/icons"
New-RoundedIcon -Source $iconCrop -Size 512 -Path "$dir/icon-512.png" -Radius 96
New-RoundedIcon -Source $iconCrop -Size 192 -Path "$dir/icon-192.png" -Radius 36
New-RoundedIcon -Source $iconCrop -Size 180 -Path "$dir/apple-touch-icon.png" -Radius 34
New-RoundedIcon -Source $iconCrop -Size 32  -Path "$dir/favicon-32.png" -Radius 6
New-RoundedIcon -Source $iconCrop -Size 512 -Path "$dir/icon-512-maskable.png" -Radius 0

# Small badge for the navbar brand (next to site name)
New-RoundedIcon -Source $iconCrop -Size 96 -Path "$dir/logo-navbar.png" -Radius 20

$iconCrop.Dispose()
"Icons generated in $dir"
