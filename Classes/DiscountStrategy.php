<?php
namespace Classes;

interface DiscountStrategy {
    public function applyDiscount($total);
}

class NoDiscountStrategy implements DiscountStrategy {
    public function applyDiscount($total) {
        return $total;
    }
}

class BulkDiscountStrategy implements DiscountStrategy {
    // Si la compra es mayor a $100, se aplica un 10% de descuento.
    public function applyDiscount($total) {
        return $total > 100 ? $total * 0.90 : $total;
    }
}
