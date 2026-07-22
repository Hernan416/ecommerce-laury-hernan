<?php
namespace Classes;

require_once __DIR__ . '/DiscountStrategy.php';

class CheckoutCalculator {
    private $discountStrategy;

    // Aquí demostramos Composición y Dependency Injection (SOLID - Inversión de Dependencias)
    public function __construct(DiscountStrategy $strategy) {
        $this->discountStrategy = $strategy;
    }

    public function calculateTotal($subtotal) {
        // La responsabilidad de calcular el descuento está delegada (SOLID SRP).
        return $this->discountStrategy->applyDiscount($subtotal);
    }
}
