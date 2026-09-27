<?php
session_start();

require_once __DIR__ . '/Transaction.php';

// Inisialisasi saldo dan riwayat transaksi
$_SESSION['balance'] = 0.00;
$_SESSION['transactions'] = [];

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