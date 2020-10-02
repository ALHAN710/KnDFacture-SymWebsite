<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use App\Repository\ProductRepository;

/**
 * @ORM\Entity(repositoryClass=ProductRepository::class)
 * @UniqueEntity(
 *  fields={"name", "sku"},
 *  message="Another product is already registered with this name or sku, please modify it"
 * )
 */
class Product
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\NotBlank(message="Please enter the name of product")
     */
    private $name;

    /**
     * @ORM\Column(type="float")
     * @Assert\NotBlank(message="Please enter the price of product")
     */
    private $price;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\NotBlank(message="Please enter the SKU of product")
     */
    private $sku;

    /**
     * @ORM\OneToMany(targetEntity=OrderItem::class, mappedBy="product")
     */
    private $orderItems;

    /**
     * 
     */
    private $instant; //@ORM\Column(type="string", length=255)

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type;

    /**
     * 
     */
    private $age; //@ORM\Column(type="integer")

    /**
     * @ORM\OneToMany(targetEntity=InventoryAvailability::class, mappedBy="product")
     */
    private $inventoryAvailabilities;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity=Enterprise::class, inversedBy="products")
     * @ORM\JoinColumn(nullable=false)
     */
    private $enterprise;

    /**
     * @ORM\OneToMany(targetEntity=Lot::class, mappedBy="product", orphanRemoval=true)
     */
    private $lots;

    public function __construct()
    {
        $this->orderItems = new ArrayCollection();
        $this->inventoryAvailabilities = new ArrayCollection();
        $this->lots = new ArrayCollection();
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

    public function getSku(): ?string
    {
        return $this->sku;
    }

    public function setSku(string $sku): self
    {
        $this->sku = $sku;

        return $this;
    }

    /**
     * @return Collection|OrderItem[]
     */
    public function getOrderItems(): Collection
    {
        return $this->orderItems;
    }

    public function addOrderItem(OrderItem $orderItem): self
    {
        if (!$this->orderItems->contains($orderItem)) {
            $this->orderItems[] = $orderItem;
            $orderItem->setProduct($this);
        }

        return $this;
    }

    public function removeOrderItem(OrderItem $orderItem): self
    {
        if ($this->orderItems->contains($orderItem)) {
            $this->orderItems->removeElement($orderItem);
            // set the owning side to null (unless already changed)
            if ($orderItem->getProduct() === $this) {
                $orderItem->setProduct(null);
            }
        }

        return $this;
    }

    public function getInstant(): ?string
    {
        return $this->instant;
    }

    public function setInstant(string $instant): self
    {
        $this->instant = $instant;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getAge(): ?int
    {
        return $this->age;
    }

    public function setAge(int $age): self
    {
        $this->age = $age;

        return $this;
    }

    /**
     * @return Collection|InventoryAvailability[]
     */
    public function getInventoryAvailability(): Collection
    {
        return $this->inventoryAvailabilities;
    }

    public function addInventoryAvailability(InventoryAvailability $inventoryAvailability): self
    {
        if (!$this->inventoryAvailabilities->contains($inventoryAvailability)) {
            $this->inventoryAvailabilities[] = $inventoryAvailability;
            $inventoryAvailability->setProduct($this);
        }

        return $this;
    }

    public function removeInventoryAvailability(InventoryAvailability $inventoryAvailability): self
    {
        if ($this->inventoryAvailabilities->contains($inventoryAvailability)) {
            $this->inventoryAvailabilities->removeElement($inventoryAvailability);
            // set the owning side to null (unless already changed)
            if ($inventoryAvailability->getProduct() === $this) {
                $inventoryAvailability->setProduct(null);
            }
        }

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getEnterprise(): ?Enterprise
    {
        return $this->enterprise;
    }

    public function setEnterprise(?Enterprise $enterprise): self
    {
        $this->enterprise = $enterprise;

        return $this;
    }

    /**
     * @return Collection|Lot[]
     */
    public function getLots(): Collection
    {
        return $this->lots;
    }

    public function addLot(Lot $lot): self
    {
        if (!$this->lots->contains($lot)) {
            $this->lots[] = $lot;
            $lot->setProduct($this);
        }

        return $this;
    }

    public function removeLot(Lot $lot): self
    {
        if ($this->lots->contains($lot)) {
            $this->lots->removeElement($lot);
            // set the owning side to null (unless already changed)
            if ($lot->getProduct() === $this) {
                $lot->setProduct(null);
            }
        }

        return $this;
    }
}
