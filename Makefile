.PHONY: up down logs backup restore test

DATE := $(shell date +%F)

up:
	docker compose up -d

down:
	docker compose down

logs:
	docker compose logs -f

backup:
	zip -r data-$(DATE).zip data
	@echo "Sauvegarde : data-$(DATE).zip"

restore:
	@test -n "$(ARCHIVE)" || (echo "Usage : make restore ARCHIVE=data-AAAA-MM-JJ.zip" && exit 1)
	@test -f "$(ARCHIVE)" || (echo "Archive introuvable : $(ARCHIVE)" && exit 1)
	docker compose down
	@if [ -d data ]; then mv data data.avant-restauration-$(shell date +%F-%H%M%S); fi
	unzip -q "$(ARCHIVE)"
	docker compose up -d
	@echo "Restauration terminée depuis $(ARCHIVE)"

test:
	docker compose exec app php artisan test
