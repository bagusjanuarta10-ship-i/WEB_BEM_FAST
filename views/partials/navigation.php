<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="views/partials/site.css?v=22">

<?php $navUser = Auth::currentUser(); ?>
<nav class="site-nav" aria-label="Navigasi utama">
    <a class="brand" href="index.php?action=tentang">BEM FST</a>
    <div class="nav-links">
        <a href="index.php?action=anggota">Anggota</a>
        <a href="index.php?action=kegiatan">Kegiatan</a>
        <a href="index.php?action=divisi">Divisi</a>
        <a href="index.php?action=jabatan">Jabatan</a>
        <a href="index.php?action=tentang">Tentang</a>
        <?php if ($navUser && $navUser['role'] === 'admin'): ?>
            <a href="index.php?action=users">Kelola Akun</a>
        <?php endif; ?>
    </div>
    <div class="nav-account">
        <?php if ($navUser): ?>
            <span class="nav-user"><?= htmlspecialchars($navUser['username'], ENT_QUOTES, 'UTF-8') ?></span>
            <a class="nav-password-link" href="index.php?action=change_password">Ganti Password</a>
            <form method="post" action="index.php?action=logout" class="nav-logout">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Auth::csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
                <button type="submit">Keluar</button>
            </form>
        <?php else: ?>
            <a class="nav-auth-link" href="index.php?action=login">
                <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                    <path d="M20 21a8 8 0 0 0-16 0"></path>
                    <circle cx="12" cy="8" r="4"></circle>
                </svg>
                <span>Login / Daftar</span>
            </a>
        <?php endif; ?>
    </div>
</nav>
