<?php

namespace App\Entity;

use App\Repository\CommercialSheetItemLotRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CommercialSheetItemLotRepository::class)
 */
class CommercialSheetItemLot
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=CommercialSheetItem::class, inversedBy="commercialSheetItemLots")
     * @ORM\JoinColumn(nullable=false)
     */
    private $commercialSheetItem;

    /**
     * @ORM\ManyToOne(targetEntity=Lot::class, inversedBy="commercialSheetItemLots")
     * @ORM\JoinColumn(nullable=false)
     */
    private $lot;

    /**
     * @ORM\Column(type="integer")
     */
    private $quantity;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCommercialSheetItem(): ?CommercialSheetItem
    {
        return $this->commercialSheetItem;
    }

    public function setCommercialSheetItem(?CommercialSheetItem $commercialSheetItem): self
    {
        $this->commercialSheetItem = $commercialSheetItem;

        return $this;
    }

    public function getLot(): ?Lot
    {
        return $this->lot;
    }

    public function setLot(?Lot $lot): self
    {
        $this->lot = $lot;

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
}
