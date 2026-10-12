<?php

declare(strict_types=1);

namespace App\Services;

use Carbon\CarbonImmutable;

final class boq_rule_service
{
    public const RULES = [
        'plate' => [
            'label' => 'Pelat',
            'unit' => 'kg',
            'calculator' => true,
            'round_up' => false,
            'hint' => 'Berat = panjang × lebar × tebal × massa jenis × faktor keamanan. Minimum berlaku per keping.',
        ],
        'pipe' => [
            'label' => 'Pipa',
            'unit' => 'm',
            'calculator' => true,
            'round_up' => false,
            'hint' => 'Per batang: di bawah 0,5 m ditagih 0,5 m; di atas 0,5 m sampai di bawah 1 m ditagih 1 m. Berlaku untuk semua ukuran pipa.',
        ],
        'docking' => [
            'label' => 'Docking',
            'unit' => 'hari',
            'calculator' => false,
            'round_up' => true,
            'hint' => 'Hari dibulatkan ke atas. Minimum dihitung dari total seluruh pekerjaan dalam proyek.',
        ],
        'crane' => [
            'label' => 'Crane',
            'unit' => 'jam',
            'calculator' => false,
            'round_up' => true,
            'hint' => 'Jam dibulatkan ke atas. Minimum dihitung dari total seluruh pekerjaan dalam proyek.',
        ],
        'tugboat' => [
            'label' => 'Kapal Tunda',
            'unit' => 'jam',
            'calculator' => true,
            'round_up' => false,
            'hint' => 'Tarif mengikuti hari dan jam pemakaian. Durasi tiap pemakaian dibulatkan ke atas per jam.',
        ],
        'area' => [
            'label' => 'Luasan (Cleaning / Blasting / Pengecatan)',
            'unit' => 'm²',
            'calculator' => false,
            'round_up' => false,
            'hint' => 'Ditagih per m². Isi minimum pada item hanya bila diperlukan (mis. cleaning).',
        ],
    ];

    public const PLATE_MATERIALS = [
        'steel' => ['label' => 'Baja', 'density' => 7850],
        'aluminium' => ['label' => 'Aluminium', 'density' => 2700],
        'brass' => ['label' => 'Kuningan', 'density' => 8400],
    ];

    // Minutes from midnight; anything outside "day" and "evening" is the night band.
    public const TUGBOAT_BANDS = [
        'day' => ['label' => '07.30–16.30', 'from' => 450, 'to' => 990],
        'evening' => ['label' => '16.30–22.30', 'from' => 990, 'to' => 1350],
        'night' => ['label' => '22.30–07.30', 'from' => 1350, 'to' => 450],
    ];

    public const TUGBOAT_RATES = [
        'weekday' => ['label' => 'Senin–Jumat', 'day' => 100, 'evening' => 150, 'night' => 200],
        'weekend' => ['label' => 'Sabtu, Minggu & Libur', 'day' => 200, 'evening' => 250, 'night' => 300],
    ];

    public const MAX_TUGBOAT_ENTRIES = 20;

    public const MAX_TUGBOAT_HOURS = 72;

    public function unit_for(?string $rule): ?string
    {
        return self::RULES[$rule ?? '']['unit'] ?? null;
    }

    public function has_calculator(?string $rule): bool
    {
        return self::RULES[$rule ?? '']['calculator'] ?? false;
    }

    // Plate minimums apply per piece inside the calculator, pipe steps replace minimums, and every
    // other minimum is compared with the project total of that price item.
    public function minimum_scope(?string $rule, ?array $calculation): ?string
    {
        $type = $calculation['type'] ?? null;

        return match (true) {
            $rule === 'plate' && $type === 'plate' => 'piece',
            $rule === 'pipe' && $type === 'pipe' => null,
            default => 'project',
        };
    }

    /** @return array{calculation: array, volume: string} */
    public function calculate(string $rule, array $input): array
    {
        return match ($rule) {
            'plate' => $this->calculate_plate($input),
            'pipe' => $this->calculate_pipe($input),
            'tugboat' => $this->calculate_tugboat($input),
        };
    }

    // Quantity to charge before project minimums; piece minimums are applied here.
    public function billed_base(?string $rule, ?array $calculation, mixed $volume, ?string $minimum): ?string
    {
        if ($volume === null || $volume === '') {
            return null;
        }

        $type = $calculation['type'] ?? null;

        if ($rule === 'plate' && $type === 'plate') {
            $piece = (string) $calculation['piece_weight_kg'];
            if ($minimum !== null && bccomp($piece, $minimum, 4) < 0) {
                $piece = $minimum;
            }

            return bcmul($piece, (string) $calculation['quantity'], 4);
        }

        if ($rule === 'pipe' && $type === 'pipe') {
            return bcadd((string) $calculation['billed_total_m'], '0', 4);
        }

        if ($rule === 'tugboat' && $type === 'tugboat') {
            return bcadd((string) $calculation['equivalent_hours'], '0', 4);
        }

        $volume = bcadd((string) $volume, '0', 4);

        return (self::RULES[$rule ?? '']['round_up'] ?? false) ? $this->ceil($volume) : $volume;
    }

    // Customer-facing text deliberately omits weights and billed lengths that would expose minimums.
    public function describe(?array $calculation, bool $internal): ?string
    {
        return match ($calculation['type'] ?? null) {
            'plate' => $this->describe_plate($calculation, $internal),
            'pipe' => $this->describe_pipe($calculation, $internal),
            'tugboat' => $this->describe_tugboat($calculation),
            default => null,
        };
    }

    public function format_number(mixed $value, int $decimals = 4): string
    {
        $formatted = number_format((float) $value, $decimals, ',', '.');

        return str_contains($formatted, ',') ? rtrim(rtrim($formatted, '0'), ',') : $formatted;
    }

    private function calculate_plate(array $input): array
    {
        $material = $input['material'];
        $density = self::PLATE_MATERIALS[$material]['density'];
        $quantity = (int) $input['quantity'];
        $length = (float) $input['length'];
        $width = (float) $input['width'];
        $thickness = (float) $input['thickness'];
        $safety_factor = (float) $input['safety_factor'];
        $piece_weight = round($length * $width * $thickness * 1e-9 * $density * $safety_factor, 4);

        return [
            'calculation' => [
                'type' => 'plate',
                'material' => $material,
                'density' => $density,
                'length' => $length,
                'width' => $width,
                'thickness' => $thickness,
                'quantity' => $quantity,
                'safety_factor' => $safety_factor,
                'piece_weight_kg' => $piece_weight,
                'weight_kg' => round($piece_weight * $quantity, 4),
            ],
            'volume' => number_format($piece_weight * $quantity, 4, '.', ''),
        ];
    }

    private function calculate_pipe(array $input): array
    {
        $length = bcadd((string) $input['length_m'], '0', 4);
        $quantity = (int) $input['quantity'];
        $billed = match (true) {
            bccomp($length, '0.5', 4) <= 0 => '0.5',
            bccomp($length, '1', 4) < 0 => '1',
            default => $length,
        };

        return [
            'calculation' => [
                'type' => 'pipe',
                'size' => ($input['size'] ?? '') !== '' ? (string) $input['size'] : null,
                'length_m' => (float) $length,
                'quantity' => $quantity,
                'billed_length_m' => (float) $billed,
                'billed_total_m' => (float) bcmul($billed, (string) $quantity, 4),
            ],
            'volume' => bcmul($length, (string) $quantity, 4),
        ];
    }

    private function calculate_tugboat(array $input): array
    {
        $entries = [];
        $total_hours = 0;
        $equivalent_minutes = 0;

        foreach ($input['entries'] as $entry) {
            $start = CarbonImmutable::parse($entry['start_at'], config('app.timezone'))->startOfMinute();
            $end = CarbonImmutable::parse($entry['end_at'], config('app.timezone'))->startOfMinute();
            $is_holiday = filter_var($entry['is_holiday'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $billed_hours = (int) ceil($start->diffInMinutes($end) / 60);
            $bands = $this->split_tugboat_usage($start, $start->addHours($billed_hours), $is_holiday);

            foreach ($bands as $band) {
                $equivalent_minutes += $band['minutes'] * $band['percent'];
            }

            $total_hours += $billed_hours;
            $entries[] = [
                'start_at' => $start->format('Y-m-d\TH:i'),
                'end_at' => $end->format('Y-m-d\TH:i'),
                'is_holiday' => $is_holiday,
                'billed_hours' => $billed_hours,
                'bands' => array_map(fn (array $band): array => [
                    'day_type' => $band['day_type'],
                    'band' => $band['band'],
                    'hours' => round($band['minutes'] / 60, 4),
                    'percent' => $band['percent'],
                ], $bands),
            ];
        }

        $equivalent_hours = round($equivalent_minutes / 6000, 4);

        return [
            'calculation' => [
                'type' => 'tugboat',
                'entries' => $entries,
                'total_hours' => $total_hours,
                'equivalent_hours' => $equivalent_hours,
            ],
            'volume' => number_format($total_hours, 4, '.', ''),
        ];
    }

    /** @return list<array{day_type: string, band: string, minutes: int, percent: int}> */
    private function split_tugboat_usage(CarbonImmutable $start, CarbonImmutable $end, bool $is_holiday): array
    {
        $buckets = [];
        $cursor = $start;

        while ($cursor < $end) {
            $minute = $cursor->hour * 60 + $cursor->minute;
            [$band, $band_end] = match (true) {
                $minute >= 450 && $minute < 990 => ['day', 990],
                $minute >= 990 && $minute < 1350 => ['evening', 1350],
                $minute < 450 => ['night', 450],
                default => ['night', 1440],
            };
            $segment_end = $cursor->startOfDay()->addMinutes($band_end);
            $segment_end = $segment_end < $end ? $segment_end : $end;
            $day_type = $is_holiday || $cursor->isWeekend() ? 'weekend' : 'weekday';
            $key = $day_type.'.'.$band;

            $buckets[$key] ??= ['day_type' => $day_type, 'band' => $band, 'minutes' => 0, 'percent' => self::TUGBOAT_RATES[$day_type][$band]];
            $buckets[$key]['minutes'] += (int) $cursor->diffInMinutes($segment_end);
            $cursor = $segment_end;
        }

        return array_values($buckets);
    }

    private function describe_plate(array $calculation, bool $internal): string
    {
        $text = (self::PLATE_MATERIALS[$calculation['material'] ?? '']['label'] ?? 'Pelat')
            .' '.$this->format_number($calculation['length'], 2)
            .' × '.$this->format_number($calculation['width'], 2)
            .' × '.$this->format_number($calculation['thickness'], 2).' mm'
            .' × '.$calculation['quantity'].' keping'
            .' · faktor '.$this->format_number($calculation['safety_factor'], 2);

        return $internal
            ? $text.' · '.$this->format_number($calculation['piece_weight_kg']).' kg/keping'
            : $text;
    }

    private function describe_pipe(array $calculation, bool $internal): string
    {
        $text = trim('Pipa '.($calculation['size'] ?? ''))
            .' · '.$this->format_number($calculation['length_m']).' m × '.$calculation['quantity'].' batang';

        return $internal && (float) $calculation['billed_length_m'] !== (float) $calculation['length_m']
            ? $text.' · ditagih '.$this->format_number($calculation['billed_length_m']).' m/batang'
            : $text;
    }

    private function describe_tugboat(array $calculation): string
    {
        return collect($calculation['entries'])->map(function (array $entry): string {
            $start = CarbonImmutable::parse($entry['start_at'])->locale('id');
            $end = CarbonImmutable::parse($entry['end_at'])->locale('id');
            $bands = collect($entry['bands'])
                ->map(fn (array $band): string => $this->format_number($band['hours']).' jam × '.$band['percent'].'%')
                ->implode(' + ');

            return $start->translatedFormat('D, j M Y H.i').'–'.$end->translatedFormat($start->isSameDay($end) ? 'H.i' : 'D, j M H.i')
                .' WIB'.($entry['is_holiday'] ? ' (libur)' : '').': '.$bands;
        })->implode('; ');
    }

    private function ceil(string $value): string
    {
        $whole = bcadd($value, '0', 0);

        return bccomp($value, $whole, 4) > 0 ? bcadd($whole, '1', 4) : bcadd($whole, '0', 4);
    }
}
