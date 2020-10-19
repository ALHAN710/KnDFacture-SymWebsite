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
     * @ORM\Column(type="string", length=255, unique=true, nullable=true)
     * @Assert\NotBlank(message="Please enter the SKU of product")
     */
    private $sku;

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

    /**
     * @ORM\OneToMany(targetEntity=CommercialSheetItem::class, mappedBy="product")
     */
    private $commercialSheetItems;

    /**
     * @ORM\Column(type="boolean")
     */
    private $hasStock;

    /**
     * @ORM\ManyToMany(targetEntity=Category::class, mappedBy="products")
     */
    private $categories;

    private $product;
    private $productName;
    private $productPrice;
    private $productSku;
    private $productDescription;
    private $productHasStock;

    public function __construct()
    {
        //$this->orderItems = new ArrayCollection();
        $this->inventoryAvailabilities = new ArrayCollection();
        $this->lots = new ArrayCollection();
        $this->commercialSheetItems = new ArrayCollection();
        $this->categories = new ArrayCollection();
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

    /**
     * @return Collection|CommercialSheetItem[]
     */
    public function getCommercialSheetItems(): Collection
    {
        return $this->commercialSheetItems;
    }

    public function addCommercialSheetItem(CommercialSheetItem $commercialSheetItem): self
    {
        if (!$this->commercialSheetItems->contains($commercialSheetItem)) {
            $this->commercialSheetItems[] = $commercialSheetItem;
            $commercialSheetItem->setProduct($this);
        }

        return $this;
    }

    public function removeCommercialSheetItem(CommercialSheetItem $commercialSheetItem): self
    {
        if ($this->commercialSheetItems->contains($commercialSheetItem)) {
            $this->commercialSheetItems->removeElement($commercialSheetItem);
            // set the owning side to null (unless already changed)
            if ($commercialSheetItem->getProduct() === $this) {
                $commercialSheetItem->setProduct(null);
            }
        }

        return $this;
    }

    public function getHasStock(): ?bool
    {
        return $this->hasStock;
    }

    public function setHasStock(?bool $hasStock): self
    {
        $this->hasStock = $hasStock;

        return $this;
    }

    /**
     * @return Collection|Category[]
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): self
    {
        if (!$this->categories->contains($category)) {
            $this->categories[] = $category;
            $category->addProduct($this);
        }

        return $this;
    }

    public function removeCategory(Category $category): self
    {
        if ($this->categories->contains($category)) {
            $this->categories->removeElement($category);
            $category->removeProduct($this);
        }

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(Product $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getProductName(): ?Product
    {
        return $this->productName;
    }

    public function setProductName(Product $productName): self
    {
        $this->productName = $productName;

        return $this;
    }

    public function getProductPrice(): ?Product
    {
        return $this->productPrice;
    }

    public function setProductPrice(Product $productPrice): self
    {
        $this->productPrice = $productPrice;

        return $this;
    }

    public function getProductSku(): ?Product
    {
        return $this->productSku;
    }

    public function setProductSku(Product $productSku): self
    {
        $this->productSku = $productSku;

        return $this;
    }

    public function getProductDescription(): ?Product
    {
        return $this->productDescription;
    }

    public function setProductDescription(Product $productDescription): self
    {
        $this->productDescription = $productDescription;

        return $this;
    }

    public function getProductHasStock(): ?Product
    {
        return $this->productHasStock;
    }

    public function setProductHasStock(Product $productHasStock): self
    {
        $this->productHasStock = $productHasStock;

        return $this;
    }
}
