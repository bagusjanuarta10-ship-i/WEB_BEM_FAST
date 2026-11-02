<?php
require __DIR__ . '/../partials/navigation.php';
$escape = static function ($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
?>

<main class="page">
    <section class="content-card auth-card">
        <p class="eyebrow">Keamanan akun</p>
        <h1>Ganti Password</h1>
        <p>Masukkan password saat ini, lalu pilih password baru.</p>

        <?php if ($errors): ?>
            <div class="alert" role="alert">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= $escape($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" action="index.php?action=change_password">
            <input type="hidden" name="csrf_token" value="<?= $escape(Auth::csrfToken()) ?>">
            <p>
                <label for="current_password">Password saat ini</label><br>
                <span class="password-input-row">
                    <input id="current_password" name="current_password" type="password"
                        autocomplete="current-password" required>
                    <button class="password-toggle" type="button" data-password-target="current_password"
                        aria-label="Lihat password saat ini" title="Lihat kata sandi" aria-pressed="false">
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
            <p>
                <label for="new_password">Password baru</label><br>
                <span class="password-input-row">
                    <input id="new_password" name="new_password" type="password"
                        minlength="12" maxlength="255" autocomplete="new-password" required>
                    <button class="password-toggle" type="button" data-password-target="new_password"
                        aria-label="Lihat password baru" title="Lihat kata sandi" aria-pressed="false">
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
                <small>Gunakan 12-255 karakter dan jangan gunakan password lama.</small>
            </p>
            <p>
                <label for="confirm_password">Konfirmasi password baru</label><br>
                <span class="password-input-row">
                    <input id="confirm_password" name="confirm_password" type="password"
                        minlength="12" maxlength="255" autocomplete="new-password" required>
                    <button class="password-toggle" type="button" data-password-target="confirm_password"
                        aria-label="Lihat konfirmasi password baru" title="Lihat kata sandi" aria-pressed="false">
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
            <button type="submit">Simpan Password Baru</button>
        </form>
    </section>
</main>
<?php require __DIR__ . '/../partials/footer.php'; ?>
<script src="views/partials/password-toggle.js?v=1" defer></script>
