<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventType;
use App\Helpers\CallApiService;
use App\Repository\EventRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/event')]
class EventController extends AbstractController
{

    #[Route('/', name: 'list_event')]
    public function listEvent(EventRepository $eventRepository): Response
    {

        $today = new DateTime(); // Get today's date
        $oneMonthAgo = $today->modify('-1 month');

        $event = $eventRepository->findByCreatedDateAfter($oneMonthAgo);


        return $this->render('event/index.html.twig', [
            'event' => $event,
        ]);

    }


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

        $event->setCreatedDate(new DateTime('now'));

        if ($eventForm->isSubmitted() && $eventForm->isSubmitted()) {

            /** @var Event $newEvent */
            $newEvent = $eventForm->getData();
            $newLocation = $newEvent->getLocation();

            $responseApi = $callApiService->getFranceDataLoc($newLocation);
            if (array_key_exists('features', $responseApi) && count($responseApi['features']) > 0) {

                $newLocation->setLongitude($responseApi['features'][0]['geometry']['coordinates'][0])
                    ->setLatitude($responseApi['features'][0]['geometry']['coordinates'][1]);
                $user = $this->getUser();
                $event->setOrganizer($user);


                $em->persist($newEvent);
                $em->flush();

                $this->addFlash('success', 'Event created !');
                return $this->redirectToRoute('list_event');
            } else {
                $this->addFlash('success', 'Event not created !');
            }
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
        ]);

    }


    // changer l'etat d'un event pour le cancel + motif d'annulation
    #[Route('/detail/{id}/cancel', name: 'cancel_event')]
    public function cancelEvent(int $id, EntityManagerInterface $em, EventRepository $eventRepository, Request $request): Response
    {
        $event = $eventRepository->find($id);
        $motif = $request->request->get('motif');
        // Vérifier si l'utilisateur est l'organisateur de l'événement
        if (!$this->isGranted('ROLE_USER') || $event->getOrganizer() !== $this->getUser()) {

            // Vérifier si la date de début de l'événement est passée
            if ($event->getFirstAirDate() < new \DateTime()) {
                $this->addFlash('error', 'L\'événement a déjà commencé et ne peut pas être annulé.');
            }

            if (empty($motif)) {
                $this->addFlash('error', 'Veuillez fournir un motif pour annuler événement.');
                return $this->redirectToRoute('detail_event', ['id' => $event->getId()]);
            }
        }
        $event->setState('CANCELLED');
        $event->setCancelReason($motif);
        $event = $em->flush();
        return $this->redirectToRoute('list_event');

        // Envoyer un message de confirmation à l'organisateur
        $this->addFlash('success', 'L\'événement a été annulé avec succès.');

        return $this->render('event/detail.html.twig', [
            'event' => $event,
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


}
