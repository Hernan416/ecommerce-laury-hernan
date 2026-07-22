<?php
namespace Classes;

require_once __DIR__ . '/Product.php';

class VinylProduct extends Product {
    private $artist;

    public function __construct($id, $name, $price, $artist) {
        parent::__construct($id, $name, $price);
        $this->artist = $artist;
    }

    public function getDetails() {
        return "Vinilo: {$this->name} - Artista: {$this->artist}";
    }
}
