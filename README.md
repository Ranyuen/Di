Ranyuen/Di
==
Simple DI (Dependency Injector) extending Pimple.

Example
--
```php
class Momonga { }

$container = new \Ranyuen\Container;
$container->bind('Momonga', 'momonga', function ($c) { return new Momonga; });

class Yuraru
{
    /** @Inject * /
    public function __construct(Momonga $momonga) { }
}

$yuraru = $container->newInstance('Yuraru');

class Gardea
{
    /**
     * @Inject
     * @var Momonga
     * /
    public $momonga;
}

$gardea = $container->newInstance();

$gardea = new Gardea;
$container->inject($gardea);
 ```
