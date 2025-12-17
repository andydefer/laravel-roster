# ---------------------------------------------------
# Variables
# ---------------------------------------------------
PINT = ./vendor/bin/pint
RESOURCES_DIR = resources
PHPSTAN = ./vendor/bin/phpstan
RECTOR = ./vendor/bin/rector
# ARTISAN = php artisan  # Pas utilisé dans un package

# ---------------------------------------------------
# Commit & push tous les changements
# ---------------------------------------------------
# Commit & push tous les changements
git-commit-push:
	@read -p "Enter commit message: " msg; \
	if [ -z "$$msg" ]; then \
		echo "Commit message cannot be empty!"; \
		exit 1; \
	fi; \
	git add .; \
	git commit -m "$$msg"; \
	git push

# ---------------------------------------------------
# Gestion des tags: major, minor, patch
# ---------------------------------------------------
# Gestion des tags: major, minor, patch
git-tag:
	@bash -c '\
	read -p "Tag type (major/minor/patch): " type; \
	last_tag=$$(git tag --sort=-v:refname | head -n 1); \
	if [ -z "$$last_tag" ]; then last_tag="0.0.0"; fi; \
	major=$$(echo $$last_tag | cut -d. -f1); \
	minor=$$(echo $$last_tag | cut -d. -f2); \
	patch=$$(echo $$last_tag | cut -d. -f3); \
	if [ "$$type" = "major" ]; then \
		major=$$((major + 1)); minor=0; patch=0; \
	elif [ "$$type" = "minor" ]; then \
		minor=$$((minor + 1)); patch=0; \
	elif [ "$$type" = "patch" ]; then \
		patch=$$((patch + 1)); \
	else echo "Invalid type: $$type"; exit 1; fi; \
	new_tag="$$major.$$minor.$$patch"; \
	git tag -a "$$new_tag" -m "Release $$new_tag"; \
	git push origin "$$new_tag"; \
	echo "Pushed new tag: $$new_tag"; \
	'

# ---------------------------------------------------
# Republier le dernier tag
# ---------------------------------------------------
# Republie le dernier tag sur l'origine
git-tag-republish:
	@bash -c '\
	last_tag=$$(git tag --sort=-v:refname | head -n 1); \
	if [ -z "$$last_tag" ]; then echo "No tags found!"; exit 1; fi; \
	echo "Republishing last tag: $$last_tag"; \
	git push origin "$$last_tag" --force; \
	echo "Tag $$last_tag republished"; \
	'

# ---------------------------------------------------
# Exécute PHPUnit
# ---------------------------------------------------
# Exécute les tests PHPUnit
test:
	@vendor/bin/phpunit

# ---------------------------------------------------
# Concatène tout le code PHP de src dans all.php
# ---------------------------------------------------
# Parcourt src/, tests/ et database/ et écrit tout le contenu PHP dans all.php
concat-all:
	@echo "🔹 Concaténation de tous les fichiers PHP de src/, tests/ et database/ dans all.php..."
	@find src tests database -type f -name "*.php" -exec sh -c 'echo "\n\n// ==== {} ====\n\n"; cat {}' \; > all.php
	@echo "✅ Fichier all.php généré avec succès."

# ---------------------------------------------------
# Linters & Formatters (prefix: lint-)
# ---------------------------------------------------
# Lint PHP avec Pint
lint-php:
	@$(PINT)

# Fix PHP avec Pint
lint-php-fix:
	@$(PINT) --test

# Lint PHP avec PHPStan
lint-phpstan:
	@clear && $(PHPSTAN) analyse src tests --level=max

# Applique Rector pour refactorer le code
lint-rector:
	@$(RECTOR) process

# Exécute tous les linters
lint-all:
	@make lint-php lint-phpstan

# Exécute tous les fixers
lint-all-fix:
	@make lint-php-fix lint-rector

# ---------------------------------------------------
# Affiche l'aide
# ---------------------------------------------------
# Affiche l'aide
help:
	@echo "📖 Makefile commands:"; \
	awk '/^#/{desc=$$0}/^[a-zA-Z0-9_-]+:/{gsub(":", "", $$1); gsub(/^# /, "", desc); printf "%-20s -> %s\n", $$1, desc}' $(MAKEFILE_LIST) | sort
