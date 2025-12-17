# Changelog

Toutes les modifications notables de ce projet seront documentées dans ce fichier.
Le format est basé sur [Keep a Changelog](https://keepachangelog.com/fr/1.0.0/),
et ce projet suit les principes de [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.0.7] - 2025-12-17

### 🚀 Nouveautés

#### ⚡️ Services Complets
- **Nouveau `ScheduleService`** : Service complet de gestion des rendez-vous avec validation des disponibilités
- **Nouveau `ImpedimentService`** : Service de gestion des blocages temporels avec suppression automatique des conflits
- **API fluide** : Méthodes chaînables avec `for()`, `whereType()`, `between()`, etc.
- **Recherche intelligente** : `findNextAvailableSlot()`, `findAvailableSlots()`, `isTimeSlotAvailable()`

#### 🏗️ Architecture Redesignée
- **Hiérarchie claire** : `Schedulable → Availability → (Schedule|Impediment)`
- **Relations optimisées** : Suppression du polymorphisme direct sur Schedule
- **Validation centralisée** : Logique de validation dans les modèles Eloquent

#### 🛡️ Validation Robuste
- **Chevauchements automatiques** : Empêche les conflits entre schedules
- **Contraintes temporelles** : Respect strict des heures de disponibilité
- **Suppression intelligente** : Les impediments suppriment automatiquement les schedules qui chevauchent

### 💥 Changements Cassants

#### 🗄️ Modifications de Base de Données
```sql
-- AVANT
schedules {
  id, schedulable_id, schedulable_type, start_date, end_date, ...
}

-- APRÈS
schedules {
  id, availability_id, start_datetime, end_datetime, ...
}

-- NOUVELLE TABLE
impediments {
  id, availability_id, reason, start_datetime, end_datetime, metadata
}
```

#### 🔄 Changements d'API
```php
// AVANT (si existait)
$user->schedules()->create([...]);

// APRÈS
Schedule::for($user)->create([
    'title' => 'Consultation',
    'start_datetime' => $start,
    'end_datetime' => $end,
    'status' => 'available'
]);
```

#### 🏷️ Relations Modifiées
- Les `Schedule` n'ont plus de relation morphique directe avec le `Schedulable`
- Accès indirect via `$schedule->schedulable()` (via l'`Availability`)
- Les `Impediment` sont liés aux `Availability`, pas directement au `Schedulable`

### 📈 Améliorations

#### 🎯 Meilleure Sémantique
- **Terminologie précise** : Schedule (rendez-vous) vs Impediment (blocage)
- **Hiérarchie logique** : Disponibilité → Événements/Blocages
- **API intuitive** : Méthodes nommées de manière cohérente

#### 🧪 Couverture de Tests Étendue
- **60+ tests** couvrant tous les cas d'usage
- **Tests d'intégration** entre modèles et services
- **Scénarios edge cases** : chevauchements, validations, conflits

#### 🔌 Architecture Extensible
- **Design pattern Service** pour une logique métier centralisée
- **Facades dédiées** pour une API propre
- **Hooks Eloquent** pour une validation automatique

### 🛠️ Migration Required

#### 1. Mise à Jour de la Base de Données
```bash
# 1. Exécuter les nouvelles migrations
php artisan migrate

# 2. Migrer les données existantes (si nécessaire)
# Les schedules existants doivent être associés à une availability
```

#### 2. Mise à Jour du Code
```php
// AVANT (si vous aviez du code existant)
$schedule = new Schedule([
    'schedulable_id' => $user->id,
    'schedulable_type' => get_class($user),
    // ...
]);

// APRÈS - Utiliser le service
$schedule = Schedule::for($user)->create([
    'title' => '...',
    'start_datetime' => '...',
    'end_datetime' => '...',
]);

// Pour les impediments (nouveau)
$impediment = Impediment::for($user)->create([
    'reason' => 'Réunion',
    'start_datetime' => '...',
    'end_datetime' => '...',
]);
```

#### 3. Vérification des Relations
```php
// AVANT
$userSchedules = $user->schedules;

// APRÈS
$userSchedules = Schedule::for($user)->all();
// OU via le trait HasRoster
$userSchedules = $user->availabilities->flatMap->schedules;
```

### 📋 API Nouvelle

#### 🗓️ ScheduleService
```php
// Création avec validation
Schedule::for($user)->create([...]);

// Recherche de créneaux
$nextSlot = Schedule::for($user)->findNextAvailableSlot(60);
$slots = Schedule::for($user)->findAvailableSlots($start, $end, 30);

// Vérification
$available = Schedule::for($user)->isTimeSlotAvailable($start, $end);

// Filtrage
$schedules = Schedule::for($user)
    ->whereType('consultation')
    ->between($startDate, $endDate)
    ->get();
```

#### ⛔ ImpedimentService
```php
// Création (supprime automatiquement les schedules en conflit)
Impediment::for($user)->create([...]);

// Vérification de blocage
$blocked = Impediment::for($user)->isTimeSlotBlocked($start, $end);

// Gestion
$impediments = Impediment::for($user)
    ->whereType('meeting')
    ->between($startDate, $endDate)
    ->get();
```

### 🎯 Points Clés de la Version 0.0.7

1. **🚀 Performance** : Relations optimisées, moins de requêtes
2. **🛡️ Fiabilité** : Validation automatique des contraintes métier
3. **🧩 Modularité** : Services indépendants et réutilisables
4. **📚 Documentation** : API claire et cohérente
5. **🧪 Qualité** : Tests complets couvrant tous les scénarios

---

## [0.0.5] - 2025-XX-XX

### 🔄 Renommage
- **Renommage du trait** : `Schedulable` → `HasRoster`
- **Meilleure sémantique** : Un modèle "possède un roster" plutôt qu'il est "schedulable"

### 📝 Notes
- Changement purement sémantique, pas de modification fonctionnelle
- Impacte uniquement les modèles utilisant le trait

---

## Migration Summary

| Version | Changement Principal | Impact |
|---------|---------------------|--------|
| 0.0.5 | Renommage `Schedulable` → `HasRoster` | Faible, changement de nom seulement |
| 0.0.7 | **Refonte complète** : Architecture à 3 niveaux, nouveaux services | **Élevé**, nécessite migration des données et du code |

### 📊 Impact sur le Code Existant

| Composant | Impact 0.0.5 | Impact 0.0.7 |
|-----------|--------------|--------------|
| Modèles avec le trait | 🟡 Modéré (renommage) | 🟢 Aucun (toujours compatible) |
| Relations schedules | 🟢 Aucun | 🔴 Élevé (changement de structure) |
| Création de schedules | 🟢 Aucun | 🔴 Élevé (nouvelle API) |
| Requêtes existantes | 🟢 Aucun | 🟡 Modéré (nouvelle relation indirecte) |

**Légende** : 🟢 Aucun · 🟡 Modéré · 🔴 Élevé

### 🚨 Actions Requises pour 0.0.7

1. **Migration de la base de données** (nécessite script de migration des données)
2. **Mise à jour du code d'appel** (remplacement de l'API directe par les services)
3. **Tests de régression** (vérifier que les fonctionnalités existantes marchent)
4. **Formation** (nouvelle API à apprendre pour l'équipe)
