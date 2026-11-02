<?php
require __DIR__ . '/../partials/navigation.php';
$escape = static function ($value): string { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); };
$userModel = new UserModel(Auth::database());
$superadmin = $userModel->getSuperadmin();
$isCurrentSuperadmin = $superadmin && (int) $superadmin['id_user'] === (int) $currentUser['id_user'];
?>
<main class="page">
<header class="page-header"><p class="eyebrow">Pengaturan admin</p><h1>Kelola Akun</h1><p>Ubah peran pengguna dan kelola akun. Akun Admin Utama bersifat permanen.</p></header>
<?php if ($errors): ?><div class="alert" role="alert"><ul><?php foreach ($errors as $error): ?><li><?= $escape($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<?php if ($flashMessage !== ''): ?><div class="success-message" role="status"><?= $escape($flashMessage) ?></div><?php endif; ?>
<div class="table-wrap"><table class="account-table"><thead><tr><th>Nama pengguna</th><th>Peran</th><th>Dibuat</th><th>Aksi</th></tr></thead><tbody>
<?php foreach ($users as $user): ?>
<?php $isSuperadminRow = $superadmin && (int) $superadmin['id_user'] === (int) $user['id_user']; ?>
<tr><td><?= $escape($user['username']) ?></td><td><?= $user['role'] === 'admin' ? 'Admin' : 'Pengguna' ?></td><td><?= $escape($user['created_at']) ?></td><td class="account-action-cell">
<?php if ($isSuperadminRow): ?><span class="secondary">Admin Utama — permanen</span>
<?php elseif ((int) $user['id_user'] === (int) $currentUser['id_user']): ?><span class="secondary">Akun Anda</span>
<?php elseif (!$isCurrentSuperadmin): ?><span class="secondary">Tidak berwenang</span>
<?php else: ?>
<div class="account-actions">
<form method="post" action="index.php?action=users" class="account-role-form">
<input type="hidden" name="csrf_token" value="<?= $escape(Auth::csrfToken()) ?>">
<input type="hidden" name="operation" value="role">
<input type="hidden" name="id_user" value="<?= $escape($user['id_user']) ?>">
<select name="role" aria-label="Peran <?= $escape($user['username']) ?>" onchange="this.form.submit()">
<option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>Pengguna</option>
<option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
</select>
</form>
<button class="action-icon" type="button" title="Ubah nama pengguna" aria-label="Ubah nama pengguna <?= $escape($user['username']) ?>" onclick="openUsernameEditor(this)">
<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M7 2.5h7l3 3v16H7z"></path><path d="M14 2.5v3h3"></path><path d="M9.5 11.5h5M9.5 15.5h5"></path></svg>
</button>
<dialog class="account-edit-dialog">
<form method="post" action="index.php?action=users">
<input type="hidden" name="csrf_token" value="<?= $escape(Auth::csrfToken()) ?>">
<input type="hidden" name="operation" value="username">
<input type="hidden" name="id_user" value="<?= $escape($user['id_user']) ?>">
<label>Nama pengguna baru<input name="username" maxlength="50" value="<?= $escape($user['username']) ?>" required></label>
<div class="dialog-actions"><button type="button" onclick="this.closest('dialog').close()">Batal</button><button type="submit">Simpan</button></div>
</form>
</dialog>
<form method="post" action="index.php?action=users" class="inline-form">
<input type="hidden" name="csrf_token" value="<?= $escape(Auth::csrfToken()) ?>">
<input type="hidden" name="operation" value="delete">
<input type="hidden" name="id_user" value="<?= $escape($user['id_user']) ?>">
<button class="icon-button delete-icon" type="submit" onclick="return confirm('Hapus akun ini?')" aria-label="Hapus akun <?= $escape($user['username']) ?>" title="Hapus akun">
<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h18"></path><path d="M8 6V4h8v2"></path><path d="m19 6-1 14H6L5 6"></path><path d="M10 11v5M14 11v5"></path></svg>
</button>
</form>
</div>
<?php endif; ?></td></tr>
<?php endforeach; ?></tbody></table></div>
</main>
<?php require __DIR__ . '/../partials/footer.php'; ?>
<script>
function openUsernameEditor(button) {
    const dialog = button.nextElementSibling;
    if (dialog && typeof dialog.showModal === 'function') {
        dialog.showModal();
        const input = dialog.querySelector('input[name="username"]');
        if (input) { input.focus(); input.select(); }
    }
}
</script>
