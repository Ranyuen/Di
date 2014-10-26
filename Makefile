test:
	find lib/ -name "*.php" -exec php -l {} > /dev/null \;
	find tests/ -name "*.php" -exec php -l {} > /dev/null \;
	vendor/bin/phpunit
