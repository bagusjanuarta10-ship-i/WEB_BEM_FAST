<?php
require __DIR__ . '/../partials/navigation.php';

$escape = static function ($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
$isEditing = $formData['id_keanggotaan'] !== '';
?>

<main class="page">
<header class="page-header">
    <p class="eyebrow">Pengelolaan organisasi</p>
    <h1>Data Keanggotaan</h1>
    <p>Atur mahasiswa, periode, divisi, dan jabatan dalam satu daftar kepengurusan.</p>
</header>

<?php if ($errors): ?>
    <div class="alert" role="alert">
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?= $escape($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<?php if ($flashMessage !== ''): ?>
    <div class="success-message" role="status"><?= $escape($flashMessage) ?></div>
<?php endif; ?>

<section class="content-card">
<h2><?= $isEditing ? 'Ubah Data Anggota' : 'Tambah Anggota Baru' ?></h2>
<p><?= $isEditing
        ? 'Perbarui identitas mahasiswa atau informasi kepengurusan anggota ini.'
        : 'Isi identitas mahasiswa dan penempatannya dalam organisasi. Data mahasiswa dan keanggotaan akan disimpan sekaligus.' ?></p>

<?php if (!$periode || !$divisi || !$jabatan): ?>
    <p>Pastikan sudah tersedia data periode, divisi, dan jabatan sebelum menambah anggota.</p>
<?php else: ?>
    <form method="post" action="index.php?action=keanggotaan">
        <input type="hidden" name="csrf_token" value="<?= $escape($_SESSION['csrf_token']) ?>">
        <input type="hidden" name="operation" value="save_member">
        <input type="hidden" name="id_keanggotaan" value="<?= $escape($formData['id_keanggotaan']) ?>">
        <input type="hidden" name="id_mahasiswa" value="<?= $escape($formData['id_mahasiswa']) ?>">
        <input type="hidden" name="return_to" value="<?= $escape($returnTo) ?>">

        <p>
            <label for="nim">NIM</label><br>
            <input id="nim" name="nim" type="text" maxlength="20"
                value="<?= $escape($formData['nim']) ?>" required>
        </p>

        <p>
            <label for="nama">Nama lengkap</label><br>
            <input id="nama" name="nama" type="text" maxlength="100"
                value="<?= $escape($formData['nama']) ?>" required>
        </p>

        <p>
            <label for="program_studi">Program studi</label><br>
            <input id="program_studi" name="program_studi" type="text" maxlength="100"
                value="<?= $escape($formData['program_studi']) ?>" required>
        </p>

        <p>
            <label for="email">Email (opsional)</label><br>
            <input id="email" name="email" type="email" maxlength="100"
                value="<?= $escape($formData['email']) ?>">
        </p>

        <p>
            <label for="id_periode">Periode</label><br>
            <select id="id_periode" name="id_periode" required>
                <option value="">Pilih periode</option>
                <?php foreach ($periode as $item): ?>
                    <option value="<?= $escape($item['id_periode']) ?>"
                        <?= (string) $formData['id_periode'] === (string) $item['id_periode'] ? 'selected' : '' ?>>
                        <?= $escape($item['nama_periode']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>

        <p>
            <label for="id_divisi">Divisi</label><br>
            <select id="id_divisi" name="id_divisi" required>
                <option value="">Pilih divisi</option>
                <?php foreach ($divisi as $item): ?>
                    <option value="<?= $escape($item['id_divisi']) ?>"
                        <?= (string) $formData['id_divisi'] === (string) $item['id_divisi'] ? 'selected' : '' ?>>
                        <?= $escape($item['nama_divisi']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>

        <p>
            <label for="id_jabatan">Jabatan</label><br>
            <select id="id_jabatan" name="id_jabatan" required>
                <option value="">Pilih jabatan</option>
                <?php foreach ($jabatan as $item): ?>
                    <option value="<?= $escape($item['id_jabatan']) ?>"
                        <?= (string) $formData['id_jabatan'] === (string) $item['id_jabatan'] ? 'selected' : '' ?>>
                        <?= $escape($item['nama_jabatan']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>

        <p>
            <label for="tanggal_bergabung">Tanggal Bergabung</label><br>
            <input id="tanggal_bergabung" name="tanggal_bergabung" type="date"
                value="<?= $escape($formData['tanggal_bergabung']) ?>" required>
        </p>

        <p>
            <label for="status">Status</label><br>
            <select id="status" name="status" required>
                <option value="aktif" <?= $formData['status'] === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                <option value="nonaktif" <?= $formData['status'] === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
            </select>
        </p>

        <button type="submit"><?= $isEditing ? 'Simpan Perubahan' : 'Tambah Anggota' ?></button>
        <?php if ($isEditing): ?>
            <a href="index.php?action=keanggotaan">Batal</a>
        <?php endif; ?>
    </form>
<?php endif; ?>
</section>

<?php if (!$isEditing): ?>
<h2>Daftar Keanggotaan</h2>

<?php if (!$keanggotaan): ?>
    <p>Belum ada data keanggotaan.</p>
<?php else: ?>
    <div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>NIM</th>
                <th>Nama</th>
                <th>Periode</th>
                <th>Divisi</th>
                <th>Jabatan</th>
                <th>Tanggal Bergabung</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($keanggotaan as $item): ?>
                <tr>
                    <td><?= $escape($item['nim']) ?></td>
                    <td><?= $escape($item['nama']) ?></td>
                    <td><?= $escape($item['nama_periode']) ?></td>
                    <td><?= $escape($item['nama_divisi']) ?></td>
                    <td><?= $escape($item['nama_jabatan']) ?></td>
                    <td><?= $escape($item['tanggal_bergabung']) ?></td>
                    <?php $isActive = $item['status'] === 'aktif'; ?>
                    <td>
                        <span class="status-indicator <?= $isActive ? 'is-active' : 'is-inactive' ?>"
                            role="img" aria-label="<?= $isActive ? 'Aktif' : 'Tidak aktif' ?>"
                            title="<?= $isActive ? 'Aktif' : 'Tidak aktif' ?>"></span>
                    </td>
                    <td>
                        <a class="action-icon" href="index.php?action=keanggotaan&amp;edit=<?= $escape($item['id_keanggotaan']) ?>"
                            aria-label="Ubah <?= $escape($item['nama']) ?>" title="Ubah">
                            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                <path d="M12 20h9"></path>
                                <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L8 18l-4 1 1-4Z"></path>
                            </svg>
                        </a>
                        <form method="post" action="index.php?action=keanggotaan" class="inline-form">
                            <input type="hidden" name="csrf_token" value="<?= $escape($_SESSION['csrf_token']) ?>">
                            <input type="hidden" name="operation" value="delete">
                            <input type="hidden" name="id_keanggotaan" value="<?= $escape($item['id_keanggotaan']) ?>">
                            <input type="hidden" name="return_to" value="<?= $escape($returnTo) ?>">
                            <button class="icon-button delete-icon" type="submit"
                                aria-label="Hapus <?= $escape($item['nama']) ?>" title="Hapus"
                                onclick="return confirm('Hapus data keanggotaan ini?')">
                                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                    <path d="M3 6h18"></path>
                                    <path d="M8 6V4h8v2"></path>
                                    <path d="m19 6-1 14H6L5 6"></path>
                                    <path d="M10 11v5M14 11v5"></path>
                                </svg>
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
        </div>
    <?php endif; ?>
<?php endif; ?>
    </main>
<?php require __DIR__ . '/../partials/footer.php'; ?>
