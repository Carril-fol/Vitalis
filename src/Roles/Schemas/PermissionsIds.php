<?php
namespace App\Roles\Models;

final class PermissionIds
{
    /** @var int[] */
    private array $ids;

    /** @param int[] $ids */
    public function __construct(array $ids)
    {
        $this->ids = array_values(array_unique(
            array_filter($ids, static fn(int $id): bool => $id > 0),
            SORT_REGULAR
        ));
    }

    /** @return int[] */
    public function toArray(): array
    {
        return $this->ids;
    }

    public function isEmpty(): bool
    {
        return $this->ids === array();
    }
}