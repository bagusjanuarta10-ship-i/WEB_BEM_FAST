<?php
class kelasController
{
    private KegiatanModel $model;

    public function __construct()
    {
        $database = require __DIR__ . '/../config/database.php';
        $this->model = new KegiatanModel($database);
    }

    public function kelas(): void
    {
        $currentUser = Auth::currentUser();
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && (!$currentUser || $currentUser['role'] !== 'admin')) {
            http_response_code(403);
            require __DIR__ . '/../views/auth/forbidden.php';
            return;
        }
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        $errors = [];
        $flashMessage = $_SESSION['flash_message'] ?? '';
        unset($_SESSION['flash_message']);
        $formData = ['id_kegiatan' => '', 'nama_kegiatan' => '', 'tanggal_kegiatan' => '', 'id_periode' => '', 'id_divisi' => '', 'lokasi' => '', 'deskripsi' => ''];
        $showForm = isset($_GET['add']) || isset($_GET['edit']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Auth::isValidCsrfToken($_POST['csrf_token'] ?? null)) {
                $errors[] = 'Sesi formulir tidak valid. Muat ulang halaman dan coba lagi.';
            } else {
                $operation = $_POST['operation'] ?? 'create';
                if ($operation === 'delete') {
                    $id = filter_var($_POST['id_kegiatan'] ?? null, FILTER_VALIDATE_INT);
                    if (!$id) $errors[] = 'Kegiatan yang akan dihapus tidak valid.';
                    else {
                        try {
                            $this->model->delete((int) $id);
                            $_SESSION['flash_message'] = 'Kegiatan berhasil dihapus.';
                            header('Location: index.php?action=kelas'); exit;
                        } catch (PDOException $exception) {
                            error_log($exception->getMessage()); $errors[] = 'Kegiatan gagal dihapus.';
                        }
                    }
                } else {
                    $showForm = true;
                    $formData = ['id_kegiatan' => trim((string) ($_POST['id_kegiatan'] ?? '')), 'nama_kegiatan' => trim((string) ($_POST['nama_kegiatan'] ?? '')), 'tanggal_kegiatan' => trim((string) ($_POST['tanggal_kegiatan'] ?? '')), 'id_periode' => trim((string) ($_POST['id_periode'] ?? '')), 'id_divisi' => trim((string) ($_POST['id_divisi'] ?? '')), 'lokasi' => trim((string) ($_POST['lokasi'] ?? '')), 'deskripsi' => trim((string) ($_POST['deskripsi'] ?? ''))];
                    if ($formData['nama_kegiatan'] === '' || strlen($formData['nama_kegiatan']) > 150) $errors[] = 'Nama kegiatan wajib diisi dan maksimal 150 karakter.';
                    $date = DateTime::createFromFormat('!Y-m-d', $formData['tanggal_kegiatan']);
                    if (!$date || $date->format('Y-m-d') !== $formData['tanggal_kegiatan']) $errors[] = 'Masukkan tanggal kegiatan yang valid.';
                    if (strlen($formData['lokasi']) > 150) $errors[] = 'Lokasi maksimal 150 karakter.';
                    if (strlen($formData['deskripsi']) > 65535) $errors[] = 'Deskripsi terlalu panjang.';
                    if (!filter_var($formData['id_periode'], FILTER_VALIDATE_INT) || (int) $formData['id_periode'] < 1) $errors[] = 'Pilih periode kegiatan yang tersedia.';
                    elseif (!$this->model->periodExists((int) $formData['id_periode'])) $errors[] = 'Periode yang dipilih tidak ditemukan.';
                    if ($formData['id_divisi'] !== '') {
                        if (!filter_var($formData['id_divisi'], FILTER_VALIDATE_INT) || (int) $formData['id_divisi'] < 1) $errors[] = 'Pilih divisi kegiatan yang valid.';
                        elseif (!$this->model->divisionExists((int) $formData['id_divisi'])) $errors[] = 'Divisi yang dipilih tidak ditemukan.';
                    }
                    if (!$errors) {
                        try {
                            $data = ['nama_kegiatan' => $formData['nama_kegiatan'], 'tanggal_kegiatan' => $formData['tanggal_kegiatan'], 'lokasi' => $formData['lokasi'] !== '' ? $formData['lokasi'] : null, 'deskripsi' => $formData['deskripsi'] !== '' ? $formData['deskripsi'] : null, 'id_periode' => (int) $formData['id_periode'], 'id_divisi' => $formData['id_divisi'] !== '' ? (int) $formData['id_divisi'] : null];
                            if ($formData['id_kegiatan'] !== '') { $this->model->update((int) $formData['id_kegiatan'], $data); $_SESSION['flash_message'] = 'Kegiatan berhasil diperbarui.'; }
                            else { $this->model->create($data); $_SESSION['flash_message'] = 'Kegiatan berhasil ditambahkan.'; }
                            header('Location: index.php?action=kelas'); exit;
                        } catch (PDOException $exception) {
                            error_log($exception->getMessage()); $errors[] = 'Kegiatan gagal disimpan. Periksa isian dan koneksi database.';
                        }
                    }
                }
            }
        } elseif (isset($_GET['add']) || isset($_GET['edit'])) {
            $showForm = true;
        }

        if (isset($_GET['edit'])) {
            $id = filter_var($_GET['edit'], FILTER_VALIDATE_INT);
            if ($id) {
                $record = $this->model->get((int) $id);
                if ($record) $formData = array_map('strval', $record);
                else $errors[] = 'Kegiatan yang akan diubah tidak ditemukan.';
            }
        }

        $periode = $this->model->getPeriodOptions();
        $divisi = $this->model->getDivisionOptions();
        $kegiatan = $this->model->getAll();
        require __DIR__ . '/../views/kelas/index.php';
    }
}
