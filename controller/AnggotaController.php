<?php
class AnggotaController
{
    private AnggotaModel $model;

    public function __construct()
    {
        $database = require __DIR__ . '/../config/database.php';
        $this->model = new AnggotaModel($database);
    }

    public function index(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $csrfToken = $_SESSION['csrf_token'];
        $flashMessage = $_SESSION['flash_message'] ?? '';
        unset($_SESSION['flash_message']);
        $currentUser = Auth::currentUser();

        $search = trim((string) ($_GET['search'] ?? ''));
        $page = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT);
        $page = $page && $page > 0 ? (int) $page : 1;
        $perPage = 10;
        $total = $this->model->countMembers($search);
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page = min($page, $totalPages);
        $anggota = $this->model->getAllMembers($search, $perPage, ($page - 1) * $perPage);

        require __DIR__ . '/../views/Anggota/index.php';
    }
}