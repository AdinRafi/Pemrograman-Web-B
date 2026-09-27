<?php
session_start();

require_once __DIR__ . '/Transaction.php';

// Inisialisasi saldo dan riwayat transaksi
if (!isset($_SESSION['balance'])) {
    $_SESSION['balance'] = 0.00;
}

if (!isset($_SESSION['transactions'])) {
    $_SESSION['transactions'] = [];
}

// Buat CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validasi CSRF
    $csrfToken = $_POST['csrf_token'] ?? '';

    if (
        !hash_equals($_SESSION['csrf_token'], $csrfToken)
    ) {
        $error = 'Token CSRF tidak valid.';
    } else {
        $type = $_POST['type'] ?? '';
        $amountInput = $_POST['amount'] ?? '';

        // Validasi jumlah sebagai desimal positif
        if (
            !is_string($amountInput) ||
            !preg_match('/^\d+(?:\.\d{1,2})?$/', $amountInput) ||
            (float) $amountInput <= 0
        ) {
            $error = 'Jumlah transaksi harus berupa angka desimal positif.';
        } else 
        {
            $amount = (float) $amountInput;

            // Cocokkan jenis transaksi dengan match
            $transactionType = match ($type) {
                'deposit' => 'deposit',
                'withdraw' => 'withdraw',
                default => throw new RuntimeException('Jenis transaksi tidak valid.'),
            };
                try {
                    $transaction = new Transaction(
                        id: bin2hex(random_bytes(8)),
                        type: $transactionType,
                        amount: $amount
                    );

                    $transaction->process($_SESSION['balance']);

                    $_SESSION['transactions'][] = [
                        'id' => $transaction->getId(),
                        'type' => $transaction->getType(),
                        'amount' => $transaction->getAmount(),
                    ];

                    $message = 'Transaksi berhasil diproses.';
                } 
            catch (RuntimeException $e) {
                $error = $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finance</title>
</head>
<body>
    <h1>Transaksi</h1>
    <?php if ($message): ?>
        <p style="color: green;"><?=htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <?php if ($error): ?>
        <p style="color: red;"><?=htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form method="POST">
        <input 
            type="hidden" 
            name="csrf_token" 
            value="<?=htmlspecialchars($_SESSION['csrf_token']); ?>"
        >
        
        <label for="type">Jenis transaksi:</label>
        <select name="type" id="type" required>
            <option value="deposit">Deposit</option>
            <option value="withdraw">Penarikan</option>
        </select>

        <br><br>

        <label for="amount">Jumlah:</label>
        <input 
            type="text" 
            name="amount" 
            id="amount" 
            inputmode="decimal" 
            pattern="\d+(?:\.\d{1,2})?" 
            required
        >

        <br><br>

        <button type="submit">Proses</button>
    </form>

    <h2>Saldo</h2>  
    <p>
        Rp <?=htmlspecialchars(number_format($_SESSION['balance'], 2, ',', '.')); ?>
    </p>

    <h2>Riwayat Transaksi</h2>
    <?php if (!empty($_SESSION['transactions'])): ?>
        <table border="1">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Jenis</th>
                    <th>Jumlah</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($_SESSION['transactions'] as $transaction): ?>
                    <tr>
                        <td><?=htmlspecialchars($transaction['id']); ?></td>
                        <td><?=htmlspecialchars($transaction['type']); ?></td>
                        <td>Rp <?=htmlspecialchars(number_format($transaction['amount'], 2, ',', '.')); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Belum ada transaksi.</p>
    <?php endif; ?>
</body>