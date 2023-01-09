<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\Type\UserType;
use App\Repository\UserRepository;
use App\Service\FormProcessingService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegistrationController extends AbstractController
{
    /**
     * @param Request $request
     * @param UserPasswordHasherInterface $passwordHasher
     * @param ManagerRegistry $doctrine
     * @param UserRepository $userRepository
     * @return \InvalidArgumentException
     */
    public function index
    (
        Request $request, 
        UserPasswordHasherInterface $passwordHasher, 
        ManagerRegistry $doctrine,
        UserRepository $userRepository,
        FormProcessingService $formProcessingService
    ): Response
    {
        $entityManager = $doctrine->getManager();

        $user = new User();

        $form = $this->createForm(UserType::class , $user, ['csrf_protection' => false]);
        $errors = $formProcessingService->processForm($form, $request, $user);

        if (count($errors) > 0) {
            return new JsonResponse(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        } else {
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $user->getPassword()
            );
            $user->setPassword($hashedPassword);

            if ($userRepository->findOneByEmail($user->getEmail())) {
                return new JsonResponse(['errors' => 'User with given email already exists!'], 404);
            }

            $entityManager->persist($user);
            $entityManager->flush();
            return new JsonResponse(['msg' => 'Success'], Response::HTTP_CREATED);
        }
    }
}