<?php

namespace App\Models;

class GrossProduct
{
    public int $id;
    public string $name;
    public float $amount;

    public function __construct(int $id, string $name, float $amount)
    {
        $this->id = $id;
        $this->name = $name;
        $this->amount = $amount;
    }
}
