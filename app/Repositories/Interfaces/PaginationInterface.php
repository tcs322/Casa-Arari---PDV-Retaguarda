<?php

namespace App\Repositories\Interfaces;

interface PaginationInterface
{
    /**
     * @return stdClass[]
     */
    public function items(): array;
    public function total(): int;
    public function isFirstPage(): bool;
    public function isLastPage(): bool;
    public function getNumberCurrentPage(): int;
    public function getNumberPreviousPage(): int;
    public function getNumberNextPage(): int;
}
