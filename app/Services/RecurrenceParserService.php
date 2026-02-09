<?php

namespace App\Services;

use App\Enums\FrequencyType;

class RecurrenceParserService
{
    private const DAY_MAP = [
        'montag' => 1,
        'dienstag' => 2,
        'mittwoch' => 3,
        'donnerstag' => 4,
        'freitag' => 5,
        'samstag' => 6,
        'sonntag' => 0,
    ];

    /**
     * Parst eine Eingabe im Format: "Name, Menge Einheit, Wiederholung"
     * Beispiele:
     *   "Milch, 1L, woechentlich am Montag"
     *   "Brot, 1 Stueck, taeglich"
     *   "Eier, 10 Stueck, alle 2 Wochen am Freitag"
     *   "Mehl, 1kg, monatlich am 15."
     */
    public function parse(string $input): ?array
    {
        $parts = array_map('trim', explode(',', $input));

        if (count($parts) < 2) {
            return null;
        }

        $name = $parts[0];
        if (empty($name)) {
            return null;
        }

        // Menge + Einheit parsen (2. Teil)
        $amount = null;
        $unit = null;
        $frequencyRaw = '';

        if (count($parts) >= 3) {
            // Format: Name, Menge Einheit, Wiederholung
            $amountPart = $parts[1];
            $frequencyRaw = implode(', ', array_slice($parts, 2));
            $this->parseAmount($amountPart, $amount, $unit);
        } else {
            // Format: Name, Wiederholung (ohne Menge)
            $frequencyRaw = $parts[1];
        }

        // Wiederholung parsen
        $frequency = $this->parseFrequency($frequencyRaw);

        if (!$frequency) {
            return null;
        }

        return [
            'name' => $name,
            'amount' => $amount,
            'unit' => $unit,
            'frequency_type' => $frequency['type'],
            'frequency_day' => $frequency['day'],
            'frequency_interval' => $frequency['interval'],
            'raw_input' => $input,
        ];
    }

    private function parseAmount(string $input, ?float &$amount, ?string &$unit): void
    {
        $input = trim($input);

        // Muster: "1.5 kg", "1L", "10 Stueck", "500g", "0,5 l"
        if (preg_match('/^(\d+(?:[.,]\d+)?)\s*(.*)$/u', $input, $matches)) {
            $amount = (float) str_replace(',', '.', $matches[1]);
            $unit = trim($matches[2]) ?: null;
        } else {
            // Kein numerischer Wert -> alles als Einheit/Name behandeln
            $unit = $input ?: null;
        }
    }

    private function parseFrequency(string $input): ?array
    {
        $input = mb_strtolower(trim($input));

        // "taeglich" / "jeden Tag" / "taeglich"
        if (preg_match('/^(t[aä]glich|jeden\s+tag)$/u', $input)) {
            return [
                'type' => FrequencyType::Daily,
                'day' => null,
                'interval' => 1,
            ];
        }

        // "woechentlich am Montag" / "jeden Montag" / "woechentlich"
        if (preg_match('/^(w[oö]chentlich)(?:\s+am\s+(\w+))?$/u', $input, $matches)) {
            $day = isset($matches[2]) ? $this->resolveDay($matches[2]) : null;
            return [
                'type' => FrequencyType::Weekly,
                'day' => $day,
                'interval' => 1,
            ];
        }

        if (preg_match('/^jeden\s+(\w+)$/u', $input, $matches)) {
            $day = $this->resolveDay($matches[1]);
            if ($day !== null) {
                return [
                    'type' => FrequencyType::Weekly,
                    'day' => $day,
                    'interval' => 1,
                ];
            }
        }

        // "alle X Wochen am Tag"
        if (preg_match('/^alle\s+(\d+)\s+wochen(?:\s+am\s+(\w+))?$/u', $input, $matches)) {
            $interval = (int) $matches[1];
            $day = isset($matches[2]) ? $this->resolveDay($matches[2]) : null;
            return [
                'type' => FrequencyType::EveryXWeeks,
                'day' => $day,
                'interval' => max(1, $interval),
            ];
        }

        // "monatlich am 15." / "monatlich am 1."
        if (preg_match('/^monatlich\s+am\s+(\d+)\.?$/u', $input, $matches)) {
            $dayOfMonth = (int) $matches[1];
            if ($dayOfMonth >= 1 && $dayOfMonth <= 31) {
                return [
                    'type' => FrequencyType::MonthlyOnDay,
                    'day' => $dayOfMonth,
                    'interval' => 1,
                ];
            }
        }

        // "monatlich"
        if (preg_match('/^monatlich$/u', $input)) {
            return [
                'type' => FrequencyType::Monthly,
                'day' => null,
                'interval' => 1,
            ];
        }

        return null;
    }

    private function resolveDay(string $dayName): ?int
    {
        $dayName = mb_strtolower(trim($dayName));

        return self::DAY_MAP[$dayName] ?? null;
    }

    /**
     * Gibt die deutsche Beschreibung einer Wiederholung zurueck.
     */
    public static function describe(FrequencyType $type, ?int $day, int $interval): string
    {
        $dayNames = array_flip(self::DAY_MAP);
        $dayNameMap = [
            0 => 'Sonntag',
            1 => 'Montag',
            2 => 'Dienstag',
            3 => 'Mittwoch',
            4 => 'Donnerstag',
            5 => 'Freitag',
            6 => 'Samstag',
        ];

        return match ($type) {
            FrequencyType::Daily => 'Taeglich',
            FrequencyType::Weekly => 'Woechentlich' . ($day !== null && isset($dayNameMap[$day]) ? ' am ' . $dayNameMap[$day] : ''),
            FrequencyType::Monthly => 'Monatlich',
            FrequencyType::MonthlyOnDay => 'Monatlich am ' . $day . '.',
            FrequencyType::EveryXWeeks => 'Alle ' . $interval . ' Wochen' . ($day !== null && isset($dayNameMap[$day]) ? ' am ' . $dayNameMap[$day] : ''),
        };
    }
}
