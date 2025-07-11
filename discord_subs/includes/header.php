<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' : '' ?>Discord Subs</title>
    
    <link rel="icon" type="image/png" href="/assets/images/logo.png">

    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="/assets/css/style.css">
    
    <?php if(isset($extraStyles)): echo $extraStyles; endif; ?>
</head>