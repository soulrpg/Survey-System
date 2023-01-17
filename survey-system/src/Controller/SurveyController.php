<?php

namespace App\Controller;

use App\Entity\Answer;
use App\Entity\AnswerGroup;
use App\Entity\Option;
use App\Entity\Question;
use App\Entity\Survey;
use App\Repository\OptionRepository;
use App\Repository\SurveyRepository;
use App\Service\FormProcessingService;
use App\Service\SerializerService;
use App\Service\ValidateAnswersService;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
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
        if ($survey->isPublic()) {
            return new JsonResponse(
                $serializerService->getSerializer()->normalize(
                    $survey,
                    'json',
                    [AbstractNormalizer::ATTRIBUTES => ['id', 'title', 'description', 'public']]
                ), 
                Response::HTTP_OK
            );
        } else {
            return new JsonResponse(['error' => 'Survey not found'], Response::HTTP_NOT_FOUND);
        }
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
            try {
                $entityManager->remove($survey);
                $entityManager->flush();
            } catch (ForeignKeyConstraintViolationException $exception) {
                return new JsonResponse(['error' => 'You cannot remove survey with existing children.'], Response::HTTP_FORBIDDEN);
            }

        } else {
            return new JsonResponse(['error' => 'Survey does not belong to current user'], Response::HTTP_FORBIDDEN);
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
     * Show count of answers given to all questions & options of one survey
     *
     * {
     *    "question_1": ["option_1": 9, "option_2": 20],
     *    "question_1": ["option_1": 9, "option_2": 2, "option_3": 5],
     * }
     * 
     * @param Survey $survey
     * @return JsonResponse
     */
    public function showAnswerCount(Survey $survey): JsonResponse
    {
        if ($this->getUser()->getId() === $survey->getUser()->getId()) {
            $answerCount = [];
            $surveyQuestions = $survey->getQuestions();
            /** @var Question $question */
            foreach ($surveyQuestions as $question) {
                $options = $question->getOptions();
                $answerCount[$question->getTitle()] = [];
                /** @var Option $option */
                foreach ($options as $option) {
                    $answerCount[$question->getTitle()][$option->getName()] = $option->getAnswers()->count();
                }
            }
            return new JsonResponse(
                $answerCount,
                Response::HTTP_OK
            );
        } else {
            return new JsonResponse(['error' => 'Survey does not belong to current user'], Response::HTTP_FORBIDDEN);
        }
    }

    /**
     * Submit answers (AnswerGroup) to given survey
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
        $answerGroup->setSurvey($survey);
        
        $entityManager = $doctrine->getManager();

        $entityManager->persist($answerGroup);
        $entityManager->flush();

        $formData = $request->getContent();
        $body = json_decode($formData, true);

        $optionRepository = new OptionRepository($doctrine);

        foreach ($body as $answerId => $optionPicked) {
            $newAnswer = new Answer();
            $newAnswer->setPickedOption($optionRepository->find($optionPicked));
            $answerGroup->addAnswer($newAnswer);
        }

        try {
            $validateAnswersService->validate($survey, $answerGroup);
        } catch (\UnexpectedValueException $exception) {
            return new JsonResponse(['msg' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        foreach ($answerGroup->getAnswers() as $answer) {
            $answer->setAnswerGroup($answerGroup);
            $entityManager->persist($answer);
        }
        $entityManager->flush();
        
        return new JsonResponse(['msg' => 'Success'], Response::HTTP_CREATED);
    }
}
