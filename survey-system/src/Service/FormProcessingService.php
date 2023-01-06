<?php

namespace App\Service;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class FormProcessingService
{   
    private ValidatorInterface $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * Processes form data sent through request data (json).
     * Returns result of entity object validation.
     * 
     * @param FormInterface $form
     * @param Request $request
     * @param object $entity
     * @return ConstraintViolationListInterface
     */
    public function processForm(
        FormInterface $form,
        Request $request, 
        object $entity
    ): ConstraintViolationListInterface
    {
        $formData = $request->getContent();
        $body = json_decode($formData, true); 
        
        $form->submit($body);

        return $this->validator->validate($entity);
    }
}