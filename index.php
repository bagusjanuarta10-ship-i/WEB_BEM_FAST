<?php

require_once __DIR__ . '/models/MahasiswaModel.php';
require_once __DIR__ . '/models/KeanggotaanModel.php';
require_once __DIR__ . '/models/KegiatanModel.php';
require_once __DIR__ . '/models/DataOrganisasiModel.php';
require_once __DIR__ . '/models/UserModel.php';

require_once __DIR__ . '/controller/MahasiswaController.php';
require_once __DIR__ . '/controller/aboutController.php';
require_once __DIR__ . '/controller/kelasController.php';
require_once __DIR__ . '/controller/KeanggotaanController.php';
require_once __DIR__ . '/controller/DataOrganisasiController.php';
require_once __DIR__ . '/controller/Auth.php';
require_once __DIR__ . '/controller/AuthController.php';

Auth::initialize(require __DIR__ . '/config/database.php');

$action = $_GET['action'] ?? 'about';

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
    case 'about':
        $controller = new aboutController();
        $controller->about();
        break;
    case 'kelas':
        $controller = new kelasController();
        $controller->kelas();
        break;
    case 'keanggotaan':
        $controller = new KeanggotaanController();
        $controller->index();
        break;
    case 'divisi':
        $controller = new DataOrganisasiController();
        $controller->divisi();
        break;
    case 'jabatan':
        $controller = new DataOrganisasiController();
        $controller->jabatan();
        break;
    case 'mahasiswa':
        $controller = new MahasiswaController();
        $controller->index();
        break;
    default:
        $controller = new aboutController();
        $controller->about();
        break;
}