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


    // search with name and date
    #[Route('/filter', name: 'list_event_filter_name_date')]
    public function listEvent(EventRepository $eventRepository, Request $request): Response
    {
        $searchQuery = $request->query->get('name');
        $startDate = $request->query->get('startDate');
        $endDate = $request->query->get('endDate');
        $events = [];

        if ($searchQuery && $startDate && $endDate) {
            // Filter by name and dates
            $events = $eventRepository->findByNameAndDates($searchQuery, $startDate, $endDate);
        } elseif ($startDate && $endDate) {
            // Filter by dates
            $events = $eventRepository->findEventsBetweenDates($startDate, $endDate);
        } elseif ($searchQuery) {
            // Filter by name
            $events = $eventRepository->findByName($searchQuery);
        } else {
            // Show all events
            $events = $eventRepository->findAll();
        }

        return $this->render('event/index.html.twig', [
            'event' => $events,
        ]);
    }


    // filter organizer or participate
    #[Route('/user', name: 'list_event_filter')]
    public function listEventFilterOrganizer(EventRepository $eventRepository, Request $request): Response
    {
        $participation = $request->query->get('participation');
        $user = $this->getUser();

        if ($participation === 'organizing') {
            $event = $eventRepository->findByOrganizer($user);
        } elseif ($participation === 'participating') {
            $event = $eventRepository->findEventsWithParticipant($user);
        } else {
            $event = $eventRepository->findAll();
        }

        return $this->render('event/index.html.twig', [
            'event' => $event,
            'participation' => $participation,
        ]);
    }
}
