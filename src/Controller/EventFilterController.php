<?php

namespace App\Controller;

use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
#[Route('/event')]
class EventFilterController extends AbstractController
{

    // Recherche par nom et date
    #[Route('/filter', name: 'list_event_filter_name_date')]
    public function listEvent(EventRepository $eventRepository, Request $request): Response
    {
        $searchQuery = $request->query->get('searchQuery');
        $startDate = $request->query->get('startDate');
        $endDate = $request->query->get('endDate');
        $location = $request->query->get('location');

        $event = [];

        if ($searchQuery && $startDate && $endDate && $location) {
            // Rechercher par nom et dates
             $event = $eventRepository->findByNameAndDates($searchQuery, $startDate, $endDate);
        } elseif ($startDate && $endDate) {
            // Rechercher par dates
            $event = $eventRepository->findEventsBetweenDates($startDate, $endDate);
        } elseif ($searchQuery) {
            // Rechercher par nom
            $event = $eventRepository->findByName($searchQuery);
        } elseif ($location) {
            // Rechercher par location
            $event = $eventRepository->findEventByLocation($location);
        } else {
            // Afficher tous les événements
            $event = $eventRepository->findAll();
        }

        return $this->render('event/index.html.twig', [
            'event' => $event,
        ]);
    }


    // filtre j'organise ou je participe
    #[Route('/organizer', name: 'list_event_filter')]
    public function listEventFilterOrganizer(EventRepository $eventRepository, Request $request): Response
    {
        $participation = $request->query->get('participation');
        $user = $this->getUser();

        if ($participation === 'organizing') {
            $event = $eventRepository->findByOrganizer($user);
        } elseif ($participation === 'participating') {
            $event = $eventRepository->findEventsWithParticipant($user);
        } else {
            // If no filter or invalid filter, handle as needed
            $event = $eventRepository->findAll(); // Example for fetching all events
        }

        return $this->render('event/index.html.twig', [
            'event' => $event, // Pass events to template
            'participation' => $participation, // Pass participation for radio checks
        ]);
    }
}
