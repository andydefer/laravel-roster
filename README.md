# Laravel Roster

![PHP Version](https://img.shields.io/badge/PHP-8.3%2B-blue)
![Laravel Version](https://img.shields.io/badge/Laravel-12%2B-orange)
![License](https://img.shields.io/badge/license-MIT-green)
![Tests](https://img.shields.io/badge/tests-760%20passing-brightgreen)
![Coverage](https://img.shields.io/badge/coverage-88%25-green)

**Roster** is a comprehensive Laravel package for advanced schedule management, availabilities, and bookings. Built with a robust architecture, it handles recurring availabilities, booked slots, and impediments with exhaustive business validation.

## 📦 Installation

```bash
composer require andydefer/laravel-roster
```

Publish package resources:

```bash
php artisan roster:install
```

Or manually:

```bash
# Configuration
php artisan vendor:publish --tag=roster-config

# Migrations
php artisan vendor:publish --tag=roster-migrations

# Run migrations
php artisan migrate
```

## 🚀 Quick Start

### 1. Add the trait to your models

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Roster\Traits\HasRoster;

class Doctor extends Model
{
    use HasRoster;
}
```

### 2. Create recurring availabilities

```php
// Create an availability for a doctor
$availability = availability_for($doctor)->create([
    'type' => 'consultation',
    'daily_start' => '09:00:00',
    'daily_end' => '17:00:00',
    'days' => ['monday', 'wednesday', 'friday'],
    'validity_start' => '2038-01-01',
    'validity_end' => '2038-12-31',
]);
```

### 3. Schedule appointments

```php
// Book a slot within this availability
$schedule = schedule_for($availability)->create([
    'title' => 'Annual Consultation - Patient A',
    'start_datetime' => '2038-01-04 10:00:00',
    'end_datetime' => '2038-01-04 11:00:00',
    'status' => \Roster\Enums\ScheduleStatus::BOOKED,
    'metadata' => ['patient_id' => 123],
]);
```

### 4. Manage temporary unavailabilities

```php
// Block a slot for training
$impediment = impediment_for($availability)->create([
    'reason' => 'Mandatory medical training',
    'start_datetime' => '2038-01-04 09:00:00',
    'end_datetime' => '2038-01-04 12:00:00',
]);
```

### 5. Search for available slots

```php
// Find the next available slot
$nextSlot = schedule_for($availability)->findNextSlot(
    durationMinutes: 45,
    type: 'consultation',
    startFrom: now()->addDay()
);

// Check availability for a specific slot
$isAvailable = schedule_for($availability)->isTimeSlotAvailable(
    start: '2038-01-06 14:00:00',
    end: '2038-01-06 15:00:00',
    type: 'consultation'
);
```

## 📖 Core Concepts

### The Immutability Principle

Roster prevents direct model mutations to ensure data integrity. All operations must go through the appropriate services:

```php
// ❌ FORBIDDEN: Direct modification
$availability->update(['daily_end' => '18:00:00']); // Throws an exception

// ✅ ALLOWED: Via the service
availability_for($doctor)->update($availability->id, [
    'daily_end' => '18:00:00'
]);
```

### Single Context Per Action

Each service is designed for a single action with its own context:

```php
// ❌ FORBIDDEN: Service reuse
$service = availability_for($doctor);
$service->create([...]);
$service->update(1, [...]); // Corrupted context

// ✅ ALLOWED: New context for each action
availability_for($doctor)->create([...]);
availability_for($doctor)->update(1, [...]);
```

### The 3 Main Entities

1. **Availability**: Defines when a resource is available (days, hours, period)
2. **Schedule**: Represents a booked slot within an availability
3. **Impediment**: Temporarily blocks an availability

## 🛡️ Secure Architecture

### Mutation Access Control

The system uses two contexts to control access:

```php
// 1. Mutation context (internal)
// Used by repositories to authorize CRUD operations
RosterMutationContext::allow(function () {
    return Availability::create([...]); // Authorized in this context
});

// 2. Service context (public)
// Used by helpers to authorize service usage
RosterServiceContext::allowViaHelper(function () {
    return $service->create([...]); // Authorized via helper
});
```

### Secure Helpers

The `availability_for()`, `schedule_for()`, and `impediment_for()` helpers automatically create the necessary context:

```php
// These helpers automatically handle:
// 1. Execution context creation
// 2. Schedulable entity validation
// 3. Prevention of reuse
```

## 🎯 Exhaustive Business Validation

Roster includes **17 validation rules** that guarantee system consistency:

### Main Rules:
- **SchedulableValidationRule** (110) - Verifies schedulable context is present
- **RequiredFieldsRule** (100) - Validates required fields per operation
- **AvailabilityTemporalCoherenceRule** (100) - Ensures temporal coherence
- **TemporalConflictRule** (80) - Prevents schedule overlaps
- **AvailabilityOverlapRule** (80) - Prevents availability overlaps
- **TimeRangeRule** (85) - Validates time ranges (no multi-day spans)

### Rule Visualization:

```bash
# List all available rules
php artisan roster:debug-rules

# View rules for a specific entity
php artisan roster:debug-rules availability --operation=create
```

## 📊 Real-World Usage Examples

### Medical Clinic Management

```php
// Creating availabilities for different specialists
$cardiologist = Doctor::where('specialty', 'cardiology')->first();
$availability = availability_for($cardiologist)->create([
    'type' => 'consultation',
    'daily_start' => '08:30:00',
    'daily_end' => '12:30:00',
    'days' => ['monday', 'wednesday', 'friday'],
    'validity_start' => '2024-01-01',
    'validity_end' => '2024-12-31',
]);

// Patient booking
$appointment = schedule_for($availability)->create([
    'title' => 'Cardiac consultation',
    'start_datetime' => '2024-06-10 10:00:00',
    'end_datetime' => '2024-06-10 11:00:00',
    'status' => ScheduleStatus::BOOKED,
    'metadata' => [
        'patient_id' => 'CARD001',
        'priority' => 'medium',
        'tests_required' => ['echocardiogram', 'stress_test']
    ],
]);

// Managing unavailability (training)
impediment_for($availability)->create([
    'reason' => 'Continuing education',
    'start_datetime' => '2024-06-15 09:00:00',
    'end_datetime' => '2024-06-15 12:00:00',
    'metadata' => ['mandatory' => true, 'location' => 'Auditorium'],
]);
```

### Room Booking System

```php
// Two doctors sharing a room
$room = Room::find(1);

// First doctor uses the room on Monday
$doctor1Availability = availability_for($doctor1)->create([
    'type' => 'room_a',
    'daily_start' => '09:00:00',
    'daily_end' => '17:00:00',
    'days' => ['monday', 'wednesday', 'friday'],
    'validity_start' => '2024-01-01',
    'validity_end' => '2024-12-31',
]);

// Second doctor uses the room on Tuesday
$doctor2Availability = availability_for($doctor2)->create([
    'type' => 'room_a',
    'daily_start' => '09:00:00',
    'daily_end' => '17:00:00',
    'days' => ['tuesday', 'thursday'],
    'validity_start' => '2024-01-01',
    'validity_end' => '2024-12-31',
]);

// The system automatically prevents conflicts
schedule_for($doctor1Availability)->create([
    'title' => 'Room A usage - Dr. Smith',
    'start_datetime' => '2024-06-10 10:00:00', // Monday
    'end_datetime' => '2024-06-10 12:00:00',
]);

// ❌ This booking will fail (inter-doctor conflict)
schedule_for($doctor2Availability)->create([
    'title' => 'Room A usage - Dr. Jones',
    'start_datetime' => '2024-06-10 11:00:00', // Same day as Dr. Smith
    'end_datetime' => '2024-06-10 13:00:00',
]);
```

### Recurring Impediments Management

```php
// Creating a weekly availability
$weeklyAvailability = availability_for($doctor)->create([
    'type' => 'consultation',
    'daily_start' => '08:00:00',
    'daily_end' => '18:00:00',
    'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
    'validity_start' => '2024-01-01',
    'validity_end' => '2024-12-31',
]);

// Recurring impediments (lunch break)
$weekdays = ['2024-01-08', '2024-01-09', '2024-01-10', '2024-01-11', '2024-01-12'];

foreach ($weekdays as $weekday) {
    impediment_for($weeklyAvailability)->create([
        'reason' => 'Lunch break',
        'start_datetime' => Carbon::parse($weekday)->setTime(12, 0, 0),
        'end_datetime' => Carbon::parse($weekday)->setTime(13, 0, 0),
        'metadata' => ['type' => 'lunch', 'recurring' => true],
    ]);
}

// Finding available slots despite impediments
$availableSlots = schedule_for($weeklyAvailability)->findAvailableSlots(
    startDate: '2024-01-08',
    endDate: '2024-01-12',
    durationMinutes: 60,
    type: 'consultation'
);
```

## 🔧 Complete API

### Availability Service

```php
// CRUD
availability_for($schedulable)->create($data);
availability_for($schedulable)->find($id);
availability_for($schedulable)->update($id, $data);
availability_for($schedulable)->delete($id);

// Search
availability_for($schedulable)->all();
availability_for($schedulable)->setFilter('type', 'consultation')->all();

// Checks
availability_for($schedulable)->isAvailableOnDate($date, $type);
availability_for($schedulable)->getAvailabilityForTimeSlot($start, $end, $type);
```

### Schedule Service

```php
// Booking
schedule_for($availability)->create($data);
schedule_for($availability)->update($id, $data);
schedule_for($availability)->delete($id);

// Slot search
schedule_for($availability)->findNextSlot($durationMinutes, $type, $startFrom);
schedule_for($availability)->findAvailableSlots($startDate, $endDate, $durationMinutes, $type);

// Checks
schedule_for($availability)->isTimeSlotAvailable($start, $end, $type);
schedule_for($availability)->isPeriodAvailable($start, $end, $type);
```

### Impediment Service

```php
// Impediment management
impediment_for($availability)->create($data);
impediment_for($availability)->update($id, $data);
impediment_for($availability)->delete($id);

// Checks
impediment_for($availability)->isTimeSlotBlocked($start, $end);
impediment_for($availability)->getAvailableTimeSlots($start, $end, $type);
```

## ⚙️ Configuration

### Configuration file (`config/roster.php`)

```php
return [
    // Allowed activity types
    'allowed_types' => [
        'consultation',
        'surgery',
        'emergency',
        'training',
        'room_a',
        'echography',
        'scan',
    ],

    // Minimum durations (in minutes)
    'durations' => [
        'minimum_availability_minutes' => 15,
        'minimum_schedule_minutes' => 15,
        'minimum_impediment_minutes' => 5,
        'max_search_period_days' => 365,
        'max_availability_days' => 365,
    ],

    // Validation rules cache
    'cache' => [
        'enabled' => env('ROSTER_CACHE_ENABLED', true),
        'cache_file' => storage_path('framework/cache/roster_rules.php'),
        'cache_max_age_hours' => 24,
    ],
];
```

### Environment variables

```env
ROSTER_TIMEZONE=Europe/Paris
ROSTER_CACHE_ENABLED=true
```

## 🧪 Comprehensive Testing

The package includes **760 tests** covering all scenarios:

```bash
# Run all tests
php artisan test

# Integration tests
php artisan test --group=integration

# Performance tests
php artisan test --filter=test_performance_and_load_scenario

# Complex scenario tests
php artisan test --filter=test_real_world_complex_scenario
```

### Tested scenarios:
- ✅ Complete availability lifecycle
- ✅ Impediment management with conflicts
- ✅ Intelligent booking system
- ✅ Complex interactions (availabilities + impediments + schedules)
- ✅ Multi-user conflicts with shared resources
- ✅ Error handling and edge cases
- ✅ Performance testing with massive data
- ✅ Recovery from errors
- ✅ Realistic complex scenario (hospital with multiple specialists)

## 🚨 Error Management

```php
use Roster\Validation\Exceptions\ValidationFailedException;

try {
    $schedule = schedule_for($availability)->create($data);
} catch (ValidationFailedException $e) {
    // Get detailed violations with rule information
    $violations = $e->getViolations();
    // Array of ViolationData objects containing:
    // - field name
    // - error message
    // - rule that triggered the violation
    // - rule description for context

    $detailedReport = $e->toDetailedArray();
    // Includes rule descriptions for better debugging

    return response()->json([
        'error' => 'validation_failed',
        'message' => $e->getFormattedMessage(),
        'violations' => $detailedReport['violations'],
    ], 422);
}
```

## 📊 Development Tools

### Validation Rule Debugging

```bash
# Display all rules
php artisan roster:debug-rules

# Filter by entity
php artisan roster:debug-rules availability

# Filter by operation
php artisan roster:debug-rules availability --operation=create

# Display methods
php artisan roster:debug-rules availability --show-methods

# Display sources
php artisan roster:debug-rules availability --show-source
```

### Cache Management

```bash
# Generate rules cache
php artisan roster:cache-rules

# Display cache stats
php artisan roster:cache-rules --show

# Clear cache
php artisan roster:cache-rules --clear

# Force regeneration
php artisan roster:cache-rules --force
```

## 🤝 Contribution

1. **Fork** the repository
2. **Create a branch** (`git checkout -b feature/amazing-feature`)
3. **Commit your changes** (`git commit -m 'Add amazing feature'`)
4. **Push to the branch** (`git push origin feature/amazing-feature`)
5. **Open a Pull Request**

### Run Tests

```bash
# All tests
composer test

# With code coverage
composer test-coverage

# Check code style
composer lint
```

## 📄 License

This package is open-source and available under the [MIT](LICENSE) license.

## 🔗 Useful Links

- [API Documentation](docs/api.md)
- [Migration Guide](docs/migration.md)
- [Changelog](CHANGELOG.md)
- [Issues](https://github.com/vendor/laravel-roster/issues)

---

**Roster** - A professional solution for advanced schedule management, designed for critical applications where every minute counts. ⚕️⏰✨