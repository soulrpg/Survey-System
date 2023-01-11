<?php

namespace App\Controller;

use App\Entity\AnswerGroup;
use App\Entity\Survey;
use App\Repository\SurveyRepository;
use App\Service\FormProcessingService;
use App\Service\SerializerService;
use App\Service\ValidateAnswersService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

/**
 * Gives access to all CRUD operations on survey for user that is an owner of a given survey.
 * For getting given survey data to display it to participants /api/survey/show is used 
 * which doesn't check user's JWT token through firewall.
 */
class SurveyController extends AbstractController
{
    /**
     * @param SurveyRepository $surveyRepository
     * @param SerializerService $serializerService
     * @return JsonResponse
     */
    public function list(SurveyRepository $surveyRepository, SerializerService $serializerService): JsonResponse
    {
        return new JsonResponse(
            $serializerService->getSerializer()->normalize(
                $surveyRepository->findBy(['user' => $this->getUser()]),
                'json',
                [AbstractNormalizer::ATTRIBUTES => ['id', 'title', 'description', 'public']]
            ), 
            Response::HTTP_OK
        );
    }

    public function show(Survey $survey, SerializerService $serializerService): JsonResponse
    {
        return new JsonResponse(
            $serializerService->getSerializer()->normalize(
                $survey,
                'json',
                [AbstractNormalizer::ATTRIBUTES => ['id', 'title', 'description', 'public']]
            ), 
            Response::HTTP_OK
        );
    }

    public function update(
        Survey $survey,
        Request $request,
        ManagerRegistry $doctrine, 
        FormProcessingService $formProcessingService
    ): JsonResponse
    {
        if ($this->getUser()->getId() !== $survey->getUser()->getId()) {
            return new JsonResponse(['msg' => 'Survey does not belong to current user'], Response::HTTP_FORBIDDEN);
        }

        $entityManager = $doctrine->getManager();
        $survey->setUser($this->getUser());

        $form = $this->createForm(\App\Form\Type\SurveyType::class , $survey, ['csrf_protection' => false]);
        $errors = $formProcessingService->processForm($form, $request, $survey);

        if (count($errors) > 0) {
            return new JsonResponse(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        } else {
            $entityManager->persist($survey);
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
        $survey = new Survey();
        
        $entityManager = $doctrine->getManager();
        $survey->setUser($this->getUser());
        
        $form = $this->createForm(\App\Form\Type\SurveyType::class , $survey, ['csrf_protection' => false]);
        $errors = $formProcessingService->processForm($form, $request, $survey);

        if (count($errors) > 0) {
            return new JsonResponse(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        } else {
            $entityManager->persist($survey);
            $entityManager->flush();
            return new JsonResponse(['msg' => 'Success'], Response::HTTP_CREATED);
        }
    }

    public function delete(Survey $survey, ManagerRegistry $doctrine): JsonResponse
    {
        $entityManager = $doctrine->getManager();

        if ($this->getUser()->getId() === $survey->getUser()->getId()) {
            $entityManager->remove($survey);
            $entityManager->flush();
        } else {
            return new JsonResponse(['msg' => 'Survey does not belong to current user'], Response::HTTP_FORBIDDEN);
        }
        return new JsonResponse(['msg' => 'Success'], Response::HTTP_OK);
    }

    /**
     * List questions belonging to a specific survey
     * 
     * @param Survey $survey
     * @param SerializerService $serializerService
     * @return JsonResponse
     */
    public function listQuestions(Survey $survey, SerializerService $serializerService): JsonResponse
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
     * List answerGroups belonging to a specific survey
     * 
     * @param Survey $survey
     * @param SerializerService $serializerService
     * @return JsonResponse
     */
    public function listAnswerGroups(Survey $survey, SerializerService $serializerService): JsonResponse
    {
        return new JsonResponse(
            $serializerService->getSerializer()->normalize(
                $survey->getAnswerGroups(),
                'json',
                [AbstractNormalizer::ATTRIBUTES => ['id', ['pickedOption' => 'id']]]
            ), 
            Response::HTTP_OK
        );
    }

    /**
     * Submit answers (AnswerGroup) to given survey
     * TODO: Check if that works, if not create answer group & answers manually without
     * processing the form
     *
     * @param Survey $survey
     * @param Request $request
     * @param ManagerRegistry $doctrine
     * @param FormProcessingService $formProcessingService
     * @return JsonResponse
     */
    public function sendAnswers(
        Survey $survey, 
        Request $request,
        ManagerRegistry $doctrine, 
        FormProcessingService $formProcessingService,
        ValidateAnswersService $validateAnswersService
    ): JsonResponse 
    {
        $answerGroup = new AnswerGroup();
        
        $entityManager = $doctrine->getManager();
        
        $form = $this->createForm(\App\Form\Type\AnswerGroup::class , $answerGroup, ['csrf_protection' => false]);
        $errors = $formProcessingService->processForm($form, $request, $answerGroup);

        if (count($errors) > 0) {
            return new JsonResponse(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        } else {
            // Check if all answers correspond to proper survey and question
            try {
                $validateAnswersService->validate($survey, $answerGroup);
            } catch (\UnexpectedValueException $exception) {
                return new JsonResponse(['msg' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
            }
            
            foreach ($answerGroup->getAnswers() as $answer) {
                $answer->setAnswerGroup($answerGroup->getId());
                $entityManager->persist($answer);
            }
            $entityManager->persist($answerGroup);
            $entityManager->flush();
            return new JsonResponse(['msg' => 'Success'], Response::HTTP_CREATED);
        }
    }
}
