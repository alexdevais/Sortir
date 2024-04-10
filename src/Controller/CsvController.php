<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UploadUserCsvType;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use SplFileObject;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class CsvController extends AbstractController
{


    #[Route('/csv', name: 'app_csv')]
    public function importUsersAction(Request $request, EntityManagerInterface $entityManager): Response
    {
        $scv = new UploadUserCsvType();
        $form = $this->createForm(UploadUserCsvType::class, $scv);
        $form->handleRequest($request);

        $formView = $form->createView();

         if ($form->isSubmitted() && $form->isValid()) {
             $form = $form->get('csvFile')->getData();
             $lines = $this->readCsvFile($form);
             $users = [];
             foreach ($lines as $line) {
                 $data = str_getcsv($line, ',');
                 $user = new User();
                 $user->setEmail($data['0']);
                 $roles = explode(',', $data['1']); // Extract roles as an array
                 $user->setRoles($roles);
                 $user->setPassword($data['2']);
                 $user->setName($data['3']);
                 $user->setfirstName($data['4']);
                 $user->setState($data['5']);



                 $users[] = $user;

             }
             foreach ($users as $user) {
                 $entityManager->persist($user);
             }
             $entityManager->flush();
             $this->addFlash('success', 'les utilisateurs ont bien été importé');
             return $this->redirectToRoute('app_csv');
         }
         return $this->render('csv/index.html.twig',
         ['form'=>$formView

         ]);

    }
    private function readCsvFile($form)

    {
        $lines =[];
        $handle = fopen($form, 'r');

    while(($line = fgets($handle))!==false) {
        $lines[] = $line;
    }
        fclose($handle);
        return $lines;
    }

}
