<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UserController extends AbstractController
{

    // afficher le détail d'un utilisateur
    #[Route('/user', name: 'app_user')]
    public function index(): Response
    {
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    //création d'une route de page de détail du user
    #[Route('/user/detail/{id}', name: 'app_detail')]
    public function detail(EntityManagerInterface $em, int $id): Response
    {
        $user = $em->getRepository(User::class)->find($id);
        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }
        return $this->render('user/detail.html.twig', [
            'id' => $id,
            'user' => $user
        ]);
    }

    // l'utilisateur peut modifier son profil (en récupérant son id)
    #[Route('/user/update/{id}', name: 'app_update')]
    public function update(Request $request, EntityManagerInterface $em, int $id): Response
    {
        $user = $em->getRepository(User::class)->find($id);

        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Profile updated successfully');

            return $this->redirectToRoute('app_detail', ['id' => $user->getId()]);
        }

        return $this->render('user/update.html.twig', [
            'updateForm' => $form->createView(),
        ]);

    }
}


