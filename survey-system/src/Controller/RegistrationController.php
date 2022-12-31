<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\Type\UserType;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
        ValidatorInterface $validator
    ): Response
    {
        $entityManager = $doctrine->getManager();

        $user = new User();

        $formData = $request->getContent();
        $body = json_decode($formData, true);
        $form = $this->createForm(UserType::class, $user, ['csrf_protection' => false]);
        $form->submit($body);

        $errors = $validator->validate($user);

        if (count($errors) > 0) {
            return new JsonResponse(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        } else {
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $user->getPassword()
            );
            $user->setPassword($hashedPassword);

            if ($userRepository->findOneByEmail($user->getEmail())) {
                throw new \InvalidArgumentException("User with given email already exists!");
            }

            $entityManager->persist($user);
            $entityManager->flush();
            return new JsonResponse(['msg' => 'Success'], Response::HTTP_CREATED);
        }
    }
}