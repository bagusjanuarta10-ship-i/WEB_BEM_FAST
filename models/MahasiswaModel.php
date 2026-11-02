<?php

class MahasiswaModel
{
    private PDO $database;

    public function __construct(PDO $database)
    {
        $this->database = $database;
    }

    public function getAllMembers(string $search = '', int $limit = 10, int $offset = 0): array
    {
        $where = '';
        $params = [];
        if ($search !== '') {
            $where = ' WHERE m.nim LIKE :search0 OR m.nama LIKE :search1 OR m.program_studi LIKE :search2 OR d.nama_divisi LIKE :search3 OR j.nama_jabatan LIKE :search4';
            for ($index = 0; $index < 5; $index++) {
                $params['search' . $index] = '%' . $search . '%';
            }
        }
        $statement = $this->database->prepare(
            'SELECT k.id_keanggotaan, m.nim, m.nama, m.program_studi, m.email,
                    k.status, k.tanggal_bergabung, p.nama_periode,
                    d.nama_divisi, j.nama_jabatan
             FROM keanggotaan k
             INNER JOIN mahasiswa m ON m.id_mahasiswa = k.id_mahasiswa
             INNER JOIN periode p ON p.id_periode = k.id_periode
             INNER JOIN divisi d ON d.id_divisi = k.id_divisi
             INNER JOIN jabatan j ON j.id_jabatan = k.id_jabatan' . $where . '
             ORDER BY p.tanggal_mulai DESC, j.id_jabatan, m.nama
             LIMIT :limit OFFSET :offset'
        );
        foreach ($params as $key => $value) $statement->bindValue($key, $value);
        $statement->bindValue('limit', $limit, PDO::PARAM_INT);
        $statement->bindValue('offset', $offset, PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetchAll();
    }

    public function countMembers(string $search = ''): int
    {
        $where = '';
        $params = [];
        if ($search !== '') {
            $where = ' WHERE m.nim LIKE :search0 OR m.nama LIKE :search1 OR m.program_studi LIKE :search2 OR d.nama_divisi LIKE :search3 OR j.nama_jabatan LIKE :search4';
            for ($index = 0; $index < 5; $index++) {
                $params['search' . $index] = '%' . $search . '%';
            }
        }
        $statement = $this->database->prepare(
            'SELECT COUNT(*) FROM keanggotaan k
             INNER JOIN mahasiswa m ON m.id_mahasiswa = k.id_mahasiswa
             INNER JOIN periode p ON p.id_periode = k.id_periode
             INNER JOIN divisi d ON d.id_divisi = k.id_divisi
             INNER JOIN jabatan j ON j.id_jabatan = k.id_jabatan' . $where
        );
        foreach ($params as $key => $value) $statement->bindValue($key, $value);
        $statement->execute();
        return (int) $statement->fetchColumn();
    }
}
