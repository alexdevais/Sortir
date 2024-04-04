<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Helpers\FileUploader;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

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
    #[IsGranted('ROLE_ADMIN')]
    public function update(Request $request, EntityManagerInterface $em, int $id, FileUploader $fileUploader): Response
    {
        $user = $em->getRepository(User::class)->find($id);

        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $photo = $form->get('photo')->getData();
            if ($photo) {
                $fileName = $fileUploader->upload($photo,$this->getParameter('brochures_directory'));
                $user->setPhoto($fileName);
            }

            $em->flush();
            $this->addFlash('success', 'Profile updated successfully');

            return $this->redirectToRoute('app_detail', ['id' => $user->getId()]);
        }

        return $this->render('user/update.html.twig', [
            'updateForm' => $form->createView(),
        ]);

    }

    #[Route('/users', name: 'app_user_list')]
    public function list(UserRepository $repository,): Response
    {
        $users = $repository->findAll();


        return $this->render('user/list.html.twig', [
            'users' => $users,
        ]);
    }


}


