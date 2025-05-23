<?php

namespace App\Entity;

use App\Repository\VeloRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: VeloRepository::class)]
class Velo
{
    public const ETAT_DISPONIBLE = 'disponible';
    public const ETAT_EN_LOCATION = 'en_location';
    public const ETAT_EN_MAINTENANCE = 'en_maintenance';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $modele = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $type = null;

    #[ORM\Column(length: 50)]
    #[Assert\Choice(choices: [self::ETAT_DISPONIBLE, self::ETAT_EN_LOCATION, self::ETAT_EN_MAINTENANCE])]
    private ?string $etat = self::ETAT_DISPONIBLE;

    #[ORM\OneToMany(mappedBy: 'velo', targetEntity: Location::class)]
    private Collection $locations;

    public function __construct()
    {
        $this->locations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getModele(): ?string
    {
        return $this->modele;
    }

    public function setModele(string $modele): static
    {
        $this->modele = $modele;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getEtat(): ?string
    {
        return $this->etat;
    }

    public function setEtat(string $etat): static
    {
        if (!in_array($etat, [self::ETAT_DISPONIBLE, self::ETAT_EN_LOCATION, self::ETAT_EN_MAINTENANCE])) {
            throw new \InvalidArgumentException('État invalide');
        }
        $this->etat = $etat;
        return $this;
    }

    /**
     * @return Collection<int, Location>
     */
    public function getLocations(): Collection
    {
        return $this->locations;
    }

    public function addLocation(Location $location): static
    {
        if (!$this->locations->contains($location)) {
            $this->locations->add($location);
            $location->setVelo($this);
        }

        return $this;
    }

    public function removeLocation(Location $location): static
    {
        if ($this->locations->removeElement($location)) {
            if ($location->getVelo() === $this) {
                $location->setVelo(null);
            }
        }

        return $this;
    }

    public function isDisponible(): bool
    {
        return $this->etat === self::ETAT_DISPONIBLE;
    }
} 