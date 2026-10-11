<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/src/GuestBook.php';

$host = '127.0.0.1';
$db   = 'akademik_db';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO(
        "mysql:host={$host};dbname={$db};charset=utf8mb4",
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

function e(string $teks): string
{
    return htmlspecialchars($teks, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$guestBook = new GuestBook($pdo);

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $token = (string)($_POST['csrf_token'] ?? '');
    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(403);
        exit('Permintaan ditolak: token CSRF tidak valid.');
    }
    $guestBook->simpan(
        trim((string)($_POST['nama'] ?? '')),
        trim((string)($_POST['email'] ?? '')),
        trim((string)($_POST['pesan'] ?? '')),
    );
    unset($_SESSION['csrf_token']);
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
        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
        
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
                <td><?= e($row['nama']) ?></td>
                <td><?= e($row['email']) ?></td>
                <td><?= nl2br(e($row['pesan'])) ?></td>
                <td><?= e($row['tanggal_kirim']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>