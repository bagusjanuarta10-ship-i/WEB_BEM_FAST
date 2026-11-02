<?php

class DivisiJabatanModel
{
    private PDO $database;

    public function __construct(PDO $database)
    {
        $this->database = $database;
    }

    public function ensureNamaKetuaColumns(): void
    {
        foreach (['divisi', 'jabatan'] as $table) {
            $column = $table === 'divisi' ? 'nama_divisi' : 'nama_jabatan';
            $exists = $this->database->prepare('SHOW COLUMNS FROM ' . $table . ' LIKE \'nama_ketua\'');
            $exists->execute();
            if (!$exists->fetch()) {
                $this->database->exec("ALTER TABLE {$table} ADD COLUMN nama_ketua VARCHAR(100) NULL AFTER {$column}");
            }
        }
    }

    public function getDivisions(): array
    {
        $this->ensureNamaKetuaColumns();
        return $this->database->query(
            'SELECT id_divisi, nama_divisi, nama_ketua, deskripsi FROM divisi ORDER BY nama_divisi'
        )->fetchAll();
    }

    public function getDivision(int $id): ?array
    {
        $statement = $this->database->prepare(
            'SELECT id_divisi, nama_divisi, nama_ketua, deskripsi FROM divisi WHERE id_divisi = :id'
        );
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();
        return $row ?: null;
    }

    public function saveDivision(int $id, string $name, string $chair): void
    {
        if ($id > 0) {
            $statement = $this->database->prepare(
                'UPDATE divisi SET nama_divisi = :name, nama_ketua = :chair, deskripsi = :description WHERE id_divisi = :id'
            );
            $statement->execute(['name' => $name, 'chair' => $chair !== '' ? $chair : null, 'description' => $name, 'id' => $id]);
        } else {
            $statement = $this->database->prepare(
                'INSERT INTO divisi (nama_divisi, nama_ketua, deskripsi) VALUES (:name, :chair, :description)'
            );
            $statement->execute(['name' => $name, 'chair' => $chair !== '' ? $chair : null, 'description' => $name]);
        }
    }

    public function deleteDivision(int $id): void
    {
        $statement = $this->database->prepare('DELETE FROM divisi WHERE id_divisi = :id');
        $statement->execute(['id' => $id]);
    }

    public function getPositions(): array
    {
        $this->ensureNamaKetuaColumns();
        return $this->database->query(
            'SELECT id_jabatan, nama_jabatan, nama_ketua, deskripsi FROM jabatan ORDER BY id_jabatan'
        )->fetchAll();
    }

    public function getPosition(int $id): ?array
    {
        $statement = $this->database->prepare(
            'SELECT id_jabatan, nama_jabatan, nama_ketua, deskripsi FROM jabatan WHERE id_jabatan = :id'
        );
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();
        return $row ?: null;
    }

    public function savePosition(int $id, string $name, string $chair): void
    {
        if ($id > 0) {
            $statement = $this->database->prepare(
                'UPDATE jabatan SET nama_jabatan = :name, nama_ketua = :chair, deskripsi = :description WHERE id_jabatan = :id'
            );
            $statement->execute(['name' => $name, 'chair' => $chair !== '' ? $chair : null, 'description' => $name, 'id' => $id]);
        } else {
            $statement = $this->database->prepare(
                'INSERT INTO jabatan (nama_jabatan, nama_ketua, deskripsi) VALUES (:name, :chair, :description)'
            );
            $statement->execute(['name' => $name, 'chair' => $chair !== '' ? $chair : null, 'description' => $name]);
        }
    }

    public function deletePosition(int $id): void
    {
        $statement = $this->database->prepare('DELETE FROM jabatan WHERE id_jabatan = :id');
        $statement->execute(['id' => $id]);
    }
}
