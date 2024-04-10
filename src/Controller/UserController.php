<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Form\UpdateFormType;
use App\Helpers\FileUploader;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/user', name: 'app')]
class UserController extends AbstractController
{

    // l'utilisateur peut modifier son profil (en récupérant son id)
    #[IsGranted('ROLE_USER')]
    #[Route('/update/{id}', name: '_update')]
    public function update(Request $request, EntityManagerInterface $em, int $id, FileUploader $fileUploader): Response
    {
        $user = $em->getRepository(User::class)->find($id);

        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        $form = $this->createForm(UpdateFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $photo = $form->get('photo')->getData();
            if ($photo) {
                $fileName = $fileUploader->upload($photo, $this->getParameter('brochures_directory'));
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
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/list', name: '_user_list')]
    public function list(UserRepository $repository,): Response
    {
        $users = $repository->findAll();
        return $this->render('user/list.html.twig', [
            'users' => $users,
        ]);
    }
    #[IsGranted('ROLE_USER')]
    #[Route('/profile/{id}', name: '_profile')]
    public function profile(EntityManagerInterface $em, int $id): Response
    {
        $user = $em->getRepository(User::class)->find($id);
        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }
        return $this->render('user/profile.html.twig', [
            'id' => $id,
            'user' => $user
        ]);
    }


}


