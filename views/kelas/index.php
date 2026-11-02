<?php
require __DIR__ . '/../partials/navigation.php';
$escape = static function ($value): string { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); };
$isEditing = ($formData['id_kegiatan'] ?? '') !== '';
$hasSearch = trim((string) ($search ?? '')) !== '' || trim((string) ($dateFrom ?? '')) !== '' || trim((string) ($dateTo ?? '')) !== '';
?>
<main class="page">
<header class="page-header">
    <p class="eyebrow">Ruang untuk bergerak dan berdampak</p>
    <h1>Agenda Kegiatan</h1>
    <p>Program dan kegiatan yang dirancang untuk mempererat kebersamaan serta memberi manfaat bagi civitas akademika.</p>
</header>
<?php if ($errors): ?><div class="alert" role="alert"><ul><?php foreach ($errors as $error): ?><li><?= $escape($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<?php if ($flashMessage !== ''): ?><div class="success-message" role="status"><?= $escape($flashMessage) ?></div><?php endif; ?>

<?php if ($currentUser && $currentUser['role'] === 'admin'): ?><p><a class="button-link" href="index.php?action=kelas&amp;add=1">Tambah Kegiatan</a></p><?php endif; ?>

<form method="get" action="index.php" class="search-form activity-search-form">
    <input type="hidden" name="action" value="kelas">
    <div class="activity-search-keyword"><label for="search-kegiatan">Kata kunci</label><input id="search-kegiatan" name="search" type="search" value="<?= $escape($search) ?>" placeholder="Nama, lokasi, divisi, periode..."></div>
    <div class="activity-search-date"><label for="date-from">Dari tanggal</label><input id="date-from" name="date_from" type="date" max="<?= $escape($dateTo) ?>" value="<?= $escape($dateFrom) ?>"></div>
    <div class="activity-search-date"><label for="date-to">Sampai tanggal</label><input id="date-to" name="date_to" type="date" min="<?= $escape($dateFrom) ?>" value="<?= $escape($dateTo) ?>"></div>
    <div class="activity-search-actions">
        <button type="submit" class="search-button"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="6"></circle><path d="m16 16 4 4"></path></svg><span>Cari</span></button>
        <?php if ($hasSearch): ?><a href="index.php?action=kelas">Reset</a><?php endif; ?>
    </div>
</form>

<?php if (!$kegiatan): ?>
<section class="empty-state"><h2><?= $hasSearch ? 'Kegiatan tidak ditemukan' : 'Belum ada kegiatan' ?></h2><p><?= $hasSearch ? 'Coba kata kunci atau rentang tanggal lain.' : 'Agenda organisasi akan ditampilkan di halaman ini setelah data kegiatan ditambahkan.' ?></p></section>
<?php else: ?>
<p class="result-count"><?= count($kegiatan) ?> kegiatan ditemukan<?= $search !== '' ? ' untuk pencarian ' . $escape($search) : '' ?></p>
<div class="table-wrap"><table><thead><tr>
<th scope="col">Nama Kegiatan</th><th scope="col">Tanggal</th><th scope="col">Periode</th><th scope="col">Penyelenggara</th><th scope="col">Lokasi</th><th scope="col">Deskripsi</th>
<?php if ($currentUser && $currentUser['role'] === 'admin'): ?><th scope="col">Aksi</th><?php endif; ?>
</tr></thead><tbody>
<?php foreach ($kegiatan as $item): ?><tr>
<td><strong><?= $escape($item['nama_kegiatan']) ?></strong></td>
<td><?= $escape($item['tanggal_kegiatan']) ?></td><td><?= $escape($item['nama_periode']) ?></td>
<td><?= $escape($item['nama_divisi'] ?: 'BEM FST') ?></td><td><?= $escape($item['lokasi'] ?: 'Belum ditentukan') ?></td><td><?= $escape($item['deskripsi'] ?: 'Belum ada deskripsi') ?></td>
<?php if ($currentUser && $currentUser['role'] === 'admin'): ?><td>
<a class="action-icon" href="index.php?action=kelas&amp;edit=<?= $escape($item['id_kegiatan']) ?>" aria-label="Ubah <?= $escape($item['nama_kegiatan']) ?>" title="Ubah"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20h9"></path><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L8 18l-4 1 1-4Z"></path></svg></a>
<form method="post" action="index.php?action=kelas" class="inline-form"><input type="hidden" name="csrf_token" value="<?= $escape($_SESSION['csrf_token']) ?>"><input type="hidden" name="operation" value="delete"><input type="hidden" name="id_kegiatan" value="<?= $escape($item['id_kegiatan']) ?>"><button class="icon-button delete-icon" type="submit" aria-label="Hapus <?= $escape($item['nama_kegiatan']) ?>" title="Hapus" onclick="return confirm('Hapus kegiatan ini?')"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h18"></path><path d="M8 6V4h8v2"></path><path d="m19 6-1 14H6L5 6"></path></svg></button></form>
</td><?php endif; ?></tr><?php endforeach; ?></tbody></table></div>
<?php endif; ?>

<?php if ($showForm && $currentUser && $currentUser['role'] === 'admin'): ?>
<section class="content-card">
<h2><?= $isEditing ? 'Ubah Kegiatan' : 'Tambah Kegiatan' ?></h2>
<p><?= $isEditing ? 'Perbarui informasi kegiatan organisasi.' : 'Isi informasi kegiatan organisasi untuk menambahkannya ke agenda.' ?></p>
<?php if (!$periode): ?><p>Tambahkan data periode terlebih dahulu sebelum membuat kegiatan.</p>
<?php else: ?>
<form method="post" action="index.php?action=kelas"><input type="hidden" name="csrf_token" value="<?= $escape($_SESSION['csrf_token']) ?>"><input type="hidden" name="operation" value="save"><input type="hidden" name="id_kegiatan" value="<?= $escape($formData['id_kegiatan']) ?>">
<p><label for="nama_kegiatan">Nama kegiatan</label><br><input id="nama_kegiatan" name="nama_kegiatan" type="text" maxlength="150" value="<?= $escape($formData['nama_kegiatan']) ?>" required></p>
<p><label for="tanggal_kegiatan">Tanggal kegiatan</label><br><input id="tanggal_kegiatan" name="tanggal_kegiatan" type="date" value="<?= $escape($formData['tanggal_kegiatan']) ?>" required></p>
<p><label for="id_periode">Periode</label><br><select id="id_periode" name="id_periode" required><option value="">Pilih periode</option><?php foreach ($periode as $item): ?><option value="<?= $escape($item['id_periode']) ?>" <?= (string) $formData['id_periode'] === (string) $item['id_periode'] ? 'selected' : '' ?>><?= $escape($item['nama_periode']) ?></option><?php endforeach; ?></select></p>
<p><label for="id_divisi">Penyelenggara (opsional)</label><br><select id="id_divisi" name="id_divisi"><option value="">BEM FST</option><?php foreach ($divisi as $item): ?><option value="<?= $escape($item['id_divisi']) ?>" <?= (string) $formData['id_divisi'] === (string) $item['id_divisi'] ? 'selected' : '' ?>><?= $escape($item['nama_divisi']) ?></option><?php endforeach; ?></select></p>
<p><label for="lokasi">Lokasi (opsional)</label><br><input id="lokasi" name="lokasi" type="text" maxlength="150" value="<?= $escape($formData['lokasi']) ?>"></p>
<p><label for="deskripsi">Deskripsi (opsional)</label><br><textarea id="deskripsi" name="deskripsi" rows="4" maxlength="65535"><?= $escape($formData['deskripsi']) ?></textarea></p>
<button type="submit"><?= $isEditing ? 'Simpan Perubahan' : 'Tambah Kegiatan' ?></button><?php if ($isEditing): ?><a href="index.php?action=kelas">Batal</a><?php endif; ?>
</form><?php endif; ?></section>
<?php endif; ?>
</main>
<?php require __DIR__ . '/../partials/footer.php'; ?>
