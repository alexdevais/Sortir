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

        $eventForm = $this->createForm(EventType::class);
        $eventForm->handleRequest($request);

        if($eventForm->isSubmitted() && $eventForm->isSubmitted()){
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
    public function inscriptionEvent(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('login');
        }

        $event = $em->getRepository(Event::class)->find($id);

        if (!$event) {
            throw $this->createNotFoundException('Event not found!');
        }

        $isAlreadyRegistered = $event->getParticipants()->contains($user);

        if (!$isAlreadyRegistered) {
            $event->addParticipant($user);
            $em->flush();

            $this->addFlash('success', 'Event registration successful !');
        } else {
            $this->addFlash('warning', 'You are already registered for this event.');
        }

        return $this->redirectToRoute('event_detail', ['id' => $event->getId()]);
    }

}
