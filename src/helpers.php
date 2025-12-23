<?php

/**
 * Roster Helpers
 *
 * Collection of helper functions for the Roster package.
 */
use Carbon\WeekDay;
use Carbon\Month;
use Illuminate\Support\Carbon;

if (!function_exists('roster_day_of_week')) {
    /**
     * Retourne le jour de la semaine d'une date.
     *
     * @param string|DateTimeInterface $date Exemple : '2038-07-01' ou new DateTime('2038-07-01')
     * @return string|null 'monday', 'tuesday', ... ou null si date invalide
     */
    function roster_day_of_week($date): ?string
    {
        try {
            $dt = $date instanceof DateTimeInterface ? $date : new DateTime($date);

            return strtolower($dt->format('l')); // 'Monday' -> 'monday'
        } catch (Exception $exception) {
            return null; // date invalide
        }
    }
}

if (!function_exists('roster_days_in_period')) {
    /**
     * Retourne tous les jours d'une période.
     *
     * @param string|DateTimeInterface|Carbon $startDate Date de début
     * @param string|DateTimeInterface|Carbon $endDate Date de fin
     * @return array<string> Liste des jours (ex: ['monday', 'tuesday'])
     */
    function roster_days_in_period(DateTimeInterface|WeekDay|Month|string|int|float|null $startDate, DateTimeInterface|WeekDay|Month|string|int|float|null $endDate): array
    {
        try {
            // Utiliser Carbon qui est déjà dans les dépendances
            $start = Carbon::parse($startDate);
            $end = Carbon::parse($endDate);

            $days = [];
            $currentDate = $start->copy();

            while ($currentDate <= $end) {
                $days[] = strtolower($currentDate->format('l'));
                $currentDate->addDay();
            }

            return array_unique($days);
        } catch (Exception $exception) {
            return [];
        }
    }
}

if (!function_exists('roster_format_period_days_for_display')) {
    /**
     * Formate les jours d'une période pour l'affichage.
     * Détecte les séquences continues et les formate comme "X to Y".
     *
     * @param array<string> $days Liste des jours (doit être triée)
     * @return string Chaîne formatée (ex: "Thursday to Sunday" ou "Monday, Wednesday and Friday")
     */
    function roster_format_period_days_for_display(array $days): string
    {
        if ($days === []) {
            return '';
        }

        // S'assurer que les jours sont triés
        $dayOrder = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        usort($days, function ($a, $b) use ($dayOrder): int {
            return array_search($a, $dayOrder, true) <=> array_search($b, $dayOrder, true);
        });

        // Si un seul jour
        if (count($days) === 1) {
            return ucfirst($days[0]);
        }

        // Vérifier si c'est une séquence continue
        $isContinuous = true;
        $dayIndices = array_map(fn($day): false|int => array_search($day, $dayOrder, true), $days);

        for ($i = 0; $i < count($dayIndices) - 1; ++$i) {
            $current = $dayIndices[$i];
            $next = $dayIndices[$i + 1];

            // Deux jours sont consécutifs si :
            // 1. next = current + 1 (cas normal)
            // 2. OU current = 6 (dimanche) et next = 0 (lundi) - traverse le weekend
            if ($next !== $current + 1 && !($current === 6 && $next === 0)) {
                $isContinuous = false;
                break;
            }
        }

        // Si c'est une séquence continue, formater comme "X to Y"
        if ($isContinuous) {
            // Vérifier si la séquence traverse le weekend
            // Si oui, nous avons peut-être besoin d'un format spécial
            $first = ucfirst($days[0]);
            $last = ucfirst(end($days));

            // Si la séquence commence par dimanche et finit par samedi,
            // c'est que nous avons tous les jours
            if ($days[0] === 'sunday' && end($days) === 'saturday') {
                return 'Monday to Sunday'; // Format plus logique
            }

            return sprintf('%s to %s', $first, $last);
        }

        // Sinon, utiliser le format normal
        return roster_format_days_for_display($days);
    }
}

if (!function_exists('roster_format_days_for_display')) {
    /**
     * Formate une liste de jours pour l'affichage.
     *
     * @param array<string> $days Liste des jours
     * @return string Chaîne formatée (ex: "Monday, Tuesday and Thursday")
     */
    function roster_format_days_for_display(array $days): string
    {
        if ($days === []) {
            return '';
        }

        $capitalized = array_map('ucfirst', $days);

        if (count($capitalized) === 1) {
            return $capitalized[0];
        }

        if (count($capitalized) === 2) {
            return $capitalized[0] . ' and ' . $capitalized[1];
        }

        $last = array_pop($capitalized);
        return implode(', ', $capitalized) . ' and ' . $last;
    }
}

if (!function_exists('roster_period_duration_in_days')) {
    /**
     * Calcule la durée d'une période en jours.
     *
     * @param string|DateTimeInterface $startDate Date de début
     * @param string|DateTimeInterface $endDate Date de fin
     * @return int|null Nombre de jours ou null si dates invalides
     */
    function roster_period_duration_in_days($startDate, $endDate): ?int
    {
        try {
            if (!$startDate instanceof DateTimeInterface) {
                $startDate = new DateTime($startDate);
            }

            if (!$endDate instanceof DateTimeInterface) {
                $endDate = new DateTime($endDate);
            }

            // Ajouter 1 pour inclure le jour de fin
            return (int) $startDate->diff($endDate)->days + 1;
        } catch (Exception $exception) {
            return null;
        }
    }
}

if (!function_exists('roster_is_day_in_period')) {
    /**
     * Vérifie si un jour de la semaine est dans une période.
     *
     * @param string $day Jour à vérifier (ex: 'monday')
     * @param string|DateTimeInterface $startDate Date de début
     * @param string|DateTimeInterface $endDate Date de fin
     */
    function roster_is_day_in_period(string $day, DateTimeInterface|WeekDay|Month|string|int|float|null $startDate, DateTimeInterface|WeekDay|Month|string|int|float|null $endDate): bool
    {
        $daysInPeriod = roster_days_in_period($startDate, $endDate);
        return in_array($day, $daysInPeriod, true);
    }
}

if (!function_exists('roster_get_valid_days_in_period')) {
    /**
     * Filtre une liste de jours pour ne garder que ceux dans la période.
     *
     * @param array<string> $days Liste des jours à filtrer
     * @param string|DateTimeInterface $startDate Date de début
     * @param string|DateTimeInterface $endDate Date de fin
     * @return array<string> Jours filtrés
     */
    function roster_get_valid_days_in_period(array $days, DateTimeInterface|WeekDay|Month|string|int|float|null $startDate, DateTimeInterface|WeekDay|Month|string|int|float|null $endDate): array
    {
        $daysInPeriod = roster_days_in_period($startDate, $endDate);
        $validDays = array_intersect($days, $daysInPeriod);

        // Trier selon l'ordre des jours de la semaine
        $dayOrder = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        usort($validDays, function ($a, $b) use ($dayOrder): int {
            return array_search($a, $dayOrder, true) <=> array_search($b, $dayOrder, true);
        });

        return array_values($validDays);
    }
}

if (!function_exists('roster_should_auto_adjust_days')) {
    /**
     * Détermine si les jours doivent être ajustés automatiquement.
     *
     * @param string|DateTimeInterface|null $startDate Date de début
     * @param string|DateTimeInterface|null $endDate Date de fin
     */
    function roster_should_auto_adjust_days($startDate, $endDate): bool
    {
        // Si pas de dates, pas d'ajustement
        if ($startDate === null || $endDate === null) {
            return false;
        }

        try {
            $duration = roster_period_duration_in_days($startDate, $endDate);
            return $duration !== null && $duration < 7;
        } catch (Exception $exception) {
            return false;
        }
    }
}
