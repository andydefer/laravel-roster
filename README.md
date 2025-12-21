# Laravel Roster

![PHP Version](https://img.shields.io/badge/PHP-8.3%2B-blue)
![Laravel Version](https://img.shields.io/badge/Laravel-10%2B-orange)
![License](https://img.shields.io/badge/license-MIT-green)
![Tests](https://img.shields.io/badge/tests-161%20passing-brightgreen)
![Coverage](https://img.shields.io/badge/coverage-88%25-green)

**Roster** est un package Laravel complet et robuste pour la gestion avancée d'emplois du temps, de disponibilités et de réservations. Conçu pour les applications SaaS complexes, il gère les disponibilités récurrentes, les créneaux réservés et les empêchements avec une précision chirurgicale.

## ✨ Fonctionnalités

### 🗓️ **Gestion complète des disponibilités**
- **Disponibilités récurrentes** avec jours, heures et plages de dates
- **Fusion automatique** des disponibilités adjacentes
- **Validation des chevauchements** en temps réel
- **Filtrage** par type, jour et plage temporelle

### ⏰ **Système de réservation intelligent**
- **Créneaux horaires** avec titres, descriptions et statuts
- **Recherche de disponibilités** automatique
- **Prévention des doubles réservations**
- **Statuts multiples** (disponible, réservé, annulé, bloqué)

### 🚫 **Gestion des empêchements (Impediments)**
- **Blocages temporaires** pour indisponibilités
- **Validation** contre les chevauchements avec les réservations
- **Raisons** et métadonnées personnalisables

### 🎯 **Recherche avancée**
- **Trouver le prochain créneau disponible**
- **Recherche de plages continues**
- **Vérification de disponibilité** à un instant précis
- **Génération de créneaux** dans une période

## 📦 Installation

```bash
composer require vendor/laravel-roster
```

Publiez les ressources du package :

```bash
php artisan roster:install
```

Ou manuellement :

```bash
# Configuration
php artisan vendor:publish --tag=roster-config

# Migrations
php artisan vendor:publish --tag=roster-migrations

# Exécutez les migrations
php artisan migrate
```

## 🚀 Utilisation rapide

### 1. Ajoutez le trait à vos modèles

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Roster\Traits\HasRoster;

class User extends Model
{
    use HasRoster;

    // Votre modèle est maintenant "schedulable" !
}
```

### 2. Créez des disponibilités

```php
use Roster\Facades\Availability;

// Créer une disponibilité récurrente
$availability = Availability::for($user)->create([
    'type' => 'consultation',
    'start_time' => '09:00:00',
    'end_time' => '17:00:00',
    'days' => ['monday', 'wednesday', 'friday'],
    'start_date' => '2024-01-01',
    'end_date' => '2024-12-31',
]);
```

### 3. Planifiez des rendez-vous

```php
use Roster\Facades\Schedule;

// Réserver un créneau
$appointment = Schedule::for($user)->create($availability, [
    'title' => 'Consultation client',
    'start_datetime' => '2024-06-10 10:00:00',
    'end_datetime' => '2024-06-10 11:00:00',
    'status' => 'booked',
    'description' => 'Première consultation avec M. Dupont',
]);
```

### 4. Gérez les indisponibilités

```php
use Roster\Facades\Impediment;

// Bloquer un créneau (vacances, formation, etc.)
$impediment = Impediment::for($user)->create($availability, [
    'reason' => 'Formation interne',
    'start_datetime' => '2024-06-15 09:00:00',
    'end_datetime' => '2024-06-15 17:00:00',
    'metadata' => ['location' => 'Salle A', 'trainer' => 'Jane Doe'],
]);
```

## 📖 Documentation complète

### Architecture

Roster suit une architecture en couches propre :

```
Modèles (Models)
    ├── Availability (règles de disponibilité)
    ├── Schedule (créneaux réservés)
    └── Impediment (blocages temporaires)
        ↓
Repositories (Couche données)
        ↓
Services (Logique métier)
        ↓
Facades (API publique)
```

### Les 3 entités principales

#### 1. **Availability** (Disponibilité)
Définit quand une ressource est disponible de manière récurrente.

```php
$availability = Availability::for($resource)->create([
    'type' => 'meeting',           // Type d'activité
    'start_time' => '09:00:00',    // Heure de début quotidienne
    'end_time' => '18:00:00',      // Heure de fin quotidienne
    'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
    'start_date' => '2024-01-01',  // Date de début (optionnel)
    'end_date' => '2024-12-31',    // Date de fin (optionnel)
]);
```

#### 2. **Schedule** (Créneau)
Un rendez-vous ou événement spécifique dans une disponibilité.

```php
$schedule = Schedule::for($resource)->create($availability, [
    'title' => 'Réunion d\'équipe',
    'description' => 'Revue hebdomadaire des projets',
    'start_datetime' => '2024-06-10 14:00:00',
    'end_datetime' => '2024-06-10 15:30:00',
    'status' => 'booked', // available, booked, cancelled, blocked
    'metadata' => [
        'attendees' => ['Alice', 'Bob', 'Charlie'],
        'project' => 'Projet Alpha',
    ],
]);
```

#### 3. **Impediment** (Empêchement)
Une période où la disponibilité est temporairement suspendue.

```php
$impediment = Impediment::for($resource)->create($availability, [
    'reason' => 'Maintenance système',
    'start_datetime' => '2024-06-15 00:00:00',
    'end_datetime' => '2024-06-15 06:00:00',
    'metadata' => [
        'type' => 'maintenance',
        'impact' => 'system_down',
    ],
]);
```

### API Fluent

#### Disponibilités (Availability Facade)

```php
use Roster\Facades\Availability;

// CRUD de base
Availability::for($user)->create([...]);
Availability::for($user)->find($id);
Availability::for($user)->update($id, [...]);
Availability::for($user)->delete($id);

// Recherche et filtrage
Availability::for($user)
    ->whereType('consultation')
    ->filterByDay('monday')
    ->get();

// Vérifications
Availability::for($user)->isAvailableAt($datetime);
Availability::for($user)->isAvailableForPeriod($start, $end);

// Recherche de créneaux
$slots = Availability::for($user)->findSlotsInPeriod(
    startDate: '2024-06-01',
    endDate: '2024-06-30',
    durationMinutes: 60,
    intervalMinutes: 30,
    type: 'consultation'
);
```

#### Réservations (Schedule Facade)

```php
use Roster\Facades\Schedule;

// Création avec availability requise
Schedule::for($room)->create($availability, [...]);

// Trouver le prochain créneau
$nextSlot = Schedule::for($doctor)->findNextAvailableSlot(
    durationMinutes: 30,
    type: 'consultation'
);

// Vérifier la disponibilité
$isAvailable = Schedule::for($resource)->isTimeSlotAvailable(
    start: '2024-06-10 14:00:00',
    end: '2024-06-10 15:00:00',
    type: 'meeting'
);

// Récupérer les réservations d'une période
$appointments = Schedule::for($user)
    ->between('2024-06-01', '2024-06-30')
    ->whereStatus('booked')
    ->get();
```

#### Empêchements (Impediment Facade)

```php
use Roster\Facades\Impediment;

// Création avec availability requise
Impediment::for($equipment)->create($availability, [...]);

// Vérifier si un créneau est bloqué
$isBlocked = Impediment::for($resource)->isTimeSlotBlocked(
    start: '2024-06-15 10:00:00',
    end: '2024-06-15 11:00:00'
);

// Obtenir les créneaux disponibles malgré les empêchements
$availableSlots = Impediment::for($resource)->getAvailableTimeSlots(
    start: '2024-06-15 08:00:00',
    end: '2024-06-15 18:00:00'
);
```

### Fonctionnalités avancées

#### Fusion automatique des disponibilités

Roster fusionne automatiquement les disponibilités adjacentes :

```php
// Ces deux disponibilités seront fusionnées en une seule
Availability::for($user)->create([
    'type' => 'consultation',
    'start_time' => '09:00:00',
    'end_time' => '12:00:00',
    'days' => ['monday'],
]);

Availability::for($user)->create([
    'type' => 'consultation',
    'start_time' => '12:00:00',
    'end_time' => '15:00:00',
    'days' => ['monday'],
]);

// Résultat : Une seule disponibilité de 09:00 à 15:00
```

#### Validation des chevauchements

Le package prévient automatiquement les conflits :

```php
// ❌ Cela lèvera une exception
try {
    Schedule::for($room)->create($availability, [
        'start_datetime' => '2024-06-10 10:30:00',
        'end_datetime' => '2024-06-10 11:30:00',
    ]);

    // Chevauchement avec le créneau existant
    Schedule::for($room)->create($availability, [
        'start_datetime' => '2024-06-10 11:00:00',
        'end_datetime' => '2024-06-10 12:00:00',
    ]);
} catch (\Roster\Exceptions\OverlappingScheduleException $e) {
    // Gérer le conflit
}
```

#### Plages de dates ouvertes

Support des plages infinies :

```php
// Disponibilité sans date de fin
Availability::for($user)->create([
    'type' => 'consultation',
    'start_time' => '09:00:00',
    'end_time' => '17:00:00',
    'days' => ['monday', 'wednesday', 'friday'],
    'start_date' => '2024-01-01',
    // Pas de end_date = disponible indéfiniment
]);

// Disponibilité sans dates (toujours valide)
Availability::for($user)->create([
    'type' => 'emergency',
    'start_time' => '00:00:00',
    'end_time' => '23:59:59',
    'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'],
    // Pas de start_date ni end_date = toujours disponible
]);
```

## ⚙️ Configuration

Publiez le fichier de configuration pour personnaliser le package :

```bash
php artisan vendor:publish --tag=roster-config
```

### Configuration principale (`config/roster.php`)

```php
return [
    // Timezone par défaut
    'timezone' => env('ROSTER_TIMEZONE', 'UTC'),

    // Validation des dates futures
    'validate_future_dates' => [
        'enabled' => true,
        'availability' => ['allow_past' => false],
        'schedule' => ['allow_past' => false],
        'impediment' => ['allow_past' => true],
    ],

    // Durées minimales (en minutes)
    'durations' => [
        'minimum_impediment_minutes' => 5,
        'minimum_schedule_minutes' => 15,
        'minimum_availability_minutes' => 15,
        'default_slot_duration_minutes' => 60,
    ],

    // Cache
    'cache' => [
        'enabled' => true,
        'ttl' => 3600,
        'availability' => ['enabled' => true, 'ttl' => 1800],
        'schedule' => ['enabled' => true, 'ttl' => 900],
    ],
];
```

### Variables d'environnement

```env
ROSTER_TIMEZONE=Europe/Paris
ROSTER_VALIDATE_FUTURE_DATES=true
ROSTER_MIN_SCHEDULE_MINUTES=15
ROSTER_CACHE_ENABLED=true
ROSTER_CACHE_TTL=3600
```

## 🔧 Personnalisation

### Créer des types d'activité personnalisés

Étendez l'enum `ActivityType` :

```php
namespace App\Enums;

use Roster\Enums\ActivityType as BaseActivityType;

enum CustomActivityType: string
{
    case SURGERY = 'surgery';
    case PHYSIOTHERAPY = 'physiotherapy';
    case LAB_TEST = 'lab_test';

    public static function values(): array
    {
        return array_merge(
            parent::values(),
            array_column(self::cases(), 'value')
        );
    }
}
```

### Ajouter des relations personnalisées

```php
namespace App\Models;

use Roster\Models\Schedule as BaseSchedule;

class Appointment extends BaseSchedule
{
    protected $table = 'roster_schedules';

    // Relations supplémentaires
    public function patient()
    {
        return $this->belongsTo(Patient::class, 'metadata->patient_id');
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'appointment_id');
    }
}
```

## 🧪 Tests

Le package inclut 161 tests complets couvrant 88% du code.

```bash
# Exécuter tous les tests
php artisan test --testsuite=roster

# Exécuter des tests spécifiques
php artisan test --filter=AvailabilityServiceTest
php artisan test --filter=test_create_schedule_successfully

# Avec coverage (si Xdebug est installé)
php artisan test --coverage --min=80
```

### Structure des tests

```
tests/
├── Unit/                          # Tests unitaires
│   ├── Commands/                  # Tests de commandes
│   └── Services/                  # Tests de services unitaires
├── Feature/                       # Tests fonctionnels
│   ├── Facades/                   # Tests des facades
│   └── Services/                  # Tests des services
├── Integration/                   # Tests d'intégration
│   └── Traits/                    # Tests des traits
└── database/                      # Migrations de test
```

## 🚨 Gestion des erreurs

Roster utilise un système d'exceptions détaillé :

```php
use Roster\Exceptions\{
    ValidationException,
    OverlappingScheduleException,
    OverlappingImpedimentException,
    ScheduleImpedimentOverlapException,
    MissingSchedulableException
};

try {
    $schedule = Schedule::for($user)->create($availability, $data);
} catch (OverlappingScheduleException $e) {
    // Créneau qui chevauche une réservation existante
    return response()->json([
        'error' => 'time_slot_unavailable',
        'message' => $e->getMessage(),
        'context' => $e->getContext(),
    ], 409);
} catch (ValidationException $e) {
    // Données invalides
    return response()->json([
        'error' => 'validation_failed',
        'message' => $e->getMessage(),
    ], 422);
} catch (MissingSchedulableException $e) {
    // Modèle schedulable non défini
    return response()->json([
        'error' => 'missing_resource',
        'message' => $e->getMessage(),
    ], 400);
}
```

## 📊 Performances

### Optimisations incluses

1. **Cache configurable** : Résultats fréquents en cache
2. **Indexes de base de données** : Optimisés pour les recherches temporelles
3. **Requêtes optimisées** : Utilisation de whereJsonContains efficace
4. **Pagination mentale** : Limitation des périodes de recherche

### Bonnes pratiques

```php
// ✅ Efficace : Limiter la période de recherche
Availability::for($user)->findSlotsInPeriod(
    startDate: Carbon::now(),
    endDate: Carbon::now()->addDays(30), // Limité à 30 jours
    durationMinutes: 60
);

// ❌ À éviter : Périodes trop longues
Availability::for($user)->findSlotsInPeriod(
    startDate: Carbon::now(),
    endDate: Carbon::now()->addYears(10), // Trop long !
    durationMinutes: 60
);
```

## 🔄 Workflows courants

### Système de rendez-vous médicaux

```php
class AppointmentController
{
    public function book(AppointmentRequest $request, Doctor $doctor)
    {
        // 1. Trouver une disponibilité
        $availability = Availability::for($doctor)
            ->whereType('consultation')
            ->first();

        // 2. Vérifier la disponibilité du créneau
        $isAvailable = Schedule::for($doctor)->isTimeSlotAvailable(
            start: $request->input('start_time'),
            end: $request->input('end_time'),
            type: 'consultation'
        );

        if (!$isAvailable) {
            // 3. Trouver le prochain créneau disponible
            $nextSlot = Schedule::for($doctor)->findNextAvailableSlot(
                durationMinutes: 30,
                type: 'consultation'
            );

            return response()->json([
                'message' => 'Créneau non disponible',
                'next_available' => $nextSlot,
            ], 409);
        }

        // 4. Créer le rendez-vous
        $appointment = Schedule::for($doctor)->create($availability, [
            'title' => "Consultation {$request->user()->name}",
            'start_datetime' => $request->input('start_time'),
            'end_datetime' => $request->input('end_time'),
            'status' => 'booked',
            'metadata' => [
                'patient_id' => $request->user()->id,
                'reason' => $request->input('reason'),
            ],
        ]);

        // 5. Envoyer une confirmation
        // ...

        return response()->json($appointment, 201);
    }
}
```

### Système de réservation de salles

```php
class RoomBookingController
{
    public function checkAvailability(Room $room, Request $request)
    {
        $slots = Availability::for($room)->findSlotsInPeriod(
            startDate: $request->input('start_date'),
            endDate: $request->input('end_date'),
            durationMinutes: $request->input('duration', 60),
            intervalMinutes: 30,
            type: 'meeting'
        );

        // Filtrer les créneaux avec des empêchements
        $availableSlots = collect($slots)->filter(function ($slot) use ($room) {
            return !Impediment::for($room)->isTimeSlotBlocked(
                start: $slot['start'],
                end: $slot['end']
            );
        });

        return response()->json($availableSlots->values());
    }
}
```

## 🤝 Contribution

Les contributions sont les bienvenues ! Veuillez suivre ces étapes :

1. **Fork** le repository
2. **Créez une branche** (`git checkout -b feature/amazing-feature`)
3. **Commitez vos changements** (`git commit -m 'Add amazing feature'`)
4. **Push vers la branche** (`git push origin feature/amazing-feature`)
5. **Ouvrez une Pull Request**

### Standards de code

- Suivez les [PSR-12](https://www.php-fig.org/psr/psr-12/)
- Ajoutez des tests pour toute nouvelle fonctionnalité
- Mettez à jour la documentation
- Utilisez des types stricts (`declare(strict_types=1)`)
- Documentez le code avec PHPDoc

### Exécuter les tests

```bash
# Installer les dépendances de développement
composer install

# Exécuter les tests
composer test

# Vérifier la couverture de code
composer test-coverage

# Vérifier le style de code
composer lint
```

## 📄 Licence

Ce package est open-source et disponible sous la licence [MIT](LICENSE).

## 🙏 Remerciements

- [Laravel](https://laravel.com) pour l'excellent framework
- [Carbon](https://carbon.nesbot.com) pour la gestion des dates
- Tous les [contributeurs](../../contributors) qui ont aidé à ce projet

## 🔗 Liens utiles

- [Documentation complète](docs/README.md)
- [Guide de migration](docs/migration.md)
- [API Reference](docs/api.md)
- [Changelog](CHANGELOG.md)
- [Issues](https://github.com/vendor/laravel-roster/issues)
- [Discussions](https://github.com/vendor/laravel-roster/discussions)

---

**Roster** - Parce que la gestion du temps devrait être simple, pas chronophage. ⏰✨