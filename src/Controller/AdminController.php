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
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin', name: 'app')]
class AdminController extends AbstractController
{
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/list-admin', name: '_admin_list')]
    public function displayUsers(UserRepository $repository,): Response
    {
        $users = $repository->findAll();


        return $this->render('admin/list.html.twig', [
            'users' => $users,
        ]);
    }
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/create-admin', name: '_admin_create')]
    public function createUser(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, FileUploader $fileUploader): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        //$newFilename = null;
        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $photo = $form->get('photo')->getData();
            if ($photo) {
                $fileName = $fileUploader->upload($photo, $this->getParameter('brochures_directory'));
                $user->setPhoto($fileName);
            }

            $user->setState(true);
            $user->setRoles(['ROLE_USER']);
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_list', ['id' => $user->getId()]);
        }

        return $this->render('registration/register.html.twig', ['registrationForm' => $form,]);
    }

    #[Route('/update/{id}', name: '_admin_update')]
    #[IsGranted('ROLE_USER')]
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
    #[Route('/delete-user/{id}', name: '_admin_delete')]
    public function deleteUser(EntityManagerInterface $em, int $id, UserRepository $userRepository): Response
    {
        $user = $em->getRepository(User::class)->find($id);

        $em->remove($user);
        $em->flush();
        $this->addFlash('success', 'Utilisateur supprimé avec succès');

        return $this->redirectToRoute('app_admin_list');
    }
    #[IsGranted('ROLE_ADMIN')]
    // créer une route permettant de rendre inactif un user
    #[Route('/disable/{id}', name: '_admin_disable')]
    public function disable(EntityManagerInterface $em, int $id, UserRepository $userRepository): Response
    {
        $user = $em->getRepository(User::class)->find($id);

        $userState = $user->isState();
        if ($userState === true) {
            $user->setState(false);
        } else {
            $user->setState(true);
        }
        $em->persist($user);
        $em->flush();


        return $this->redirectToRoute('app_profile', [
            'id' => $id,
            'user' => $user
        ]);
    }
}
