<?php

namespace App\Service;

use App\Entity\Location;
use App\Entity\Velo;
use App\Repository\LocationRepository;
use DateTime;

class LocationService
{
    private $locationRepository;

    public function __construct(LocationRepository $locationRepository)
    {
        $this->locationRepository = $locationRepository;
    }

    public function calculerPrix(Location $location): float
    {
        $debut = $location->getDateDebut();
        $fin = $location->getDateFin();
        $duree = $fin->diff($debut)->days + 1; // +1 car on compte le jour de début
        
        // Prix de base selon le type de vélo
        $prixBase = $this->getPrixBaseVelo($location->getVelo());
        
        // Calcul avec réduction selon la durée
        return $this->appliquerReduction($prixBase * $duree, $duree);
    }
    
    private function getPrixBaseVelo(Velo $velo): float
    {
        // Prix selon le type de vélo
        return match($velo->getType()) {
            'VTT' => 25.0,
            'ROUTE' => 30.0,
            'ELECTRIQUE' => 45.0,
            default => 20.0,
        };
    }
    
    private function appliquerReduction(float $prix, int $duree): float
    {
        // Réductions selon la durée
        if ($duree >= 7) {
            return $prix * 0.8; // -20% pour 7 jours ou plus
        }
        if ($duree >= 3) {
            return $prix * 0.9; // -10% pour 3 jours ou plus
        }
        return $prix;
    }

    public function verifierDisponibilite(Velo $velo, DateTime $debut, DateTime $fin): bool
    {
        return $this->locationRepository->isVeloAvailableForPeriod(
            $velo->getId(),
            $debut,
            $fin
        );
    }

    public function validerLocation(Location $location): array
    {
        $erreurs = [];
        
        // Vérifier que le vélo est disponible
        if (!$this->verifierDisponibilite($location->getVelo(), $location->getDateDebut(), $location->getDateFin())) {
            $erreurs[] = "Le vélo n'est pas disponible sur cette période.";
        }
        
        // Vérifier que la date de début est dans le futur
        if ($location->getDateDebut() < new DateTime()) {
            $erreurs[] = "La date de début doit être dans le futur.";
        }
        
        // Vérifier que la durée n'est pas trop longue (max 30 jours)
        $duree = $location->getDateFin()->diff($location->getDateDebut())->days;
        if ($duree > 30) {
            $erreurs[] = "La durée de location ne peut pas dépasser 30 jours.";
        }
        
        return $erreurs;
    }
} 