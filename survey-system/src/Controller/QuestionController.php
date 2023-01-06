<?php

namespace App\Controller;

use App\Entity\Question;
use App\Entity\Survey;
use App\Service\FormProcessingService;
use App\Service\SerializerService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

/**
 * Single question CRUD actions
 */
class QuestionController extends AbstractController
{
    /**
     * List questions belonging to a specific survey
     * 
     * @param Survey $survey
     * @param SerializerService $serializerService
     * @return JsonResponse
     */
    public function list(Survey $survey, SerializerService $serializerService): JsonResponse
    {
        return new JsonResponse(
            $serializerService->getSerializer()->normalize(
                $survey->getQuestions(),
                'json',
                [AbstractNormalizer::ATTRIBUTES => ['id', 'title', 'description']]
            ), 
            Response::HTTP_OK
        );
    }

    /**
     * Shows specific question
     * 
     * @param Question $question
     * @param SerializerService $serializerService
     * @return JsonResponse
     */
    public function show(Question $question, SerializerService $serializerService): JsonResponse
    {
        return new JsonResponse(
            $serializerService->getSerializer()->normalize(
                $question,
                'json',
                [AbstractNormalizer::ATTRIBUTES => ['id', 'title', 'description']]
            ), 
            Response::HTTP_OK
        );
    }

    public function update(
        Question $question,
        Request $request,
        ManagerRegistry $doctrine, 
        FormProcessingService $formProcessingService
    ): JsonResponse
    {
        if ($this->getUser()->getId() !== $question->getSurvey()->getUser()->getId()) {
            return new JsonResponse(['msg' => 'Question does not belong to current user'], Response::HTTP_FORBIDDEN);
        }

        $entityManager = $doctrine->getManager();

        $form = $this->createForm(\App\Form\Type\QuestionType::class , $question, ['csrf_protection' => false]);
        $errors = $formProcessingService->processForm($form, $request, $question);

        if (count($errors) > 0) {
            return new JsonResponse(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        } else {
            $entityManager->persist($question);
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
        $question = new Question();
        
        $entityManager = $doctrine->getManager();
        
        $form = $this->createForm(\App\Form\Type\QuestionType::class , $question, ['csrf_protection' => false]);
        $errors = $formProcessingService->processForm($form, $request, $question);

        if (count($errors) > 0) {
            return new JsonResponse(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        } else {
            $entityManager->persist($question);
            $entityManager->flush();
            return new JsonResponse(['msg' => 'Success'], Response::HTTP_CREATED);
        }
    }

    public function delete(Question $question, ManagerRegistry $doctrine): JsonResponse
    {
        $entityManager = $doctrine->getManager();
        if ($this->getUser()->getId() === $question->getSurvey()->getUser()->getId()) {
            $entityManager->remove($question);
            $entityManager->flush();
        } else {
            return new JsonResponse(['msg' => 'Question does not belong to current user'], Response::HTTP_FORBIDDEN);
        }
        return new JsonResponse(null, Response::HTTP_OK);
    }
}
