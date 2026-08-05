<?php

namespace App\Services;

use App\Models\DockingSpace;
use App\Models\Ship;
use Illuminate\Support\Collection;

class DockingCapacityService
{
    public function evaluate_ship_for_space(Ship $ship, DockingSpace $docking_space, ?int $current_occupancy_count = null): array
    {
        $ship_draft = $this->to_decimal($ship->loaded_draft ?? $ship->empty_draft);
        $ship_breadth = $this->to_decimal($ship->breadth);
        $ship_length = $this->to_decimal($ship->length_overall);
        $ship_tonnage = $this->to_decimal($ship->gross_tonnage ?? $ship->net_tonnage);

        $space_max_draft = $this->to_decimal($docking_space->max_draft);
        $space_max_breadth = $this->to_decimal($docking_space->max_width ?? $docking_space->max_breadth);
        $space_max_length = $this->to_decimal($docking_space->max_length);
        $space_max_tonnage = $this->to_decimal($docking_space->max_weight ?? $docking_space->max_tonnage);

        $occupancy_count = $current_occupancy_count
            ?? $docking_space->current_docking_occupancies()->count();
        $max_capacity = (int) ($this->to_decimal($docking_space->max_capacity) ?? 0);

        $checks = [
            'status_active' => $docking_space->status === 'active',
            'draft' => $this->compare_value($ship_draft, $space_max_draft),
            'breadth' => $this->compare_value($ship_breadth, $space_max_breadth),
            'length' => $space_max_length === null ? true : $this->compare_value($ship_length, $space_max_length),
            'tonnage' => $this->compare_value($ship_tonnage, $space_max_tonnage),
            'capacity' => $max_capacity > 0 && $occupancy_count < $max_capacity,
        ];

        $violations = [];

        if (!$checks['status_active']) {
            $violations[] = 'Docking space sedang tidak aktif.';
        }

        if (!$checks['draft']) {
            $violations[] = 'Draft kapal melebihi batas draft docking space.';
        }

        if (!$checks['breadth']) {
            $violations[] = 'Lebar kapal melebihi batas lebar docking space.';
        }

        if (!$checks['length']) {
            $violations[] = 'Panjang kapal melebihi batas panjang docking space.';
        }

        if (!$checks['tonnage']) {
            $violations[] = 'Tonnage/berat kapal melebihi kapasitas docking space.';
        }

        if (!$checks['capacity']) {
            $violations[] = 'Docking space sudah mencapai kapasitas okupansi maksimum.';
        }

        $is_compatible = empty($violations);
        $passed_checks = count(array_filter($checks));
        $compatibility_score = (int) round(($passed_checks / count($checks)) * 100);

        return [
            'is_compatible' => $is_compatible,
            'compatibility_score' => $compatibility_score,
            'checks' => $checks,
            'violations' => $violations,
            'ship_snapshot' => [
                'ship_id' => $ship->id,
                'draft' => $ship_draft,
                'breadth' => $ship_breadth,
                'length_overall' => $ship_length,
                'gross_tonnage' => $ship_tonnage,
            ],
            'docking_space_snapshot' => [
                'docking_space_id' => $docking_space->id,
                'max_draft' => $space_max_draft,
                'max_breadth' => $space_max_breadth,
                'max_length' => $space_max_length,
                'max_tonnage' => $space_max_tonnage,
                'max_capacity' => $max_capacity,
                'current_occupancy_count' => $occupancy_count,
            ],
        ];
    }

    public function evaluate_ship_for_spaces(Ship $ship, Collection $docking_spaces): Collection
    {
        return $docking_spaces->map(function (DockingSpace $docking_space) use ($ship): array {
            return [
                'docking_space' => $docking_space,
                'evaluation' => $this->evaluate_ship_for_space($ship, $docking_space),
            ];
        });
    }

    private function compare_value(?float $ship_value, ?float $space_limit): bool
    {
        if ($ship_value === null || $space_limit === null) {
            return false;
        }

        return $ship_value <= $space_limit;
    }

    private function to_decimal(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        $sanitized = preg_replace('/[^0-9.\-]/', '', (string) $value);

        if ($sanitized === null || $sanitized === '' || !is_numeric($sanitized)) {
            return null;
        }

        return (float) $sanitized;
    }
}
