Ranyuen/Di
==
Simple DI (Dependency Injector) extending [Pimple](http://pimple.sensiolabs.org/).

_cf._ [fabpot/Pimple](https://github.com/fabpot/Pimple)

_cf._ [koriym/Ray.Di](https://github.com/koriym/Ray.Di)

Example
--
```php
class Momonga { }

$container = new \Ranyuen\Di\Container;
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
