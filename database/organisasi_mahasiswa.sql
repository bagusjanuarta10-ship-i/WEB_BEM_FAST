CREATE DATABASE IF NOT EXISTS organisasi_mahasiswa
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE organisasi_mahasiswa;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS kegiatan;
DROP TABLE IF EXISTS keanggotaan;
DROP TABLE IF EXISTS mahasiswa;
DROP TABLE IF EXISTS jabatan;
DROP TABLE IF EXISTS divisi;
DROP TABLE IF EXISTS periode;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
    id_user INT UNSIGNED NOT NULL AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'user',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_user),
    UNIQUE KEY username (username)
) ENGINE=InnoDB;

CREATE TABLE mahasiswa (
    id_mahasiswa INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nim VARCHAR(20) NOT NULL,
    nama VARCHAR(100) NOT NULL,
    program_studi VARCHAR(100) NOT NULL,
    email VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_mahasiswa),
    UNIQUE KEY nim (nim)
) ENGINE=InnoDB;

CREATE TABLE periode (
    id_periode INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nama_periode VARCHAR(50) NOT NULL,
    tanggal_mulai DATE NOT NULL,
    tanggal_selesai DATE NOT NULL,
    PRIMARY KEY (id_periode),
    UNIQUE KEY nama_periode (nama_periode)
) ENGINE=InnoDB;

CREATE TABLE divisi (
    id_divisi INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nama_divisi VARCHAR(100) NOT NULL,
    nama_ketua VARCHAR(100) DEFAULT NULL,
    deskripsi TEXT DEFAULT NULL,
    PRIMARY KEY (id_divisi),
    UNIQUE KEY nama_divisi (nama_divisi)
) ENGINE=InnoDB;

CREATE TABLE jabatan (
    id_jabatan INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nama_jabatan VARCHAR(100) NOT NULL,
    nama_ketua VARCHAR(100) DEFAULT NULL,
    deskripsi TEXT DEFAULT NULL,
    PRIMARY KEY (id_jabatan),
    UNIQUE KEY nama_jabatan (nama_jabatan)
) ENGINE=InnoDB;

CREATE TABLE keanggotaan (
    id_keanggotaan INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_mahasiswa INT UNSIGNED NOT NULL,
    id_periode INT UNSIGNED NOT NULL,
    id_divisi INT UNSIGNED NOT NULL,
    id_jabatan INT UNSIGNED NOT NULL,
    tanggal_bergabung DATE NOT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'aktif',
    PRIMARY KEY (id_keanggotaan),
    UNIQUE KEY mahasiswa_periode (id_mahasiswa, id_periode),
    CONSTRAINT fk_keanggotaan_mahasiswa
        FOREIGN KEY (id_mahasiswa) REFERENCES mahasiswa(id_mahasiswa)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_keanggotaan_periode
        FOREIGN KEY (id_periode) REFERENCES periode(id_periode)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_keanggotaan_divisi
        FOREIGN KEY (id_divisi) REFERENCES divisi(id_divisi)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_keanggotaan_jabatan
        FOREIGN KEY (id_jabatan) REFERENCES jabatan(id_jabatan)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE kegiatan (
    id_kegiatan INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_periode INT UNSIGNED NOT NULL,
    id_divisi INT UNSIGNED DEFAULT NULL,
    nama_kegiatan VARCHAR(150) NOT NULL,
    tanggal_kegiatan DATE NOT NULL,
    lokasi VARCHAR(150) DEFAULT NULL,
    deskripsi TEXT DEFAULT NULL,
    PRIMARY KEY (id_kegiatan),
    CONSTRAINT fk_kegiatan_periode
        FOREIGN KEY (id_periode) REFERENCES periode(id_periode)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_kegiatan_divisi
        FOREIGN KEY (id_divisi) REFERENCES divisi(id_divisi)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

INSERT INTO periode (nama_periode, tanggal_mulai, tanggal_selesai)
VALUES ('2026-2027', '2026-01-01', '2026-12-31');

INSERT INTO divisi (nama_divisi, nama_ketua, deskripsi) VALUES
('Pengembangan Organisasi', NULL, 'Pengembangan Organisasi'),
('Humas', NULL, 'Humas'),
('Media', NULL, 'Media'),
('Kaderisasi', NULL, 'Kaderisasi');

INSERT INTO jabatan (nama_jabatan, nama_ketua, deskripsi) VALUES
('Ketua', NULL, 'Ketua'),
('Sekretaris', NULL, 'Sekretaris'),
('Bendahara', NULL, 'Bendahara'),
('Koordinator Divisi', NULL, 'Koordinator Divisi'),
('Anggota', NULL, 'Anggota');
