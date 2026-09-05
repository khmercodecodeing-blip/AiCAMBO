<?php
if (!empty($_GET['navigation'])) {
    require dirname(__DIR__, 5) . '/app/views/layouts/header.php';
    echo '<main>';
    return;
}
?>
<!doctype html>
<html lang="km">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Purchase Delivery Test Preview</title>
    <link rel="stylesheet" href="/public/assets/css/style.css">
</head>
<body>
<div style="padding:12px;text-align:center;border-bottom:1px solid #ddd;">Test preview: synthetic purchase, no real payment</div>
<main>