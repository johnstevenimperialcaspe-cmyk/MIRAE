<?php
if (!isset($pageTitle)) {
    $pageTitle = 'Admin';
}
if (!isset($assetBase)) {
    $assetBase = '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo htmlspecialchars($pageTitle); ?> - MIRAE Admin</title>
    <link rel="icon" type="image/png" href="<?php echo $assetBase; ?>../images/MD.png">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" />
    <link rel="stylesheet" href="<?php echo $assetBase; ?>assets/css/admin.css" />
    <link rel="stylesheet" href="<?php echo $assetBase; ?>../css/loader.css" />
</head>
<body class="admin-page">
<div class="preloader">
    <div class="loader"></div>
</div>
<div class="admin-layout">
