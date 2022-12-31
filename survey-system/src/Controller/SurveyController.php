<?php

namespace App\Controller;

use App\Entity\Survey;
use App\Repository\SurveyRepository;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Gives access to all CRUD operations on survey for user that is an owner of a given survey.
 * For getting given survey data to display it to participants /api/survey/show is used 
 * which doesn't check user's JWT token through firewall.
 */
class SurveyController extends AbstractController
{
    public function list(SurveyRepository $surveyRepository): JsonResponse
    {
        return new JsonResponse(
            json_encode($surveyRepository->findBy(['user' => $this->getUser()->getId()])), 
            Response::HTTP_OK
        );
    }

    public function show(Survey $survey): JsonResponse
    {
        return new JsonResponse(json_encode($survey), Response::HTTP_OK);
    }

    public function send(Survey $survey, Request $request): JsonResponse
    {
        $formData = $request->getContent();
        $body = json_decode($formData, true);
        //TODO implement form data processing
        return new JsonResponse(null, Response::HTTP_CREATED);
    }

    public function update(Survey $survey): JsonResponse
    {
        //TODO implement form data processing
        return new JsonResponse(null, Response::HTTP_OK);
    }

    public function create(Survey $survey): JsonResponse
    {
        //TODO implement form data processing
        return new JsonResponse(null, Response::HTTP_CREATED);
    }

    public function delete(Survey $survey, ManagerRegistry $doctrine): JsonResponse
    {
        $entityManager = $doctrine->getManager();

        if ($this->getUser()->getId() === $survey->getUser()->getId()) {
            $entityManager->remove($survey);
            $entityManager->flush();
        } else {
            throw new NotFoundHttpException('Survey ID does not belong to correct owner.');
        }
        return new JsonResponse(null, Response::HTTP_OK);
    }
}
