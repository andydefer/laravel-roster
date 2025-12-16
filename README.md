# Laravel Roster

A lightweight and predictable scheduling engine for Laravel models.

Laravel Roster is an open‑source package designed to handle **availabilities, bookings, conflicts and time slots** in a clean and Laravel‑native way. It focuses on clarity, simplicity and real‑world scheduling use cases such as teams, shifts, rooms and appointments.

---

## ✨ Features

* Attach schedules to **any Eloquent model**
* Define **availabilities**, **blocks** and **bookings**
* Generate **bookable time slots** with duration and buffers
* Prevent **conflicting schedules** by design
* Fluent, expressive and readable API
* Lightweight core with no UI assumptions
* Built for Laravel 10+

---

## 📦 Installation

Install the package via Composer:

```bash
composer require andydefer/roster
```

Publish the migrations:

```bash
php artisan vendor:publish --tag="roster-migrations"
```

Run the migrations:

```bash
php artisan migrate
```

---

## 🧩 Basic Concept

Laravel Roster works by attaching **schedules** to your existing models (users, doctors, rooms, employees, etc.).

Each model can have multiple schedules such as:

* working hours
* blocked periods
* reservations

Roster then answers questions like:

* *Is this time range available?*
* *Which slots can be booked on this date?*

---

## 🧠 Usage

### 1️⃣ Add the `HasRoster` trait

Add the trait to any model you want to make schedulable:

```php
use Roster\Traits\HasRoster;

class Doctor extends Model
{
    use HasRoster;
}
```

---

### 2️⃣ Define availabilities

```php
use Roster\Facades\Roster;

Roster::for($doctor)
    ->named('Office Hours')
    ->availability()
    ->weekly(['monday', 'tuesday', 'wednesday', 'thursday', 'friday'])
    ->from('09:00')
    ->to('17:00')
    ->save();
```

---

### 3️⃣ Generate bookable slots

```php
$slots = $doctor->getRosterSlots(
    date: '2025-01-15',
    duration: 60,
    buffer: 15
);
```

---

### 4️⃣ Check availability

```php
$isAvailable = $doctor->isBookableAtTime(
    date: '2025-01-15',
    start: '15:00',
    end: '16:00'
);
```

---

## ⚠️ Conflict Handling

Laravel Roster keeps scheduling rules in one place:

* Availabilities define *when something can happen*
* Blocks and bookings define *when it cannot*
* Conflicts are resolved automatically by the engine

This ensures predictable and testable scheduling logic.

---

## 🧪 Testing

Run the test suite:

```bash
php artisan test
```

Time‑based logic is heavily tested to prevent edge‑case bugs.

---

## 🗺️ Roadmap

* [ ] MVP scheduling engine
* [ ] Conflict rules & overlap policies
* [ ] Timezone support
* [ ] Recurring exceptions
* [ ] Performance optimizations
* [ ] v1.0 stable release

---

## 🤝 Contributing

Contributions are welcome!

* Fork the repository
* Create a feature branch
* Add tests for your changes
* Submit a pull request

Please keep the API clean and predictable.

---

## 📄 License

Laravel Roster is open‑source software licensed under the **MIT license**.

---

## ❤️ Philosophy

Laravel Roster is intentionally focused.

It does not try to be a full calendar UI or booking platform. Instead, it provides a **reliable scheduling core** you can build on top of — with Livewire, Inertia, APIs or any frontend you choose.
