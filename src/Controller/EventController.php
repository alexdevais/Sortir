<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventType;
use App\Helpers\CallApiService;
use App\Repository\EventRepository;
use DateTime;
use DeviceDetector\ClientHints;
use DeviceDetector\DeviceDetector;
use DeviceDetector\Parser\AbstractParser;
use DeviceDetector\Parser\Device\AbstractDeviceParser;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/event')]
class EventController extends AbstractController
{


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

    #[Route('/create', name: 'create_event')]
    public function createEvent(Request $request, EntityManagerInterface $em, CallApiService $callApiService): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        AbstractDeviceParser::setVersionTruncation(AbstractParser::VERSION_TRUNCATION_NONE);
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        $clientHints = ClientHints::factory($_SERVER);
        $dd = new DeviceDetector($userAgent, $clientHints);
        $dd->parse();
        $device = $dd->isDesktop();
        if (!$device) {
            $this->addFlash('error', 'This path is not available for mobile devices');
            return $this->redirectToRoute('app_home');

        }


        $event = new Event();
        $eventForm = $this->createForm(EventType::class, $event);
        $eventForm->handleRequest($request);
        $event->setCreatedDate(new DateTime('now'));

        if ($eventForm->isSubmitted() && $eventForm->isSubmitted()) {

            /** @var Event $newEvent */
            $newEvent = $eventForm->getData();
            $newLocation = $newEvent->getLocation();

            $user = $this->getUser();
            $event->setOrganizer($user);

            $em->persist($newEvent);
            $em->flush();

            $this->addFlash('success', 'Event created !');
            return $this->redirectToRoute('list_event');
        } else {
            $this->addFlash('success', 'Event not created !');
        }


        return $this->render('event/event.html.twig', [
            'form' => $eventForm,
        ]);
    }


    #[Route('/detail/{id}', name: 'detail_event')]
    public function detailEvent(int $id, EventRepository $eventRepository): Response
    {
        $user = $this->getUser();
        $participantId = $user->getId();
        $event = $eventRepository->find($id);
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

            if (empty($motif)) {
                $this->addFlash('error', 'Please provide a reason for canceling the event.');
                return $this->redirectToRoute('detail_event', ['id' => $event->getId()]);
            }

        }
        $event->setState('CANCELLED');
        $event->setCancelReason($motif);
        $event = $em->flush();
        return $this->redirectToRoute('list_event');
        // Envoyer un message de confirmation à l'organisateur
        $this->addFlash('success', 'The event has been successfully canceled.');

        return $this->render('event/detail.html.twig', [
            'event' => $event,
            'user' => $user,
        ]);
    }

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


    #[Route('/ajax-search-address', name: 'ajax_search_address')]
    public function ajaxSearchAddress(#[MapQueryParameter] string $address, CallApiService $callApiService): Response
    {

        $response = $callApiService->getFranceDataFromStreet($address);
        $addresses = [];
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

