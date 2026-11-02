<?php

class KeanggotaanModel
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
        return $this->database->query('SELECT id_divisi, nama_divisi FROM divisi ORDER BY nama_divisi')->fetchAll();
    }

    public function getPositionOptions(): array
    {
        return $this->database->query('SELECT id_jabatan, nama_jabatan FROM jabatan ORDER BY nama_jabatan')->fetchAll();
    }

    public function getAll(): array
    {
        return $this->database->query(
            'SELECT k.id_keanggotaan, m.nim, m.nama, p.nama_periode,
                    d.nama_divisi, j.nama_jabatan, k.tanggal_bergabung, k.status
             FROM keanggotaan k
             INNER JOIN mahasiswa m ON m.id_mahasiswa = k.id_mahasiswa
             INNER JOIN periode p ON p.id_periode = k.id_periode
             INNER JOIN divisi d ON d.id_divisi = k.id_divisi
             INNER JOIN jabatan j ON j.id_jabatan = k.id_jabatan
             ORDER BY p.tanggal_mulai DESC, m.nama'
        )->fetchAll();
    }

    public function getEditData(int $id): ?array
    {
        $statement = $this->database->prepare(
            'SELECT k.id_keanggotaan, k.id_mahasiswa, m.nim, m.nama, m.program_studi, m.email,
                    k.id_periode, k.id_divisi, k.id_jabatan, k.tanggal_bergabung, k.status
             FROM keanggotaan k
             INNER JOIN mahasiswa m ON m.id_mahasiswa = k.id_mahasiswa
             WHERE k.id_keanggotaan = :id'
        );
        $statement->execute(['id' => $id]);
        $data = $statement->fetch();
        return $data ?: null;
    }

    public function delete(int $id): void
    {
        $statement = $this->database->prepare('DELETE FROM keanggotaan WHERE id_keanggotaan = :id');
        $statement->execute(['id' => $id]);
    }

    public function existsInTable(string $table, string $idColumn, int $id): bool
    {
        $allowed = ['periode' => 'id_periode', 'divisi' => 'id_divisi', 'jabatan' => 'id_jabatan', 'mahasiswa' => 'id_mahasiswa'];
        if (!isset($allowed[$table]) || $allowed[$table] !== $idColumn) {
            return false;
        }
        $statement = $this->database->prepare("SELECT COUNT(*) FROM {$table} WHERE {$idColumn} = :id");
        $statement->execute(['id' => $id]);
        return (int) $statement->fetchColumn() > 0;
    }

    public function nimExistsForAnotherStudent(string $nim, int $studentId): bool
    {
        $statement = $this->database->prepare(
            'SELECT COUNT(*) FROM mahasiswa WHERE nim = :nim AND id_mahasiswa <> :student_id'
        );
        $statement->execute(['nim' => $nim, 'student_id' => $studentId]);
        return (int) $statement->fetchColumn() > 0;
    }

    public function membershipExistsForAnotherRecord(int $studentId, int $periodId, int $membershipId): bool
    {
        $statement = $this->database->prepare(
            'SELECT COUNT(*) FROM keanggotaan
             WHERE id_mahasiswa = :student_id AND id_periode = :period_id
             AND id_keanggotaan <> :membership_id'
        );
        $statement->execute([
            'student_id' => $studentId,
            'period_id' => $periodId,
            'membership_id' => $membershipId,
        ]);
        return (int) $statement->fetchColumn() > 0;
    }

    public function save(array $formData): void
    {
        $this->database->beginTransaction();
        try {
            $studentValues = [
                'nim' => $formData['nim'],
                'nama' => $formData['nama'],
                'program_studi' => $formData['program_studi'],
                'email' => $formData['email'] !== '' ? $formData['email'] : null,
            ];
            $isEditing = $formData['id_keanggotaan'] !== '';

            if (!$isEditing) {
                $statement = $this->database->prepare(
                    'INSERT INTO mahasiswa (nim, nama, program_studi, email)
                     VALUES (:nim, :nama, :program_studi, :email)'
                );
                $statement->execute($studentValues);
                $studentId = (int) $this->database->lastInsertId();
            } else {
                $statement = $this->database->prepare(
                    'SELECT id_mahasiswa FROM keanggotaan WHERE id_keanggotaan = :id_keanggotaan FOR UPDATE'
                );
                $statement->execute(['id_keanggotaan' => (int) $formData['id_keanggotaan']]);
                $studentId = (int) $statement->fetchColumn();
                if ($studentId < 1) {
                    throw new RuntimeException('Data keanggotaan tidak ditemukan.');
                }
                $studentValues['id_mahasiswa'] = $studentId;
                $statement = $this->database->prepare(
                    'UPDATE mahasiswa SET nim = :nim, nama = :nama, program_studi = :program_studi, email = :email
                     WHERE id_mahasiswa = :id_mahasiswa'
                );
                $statement->execute($studentValues);
            }

            $membershipValues = [
                'id_mahasiswa' => $studentId,
                'id_periode' => (int) $formData['id_periode'],
                'id_divisi' => (int) $formData['id_divisi'],
                'id_jabatan' => (int) $formData['id_jabatan'],
                'tanggal_bergabung' => $formData['tanggal_bergabung'],
                'status' => $formData['status'],
            ];

            if (!$isEditing) {
                $statement = $this->database->prepare(
                    'INSERT INTO keanggotaan
                        (id_mahasiswa, id_periode, id_divisi, id_jabatan, tanggal_bergabung, status)
                     VALUES
                        (:id_mahasiswa, :id_periode, :id_divisi, :id_jabatan, :tanggal_bergabung, :status)'
                );
            } else {
                $membershipValues['id_keanggotaan'] = (int) $formData['id_keanggotaan'];
                $statement = $this->database->prepare(
                    'UPDATE keanggotaan SET id_periode = :id_periode, id_divisi = :id_divisi,
                        id_jabatan = :id_jabatan, tanggal_bergabung = :tanggal_bergabung, status = :status
                     WHERE id_keanggotaan = :id_keanggotaan'
                );
            }
            $statement->execute($membershipValues);
            $this->database->commit();
        } catch (Throwable $exception) {
            if ($this->database->inTransaction()) {
                $this->database->rollBack();
            }
            throw $exception;
        }
    }
}
