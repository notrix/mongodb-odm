<?php

declare(strict_types=1);

namespace Doctrine\ODM\MongoDB\Tests\Aggregation\Stage;

use Doctrine\ODM\MongoDB\Tests\BaseTest;
use Documents\CmsComment;
use Documents\User;
use MongoDB\BSON\UTCDateTime;

class ReplaceRootTest extends BaseTest
{
    public function testTypeConversion()
    {
        $builder = $this->dm->createAggregationBuilder(User::class);

        $dateTime = new \DateTimeImmutable('2000-01-01T00:00Z');
        $mongoDate = new UTCDateTime((int) $dateTime->format('Uv'));
        $stage = $builder
            ->replaceRoot()
                ->field('isToday')
                ->eq('$createdAt', $dateTime);

        $this->assertEquals(
            [
            '$replaceRoot' => [
                'isToday' => ['$eq' => ['$createdAt', $mongoDate]],
            ],
            ],
            $stage->getExpression()
        );
    }

    public function testTypeConversionWithDirectExpression()
    {
        $builder = $this->dm->createAggregationBuilder(User::class);

        $dateTime = new \DateTimeImmutable('2000-01-01T00:00Z');
        $mongoDate = new UTCDateTime((int) $dateTime->format('Uv'));
        $stage = $builder
            ->replaceRoot(
                $builder->expr()
                    ->field('isToday')
                    ->eq('$createdAt', $dateTime)
            );

        $this->assertEquals(
            [
            '$replaceRoot' => [
                'isToday' => ['$eq' => ['$createdAt', $mongoDate]],
            ],
            ],
            $stage->getExpression()
        );
    }

    public function testFieldNameConversion()
    {
        $builder = $this->dm->createAggregationBuilder(CmsComment::class);

        $stage = $builder
            ->replaceRoot()
                ->field('someField')
                ->concat('$authorIp', 'foo');

        $this->assertEquals(
            [
            '$replaceRoot' => [
                'someField' => ['$concat' => ['$ip', 'foo']],
            ],
            ],
            $stage->getExpression()
        );
    }

    public function testFieldNameConversionWithDirectExpression()
    {
        $builder = $this->dm->createAggregationBuilder(CmsComment::class);

        $stage = $builder
            ->replaceRoot('$authorIp');

        $this->assertEquals(
            ['$replaceRoot' => '$ip'],
            $stage->getExpression()
        );
    }
}
