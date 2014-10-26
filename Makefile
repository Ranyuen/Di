tests:
	find lib/ -name "*.php" -exec php -l {} \;
	find test/ -name "*.php" -exec php -l {} \;
	vendor/bin/phpunit test
