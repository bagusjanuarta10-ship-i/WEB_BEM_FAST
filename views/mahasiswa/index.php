<?php
require __DIR__ . '/../partials/navigation.php';
$escape = static function ($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
?>

<main class="page">
    <header class="page-header">
        <p class="eyebrow">Keluarga besar BEM FST</p>
        <h1>Anggota Organisasi</h1>
        <p>Kenali mahasiswa yang berkontribusi, divisi tempat mereka berkarya, dan amanah yang dijalankan.</p>
    </header>

    <?php if ($flashMessage !== ''): ?>
        <div class="success-message" role="status"><?= $escape($flashMessage) ?></div>
    <?php endif; ?>

    <form method="get" action="index.php" class="search-form">
        <input type="hidden" name="action" value="mahasiswa">
        <label for="search-member">Cari anggota</label>
        <input id="search-member" name="search" type="search" value="<?= $escape($search) ?>" placeholder="Nama, NIM, divisi, jabatan...">
        <button type="submit" class="search-button">
            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                <circle cx="11" cy="11" r="6"></circle>
                <path d="m16 16 4 4"></path>
            </svg>
            <span>Cari</span>
        </button>
        <?php if ($search !== ''): ?><a href="index.php?action=mahasiswa">Reset</a><?php endif; ?>
    </form>

    <?php if (!$anggota): ?>
        <section class="empty-state">
            <h2>Belum ada anggota terdaftar</h2>
            <p>Data anggota organisasi akan ditampilkan di halaman ini setelah ditambahkan.</p>
            <?php if ($currentUser && $currentUser['role'] === 'admin'): ?>
                <a class="button-link" href="index.php?action=keanggotaan">Tambah anggota</a>
            <?php endif; ?>
        </section>
    <?php else: ?>
        <?php if ($currentUser && $currentUser['role'] === 'admin'): ?>
            <p><a class="button-link" href="index.php?action=keanggotaan">Tambah Anggota</a></p>
        <?php endif; ?>
        <p class="result-count"><?= $total ?> anggota ditemukan<?= $search !== '' ? ' untuk pencarian ' . $escape($search) : '' ?></p>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Mahasiswa</th>
                        <th>Program Studi</th>
                        <th>Divisi</th>
                        <th>Jabatan</th>
                        <th>Periode</th>
                        <th>Status</th>
                        <?php if ($currentUser && $currentUser['role'] === 'admin'): ?>
                            <th>Aksi</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($anggota as $item): ?>
                        <tr>
                            <td>
                                <strong><?= $escape($item['nama']) ?></strong>
                                <span class="secondary"><?= $escape($item['nim']) ?></span>
                            </td>
                            <td><?= $escape($item['program_studi']) ?></td>
                            <td><?= $escape($item['nama_divisi']) ?></td>
                            <td><?= $escape($item['nama_jabatan']) ?></td>
                            <td><?= $escape($item['nama_periode']) ?></td>
                            <?php $isActive = $item['status'] === 'aktif'; ?>
                            <td>
                                <span class="status-indicator <?= $isActive ? 'is-active' : 'is-inactive' ?>"
                                    role="img" aria-label="<?= $isActive ? 'Aktif' : 'Tidak aktif' ?>"
                                    title="<?= $isActive ? 'Aktif' : 'Tidak aktif' ?>"></span>
                            </td>
                            <?php if ($currentUser && $currentUser['role'] === 'admin'): ?>
                                <td>
                                    <a class="action-icon" href="index.php?action=keanggotaan&amp;edit=<?= $escape($item['id_keanggotaan']) ?>&amp;return_to=mahasiswa"
                                        aria-label="Ubah <?= $escape($item['nama']) ?>" title="Ubah">
                                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                            <path d="M12 20h9"></path>
                                            <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L8 18l-4 1 1-4Z"></path>
                                        </svg>
                                    </a>
                                    <form method="post" action="index.php?action=keanggotaan" class="inline-form">
                                        <input type="hidden" name="csrf_token" value="<?= $escape($csrfToken) ?>">
                                        <input type="hidden" name="operation" value="delete">
                                        <input type="hidden" name="id_keanggotaan" value="<?= $escape($item['id_keanggotaan']) ?>">
                                        <input type="hidden" name="return_to" value="mahasiswa">
                                        <button class="icon-button delete-icon" type="submit"
                                            aria-label="Hapus <?= $escape($item['nama']) ?>" title="Hapus"
                                            onclick="return confirm('Hapus data anggota ini?')">
                                            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                                <path d="M3 6h18"></path>
                                                <path d="M8 6V4h8v2"></path>
                                                <path d="m19 6-1 14H6L5 6"></path>
                                                <path d="M10 11v5M14 11v5"></path>
                                            </svg>
                                        </button>
                                    </form>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if ($totalPages > 1): ?>
            <nav class="member-pagination mt-4" aria-label="Navigasi halaman anggota">
                <p class="member-pagination-info mb-2">
                    Halaman <?= $page ?> dari <?= $totalPages ?>
                </p>
                <ul class="pagination pagination-lg justify-content-center mb-0">
                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                        <?php if ($page <= 1): ?>
                            <span class="page-link" aria-disabled="true" aria-label="Halaman sebelumnya">
                                <span aria-hidden="true">&laquo; Sebelumnya</span>
                            </span>
                        <?php else: ?>
                            <a class="page-link" href="index.php?action=mahasiswa&amp;page=<?= $page - 1 ?>&amp;search=<?= urlencode($search) ?>" aria-label="Halaman sebelumnya">
                                <span aria-hidden="true">&laquo; Sebelumnya</span>
                            </a>
                        <?php endif; ?>
                    </li>

                    <?php for ($pageNumber = 1; $pageNumber <= $totalPages; $pageNumber++): ?>
                        <li class="page-item <?= $pageNumber === $page ? 'active' : '' ?>">
                            <a class="page-link"
                                href="index.php?action=mahasiswa&amp;page=<?= $pageNumber ?>&amp;search=<?= urlencode($search) ?>"
                                <?= $pageNumber === $page ? 'aria-current="page"' : '' ?>>
                                <?= $pageNumber ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                        <?php if ($page >= $totalPages): ?>
                            <span class="page-link" aria-disabled="true" aria-label="Halaman berikutnya">
                                <span aria-hidden="true">Selanjutnya &raquo;</span>
                            </span>
                        <?php else: ?>
                            <a class="page-link" href="index.php?action=mahasiswa&amp;page=<?= $page + 1 ?>&amp;search=<?= urlencode($search) ?>" aria-label="Halaman berikutnya">
                                <span aria-hidden="true">Selanjutnya &raquo;</span>
                            </a>
                        <?php endif; ?>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</main>
<?php require __DIR__ . '/../partials/footer.php'; ?>