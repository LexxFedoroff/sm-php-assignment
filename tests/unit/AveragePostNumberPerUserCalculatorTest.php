<?php

declare(strict_types=1);

namespace Tests\unit;

use PHPUnit\Framework\TestCase;
use SocialPost\Dto\SocialPostTo;
use Statistics\Calculator\AveragePostNumberPerUserCalculator;
use Statistics\Dto\ParamsTo;
use Statistics\Dto\StatisticsTo;

/**
 * Class AveragePostNumberPerUserCalculatortTest
 *
 * @package Tests\unit
 */
class AveragePostNumberPerUserCalculatorTest extends TestCase
{
    /**
     * @test
     * @dataProvider getTestCases
     */
    public function testCalculate(AveragePostNumberPerUserCalculator $calculator, StatisticsTo $expectedDto): void
    {
        $dto = $calculator->calculate();
        $this->assertDto($expectedDto, $dto);
    }

    public function getTestCases(): array
    {
        return [
            'empty posts => empty statistic'                        =>
                $this->generateTestCase(d('2022-03-01'), d('2022-03-31'), [], stat(0)),
            'there are no posts in March'                           =>
                $this->generateTestCase(d('2022-03-01'), d('2022-03-31'), $this->generatePosts(), stat(0)),
            'there are 3 authors in January'                        =>
                $this->generateTestCase(d('2022-01-01'), d('2022-01-31'), $this->generatePosts(), stat(2.33)),
            'there are 1 author in February'                        =>
                $this->generateTestCase(d('2022-02-01'), d('2022-02-28'), $this->generatePosts(), stat(3)),
            'there are 2 authors in from January,20 to February,20' =>
                $this->generateTestCase(d('2022-01-20'), d('2022-02-20'), $this->generatePosts(), stat(2.5))
        ];
    }

    private function assertDto(StatisticsTo $expectedDto, StatisticsTo $actualDto)
    {
        $this->assertEquals($expectedDto->getName(), $actualDto->getName());
        $this->assertEquals($expectedDto->getSplitPeriod(), $actualDto->getSplitPeriod());
        $this->assertEquals($expectedDto->getUnits(), $actualDto->getUnits());
        $this->assertEquals($expectedDto->getValue(), $actualDto->getValue());

        $this->assertSameSize($expectedDto->getChildren(), $actualDto->getChildren());

        $n = count($expectedDto->getChildren());

        for ($i = 0; $i < $n; $i++) {
            $this->assertDto($expectedDto->getChildren()[$i], $actualDto->getChildren()[$i]);
        }
    }

    private function generateTestCase(\DateTime $startDate, \DateTime $endDate, iterable $posts, StatisticsTo $expectedStatistics): array
    {
        $calculator = new AveragePostNumberPerUserCalculator();

        $params = new ParamsTo();
        $params->setStatName('test');
        $params->setStartDate($startDate);
        $params->setEndDate($endDate);
        $calculator->setParameters($params);

        foreach ($posts as $post) {
            $calculator->accumulateData($post);
        }

        return [$calculator, $expectedStatistics];
    }

    private function generatePosts(): iterable
    {
        // january
        yield post('1', '2022-01-01');
        yield post('1', '2022-01-02');
        yield post('1', '2022-01-03');
        yield post('2', '2022-01-02');
        yield post('3', '2022-01-02');
        yield post('3', '2022-01-20');
        yield post('3', '2022-01-31');

        // february
        yield post('2', '2022-02-01');
        yield post('2', '2022-02-10');
        yield post('2', '2022-02-20');
    }
}

function d($date): \DateTime|bool
{
    return \DateTime::createFromFormat('Y-m-d', $date);
}

function stat(float $value): StatisticsTo
{
    $statisticsTo = new StatisticsTo();
    $statisticsTo->setName('test');
    $statisticsTo->setUnits('posts');
    $statisticsTo->setValue($value);

    return $statisticsTo;
}

function post(string $author, string $date): SocialPostTo
{
    $post = new  SocialPostTo();
    $post->setId(uniqid());
    $post->setAuthorId($author);
    $post->setAuthorName("Author $author");
    $post->setText(uniqid());
    $post->setType('type1');
    $post->setDate(d($date));
    return $post;
}
