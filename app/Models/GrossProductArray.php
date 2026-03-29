<?php

namespace App\Models;

class GrossProductArray
{
    private array $array;


    // @var GrossProduct[] $array
    public function __construct($array = [])
    {
        $this->array = $array;
    }

    public function get(): array
    {
        return $this->array;
    }

    public function addIngredientProduct(array $p, float $gross_weight)
    {
        $pWeight = $p['pivot']['share'] * $gross_weight;
        if (array_key_exists($p['id'], $this->array)) {
            $this->array[$p['id']]->amount += $pWeight;
        } else {
            $this->array[$p['id']] = new GrossProduct($p['id'], $p['name'], $pWeight);
        }
    }

    public function addProduct(GrossProduct $p)
    {
        if (array_key_exists($p->id, $this->array)) {
            $this->array[$p->id]->amount += $p->amount;
        } else {
            $this->array[$p->id] = new GrossProduct($p->id, $p->name, $p->amount);
        }
    }

    public function writeOffProduct(GrossProduct $p)
    {
        if (array_key_exists($p->id, $this->array)) {
            $this->array[$p->id]->amount -= $p->amount;
        } else {
            $this->array[$p->id] = new GrossProduct($p->id, $p->name, -$p->amount);
        }
    }

    public function addInventoryProduct(array $p)
    {
        $weight = $p['pivot']['net_weight'] * $p['pivot']['amount'];

        if (array_key_exists($p['id'], $this->array)) {
            $this->array[$p['id']]->amount += $weight;
        } else {
            $this->array[$p['id']] = new GrossProduct($p['id'], $p['name'], $weight);
        }
    }

    public function addPurchaseProduct(array $p)
    {
        $weight = $p['pivot']['net_weight'] * $p['pivot']['amount'];

        if (array_key_exists($p['id'], $this->array)) {
            $this->array[$p['id']]->amount += $weight;
        } else {
            $this->array[$p['id']] = new GrossProduct($p['id'], $p['product']['name'], $weight);
        }
    }
}
