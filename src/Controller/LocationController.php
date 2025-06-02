<?php

namespace App\Controller;

use App\Entity\Location;
use App\Entity\Velo;
use App\Form\LocationType;
use App\Repository\LocationRepository;
use App\Service\LocationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/locations')]
class LocationController extends AbstractController
{
    private $locationService;

    public function __construct(LocationService $locationService)
    {
        $this->locationService = $locationService;
    }

    #[Route('/', name: 'app_location_index', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function index(LocationRepository $locationRepository): Response
    {
        return $this->render('location/index.html.twig', [
            'locations' => $locationRepository->findAll(),
            'locations_actives' => $locationRepository->findActiveLocations(),
        ]);
    }

    #[Route('/new', name: 'app_location_new', methods: ['GET', 'POST'])]
    #[Route('/new/{velo}', name: 'app_location_new_velo', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, LocationRepository $locationRepository, Velo $velo = null): Response
    {
        $location = new Location();
        if ($velo) {
            $location->setVelo($velo);
        }

        $form = $this->createForm(LocationType::class, $location);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Validation de la location
            $erreurs = $this->locationService->validerLocation($location);
            
            if (count($erreurs) > 0) {
                foreach ($erreurs as $erreur) {
                    $this->addFlash('error', $erreur);
                }
                return $this->render('location/new.html.twig', [
                    'location' => $location,
                    'form' => $form,
                ]);
            }

            // Calcul du prix
            $prix = $this->locationService->calculerPrix($location);
            $location->setPrix($prix);

            // Mettre à jour l'état du vélo
            $velo = $location->getVelo();
            $velo->setEtat(Velo::ETAT_EN_LOCATION);

            $locationRepository->save($location, true);

            return $this->redirectToRoute('app_location_show', ['id' => $location->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('location/new.html.twig', [
            'location' => $location,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_location_show', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function show(Location $location): Response
    {
        return $this->render('location/show.html.twig', [
            'location' => $location,
        ]);
    }

    #[Route('/{id}/terminer', name: 'app_location_terminer', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function terminer(Request $request, Location $location, LocationRepository $locationRepository): Response
    {
        if ($this->isCsrfTokenValid('terminer'.$location->getId(), $request->request->get('_token'))) {
            // Mettre à jour l'état du vélo
            $velo = $location->getVelo();
            $velo->setEtat(Velo::ETAT_DISPONIBLE);

            $locationRepository->save($location, true);

            $this->addFlash('success', 'Location terminée avec succès.');
        }

        return $this->redirectToRoute('app_location_show', ['id' => $location->getId()], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}', name: 'app_location_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, Location $location, LocationRepository $locationRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$location->getId(), $request->request->get('_token'))) {
            // Remettre le vélo en état disponible si la location est en cours
            $velo = $location->getVelo();
            if ($velo->getEtat() === Velo::ETAT_EN_LOCATION) {
                $velo->setEtat(Velo::ETAT_DISPONIBLE);
            }

            $locationRepository->remove($location, true);
        }

        return $this->redirectToRoute('app_location_index', [], Response::HTTP_SEE_OTHER);
    }
} 