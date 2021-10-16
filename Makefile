install:
	composer install
lint:
	composer exec --verbose phpcs -- --standard=PSR12 src
linttests:
	composer exec --verbose phpcs -- --standard=PSR12 src
test:
	composer exec phpunit tests
test-coverage:
	composer exec --verbose phpunit tests -- --coverage-clover build/logs/clover.xml