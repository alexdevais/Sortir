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

    // Recherche par nom
    #[Route('/name', name: 'list_event_name')]
    public function listEventFilterName(EventRepository $eventRepository, Request $request): Response
    {
        $searchQuery = $request->query->get('searchQuery');
        if ($searchQuery) {
            $event = $eventRepository->findByName($searchQuery);
        } else {
            $event = [];
        }
        return $this->render('event/index.html.twig', [
            'event' => $event,
            'searchTerm' => $searchQuery,
        ]);
    }

    // Recherche par date
    #[Route('/date', name: 'list_event_date')]
    public function listEventFilterDate(EventRepository $eventRepository, Request $request): Response
    {
        // Recherche par dates
        $startDate = $request->query->get('startDate');
        $endDate = $request->query->get('endDate');
        $event = [];

        if ($startDate && $endDate) {
            $event = $eventRepository->findEventsBetweenDates($startDate, $endDate);
        }

        return $this->render('event/index.html.twig', [
            'event' => $event, // Note: Use 'events' instead of 'event' for clarity

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

    // TODO filtre event ou je ne participe pas
//    #[Route('/not_participant', name: 'list_event_not_participant')]
//    public function listEventFilterNotParticipant(EventRepository $eventRepository, Request $request): Response
//    {
//        // Vérification de la case "isParticipating"
//        $isNotParticipating = !$request->query->get('isNotParticipating');
//        $event = [];
//
//        if ($isNotParticipating) {
//            $loggedInUser = $this->getUser();
//            $event = $eventRepository->findEventsWithoutParticipant($loggedInUser);
//        }
//
//        return $this->render('event/index.html.twig', [
//            'event' => $event,
//            'isNotParticipating' => $isNotParticipating,
//        ]);
//    }
//


}
