<?php

class KeanggotaanController
{
    private KeanggotaanModel $model;

    public function __construct()
    {
        $database = require __DIR__ . '/../config/database.php';
        $this->model = new KeanggotaanModel($database);
    }

    public function index(): void
    {
        if (!Auth::isAdmin()) {
            http_response_code(403);
            require __DIR__ . '/../views/auth/forbidden.php';
            return;
        }

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $errors = [];
        $returnTo = ($_GET['return_to'] ?? '') === 'mahasiswa' ? 'mahasiswa' : 'keanggotaan';
        $flashMessage = $_SESSION['flash_message'] ?? '';
        unset($_SESSION['flash_message']);
        $formData = [
            'id_keanggotaan' => '', 'id_mahasiswa' => '', 'nim' => '', 'nama' => '',
            'program_studi' => '', 'email' => '', 'id_periode' => '', 'id_divisi' => '',
            'id_jabatan' => '', 'tanggal_bergabung' => '', 'status' => 'aktif',
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $returnTo = ($_POST['return_to'] ?? '') === 'mahasiswa' ? 'mahasiswa' : 'keanggotaan';
            if (!Auth::isValidCsrfToken($_POST['csrf_token'] ?? null)) {
                $errors[] = 'Sesi formulir tidak valid. Muat ulang halaman dan coba lagi.';
            } else {
                $operation = $_POST['operation'] ?? '';
                if ($operation === 'delete') {
                    $id = filter_var($_POST['id_keanggotaan'] ?? null, FILTER_VALIDATE_INT);
                    if (!$id) {
                        $errors[] = 'Data keanggotaan yang akan dihapus tidak valid.';
                    } else {
                        try {
                            $this->model->delete((int) $id);
                            $_SESSION['flash_message'] = 'Data anggota berhasil dihapus.';
                            $this->redirectAfterSave($returnTo);
                        } catch (PDOException $exception) {
                            error_log($exception->getMessage());
                            $errors[] = 'Data keanggotaan gagal dihapus. Silakan coba lagi.';
                        }
                    }
                } elseif ($operation === 'save_member') {
                    $formData = [
                        'id_keanggotaan' => trim((string) ($_POST['id_keanggotaan'] ?? '')),
                        'id_mahasiswa' => trim((string) ($_POST['id_mahasiswa'] ?? '')),
                        'nim' => trim((string) ($_POST['nim'] ?? '')),
                        'nama' => trim((string) ($_POST['nama'] ?? '')),
                        'program_studi' => trim((string) ($_POST['program_studi'] ?? '')),
                        'email' => trim((string) ($_POST['email'] ?? '')),
                        'id_periode' => trim((string) ($_POST['id_periode'] ?? '')),
                        'id_divisi' => trim((string) ($_POST['id_divisi'] ?? '')),
                        'id_jabatan' => trim((string) ($_POST['id_jabatan'] ?? '')),
                        'tanggal_bergabung' => trim((string) ($_POST['tanggal_bergabung'] ?? '')),
                        'status' => trim((string) ($_POST['status'] ?? '')),
                    ];
                    if ($formData['nim'] === '' || strlen($formData['nim']) > 20) $errors[] = 'NIM wajib diisi dan maksimal 20 karakter.';
                    if ($formData['nama'] === '' || strlen($formData['nama']) > 100) $errors[] = 'Nama wajib diisi dan maksimal 100 karakter.';
                    if ($formData['program_studi'] === '' || strlen($formData['program_studi']) > 100) $errors[] = 'Program studi wajib diisi dan maksimal 100 karakter.';
                    if (strlen($formData['email']) > 100 || ($formData['email'] !== '' && filter_var($formData['email'], FILTER_VALIDATE_EMAIL) === false)) $errors[] = 'Masukkan alamat email yang valid, maksimal 100 karakter.';
                    $errors = array_merge($errors, $this->validateForm($formData));
                    if (!$errors) {
                        try {
                            $this->model->save($formData);
                            $_SESSION['flash_message'] = $formData['id_keanggotaan'] === '' ? 'Anggota baru berhasil ditambahkan.' : 'Data anggota berhasil diperbarui.';
                            $this->redirectAfterSave($returnTo);
                        } catch (PDOException $exception) {
                            error_log($exception->getMessage());
                            $errors[] = $exception->getCode() === '23000'
                                ? 'NIM sudah digunakan atau anggota sudah tercatat pada periode tersebut.'
                                : 'Data anggota gagal disimpan. Periksa isian dan koneksi database.';
                        } catch (RuntimeException $exception) {
                            $errors[] = $exception->getMessage();
                        }
                    }
                } else {
                    $errors[] = 'Perintah formulir tidak dikenal.';
                }
            }
        } elseif (isset($_GET['edit'])) {
            $id = filter_var($_GET['edit'], FILTER_VALIDATE_INT);
            if ($id) {
                $record = $this->model->getEditData((int) $id);
                if ($record) {
                    $formData = array_map('strval', $record);
                } else {
                    $errors[] = 'Data keanggotaan tidak ditemukan.';
                }
            } else {
                $errors[] = 'ID keanggotaan tidak valid.';
            }
        }

        $periode = $this->model->getPeriodOptions();
        $divisi = $this->model->getDivisionOptions();
        $jabatan = $this->model->getPositionOptions();
        $keanggotaan = $this->model->getAll();
        require __DIR__ . '/../views/keanggotaan/index.php';
    }

    private function validateForm(array $formData): array
    {
        $errors = [];
        $foreignKeys = ['id_periode' => 'periode', 'id_divisi' => 'divisi', 'id_jabatan' => 'jabatan'];
        if ($formData['id_keanggotaan'] !== '') $foreignKeys['id_mahasiswa'] = 'mahasiswa';

        foreach ($foreignKeys as $field => $table) {
            if (!filter_var($formData[$field], FILTER_VALIDATE_INT) || (int) $formData[$field] < 1) {
                $errors[] = 'Pilih mahasiswa, periode, divisi, dan jabatan yang tersedia.';
                break;
            }
            if (!$this->model->existsInTable($table, $field, (int) $formData[$field])) {
                $errors[] = 'Pilihan mahasiswa, periode, divisi, atau jabatan tidak ditemukan.';
                break;
            }
        }
        if ($formData['id_keanggotaan'] !== '' && (!filter_var($formData['id_keanggotaan'], FILTER_VALIDATE_INT) || (int) $formData['id_keanggotaan'] < 1)) $errors[] = 'ID keanggotaan tidak valid.';
        if ($formData['id_keanggotaan'] !== '' && (!filter_var($formData['id_mahasiswa'], FILTER_VALIDATE_INT) || (int) $formData['id_mahasiswa'] < 1)) $errors[] = 'ID mahasiswa yang akan diubah tidak valid.';
        $date = DateTime::createFromFormat('!Y-m-d', $formData['tanggal_bergabung']);
        if (!$date || $date->format('Y-m-d') !== $formData['tanggal_bergabung']) $errors[] = 'Tanggal bergabung wajib diisi dengan tanggal yang valid.';
        if (!in_array($formData['status'], ['aktif', 'nonaktif'], true)) $errors[] = 'Pilih status aktif atau nonaktif.';
        return $errors;
    }

    private function redirectAfterSave(string $returnTo): void
    {
        $action = $returnTo === 'mahasiswa' ? 'mahasiswa' : 'keanggotaan';
        header('Location: index.php?action=' . $action);
        exit;
    }
}
