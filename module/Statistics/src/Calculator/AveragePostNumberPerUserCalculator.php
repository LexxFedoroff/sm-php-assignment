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
    private $totals = [];

    /**
     * @var array
     */
    private $authors = [];

    /**
     * @inheritDoc
     */
    protected function doAccumulate(SocialPostTo $postTo): void
    {
        $key = $postTo->getAuthorId();

        $this->authors[$key] = $postTo->getAuthorName();
        $this->totals[$key] = ($this->totals[$key] ?? 0) + 1;
    }

    /**
     * @inheritDoc
     */
    protected function doCalculate(): StatisticsTo
    {
        // TODO should I add sort by name here ?
        $stats = new StatisticsTo();
        foreach ($this->totals as $authorId => $total) {
            $authorName = $this->authors[$authorId];
            $child = (new StatisticsTo())
                ->setName($this->parameters->getStatName())
                ->setSplitPeriod($authorName)
                ->setValue($total)
                ->setUnits(self::UNITS);

            $stats->addChild($child);
        }

        return $stats;
    }
}
