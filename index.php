<?php

$config = require 'config.php';

function dd($data) {
    die(var_dump($data));
}

interface Buyable
{
    public function getBuyableId();

    public function getBuyableDescription();

    public function getBuyablePrice();
}

class Product implements Buyable
{
    public int $id;
    public string $description;
    public int $price;

    public function __construct($id, $description, $price)
    {
        $this->id = $id;
        $this->description = $description;
        $this->price = $price;
    }

    public function getBuyableId(): int
    {
        return $this->id;
    }

    public function getBuyablePrice(): int
    {
        return $this->price;
    }

    public function getBuyableDescription(): string
    {
        return $this->description;
    }

    public function modal(): Product
    {
        return $this;
    }
}

class Cart
{
    protected array $repo = [];

    public function add(Buyable $product, $qty = 1)
    {
        if (array_key_exists($product->getBuyableId(), $this->repo)) {
            $this->update($product, $qty);
        } else {
            $this->repo[$product->getBuyableId()] = CartItem::fromBuyableProduct($product, $qty);
        }

    }

    public function update(Buyable $product, $qty)
    {
        $this->repo[$product->getBuyableId()]->qty += $qty;
    }

    public function total(): string
    {
        $total = array_reduce($this->repo, function ($total, CartItem $cartItem) {
            return $total + ($cartItem->qty * $cartItem->price);
        }, 0);

        return number_format($total, 2, '.', '');
    }

    public function content()
    {
        return $this->repo;
    }

    public function count(): int
    {
        return array_reduce($this->repo, function ($carry, CartItem $current) {
            $carry += $current->qty;
            return $carry;
        }, 0);
    }
}

class CartItem
{
    public int $id;
    public string $description;
    public int $price;
    public $qty;
    protected int $taxRate = 10;
    protected $associatedModal = null;

    public function __construct(Buyable $item, $qty = 1)
    {
        $this->id = $item->getBuyableId();
        $this->description = $item->getBuyableDescription();
        $this->price = $item->getBuyablePrice();
        $this->qty = $qty;
        $this->associatedModal = $item->modal();
    }

    public static function fromBuyableProduct(Buyable $product, $qty): CartItem
    {
        return new self($product, $qty);
    }

    public function total()
    {
        return $this->total;
    }

    public function __get($attribute)
    {
        if ($attribute === 'total') {
            return $this->price * $this->qty;
        }
        
        if ($attribute === 'tax') {
            return $this->price * ($this->taxRate / 100);
        }
        
        if ($attribute === 'taxTotal') {
            return $this->tax * $this->qty;
        }

        if ($attribute === 'modal' && isset($this->associatedModal)) {
            return $this->associatedModal;
        }

        return null;
    }
}

$cart = new Cart();
$appleLaptop = new Product(1, 'Apple Laptop', 1200);
$lenovoLaptop = new Product(2, 'Lenovo Thinkpad', 1590);
$toaster = new Product(3, 'Toshiba Toaster', 150);

$cart->add($appleLaptop, 2); // 2400
$cart->add($lenovoLaptop, 4); // 6360
$cart->add($toaster); //yyp
$cart->add($toaster, 4); // 600

dd($cart);
