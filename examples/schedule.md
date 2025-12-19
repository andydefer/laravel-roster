```md
# Exemple complet d'utilisation du `ScheduleService` via la façade `Roster\Schedule`

Voici des exemples détaillés montrant comment utiliser toutes les fonctionnalités du `ScheduleService` dans une application Laravel.

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

// Créer des disponibilités de base pour le médecin
$availability1 = Availability::for($user)->create([
    'type' => 'consultation',
    'start_time' => '08:00:00',
    'end_time' => '12:00:00',
    'days' => ['monday', 'wednesday', 'friday'],
    'start_date' => '2024-06-01',
    'end_date' => '2024-12-31',
]);

$availability2 = Availability::for($user)->create([
    'type' => 'consultation',
    'start_time' => '14:00:00',
    'end_time' => '18:00:00',
    'days' => ['tuesday', 'thursday'],
    'start_date' => '2024-06-01',
    'end_date' => '2024-12-31',
]);

$availability3 = Availability::for($user)->create([
    'type' => 'emergency',
    'start_time' => '18:00:00',
    'end_time' => '22:00:00',
    'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
    'start_date' => '2024-06-01',
    'end_date' => '2024-12-31',
]);
```

## Exemple 1 : Créer des rendez-vous (Schedules)

```php
use Roster\Schedule;
use App\Models\User;
use Illuminate\Support\Carbon;

$user = User::find(1);
$now = Carbon::now();

// 1. Créer un rendez-vous de consultation
$appointment = Schedule::for($user)->create([
    'title' => 'Consultation routine - M. Dupont',
    'description' => 'Bilan de santé annuel',
    'start_datetime' => Carbon::parse('2024-06-03 09:00:00'), // Lundi 3 juin 2024 à 9h
    'end_datetime' => Carbon::parse('2024-06-03 10:00:00'),   // Jusqu'à 10h
    'status' => 'booked',
    'metadata' => [
        'patient_id' => 123,
        'insurance' => 'Mutuelles Santé',
        'priority' => 'normal'
    ]
]);

echo "Rendez-vous créé avec l'ID: " . $appointment->id;
echo "Type: " . $appointment->type; // Récupère automatiquement le type de l'availability
echo "Disponibilité liée: " . $appointment->availability_id;

// 2. Créer un rendez-vous d'urgence
$emergency = Schedule::for($user)->create([
    'title' => 'Urgence - M. Martin',
    'description' => 'Douleur thoracique',
    'start_datetime' => Carbon::parse('2024-06-03 19:00:00'),
    'end_datetime' => Carbon::parse('2024-06-03 20:00:00'),
    'status' => 'booked',
    'type' => 'emergency', // Spécifier explicitement le type
    'metadata' => [
        'patient_id' => 456,
        'emergency_level' => 'high',
        'symptoms' => 'Douleur thoracique, essoufflement'
    ]
]);

// 3. Tentative de création dans le passé (échoue)
try {
    Schedule::for($user)->create([
        'title' => 'Rendez-vous passé',
        'start_datetime' => Carbon::parse('2023-12-01 10:00:00'), // Date passée
        'end_datetime' => Carbon::parse('2023-12-01 11:00:00'),
        'status' => 'available',
    ]);
} catch (\InvalidArgumentException $e) {
    echo "Erreur: " . $e->getMessage(); // "Cannot schedule in the past"
}

// 4. Tentative de création sans disponibilité correspondante
try {
    Schedule::for($user)->create([
        'title' => 'Rendez-vous samedi',
        'start_datetime' => Carbon::parse('2024-06-01 10:00:00'), // Samedi (pas de disponibilité)
        'end_datetime' => Carbon::parse('2024-06-01 11:00:00'),
        'status' => 'available',
    ]);
} catch (\InvalidArgumentException $e) {
    echo "Erreur: " . $e->getMessage(); // "No matching availability found for this schedule"
}
```

## Exemple 2 : Rechercher et récupérer des rendez-vous

```php
use Roster\Schedule;
use App\Models\User;
use Illuminate\Support\Carbon;

$user = User::find(1);

// 1. Trouver un rendez-vous par ID
$schedule = Schedule::for($user)->find(1);
if ($schedule) {
    echo "Titre: " . $schedule->title;
    echo "Date: " . $schedule->start_datetime->format('d/m/Y H:i');
    echo "Statut: " . $schedule->status->value;
}

// 2. Récupérer tous les rendez-vous
$allSchedules = Schedule::for($user)->all();
foreach ($allSchedules as $schedule) {
    echo "Rendez-vous #" . $schedule->id . ": " . $schedule->title;
}

// 3. Récupérer avec pagination (via Eloquent)
$schedules = Schedule::for($user)->get();
$paginated = $schedules->paginate(10); // Utilisez la pagination standard

// 4. Récupérer les rendez-vous pour une période spécifique
$startDate = Carbon::parse('2024-06-01');
$endDate = Carbon::parse('2024-06-30');

$juneSchedules = Schedule::for($user)
    ->between($startDate, $endDate);

echo "Nombre de rendez-vous en juin: " . $juneSchedules->count();
```

## Exemple 3 : Filtrer les rendez-vous

```php
use Roster\Schedule;
use App\Models\User;
use Illuminate\Support\Carbon;

$user = User::find(1);

// 1. Filtrer par type d'activité
$consultations = Schedule::for($user)
    ->whereType('consultation')
    ->get();

$emergencies = Schedule::for($user)
    ->whereType('emergency')
    ->get();

// 2. Filtrer par statut
$findSlotsInPeriod = Schedule::for($user)
    ->whereStatus('available')
    ->get();

$bookedAppointments = Schedule::for($user)
    ->whereStatus('booked')
    ->get();

$cancelledAppointments = Schedule::for($user)
    ->whereStatus('cancelled')
    ->get();

// 3. Filtrer par date de début
$afterToday = Schedule::for($user)
    ->whereStartDate(Carbon::now())
    ->get();

// 4. Filtrer par date de fin
$beforeEndOfMonth = Schedule::for($user)
    ->whereEndDate(Carbon::now()->endOfMonth())
    ->get();

// 5. Combinaison de filtres
$consultationsBooked = Schedule::for($user)
    ->whereType('consultation')
    ->whereStatus('booked')
    ->get();

// 6. Réinitialiser les filtres
$allAgain = Schedule::for($user)
    ->whereType('consultation')
    ->whereStatus('booked')
    ->resetFilters()
    ->get(); // Retourne tous les schedules
```

## Exemple 4 : Mettre à jour des rendez-vous

```php
use Roster\Schedule;
use App\Models\User;
use Illuminate\Support\Carbon;

$user = User::find(1);
$schedule = Schedule::for($user)->find(1);

if ($schedule) {
    // 1. Mise à jour simple (changer le titre)
    Schedule::for($user)->update($schedule->id, [
        'title' => 'Consultation modifiée - M. Dupont',
        'description' => 'Bilan de santé avec examens complémentaires'
    ]);

    // 2. Changer l'horaire du rendez-vous
    $newStart = Carbon::parse('2024-06-03 11:00:00');
    $newEnd = Carbon::parse('2024-06-03 12:00:00');

    $updated = Schedule::for($user)->update($schedule->id, [
        'start_datetime' => $newStart,
        'end_datetime' => $newEnd,
        'status' => 'booked'
    ]);

    if ($updated) {
        echo "Rendez-vous déplacé avec succès";
    }

    // 3. Annuler un rendez-vous
    Schedule::for($user)->update($schedule->id, [
        'status' => 'cancelled',
        'metadata' => array_merge($schedule->metadata ?? [], [
            'cancellation_reason' => 'Patient indisponible',
            'cancelled_by' => 'admin',
            'cancelled_at' => Carbon::now()->toDateTimeString()
        ])
    ]);

    // 4. Tentative de déplacement vers un créneau indisponible
    try {
        Schedule::for($user)->update($schedule->id, [
            'start_datetime' => Carbon::parse('2024-06-03 09:30:00'), // Chevauchement avec autre rendez-vous
            'end_datetime' => Carbon::parse('2024-06-03 10:30:00'),
        ]);
    } catch (\InvalidArgumentException $e) {
        echo "Impossible de déplacer: " . $e->getMessage();
    }
}
```

## Exemple 5 : Supprimer des rendez-vous

```php
use Roster\Schedule;
use App\Models\User;

$user = User::find(1);
$schedule = Schedule::for($user)->find(1);

if ($schedule) {
    // 1. Supprimer un rendez-vous
    $deleted = Schedule::for($user)->delete($schedule->id);

    if ($deleted) {
        echo "Rendez-vous supprimé avec succès";
    }

    // 2. Tentative de suppression d'un rendez-vous inexistant
    $result = Schedule::for($user)->delete(999);
    if (!$result) {
        echo "Rendez-vous non trouvé";
    }
}
```

## Exemple 6 : Vérifier la disponibilité

```php
use Roster\Schedule;
use App\Models\User;
use Illuminate\Support\Carbon;

$user = User::find(1);

// 1. Vérifier si un créneau est disponible
$start = Carbon::parse('2024-06-03 10:00:00');
$end = Carbon::parse('2024-06-03 11:00:00');

$isAvailable = Schedule::for($user)
    ->isTimeSlotAvailable($start, $end, 'consultation');

if ($isAvailable) {
    echo "Le créneau est disponible!";
} else {
    echo "Le créneau n'est pas disponible";
}

// 2. Vérifier avec un type spécifique
$isEmergencyAvailable = Schedule::for($user)
    ->isTimeSlotAvailable($start, $end, 'emergency');

// 3. Vérifier différents créneaux
$timeSlots = [
    ['09:00', '10:00'],
    ['10:00', '11:00'],
    ['11:00', '12:00'],
    ['14:00', '15:00'],
];

foreach ($timeSlots as $slot) {
    $slotStart = Carbon::parse('2024-06-03 ' . $slot[0]);
    $slotEnd = Carbon::parse('2024-06-03 ' . $slot[1]);

    $available = Schedule::for($user)
        ->isTimeSlotAvailable($slotStart, $slotEnd, 'consultation');

    echo "Créneau {$slot[0]}-{$slot[1]}: " . ($available ? 'Disponible' : 'Occupé');
}
```

## Exemple 7 : Trouver des créneaux disponibles

```php
use Roster\Schedule;
use App\Models\User;
use Illuminate\Support\Carbon;

$user = User::find(1);

// 1. Trouver le prochain créneau disponible
$nextSlot = Schedule::for($user)
    ->findNextAvailableSlot(60, 'consultation'); // 60 minutes, type consultation

if ($nextSlot) {
    echo "Prochain créneau disponible: ";
    echo "Début: " . $nextSlot['start']->format('d/m/Y H:i');
    echo "Fin: " . $nextSlot['end']->format('d/m/Y H:i');
    echo "Type: " . $nextSlot['type'];
    echo "Disponibilité ID: " . $nextSlot['availability_id'];
} else {
    echo "Aucun créneau disponible dans les 30 prochains jours";
}

// 2. Trouver tous les créneaux disponibles dans une période
$startDate = Carbon::parse('2024-06-01');
$endDate = Carbon::parse('2024-06-07');
$duration = 30; // 30 minutes

$findSlotsInPeriod = Schedule::for($user)
    ->findSlotsInPeriod($startDate, $endDate, $duration, 'consultation');

echo "Créneaux disponibles du 1er au 7 juin:";
foreach ($findSlotsInPeriod as $slot) {
    echo "• " . $slot['start']->format('d/m H:i') . " - " . $slot['end']->format('H:i');
    echo "  (Type: " . $slot['type'] . ")";
}

// 3. Pour les urgences (créneaux plus courts)
$emergencySlots = Schedule::for($user)
    ->findSlotsInPeriod($startDate, $endDate, 15, 'emergency'); // 15 minutes
```

## Exemple 8 : Gestion des conflits et chevauchements

```php
use Roster\Schedule;
use Roster\Impediment;
use App\Models\User;
use Illuminate\Support\Carbon;

$user = User::find(1);

// 1. Créer des rendez-vous qui se chevauchent (échouera)
try {
    // Premier rendez-vous
    Schedule::for($user)->create([
        'title' => 'Rendez-vous 1',
        'start_datetime' => Carbon::parse('2024-06-03 09:00:00'),
        'end_datetime' => Carbon::parse('2024-06-03 10:00:00'),
        'status' => 'booked',
    ]);

    // Deuxième rendez-vous qui chevauche (échouera)
    Schedule::for($user)->create([
        'title' => 'Rendez-vous 2',
        'start_datetime' => Carbon::parse('2024-06-03 09:30:00'), // Chevauchement
        'end_datetime' => Carbon::parse('2024-06-03 10:30:00'),
        'status' => 'booked',
    ]);
} catch (\InvalidArgumentException $e) {
    echo "Erreur de chevauchement: " . $e->getMessage();
}

// 2. Vérifier l'impact des impediments
// Créer un impediment (pause déjeuner)
Impediment::for($user)->create([
    'reason' => 'Pause déjeuner',
    'start_datetime' => Carbon::parse('2024-06-03 12:00:00'),
    'end_datetime' => Carbon::parse('2024-06-03 13:00:00'),
]);

// Essayer de créer un rendez-vous pendant l'impediment
try {
    Schedule::for($user)->create([
        'title' => 'Rendez-vous pendant pause',
        'start_datetime' => Carbon::parse('2024-06-03 12:30:00'),
        'end_datetime' => Carbon::parse('2024-06-03 13:30:00'),
        'status' => 'booked',
    ]);
} catch (\InvalidArgumentException $e) {
    echo "Erreur: " . $e->getMessage(); // "Schedule overlaps with an impediment"
}
```

## Exemple 9 : Intégration dans un contrôleur Laravel

```php
// App\Http\Controllers\ScheduleController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Roster\Schedule;
use Roster\Availability;
use App\Models\User;
use Illuminate\Support\Carbon;

class ScheduleController extends Controller
{
    /**
     * Afficher tous les rendez-vous d'un utilisateur
     */
    public function index(Request $request, User $user): JsonResponse
    {
        $query = Schedule::for($user);

        // Appliquer les filtres depuis la requête
        if ($request->has('type')) {
            $query->whereType($request->get('type'));
        }

        if ($request->has('status')) {
            $query->whereStatus($request->get('status'));
        }

        if ($request->has('start_date')) {
            $query->whereStartDate(Carbon::parse($request->get('start_date')));
        }

        if ($request->has('end_date')) {
            $query->whereEndDate(Carbon::parse($request->get('end_date')));
        }

        $schedules = $query->get();

        return response()->json([
            'data' => $schedules,
            'count' => $schedules->count(),
        ]);
    }

    /**
     * Créer un nouveau rendez-vous
     */
    public function store(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_datetime' => 'required|date|after:now',
            'end_datetime' => 'required|date|after:start_datetime',
            'status' => 'required|in:available,booked,cancelled,blocked',
            'type' => 'nullable|string',
            'metadata' => 'nullable|array',
        ]);

        try {
            $schedule = Schedule::for($user)->create($validated);

            return response()->json([
                'message' => 'Rendez-vous créé avec succès',
                'data' => $schedule,
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Mettre à jour un rendez-vous
     */
    public function update(Request $request, User $user, int $id): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'start_datetime' => 'sometimes|date|after:now',
            'end_datetime' => 'sometimes|date|after:start_datetime',
            'status' => 'sometimes|in:available,booked,cancelled,blocked',
            'metadata' => 'nullable|array',
        ]);

        $updated = Schedule::for($user)->update($id, $validated);

        if ($updated) {
            $schedule = Schedule::for($user)->find($id);

            return response()->json([
                'message' => 'Rendez-vous mis à jour',
                'data' => $schedule,
            ]);
        }

        return response()->json([
            'message' => 'Rendez-vous non trouvé',
        ], 404);
    }

    /**
     * Supprimer un rendez-vous
     */
    public function destroy(User $user, int $id): JsonResponse
    {
        $deleted = Schedule::for($user)->delete($id);

        if ($deleted) {
            return response()->json([
                'message' => 'Rendez-vous supprimé',
            ]);
        }

        return response()->json([
            'message' => 'Rendez-vous non trouvé',
        ], 404);
    }

    /**
     * Vérifier la disponibilité
     */
    public function checkAvailability(User $user, Request $request): JsonResponse
    {
        $request->validate([
            'start' => 'required|date|after:now',
            'end' => 'required|date|after:start',
            'type' => 'nullable|string',
        ]);

        $start = Carbon::parse($request->get('start'));
        $end = Carbon::parse($request->get('end'));
        $type = $request->get('type');

        $isAvailable = Schedule::for($user)
            ->isTimeSlotAvailable($start, $end, $type);

        return response()->json([
            'available' => $isAvailable,
        ]);
    }

    /**
     * Trouver les créneaux disponibles
     */
    public function findSlotsInPeriod(User $user, Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => 'required|date|after:yesterday',
            'end_date' => 'required|date|after:start_date',
            'duration' => 'required|integer|min:1|max:480', // en minutes, max 8h
            'type' => 'nullable|string',
        ]);

        $startDate = Carbon::parse($request->get('start_date'));
        $endDate = Carbon::parse($request->get('end_date'));
        $duration = $request->get('duration');
        $type = $request->get('type');

        // Vérifier que la période ne dépasse pas 3 mois
        if ($startDate->diffInDays($endDate) > 90) {
            return response()->json([
                'message' => 'La période ne peut pas dépasser 3 mois',
            ], 422);
        }

        $slots = Schedule::for($user)
            ->findSlotsInPeriod($startDate, $endDate, $duration, $type);

        return response()->json([
            'slots' => array_map(function($slot) {
                return [
                    'start' => $slot['start']->toDateTimeString(),
                    'end' => $slot['end']->toDateTimeString(),
                    'type' => $slot['type'],
                    'availability_id' => $slot['availability_id'],
                ];
            }, $slots),
            'count' => count($slots),
        ]);
    }

    /**
     * Trouver le prochain créneau disponible
     */
    public function findNextSlot(User $user, Request $request): JsonResponse
    {
        $request->validate([
            'duration' => 'required|integer|min:1|max:480',
            'type' => 'nullable|string',
        ]);

        $duration = $request->get('duration');
        $type = $request->get('type');

        $slot = Schedule::for($user)
            ->findNextAvailableSlot($duration, $type);

        if ($slot) {
            return response()->json([
                'slot' => [
                    'start' => $slot['start']->toDateTimeString(),
                    'end' => $slot['end']->toDateTimeString(),
                    'type' => $slot['type'],
                    'availability_id' => $slot['availability_id'],
                ],
            ]);
        }

        return response()->json([
            'message' => 'Aucun créneau disponible dans les 30 prochains jours',
        ], 404);
    }
}
```

## Exemple 10 : Utilisation dans les vues Blade

```blade
{{-- resources/views/schedules/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Rendez-vous de {{ $user->name }}</h2>

    <!-- Filtres -->
    <div class="card mb-4">
        <div class="card-body">
            <form id="filterForm">
                <div class="row">
                    <div class="col-md-3">
                        <label for="type" class="form-label">Type</label>
                        <select name="type" id="type" class="form-select">
                            <option value="">Tous les types</option>
                            <option value="consultation">Consultation</option>
                            <option value="emergency">Urgence</option>
                            <option value="meeting">Réunion</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="status" class="form-label">Statut</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">Tous les statuts</option>
                            <option value="available">Disponible</option>
                            <option value="booked">Réservé</option>
                            <option value="cancelled">Annulé</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="start_date" class="form-label">À partir du</label>
                        <input type="date" name="start_date" id="start_date" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label for="end_date" class="form-label">Jusqu'au</label>
                        <input type="date" name="end_date" id="end_date" class="form-control">
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">Filtrer</button>
                    <button type="button" id="resetFilters" class="btn btn-secondary">Réinitialiser</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des rendez-vous -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Date & Heure</th>
                            <th>Titre</th>
                            <th>Type</th>
                            <th>Statut</th>
                            <th>Durée</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($schedules as $schedule)
                        <tr>
                            <td>
                                {{ $schedule->start_datetime->format('d/m/Y H:i') }}<br>
                                <small>à {{ $schedule->end_datetime->format('H:i') }}</small>
                            </td>
                            <td>{{ $schedule->title }}</td>
                            <td>
                                <span class="badge bg-info">{{ $schedule->type }}</span>
                            </td>
                            <td>
                                @php
                                    $statusClasses = [
                                        'available' => 'bg-success',
                                        'booked' => 'bg-primary',
                                        'cancelled' => 'bg-danger',
                                        'blocked' => 'bg-warning'
                                    ];
                                @endphp
                                <span class="badge {{ $statusClasses[$schedule->status->value] ?? 'bg-secondary' }}">
                                    {{ $schedule->status->value }}
                                </span>
                            </td>
                            <td>{{ $schedule->duration_minutes }} minutes</td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editModal"
                                            data-id="{{ $schedule->id }}"
                                            data-title="{{ $schedule->title }}"
                                            data-start="{{ $schedule->start_datetime->format('Y-m-d\TH:i') }}"
                                            data-end="{{ $schedule->end_datetime->format('Y-m-d\TH:i') }}"
                                            data-status="{{ $schedule->status->value }}">
                                        Modifier
                                    </button>
                                    <form action="{{ route('schedules.destroy', [$user, $schedule->id]) }}"
                                          method="POST"
                                          class="d-inline"
                                          onsubmit="return confirm('Supprimer ce rendez-vous ?')">
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
        </div>
    </div>

    <!-- Bouton pour créer un nouveau rendez-vous -->
    <div class="mt-4">
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createModal">
            <i class="bi bi-plus-circle"></i> Nouveau rendez-vous
        </button>

        <!-- Bouton pour vérifier la disponibilité -->
        <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#availabilityModal">
            <i class="bi bi-calendar-check"></i> Vérifier disponibilité
        </button>
    </div>
</div>

<!-- Modals -->
@include('schedules.modals.create')
@include('schedules.modals.edit')
@include('schedules.modals.availability')

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

            modal.querySelector('#editTitle').value = button.getAttribute('data-title');
            modal.querySelector('#editStart').value = button.getAttribute('data-start');
            modal.querySelector('#editEnd').value = button.getAttribute('data-end');
            modal.querySelector('#editStatus').value = button.getAttribute('data-status');

            // Mettre à jour l'action du formulaire
            const form = modal.querySelector('#editForm');
            const scheduleId = button.getAttribute('data-id');
            form.action = form.action.replace(/\/\d+$/, '/' + scheduleId);
        });
    }
});
</script>
@endsection
```

## Exemple 11 : Scénario complet de prise de rendez-vous

```php
<?php
// App\Services\AppointmentService.php
namespace App\Services;

use Roster\Schedule;
use Roster\Availability;
use App\Models\User;
use App\Models\Patient;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class AppointmentService
{
    /**
     * Prendre un rendez-vous pour un patient
     */
    public function bookAppointment(User $doctor, Patient $patient, array $data): array
    {
        try {
            // Validation des données
            $this->validateAppointmentData($data);

            // Vérifier que le médecin a des disponibilités
            if (!$this->doctorHasAvailability($doctor, $data['type'])) {
                throw new InvalidArgumentException('Le médecin n\'a pas de disponibilité pour ce type de rendez-vous');
            }

            // Vérifier la disponibilité du créneau
            $start = Carbon::parse($data['start_datetime']);
            $end = Carbon::parse($data['end_datetime']);

            if (!Schedule::for($doctor)->isTimeSlotAvailable($start, $end, $data['type'])) {
                throw new InvalidArgumentException('Ce créneau n\'est plus disponible');
            }

            // Créer le rendez-vous
            $appointment = Schedule::for($doctor)->create([
                'title' => "Consultation - " . $patient->full_name,
                'description' => $data['reason'] ?? 'Consultation de routine',
                'start_datetime' => $start,
                'end_datetime' => $end,
                'status' => 'booked',
                'type' => $data['type'] ?? 'consultation',
                'metadata' => [
                    'patient_id' => $patient->id,
                    'patient_name' => $patient->full_name,
                    'reason' => $data['reason'] ?? null,
                    'booked_by' => auth()->user()->id ?? null,
                    'booked_at' => Carbon::now()->toDateTimeString(),
                ]
            ]);

            // Envoyer une confirmation par email
            $this->sendConfirmationEmail($patient, $appointment);

            // Log de l'action
            Log::info('Rendez-vous créé', [
                'doctor_id' => $doctor->id,
                'patient_id' => $patient->id,
                'appointment_id' => $appointment->id,
                'datetime' => $start->toDateTimeString(),
            ]);

            return [
                'success' => true,
                'appointment' => $appointment,
                'message' => 'Rendez-vous confirmé',
            ];

        } catch (InvalidArgumentException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'suggestions' => $this->getAlternativeSlots($doctor, $data),
            ];
        }
    }

    /**
     * Annuler un rendez-vous
     */
    public function cancelAppointment(User $doctor, int $appointmentId, string $reason): array
    {
        try {
            $appointment = Schedule::for($doctor)->find($appointmentId);

            if (!$appointment) {
                throw new InvalidArgumentException('Rendez-vous non trouvé');
            }

            if ($appointment->isPast()) {
                throw new InvalidArgumentException('Impossible d\'annuler un rendez-vous passé');
            }

            // Mettre à jour le statut
            Schedule::for($doctor)->update($appointmentId, [
                'status' => 'cancelled',
                'metadata' => array_merge($appointment->metadata ?? [], [
                    'cancelled_at' => Carbon::now()->toDateTimeString(),
                    'cancelled_by' => auth()->user()->id ?? null,
                    'cancellation_reason' => $reason,
                ])
            ]);

            // Rendre le créneau disponible
            $newAppointment = Schedule::for($doctor)->create([
                'title' => 'Créneau disponible',
                'description' => 'Anciennement réservé par ' . ($appointment->metadata['patient_name'] ?? 'patient'),
                'start_datetime' => $appointment->start_datetime,
                'end_datetime' => $appointment->end_datetime,
                'status' => 'available',
                'type' => $appointment->type,
                'metadata' => [
                    'was_cancelled' => true,
                    'original_appointment_id' => $appointment->id,
                    'cancelled_at' => Carbon::now()->toDateTimeString(),
                ]
            ]);

            // Envoyer une notification d'annulation
            $this->sendCancellationEmail($appointment);

            Log::info('Rendez-vous annulé', [
                'appointment_id' => $appointment->id,
                'new_appointment_id' => $newAppointment->id,
                'reason' => $reason,
            ]);

            return [
                'success' => true,
                'message' => 'Rendez-vous annulé',
                'cancelled_appointment' => $appointment->fresh(),
                'available_slot' => $newAppointment,
            ];

        } catch (InvalidArgumentException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     Trouver des créneaux alternatifs
     */
    public function findAlternativeSlots(User $doctor, array $originalRequest): array
    {
        $duration = Carbon::parse($originalRequest['end_datetime'])
            ->diffInMinutes(Carbon::parse($originalRequest['start_datetime']));

        $type = $originalRequest['type'] ?? 'consultation';

        // Chercher des créneaux aujourd'hui et demain
        $today = Carbon::today();
        $tomorrow = Carbon::tomorrow();

        // Créneaux disponibles aujourd'hui
        $slotsToday = Schedule::for($doctor)
            ->findSlotsInPeriod($today, $today->copy()->endOfDay(), $duration, $type);

        // Créneaux disponibles demain
        $slotsTomorrow = Schedule::for($doctor)
            ->findSlotsInPeriod($tomorrow, $tomorrow->copy()->endOfDay(), $duration, $type);

        // Prochain créneau disponible
        $nextSlot = Schedule::for($doctor)
            ->findNextAvailableSlot($duration, $type);

        return [
            'today' => array_slice($slotsToday, 0, 3), // 3 premiers créneaux d'aujourd'hui
            'tomorrow' => array_slice($slotsTomorrow, 0, 3), // 3 premiers créneaux de demain
            'next_available' => $nextSlot,
        ];
    }

    /**
     * Valider les données du rendez-vous
     */
    protected function validateAppointmentData(array $data): void
    {
        $required = ['start_datetime', 'end_datetime'];

        foreach ($required as $field) {
            if (!isset($data[$field])) {
                throw new InvalidArgumentException("Le champ '$field' est requis");
            }
        }

        $start = Carbon::parse($data['start_datetime']);
        $end = Carbon::parse($data['end_datetime']);

        if ($end->lte($start)) {
            throw new InvalidArgumentException('L\'heure de fin doit être après l\'heure de début');
        }

        if ($start->lt(Carbon::now())) {
            throw new InvalidArgumentException('Impossible de prendre rendez-vous dans le passé');
        }

        // Vérifier que la durée est raisonnable
        $duration = $end->diffInMinutes($start);
        if ($duration < 15 || $duration > 240) { // Entre 15 minutes et 4 heures
            throw new InvalidArgumentException('La durée du rendez-vous doit être entre 15 minutes et 4 heures');
        }
    }

    /**
     * Vérifier si le médecin a des disponibilités pour un type donné
     */
    protected function doctorHasAvailability(User $doctor, ?string $type): bool
    {
        $query = Availability::for($doctor);

        if ($type) {
            $query->whereType($type);
        }

        return $query->count() > 0;
    }

    /**
     * Envoyer un email de confirmation
     */
    protected function sendConfirmationEmail(Patient $patient, $appointment): void
    {
        // Implémenter l'envoi d'email
        // Mail::to($patient->email)->send(new AppointmentConfirmation($appointment));
    }

    /**
     * Envoyer un email d'annulation
     */
    protected function sendCancellationEmail($appointment): void
    {
        // Implémenter l'envoi d'email
        // if (isset($appointment->metadata['patient_email'])) {
        //     Mail::to($appointment->metadata['patient_email'])
        //         ->send(new AppointmentCancellation($appointment));
        // }
    }
}
```

Ces exemples montrent comment utiliser toutes les fonctionnalités du `ScheduleService` dans le contexte réel d'une application Laravel, en respectant la contrainte de ne pas pouvoir créer de schedules dans le passé.
```
