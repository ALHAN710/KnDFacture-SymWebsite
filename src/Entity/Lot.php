<?php

namespace App\Entity;

use DateTime;
use DateInterval;
use DateTimeZone;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\LotRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\PositiveOrZero;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=LotRepository::class)
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity(
 *  fields={"number","product","inventory", "dlc"},
 *  message="Another Lot is already saved with this informations, please change it"
 * )
 */
class Lot
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="float")
     * @Assert\PositiveOrZero
     */
    private $quantity;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Veuillez la désignation de l'élément")
     */
    private $number;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $dlc;

    /**
     * @ORM\Column(type="date")
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity=Product::class, inversedBy="lots")
     * @ORM\JoinColumn(nullable=false)
     */
    private $product;

    /**
     * @ORM\ManyToOne(targetEntity=Inventory::class, inversedBy="lots")
     * @ORM\JoinColumn(nullable=false)
     */
    private $inventory;

    /**
     * @ORM\OneToMany(targetEntity=StockMovement::class, mappedBy="lot", orphanRemoval=true)
     */
    private $stockMovements;

    /**
     * Durée de consommation du lot
     * @Assert\PositiveOrZero
     */
    private $duration;

    /**
     * @ORM\OneToMany(targetEntity=CommercialSheetItemLot::class, mappedBy="lot")
     */
    private $commercialSheetItemLots;

    public function __construct()
    {
        $this->stockMovements = new ArrayCollection();
        $this->commercialSheetItemLots = new ArrayCollection();
    }

    public function getAlert()
    {

        $nowDate = new DateTime("now");
        if ($this->dlc) {
            $this->periodofvalidity = new DateTime($this->dlc->format('Y/m/d'));
            $interval = $nowDate->diff($this->periodofvalidity);
            //$interval = $this->periodofvalidity->diff($nowDate);
            if ($interval) {
                //return gettype($interval->format('d'));
                //return $interval->format('%R%a days');// '+29 days'
                //return $interval->days; //Nombre de jour total de différence entre les dates 
                return !$interval->invert; // 
            }
        }
        return null;
    }

    /**
     * Permet d'initialiser la date limite de consommation du lot
     *
     * @ORM\PrePersist
     * @ORM\PreUpdate
     * 
     * @return void
     */
    public function initializeDlc()
    {
        // dump($this->duration);
        // if (!empty($this->duration)) {
        //     $this->dlc = new DateTime(date('Y-m-d H:i:s'), new DateTimeZone('Africa/Douala'));
        //     $this->dlc->add(new DateInterval('P' . $this->duration . 'D'));
        // }
    }
    /**
     * Permet d'initialiser la date limite de consommation du lot
     *
     * @ORM\PrePersist
     * @ORM\PreUpdate
     * 
     * @return void
     */
    public function initializeProduct()
    {
        // if (!empty($this->productId)) {
        //     $this->setProduct($this->productId);
        // }
    }
    /**
     * Permet d'initialiser l'inventaire associé au lot
     *
     * @ORM\PrePersist
     * @ORM\PreUpdate
     * 
     * @return void
     */
    public function initializeInventory()
    {
        // if (!empty($this->inventory)) {
        //     $this->setInventory($this->inventoryId);
        // }
    }

    /**
     * Permet d'initialiser la date de création de l'utilisateur
     *
     * @ORM\PrePersist
     * @ORM\PreUpdate
     * 
     * @return void
     */
    public function initializeCreatedAt()
    {
        if (empty($this->createdAt)) {
            $this->createdAt = new DateTime(date('Y-m-d H:i:s'), new DateTimeZone('Africa/Douala'));
        }
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

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(string $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function getDlc(): ?\DateTimeInterface
    {
        // if (!empty($this->duration)) {
        //     $this->dlc = new DateTime(date('Y-m-d H:i:s'), new DateTimeZone('Africa/Douala'));
        //     $this->dlc->add(new DateInterval('P' . $this->duration . 'D'));
        // }
        return $this->dlc;
    }

    public function setDlc(?\DateTimeInterface $dlc): self
    {
        $this->dlc = $dlc;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

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

    public function getInventory(): ?Inventory
    {
        return $this->inventory;
    }

    public function setInventory(?Inventory $inventory): self
    {
        $this->inventory = $inventory;

        return $this;
    }

    /**
     * @return Collection|StockMovement[]
     */
    public function getStockMovements(): Collection
    {
        return $this->stockMovements;
    }

    public function addStockMovement(StockMovement $stockMovement): self
    {
        if (!$this->stockMovements->contains($stockMovement)) {
            $this->stockMovements[] = $stockMovement;
            $stockMovement->setLot($this);
        }

        return $this;
    }

    public function removeStockMovement(StockMovement $stockMovement): self
    {
        if ($this->stockMovements->contains($stockMovement)) {
            $this->stockMovements->removeElement($stockMovement);
            // set the owning side to null (unless already changed)
            if ($stockMovement->getLot() === $this) {
                $stockMovement->setLot(null);
            }
        }

        return $this;
    }

    /**
     * Get the value of duration
     */
    public function getDuration(): ?int
    {
        //return $this->duration;
        if (!empty($this->createdAt) && !empty($this->dlc)) {
            $interval = $this->createdAt->diff($this->dlc);
            //$interval = $this->dlc->diff($this->createdAt);
            if ($interval) {
                // dump($interval->invert);
                //dump($interval->format('%R%a days'));
                // dd(gettype($interval->days));
                //dd(gettype($interval->format('d')));
                //return $interval->format('%R%a days');// '+29 days'
                //return $interval->days; //Nombre de jour total de différence entre les dates 
                //return !$interval->invert; // 

                return $interval->days;
            }
        }
        return 0;
    }

    /**
     * Set the value of duration
     *
     * @return  self
     */
    public function setDuration(int $duration)
    {

        $this->duration = $duration;

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
            $commercialSheetItemLot->setLot($this);
        }

        return $this;
    }

    public function removeCommercialSheetItemLot(CommercialSheetItemLot $commercialSheetItemLot): self
    {
        if ($this->commercialSheetItemLots->contains($commercialSheetItemLot)) {
            $this->commercialSheetItemLots->removeElement($commercialSheetItemLot);
            // set the owning side to null (unless already changed)
            if ($commercialSheetItemLot->getLot() === $this) {
                $commercialSheetItemLot->setLot(null);
            }
        }

        return $this;
    }
}
