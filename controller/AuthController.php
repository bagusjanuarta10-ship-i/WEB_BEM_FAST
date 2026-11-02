<?php

class AuthController
{
    private UserModel $model;

    public function __construct()
    {
        $this->model = new UserModel(Auth::database());
    }
    public function login(): void
    {
        $errors = [];
        $username = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim((string) ($_POST['username'] ?? ''));
            $password = (string) ($_POST['password'] ?? '');

            if (!Auth::isValidCsrfToken($_POST['csrf_token'] ?? null)) {
                $errors[] = 'Sesi formulir tidak valid. Muat ulang halaman dan coba lagi.';
            } else {
                $user = $this->model->findByUsername($username);

                if (!$user || !password_verify($password, $user['password_hash'])) {
                    $errors[] = 'Nama pengguna atau kata sandi salah.';
                } else {
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = (int) $user['id_user'];
                    header('Location: index.php?action=tentang');
                    exit;
                }
            }
        }

        $mode = 'login';
        require __DIR__ . '/../views/auth/form.php';
    }

    public function register(): void
    {
        $errors = [];
        $username = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim((string) ($_POST['username'] ?? ''));
            $password = (string) ($_POST['password'] ?? '');
            $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

            if (!Auth::isValidCsrfToken($_POST['csrf_token'] ?? null)) {
                $errors[] = 'Sesi formulir tidak valid. Muat ulang halaman dan coba lagi.';
            } elseif (!$this->model->hasAdministrator()) {
                $errors[] = 'Registrasi belum dapat dilakukan karena akun admin awal belum dikonfigurasi.';
            } else {
                if (!preg_match('/\A[a-zA-Z0-9._-]{3,50}\z/', $username)) {
                    $errors[] = 'Nama pengguna harus 3-50 karakter, hanya huruf, angka, titik, garis bawah, atau tanda hubung.';
                }
                if (strlen($password) < 12) {
                    $errors[] = 'Kata sandi minimal 12 karakter.';
                }
                if ($password !== $confirmPassword) {
                    $errors[] = 'Konfirmasi kata sandi tidak sama.';
                }
                if (Auth::bootstrapUsername() !== null
                    && strcasecmp($username, Auth::bootstrapUsername()) === 0) {
                    $errors[] = 'Nama pengguna tersebut tidak tersedia.';
                }

                if (!$errors) {
                    try {
                        $userId = $this->model->create($username, password_hash($password, PASSWORD_DEFAULT));
                        session_regenerate_id(true);
                        $_SESSION['user_id'] = $userId;
                        $_SESSION['flash_message'] = 'Pendaftaran berhasil. Akun Anda memiliki akses pengguna.';
                        header('Location: index.php?action=tentang');
                        exit;
                    } catch (PDOException $exception) {
                        if ($exception->getCode() === '23000') {
                            $errors[] = 'Nama pengguna sudah digunakan.';
                        } else {
                            error_log($exception->getMessage());
                            $errors[] = 'Akun gagal dibuat. Silakan coba lagi.';
                        }
                    }
                }
            }
        }

        $mode = 'register';
        require __DIR__ . '/../views/auth/form.php';
    }

    public function changePassword(): void
    {
        $currentUser = Auth::currentUser();
        if ($currentUser === null) {
            http_response_code(403);
            require __DIR__ . '/../views/auth/forbidden.php';
            return;
        }

        $errors = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $currentPassword = (string) ($_POST['current_password'] ?? '');
            $newPassword = (string) ($_POST['new_password'] ?? '');
            $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

            if (!Auth::isValidCsrfToken($_POST['csrf_token'] ?? null)) {
                $errors[] = 'Sesi formulir tidak valid. Muat ulang halaman dan coba lagi.';
            } else {
                $passwordHash = $this->model->findPasswordHash((int) $currentUser['id_user']);

                if (!is_string($passwordHash) || !password_verify($currentPassword, $passwordHash)) {
                    $errors[] = 'Kata sandi saat ini tidak sesuai.';
                }
                if (strlen($newPassword) < 12 || strlen($newPassword) > 255) {
                    $errors[] = 'Kata sandi baru harus 12-255 karakter.';
                }
                if ($newPassword !== $confirmPassword) {
                    $errors[] = 'Konfirmasi kata sandi baru tidak sama.';
                }
                if (is_string($passwordHash)
                    && $newPassword !== ''
                    && password_verify($newPassword, $passwordHash)) {
                    $errors[] = 'Kata sandi baru harus berbeda dari kata sandi saat ini.';
                }

                if (!$errors) {
                    try {
                        $this->model->updatePassword(
                            (int) $currentUser['id_user'],
                            password_hash($newPassword, PASSWORD_DEFAULT)
                        );
                        session_regenerate_id(true);
                        $_SESSION['flash_message'] = 'Kata sandi berhasil diubah.';
                        header('Location: index.php?action=tentang');
                        exit;
                    } catch (PDOException $exception) {
                        error_log($exception->getMessage());
                        $errors[] = 'Kata sandi gagal diubah. Silakan coba lagi.';
                    }
                }
            }
        }

        require __DIR__ . '/../views/auth/change-password.php';
    }

    public function logout(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST'
            || !Auth::isValidCsrfToken($_POST['csrf_token'] ?? null)) {
            http_response_code(400);
            echo 'Permintaan logout tidak valid.';
            return;
        }

        $_SESSION = [];
        session_regenerate_id(true);
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        header('Location: index.php?action=tentang');
        exit;
    }

    public function users(): void
    {
        if (!Auth::isAdmin()) {
            http_response_code(403);
            require __DIR__ . '/../views/auth/forbidden.php';
            return;
        }

        $errors = [];
        $flashMessage = $_SESSION['flash_message'] ?? '';
        unset($_SESSION['flash_message']);
        $currentUser = Auth::currentUser();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Auth::isValidCsrfToken($_POST['csrf_token'] ?? null)) {
                $errors[] = 'Sesi formulir tidak valid. Muat ulang halaman dan coba lagi.';
            } else {
                $userId = filter_var($_POST['id_user'] ?? null, FILTER_VALIDATE_INT);
                $operation = $_POST['operation'] ?? 'role';
                $isSuperadmin = $this->model->isSuperadmin((int) $currentUser['id_user'], Auth::bootstrapUsername());
                if (!$userId) {
                    $errors[] = 'Akun yang dipilih tidak valid.';
                } else {
                    $target = $this->model->findUser((int) $userId);
                    $targetIsSuperadmin = $this->model->isSuperadmin((int) $userId, Auth::bootstrapUsername());
                    if (!$target) {
                        $errors[] = 'Akun tidak ditemukan.';
                    } elseif ($targetIsSuperadmin) {
                        $errors[] = 'Akun Admin Utama bersifat permanen dan tidak dapat diubah atau dihapus.';
                    } elseif (!$isSuperadmin) {
                        $errors[] = 'Admin baru tidak diizinkan mengubah atau menghapus akun lain.';
                    } elseif ((int) $userId === (int) $currentUser['id_user']) {
                        $errors[] = 'Akun yang sedang digunakan tidak dapat diubah atau dihapus dari halaman ini.';
                    } else {
                        if ($operation === 'delete') {
                            $this->model->deleteUser((int) $userId);
                            $_SESSION['flash_message'] = 'Akun berhasil dihapus.';
                        } elseif ($operation === 'username') {
                            $username = trim((string) ($_POST['username'] ?? ''));
                            if (!preg_match('/\A[a-zA-Z0-9._-]{3,50}\z/', $username)) {
                                $errors[] = 'Nama pengguna harus 3-50 karakter dan hanya boleh berisi huruf, angka, titik, garis bawah, atau tanda hubung.';
                            } else {
                                try {
                                    $this->model->updateUsername((int) $userId, $username);
                                    $_SESSION['flash_message'] = 'Nama pengguna berhasil diperbarui.';
                                } catch (PDOException $exception) {
                                    $errors[] = $exception->getCode() === '23000' ? 'Nama pengguna sudah digunakan.' : 'Nama pengguna gagal diperbarui.';
                                }
                            }
                        } else {
                            $role = $_POST['role'] ?? '';
                            if (!in_array($role, ['admin', 'user'], true)) {
                                $errors[] = 'Perubahan peran akun tidak valid.';
                            } else {
                                $this->model->updateRole((int) $userId, $role);
                                $_SESSION['flash_message'] = 'Peran akun berhasil diperbarui.';
                            }
                        }
                        if (!$errors && $operation !== 'username') {
                            header('Location: index.php?action=users'); exit;
                        }
                        if (!$errors && $operation === 'username') {
                            header('Location: index.php?action=users'); exit;
                        }
                    }
                }
            }
        }

        $users = $this->model->getAll();
        require __DIR__ . '/../views/auth/users.php';
    }
}
