<?php
require __DIR__ . '/../partials/navigation.php';
$escape = static function ($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
$currentUser = Auth::currentUser();
$isEditing = $formData['id'] !== '';
?>

<main class="page">
    <header class="page-header">
        <p class="eyebrow">Data organisasi</p>
        <h1><?= $escape($title) ?></h1>
        <p><?= $escape($description) ?></p>
    </header>

    <?php if ($errors): ?>
        <div class="alert" role="alert"><ul>
            <?php foreach ($errors as $error): ?><li><?= $escape($error) ?></li><?php endforeach; ?>
        </ul></div>
    <?php endif; ?>

    <?php if ($flashMessage !== ''): ?>
        <div class="success-message" role="status"><?= $escape($flashMessage) ?></div>
    <?php endif; ?>

    <?php if ($currentUser && $currentUser['role'] === 'admin'): ?>
        <p><a class="button-link" href="index.php?action=<?= $escape($type) ?>&amp;add=1">Tambah <?= $escape($isDivision ? 'Divisi' : 'Jabatan') ?></a></p>
    <?php endif; ?>

    <?php if ($showForm && $currentUser && $currentUser['role'] === 'admin'): ?>
        <section class="content-card">
            <h2><?= $isEditing ? 'Ubah Data' : ($isDivision ? 'Tambah Divisi' : 'Tambah Jabatan') ?></h2>
            <form method="post" action="index.php?action=<?= $escape($type) ?>">
                <input type="hidden" name="csrf_token" value="<?= $escape(Auth::csrfToken()) ?>">
                <input type="hidden" name="operation" value="save">
                <input type="hidden" name="id" value="<?= $escape($formData['id']) ?>">
                <p>
                    <label for="name"><?= $escape($nameLabel) ?></label><br>
                    <input id="name" name="name" type="text" maxlength="100" value="<?= $escape($formData['name']) ?>" required>
                </p>
                <p>
                    <label for="chair">Nama Ketua</label><br>
                    <input id="chair" name="chair" type="text" maxlength="100" value="<?= $escape($formData['chair']) ?>" required>
                </p>
                <button type="submit"><?= $isEditing ? 'Simpan Perubahan' : 'Tambah' ?></button>
                <?php if ($isEditing): ?><a href="index.php?action=<?= $escape($type) ?>">Batal</a><?php endif; ?>
            </form>
        </section>
    <?php endif; ?>

    <?php if (!$rows): ?>
        <section class="empty-state">
            <h2>Belum ada data</h2>
            <p>Data <?= $escape(strtolower($title)) ?> akan ditampilkan di sini setelah ditambahkan.</p>
        </section>
    <?php else: ?>
        <p class="result-count"><?= count($rows) ?> data</p>
        <div class="table-wrap">
            <table>
                <thead><tr>
                    <?php foreach ($columns as $label): ?><th scope="col"><?= $escape($label) ?></th><?php endforeach; ?>
                    <?php if ($currentUser && $currentUser['role'] === 'admin'): ?><th scope="col">Aksi</th><?php endif; ?>
                </tr></thead>
                <tbody>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <?php foreach ($columns as $field => $label): ?>
                                <td><?= $escape($row[$field] ?: '—') ?></td>
                            <?php endforeach; ?>
                            <?php if ($currentUser && $currentUser['role'] === 'admin'): ?>
                                <td>
                                    <a class="action-icon" href="index.php?action=<?= $escape($type) ?>&amp;edit=<?= $escape($row[$idField]) ?>" aria-label="Ubah" title="Ubah">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20h9"></path><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L8 18l-4 1 1-4Z"></path></svg>
                                    </a>
                                    <form method="post" action="index.php?action=<?= $escape($type) ?>" class="inline-form">
                                        <input type="hidden" name="csrf_token" value="<?= $escape(Auth::csrfToken()) ?>">
                                        <input type="hidden" name="operation" value="delete">
                                        <input type="hidden" name="id" value="<?= $escape($row[$idField]) ?>">
                                        <button class="icon-button delete-icon" type="submit" aria-label="Hapus" title="Hapus" onclick="return confirm('Hapus data ini?')">
                                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h18"></path><path d="M8 6V4h8v2"></path><path d="m19 6-1 14H6L5 6"></path></svg>
                                        </button>
                                    </form>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</main>
<?php require __DIR__ . '/../partials/footer.php'; ?>
