Voici des exemples complets d'utilisation pour chaque fonctionnalité du `AvailabilityService` via la façade `Roster\Availability` :

## Configuration initiale dans un modèle

```php
// App\Models\User.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    // Utiliser le trait pour gérer les disponibilités
    use \Roster\Traits\HasRoster;
}
```

## Exemple 1 : Créer des disponibilités

```php
use Roster\Availability;
use App\Models\User;

// Obtenir un utilisateur
$user = User::find(1);

// Créer une disponibilité de base
$availability = Availability::for($user)->create([
    'type' => 'consultation',
    'start_time' => '09:00:00',
    'end_time' => '12:00:00',
    'days' => ['monday', 'wednesday', 'friday'],
]);

// Créer avec dates limites
$availability = Availability::for($user)->create([
    'type' => 'training',
    'start_time' => '14:00:00',
    'end_time' => '17:00:00',
    'days' => ['tuesday', 'thursday'],
    'start_date' => '2024-01-01',
    'end_date' => '2024-06-30',
]);

// Création avec fusion automatique des disponibilités adjacentes
$availability1 = Availability::for($user)->create([
    'type' => 'consultation',
    'start_time' => '09:00:00',
    'end_time' => '12:00:00',
    'days' => ['monday'],
]);

// Celle-ci fusionnera avec la précédente car elle est adjacente (commence là où la première finit)
$availability2 = Availability::for($user)->create([
    'type' => 'consultation',
    'start_time' => '12:00:00', // Adjacent à la fin de la première
    'end_time' => '14:00:00',
    'days' => ['monday'],
]);

// Résultat: Une seule disponibilité de 09:00 à 14:00 le lundi
```

## Exemple 2 : Gestion des erreurs et validations

```php
use Roster\Availability;
use App\Models\User;

$user = User::find(1);

// Tentative de création avec chevauchement (échouera)
try {
    Availability::for($user)->create([
        'type' => 'consultation',
        'start_time' => '09:00:00',
        'end_time' => '12:00:00',
        'days' => ['monday'],
    ]);

    // Échouera car chevauchement
    Availability::for($user)->create([
        'type' => 'consultation',
        'start_time' => '10:00:00',
        'end_time' => '13:00:00',
        'days' => ['monday'],
    ]);
} catch (\InvalidArgumentException $e) {
    echo $e->getMessage(); // "This availability overlaps with an existing one."
}

// Types différents ne peuvent pas se chevaucher non plus
try {
    Availability::for($user)->create([
        'type' => 'consultation',
        'start_time' => '09:00:00',
        'end_time' => '12:00:00',
        'days' => ['monday'],
    ]);

    // Échouera aussi (pas de chevauchement entre types)
    Availability::for($user)->create([
        'type' => 'meeting', // Type différent mais même horaire
        'start_time' => '09:00:00',
        'end_time' => '12:00:00',
        'days' => ['monday'],
    ]);
} catch (\InvalidArgumentException $e) {
    echo $e->getMessage(); // "This availability overlaps with an existing one."
}
```

## Exemple 3 : Mettre à jour des disponibilités

```php
use Roster\Availability;
use App\Models\User;

$user = User::find(1);
$availability = $user->availabilities()->first();

// Mise à jour partielle
Availability::for($user)->update($availability->id, [
    'start_time' => '10:00:00',
    'end_time' => '13:00:00',
    'days' => ['monday', 'tuesday'], // Changement de jours
]);

// Mise à jour complète
Availability::for($user)->update($availability->id, [
    'type' => 'emergency',
    'start_time' => '08:00:00',
    'end_time' => '20:00:00',
    'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
    'start_date' => '2024-01-01',
    'end_date' => '2024-12-31',
]);
```

## Exemple 4 : Supprimer et rechercher

```php
use Roster\Availability;
use App\Models\User;

$user = User::find(1);

// Supprimer une disponibilité
Availability::for($user)->delete(1);

// Trouver par ID
$availability = Availability::for($user)->find(2);

// Vérifier l'existence de chevauchements
$hasOverlap = Availability::for($user)->hasOverlapping([
    'type' => 'consultation',
    'start_time' => '10:00:00',
    'end_time' => '12:00:00',
    'days' => ['monday'],
]);

if ($hasOverlap) {
    echo "Cette plage horaire n'est pas disponible";
}

// Trouver toutes les disponibilités qui chevauchent
$overlapping = Availability::for($user)->findOverlapping([
    'type' => 'consultation',
    'start_time' => '14:00:00',
    'end_time' => '16:00:00',
    'days' => ['tuesday'],
]);

foreach ($overlapping as $availability) {
    echo "Disponibilité ID {$availability->id} chevauche";
}
```

## Exemple 5 : Récupération et filtrage

```php
use Roster\Availability;
use App\Models\User;

$user = User::find(1);

// Récupérer toutes les disponibilités
$allAvailabilities = Availability::for($user)->all();

// Récupérer avec filtres
$consultations = Availability::for($user)
    ->whereType('consultation')
    ->get();

$mondayAvailabilities = Availability::for($user)
    ->whereDay('monday')
    ->get();

// Combinaison de filtres
$mondayConsultations = Availability::for($user)
    ->whereType('consultation')
    ->whereDay('monday')
    ->get();

// Réinitialiser les filtres
Availability::for($user)
    ->whereType('consultation')
    ->whereDay('monday')
    ->resetFilters()
    ->get(); // Retourne toutes les disponibilités
```

## Exemple 6 : Vérification de disponibilité en temps réel

```php
use Roster\Availability;
use Illuminate\Support\Carbon;
use App\Models\User;

$user = User::find(1);
$datetime = Carbon::parse('2024-01-15 10:30:00'); // Un lundi à 10h30

// Vérifier si l'utilisateur est disponible à un moment précis
if (Availability::for($user)->isAvailableAt($datetime)) {
    echo "L'utilisateur est disponible à ce moment";
} else {
    echo "L'utilisateur n'est pas disponible à ce moment";
}

// Trouver le prochain créneau disponible
$nextSlot = Availability::for($user)->nextAvailableSlot(
    Carbon::now(), // À partir de maintenant
    60 // Durée souhaitée en minutes
);

if ($nextSlot) {
    echo "Prochain créneau disponible : " . $nextSlot->format('Y-m-d H:i:s');
}

// Récupérer tous les créneaux disponibles dans une période
$slots = Availability::for($user)->availableSlots(
    Carbon::parse('2024-01-01'),
    Carbon::parse('2024-01-31'),
    60, // Durée du créneau en minutes
    30  // Intervalle entre les créneaux en minutes
);

foreach ($slots as $slot) {
    echo "Créneau disponible : {$slot['start']->format('Y-m-d H:i')} - {$slot['end']->format('Y-m-d H:i')}";
    echo "Type : {$slot['type']}";
}
```

## Exemple 7 : Gestion des disponibilités adjacentes

```php
use Roster\Availability;
use App\Models\User;

$user = User::find(1);

// Trouver les disponibilités adjacentes à une nouvelle
$adjacent = Availability::for($user)->findAdjacentAvailabilities([
    'type' => 'consultation',
    'start_time' => '12:00:00',
    'end_time' => '14:00:00',
    'days' => ['monday'],
]);

foreach ($adjacent as $availability) {
    echo "Disponibilité adjacente ID: {$availability->id}";
    echo "Horaire : {$availability->start_time->format('H:i')} - {$availability->end_time->format('H:i')}";
}

// Lors de la création, les disponibilités adjacentes sont automatiquement fusionnées
// Exemple avec 3 créneaux qui se touchent
Availability::for($user)->create([
    'type' => 'consultation',
    'start_time' => '09:00:00',
    'end_time' => '11:00:00',
    'days' => ['monday'],
]);

Availability::for($user)->create([
    'type' => 'consultation',
    'start_time' => '11:00:00', // Adjacent
    'end_time' => '13:00:00',
    'days' => ['monday'],
]);

// Résultat : une seule disponibilité de 09:00 à 13:00
```

## Exemple 8 : Intégration dans un contrôleur Laravel

```php
// App\Http\Controllers\AvailabilityController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Roster\Availability;
use App\Models\User;
use Illuminate\Support\Carbon;

class AvailabilityController extends Controller
{
    public function index(User $user): JsonResponse
    {
        $availabilities = Availability::for($user)->all();
        return response()->json($availabilities);
    }

    public function store(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|string',
            'start_time' => 'required|date_format:H:i:s',
            'end_time' => 'required|date_format:H:i:s|after:start_time',
            'days' => 'required|array|min:1',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        try {
            $availability = Availability::for($user)->create($validated);
            return response()->json($availability, 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function update(Request $request, User $user, $id): JsonResponse
    {
        $validated = $request->validate([
            'start_time' => 'sometimes|date_format:H:i:s',
            'end_time' => 'sometimes|date_format:H:i:s|after:start_time',
            'days' => 'sometimes|array|min:1',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $updated = Availability::for($user)->update($id, $validated);

        if ($updated) {
            return response()->json(['message' => 'Disponibilité mise à jour']);
        }

        return response()->json(['message' => 'Disponibilité non trouvée'], 404);
    }

    public function checkAvailability(User $user, Request $request): JsonResponse
    {
        $request->validate([
            'datetime' => 'required|date',
            'duration' => 'required|integer|min:1', // en minutes
        ]);

        $datetime = Carbon::parse($request->datetime);

        // Vérifier si disponible maintenant
        $isAvailable = Availability::for($user)->isAvailableAt($datetime);

        // Trouver le prochain créneau
        $nextSlot = Availability::for($user)->nextAvailableSlot(
            $datetime,
            $request->duration
        );

        return response()->json([
            'available' => $isAvailable,
            'next_available_slot' => $nextSlot ? $nextSlot->format('Y-m-d H:i:s') : null,
        ]);
    }
}
```

## Exemple 9 : Utilisation dans les vues Blade

```blade
{{-- resources/views/availabilities/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Disponibilités de {{ $user->name }}</h2>

    @foreach($availabilities as $availability)
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title">{{ $availability->type }}</h5>
            <p class="card-text">
                Jours: {{ implode(', ', $availability->days) }}<br>
                Horaires: {{ $availability->start_time->format('H:i') }} - {{ $availability->end_time->format('H:i') }}<br>
                @if($availability->start_date && $availability->end_date)
                Période: {{ $availability->start_date->format('d/m/Y') }} - {{ $availability->end_date->format('d/m/Y') }}
                @else
                Période: Indéfinie
                @endif
            </p>
        </div>
    </div>
    @endforeach

    <h3>Vérifier la disponibilité</h3>
    <form action="{{ route('availabilities.check', $user) }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="datetime" class="form-label">Date et heure</label>
            <input type="datetime-local" class="form-control" id="datetime" name="datetime" required>
        </div>
        <div class="mb-3">
            <label for="duration" class="form-label">Durée (minutes)</label>
            <input type="number" class="form-control" id="duration" name="duration" value="60" min="1" required>
        </div>
        <button type="submit" class="btn btn-primary">Vérifier</button>
    </form>

    @if(session('availability_check'))
    <div class="alert alert-info mt-3">
        @if(session('availability_check.available'))
        Disponible à cette heure !
        @else
        Non disponible à cette heure.
        @endif
        @if(session('availability_check.next_slot'))
        Prochain créneau disponible: {{ session('availability_check.next_slot') }}
        @endif
    </div>
    @endif
</div>
@endsection
```

Ces exemples montrent comment utiliser toutes les fonctionnalités du `AvailabilityService` dans le contexte réel d'une application Laravel.