<?php

require_once __DIR__ . '/models/AnggotaModel.php';
require_once __DIR__ . '/models/KeanggotaanModel.php';
require_once __DIR__ . '/models/KegiatanModel.php';
require_once __DIR__ . '/models/DivisiJabatanModel.php';
require_once __DIR__ . '/models/UserModel.php';

require_once __DIR__ . '/controller/AnggotaController.php';
require_once __DIR__ . '/controller/TentangController.php';
require_once __DIR__ . '/controller/KegiatanController.php';
require_once __DIR__ . '/controller/KeanggotaanController.php';
require_once __DIR__ . '/controller/DivisiJabatanController.php';
require_once __DIR__ . '/controller/Auth.php';
require_once __DIR__ . '/controller/AuthController.php';

Auth::initialize(require __DIR__ . '/config/database.php');

$action = $_GET['action'] ?? 'tentang';

switch ($action) {
    case 'login':
        (new AuthController())->login();
        break;
    case 'register':
        (new AuthController())->register();
        break;
    case 'change_password':
        (new AuthController())->changePassword();
        break;
    case 'logout':
        (new AuthController())->logout();
        break;
    case 'users':
        (new AuthController())->users();
        break;
    case 'tentang':
        $controller = new TentangController();
        $controller->tentang();
        break;
    case 'kegiatan':
    case 'kelas':
        $controller = new KegiatanController();
        $controller->kegiatan();
        break;
    case 'keanggotaan':
        $controller = new KeanggotaanController();
        $controller->index();
        break;
    case 'divisi':
        $controller = new DivisiJabatanController();
        $controller->divisi();
        break;
    case 'jabatan':
        $controller = new DivisiJabatanController();
        $controller->jabatan();
        break;
    case 'anggota':
    case 'mahasiswa':
        $controller = new AnggotaController();
        $controller->index();
        break;
    default:
        $controller = new TentangController();
        $controller->tentang();
        break;
}