# Makefile pour automatiser git commit, push, tags, et tests

# Commit & push tous les changements
git-commit-push:  # Commit & push tous les changements
	@read -p "Enter commit message: " msg; \
	if [ -z "$$msg" ]; then \
		echo "Commit message cannot be empty!"; \
		exit 1; \
	fi; \
	git add .; \
	git commit -m "$$msg"; \
	git push

# Gestion des tags: major, minor, patch
git-tag:  # Gestion des tags: major, minor, patch
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

# Republier le dernier tag
git-tag-republish:  # Republie le dernier tag sur l'origine
	@bash -c '\
	last_tag=$$(git tag --sort=-v:refname | head -n 1); \
	if [ -z "$$last_tag" ]; then echo "No tags found!"; exit 1; fi; \
	echo "Republishing last tag: $$last_tag"; \
	git push origin "$$last_tag" --force; \
	echo "Tag $$last_tag republished"; \
	'

# Exécute PHPUnit
test:  # Exécute les tests PHPUnit
	@vendor/bin/phpunit

# Concatène tout le code PHP de src dans all.php
concat-src:  # Parcourt src/ et écrit tout le contenu PHP dans all.php
	@echo "🔹 Concaténation de tous les fichiers PHP de src/ dans all.php..."
	@find src -type f -name "*.php" -exec sh -c 'echo "\n\n// ==== {} ====\n\n"; cat {}' \; > all.php
	@echo "✅ Fichier all.php généré avec succès."

# Affiche l'aide et les descriptions
help:  # Affiche l'aide
	@echo "📖 Makefile commands:"; \
	awk '/^#/{desc=$$0}/^[a-zA-Z0-9_-]+:/{gsub(":", "", $$1); gsub(/^# /, "", desc); printf "%-20s -> %s\n", $$1, desc}' $(MAKEFILE_LIST) | sort
