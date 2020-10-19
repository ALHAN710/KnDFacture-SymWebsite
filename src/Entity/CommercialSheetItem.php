<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Repository\CommercialSheetItemRepository;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints as Assert;
//use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
//@UniqueEntity(
// *  fields={"pu","quantity","designation"},
// *  message="Another CommercialSheetItem is already saved with this informations, please change it"
// * )

/** 
 * @ORM\Entity(repositoryClass=CommercialSheetItemRepository::class)
 * 
 */
class CommercialSheetItem
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="float")
     * @Assert\Positive
     */
    private $pu;

    /**
     * @ORM\Column(type="integer")
     * @Assert\Positive
     */
    private $quantity;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Veuillez la désignation de l'élément")
     */
    private $designation;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * 
     */
    private $reference;

    /**
     * @ORM\ManyToMany(targetEntity=CommercialSheet::class, inversedBy="commercialSheetItems")
     */
    private $commercialSheet;

    /**
     * @ORM\ManyToOne(targetEntity=Product::class, inversedBy="commercialSheetItems")
     */
    private $product;

    private $productPrice;

    private $productSku;

    private $productType;

    private $amount;

    private $available;

    private $isChanged;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $itemOfferType;

    /**
     * @ORM\OneToMany(targetEntity=CommercialSheetItemLot::class, mappedBy="commercialSheetItem")
     */
    private $commercialSheetItemLots;

    public function __construct()
    {
        $this->commercialSheet = new ArrayCollection();
        $this->commercialSheetItemLots = new ArrayCollection();
    }

    public function getProductPrice(): ?Product
    {
        return $this->productPrice;
    }

    public function setProductPrice(?Product $productPrice): self
    {
        $this->productPrice = $productPrice;

        return $this;
    }

    public function getProductSku(): ?Product
    {
        return $this->productSku;
    }

    public function setProductSku(?Product $productSku): self
    {
        $this->productSku = $productSku;

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

    public function getAvailable(): ?int
    {
        return $this->available;
    }

    public function setAvailable(int $available): self
    {
        $this->available = $available;

        return $this;
    }

    public function getIsChanged(): ?int
    {
        return $this->isChanged;
    }

    public function setIsChanged(int $isChanged): self
    {
        $this->isChanged = $isChanged;

        return $this;
    }

    public function getProductType(): ?Product
    {
        return $this->productType;
    }

    public function setProductType(?Product $productType): self
    {
        $this->productType = $productType;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPu(): ?float
    {
        return $this->pu;
    }

    public function setPu(float $pu): self
    {
        $this->pu = $pu;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getDesignation(): ?string
    {
        return $this->designation;
    }

    public function setDesignation(string $designation): self
    {
        $this->designation = $designation;

        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(?string $reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    /**
     * @return Collection|CommercialSheet[]
     */
    public function getCommercialSheet(): Collection
    {
        return $this->commercialSheet;
    }

    public function addCommercialSheet(CommercialSheet $commercialSheet): self
    {
        if (!$this->commercialSheet->contains($commercialSheet)) {
            $this->commercialSheet[] = $commercialSheet;
        }

        return $this;
    }

    public function removeCommercialSheet(CommercialSheet $commercialSheet): self
    {
        if ($this->commercialSheet->contains($commercialSheet)) {
            $this->commercialSheet->removeElement($commercialSheet);
        }

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

    public function getItemOfferType(): ?string
    {
        return $this->itemOfferType;
    }

    public function setItemOfferType(string $itemOfferType): self
    {
        $this->itemOfferType = $itemOfferType;

        return $this;
    }

    /**
     * @return Collection|CommercialSheetItemLot[]
     */
    public function getCommercialSheetItemLots(): Collection
    {
        return $this->commercialSheetItemLots;
    }

    public function addCommercialSheetItemLot(CommercialSheetItemLot $commercialSheetItemLot): self
    {
        if (!$this->commercialSheetItemLots->contains($commercialSheetItemLot)) {
            $this->commercialSheetItemLots[] = $commercialSheetItemLot;
            $commercialSheetItemLot->setCommercialSheetItem($this);
        }

        return $this;
    }

    public function removeCommercialSheetItemLot(CommercialSheetItemLot $commercialSheetItemLot): self
    {
        if ($this->commercialSheetItemLots->contains($commercialSheetItemLot)) {
            $this->commercialSheetItemLots->removeElement($commercialSheetItemLot);
            // set the owning side to null (unless already changed)
            if ($commercialSheetItemLot->getCommercialSheetItem() === $this) {
                $commercialSheetItemLot->setCommercialSheetItem(null);
            }
        }

        return $this;
    }
}
