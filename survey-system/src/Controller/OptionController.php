<?php

namespace App\Controller;

use App\Entity\Option;
use App\Service\FormProcessingService;
use App\Service\SerializerService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

/**option
 * Single option CRUD actions
 */
class OptionController extends AbstractController
{
    /**
     * Shows specific option
     * 
     * @param Option $option
     * @param SerializerService $serializerService
     * @return JsonResponse
     */
    public function show(Option $option, SerializerService $serializerService): JsonResponse
    {
        return new JsonResponse(
            $serializerService->getSerializer()->normalize(
                $option,
                'json',
                [AbstractNormalizer::ATTRIBUTES => ['id', 'title', 'description']]
            ), 
            Response::HTTP_OK
        );
    }

    public function update(
        Option $option,
        Request $request,
        ManagerRegistry $doctrine, 
        FormProcessingService $formProcessingService
    ): JsonResponse
    {
        if ($this->getUser()->getId() !== $option->getQuestion()->getSurvey()->getUser()->getId()) {
            return new JsonResponse(['msg' => 'Option does not belong to current user'], Response::HTTP_FORBIDDEN);
        }

        $entityManager = $doctrine->getManager();

        $form = $this->createForm(\App\Form\Type\OptionType::class , $option, ['csrf_protection' => false]);
        $errors = $formProcessingService->processForm($form, $request, $option);

        if (count($errors) > 0) {
            return new JsonResponse(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        } else {
            $entityManager->persist($option);
            $entityManager->flush();
            return new JsonResponse(['msg' => 'Success'], Response::HTTP_CREATED);
        }
    }

    public function create(
        Request $request,
        ManagerRegistry $doctrine, 
        FormProcessingService $formProcessingService
    ): JsonResponse
    {
        $option = new Option();
        
        $entityManager = $doctrine->getManager();
        
        $form = $this->createForm(\App\Form\Type\OptionType::class , $option, ['csrf_protection' => false]);
        $errors = $formProcessingService->processForm($form, $request, $option);

        if (count($errors) > 0) {
            return new JsonResponse(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        } else {
            $entityManager->persist($option);
            $entityManager->flush();
            return new JsonResponse(['msg' => 'Success'], Response::HTTP_CREATED);
        }
    }

    public function delete(Option $option, ManagerRegistry $doctrine): JsonResponse
    {
        $entityManager = $doctrine->getManager();
        if ($this->getUser()->getId() === $option->getQuestion()->getSurvey()->getUser()->getId()) {
            $entityManager->remove($option);
            $entityManager->flush();
        } else {
            return new JsonResponse(['msg' => 'Option does not belong to current user'], Response::HTTP_FORBIDDEN);
        }
        return new JsonResponse(['msg' => 'Success'], Response::HTTP_OK);
    }
}
