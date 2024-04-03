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

            return $this->redirectToRoute('list_event');
        }

        return $this->render('event/event.html.twig', [
            'form' => $eventForm,
        ]);
    }
}
