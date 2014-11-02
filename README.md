[![Build Status](https://travis-ci.org/Ranyuen/Di.svg)](https://travis-ci.org/Ranyuen/Di)

Ranyuen/Di
==
Simple Ray.Di style DI (Dependency Injector) extending Pimple.

_cf._ [fabpot/Pimple](https://github.com/fabpot/Pimple)

_cf._ [koriym/Ray.Di](https://github.com/koriym/Ray.Di)

Features
--
1. Compatible with Pimple 3.
2. Injection through Ray.Di style _@Inject_ annotations. It's easy!

Install
--
```sh
composer require ranyuen/di
```

Example
--
We can use Ranyuen/Di same as Pimple 3.

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

Basic _@Inject_ annotations example. Inject to constructor & properties.

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
