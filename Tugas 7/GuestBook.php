<?php
declare(strict_types=1);

class GuestBook
{   
    public function __construct(private PDO $pdo) 
    {   
        $this->buatTabel();
    }

    private function buatTabel(): void
    {
        $this->pdo->exec(
            "CREATE TABLE IF NOT EXISTS buku_tamu (
                id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                nama            VARCHAR(100) NOT NULL,
                email           VARCHAR(150) NOT NULL, 
                pesan           TEXT NOT NULL, 
                tanggal_kirim   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, 
                INDEX idx_tanggal_kirim (tanggal_kirim)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );
    }

    public function simpan(string $nama, string $email, string $pesan): bool 
    {
        $sql = "INSERT INTO buku_tamu (nama, email, pesan)
                VALUES (:nama :email :pesan)";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            'nama' => $nama,
            'email' => $email,
            'pesan' => $pesan,
        ]);
    }

    public function ambilSemua(int $batas=100): array
    {
        $sql = "SELECT id, nama, email, pesan, tanggal_kirim
                FROM buku_tamu
                ORDER BY tanggal_kirim DESC, id DESC
                LIMIT :batas";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':batas', $batas, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchALL();
    }
}
