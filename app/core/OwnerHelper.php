<?php
/**
 * Helper to resolve owner_id and is_shared from POST data
 */
trait OwnerHelper
{
    protected function resolveOwner(array $data): array
    {
        $rawOwner = $data['owner_id'] ?? null;
        $isShared = !empty($data['is_shared']) && $data['is_shared'] == '1';

        if ($rawOwner === 'shared' || $isShared) {
            return ['owner_id' => null, 'is_shared' => 1];
        }

        return [
            'owner_id'  => !empty($rawOwner) ? (int)$rawOwner : null,
            'is_shared' => 0,
        ];
    }
}
