# Flow d'utilisation complet de votre package `andydefer/roster`

## 1. Installation depuis Packagist

```bash
# Dans votre projet Laravel
composer require andydefer/roster

# Publier la configuration et les fichiers
php artisan vendor:publish --provider="Andydefer\Roster\RosterServiceProvider"

# Lancer l'installation complète
php artisan roster:install
```