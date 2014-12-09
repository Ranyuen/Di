[![Build Status](https://travis-ci.org/Ranyuen/Di.svg)](https://travis-ci.org/Ranyuen/Di)
[![HHVM Status](http://hhvm.h4cc.de/badge/ranyuen/di.svg)](http://hhvm.h4cc.de/package/ranyuen/di)

Ranyuen/Di
==
Annotation based simple DI (Dependency Injection) & AOP (Aspect Oriented Programming).

_cf._ [fabpot/Pimple](http://pimple.sensiolabs.org/)

_cf._ [koriym/Ray.Di & Ray.Aop](https://code.google.com/p/rayphp/)

_cf._ [mnapoli/PHP-DI](http://php-di.org/)

Features
--
1. Compatible with Pimple 3.
2. Zero configuration. Injection through reflection and annotations. It's easy!
3. AOP support.

Install
--
```sh
composer require ranyuen/di
```

Support PHP >=5.4 and latest HHVM.

DI Example
--
Ranyuen/Di just extends Pimple. So we can use this same as Pimple 3.

```php
<?php
require_once 'vendor/autoload.php';

use Ranyuen\Di\Container;

class Momonga
{
    public $id;

    public function __construct($id = '')
    {
        $this->id = $id;
    }
}

$container = new Container();
$container['id'] = 'Sample ID.';
$container['momonga'] = function ($c) { return new Momonga($c['id']); };
$container['factory'] = $container->factory(function ($c) { return new Momonga(); });

var_dump('Sample ID.' === $container['id']);
var_dump($container['momonga'] instanceof Momonga);
var_dump($container['momonga'] === $container['momonga']);
var_dump('Sample ID.' === $container['momonga']->id);
var_dump($container['factory'] instanceof Momonga);
var_dump($container['factory'] !== $container['factory']);
?>
```

Basic Ray.Di and PHP-DI style _@Inject_ annotations example. Inject to constructor & properties.

```php
<?php
$container = new Container();
$container['momonga'] = function ($c) { return new Momonga(); };

class Yuraru
{
    public $benri;
    public $id;

    /** @Inject */
    public $momonga;

    public function __construct($momonga, $id)
    {
        $this->benri = $momonga;
        $this->id = $id;
    }
}

// We can pass additional args.
$yuraru = $container->newInstance('Yuraru', ['Sample ID.']);

var_dump($yuraru->benri instanceof Momonga);
var_dump('Sample ID.' === $yuraru->id);
var_dump($yuraru->momonga instanceof Momonga);
?>
```

Inject to properties.

```php
<?php
$container = new Container();
$container['momonga'] = function ($c) { return new Momonga(); };

class Gardea
{
    /** @Inject */
    public $momonga;
}

$gardea = new Gardea();
$container->inject($gardea);

var_dump($gardea->momonga instanceof Momonga);
?>
```

Detect with type hinting through _bind_ method.

```php
<?php
$container = new Container();
$container->bind('Momonga', 'momonga', function ($c) { return new Momonga(); });

class Benri
{
    /**
     * @Inject
     * @var Momonga
     */
    public $benri;

    public $momonga;

    public function __construct(Momonga $benri)
    {
        $this->momonga = $benri;
    }
}

$benri = $container->newInstance('Benri');

var_dump($benri->benri instanceof Momonga);
var_dump($benri->momonga instanceof Momonga);
?>
```

Assign services with another names by _@Named_ annotation.

```php
<?php
$container = new Container();
$container['momonga'] = function ($c) { return new Momonga(); };

class Musasabi
{
    /**
     * @Inject
     * @Named('musasabi=momonga')
     */
    public $musasabi;

    public $benri;

    /** @Named('benri=momonga') */
    public function __construct($benri)
    {
        $this->benri = $benri;
    }
}

$musasabi = $container->newInstance('Musasabi');

var_dump($musasabi->benri instanceof Momonga);
var_dump($musasabi->musasabi instanceof Momonga);
?>
```

We can use every methods that are defined at Pimple: _factory_, _protect_, _extend_ and _raw_. Below is binding example with _factory_.

```php
<?php
$container = new Container();
$container->bind('Momonga', 'momonga', function ($c) { return new Momonga(); });
$container->bind('Momonga', 'factory',
    $container->factory(function ($c) { return new Momonga(); })
);

class MomongaFactory
{
    /** @Inject */
    public $momonga;

    /** @Inject */
    public $factory;
}

$momonga = $container->newInstance('MomongaFactory');

var_dump($momonga->momonga instanceof Momonga);
var_dump($momonga->momonga === $container['momonga']);
var_dump($momonga->factory instanceof Momonga);
var_dump($momonga->factory !== $container['factory']);
?>
```

Take out DI instance by static access. We call this "Facade", like Laravel framwork.

```php
<?php
class Building
{
  public function launch()
  {
    return 'rocket';
  }
}

$c = new Container();
Container::setAsFacade($c);
$c['building'] = function ($c) { return new Building(); };
$c->facade('Station', 'building');
var_dump('rocket' === Station::launch());
?>
```

AOP Example
--
Basic AOP example.
```php
<?php
class Monday
{
    public function sunday($day = 1)
    {
        return $day;
    }

    public function tuesday($day = 2)
    {
        return $day;
    }
}

$c = new Container();
$c->wrap('Monday', ['tuesday'], function ($invocation, $args) {
    $day = $args[0];

    return $invocation($day + 1);
});
$c->wrap('Monday', ['/day$/'], function ($invocation, $args) {
    $day = $args[0];

    return $invocation($day * 7);
});
$monday = $c->newInstance('Monday');
var_dump(1 * 7     === $monday->sunday());
var_dump(2 * 7 + 1 === $monday->tuesday());
?>
```

Is there no annotation for AOP? Yes we can!

```php
<?php
class Tuesday
{
    /** @Wrap('advice.sunday,advice.monday') */
    public function wednesday($day)
    {
        return $day + 3;
    }
}

$c = new Container();
$c['advice.sunday'] = $c->protect(function ($invocation, $args) {
    $day = $args[0];

    return $invocation($day + 4);
});
$c['advice.monday'] = $c->protect(function ($invocation, $args) {
    $day = $args[0];

    return $invocation($day * 7);
});
$tuesday = $c->newInstance('Tuesday');
var_dump(5 * 7 + 4 + 3 === $tuesday->wednesday(5));
?>
```
