<?php

namespace App\Entity;

use App\Repository\OrderItemRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


/**
 * @ORM\Entity(repositoryClass=OrderItemRepository::class)
 * @UniqueEntity(
 *  fields={"product","quantity"},
 *  message="A reduction is already registered with this number, please modify it"
 * )
 */
class OrderItem
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="float")
     * @Assert\Type(type="float", message = "The value {{ value }} must be type {{ type }}")
     */
    private $quantity;

    /**
     * @ORM\ManyToOne(targetEntity=Product::class, inversedBy="orderItems")
     * @ORM\JoinColumn(nullable=false)
     */
    private $product;

    private $offerIn;

    private $available;

    private $price;

    private $priceView;

    private $priceIn;

    private $sku;

    private $offerType;

    private $offerTypeIn;

    private $amount;

    /**
     * @ORM\ManyToMany(targetEntity=CommercialSheet::class, inversedBy="orderItems")
     */
    private $commercialSheets;

    public function __construct()
    {
        //$this->commercialSheet_ = new ArrayCollection();
        $this->commercialSheets = new ArrayCollection();
    }

    public function getOfferIn(): ?string
    {
        return $this->offerIn;
    }

    public function setOfferIn(string $offerIn): self
    {
        $this->offerIn = $offerIn;

        return $this;
    }

    public function getAvailable(): ?int
    {
        return $this->available;
    }

    public function setAvailable(int $available): self
    {
        $this->available = $available;

        return $this;
    }

    public function getPrice(): ?Product
    {
        return $this->price;
    }

    public function setPrice(?Product $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getPriceView(): ?float
    {
        return $this->priceView;
    }

    public function setPriceView(?float $priceView): self
    {
        $this->priceView = $priceView;

        return $this;
    }

    public function getPriceIn(): ?float
    {
        return $this->priceIn;
    }

    public function setPriceIn(?float $priceIn): self
    {
        $this->priceIn = $priceIn;

        return $this;
    }

    public function getSku(): ?Product
    {
        return $this->sku;
    }

    public function setSku(?Product $sku): self
    {
        $this->sku = $sku;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getOfferTypeIn(): ?string
    {
        return $this->offerTypeIn;
    }

    public function setOfferTypeIn(string $offerTypeIn): self
    {
        $this->offerTypeIn = $offerTypeIn;

        return $this;
    }

    public function getOfferType(): ?Product
    {
        return $this->offerType;
    }

    public function setOfferType(?Product $offerType): self
    {
        $this->offerType = $offerType;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuantity(): ?float
    {
        return $this->quantity;
    }

    public function setQuantity(float $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;

        return $this;
    }

    /**
     * @return Collection|CommercialSheet[]
     */
    public function getCommercialSheets(): Collection
    {
        return $this->commercialSheets;
    }

    public function addCommercialSheet(CommercialSheet $commercialSheet): self
    {
        if (!$this->commercialSheets->contains($commercialSheet)) {
            $this->commercialSheets[] = $commercialSheet;
        }

        return $this;
    }

    public function removeCommercialSheet(CommercialSheet $commercialSheet): self
    {
        if ($this->commercialSheets->contains($commercialSheet)) {
            $this->commercialSheets->removeElement($commercialSheet);
        }

        return $this;
    }
}
