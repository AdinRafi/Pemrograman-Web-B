<?php
declare(strict_types=1);

class Transaction
{
    public function __construct(
        private string $id, 
        private string $type, 
        private float $amount
    ) {}

    public function getId(): string
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function process(float &$sessionBalance): void
    {
        match ($this->type){
            'deposit' => $this->deposit($sessionBalance),
            'withdraw' => $this->withdraw($sessionBalance),
            default => throw new Exception("Jenis transaksi tidak valid."),
        };
    }

    public function deposit(float &$sessionBalance): void
    {
        $sessionBalance += $this->amount;
    }

    public function withdraw(float &$sessionBalance): void
    {   
        if($this->amount > $sessionBalance) 
        {
            throw new Exception("Penarikan ditolak: Saldo tidak mencukupi.");
        }
        $sessionBalance -= $this->amount;
    }
}