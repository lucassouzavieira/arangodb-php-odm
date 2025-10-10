ARANGODB_VERSION="3.12.5.2"
env-up:
	cp .env.testing .env
	echo -e "\nARANGODB_VERSION=$(ARANGODB_VERSION)" >> .env
	docker compose -f tests/compose.yaml up -d

env-down:
	docker compose -f tests/compose.yaml down -v
	rm .env

unittests:
	vendor/bin/phpunit -c phpunit.xml

format:
	vendor/bin/php-cs-fixer fix src/ --rules=@PSR1,@PSR2,@PSR12 && vendor/bin/php-cs-fixer fix tests/ --rules=@PSR1,@PSR2,@PSR12

analyze:
	vendor/bin/phpstan --memory-limit=256M analyze src/
