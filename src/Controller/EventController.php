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


    #[Route('/create', name: 'create_location')]
    public function createLocation(Request $request, EntityManagerInterface $em): Response
    {
        $locationForm = $this->createForm(LocationType::class);
        $locationForm->handleRequest($request);

        if($locationForm->isSubmitted() && $locationForm->isSubmitted()){
            $location = $locationForm->getData();
            $em->persist($location);
            $em->flush();
            return $this->redirectToRoute('create_event',['id'=> $location->getId()]);

        }
        return $this->render('event/locationForm.html.twig', [
            'locationForm' => $locationForm,
        ]);
    }

    #[Route('/event', name: 'create_event')]
    public function createEvent(Request $request, EntityManagerInterface $em): Response
    {
        $event = new Event();
        $eventForm = $this->createForm(EventType::class, $event);
        $eventForm->handleRequest($request);

        if($eventForm->isSubmitted() && $eventForm->isSubmitted()){
            $event = $eventForm->getData();
            $em->persist($event);
            $em->flush();
            return $this->redirectToRoute('list_event');
        }

        return $this->render('event/eventForm.html.twig', [
            'eventForm' => $eventForm,
        ]);
    }
}
