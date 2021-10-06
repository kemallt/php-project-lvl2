install:
	composer install
lint:
	composer exec --verbose phpcs -- --standard=PSR12 src
test:
	./vendor/bin/phpunit tests