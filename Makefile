test:
	find lib/ -name "*.php" -exec php -l {} > /dev/null \;
	find tests/ -name "*.php" -exec php -l {} > /dev/null \;
	vendor/bin/php-cs-fixer fix lib/
	vendor/bin/php-cs-fixer fix tests/
	vendor/bin/phpmd lib/ text phpmd.xml
	vendor/bin/phpcs --standard=phpcs.xml --extensions=php lib/
	vendor/bin/phpunit
