<?php

namespace App\Repositories\Presenters;

use App\Repositories\Interfaces\PaginationInterface;
use Illuminate\Pagination\LengthAwarePaginator;
class PaginationPresenter implements PaginationInterface
{
    /**
     * @var \stdClass[]
     */
    private array $items;
    public function __construct(
        protected LengthAwarePaginator $lengthAwarePaginator
    )
    {
        $this->items = $this->resolveItems($this->lengthAwarePaginator->items());
    }

    public function items(): array
    {
        return $this->items;
    }

    public function total(): int
    {
        return $this->lengthAwarePaginator->total() ?? 0;
    }

    public function isFirstPage(): bool
    {
        return $this->lengthAwarePaginator->onFirstPage();
    }

    public function isLastPage(): bool
    {
        return $this->lengthAwarePaginator->onLastPage();
    }

    public function getNumberCurrentPage(): int
    {
        return $this->lengthAwarePaginator->currentPage() ?? 1;
    }

    public function getNumberPreviousPage(): int
    {
        return $this->getNumberCurrentPage() - 1;
    }

    public function getNumberNextPage(): int
    {
        return $this->getNumberCurrentPage() + 1;
    }

    private function resolveItems(array $items): array
    {
        $response = [];
        foreach ($items as $item)
        {
            $stdClassObject = new \stdClass;
            foreach ($item->toArray() as $key => $value) {
                $stdClassObject->{$key} = $value;
            }
            array_push($response, $stdClassObject);
        }

        return $response;
    }
}
