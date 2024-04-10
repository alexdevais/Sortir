<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventType;
use App\Helpers\CallApiService;
use App\Repository\EventRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[Route('/event')]
#[IsGranted('ROLE_USER')]
class EventController extends AbstractController
{

    // Affichage de la liste d'event
    #[Route('/', name: 'list_event')]
    public function listEvent(EventRepository $eventRepository): Response
    {
        // event de moins d'un mois
        $today = new DateTime(); // Get today's date
        $oneMonthAgo = $today->modify('-1 month');
        $events = $eventRepository->findByCreatedDateAfter($oneMonthAgo);

        return $this->render('event/index.html.twig', [
            'event' => $events,
        ]);
    }

    // Création d'event
    #[Route('/create', name: 'create_event')]
    public function createEvent(Request $request, EntityManagerInterface $em, CallApiService $callApiService): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $event = new Event();
        $eventForm = $this->createForm(EventType::class, $event);
        $eventForm->handleRequest($request);

        // setup la date de creation
        $event->setCreatedDate(new DateTime('now'));

        if ($eventForm->isSubmitted() && $eventForm->isSubmitted()) {

            $newEvent = $eventForm->getData();
            $user = $this->getUser();

            // recup l'id du user pour l'associé a un organizer
            $event->setOrganizer($user);
            $em->persist($newEvent);
            $em->flush();

            $this->addFlash('success', 'Event created !');
            return $this->redirectToRoute('list_event');
        }


        return $this->render('event/event.html.twig', [
            'form' => $eventForm,
        ]);
    }

    // Affichage detail event
    #[Route('/detail/{id}', name: 'detail_event')]
    public function detailEvent(int $id, EventRepository $eventRepository): Response
    {
        $event = $eventRepository->find($id);

        // recup user qui participe a l'envent
        $user = $this->getUser();
        $participantId = $user->getId();
        $participant = $eventRepository->FindParticipantById($participantId);

        return $this->render('event/detail.html.twig', [
            'event' => $event,
            'user' => $user,
            'participant' => $participant,
        ]);

    }


    // changer l'etat d'un event pour le cancel + motif d'annulation
    #[Route('/detail/{id}/cancel', name: 'cancel_event')]
    public function cancelEvent(int $id, EntityManagerInterface $em, EventRepository $eventRepository, Request $request): Response
    {
        $event = $eventRepository->find($id);
        $user = $this->getUser();
        $motif = $request->request->get('motif');

        // Vérifier si l'utilisateur est l'organisateur de l'événement
        if (!$this->isGranted('ROLE_USER') && $event->getOrganizer() !== $this->getUser() || !$this->isGranted('ROLE_ADMIN')) {

            // Vérifier si la date de début de l'événement est passée
            if ($event->getFirstAirDate() < new \DateTime()) {
                $this->addFlash('error', 'The event has already started and cannot be canceled.');
            }
            // Vérifier si un motif a été rempli
            if (empty($motif)) {
                $this->addFlash('error', 'Please provide a reason for canceling the event.');
                return $this->redirectToRoute('detail_event', ['id' => $event->getId()]);
            }
        }
        $event->setState('CANCELLED');
        $event->setCancelReason($motif);
        $event = $em->flush();
        return $this->redirectToRoute('list_event');

        $this->addFlash('success', 'The event has been successfully canceled.');

        return $this->render('event/detail.html.twig', [
            'event' => $event,
            'user' => $user,
        ]);
    }

    // register et unregister a un event
    #[Route('/inscription/{id}', name: 'inscription_event')]
    public function inscriptionEvent(int $id, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('login');
        }

        $event = $em->getRepository(Event::class)->find($id);
        if (!$event) {
            throw $this->createNotFoundException('Event not found!');
        }

        // ne peux pas s'inscrire si l'event n'a pas le status OPEN
        if ($event->getState() !== 'OPEN') {
            $this->addFlash('warning', 'Event is not open for registration.');
            return $this->redirectToRoute('detail_event', ['id' => $event->getId()]);
        }

        // ne peux pas s'inscrire si l'vent est deja plein
        if ($event->getParticipants()->count() >= $event->getNbInscriptionMax()) {
            $this->addFlash('warning', 'Registration limit reached for this event.');
            return $this->redirectToRoute('detail_event', ['id' => $event->getId()]);
        }

        $isAlreadyRegistered = $event->getUser()->contains($user);

        // register event
        if ($isAlreadyRegistered) {
            if ($event->getDateLimitationInscription() > new \DateTimeImmutable()) {
                $event->removeParticipant($user);
                $em->flush();

                $this->addFlash('success', 'You have successfully unregistered from the event.');
            } else {
                $this->addFlash('warning', 'The deadline for unregistering from this event has passed.');
            }

            // unregister event
        } else {
            if ($event->getDateLimitationInscription() > new \DateTimeImmutable()) {
                $event->addParticipant($user);
                $em->flush();

                $this->addFlash('success', 'You have successfully unregistered from the event.');
            } else {
                $this->addFlash('warning', 'The deadline for unregistering from this event has passed.');
            }
        }
        return $this->redirectToRoute('detail_event', ['id' => $event->getId()]);
    }

    // requete ajax vers l'api data-gouv
    #[Route('/ajax-search-address', name: 'ajax_search_address')]
    public function ajaxSearchAddress(#[MapQueryParameter] string $address, CallApiService $callApiService): Response
    {

        $response = $callApiService->getFranceDataFromStreet($address);
        $addresses = [];

        // associer element de l'api a mes elements
        foreach ($response['features'] as $address) {
            $addresses[] = [
                'street' => $address['properties']['name'],
                'zipcode' => $address['properties']['postcode'],
                'city' => $address['properties']['city'],
                'latitude' => $address['geometry']['coordinates'][1],
                'longitude' => $address['geometry']['coordinates'][0],
            ];
        }

        $htlmContent = $this->renderView('event/includes/_addresses.html.twig', [
            'addresses' => $addresses,
        ]);

        return $this->json(['success' => true, 'htmlContent' => $htlmContent]);
    }


}

