<?php

namespace App\Tests;

use App\Entity\Survey;
use App\Service\FormProcessingService;
use Doctrine\Migrations\Tools\Console\Exception\VersionDoesNotExist;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validation;


class FormProcessingServiceTest extends TestCase
{
    private FormProcessingService $formProcessingService; 

    protected function setUp(): void
    {
        $validator = Validation::createValidatorBuilder()->enableAnnotationMapping(true)->getValidator();
        $this->formProcessingService = new FormProcessingService($validator);
    }

    public function testCorrrectSurvey(): void
    {
        $survey = new Survey();
        $request = $this->createMock(Request::class);
        $request->expects($this->once())
            ->method('getContent')
            ->willReturn('{"title": "Test title", "description": "<p>Test descrption.</p>"}');
        $form = $this->createMock(FormInterface::class);
        $form->expects($this->once())
            ->method('submit');
        $this->formProcessingService->processForm($form, $request, $survey);
    }

    public function testIncorrrectSurvey(): void
    {
        $survey = new Survey();
        $request = $this->createMock(Request::class);
        $request->expects($this->once())
            ->method('getContent')
            ->willReturn('{"description": "<p>Test descrption.</p>"}');
        $form = $this->createMock(FormInterface::class);
        $form->expects($this->once())
            ->method('submit');
        $this->formProcessingService->processForm($form, $request, $survey);
    }
}
