# Exemple complet d'utilisation du `ImpedimentService` via la façade `Roster\Impediment`

Voici des exemples détaillés montrant comment utiliser toutes les fonctionnalités du `ImpedimentService` dans une application Laravel.

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

## Exemple 1 : Créer des impediments (blocages de temps)

```php
use Roster\Impediment;
use App\Models\User;
use Illuminate\Support\Carbon;

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
echo "Raison: " . $lunchBreak->reason;
echo "Disponibilité liée: " . $lunchBreak->availability_id;

// 2. Créer un impediment de congé
$vacation = Impediment::for($user)->create([
    'reason' => 'Congés annuels',
    'start_datetime' => Carbon::parse('2024-07-15 00:00:00'),
    'end_datetime' => Carbon::parse('2024-08-05 23:59:59'),
    'metadata' => [
        'type' => 'vacation',
        'approved_by' => 'HR Department',
        'document_ref' => 'VAC-2024-007'
    ]
]);

// 3. Créer un impediment pour une formation
$training = Impediment::for($user)->create([
    'reason' => 'Formation médicale continue',
    'start_datetime' => Carbon::parse('2024-06-05 18:00:00'),
    'end_datetime' => Carbon::parse('2024-06-05 21:00:00'),
    'type' => 'training', // Spécifier le type d'activité
    'metadata' => [
        'training_title' => 'Nouvelles techniques chirurgicales',
        'organizer' => 'College of Surgeons',
        'credits' => 3
    ]
]);

// 4. Créer un impediment pour une réunion d'équipe
$teamMeeting = Impediment::for($user)->create([
    'reason' => 'Réunion d\'équipe hebdomadaire',
    'start_datetime' => Carbon::parse('2024-06-04 08:00:00'),
    'end_datetime' => Carbon::parse('2024-06-04 09:30:00'),
    'metadata' => [
        'recurring' => true,
        'frequency' => 'weekly',
        'day_of_week' => 'tuesday'
    ]
]);

// 5. Tentative de création dans le passé (possible pour les impediments)
$pastImpediment = Impediment::for($user)->create([
    'reason' => 'Maladie passée',
    'start_datetime' => Carbon::parse('2024-05-15 09:00:00'), // Date passée OK pour impediments
    'end_datetime' => Carbon::parse('2024-05-15 17:00:00'),
    'metadata' => [
        'sick_leave' => true,
        'medical_certificate' => 'MC-12345'
    ]
]);

// 6. Tentative de création sans disponibilité correspondante (échoue)
try {
    Impediment::for($user)->create([
        'reason' => 'Réunion weekend',
        'start_datetime' => Carbon::parse('2024-06-01 10:00:00'), // Samedi (pas de disponibilité)
        'end_datetime' => Carbon::parse('2024-06-01 12:00:00'),
    ]);
} catch (\InvalidArgumentException $e) {
    echo "Erreur: " . $e->getMessage(); // "No matching availability found for this impediment"
}
```

## Exemple 2 : Rechercher et récupérer des impediments

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
foreach ($allImpediments as $imp) {
    echo "Impediment #" . $imp->id . ": " . $imp->reason;
}

// 3. Récupérer avec pagination
$impediments = Impediment::for($user)->get();
$paginated = $impediments->paginate(10);

// 4. Récupérer les impediments pour une période spécifique
$startDate = Carbon::parse('2024-06-01');
$endDate = Carbon::parse('2024-06-30');

$juneImpediments = Impediment::for($user)
    ->between($startDate, $endDate);

echo "Nombre d'impediments en juin: " . $juneImpediments->count();
```

## Exemple 3 : Filtrer les impediments

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

## Exemple 4 : Mettre à jour des impediments

```php
use Roster\Impediment;
use App\Models\User;
use Illuminate\Support\Carbon;

$user = User::find(1);
$impediment = Impediment::for($user)->find(1);

if ($impediment) {
    // 1. Mise à jour simple (changer la raison)
    Impediment::for($user)->update($impediment->id, [
        'reason' => 'Pause déjeuner prolongée',
        'metadata' => array_merge($impediment->metadata ?? [], [
            'extended_by' => 'manager',
            'extension_reason' => 'Réunion déjeuner client'
        ])
    ]);

    // 2. Changer la période de l'impediment
    $newStart = Carbon::parse('2024-06-03 12:30:00');
    $newEnd = Carbon::parse('2024-06-03 13:30:00');

    $updated = Impediment::for($user)->update($impediment->id, [
        'start_datetime' => $newStart,
        'end_datetime' => $newEnd,
    ]);

    if ($updated) {
        echo "Impediment modifié avec succès";
        // Note: Les schedules qui chevauchent seront automatiquement supprimés
    }

    // 3. Prolonger un impediment
    Impediment::for($user)->update($impediment->id, [
        'end_datetime' => $impediment->end_datetime->addHours(2),
        'metadata' => array_merge($impediment->metadata ?? [], [
            'extended' => true,
            'previous_end' => $impediment->end_datetime->toDateTimeString()
        ])
    ]);

    // 4. Ajouter des métadonnées supplémentaires
    Impediment::for($user)->update($impediment->id, [
        'metadata' => array_merge($impediment->metadata ?? [], [
            'updated_at' => Carbon::now()->toDateTimeString(),
            'updated_by' => auth()->user()->id ?? null,
            'notes' => 'Ajout de notes supplémentaires'
        ])
    ]);
}
```

## Exemple 5 : Supprimer des impediments

```php
use Roster\Impediment;
use App\Models\User;

$user = User::find(1);
$impediment = Impediment::for($user)->find(1);

if ($impediment) {
    // 1. Supprimer un impediment
    $deleted = Impediment::for($user)->delete($impediment->id);

    if ($deleted) {
        echo "Impediment supprimé avec succès";
        // Note: Les schedules précédemment bloqués ne sont PAS automatiquement restaurés
    }

    // 2. Tentative de suppression d'un impediment inexistant
    $result = Impediment::for($user)->delete(999);
    if (!$result) {
        echo "Impediment non trouvé";
    }
}
```

## Exemple 6 : Vérifier si un créneau est bloqué

```php
use Roster\Impediment;
use App\Models\User;
use Illuminate\Support\Carbon;

$user = User::find(1);

// 1. Vérifier si un créneau est bloqué par un impediment
$start = Carbon::parse('2024-06-03 12:30:00');
$end = Carbon::parse('2024-06-03 13:00:00');

$isBlocked = Impediment::for($user)
    ->isTimeSlotBlocked($start, $end, 'consultation');

if ($isBlocked) {
    echo "Le créneau est bloqué par un impediment";
} else {
    echo "Le créneau n'est pas bloqué";
}

// 2. Vérifier avec un type spécifique
$isTrainingBlocked = Impediment::for($user)
    ->isTimeSlotBlocked($start, $end, 'training');

// 3. Vérifier différents créneaux pendant la pause déjeuner
$lunchTimeSlots = [
    ['12:00', '12:30'],
    ['12:30', '13:00'],
    ['13:00', '13:30'],
    ['13:30', '14:00'],
];

foreach ($lunchTimeSlots as $slot) {
    $slotStart = Carbon::parse('2024-06-03 ' . $slot[0]);
    $slotEnd = Carbon::parse('2024-06-03 ' . $slot[1]);

    $blocked = Impediment::for($user)
        ->isTimeSlotBlocked($slotStart, $slotEnd, 'consultation');

    echo "Créneau {$slot[0]}-{$slot[1]}: " . ($blocked ? 'Bloqué' : 'Libre');
}
```

## Exemple 7 : Gestion des conflits avec les schedules

```php
use Roster\Impediment;
use Roster\Schedule;
use App\Models\User;
use Illuminate\Support\Carbon;

$user = User::find(1);

// Scénario: Créer un rendez-vous d'abord, puis un impediment qui le chevauche
$appointment = Schedule::for($user)->create([
    'title' => 'Consultation - M. Dupont',
    'start_datetime' => Carbon::parse('2024-06-03 14:00:00'),
    'end_datetime' => Carbon::parse('2024-06-03 15:00:00'),
    'status' => 'booked',
]);

// Tentative de création d'un impediment qui chevauche le rendez-vous
try {
    $impediment = Impediment::for($user)->create([
        'reason' => 'Réunion urgente',
        'start_datetime' => Carbon::parse('2024-06-03 14:30:00'), // Chevauchement
        'end_datetime' => Carbon::parse('2024-06-03 15:30:00'),
    ]);
} catch (\InvalidArgumentException $e) {
    echo "Erreur: " . $e->getMessage(); // "Cannot create impediment that overlaps with existing schedules"

    // Solutions possibles:
    // 1. Annuler d'abord le rendez-vous
    Schedule::for($user)->delete($appointment->id);

    // 2. Puis créer l'impediment
    $impediment = Impediment::for($user)->create([
        'reason' => 'Réunion urgente',
        'start_datetime' => Carbon::parse('2024-06-03 14:30:00'),
        'end_datetime' => Carbon::parse('2024-06-03 15:30:00'),
    ]);

    echo "Rendez-vous annulé et impediment créé";
}

// Scénario: Mettre à jour un impediment pour qu'il chevauche des rendez-vous existants
$impediment = Impediment::for($user)->create([
    'reason' => 'Maintenance bureau',
    'start_datetime' => Carbon::parse('2024-06-04 08:00:00'),
    'end_datetime' => Carbon::parse('2024-06-04 09:00:00'),
]);

// Créer des rendez-vous qui seront impactés
$appointment1 = Schedule::for($user)->create([
    'title' => 'Consultation matinale 1',
    'start_datetime' => Carbon::parse('2024-06-04 08:30:00'),
    'end_datetime' => Carbon::parse('2024-06-04 09:00:00'),
    'status' => 'booked',
]);

$appointment2 = Schedule::for($user)->create([
    'title' => 'Consultation matinale 2',
    'start_datetime' => Carbon::parse('2024-06-04 09:00:00'),
    'end_datetime' => Carbon::parse('2024-06-04 09:30:00'),
    'status' => 'booked',
]);

// Mettre à jour l'impediment pour qu'il dure plus longtemps
Impediment::for($user)->update($impediment->id, [
    'end_datetime' => Carbon::parse('2024-06-04 10:00:00'),
]);

// Vérifier que les rendez-vous ont été automatiquement supprimés
$remainingAppointments = Schedule::for($user)
    ->between(Carbon::parse('2024-06-04 00:00:00'), Carbon::parse('2024-06-04 23:59:59'))
    ->count();

echo "Rendez-vous restants le 4 juin: " . $remainingAppointments; // Devrait être 0
```

## Exemple 8 : Types courants d'impediments et bonnes pratiques

```php
use Roster\Impediment;
use App\Models\User;
use Illuminate\Support\Carbon;

$user = User::find(1);

// 1. Pauses régulières (déjeuner, café)
$lunchBreaks = [
    ['12:00', '13:00', 'Pause déjeuner'],
    ['10:30', '10:45', 'Pause café matinale'],
    ['15:30', '15:45', 'Pause café après-midi'],
];

foreach ($lunchBreaks as $break) {
    for ($i = 0; $i < 30; $i++) { // Pour les 30 prochains jours
        $date = Carbon::now()->addDays($i);

        // Ne créer que pour les jours de semaine
        if ($date->isWeekday()) {
            Impediment::for($user)->create([
                'reason' => $break[2],
                'start_datetime' => $date->copy()->setTimeFromTimeString($break[0]),
                'end_datetime' => $date->copy()->setTimeFromTimeString($break[1]),
                'metadata' => [
                    'recurring' => true,
                    'category' => 'daily_break',
                    'auto_generated' => true
                ]
            ]);
        }
    }
}

// 2. Congés et vacances
$vacationPeriods = [
    [
        'reason' => 'Vacances d\'été',
        'start' => '2024-07-15',
        'end' => '2024-08-05',
        'type' => 'vacation'
    ],
    [
        'reason' => 'Formation professionnelle',
        'start' => '2024-09-10',
        'end' => '2024-09-12',
        'type' => 'training'
    ],
    [
        'reason' => 'Congé maladie',
        'start' => '2024-10-01',
        'end' => '2024-10-03',
        'type' => 'sick_leave'
    ]
];

foreach ($vacationPeriods as $period) {
    Impediment::for($user)->create([
        'reason' => $period['reason'],
        'start_datetime' => Carbon::parse($period['start'] . ' 00:00:00'),
        'end_datetime' => Carbon::parse($period['end'] . ' 23:59:59'),
        'metadata' => [
            'type' => $period['type'],
            'full_day' => true,
            'requires_approval' => in_array($period['type'], ['vacation', 'training'])
        ]
    ]);
}

// 3. Réunions régulières
$regularMeetings = [
    [
        'reason' => 'Réunion d\'équipe hebdomadaire',
        'day_of_week' => 'monday',
        'time' => '08:00',
        'duration' => 90
    ],
    [
        'reason' => 'Revue de cas cliniques',
        'day_of_week' => 'wednesday',
        'time' => '17:00',
        'duration' => 120
    ],
    [
        'reason' => 'Formation continue',
        'day_of_week' => 'friday',
        'time' => '18:00',
        'duration' => 180
    ]
];

foreach ($regularMeetings as $meeting) {
    for ($i = 0; $i < 12; $i++) { // Pour les 12 prochaines semaines
        $date = Carbon::now()->startOfWeek()->addWeeks($i);
        $date->next($meeting['day_of_week']);

        if ($date->gte(Carbon::now())) {
            Impediment::for($user)->create([
                'reason' => $meeting['reason'],
                'start_datetime' => $date->copy()->setTimeFromTimeString($meeting['time']),
                'end_datetime' => $date->copy()->setTimeFromTimeString($meeting['time'])->addMinutes($meeting['duration']),
                'metadata' => [
                    'recurring' => true,
                    'frequency' => 'weekly',
                    'day_of_week' => $meeting['day_of_week'],
                    'series_id' => 'meeting_' . str_slug($meeting['reason'])
                ]
            ]);
        }
    }
}

// 4. Blocages ponctuels (urgences, réunions imprévues)
$oneTimeBlocks = [
    [
        'reason' => 'Audit qualité',
        'date' => '2024-06-10',
        'start_time' => '09:00',
        'end_time' => '12:00'
    ],
    [
        'reason' => 'Entretien annuel',
        'date' => '2024-06-15',
        'start_time' => '14:00',
        'end_time' => '16:00'
    ],
    [
        'reason' => 'Maintenance équipement',
        'date' => '2024-06-20',
        'start_time' => '08:00',
        'end_time' => '10:00'
    ]
];

foreach ($oneTimeBlocks as $block) {
    Impediment::for($user)->create([
        'reason' => $block['reason'],
        'start_datetime' => Carbon::parse($block['date'] . ' ' . $block['start_time']),
        'end_datetime' => Carbon::parse($block['date'] . ' ' . $block['end_time']),
        'metadata' => [
            'one_time' => true,
            'category' => 'special_event',
            'notification_sent' => false
        ]
    ]);
}
```

## Exemple 9 : Intégration dans un contrôleur Laravel

```php
// App\Http\Controllers\ImpedimentController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Roster\Impediment;
use Roster\Schedule;
use App\Models\User;
use Illuminate\Support\Carbon;

class ImpedimentController extends Controller
{
    /**
     * Afficher tous les impediments d'un utilisateur
     */
    public function index(Request $request, User $user): JsonResponse
    {
        $query = Impediment::for($user);

        // Appliquer les filtres depuis la requête
        if ($request->has('type')) {
            $query->whereType($request->get('type'));
        }

        if ($request->has('start_date')) {
            $query->whereStartDate(Carbon::parse($request->get('start_date')));
        }

        if ($request->has('end_date')) {
            $query->whereEndDate(Carbon::parse($request->get('end_date')));
        }

        // Filtrer par période si spécifié
        if ($request->has('period_start') && $request->has('period_end')) {
            $periodStart = Carbon::parse($request->get('period_start'));
            $periodEnd = Carbon::parse($request->get('period_end'));
            $impediments = $query->between($periodStart, $periodEnd);
        } else {
            $impediments = $query->get();
        }

        return response()->json([
            'data' => $impediments,
            'count' => $impediments->count(),
        ]);
    }

    /**
     * Créer un nouvel impediment
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

            // Récupérer les rendez-vous qui ont été supprimés
            $deletedSchedules = $this->getDeletedSchedulesForImpediment($impediment);

            return response()->json([
                'message' => 'Impediment créé avec succès',
                'data' => $impediment,
                'affected_schedules' => $deletedSchedules,
                'warning' => $deletedSchedules->count() > 0
                    ? count($deletedSchedules) . ' rendez-vous ont été annulés automatiquement'
                    : null
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'suggestions' => $this->getAlternativeTimes($user, $validated),
            ], 422);
        }
    }

    /**
     * Mettre à jour un impediment
     */
    public function update(Request $request, User $user, int $id): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'sometimes|string|max:255',
            'start_datetime' => 'sometimes|date',
            'end_datetime' => 'sometimes|date|after:start_datetime',
            'metadata' => 'nullable|array',
        ]);

        $impediment = Impediment::for($user)->find($id);

        if (!$impediment) {
            return response()->json([
                'message' => 'Impediment non trouvé',
            ], 404);
        }

        // Vérifier s'il y aura des schedules affectés par la mise à jour
        $willAffectSchedules = false;
        if (isset($validated['start_datetime']) || isset($validated['end_datetime'])) {
            $newStart = isset($validated['start_datetime'])
                ? Carbon::parse($validated['start_datetime'])
                : $impediment->start_datetime;
            $newEnd = isset($validated['end_datetime'])
                ? Carbon::parse($validated['end_datetime'])
                : $impediment->end_datetime;

            // Vérifier les chevauchements
            $overlappingSchedules = Schedule::where('availability_id', $impediment->availability_id)
                ->where(function ($q) use ($newStart, $newEnd) {
                    $q->where('start_datetime', '<', $newEnd)
                        ->where('end_datetime', '>', $newStart);
                })
                ->count();

            $willAffectSchedules = $overlappingSchedules > 0;
        }

        try {
            $updated = Impediment::for($user)->update($id, $validated);

            if ($updated) {
                $impediment->refresh();
                $deletedSchedules = $this->getDeletedSchedulesForImpediment($impediment);

                return response()->json([
                    'message' => 'Impediment mis à jour',
                    'data' => $impediment,
                    'affected_schedules' => $willAffectSchedules ? $deletedSchedules : [],
                    'warning' => $willAffectSchedules && $deletedSchedules->count() > 0
                        ? count($deletedSchedules) . ' rendez-vous ont été annulés automatiquement'
                        : null
                ]);
            }
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'message' => 'Erreur lors de la mise à jour',
        ], 500);
    }

    /**
     * Supprimer un impediment
     */
    public function destroy(User $user, int $id): JsonResponse
    {
        $deleted = Impediment::for($user)->delete($id);

        if ($deleted) {
            return response()->json([
                'message' => 'Impediment supprimé',
            ]);
        }

        return response()->json([
            'message' => 'Impediment non trouvé',
        ], 404);
    }

    /**
     * Vérifier si un créneau est bloqué
     */
    public function checkBlocked(User $user, Request $request): JsonResponse
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

        // Récupérer les impediments qui bloquent ce créneau
        $blockingImpediments = [];
        if ($isBlocked) {
            $impediments = Impediment::for($user)->get();
            foreach ($impediments as $impediment) {
                if ($impediment->overlapsWith($start, $end)) {
                    $blockingImpediments[] = [
                        'id' => $impediment->id,
                        'reason' => $impediment->reason,
                        'start' => $impediment->start_datetime->toDateTimeString(),
                        'end' => $impediment->end_datetime->toDateTimeString(),
                    ];
                }
            }
        }

        return response()->json([
            'blocked' => $isBlocked,
            'blocking_impediments' => $blockingImpediments,
        ]);
    }

    /**
     * Récupérer les rendez-vous supprimés par un impediment
     */
    private function getDeletedSchedulesForImpediment($impediment)
    {
        // Note: Cette méthode est théorique car les schedules sont supprimés
        // automatiquement. Dans une vraie application, vous voudriez peut-être
        // les archiver au lieu de les supprimer complètement.

        // Pour l'exemple, on retourne une collection vide
        return collect([]);
    }

    /**
     * Trouver des créneaux alternatifs pour un impediment
     */
    private function getAlternativeTimes(User $user, array $originalRequest): array
    {
        $start = Carbon::parse($originalRequest['start_datetime']);
        $end = Carbon::parse($originalRequest['end_datetime']);
        $duration = $end->diffInMinutes($start);

        // Chercher des créneaux libres aujourd'hui et demain
        $suggestions = [];

        for ($i = 0; $i < 7; $i++) {
            $date = $start->copy()->addDays($i);

            // Vérifier si ce jour a des disponibilités
            $hasAvailability = true; // Simplification

            if ($hasAvailability) {
                // Vérifier si le créneau est libre
                $isBlocked = Impediment::for($user)
                    ->isTimeSlotBlocked(
                        $date->copy()->setTimeFrom($start),
                        $date->copy()->setTimeFrom($end),
                        $originalRequest['type'] ?? null
                    );

                if (!$isBlocked) {
                    $suggestions[] = [
                        'date' => $date->toDateString(),
                        'start' => $date->copy()->setTimeFrom($start)->toDateTimeString(),
                        'end' => $date->copy()->setTimeFrom($end)->toDateTimeString(),
                    ];

                    if (count($suggestions) >= 3) {
                        break;
                    }
                }
            }
        }

        return $suggestions;
    }
}
```

## Exemple 10 : Utilisation dans les vues Blade

```blade
{{-- resources/views/impediments/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Blocages de temps pour {{ $user->name }}</h2>

    <!-- Filtres -->
    <div class="card mb-4">
        <div class="card-body">
            <form id="filterForm">
                <div class="row">
                    <div class="col-md-3">
                        <label for="type" class="form-label">Type d'activité</label>
                        <select name="type" id="type" class="form-select">
                            <option value="">Tous les types</option>
                            <option value="consultation">Consultation</option>
                            <option value="training">Formation</option>
                            <option value="meeting">Réunion</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="period_start" class="form-label">Du</label>
                        <input type="date" name="period_start" id="period_start" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label for="period_end" class="form-label">Au</label>
                        <input type="date" name="period_end" id="period_end" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label for="reason" class="form-label">Raison (contient)</label>
                        <input type="text" name="reason" id="reason" class="form-control"
                               placeholder="Pause, congé, réunion...">
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">Filtrer</button>
                    <button type="button" id="resetFilters" class="btn btn-secondary">Réinitialiser</button>
                    <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#checkModal">
                        Vérifier créneau
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des impediments -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Date & Heure</th>
                            <th>Raison</th>
                            <th>Type</th>
                            <th>Durée</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($impediments as $impediment)
                        @php
                            $statusClass = '';
                            if ($impediment->isActive()) {
                                $statusClass = 'bg-warning';
                            } elseif ($impediment->isUpcoming()) {
                                $statusClass = 'bg-info';
                            } else {
                                $statusClass = 'bg-secondary';
                            }
                        @endphp
                        <tr>
                            <td>
                                {{ $impediment->start_datetime->format('d/m/Y H:i') }}<br>
                                <small>à {{ $impediment->end_datetime->format('H:i') }}</small>
                            </td>
                            <td>{{ $impediment->reason }}</td>
                            <td>
                                @if($impediment->type)
                                <span class="badge bg-primary">{{ $impediment->type }}</span>
                                @endif
                            </td>
                            <td>{{ $impediment->duration_minutes }} minutes</td>
                            <td>
                                <span class="badge {{ $statusClass }}">
                                    @if($impediment->isActive())
                                        En cours
                                    @elseif($impediment->isUpcoming())
                                        À venir
                                    @else
                                        Passé
                                    @endif
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editModal"
                                            data-id="{{ $impediment->id }}"
                                            data-reason="{{ $impediment->reason }}"
                                            data-start="{{ $impediment->start_datetime->format('Y-m-d\TH:i') }}"
                                            data-end="{{ $impediment->end_datetime->format('Y-m-d\TH:i') }}"
                                            data-type="{{ $impediment->type }}">
                                        Modifier
                                    </button>
                                    <form action="{{ route('impediments.destroy', [$user, $impediment->id]) }}"
                                          method="POST"
                                          class="d-inline"
                                          onsubmit="return confirm('Supprimer ce blocage ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger">
                                            Supprimer
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($impediments->isEmpty())
            <div class="text-center py-5">
                <i class="bi bi-calendar-x text-muted" style="font-size: 3rem;"></i>
                <h5 class="mt-3">Aucun blocage de temps</h5>
                <p class="text-muted">Tous les créneaux sont disponibles</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Bouton pour créer un nouvel impediment -->
    <div class="mt-4">
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createModal">
            <i class="bi bi-plus-circle"></i> Nouveau blocage
        </button>

        <!-- Boutons pour les blocages récurrents -->
        <div class="btn-group">
            <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                <i class="bi bi-arrow-repeat"></i> Blocages récurrents
            </button>
            <ul class="dropdown-menu">
                <li>
                    <button class="dropdown-item" type="button" data-bs-toggle="modal" data-bs-target="#lunchModal">
                        Pause déjeuner quotidienne
                    </button>
                </li>
                <li>
                    <button class="dropdown-item" type="button" data-bs-toggle="modal" data-bs-target="#weeklyModal">
                        Réunion hebdomadaire
                    </button>
                </li>
                <li>
                    <button class="dropdown-item" type="button" data-bs-toggle="modal" data-bs-target="#vacationModal">
                        Période de congés
                    </button>
                </li>
            </ul>
        </div>
    </div>
</div>

<!-- Modals -->
@include('impediments.modals.create')
@include('impediments.modals.edit')
@include('impediments.modals.check')
@include('impediments.modals.recurring.lunch')
@include('impediments.modals.recurring.weekly')
@include('impediments.modals.recurring.vacation')

<script>
// Gestion des filtres
document.getElementById('filterForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const params = new URLSearchParams(new FormData(this));
    window.location.href = window.location.pathname + '?' + params.toString();
});

document.getElementById('resetFilters').addEventListener('click', function() {
    window.location.href = window.location.pathname;
});

// Mise à jour du modal d'édition
document.addEventListener('DOMContentLoaded', function() {
    const editModal = document.getElementById('editModal');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const modal = this;

            modal.querySelector('#editReason').value = button.getAttribute('data-reason');
            modal.querySelector('#editStart').value = button.getAttribute('data-start');
            modal.querySelector('#editEnd').value = button.getAttribute('data-end');
            modal.querySelector('#editType').value = button.getAttribute('data-type');

            // Mettre à jour l'action du formulaire
            const form = modal.querySelector('#editForm');
            const impedimentId = button.getAttribute('data-id');
            form.action = form.action.replace(/\/\d+$/, '/' + impedimentId);
        });
    }

    // Validation du formulaire de vérification
    const checkForm = document.getElementById('checkForm');
    if (checkForm) {
        checkForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const response = await fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const result = await response.json();
            const resultDiv = document.getElementById('checkResult');

            if (result.blocked) {
                resultDiv.innerHTML = `
                    <div class="alert alert-danger">
                        <h5><i class="bi bi-x-circle"></i> Créneau bloqué</h5>
                        <p>Ce créneau n'est pas disponible pour les raisons suivantes:</p>
                        <ul>
                            ${result.blocking_impediments.map(imp =>
                                `<li>${imp.reason} (${imp.start} - ${imp.end})</li>`
                            ).join('')}
                        </ul>
                    </div>
                `;
            } else {
                resultDiv.innerHTML = `
                    <div class="alert alert-success">
                        <h5><i class="bi bi-check-circle"></i> Créneau disponible</h5>
                        <p>Ce créneau est libre et peut être utilisé.</p>
                    </div>
                `;
            }
        });
    }
});
</script>
@endsection
```

## Exemple 11 : Système de gestion des congés et absences

```php
<?php
// App\Services\LeaveManagementService.php
namespace App\Services;

use Roster\Impediment;
use Roster\Schedule;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class LeaveManagementService
{
    /**
     * Demander un congé
     */
    public function requestLeave(User $employee, array $data): array
    {
        DB::beginTransaction();

        try {
            // Validation des données
            $this->validateLeaveRequest($data);

            $start = Carbon::parse($data['start_date'] . ' 00:00:00');
            $end = Carbon::parse($data['end_date'] . ' 23:59:59');

            // Vérifier les chevauchements avec des congés existants
            $existingLeaves = Impediment::for($employee)
                ->between($start, $end)
                ->filter(function($imp) {
                    return isset($imp->metadata['leave_type']);
                });

            if ($existingLeaves->count() > 0) {
                throw new InvalidArgumentException('Vous avez déjà un congé pendant cette période');
            }

            // Créer l'impediment pour le congé
            $leaveImpediment = Impediment::for($employee)->create([
                'reason' => $data['reason'] ?? 'Congé',
                'start_datetime' => $start,
                'end_datetime' => $end,
                'metadata' => [
                    'leave_type' => $data['leave_type'],
                    'status' => 'pending',
                    'requested_at' => Carbon::now()->toDateTimeString(),
                    'requested_by' => $employee->id,
                    'approver_id' => $data['approver_id'] ?? null,
                    'documentation' => $data['documentation'] ?? null,
                ]
            ]);

            // Annuler automatiquement les rendez-vous pendant le congé
            $cancelledAppointments = $this->cancelAppointmentsDuringLeave($employee, $start, $end);

            // Notifier le manager
            $this->notifyManager($employee, $leaveImpediment, $data);

            DB::commit();

            return [
                'success' => true,
                'leave' => $leaveImpediment,
                'cancelled_appointments' => $cancelledAppointments,
                'message' => 'Demande de congé soumise avec succès'
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Approuver un congé
     */
    public function approveLeave(User $approver, int $leaveId, array $data = []): array
    {
        DB::beginTransaction();

        try {
            $leave = Impediment::find($leaveId);

            if (!$leave) {
                throw new InvalidArgumentException('Congé non trouvé');
            }

            // Vérifier les permissions
            $employee = User::find($leave->schedulable_id);
            if (!$employee) {
                throw new InvalidArgumentException('Employé non trouvé');
            }

            // Mettre à jour le statut
            Impediment::for($employee)->update($leaveId, [
                'metadata' => array_merge($leave->metadata ?? [], [
                    'status' => 'approved',
                    'approved_at' => Carbon::now()->toDateTimeString(),
                    'approved_by' => $approver->id,
                    'approval_notes' => $data['notes'] ?? null,
                ])
            ]);

            // Notifier l'employé
            $this->notifyEmployee($employee, $leave, 'approved');

            DB::commit();

            return [
                'success' => true,
                'message' => 'Congé approuvé avec succès'
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Refuser un congé
     */
    public function rejectLeave(User $approver, int $leaveId, string $reason): array
    {
        DB::beginTransaction();

        try {
            $leave = Impediment::find($leaveId);

            if (!$leave) {
                throw new InvalidArgumentException('Congé non trouvé');
            }

            $employee = User::find($leave->schedulable_id);
            if (!$employee) {
                throw new InvalidArgumentException('Employé non trouvé');
            }

            // Supprimer l'impediment
            Impediment::for($employee)->delete($leaveId);

            // Restaurer les rendez-vous annulés (si possible)
            $this->restoreAppointments($employee, $leave);

            // Notifier l'employé
            $this->notifyEmployee($employee, $leave, 'rejected', $reason);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Congé refusé'
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Vérifier la disponibilité d'un employé
     */
    public function checkEmployeeAvailability(User $employee, Carbon $start, Carbon $end): array
    {
        // Vérifier les impediments (congés, réunions, etc.)
        $impediments = Impediment::for($employee)
            ->between($start, $end)
            ->get();

        $blockedPeriods = [];
        foreach ($impediments as $impediment) {
            $blockedPeriods[] = [
                'start' => $impediment->start_datetime,
                'end' => $impediment->end_datetime,
                'reason' => $impediment->reason,
                'type' => $impediment->metadata['leave_type'] ?? 'other',
            ];
        }

        // Vérifier les rendez-vous existants
        $existingAppointments = Schedule::for($employee)
            ->between($start, $end)
            ->get();

        $busyPeriods = [];
        foreach ($existingAppointments as $appointment) {
            $busyPeriods[] = [
                'start' => $appointment->start_datetime,
                'end' => $appointment->end_datetime,
                'title' => $appointment->title,
                'status' => $appointment->status->value,
            ];
        }

        return [
            'available' => empty($blockedPeriods),
            'blocked_periods' => $blockedPeriods,
            'busy_periods' => $busyPeriods,
            'summary' => [
                'total_days' => $start->diffInDays($end),
                'blocked_days' => count($blockedPeriods),
                'busy_days' => count($busyPeriods),
            ]
        ];
    }

    /**
     * Générer un rapport de congés
     */
    public function generateLeaveReport(User $employee, Carbon $startDate, Carbon $endDate): array
    {
        $leaves = Impediment::for($employee)
            ->between($startDate, $endDate)
            ->get()
            ->filter(function($imp) {
                return isset($imp->metadata['leave_type']);
            });

        $report = [
            'employee' => $employee->name,
            'period' => $startDate->format('Y-m-d') . ' to ' . $endDate->format('Y-m-d'),
            'leaves' => [],
            'summary' => [
                'total' => 0,
                'by_type' => [],
                'by_status' => [],
            ]
        ];

        foreach ($leaves as $leave) {
            $days = $leave->start_datetime->diffInDays($leave->end_datetime) + 1;
            $type = $leave->metadata['leave_type'] ?? 'unknown';
            $status = $leave->metadata['status'] ?? 'unknown';

            $report['leaves'][] = [
                'id' => $leave->id,
                'reason' => $leave->reason,
                'start_date' => $leave->start_datetime->format('Y-m-d'),
                'end_date' => $leave->end_datetime->format('Y-m-d'),
                'days' => $days,
                'type' => $type,
                'status' => $status,
                'requested_at' => $leave->metadata['requested_at'] ?? null,
                'approved_at' => $leave->metadata['approved_at'] ?? null,
            ];

            // Mettre à jour le résumé
            $report['summary']['total'] += $days;

            if (!isset($report['summary']['by_type'][$type])) {
                $report['summary']['by_type'][$type] = 0;
            }
            $report['summary']['by_type'][$type] += $days;

            if (!isset($report['summary']['by_status'][$status])) {
                $report['summary']['by_status'][$status] = 0;
            }
            $report['summary']['by_status'][$status]++;
        }

        return $report;
    }

    /**
     * Valider une demande de congé
     */
    private function validateLeaveRequest(array $data): void
    {
        $required = ['start_date', 'end_date', 'leave_type'];

        foreach ($required as $field) {
            if (!isset($data[$field])) {
                throw new InvalidArgumentException("Le champ '$field' est requis");
            }
        }

        $start = Carbon::parse($data['start_date']);
        $end = Carbon::parse($data['end_date']);

        if ($end->lt($start)) {
            throw new InvalidArgumentException('La date de fin doit être après la date de début');
        }

        // Vérifier que la durée ne dépasse pas la limite
        $maxDays = $this->getMaxLeaveDays($data['leave_type']);
        $duration = $end->diffInDays($start) + 1;

        if ($duration > $maxDays) {
            throw new InvalidArgumentException("La durée maximale pour ce type de congé est de $maxDays jours");
        }

        // Vérifier le préavis (par exemple, 48h pour les congés)
        $noticePeriod = 48; // heures
        if ($start->diffInHours(Carbon::now()) < $noticePeriod) {
            throw new InvalidArgumentException("Un préavis de {$noticePeriod}h est requis");
        }
    }

    /**
     * Annuler les rendez-vous pendant un congé
     */
    private function cancelAppointmentsDuringLeave(User $employee, Carbon $start, Carbon $end): array
    {
        $appointments = Schedule::for($employee)
            ->between($start, $end)
            ->get();

        $cancelled = [];

        foreach ($appointments as $appointment) {
            // Marquer comme annulé à cause du congé
            Schedule::for($employee)->update($appointment->id, [
                'status' => 'cancelled',
                'metadata' => array_merge($appointment->metadata ?? [], [
                    'cancelled_reason' => 'employee_leave',
                    'cancelled_at' => Carbon::now()->toDateTimeString(),
                    'original_appointment' => [
                        'title' => $appointment->title,
                        'patient_id' => $appointment->metadata['patient_id'] ?? null,
                    ]
                ])
            ]);

            $cancelled[] = $appointment;

            // Notifier le patient
            $this->notifyPatient($appointment, 'cancelled_due_to_leave');
        }

        return $cancelled;
    }

    /**
     * Restaurer les rendez-vous après refus de congé
     */
    private function restoreAppointments(User $employee, $leave): void
    {
        // Dans une vraie application, vous voudriez restaurer les rendez-vous
        // qui ont été annulés à cause de ce congé
        // Cela nécessiterait de les archiver au lieu de les supprimer

        // Pour cet exemple, on ne fait rien
    }

    /**
     * Obtenir le nombre maximum de jours pour un type de congé
     */
    private function getMaxLeaveDays(string $leaveType): int
    {
        $limits = [
            'vacation' => 30,
            'sick_leave' => 15,
            'maternity' => 112,
            'paternity' => 28,
            'training' => 10,
            'other' => 5,
        ];

        return $limits[$leaveType] ?? 5;
    }

    /**
     * Notifier le manager
     */
    private function notifyManager(User $employee, $leave, array $data): void
    {
        // Implémenter la notification
        // Mail::to($manager->email)->send(new LeaveRequestNotification($employee, $leave, $data));
    }

    /**
     * Notifier l'employé
     */
    private function notifyEmployee(User $employee, $leave, string $action, ?string $reason = null): void
    {
        // Implémenter la notification
        // Mail::to($employee->email)->send(new LeaveStatusNotification($leave, $action, $reason));
    }

    /**
     * Notifier le patient
     */
    private function notifyPatient($appointment, string $reason): void
    {
        // Implémenter la notification
        // if (isset($appointment->metadata['patient_email'])) {
        //     Mail::to($appointment->metadata['patient_email'])
        //         ->send(new AppointmentCancellationNotification($appointment, $reason));
        // }
    }
}
```
