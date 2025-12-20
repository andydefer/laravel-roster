# ===================================================
# PHP/Laravel Package Development Makefile
# ===================================================
# This Makefile provides utilities for package development,
# including code quality checks, version management, and file tracking.
# ===================================================


# ---------------------------------------------------
# Tool Executables
# ---------------------------------------------------
PINT = ./vendor/bin/pint
PHPSTAN = ./vendor/bin/phpstan
RECTOR = ./vendor/bin/rector
PSALM = ./vendor/bin/psalm


# ---------------------------------------------------
# Source Configuration
# ---------------------------------------------------
SOURCE_DIRS = config src database routes tests
IGNORED_FILES = CHANGED_FILES.md FILES_CHECKLIST.md psalm.md Makefile


# ---------------------------------------------------
# Version Control Operations
# ---------------------------------------------------

.PHONY: git-commit-push
git-commit-push:
	@read -p "Enter commit message: " commit_message; \
	if [ -z "$$commit_message" ]; then \
		echo "Error: Commit message cannot be empty"; \
		exit 1; \
	fi; \
	git add .; \
	git commit -m "$$commit_message"; \
	git push

.PHONY: git-tag
git-tag:
	@bash -c '\
	read -p "Tag type (major/minor/patch): " tag_type; \
	last_tag=$$(git tag --sort=-v:refname | head -n 1); \
	if [ -z "$$last_tag" ]; then last_tag="0.0.0"; fi; \
	major=$$(echo $$last_tag | cut -d. -f1); \
	minor=$$(echo $$last_tag | cut -d. -f2); \
	patch=$$(echo $$last_tag | cut -d. -f3); \
	if [ "$$tag_type" = "major" ]; then \
		major=$$((major + 1)); minor=0; patch=0; \
	elif [ "$$tag_type" = "minor" ]; then \
		minor=$$((minor + 1)); patch=0; \
	elif [ "$$tag_type" = "patch" ]; then \
		patch=$$((patch + 1)); \
	else echo "Invalid type: $$tag_type"; exit 1; fi; \
	new_tag="$$major.$$minor.$$patch"; \
	git tag -a "$$new_tag" -m "Release $$new_tag"; \
	git push origin "$$new_tag"; \
	echo "Released new tag: $$new_tag"; \
	'

.PHONY: generate-ai-diff
generate-ai-diff:
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

.PHONY: git-tag-republish
git-tag-republish:
	@bash -c '\
	last_tag=$$(git tag --sort=-v:refname | head -n 1); \
	if [ -z "$$last_tag" ]; then echo "No tags found!"; exit 1; fi; \
	echo "Republishing last tag: $$last_tag"; \
	git push origin "$$last_tag" --force; \
	echo "Tag $$last_tag republished"; \
	'


# ---------------------------------------------------
# File Management Operations
# ---------------------------------------------------

.PHONY: update-checklist
update-checklist:
	@echo "Updating FILES_CHECKLIST.md..."
	@if [ -f FILES_CHECKLIST.md ]; then \
		echo "Preserving existing checklist with checkmarks..."; \
		grep -E '^[0-9]+\. .* \[[ xX]\]$$' FILES_CHECKLIST.md > .existing_checklist.tmp; \
		awk -F' ' '{ \
			file_path=""; \
			for(i=2;i<NF;i++) { \
				if(i>2) file_path=file_path" "; \
				file_path=file_path$$i; \
			} \
			checkmark_state=$$NF; \
			print file_path " " checkmark_state \
		}' .existing_checklist.tmp > .existing_files.tmp; \
	else \
		touch .existing_files.tmp; \
		touch FILES_CHECKLIST.md; \
	fi; \
	echo "# Project File Checklist" > FILES_CHECKLIST.md; \
	echo "*Last updated: $$(date)*" >> FILES_CHECKLIST.md; \
	echo "" >> FILES_CHECKLIST.md; \
	echo "## Previously Checked Files" >> FILES_CHECKLIST.md; \
	file_count=1; \
	grep '\[x\]' .existing_files.tmp | sort | uniq | while read -r line; do \
		file_path=$$(echo "$$line" | awk '{$$NF=""; print $$0}' | sed 's/ $$//'); \
		echo "$$file_count. $$file_path [x]" >> FILES_CHECKLIST.md; \
		file_count=$$((file_count + 1)); \
	done; \
	previously_checked_files=$$(grep '\[x\]' .existing_files.tmp | awk '{$$NF=""; print $$0}' | sed 's/ $$//'); \
	echo "" >> FILES_CHECKLIST.md; \
	echo "## Other Files" >> FILES_CHECKLIST.md; \
	file_count=1; \
	find $(SOURCE_DIRS) -type f | sort | while read -r file_path; do \
		if ! echo "$$previously_checked_files" | grep -Fxq "$$file_path" 2>/dev/null; then \
			echo "$$file_count. $$file_path [ ]" >> FILES_CHECKLIST.md; \
			file_count=$$((file_count + 1)); \
		fi; \
	done; \
	rm -f .existing_checklist.tmp .existing_files.tmp; \
	echo "FILES_CHECKLIST.md updated successfully (states preserved, duplicates avoided)"

.PHONY: list-modified-files
list-modified-files:
	@echo "Updating CHANGED_FILES.md..."
	@previously_checked_files=$$(grep -E '^[0-9]+\. .* \[[xX]\]' FILES_CHECKLIST.md | sed 's/^[0-9]\+\. //' | sed 's/ *\[[xX]\]$$//'); \
	modified_file_count=0; \
	all_files=$$( (git diff --name-only; git ls-files --others --exclude-standard) | sort -u ); \
	echo "# Changed and Untracked Files" > CHANGED_FILES.md; \
	echo "*Updated: $$(date)*" >> CHANGED_FILES.md; \
	echo "" >> CHANGED_FILES.md; \
	echo "## Files to Review (modifications on checked files)" >> CHANGED_FILES.md; \
	for file_path in $$all_files; do \
		if echo "$$previously_checked_files" | grep -Fxq "$$file_path"; then \
			modified_file_count=$$((modified_file_count + 1)); \
			echo "$$modified_file_count. $$file_path [x]" >> CHANGED_FILES.md; \
		fi; \
	done; \
	if [ $$modified_file_count -eq 0 ]; then \
		echo "*(No modified files in this category)*" >> CHANGED_FILES.md; \
	fi; \
	echo "" >> CHANGED_FILES.md; \
	echo "## Other Modified Files" >> CHANGED_FILES.md; \
	modified_file_count=0; \
	for file_path in $$all_files; do \
		should_skip_file=0; \
		for ignored_file in $$(echo -e "$(IGNORED_FILES)"); do \
			if [ "$$file_path" = "$$ignored_file" ]; then should_skip_file=1; break; fi; \
		done; \
		if [ $$should_skip_file -eq 0 ] && ! echo "$$previously_checked_files" | grep -Fxq "$$file_path"; then \
			modified_file_count=$$((modified_file_count + 1)); \
			echo "$$modified_file_count. $$file_path [ ]" >> CHANGED_FILES.md; \
		fi; \
	done; \
	if [ $$modified_file_count -eq 0 ]; then \
		echo "*(No modified files in this category)*" >> CHANGED_FILES.md; \
	fi; \
	echo "" >> CHANGED_FILES.md; \
	echo "CHANGED_FILES.md updated successfully"

.PHONY: update-all
update-all: update-checklist list-modified-files
	@echo "All updates completed successfully!"

.PHONY: concat-all
concat-all:
	@echo "Concatenating all PHP files from source directories into all.txt..."
	@find $(SOURCE_DIRS) -type f -name "*.php" -exec sh -c 'echo ""; echo "// ==== {} ==="; echo ""; cat {}' \; > all.txt
	@echo "File all.txt generated successfully."


# ---------------------------------------------------
# Testing
# ---------------------------------------------------

.PHONY: test
test:
	@./vendor/bin/phpunit


# ---------------------------------------------------
# Code Quality Tools
# ---------------------------------------------------

.PHONY: lint-php
lint-php:
	@$(PINT)

.PHONY: lint-php-fix
lint-php-fix:
	@$(PINT) --test

.PHONY: lint-phpstan
lint-phpstan:
	@clear && $(PHPSTAN) analyse src tests --level=max

.PHONY: lint-rector
lint-rector:
	@$(RECTOR) process

.PHONY: lint-psalm
lint-psalm:
	@echo "Running Psalm on all code..."
	@$(PSALM) --show-info=true
	@echo "Psalm analysis completed."

.PHONY: lint-psalm-md
lint-psalm-md:
	@echo "Running Psalm on all code..."
	@echo "# Psalm Analysis Report" > psalm.md
	@$(PSALM) --show-info=true --no-progress >> psalm.md 2>&1 || true
	@echo "Psalm analysis completed. Results in psalm.md"

.PHONY: lint-all
lint-all:
	@make lint-php lint-phpstan lint-psalm

.PHONY: lint-all-fix
lint-all-fix:
	@make lint-php-fix lint-rector


# ---------------------------------------------------
# Release Management Workflow
# ---------------------------------------------------

.PHONY: pre-release
pre-release:
	@echo "Running pre-release checks..."
	@make test
	@make lint-all
	@echo "✅ All pre-release checks passed"

.PHONY: release
release: pre-release
	@echo "Creating release..."
	@make git-tag
	@echo "✅ Release created successfully"

.PHONY: post-release
post-release:
	@echo "Performing post-release cleanup..."
	@make update-all
	@echo "✅ Post-release cleanup completed"


# ---------------------------------------------------
# Help & Documentation
# ---------------------------------------------------
.PHONY: help
help:
	@echo "Commandes disponibles :"
	@echo ""
	@echo "Contrôle de version :"
	@echo "  git-commit-push       Commit et push de tous les changements"
	@echo "  git-tag               Créer et pousser un nouveau tag de version"
	@echo "  generate-ai-diff      Générer un diff propre pour revue par l'IA"
	@echo "  git-tag-republish     Forcer le push du dernier tag"
	@echo ""
	@echo "Gestion des fichiers :"
	@echo "  update-checklist      Mettre à jour la checklist des fichiers"
	@echo "  list-modified-files   Lister les fichiers modifiés"
	@echo "  update-all            Mettre à jour checklist et fichiers modifiés"
	@echo "  concat-all            Concaténer tous les fichiers PHP"
	@echo ""
	@echo "Tests :"
	@echo "  test                  Exécuter les tests PHPUnit"
	@echo ""
	@echo "Qualité du code :"
	@echo "  lint-php              Exécuter le formateur de code Pint"
	@echo "  lint-php-fix          Tester le formatage avec Pint sans appliquer"
	@echo "  lint-phpstan          Exécuter l'analyse statique PHPStan"
	@echo "  lint-rector           Appliquer le refactoring avec Rector"
	@echo "  lint-psalm            Exécuter l'analyse Psalm"
	@echo "  lint-psalm-md         Exécuter Psalm et sauvegarder les résultats en Markdown"
	@echo "  lint-all              Exécuter tous les linters"
	@echo "  lint-all-fix          Exécuter tous les correcteurs"
	@echo ""
	@echo "Gestion des releases :"
	@echo "  pre-release           Exécuter toutes les vérifications avant la release"
	@echo "  release               Créer une nouvelle release (inclut pre-release)"
	@echo "  post-release          Nettoyer après la release"
	@echo ""
	@echo "Aide :"
	@echo "  help                  Afficher ce message d'aide"

# ---------------------------------------------------
# Default Target
# ---------------------------------------------------
.DEFAULT_GOAL := help