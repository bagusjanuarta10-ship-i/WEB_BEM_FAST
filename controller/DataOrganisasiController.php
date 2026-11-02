<?php

class DataOrganisasiController
{
    private DataOrganisasiModel $model;

    public function __construct()
    {
        $database = require __DIR__ . '/../config/database.php';
        $this->model = new DataOrganisasiModel($database);
    }

    public function divisi(): void
    {
        $this->handleDefinitions('divisi');
    }

    public function jabatan(): void
    {
        $this->handleDefinitions('jabatan');
    }

    private function handleDefinitions(string $type): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        $errors = [];
        $flashMessage = $_SESSION['flash_message'] ?? '';
        $isDivision = $type === 'divisi';

        unset($_SESSION['flash_message']);
        $isDivision = $type === 'divisi';
        $title = $isDivision ? 'Divisi Organisasi' : 'Jabatan Organisasi';
        $description = $isDivision
            ? 'Kelola nama divisi dan ketua divisi organisasi.'
            : 'Kelola nama jabatan dan nama ketua jabatan organisasi.';
        $nameField = $isDivision ? 'nama_divisi' : 'nama_jabatan';
        $idField = $isDivision ? 'id_divisi' : 'id_jabatan';
        $nameLabel = $isDivision ? 'Nama Divisi' : 'Nama Jabatan';
        $formData = ['id' => '', 'name' => '', 'chair' => ''];
        $showForm = isset($_GET['add']) || isset($_GET['edit']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $currentUser = Auth::currentUser();
            if (!$currentUser || $currentUser['role'] !== 'admin') {
                http_response_code(403);
                require __DIR__ . '/../views/auth/forbidden.php';
                return;
            }
            if (!Auth::isValidCsrfToken($_POST['csrf_token'] ?? null)) {
                $errors[] = 'Sesi formulir tidak valid. Muat ulang halaman dan coba lagi.';
            } else {
                $operation = $_POST['operation'] ?? '';
                if ($operation === 'delete') {
                    $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
                    if (!$id) {
                        $errors[] = 'Data yang akan dihapus tidak valid.';
                    } else {
                        try {
                            $isDivision ? $this->model->deleteDivision((int) $id) : $this->model->deletePosition((int) $id);
                            $_SESSION['flash_message'] = $isDivision ? 'Divisi berhasil dihapus.' : 'Jabatan berhasil dihapus.';
                            header('Location: index.php?action=' . $type);
                            exit;
                        } catch (PDOException $exception) {
                            error_log($exception->getMessage());
                            $errors[] = 'Data masih digunakan oleh anggota atau kegiatan dan tidak dapat dihapus.';
                        }
                    }
                } elseif ($operation === 'save') {
                    $showForm = true;
                    $id = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT) ?: 0;
                    $name = trim((string) ($_POST['name'] ?? ''));
                    $chair = trim((string) ($_POST['chair'] ?? ''));
                    $formData = ['id' => (string) $id, 'name' => $name, 'chair' => $chair];
                    if ($name === '' || strlen($name) > 100) $errors[] = $nameLabel . ' wajib diisi dan maksimal 100 karakter.';
                    if ($chair === '' || strlen($chair) > 100) $errors[] = 'Nama Ketua wajib diisi dan maksimal 100 karakter.';
                    if (!$errors) {
                        try {
                            $isDivision ? $this->model->saveDivision((int) $id, $name, $chair) : $this->model->savePosition((int) $id, $name, $chair);
                            $_SESSION['flash_message'] = $isDivision ? 'Divisi berhasil disimpan.' : 'Jabatan berhasil disimpan.';
                            header('Location: index.php?action=' . $type);
                            exit;
                        } catch (PDOException $exception) {
                            error_log($exception->getMessage());
                            $errors[] = $exception->getCode() === '23000' ? $nameLabel . ' sudah digunakan.' : 'Data gagal disimpan. Silakan coba lagi.';
                        }
                    }
                } else {
                    $errors[] = 'Perintah formulir tidak dikenal.';
                }
            }
        } elseif (isset($_GET['edit'])) {
            $id = filter_var($_GET['edit'], FILTER_VALIDATE_INT);
            if ($id) {
                $record = $isDivision ? $this->model->getDivision((int) $id) : $this->model->getPosition((int) $id);
                if ($record) {
                    $formData = [
                        'id' => (string) ($record[$idField] ?? ''),
                        'name' => (string) ($record[$nameField] ?? ''),
                        'chair' => (string) ($record['nama_ketua'] ?? ''),
                    ];
                } else {
                    $errors[] = 'Data yang akan diubah tidak ditemukan.';
                }
            } else {
                $errors[] = 'ID data tidak valid.';
            }
        }

        $rows = $isDivision ? $this->model->getDivisions() : $this->model->getPositions();
        $columns = $isDivision
            ? ['nama_divisi' => 'Nama Divisi', 'nama_ketua' => 'Nama Ketua']
            : ['nama_jabatan' => 'Nama Jabatan', 'nama_ketua' => 'Nama Ketua'];
        require __DIR__ . '/../views/data-organisasi/index.php';
    }
}
