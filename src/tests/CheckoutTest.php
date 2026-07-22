<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../Classes/Product.php';
require_once __DIR__ . '/../Classes/VinylProduct.php';
require_once __DIR__ . '/../Classes/DiscountStrategy.php';
require_once __DIR__ . '/../Classes/CheckoutCalculator.php';

use Classes\VinylProduct;
use Classes\NoDiscountStrategy;
use Classes\BulkDiscountStrategy;
use Classes\CheckoutCalculator;

class CheckoutTest extends TestCase {

    public function testVinylProductInheritanceAndDetails() {
        // Demuestra: Herencia (VinylProduct hereda de Product)
        $vinyl = new VinylProduct(1, "Midnights", 35.99, "Taylor Swift");
        
        // Assert de precio (heredado)
        $this->assertEquals(35.99, $vinyl->getPrice(), "El precio heredado debe ser 35.99");
        
        // Assert de método sobrescrito / propio
        $expectedDetails = "Vinilo: Midnights - Artista: Taylor Swift";
        $this->assertEquals($expectedDetails, $vinyl->getDetails(), "Los detalles del vinilo deben coincidir");
    }

    public function testNoDiscountStrategy() {
        // Demuestra: Estrategia sin descuento
        $strategy = new NoDiscountStrategy();
        $calculator = new CheckoutCalculator($strategy);
        
        $total = $calculator->calculateTotal(150.00);
        $this->assertEquals(150.00, $total, "Con NoDiscountStrategy el total debe permanecer igual");
    }

    public function testBulkDiscountStrategyApplied() {
        // Demuestra: Composición y Strategy Pattern (Descuento del 10% si es > 100)
        $strategy = new BulkDiscountStrategy();
        $calculator = new CheckoutCalculator($strategy);
        
        // 150 > 100, por lo que aplica 10% descuento -> 150 * 0.9 = 135
        $total = $calculator->calculateTotal(150.00);
        $this->assertEquals(135.00, $total, "BulkDiscountStrategy debe aplicar un 10% de descuento para montos > 100");
    }

    public function testBulkDiscountStrategyNotApplied() {
        // Demuestra: Lógica condicional dentro de la estrategia
        $strategy = new BulkDiscountStrategy();
        $calculator = new CheckoutCalculator($strategy);
        
        // 90 no es > 100, por lo que NO aplica descuento -> 90
        $total = $calculator->calculateTotal(90.00);
        $this->assertEquals(90.00, $total, "BulkDiscountStrategy NO debe aplicar descuento para montos <= 100");
    }
}
