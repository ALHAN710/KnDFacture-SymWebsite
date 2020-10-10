<?php

namespace App\Entity;

use App\Repository\SubscriptionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SubscriptionRepository::class)
 */
class Subscription
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="integer")
     */
    private $sheetNumber;

    /**
     * @ORM\Column(type="integer")
     */
    private $productRefNumber;

    /**
     * @ORM\OneToMany(targetEntity=Enterprise::class, mappedBy="subscription")
     */
    private $enterprises;

    /**
     * @ORM\Column(type="json")
     */
    private $tarifs = [];

    public function __construct()
    {
        $this->enterprises = new ArrayCollection();
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

    public function getSheetNumber(): ?int
    {
        return $this->sheetNumber;
    }

    public function setSheetNumber(int $sheetNumber): self
    {
        $this->sheetNumber = $sheetNumber;

        return $this;
    }

    public function getProductRefNumber(): ?int
    {
        return $this->productRefNumber;
    }

    public function setProductRefNumber(int $productRefNumber): self
    {
        $this->productRefNumber = $productRefNumber;

        return $this;
    }

    /**
     * @return Collection|Enterprise[]
     */
    public function getEnterprises(): Collection
    {
        return $this->enterprises;
    }

    public function addEnterprise(Enterprise $enterprise): self
    {
        if (!$this->enterprises->contains($enterprise)) {
            $this->enterprises[] = $enterprise;
            $enterprise->setSubscription($this);
        }

        return $this;
    }

    public function removeEnterprise(Enterprise $enterprise): self
    {
        if ($this->enterprises->contains($enterprise)) {
            $this->enterprises->removeElement($enterprise);
            // set the owning side to null (unless already changed)
            if ($enterprise->getSubscription() === $this) {
                $enterprise->setSubscription(null);
            }
        }

        return $this;
    }

    public function getTarifs(): ?array
    {
        return $this->tarifs;
    }

    public function setTarifs(array $tarifs): self
    {
        $this->tarifs = $tarifs;

        return $this;
    }
}
