<?php
// views/partials/404.php
$pageTitle = '404 — Page non trouvée';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $pageTitle ?></title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/css/app.css">
</head>
<body>
  <div style="display:flex; align-items:center; justify-content:center; min-height:100vh; flex-direction:column; text-align:center;">
    <div style="font-size:64px; margin-bottom:20px;">🔍</div>
    <h1 style="font-size:32px; margin-bottom:10px;">404 — Page non trouvée</h1>
    <p style="color:var(--text-secondary); margin-bottom:30px;">La page que vous cherchez n'existe pas ou a été supprimée.</p>
    <a href="<?= BASE_URL ?>/dashboard" class="btn btn-primary" style="margin-top:20px;">
      ← Retourner au dashboard
    </a>
  </div>
</body>
</html>
