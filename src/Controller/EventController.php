<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Location;
use App\Form\EventType;
use App\Form\LocationType;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
#[Route('/event')]
class EventController extends AbstractController
{

    #[Route('/', name: 'list_event')]
    public function listEvent(EventRepository $eventRepository)
    {
        $event = $eventRepository->findAll();
        return $this->render('event/index.html.twig',[
            'event' => $event,
        ]);

    }

    #[Route('/create', name: 'create_event')]
    public function createEvent(Request $request, EntityManagerInterface $em): Response
    {
        $event = new Event();

        $eventForm = $this->createForm(EventType::class, $event);
        $eventForm->handleRequest($request);

        if($eventForm->isSubmitted() && $eventForm->isSubmitted()){
            $user = $this->getUser();

            if (!$user) {
                return $this->redirectToRoute('app_login');
            }
            $event->setOrganizer($user);

            $em->persist($eventForm->getData());
            $em->flush();

            $this->addFlash('success', 'Event created !');
            return $this->redirectToRoute('list_event');
        }

        return $this->render('event/event.html.twig', [
            'form' => $eventForm,
        ]);
    }

    #[Route('/detail/{id}', name: 'detail_event')]
    public function detailEvent(int $id,EventRepository $eventRepository): Response
    {
        $event = $eventRepository->find($id);
        return $this->render('event/detail.html.twig',[
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
            if ($event->getDateLimitationInscription() > new \DateTimeImmutable()){
                $event->removeParticipant($user);
                $em->flush();

                $this->addFlash('success', 'You have successfully unregistered from the event.');
            } else {
                $this->addFlash('warning', 'The deadline for unregistering from this event has passed.');
            }

        // unregister event
        } else {
            if ($event->getDateLimitationInscription() > new \DateTimeImmutable()){
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
