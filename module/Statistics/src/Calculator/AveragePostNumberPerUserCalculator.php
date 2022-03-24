<?php

declare(strict_types=1);

namespace Statistics\Calculator;

use SocialPost\Dto\SocialPostTo;
use Statistics\Dto\StatisticsTo;

class AveragePostNumberPerUserCalculator extends AbstractCalculator
{
    protected const UNITS = 'posts';

    /**
     * @var array
     */
    private $authors = [];

    /**
     * @var int
     */
    private $postCount = 0;

    /**
     * @inheritDoc
     */
    protected function doAccumulate(SocialPostTo $postTo): void
    {
        $key = $postTo->getAuthorId();

        $this->authors[$key] = $postTo->getAuthorName();
        $this->postCount++;
    }

    /**
     * @inheritDoc
     */
    protected function doCalculate(): StatisticsTo
    {
        $authorsCount = count($this->authors);
        $value = $authorsCount > 0
            ? $this->postCount / $authorsCount
            : 0;

        return (new StatisticsTo())->setValue(round($value,2));
    }
}
