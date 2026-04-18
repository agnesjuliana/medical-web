<?php
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../components/components.php';

requireLogin();
startSession();

$user = getCurrentUser();
$pageTitle = 'Modul 10';

// koneksi database (kalau sudah ada)
//require_once __DIR__ . '/../../config_modul10.php';

$username = $user['username'];
?>

<?php require_once __DIR__ . '/../../layout/header.php'; ?>
<?php require_once __DIR__ . '/../../layout/navbar.php'; ?>

<!-- kirim username ke HTML -->
<script>
    const username = "<?= $username ?>";
</script>

<main>

<?php include 'index.html'; ?>

</main>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>