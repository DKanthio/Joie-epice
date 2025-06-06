<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private float $price;

    #[ORM\Column(type: 'string', length: 255)]
    private string $image;

    #[ORM\Column(type: 'text')]
    private string $description;

    #[ORM\Column(type: 'integer')]
    private int $stock = 2;

    #[ORM\OneToMany(targetEntity: Cart::class, mappedBy: 'product')]
    private Collection $carts;

    public function __construct()
    {
        $this->carts = new ArrayCollection();
    }

    public static function getDefaultProducts(): array
    {
        return [
            ['id' => 1, 'name' => 'Mélange d’épice', 'price' => 10, 'image' => '1.jpg', 'description' => "Un mélange d'épices unique et aromatique, combinant la douceur de la cannelle et de la vanille, les notes épicées des poivres, et la fraîcheur du curcuma et du gingembre, rehaussé par une sélection d’autres épices fines pour des saveurs riches et équilibrées."],
            ['id' => 2, 'name' => 'Fakou', 'price' => 4, 'image' => '2.jpg', 'description' => "Le fakou est une épice traditionnelle utilisée principalement au Niger et dans certaines régions d’Afrique de l’Ouest. Ce mélange est composé de diverses épices et herbes locales, séchées et réduites en poudre. L'ingrédient principal du fakou est souvent une plante locale comme le Boscia senegalensis, dont les feuilles apportent une saveur distincte légèrement amère et terreuse."],
            ['id' => 3, 'name' => 'Mélange d’épice', 'price' => 5, 'image' => '3.jpg', 'description' => "Un mélange d'épices unique et aromatique, combinant la douceur de la cannelle et de la vanille, les notes épicées des poivres, et la fraîcheur du curcuma et du gingembre, rehaussé par une sélection d’autres épices fines pour des saveurs riches et équilibrées."],
           
        ];
    }

    public static function createProducts(): array
    {
        $products = [];
        foreach (self::getDefaultProducts() as $data) {
            $product = new Product();
            $product->setName($data['name']);
            $product->setPrice($data['price']);
            $product->setImage($data['image']);
            $product->setDescription($data['description']);

            $product->setStock(3);

          

            $products[] = $product;
        }
        return $products;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;
        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getStock(): int
    {
        return $this->stock;
    }

    public function setStock(int $stock): self
    {
        $this->stock = $stock;
        return $this;
    }

    public function getCarts(): Collection
    {
        return $this->carts;
    }

    public function addCart(Cart $cart): self
    {
        if (!$this->carts->contains($cart)) {
            $this->carts[] = $cart;
            $cart->setProduct($this);
        }
        return $this;
    }

    public function removeCart(Cart $cart): self
    {
        if ($this->carts->removeElement($cart)) {
            if ($cart->getProduct() === $this) {
                $cart->setProduct(null);
            }
        }
        return $this;
    }

}
