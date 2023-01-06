<?php

namespace App\Service;

use App\Entity\AnswerGroup;
use App\Entity\Survey;


final class ValidateAnswersService 
{
    /**
     * Validate sent answer group for given survey
     * 
     * @param Survey $survey
     * @param AnswerGroup $answerGroup
     * @throws \UnexpectedValueException
     * @return void
     */
    public function validate(Survey $survey, AnswerGroup $answerGroup): void
    {
        $questionsAnswered = array_fill_Keys($survey->getQuestions()->getKeys(), false);
        foreach ($answerGroup->getAnswers() as $answer)
        {
            $questionId = $answer->getPickedOption()->getQuestion()->getId();
            // If array key doesn't exist it means there is answer submitted to a question not belonging
            // to current survey
            if (!array_key_exists($questionId, $questionsAnswered)) {
                throw new \UnexpectedValueException('Wrong answer group submitted!');
            }
            $questionsAnswered[$questionId] = true;
        }
        if (in_array(false, $questionsAnswered)) {
            throw new \UnexpectedValueException('Not all answers submitted!');
        }
    }
}