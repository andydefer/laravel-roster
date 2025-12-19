Voici le fichier d'exemples mis à jour avec les nouvelles fonctionnalités de prévention des chevauchements :

```markdown
# Exemple complet d'utilisation du `ImpedimentService` via la façade `Roster\Impediment`

Voici des exemples détaillés montrant comment utiliser toutes les fonctionnalités du `ImpedimentService` dans une application Laravel, incluant la nouvelle prévention des chevauchements.

## Configuration initiale dans un modèle

```php
// App\Models\User.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    // Utiliser le trait pour gérer les planifications
    use \Roster\Traits\HasRoster;
}
```

## Prerequisites: Créer des disponibilités d'abord

```php
use Roster\Availability;
use App\Models\User;

$user = User::find(1);

// Créer des disponibilités pour le médecin
$consultationAvailability = Availability::for($user)->create([
    'type' => 'consultation',
    'start_time' => '08:00:00',
    'end_time' => '18:00:00',
    'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
    'start_date' => '2024-06-01',
    'end_date' => '2024-12-31',
]);

$trainingAvailability = Availability::for($user)->create([
    'type' => 'training',
    'start_time' => '18:00:00',
    'end_time' => '21:00:00',
    'days' => ['wednesday'],
    'start_date' => '2024-06-01',
    'end_date' => '2024-12-31',
]);
```

## ⚠️ NOUVEAUTÉ : Prévention des chevauchements d'impediments

### Règles de validation :
1. **Pas de chevauchement** : Deux impediments ne peuvent pas se chevaucher pour la même availability
2. **Validation automatique** : Vérification lors de la création et de la mise à jour
3. **Messages d'erreur clairs** : Exceptions détaillées en cas de conflit

## Exemple 1 : Créer des impediments (blocages de temps)

```php
use Roster\Impediment;
use App\Models\User;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

$user = User::find(1);
$now = Carbon::now();

// 1. Créer un impediment simple (pause déjeuner)
$lunchBreak = Impediment::for($user)->create([
    'reason' => 'Pause déjeuner',
    'start_datetime' => Carbon::parse('2024-06-03 12:00:00'),
    'end_datetime' => Carbon::parse('2024-06-03 13:00:00'),
    'metadata' => [
        'recurring' => true,
        'category' => 'meal_break'
    ]
]);

echo "Impediment créé avec l'ID: " . $lunchBreak->id;

// 2. Tentative de créer un impediment qui chevauche - ÉCHOUERA
try {
    $overlappingImpediment = Impediment::for($user)->create([
        'reason' => 'Réunion',
        'start_datetime' => Carbon::parse('2024-06-03 12:30:00'), // Chevauche avec pause déjeuner
        'end_datetime' => Carbon::parse('2024-06-03 13:30:00'),
    ]);
} catch (InvalidArgumentException $e) {
    echo "Erreur: " . $e->getMessage(); // "This time slot overlaps with an existing impediment"
}

// 3. Créer un impediment qui ne chevauche pas - RÉUSSIRA
$afternoonMeeting = Impediment::for($user)->create([
    'reason' => 'Réunion d\'équipe',
    'start_datetime' => Carbon::parse('2024-06-03 14:00:00'), // Après la pause
    'end_datetime' => Carbon::parse('2024-06-03 15:00:00'),
]);


Un impediment doit impérativement se situer **entièrement à l'intérieur des plages horaires** d'une disponibilité (`Availability`) existante. Il ne peut pas déborder en dehors.

**Exemple concret avec votre disponibilité :**
- Votre disponibilité "consultation" : **8h-18h du lundi au vendredi**
- Impediment valide : 10h-12h (entièrement entre 8h et 18h) ✅
- Impediment invalide : 7h-9h (déborde avant 8h) ❌
- Impediment invalide : 17h-19h (déborde après 18h) ❌
- Congé sur journée entière (0h-24h) : invalide car dépasse les bornes 8h-18h ❌

**Pour un congé de plusieurs jours** : Chaque journée du congé doit respecter les heures 8h-18h. C'est pourquoi votre congé du 15 juillet 8h au 5 août 18h est valide : chaque jour est bloqué seulement de 8h à 18h, pas la nuit.

**Solution alternative pour bloquer des journées complètes** : Créer une disponibilité spéciale "congés" avec des plages 0h-24h.
// 4. Créer un impediment de congé (plusieurs jours)
// Le congé doit être dans les heures de disponibilité (8h-18h) il
$vacation = Impediment::for($user)->create([
    'reason' => 'Congés annuels',
    'start_datetime' => Carbon::parse('2024-07-15 08:00:00'), // 8h du matin
    'end_datetime' => Carbon::parse('2024-08-05 18:00:00'),   // 18h le soir
    'metadata' => [
        'type' => 'vacation',
        'approved_by' => 'HR Department',
        'document_ref' => 'VAC-2024-007',
    ]
]);

// 5. Créer un impediment pour une formation
$training = Impediment::for($user)->create([
    'reason' => 'Formation médicale continue',
    'start_datetime' => Carbon::parse('2024-06-05 18:00:00'),
    'end_datetime' => Carbon::parse('2024-06-05 21:00:00'),
    'type' => 'training',
    'metadata' => [
        'training_title' => 'Nouvelles techniques chirurgicales',
        'organizer' => 'College of Surgeons',
        'credits' => 3
    ]
]);
```

## Exemple 2 : Mettre à jour des impediments avec validation de chevauchement

```php
use Roster\Impediment;
use App\Models\User;
use Illuminate\Support\Carbon;

$user = User::find(1);

// Créer deux impediments qui ne se chevauchent pas initialement
$impediment1 = Impediment::for($user)->create([
    'reason' => 'Pause déjeuner',
    'start_datetime' => Carbon::parse('2024-06-03 12:00:00'),
    'end_datetime' => Carbon::parse('2024-06-03 13:00:00'),
]);

$impediment2 = Impediment::for($user)->create([
    'reason' => 'Réunion',
    'start_datetime' => Carbon::parse('2024-06-03 14:00:00'),
    'end_datetime' => Carbon::parse('2024-06-03 15:00:00'),
]);

// 1. Mise à jour simple sans changement d'horaire - RÉUSSIT
$updated = Impediment::for($user)->update($impediment1->id, [
    'reason' => 'Pause déjeuner prolongée',
    'metadata' => ['extended_reason' => 'Réunion déjeuner client']
]);

// 2. Tentative de mettre à jour pour chevaucher l'autre impediment - ÉCHOUERA
try {
    Impediment::for($user)->update($impediment2->id, [
        'start_datetime' => Carbon::parse('2024-06-03 12:30:00'), // Chevauche avec impediment1
        'end_datetime' => Carbon::parse('2024-06-03 13:30:00'),
    ]);
} catch (InvalidArgumentException $e) {
    echo "Erreur: " . $e->getMessage(); // "This time slot overlaps with another impediment"
}

// 3. Mise à jour vers un horaire qui ne chevauche pas - RÉUSSIT
$updated = Impediment::for($user)->update($impediment2->id, [
    'start_datetime' => Carbon::parse('2024-06-03 15:00:00'),
    'end_datetime' => Carbon::parse('2024-06-03 16:00:00'),
]);

// 4. Mise à jour avec changement de disponibilité (si compatible)
try {
    $updated = Impediment::for($user)->update($impediment1->id, [
        'start_datetime' => Carbon::parse('2024-06-04 12:00:00'), // Jour différent
        'end_datetime' => Carbon::parse('2024-06-04 13:00:00'),
    ]);
} catch (InvalidArgumentException $e) {
    // Peut échouer si pas de disponibilité le 4 juin à cet horaire
    echo "Erreur: " . $e->getMessage();
}
```

## Exemple 3 : Rechercher et récupérer des impediments

```php
use Roster\Impediment;
use App\Models\User;
use Illuminate\Support\Carbon;

$user = User::find(1);

// 1. Trouver un impediment par ID
$impediment = Impediment::for($user)->find(1);
if ($impediment) {
    echo "Raison: " . $impediment->reason;
    echo "Période: " . $impediment->start_datetime->format('d/m/Y H:i') .
         " - " . $impediment->end_datetime->format('H:i');
    echo "Statut: " . ($impediment->isActive() ? 'Actif' :
                      ($impediment->isUpcoming() ? 'À venir' : 'Passé'));
}

// 2. Récupérer tous les impediments
$allImpediments = Impediment::for($user)->all();

// 3. Récupérer les impediments pour une période spécifique
$startDate = Carbon::parse('2024-06-01');
$endDate = Carbon::parse('2024-06-30');

$juneImpediments = Impediment::for($user)
    ->between($startDate, $endDate);

echo "Nombre d'impediments en juin: " . $juneImpediments->count();
```

## Exemple 4 : 🔍 NOUVEAU - Obtenir les créneaux disponibles entre les impediments

```php
use Roster\Impediment;
use App\Models\User;
use Illuminate\Support\Carbon;

$user = User::find(1);

// Créer quelques impediments pour démontrer
Impediment::for($user)->create([
    'reason' => 'Réunion matinale',
    'start_datetime' => Carbon::parse('2024-06-03 09:00:00'),
    'end_datetime' => Carbon::parse('2024-06-03 10:00:00'),
]);

Impediment::for($user)->create([
    'reason' => 'Pause déjeuner',
    'start_datetime' => Carbon::parse('2024-06-03 12:00:00'),
    'end_datetime' => Carbon::parse('2024-06-03 13:00:00'),
]);

Impediment::for($user)->create([
    'reason' => 'Formation',
    'start_datetime' => Carbon::parse('2024-06-03 15:00:00'),
    'end_datetime' => Carbon::parse('2024-06-03 16:30:00'),
]);

// Obtenir les créneaux disponibles pour la journée
$startOfDay = Carbon::parse('2024-06-03 08:00:00');
$endOfDay = Carbon::parse('2024-06-03 18:00:00');

$findSlotsInPeriod = Impediment::for($user)
    ->getAvailableTimeSlots($startOfDay, $endOfDay, 'consultation');

echo "Créneaux disponibles le 3 juin:";
foreach ($findSlotsInPeriod as $slot) {
    echo $slot['start']->format('H:i') . " - " . $slot['end']->format('H:i');
    // Affiche:
    // 08:00 - 09:00  (avant la réunion)
    // 10:00 - 12:00  (entre réunion et pause déjeuner)
    // 13:00 - 15:00  (après pause déjeuner, avant formation)
    // 16:30 - 18:00  (après formation)
}
```

## Exemple 5 : Vérifier si un créneau est bloqué

```php
use Roster\Impediment;
use App\Models\User;
use Illuminate\Support\Carbon;

$user = User::find(1);

// Créer un impediment
Impediment::for($user)->create([
    'reason' => 'Pause déjeuner',
    'start_datetime' => Carbon::parse('2024-06-03 12:00:00'),
    'end_datetime' => Carbon::parse('2024-06-03 13:00:00'),
]);

// 1. Vérifier un créneau qui chevauche complètement
$start = Carbon::parse('2024-06-03 12:30:00');
$end = Carbon::parse('2024-06-03 13:30:00');
$isBlocked = Impediment::for($user)->isTimeSlotBlocked($start, $end, 'consultation');
// $isBlocked = true

// 2. Vérifier un créneau qui touche juste le début
$start = Carbon::parse('2024-06-03 11:30:00');
$end = Carbon::parse('2024-06-03 12:30:00');
$isBlocked = Impediment::for($user)->isTimeSlotBlocked($start, $end, 'consultation');
// $isBlocked = true (chevauchement partiel)

// 3. Vérifier un créneau qui ne chevauche pas
$start = Carbon::parse('2024-06-03 14:00:00');
$end = Carbon::parse('2024-06-03 15:00:00');
$isBlocked = Impediment::for($user)->isTimeSlotBlocked($start, $end, 'consultation');
// $isBlocked = false

// 4. Vérifier avec un type spécifique
$isTrainingBlocked = Impediment::for($user)
    ->isTimeSlotBlocked($start, $end, 'training');
```

## Exemple 6 : Gestion des conflits avec les schedules

```php
use Roster\Impediment;
use Roster\Schedule;
use App\Models\User;
use Illuminate\Support\Carbon;

$user = User::find(1);

// Scénario 1: Tentative de création d'un impediment qui chevauche un schedule existant
$appointment = Schedule::for($user)->create([
    'title' => 'Consultation - M. Dupont',
    'start_datetime' => Carbon::parse('2024-06-03 14:00:00'),
    'end_datetime' => Carbon::parse('2024-06-03 15:00:00'),
    'status' => 'booked',
]);

try {
    $impediment = Impediment::for($user)->create([
        'reason' => 'Réunion urgente',
        'start_datetime' => Carbon::parse('2024-06-03 14:30:00'),
        'end_datetime' => Carbon::parse('2024-06-03 15:30:00'),
    ]);
} catch (InvalidArgumentException $e) {
    echo "Erreur: " . $e->getMessage(); // "Cannot create impediment that overlaps with existing schedules"
}

// Solution: Annuler d'abord le rendez-vous, puis créer l'impediment
Schedule::for($user)->delete($appointment->id);

// Maintenant la création réussit
$impediment = Impediment::for($user)->create([
    'reason' => 'Réunion urgente',
    'start_datetime' => Carbon::parse('2024-06-03 14:30:00'),
    'end_datetime' => Carbon::parse('2024-06-03 15:30:00'),
]);

// Scénario 2: Mise à jour d'un impediment - les schedules qui chevauchent sont automatiquement supprimés
$impediment = Impediment::for($user)->create([
    'reason' => 'Maintenance bureau',
    'start_datetime' => Carbon::parse('2024-06-04 08:00:00'),
    'end_datetime' => Carbon::parse('2024-06-04 09:00:00'),
]);

// Créer un rendez-vous qui sera impacté par l'extension de l'impediment
$appointment = Schedule::for($user)->create([
    'title' => 'Consultation matinale',
    'start_datetime' => Carbon::parse('2024-06-04 08:30:00'),
    'end_datetime' => Carbon::parse('2024-06-04 09:00:00'),
    'status' => 'booked',
]);

// Mettre à jour l'impediment pour qu'il dure plus longtemps
Impediment::for($user)->update($impediment->id, [
    'end_datetime' => Carbon::parse('2024-06-04 10:00:00'),
]);

// Le rendez-vous a été automatiquement supprimé
$remainingAppointments = Schedule::for($user)
    ->between(Carbon::parse('2024-06-04 00:00:00'), Carbon::parse('2024-06-04 23:59:59'))
    ->count();
// $remainingAppointments = 0
```

## Exemple 7 : Supprimer des impediments

```php
use Roster\Impediment;
use App\Models\User;

$user = User::find(1);

// Créer un impediment
$impediment = Impediment::for($user)->create([
    'reason' => 'Test impediment',
    'start_datetime' => Carbon::parse('2024-06-03 09:00:00'),
    'end_datetime' => Carbon::parse('2024-06-03 10:00:00'),
]);

// 1. Supprimer un impediment existant
$deleted = Impediment::for($user)->delete($impediment->id);
// $deleted = true

// 2. Tentative de suppression d'un impediment inexistant
$result = Impediment::for($user)->delete(999);
// $result = false

// 3. Supprimer après vérification
$impediment = Impediment::for($user)->find(2);
if ($impediment && $impediment->isUpcoming()) {
    // Ne supprimer que les impediments à venir
    $deleted = Impediment::for($user)->delete($impediment->id);
    echo "Impediment à venir supprimé";
}
```

## Exemple 8 : Filtrer les impediments

```php
use Roster\Impediment;
use App\Models\User;
use Illuminate\Support\Carbon;

$user = User::find(1);

// 1. Filtrer par type d'activité
$consultationImpediments = Impediment::for($user)
    ->whereType('consultation')
    ->get();

$trainingImpediments = Impediment::for($user)
    ->whereType('training')
    ->get();

// 2. Filtrer par date de début
$startingToday = Impediment::for($user)
    ->whereStartDate(Carbon::now())
    ->get();

// 3. Filtrer par date de fin
$endingThisMonth = Impediment::for($user)
    ->whereEndDate(Carbon::now()->endOfMonth())
    ->get();

// 4. Combinaison de filtres
$trainingThisMonth = Impediment::for($user)
    ->whereType('training')
    ->whereStartDate(Carbon::now()->startOfMonth())
    ->whereEndDate(Carbon::now()->endOfMonth())
    ->get();

// 5. Réinitialiser les filtres
$allAgain = Impediment::for($user)
    ->whereType('consultation')
    ->whereStartDate(Carbon::now())
    ->resetFilters()
    ->get(); // Retourne tous les impediments
```

## Exemple 9 : Cas d'usage avancés

### Gestion des pauses récurrentes
```php
use Roster\Impediment;
use App\Models\User;
use Illuminate\Support\Carbon;

$user = User::find(1);

// Créer des pauses déjeuner pour les 30 prochains jours ouvrables
for ($i = 0; $i < 30; $i++) {
    $date = Carbon::now()->addDays($i);

    if ($date->isWeekday()) {
        try {
            Impediment::for($user)->create([
                'reason' => 'Pause déjeuner',
                'start_datetime' => $date->copy()->setTime(12, 0),
                'end_datetime' => $date->copy()->setTime(13, 0),
                'metadata' => [
                    'recurring' => true,
                    'category' => 'daily_lunch_break',
                    'auto_generated' => true
                ]
            ]);
        } catch (InvalidArgumentException $e) {
            // Ignorer si déjà un impediment à cette heure (jour férié, etc.)
            echo "Skipping {$date->format('Y-m-d')}: " . $e->getMessage();
        }
    }
}
```

### Vérification de disponibilité pour un nouveau rendez-vous
```php
use Roster\Impediment;
use Roster\Schedule;
use App\Models\User;
use Illuminate\Support\Carbon;

function checkAvailabilityForAppointment(User $user, Carbon $proposedStart, Carbon $proposedEnd, string $type = 'consultation')
{
    // 1. Vérifier les impediments
    $impedimentBlocked = Impediment::for($user)
        ->isTimeSlotBlocked($proposedStart, $proposedEnd, $type);

    if ($impedimentBlocked) {
        return [
            'available' => false,
            'reason' => 'time_slot_blocked_by_impediment',
            'message' => 'Ce créneau est bloqué par un impediment'
        ];
    }

    // 2. Vérifier les rendez-vous existants
    $existingSchedule = Schedule::for($user)
        ->between($proposedStart, $proposedEnd)
        ->first();

    if ($existingSchedule) {
        return [
            'available' => false,
            'reason' => 'time_slot_already_booked',
            'message' => 'Ce créneau est déjà réservé'
        ];
    }

    // 3. Vérifier la disponibilité (heures de travail, etc.)
    $availability = $user->availabilities()
        ->where('type', $type)
        ->whereJsonContains('days', strtolower($proposedStart->englishDayOfWeek))
        ->where('start_time', '<=', $proposedStart->format('H:i:s'))
        ->where('end_time', '>=', $proposedEnd->format('H:i:s'))
        ->first();

    if (!$availability) {
        return [
            'available' => false,
            'reason' => 'outside_availability_hours',
            'message' => 'Hors des heures de disponibilité'
        ];
    }

    return [
        'available' => true,
        'message' => 'Créneau disponible'
    ];
}

// Utilisation
$user = User::find(1);
$proposedStart = Carbon::parse('2024-06-03 14:00:00');
$proposedEnd = Carbon::parse('2024-06-03 15:00:00');

$availability = checkAvailabilityForAppointment($user, $proposedStart, $proposedEnd);
if ($availability['available']) {
    // Créer le rendez-vous
    $appointment = Schedule::for($user)->create([
        'title' => 'Nouvelle consultation',
        'start_datetime' => $proposedStart,
        'end_datetime' => $proposedEnd,
        'status' => 'booked',
    ]);
}
```

## Exemple 10 : Intégration dans un contrôleur Laravel

```php
// App\Http\Controllers\ImpedimentController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Roster\Impediment;
use App\Models\User;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

class ImpedimentController extends Controller
{
    /**
     * Créer un nouvel impediment avec validation des chevauchements
     */
    public function store(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:255',
            'start_datetime' => 'required|date',
            'end_datetime' => 'required|date|after:start_datetime',
            'type' => 'nullable|string',
            'metadata' => 'nullable|array',
        ]);

        try {
            $impediment = Impediment::for($user)->create($validated);

            return response()->json([
                'message' => 'Impediment créé avec succès',
                'data' => $impediment,
                'available_slots' => $this->getAlternativeSlots($user, $validated),
            ], 201);

        } catch (InvalidArgumentException $e) {
            $errorMessage = $e->getMessage();

            // Fournir des suggestions basées sur le type d'erreur
            if (str_contains($errorMessage, 'overlaps with an existing impediment')) {
                return response()->json([
                    'message' => 'Ce créneau chevauche un impediment existant',
                    'suggestions' => $this->getAlternativeSlots($user, $validated),
                    'conflicts' => $this->getConflictingImpediments($user, $validated),
                ], 422);
            }

            if (str_contains($errorMessage, 'overlaps with existing schedules')) {
                return response()->json([
                    'message' => 'Ce créneau chevauche des rendez-vous existants',
                    'warning' => 'Les rendez-vous devront être annulés manuellement avant de créer cet impediment',
                ], 422);
            }

            return response()->json([
                'message' => $errorMessage,
            ], 422);
        }
    }

    /**
     * Vérifier la disponibilité d'un créneau
     */
    public function check(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'start' => 'required|date',
            'end' => 'required|date|after:start',
            'type' => 'nullable|string',
        ]);

        $start = Carbon::parse($request->get('start'));
        $end = Carbon::parse($request->get('end'));
        $type = $request->get('type');

        $isBlocked = Impediment::for($user)
            ->isTimeSlotBlocked($start, $end, $type);

        // Obtenir les créneaux disponibles autour de l'heure demandée
        $findSlotsInPeriod = Impediment::for($user)
            ->getAvailableTimeSlots(
                $start->copy()->subHours(2),
                $end->copy()->addHours(2),
                $type
            );

        return response()->json([
            'blocked' => $isBlocked,
            'available_slots' => $findSlotsInPeriod,
            'next_available' => $this->findNextAvailableSlot($user, $start, $end, $type),
        ]);
    }

    /**
     * Trouver les impediments en conflit
     */
    private function getConflictingImpediments(User $user, array $data): array
    {
        $start = Carbon::parse($data['start_datetime']);
        $end = Carbon::parse($data['end_datetime']);

        $impediments = Impediment::for($user)->get();

        $conflicts = [];
        foreach ($impediments as $impediment) {
            if ($impediment->overlapsWith($start, $end)) {
                $conflicts[] = [
                    'id' => $impediment->id,
                    'reason' => $impediment->reason,
                    'start' => $impediment->start_datetime->format('Y-m-d H:i'),
                    'end' => $impediment->end_datetime->format('H:i'),
                    'duration' => $impediment->duration_minutes . ' minutes',
                ];
            }
        }

        return $conflicts;
    }

    /**
     * Obtenir des créneaux alternatifs
     */
    private function getAlternativeSlots(User $user, array $originalRequest): array
    {
        $start = Carbon::parse($originalRequest['start_datetime']);
        $end = Carbon::parse($originalRequest['end_datetime']);
        $duration = $end->diffInMinutes($start);

        $suggestions = [];

        // Chercher des créneaux le même jour
        $sameDayEnd = $start->copy()->endOfDay();
        $findSlotsInPeriod = Impediment::for($user)
            ->getAvailableTimeSlots($start, $sameDayEnd, $originalRequest['type'] ?? null);

        foreach ($findSlotsInPeriod as $slot) {
            $slotDuration = $slot['start']->diffInMinutes($slot['end']);
            if ($slotDuration >= $duration) {
                $suggestions[] = [
                    'start' => $slot['start']->format('Y-m-d H:i'),
                    'end' => $slot['start']->copy()->addMinutes($duration)->format('H:i'),
                    'same_day' => true,
                ];

                if (count($suggestions) >= 3) break;
            }
        }

        return $suggestions;
    }

    /**
     * Trouver le prochain créneau disponible
     */
    private function findNextAvailableSlot(User $user, Carbon $start, Carbon $end, ?string $type): ?array
    {
        $duration = $end->diffInMinutes($start);

        // Chercher dans les 7 prochains jours
        for ($i = 0; $i < 7; $i++) {
            $searchDate = $start->copy()->addDays($i);

            // Vérifier si c'est un jour de disponibilité
            $availability = $user->availabilities()
                ->where('type', $type)
                ->whereJsonContains('days', strtolower($searchDate->englishDayOfWeek))
                ->first();

            if ($availability) {
                $dayStart = $searchDate->copy()->setTimeFromTimeString($availability->start_time);
                $dayEnd = $searchDate->copy()->setTimeFromTimeString($availability->end_time);

                $findSlotsInPeriod = Impediment::for($user)
                    ->getAvailableTimeSlots($dayStart, $dayEnd, $type);

                foreach ($findSlotsInPeriod as $slot) {
                    $slotDuration = $slot['start']->diffInMinutes($slot['end']);
                    if ($slotDuration >= $duration) {
                        return [
                            'date' => $searchDate->format('Y-m-d'),
                            'start' => $slot['start']->format('H:i'),
                            'end' => $slot['start']->copy()->addMinutes($duration)->format('H:i'),
                            'days_from_now' => $i,
                        ];
                    }
                }
            }
        }

        return null;
    }
}
```

## Exemple 11 : Interface utilisateur avec validation en temps réel

```javascript
// resources/js/components/ImpedimentForm.vue
<template>
  <div>
    <form @submit.prevent="submitForm">
      <div class="mb-3">
        <label for="reason" class="form-label">Raison</label>
        <input v-model="form.reason" type="text" class="form-control" required>
      </div>

      <div class="row mb-3">
        <div class="col-md-6">
          <label for="start_datetime" class="form-label">Date et heure de début</label>
          <input v-model="form.start_datetime" type="datetime-local" class="form-control" required>
        </div>
        <div class="col-md-6">
          <label for="end_datetime" class="form-label">Date et heure de fin</label>
          <input v-model="form.end_datetime" type="datetime-local" class="form-control" required>
        </div>
      </div>

      <div class="mb-3">
        <label for="type" class="form-label">Type d'activité</label>
        <select v-model="form.type" class="form-select">
          <option value="">Tous les types</option>
          <option value="consultation">Consultation</option>
          <option value="training">Formation</option>
          <option value="meeting">Réunion</option>
        </select>
      </div>

      <!-- Validation en temps réel -->
      <div v-if="validationResult" class="mb-3">
        <div v-if="validationResult.available" class="alert alert-success">
          <i class="bi bi-check-circle"></i> Ce créneau est disponible
        </div>
        <div v-else class="alert alert-danger">
          <i class="bi bi-exclamation-circle"></i> {{ validationResult.message }}

          <div v-if="validationResult.conflicts && validationResult.conflicts.length > 0" class="mt-2">
            <strong>Conflits:</strong>
            <ul class="mb-0">
              <li v-for="conflict in validationResult.conflicts" :key="conflict.id">
                {{ conflict.reason }} ({{ conflict.start }} - {{ conflict.end }})
              </li>
            </ul>
          </div>

          <div v-if="validationResult.suggestions && validationResult.suggestions.length > 0" class="mt-2">
            <strong>Suggestions:</strong>
            <ul class="mb-0">
              <li v-for="suggestion in validationResult.suggestions" :key="suggestion.start">
                {{ suggestion.start }} - {{ suggestion.end }}
              </li>
            </ul>
          </div>
        </div>
      </div>

      <button type="submit" class="btn btn-primary" :disabled="!formIsValid">
        Créer l'impediment
      </button>
    </form>
  </div>
</template>

<script>
export default {
  props: {
    userId: {
      type: Number,
      required: true
    }
  },

  data() {
    return {
      form: {
        reason: '',
        start_datetime: '',
        end_datetime: '',
        type: '',
        metadata: {}
      },
      validationResult: null,
      debounceTimer: null
    }
  },

  computed: {
    formIsValid() {
      return this.form.reason &&
             this.form.start_datetime &&
             this.form.end_datetime &&
             this.validationResult?.available
    }
  },

  watch: {
    'form.start_datetime': function() {
      this.debouncedValidate()
    },
    'form.end_datetime': function() {
      this.debouncedValidate()
    },
    'form.type': function() {
      this.debouncedValidate()
    }
  },

  methods: {
    debouncedValidate() {
      clearTimeout(this.debounceTimer)
      this.debounceTimer = setTimeout(this.validateTimeSlot, 500)
    },

    async validateTimeSlot() {
      if (!this.form.start_datetime || !this.form.end_datetime) {
        return
      }

      try {
        const response = await axios.post(`/api/users/${this.userId}/impediments/check`, {
          start: this.form.start_datetime,
          end: this.form.end_datetime,
          type: this.form.type
        })

        this.validationResult = response.data
      } catch (error) {
        console.error('Validation error:', error)
      }
    },

    async submitForm() {
      try {
        const response = await axios.post(`/api/users/${this.userId}/impediments`, this.form)

        this.$emit('created', response.data.data)
        this.resetForm()

        this.$toast.success('Impediment créé avec succès')
      } catch (error) {
        if (error.response?.status === 422) {
          this.validationResult = {
            available: false,
            message: error.response.data.message,
            conflicts: error.response.data.conflicts || [],
            suggestions: error.response.data.suggestions || []
          }
          this.$toast.error(error.response.data.message)
        } else {
          this.$toast.error('Erreur lors de la création')
        }
      }
    },

    resetForm() {
      this.form = {
        reason: '',
        start_datetime: '',
        end_datetime: '',
        type: '',
        metadata: {}
      }
      this.validationResult = null
    }
  }
}
</script>
```

## Résumé des nouvelles fonctionnalités

### ✅ Validation des chevauchements
- **Création** : Empêche les nouveaux impediments qui chevauchent
- **Mise à jour** : Vérifie les chevauchements lors des modifications
- **Messages clairs** : Exceptions détaillées avec suggestions

### ✅ Nouvelle méthode `getAvailableTimeSlots()`
- Retourne les créneaux libres entre les impediments
- Utile pour trouver des alternatives quand un créneau est bloqué
- Prend en compte le type d'activité

### ✅ Performance optimisée
- Index de base de données pour les vérifications rapides
- Contraintes d'unicité au niveau base
- Messages d'erreur en temps réel

### ✅ Intégration transparente
- Compatible avec l'API existante
- Gestion automatique des schedules qui chevauchent
- API fluide et intuitive

## Bonnes pratiques

1. **Toujours vérifier avant de créer** : Utilisez `isTimeSlotBlocked()` ou `getAvailableTimeSlots()`
2. **Gérer les erreurs** : Attrapez `InvalidArgumentException` et proposez des alternatives
3. **Interface utilisateur** : Validez en temps réel pour une meilleure expérience
4. **Archivage** : Considérez archiver plutôt que supprimer les schedules affectés
5. **Notifications** : Informez les utilisateurs lorsque leurs rendez-vous sont annulés

## Dépannage

### Erreur : "This time slot overlaps with an existing impediment"
**Solution** : Utilisez `getAvailableTimeSlots()` pour trouver des créneaux alternatifs

### Erreur : "Cannot create impediment that overlaps with existing schedules"
**Solution** : Annulez manuellement les rendez-vous concernés avant de créer l'impediment

### Erreur : "No matching availability found"
**Solution** : Vérifiez les disponibilités de l'utilisateur pour ce jour et cet horaire

### Problème de performance
**Solution** : Les index sont déjà optimisés. Vérifiez que votre base de données supporte les contraintes EXCLUDE si vous utilisez PostgreSQL.
```