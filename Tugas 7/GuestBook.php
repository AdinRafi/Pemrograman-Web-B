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
}
