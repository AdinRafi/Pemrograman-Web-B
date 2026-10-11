<?php
declare(strict_types=1);

require_once __DIR__ . '/src/GuestBook.php';

$host = '127.0.0.1';
$db   = 'akademik_db';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO(
        "mysql:host={$host};dbname:{$db};charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch(PDOException $e){
    error_log('Database Connection Error: ' . $e->getMessage());
    http_response_code(500);
    exit('Sistem mengalami kegagalan teknis');
}

$guestBook = new GuestBook($pdo);

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $guestBook->simpan(
        trim((string)($_POST['nama'] ?? '')),
        trim((string)($_POST['email'] ?? '')),
        trim((string)($_POST['pesan'] ?? '')),
    );
    header('Location: guestbook.php');
    exit;
}

$daftar = $guestBook->ambilSemua();

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Buku Tamu Perpustakaan</title>
    <style>
        body { font-family: sans-serif; max-width: 800px; margin: 2rem auto; padding: 0 1rem; }
        label { display: block; margin-top: .8rem; font-weight: bold; }
        input, textarea { width: 100%; padding: .5rem; box-sizing: border-box; }
        button { margin-top: 1rem; padding: .6rem 1.2rem; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th, td { border: 1px solid #ccc; padding: .5rem; text-align: left; vertical-align: top; }
        th { background: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Buku Tamu Perpustakaan</h1>

    <form method="post" action="guestbook.php" novalidate>
        <label for="nama">Nama</label>
        <input type="text" id="nama" name="nama">

        <label for="email">Email</label>
        <input type="text" id="email" name="email">

        <label for="pesan">Pesan</label>
        <textarea id="pesan" name="pesan" rows="4"></textarea>

        <button type="submit">Kirim Pesan</button>
    </form>

    <h2>Daftar Pesan</h2>
    <table>
        <thead>
            <tr><th>No</th><th>Nama</th><th>Email</th><th>Pesan</th><th>Tanggal</th></tr>
        </thead>
        <tbody>
        <?php foreach ($daftar as $i => $row): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><?= $row['nama'] ?></td>
                <td><?= $row['email'] ?></td>
                <td><?= $row['pesan'] ?></td>
                <td><?= $row['tanggal_kirim'] ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>