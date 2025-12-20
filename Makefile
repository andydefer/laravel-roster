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
# Génère un diff git dans un fichier diff.txt
# avec un prompt pour analyse IA (commit + résumé)
# ---------------------------------------------------
# Génère un diff git propre dans diff.txt avec prompt pour IA
git-diff:
	@echo "📝 Generating clean git diff into diff.txt..."
	@echo "Tu es un expert en revue de code et en conventions de commits (Conventional Commits)." > diff.txt
	@echo "" >> diff.txt
	@echo "À partir du diff Git ci-dessous, fais les choses suivantes :" >> diff.txt
	@echo "" >> diff.txt
	@echo "1. Propose un nom de commit clair et concis en anglais" >> diff.txt
	@echo "   avec le format <type>(<scope>): <description>," >> diff.txt
	@echo "   en respectant les Conventional Commits" >> diff.txt
	@echo "   (ex: feat:, fix:, refactor:, test:, chore:, docs:)." >> diff.txt
	@echo "" >> diff.txt
	@echo "2. Rédige un résumé du travail effectué en quelques phrases," >> diff.txt
	@echo "   orienté métier et technique." >> diff.txt
	@echo "" >> diff.txt
	@echo "3. Donne une liste d'exemples concrets de changements, en t'appuyant sur le diff :" >> diff.txt
	@echo "   - méthodes ajoutées, modifiées ou supprimées" >> diff.txt
	@echo "   - responsabilités déplacées ou clarifiées" >> diff.txt
	@echo "   - améliorations de validation, de logique ou de structure" >> diff.txt
	@echo "   - impacts fonctionnels éventuels" >> diff.txt
	@echo "" >> diff.txt
	@echo "Contraintes :" >> diff.txt
	@echo "   - Ne décris que ce qui est réellement visible dans le diff" >> diff.txt
	@echo "   - Sois précis, factuel et structuré" >> diff.txt
	@echo "   - Évite les suppositions" >> diff.txt
	@echo "   - Utilise un ton professionnel" >> diff.txt
	@echo "" >> diff.txt
	@echo "Voici le diff :" >> diff.txt
	@echo "" >> diff.txt
	@git diff HEAD -- . ':!*.phpunit.result.cache' ':!diff.txt' >> diff.txt
	@echo "✅ Clean diff.txt generated successfully (excluded test cache files)"

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
# Génère un fichier Markdown listant tous les fichiers
# avec checkbox
# ---------------------------------------------------
# Génère un fichier Markdown listant tous les fichiers avec checkbox
for-clean:
	@echo "📄 Generating FILES_CHECKLIST.md..."
	@echo "# 📂 Project File Checklist\n" > FILES_CHECKLIST.md
	@find config src database routes tests -type f | sort | \
	awk '{ printf "%d. %s [ ]\n", NR, $$0 }' >> FILES_CHECKLIST.md
	@echo "✅ FILES_CHECKLIST.md generated successfully"

# ---------------------------------------------------
# Liste des fichiers modifiés depuis le dernier commit
# ---------------------------------------------------
# Liste des fichiers modifiés depuis le dernier commit
changed-files:
	@echo "📝 Generating list of changed files since last commit..."
	@git diff --name-only HEAD | sort | \
	awk '{ printf "%d. - [ ] %s\n", NR, $$0 }' \
	> CHANGED_FILES.md
	@echo "✅ CHANGED_FILES.md generated"

# ---------------------------------------------------
# Concatène tout le code PHP de src dans all.txt
# ---------------------------------------------------
# Parcourt src/, tests/ et database/ et écrit tout le contenu PHP dans all.txt
concat-all:
	@echo "🔹 Concaténation de tous les fichiers PHP de src/, tests/ et database/ dans all.txt..."
	@find src tests database -type f -name "*.php" -exec sh -c 'echo "\n\n// ==== {} ====\n\n"; cat {}' \; > all.txt
	@echo "✅ Fichier all.txt généré avec succès."

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

