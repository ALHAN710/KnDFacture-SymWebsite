<?php

namespace App\Entity;

use DateTime;
use DateInterval;
use DateTimeZone;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use App\Repository\CommercialSheetRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints as Assert;

//use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
//@ORM\Table(name="`order`")
/**
 * @ORM\Entity(repositoryClass=CommercialSheetRepository::class)
 * 
 * @ORM\HasLifecycleCallbacks()
 * 
 */
class CommercialSheet
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    private $number; //@Assert\NotBlank(message="Please enter the order number")

    /**
     * @ORM\Column(type="boolean")
     */
    private $paymentStatus;

    /**
     * @ORM\Column(type="boolean")
     */
    private $completedStatus;

    /**
     * @ORM\Column(type="boolean")
     */
    private $deliveryStatus;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="commercialSheets")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="float")
     * @Assert\PositiveOrZero
     */
    private $itemsReduction;

    /**
     * @ORM\Column(type="float")
     * @Assert\PositiveOrZero
     */
    private $fixReduction;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $completedAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $deliverAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $payAt;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Please enter the type of commercial sheet")
     */
    private $type;

    private $periodofvalidity;

    /**
     * @ORM\Column(type="float")
     * @Assert\PositiveOrZero
     */
    private $deliveryFees;

    /**
     * @ORM\ManyToOne(targetEntity=BusinessContact::class, inversedBy="commercialSheets")
     * @ORM\JoinColumn(nullable=false)
     */
    private $businessContact;

    /**
     * @ORM\ManyToOne(targetEntity=Inventory::class, inversedBy="commercialSheets")
     */
    private $inventory;

    /**
     * @ORM\OneToMany(targetEntity=StockMovement::class, mappedBy="commercialSheet")
     */
    private $stockMovements;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\PositiveOrZero
     */
    private $duration;

    /**
     * @ORM\ManyToMany(targetEntity=CommercialSheetItem::class, mappedBy="commercialSheet")
     * @Assert\Valid()
     */
    private $commercialSheetItems;

    /**
     * @ORM\Column(type="boolean")
     */
    private $convertFlag;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $convertAt;

    /**
     * @ORM\Column(type="float")
     */
    private $advancePayment;

    public function __construct()
    {
        //$this->orderItems = new ArrayCollection();
        //$this->reductions = new ArrayCollection();
        $this->stockMovements = new ArrayCollection();
        $this->commercialSheetItems = new ArrayCollection();
    }

    public function getAmount(): float
    {
        //totalAmount = itemsAmountSubTotal + deliveryFees + taxes - totalPromoAmount;
        $totalPromoAmount = $this->getItemsReduction() + $this->getFixReduction();
        $itemsAmountSubTotal = 0.0;
        $items = $this->getCommercialSheetItems();
        foreach ($items as $item) {
            $itemsAmountSubTotal += ($item->getQuantity() * $item->getPu());
        }

        $taxes = ($this->getUser()->getEnterprise()->getTva() * $itemsAmountSubTotal) / 100.0;
        $totalAmount = $itemsAmountSubTotal + $this->getDeliveryFees() + $taxes - $totalPromoAmount - $this->getAdvancePayment();

        return $totalAmount;
    }

    /**
     * Permet d'initialiser le status de livraison de la commande à false
     *
     * @ORM\PrePersist
     * @ORM\PreUpdate
     * 
     * @return void
     */
    public function initializeAdvancePayment()
    {
        if ($this->getCompletedStatus() == true) {
            $this->setAdvancePayment($this->getAmount());
        }
    }

    /**
     * Permet d'initialiser le status de livraison de la commande à false
     *
     * @ORM\PrePersist
     * @ORM\PreUpdate
     * 
     * @return void
     */
    public function initializeConvertFlag()
    {
        if (empty($this->convertFlag)) {
            $this->convertFlag = false;
        }
    }

    /**
     * Permet d'initialiser le status de livraison de la commande à false
     *
     * @ORM\PrePersist
     * @ORM\PreUpdate
     * 
     * @return void
     */
    public function initializeDeliveryStatus()
    {
        if (empty($this->deliveryStatus)) {
            $this->deliveryStatus = false;
        }
    }

    /**
     * Permet d'initialiser le status completed de la commande à false
     *
     * @ORM\PrePersist
     * @ORM\PreUpdate
     * 
     * @return void
     */
    public function initializeCompletedStatus()
    {
        if (empty($this->completedStatus)) {
            $this->completedStatus = false;
        }
    }

    /**
     * Permet d'initialiser le status de paiement de la commande à false
     *
     * @ORM\PrePersist
     * @ORM\PreUpdate
     * 
     * @return void
     */
    public function initializePaymentStatus()
    {
        if (empty($this->paymentStatus)) {
            $this->paymentStatus = false;
        }
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

    public function getNumber(): ?string
    {
        //date('Y') . date('m') . date('d');
        return $this->getCreatedAt()->format('Y') . $this->getCreatedAt()->format('m') . $this->getCreatedAt()->format('d') . $this->getId() . '';
    }

    public function setNumber(string $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function getPaymentStatus(): ?bool
    {
        return $this->paymentStatus;
    }

    public function setPaymentStatus(bool $paymentStatus): self
    {
        $this->paymentStatus = $paymentStatus;

        return $this;
    }

    public function getCompletedStatus(): ?bool
    {
        return $this->completedStatus;
    }

    public function setCompletedStatus(bool $completedStatus): self
    {
        $this->completedStatus = $completedStatus;

        return $this;
    }

    public function getDeliveryStatus(): ?bool
    {
        return $this->deliveryStatus;
    }

    public function setDeliveryStatus(bool $deliveryStatus): self
    {
        $this->deliveryStatus = $deliveryStatus;

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getItemsReduction(): ?float
    {
        return $this->itemsReduction;
    }

    public function setItemsReduction(float $itemsReduction): self
    {
        $this->itemsReduction = $itemsReduction;

        return $this;
    }

    public function getFixReduction(): ?float
    {
        return $this->fixReduction;
    }

    public function setFixReduction(float $fixReduction): self
    {
        $this->fixReduction = $fixReduction;

        return $this;
    }

    public function getCompletedAt(): ?\DateTimeInterface
    {
        return $this->completedAt;
    }

    public function setCompletedAt(?\DateTimeInterface $completedAt): self
    {
        $this->completedAt = $completedAt;

        return $this;
    }

    public function getDeliverAt(): ?\DateTimeInterface
    {
        return $this->deliverAt;
    }

    public function setDeliverAt(?\DateTimeInterface $deliverAt): self
    {
        $this->deliverAt = $deliverAt;

        return $this;
    }

    public function getPayAt(): ?\DateTimeInterface
    {
        return $this->payAt;
    }

    public function setPayAt(?\DateTimeInterface $payAt): self
    {
        $this->payAt = $payAt;

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

    public function getPeriodofvalidity(): ?\DateTimeInterface
    {
        if (!empty($this->duration)) {
            $this->periodofvalidity = new DateTime($this->createdAt->format('Y/m/d'));
            $this->periodofvalidity->add(new DateInterval('P' . $this->duration . 'D'));
            if ($this->periodofvalidity) return $this->periodofvalidity;
        }
        return null;
    }

    public function getAlert()
    {

        $nowDate = new DateTime("now");
        $this->periodofvalidity = new DateTime($this->createdAt->format('Y/m/d'));
        $this->periodofvalidity->add(new DateInterval('P' . $this->duration . 'D'));
        $interval = $nowDate->diff($this->periodofvalidity);
        //$interval = $this->periodofvalidity->diff($nowDate);
        if ($interval) {
            //return gettype($interval->format('d'));
            //return $interval->format('%R%a days');// '+29 days'
            //return $interval->days; //Nombre de jour total de différence entre les dates 
            return !$interval->invert; // 
        }
        return null;
    }

    public function getDeliveryFees(): ?float
    {
        return $this->deliveryFees;
    }

    public function setDeliveryFees(float $deliveryFees): self
    {
        $this->deliveryFees = $deliveryFees;

        return $this;
    }

    public function getBusinessContact(): ?BusinessContact
    {
        return $this->businessContact;
    }

    public function setBusinessContact(?BusinessContact $businessContact): self
    {
        $this->businessContact = $businessContact;

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
            $stockMovement->setCommercialSheet($this);
        }

        return $this;
    }

    public function removeStockMovement(StockMovement $stockMovement): self
    {
        if ($this->stockMovements->contains($stockMovement)) {
            $this->stockMovements->removeElement($stockMovement);
            // set the owning side to null (unless already changed)
            if ($stockMovement->getCommercialSheet() === $this) {
                $stockMovement->setCommercialSheet(null);
            }
        }

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(?int $duration): self
    {
        $this->duration = $duration;

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
            $commercialSheetItem->addCommercialSheet($this);
        }

        return $this;
    }

    public function removeCommercialSheetItem(CommercialSheetItem $commercialSheetItem): self
    {
        if ($this->commercialSheetItems->contains($commercialSheetItem)) {
            $this->commercialSheetItems->removeElement($commercialSheetItem);
            $commercialSheetItem->removeCommercialSheet($this);
        }

        return $this;
    }

    public function getConvertFlag(): ?bool
    {
        return $this->convertFlag;
    }

    public function setConvertFlag(bool $convertFlag): self
    {
        $this->convertFlag = $convertFlag;

        return $this;
    }

    public function getConvertAt(): ?\DateTimeInterface
    {
        return $this->convertAt;
    }

    public function setConvertAt(?\DateTimeInterface $convertAt): self
    {
        $this->convertAt = $convertAt;

        return $this;
    }

    public function getAdvancePayment(): ?float
    {
        return $this->advancePayment;
    }

    public function setAdvancePayment(float $advancePayment): self
    {
        $this->advancePayment = $advancePayment;

        return $this;
    }
}
