<?php
require __DIR__ . '/../partials/navigation.php';
$escape = static function ($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
$isRegistration = $mode === 'register';
?>

<main class="page">
    <section class="content-card auth-card">
        <p class="eyebrow">Akun BEM FST</p>
        <h1><?= $isRegistration ? 'Registrasi' : 'Login' ?></h1>
        <p><?= $isRegistration
            ? 'Buat akun baru. Akun registrasi memiliki akses pengguna dan tidak dapat mengelola data.'
            : 'Masuk untuk mengakses fitur sesuai peran akun Anda.' ?></p>

        <?php if (!Auth::hasAdministrator()): ?>
            <div class="alert" role="status">
                Akun admin awal belum tersedia. Atur BEM_ADMIN_USERNAME dan BEM_ADMIN_PASSWORD pada konfigurasi environment aplikasi.
            </div>
        <?php endif; ?>

        <?php if ($errors): ?>
            <div class="alert" role="alert">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= $escape($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" action="index.php?action=<?= $isRegistration ? 'register' : 'login' ?>">
            <input type="hidden" name="csrf_token" value="<?= $escape(Auth::csrfToken()) ?>">
            <p>
                <label for="username">Nama pengguna</label><br>
                <input id="username" name="username" type="text" minlength="3" maxlength="50"
                    pattern="[a-zA-Z0-9._-]+" autocomplete="username"
                    value="<?= $escape($username) ?>" required>
            </p>
            <div class="password-field">
                <label for="password">Kata sandi</label><br>
                <div class="password-input-row">
                    <input id="password" name="password" type="password" minlength="12"
                        autocomplete="<?= $isRegistration ? 'new-password' : 'current-password' ?>" required>
                    <button class="password-toggle" type="button" data-password-target="password"
                        aria-label="Lihat kata sandi" title="Lihat kata sandi" aria-pressed="false">
                        <svg class="eye-open" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7Z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                        <svg class="eye-closed" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path d="m3 3 18 18"></path>
                            <path d="M10.6 10.6a2 2 0 0 0 2.8 2.8"></path>
                            <path d="M9.9 5.2A10.8 10.8 0 0 1 12 5c6.4 0 10 7 10 7a15.8 15.8 0 0 1-3 3.8"></path>
                            <path d="M6.2 6.2C3.5 8 2 12 2 12s3.6 7 10 7c1 0 2-.2 2.8-.5"></path>
                        </svg>
                    </button>
                </div>
                <?php if ($isRegistration): ?>
                    <small>Minimal 12 karakter.</small>
                <?php endif; ?>
            </div>
            <?php if ($isRegistration): ?>
                <p>
                    <label for="confirm_password">Konfirmasi kata sandi</label><br>
                    <span class="password-input-row">
                        <input id="confirm_password" name="confirm_password" type="password"
                            minlength="12" autocomplete="new-password" required>
                        <button class="password-toggle" type="button" data-password-target="confirm_password"
                            aria-label="Lihat konfirmasi kata sandi" title="Lihat kata sandi" aria-pressed="false">
                            <svg class="eye-open" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7Z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                            <svg class="eye-closed" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                <path d="m3 3 18 18"></path>
                                <path d="M10.6 10.6a2 2 0 0 0 2.8 2.8"></path>
                                <path d="M9.9 5.2A10.8 10.8 0 0 1 12 5c6.4 0 10 7 10 7a15.8 15.8 0 0 1-3 3.8"></path>
                                <path d="M6.2 6.2C3.5 8 2 12 2 12s3.6 7 10 7c1 0 2-.2 2.8-.5"></path>
                            </svg>
                        </button>
                    </span>
                </p>
            <?php endif; ?>
            <button type="submit"><?= $isRegistration ? 'Daftar' : 'Login' ?></button>
        </form>

        <p>
            <?= $isRegistration ? 'Sudah punya akun?' : 'Belum punya akun?' ?>
            <a href="index.php?action=<?= $isRegistration ? 'login' : 'register' ?>">
                <?= $isRegistration ? 'Login' : 'Registrasi' ?>
            </a>
        </p>
    </section>
</main>
<?php require __DIR__ . '/../partials/footer.php'; ?>
<script src="views/partials/password-toggle.js?v=1" defer></script>
