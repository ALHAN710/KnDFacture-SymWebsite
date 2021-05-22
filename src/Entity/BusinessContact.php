<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use App\Repository\BusinessContactRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


/**
 * @ORM\Entity(repositoryClass=BusinessContactRepository::class)
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity(
 *  fields={"socialReason","phoneNumber","address"},
 *  message="Another business contact is already registered with this informations, please change its"
 * )
 */
class BusinessContact
{ //  
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Please enter the BusinessContact's phone number")
     */
    private $phoneNumber;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $address;


    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $niu;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $rccm;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $socialReason;

    /**
     * @ORM\OneToMany(targetEntity=CommercialSheet::class, mappedBy="businessContact", orphanRemoval=true)
     */
    private $commercialSheets;

    /**
     * @ORM\ManyToMany(targetEntity=Enterprise::class, mappedBy="businesscontacts")
     */
    private $enterprises;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=500, nullable=true)
     */
    private $moreInfos;

    /**
     * Permet d'initialiser la date de création de l'utilisateur
     *
     * @ORM\PrePersist
     * @ORM\PreUpdate
     * 
     * @return void
     */
    public function initializecreatedAt()
    {
        if (empty($this->createdAt)) {
            $this->createdAt = new DateTime(date('Y-m-d H:i:s'));
        }
    }

    /**
     * Fonction de récupération du nom complet du customer
     *
     * @return string|null
     */
    public function getFullName() //: ?string
    {
        // if (!empty($this->getFirstname())) {
        //     if (!empty($this->getLastname())) {
        //         return $this->getFirstname() . ' ' . $this->getLastname();
        //     } else {
        //         return $this->getFirstname();
        //     }
        // } else if (!empty($this->getLastname())) {
        //     return $this->getLastname();
        // } else {
        //     return ' - ';
        // }
    }

    public function __construct()
    {
        //$this->deliveryAddresses = new ArrayCollection();
        //$this->orders = new ArrayCollection();
        $this->enterprises = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(string $phoneNumber): self
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

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

    public function getNiu(): ?string
    {
        return $this->niu;
    }

    public function setNiu(?string $niu): self
    {
        $this->niu = $niu;

        return $this;
    }

    public function getRccm(): ?string
    {
        return $this->rccm;
    }

    public function setRccm(?string $rccm): self
    {
        $this->rccm = $rccm;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getSocialReason(): ?string
    {
        return $this->socialReason;
    }

    public function setSocialReason(string $socialReason): self
    {
        $this->socialReason = $socialReason;

        return $this;
    }

    /**
     * @return Collection|CommercialSheets[]
     */
    public function getCommercialSheets(): Collection
    {
        return $this->commercialSheets;
    }

    public function addCommercialSheet(CommercialSheet $commercialSheet): self
    {
        if (!$this->commercialSheets->contains($commercialSheet)) {
            $this->commercialSheets[] = $commercialSheet;
            $commercialSheet->setBusinessContact($this);
        }

        return $this;
    }

    public function removeCommercialSheet(CommercialSheet $commercialSheet): self
    {
        if ($this->commercialSheets->contains($commercialSheet)) {
            $this->commercialSheets->removeElement($commercialSheet);
            // set the owning side to null (unless already changed)
            if ($commercialSheet->getBusinessContact() === $this) {
                $commercialSheet->setBusinessContact(null);
            }
        }

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
            $enterprise->addBusinesscontact($this);
        }

        return $this;
    }

    public function removeEnterprise(Enterprise $enterprise): self
    {
        if ($this->enterprises->contains($enterprise)) {
            $this->enterprises->removeElement($enterprise);
            $enterprise->removeBusinesscontact($this);
        }

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

    public function getMoreInfos(): ?string
    {
        return $this->moreInfos;
    }

    public function setMoreInfos(?string $moreInfos): self
    {
        $this->moreInfos = $moreInfos;

        return $this;
    }
}
