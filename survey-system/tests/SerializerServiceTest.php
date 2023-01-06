<?php

namespace App\Tests;

use App\Entity\Question;
use App\Entity\Survey;
use App\Service\SerializerService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class SerializerServiceTest extends TestCase
{
    private SerializerService $serializerService;
    protected function setUp(): void
    {
        $this->serializerService = new SerializerService();
    }

    public function testSurveySerialization(): void
    {
        $survey = new Survey();
        $survey->setTitle("Test title");
        $survey->setDescription("Test description");
        $survey->setPublic(false);
        $expectedResult = '{"title":"Test title","description":"Test description","public":false}';
        $this->assertEquals($expectedResult, json_encode($this->serializerService->getSerializer()->normalize(
            $survey,
            'json',
            [AbstractNormalizer::ATTRIBUTES => ['title', 'description', 'public']]
        )));
    }

    public function testQuestionSerialization(): void
    {
        $question = new Question();
        $question->setTitle("Test title");
        $question->setDescription("Test description");
        $expectedResult = '{"title":"Test title","description":"Test description"}';
        $this->assertEquals($expectedResult, json_encode($this->serializerService->getSerializer()->normalize(
            $question,
            'json',
            [AbstractNormalizer::ATTRIBUTES => ['title', 'description']]
        )));
    }
}
