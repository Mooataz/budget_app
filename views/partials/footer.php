<?php // views/partials/footer.php ?>
</main><!-- /.main-content -->

<!-- Charger app.js EN PREMIER -->
<script src="<?= BASE_URL ?>/js/app.js"></script>

<!-- Variables globales - UNIQUEMENT si pas déjà déclarées -->
<?php if (Session::isLogged()): ?>
<script>
  // Déclarer SEULEMENT si pas déjà défini
  if (typeof BASE_URL === 'undefined') {
    const BASE_URL = '<?= BASE_URL ?>';
  }
  if (typeof CSRF_TOKEN === 'undefined') {
    const CSRF_TOKEN = '<?= Session::generateCsrf() ?>';
  }
  
  // Vérifier que app.js est chargé
  if (typeof openModal === 'function') {
    console.log('✓ app.js loaded correctly');
  } else {
    console.error('✗ openModal is not defined');
  }
</script>
<?php endif; ?>

<!-- Lucide Icons -->
<script>
  if (typeof lucide !== 'undefined') {
    lucide.createIcons();
  }
</script>

</body>
</html>