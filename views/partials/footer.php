<?php // views/partials/footer.php ?>
</main><!-- /.main-content -->

<script src="<?= BASE_URL ?>/js/app.js"></script>

<?php if (Session::isLogged()): ?>
<script>
  if (typeof BASE_URL === 'undefined') { const BASE_URL = '<?= BASE_URL ?>'; }
  if (typeof CSRF_TOKEN === 'undefined') { const CSRF_TOKEN = '<?= Session::generateCsrf() ?>'; }
  if (typeof BUDGET_ID === 'undefined' && typeof budgetId !== 'undefined') { const BUDGET_ID = budgetId; }
</script>
<?php endif; ?>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    if (typeof lucide !== 'undefined') lucide.createIcons();
  });
</script>

</body>
</html>