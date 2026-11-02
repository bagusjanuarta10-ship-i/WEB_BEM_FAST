<?php

class KegiatanModel
{
    private PDO $database;

    public function __construct(PDO $database)
    {
        $this->database = $database;
    }

    public function getPeriodOptions(): array
    {
        return $this->database->query(
            'SELECT id_periode, nama_periode FROM periode ORDER BY tanggal_mulai DESC'
        )->fetchAll();
    }

    public function getDivisionOptions(): array
    {
        return $this->database->query(
            'SELECT id_divisi, nama_divisi FROM divisi ORDER BY nama_divisi'
        )->fetchAll();
    }

    public function periodExists(int $id): bool
    {
        $statement = $this->database->prepare('SELECT COUNT(*) FROM periode WHERE id_periode = :id');
        $statement->execute(['id' => $id]);
        return (int) $statement->fetchColumn() > 0;
    }

    public function divisionExists(int $id): bool
    {
        $statement = $this->database->prepare('SELECT COUNT(*) FROM divisi WHERE id_divisi = :id');
        $statement->execute(['id' => $id]);
        return (int) $statement->fetchColumn() > 0;
    }

    public function create(array $data): void
    {
        $statement = $this->database->prepare(
            'INSERT INTO kegiatan
                (nama_kegiatan, tanggal_kegiatan, lokasi, deskripsi, id_periode, id_divisi)
             VALUES
                (:nama_kegiatan, :tanggal_kegiatan, :lokasi, :deskripsi, :id_periode, :id_divisi)'
        );
        $statement->execute($data);
    }

    public function getAll(): array
    {
        return $this->database->query(
            'SELECT k.id_kegiatan, k.nama_kegiatan, k.tanggal_kegiatan, k.lokasi, k.deskripsi,
                    k.id_periode, k.id_divisi, p.nama_periode, d.nama_divisi
             FROM kegiatan k
             INNER JOIN periode p ON p.id_periode = k.id_periode
             LEFT JOIN divisi d ON d.id_divisi = k.id_divisi
             ORDER BY k.tanggal_kegiatan DESC, k.nama_kegiatan'
        )->fetchAll();
    }

    public function get(int $id): ?array
    {
        $statement = $this->database->prepare(
            'SELECT k.id_kegiatan, k.nama_kegiatan, k.tanggal_kegiatan, k.lokasi, k.deskripsi,
                    k.id_periode, k.id_divisi
             FROM kegiatan k WHERE k.id_kegiatan = :id'
        );
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();
        return $row ?: null;
    }

    public function update(int $id, array $data): void
    {
        $statement = $this->database->prepare(
            'UPDATE kegiatan SET nama_kegiatan = :nama_kegiatan, tanggal_kegiatan = :tanggal_kegiatan,
                lokasi = :lokasi, deskripsi = :deskripsi, id_periode = :id_periode, id_divisi = :id_divisi
             WHERE id_kegiatan = :id'
        );
        $data['id'] = $id;
        $statement->execute($data);
    }

    public function delete(int $id): void
    {
        $statement = $this->database->prepare('DELETE FROM kegiatan WHERE id_kegiatan = :id');
        $statement->execute(['id' => $id]);
    }
}
