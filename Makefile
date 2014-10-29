test:
	find lib/ -name "*.php" -exec php -l {} > /dev/null \;
	find tests/ -name "*.php" -exec php -l {} > /dev/null \;
	vendor/bin/php-cs-fixer fix lib/ --level=psr2
	vendor/bin/php-cs-fixer fix tests/ --level=psr2
	vendor/bin/phpmd lib/ text codesize,design,naming,unusedcode
	vendor/bin/phpcs --standard=Zend,PEAR --extensions=php lib/
	vendor/bin/phpunit
