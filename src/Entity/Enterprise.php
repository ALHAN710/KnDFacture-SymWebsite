<?php

namespace App\Entity;

use DateTime;
use DateInterval;
use DateTimeZone;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\EnterpriseRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=EnterpriseRepository::class)
 * 
 * @ORM\HasLifecycleCallbacks()
 * 
 * @UniqueEntity(
 *  fields={"socialReason", "phoneNumber"},
 *  message="Une entreprise est déjà enregistrée avec ses paramètres(raison sociale et tél), veuillez les modifier svp !"
 * )
 * 
 */
class Enterprise
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Veuillez renseigner la raison sociale ou le nom")
     * @Assert\Length(max=255, minMessage="255 caractères Max !")
     */
    private $socialReason;

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
    private $address;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Veuillez renseigner le numéro de téléphone")
     */
    private $phoneNumber;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $email;

    /**
     * @ORM\OneToMany(targetEntity=User::class, mappedBy="enterprise", orphanRemoval=true)
     */
    private $users;

    /**
     * @ORM\OneToMany(targetEntity=Inventory::class, mappedBy="enterprise", orphanRemoval=true)
     */
    private $inventories;

    /**
     * @ORM\ManyToMany(targetEntity=BusinessContact::class, inversedBy="enterprises")
     */
    private $businesscontacts;

    /**
     * @ORM\OneToMany(targetEntity=Product::class, mappedBy="enterprise", orphanRemoval=true)
     */
    private $products;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $logo;

    /**
     * @ORM\Column(type="float")
     */
    private $tva;

    /**
     * @ORM\ManyToOne(targetEntity=Subscription::class, inversedBy="enterprises")
     * 
     */
    private $subscription;

    /**
     * @ORM\Column(type="integer")
     */
    private $subscriptionDuration;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $subscribeAt;

    /**
     * @ORM\OneToMany(targetEntity=Category::class, mappedBy="entreprise", orphanRemoval=true)
     */
    private $categories;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $country;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $description;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->inventories = new ArrayCollection();
        $this->businesscontacts = new ArrayCollection();
        $this->products = new ArrayCollection();
        $this->categories = new ArrayCollection();
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

    public function subscriptionDeadLine()
    {
        $nowDate = new DateTime("now");
        $periodofvalidity = new DateTime($this->subscribeAt->format('Y/m/d'));
        $periodofvalidity->add(new DateInterval('P' . $this->subscriptionDuration . 'M'));

        /*$interval = $nowDate->diff($this->subscribeAt);
        //$interval = $this->periodofvalidity->diff($nowDate);
        if ($interval) {
            //return gettype($interval->format('d'));
            return $interval->format('%R%a jours'); // '+29 days'
            //return $interval->days; //Nombre de jour total de différence entre les dates 
            //return !$interval->invert; // 
        }
        return '';*/

        return $this->formatDateDiff($nowDate, $periodofvalidity); //
    }

    /**
     * A sweet interval formatting, will use the two biggest interval parts.
     * On small intervals, you get minutes and seconds.
     * On big intervals, you get months and days.
     * Only the two biggest parts are used.
     *
     * @param DateTime $start
     * @param DateTime|null $end
     * @return string
     */
    public function formatDateDiff($start, $end = null)
    {
        if (!($start instanceof DateTime)) {
            $start = new DateTime($start);
        }

        if ($end === null) {
            $end = new DateTime();
        }

        if (!($end instanceof DateTime)) {
            $end = new DateTime($start);
        }

        $interval = $end->diff($start);
        $doPlural = function ($nb, $str) {
            if ($str !== 'Mois') return $nb > 1 ? $str . 's' : $str;
            return $str;
        }; // adds plurals

        $format = array();
        if ($interval->y !== 0) {
            $format[] = "%y " . $doPlural($interval->y, "An");
        }
        if ($interval->m !== 0) {
            $format[] = "%m " . $doPlural($interval->m, "Mois");
        }
        if ($interval->d !== 0) {
            $format[] = "%d " . $doPlural($interval->d, "Jour");
        }
        if ($interval->h !== 0) {
            $format[] = "%h " . $doPlural($interval->h, "Heure");
        }
        if ($interval->i !== 0) {
            $format[] = "%i " . $doPlural($interval->i, "Minute");
        }
        if ($interval->s !== 0) {
            if (!count($format)) {
                return "less than a minute ago";
            } else {
                $format[] = "%s " . $doPlural($interval->s, "Seconde");
            }
        }

        // We use the two biggest parts
        if (count($format) > 1) {
            $format = array_shift($format) . " et " . array_shift($format);
        } else {
            $format = array_pop($format);
        }

        // Prepend 'since ' or whatever you like
        return $interval->format($format);
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->setEnterprise($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
            // set the owning side to null (unless already changed)
            if ($user->getEnterprise() === $this) {
                $user->setEnterprise(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Inventory[]
     */
    public function getInventories(): Collection
    {
        return $this->inventories;
    }

    public function addInventory(Inventory $inventory): self
    {
        if (!$this->inventories->contains($inventory)) {
            $this->inventories[] = $inventory;
            $inventory->setEnterprise($this);
        }

        return $this;
    }

    public function removeInventory(Inventory $inventory): self
    {
        if ($this->inventories->contains($inventory)) {
            $this->inventories->removeElement($inventory);
            // set the owning side to null (unless already changed)
            if ($inventory->getEnterprise() === $this) {
                $inventory->setEnterprise(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|BusinessContact[]
     */
    public function getBusinesscontacts(): Collection
    {
        return $this->businesscontacts;
    }

    public function addBusinesscontact(BusinessContact $businesscontact): self
    {
        if (!$this->businesscontacts->contains($businesscontact)) {
            $this->businesscontacts[] = $businesscontact;
        }

        return $this;
    }

    public function removeBusinesscontact(BusinessContact $businesscontact): self
    {
        if ($this->businesscontacts->contains($businesscontact)) {
            $this->businesscontacts->removeElement($businesscontact);
        }

        return $this;
    }

    /**
     * @return Collection|Product[]
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): self
    {
        if (!$this->products->contains($product)) {
            $this->products[] = $product;
            $product->setEnterprise($this);
        }

        return $this;
    }

    public function removeProduct(Product $product): self
    {
        if ($this->products->contains($product)) {
            $this->products->removeElement($product);
            // set the owning side to null (unless already changed)
            if ($product->getEnterprise() === $this) {
                $product->setEnterprise(null);
            }
        }

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

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(?string $logo): self
    {
        $this->logo = $logo;

        return $this;
    }

    public function getTva(): ?float
    {
        return $this->tva;
    }

    public function setTva(float $tva): self
    {
        $this->tva = $tva;

        return $this;
    }

    public function getSubscription(): ?Subscription
    {
        return $this->subscription;
    }

    public function setSubscription(?Subscription $subscription): self
    {
        $this->subscription = $subscription;

        return $this;
    }

    public function getSubscriptionDuration(): ?int
    {
        return $this->subscriptionDuration;
    }

    public function setSubscriptionDuration(?int $subscriptionDuration): self
    {
        $this->subscriptionDuration = $subscriptionDuration;

        return $this;
    }

    public function getSubscribeAt(): ?\DateTimeInterface
    {
        return $this->subscribeAt;
    }

    public function setSubscribeAt(?\DateTimeInterface $subscribeAt): self
    {
        $this->subscribeAt = $subscribeAt;

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
            $category->setEntreprise($this);
        }

        return $this;
    }

    public function removeCategory(Category $category): self
    {
        if ($this->categories->contains($category)) {
            $this->categories->removeElement($category);
            // set the owning side to null (unless already changed)
            if ($category->getEntreprise() === $this) {
                $category->setEntreprise(null);
            }
        }

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): self
    {
        $this->country = $country;

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
}
