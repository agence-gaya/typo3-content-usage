<?php

declare(strict_types=1);

namespace GAYA\ContentUsage\Tests\Unit\Pagination;

use GAYA\ContentUsage\Pagination\ListPagination;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ListPaginationTest extends TestCase
{
    #[DataProvider('pages')]
    public function testBoundaries(int $total, int|string $size, mixed $requested, int $page, int $start, int $end, int $pages): void
    {
        $pagination = new ListPagination($total, $size, $requested);
        self::assertSame($page, $pagination->page);
        self::assertSame($start, $pagination->getStart());
        self::assertSame($end, $pagination->getEnd());
        self::assertSame($pages, $pagination->numberOfPages);
        self::assertContains($page, $pagination->getPages());
        self::assertLessThanOrEqual(7, count($pagination->getPages()));
    }

    public static function pages(): iterable
    {
        yield 'empty' => [0, 100, 12, 1, 0, 0, 1];
        yield 'first' => [251, 100, 1, 1, 1, 100, 3];
        yield 'middle' => [251, 100, 2, 2, 101, 200, 3];
        yield 'last' => [251, 100, 3, 3, 201, 251, 3];
        yield 'past last' => [251, 100, 99, 3, 201, 251, 3];
        yield 'all' => [251, 'all', 99, 1, 1, 251, 1];
        yield 'many pages' => [10000, 50, 100, 100, 4951, 5000, 200];
        foreach ([null, [], '2abc', 1.5, true, -1, 0, '999999999999999999999999'] as $index => $invalid) {
            yield 'invalid ' . $index => [251, 100, $invalid, 1, 1, 100, 3];
        }
    }
}
