<?php
namespace Classes;

abstract class Product {
    protected $id;
    protected $name;
    protected $price;

    public function __construct($id, $name, $price) {
        $this->id = $id;
        $this->name = $name;
        $this->price = $price;
    }

    public function getPrice() {
        return $this->price;
    }

    abstract public function getDetails();
}
