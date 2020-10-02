<?php

namespace App\Entity;

use App\Repository\InventoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=InventoryRepository::class)
 * @ORM\HasLifecycleCallbacks()
 * 
 */
class Inventory
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Please enter the inventory's name")
     */
    private $name;

    /**
     * @ORM\Column(type="float")
     */
    private $approvDelay;

    /**
     * @ORM\Column(type="float")
     */
    private $orderingFreq;

    /**
     * @ORM\Column(type="float")
     */
    private $txOfService;

    /**
     * @ORM\OneToMany(targetEntity=InventoryAvailability::class, mappedBy="inventory")
     */
    private $inventoryAvailabilities;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $managementMode;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type;

    /**
     * @ORM\ManyToOne(targetEntity=Enterprise::class, inversedBy="inventories")
     * @ORM\JoinColumn(nullable=false)
     */
    private $enterprise;

    /**
     * @ORM\OneToMany(targetEntity=Lot::class, mappedBy="inventory")
     */
    private $lots;

    /**
     * @ORM\OneToMany(targetEntity=CommercialSheet::class, mappedBy="inventory")
     */
    private $commercialSheets;

    public function __construct()
    {
        $this->inventoryAvailabilities = new ArrayCollection();
        //$this->deliveryAddresses = new ArrayCollection();
        $this->lots = new ArrayCollection();
        $this->commercialSheets = new ArrayCollection();
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

    public function getApprovDelay(): ?float
    {
        return $this->approvDelay;
    }

    public function setApprovDelay(float $approvDelay): self
    {
        $this->approvDelay = $approvDelay;

        return $this;
    }

    public function getOrderingFreq(): ?float
    {
        return $this->orderingFreq;
    }

    public function setOrderingFreq(float $orderingFreq): self
    {
        $this->orderingFreq = $orderingFreq;

        return $this;
    }

    public function getTxOfService(): ?float
    {
        return $this->txOfService;
    }

    public function setTxOfService(float $txOfService): self
    {
        $this->txOfService = $txOfService;

        return $this;
    }

    /**
     * @return Collection|InventoryAvailability[]
     */
    public function getInventoryAvailabilities(): Collection
    {
        return $this->inventoryAvailabilities;
    }

    public function addInventoryAvailability(InventoryAvailability $inventoryAvailability): self
    {
        if (!$this->inventoryAvailabilities->contains($inventoryAvailability)) {
            $this->inventoryAvailabilities[] = $inventoryAvailability;
            $inventoryAvailability->setInventory($this);
        }

        return $this;
    }

    public function removeInventoryAvailability(InventoryAvailability $inventoryAvailability): self
    {
        if ($this->inventoryAvailabilities->contains($inventoryAvailability)) {
            $this->inventoryAvailabilities->removeElement($inventoryAvailability);
            // set the owning side to null (unless already changed)
            if ($inventoryAvailability->getInventory() === $this) {
                $inventoryAvailability->setInventory(null);
            }
        }

        return $this;
    }

    public function getManagementMode(): ?string
    {
        return $this->managementMode;
    }

    public function setManagementMode(string $managementMode): self
    {
        $this->managementMode = $managementMode;

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
            $lot->setInventory($this);
        }

        return $this;
    }

    public function removeLot(Lot $lot): self
    {
        if ($this->lots->contains($lot)) {
            $this->lots->removeElement($lot);
            // set the owning side to null (unless already changed)
            if ($lot->getInventory() === $this) {
                $lot->setInventory(null);
            }
        }

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
            $commercialSheet->setInventory($this);
        }

        return $this;
    }

    public function removeCommercialSheet(CommercialSheet $commercialSheet): self
    {
        if ($this->commercialSheets->contains($commercialSheet)) {
            $this->commercialSheets->removeElement($commercialSheet);
            // set the owning side to null (unless already changed)
            if ($commercialSheet->getInventory() === $this) {
                $commercialSheet->setInventory(null);
            }
        }

        return $this;
    }
}
