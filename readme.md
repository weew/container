# Dependency injection container

[![Build Status](https://travis-ci.org/weew/php-container.svg?branch=master)](https://travis-ci.org/weew/php-container)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/weew/php-container/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/weew/php-container/?branch=master)
[![Coverage Status](https://coveralls.io/repos/weew/php-container/badge.svg?branch=master&service=github)](https://coveralls.io/github/weew/php-container?branch=master)
[![License](https://poser.pugx.org/weew/php-container/license)](https://packagist.org/packages/weew/php-container)

## Installation

`composer require weew/php-container`

## Usage

#### Primitive data

Storing any type of data:

```php
$container = new Container();
$container->set('foo', 'bar');

// returns bar
$container->get('foo');
```

#### Classes

Retrieving classes:

```php
class Foo {}

$container = new Container();

// returns a new instance of Foo
$container->get(Foo::class);
```

Passing additional parameters:

```php
class Foo {
    public function __construct($x, $y = 2) {};
}

$container = new Container();

// parameters are matched by name
$container->get(Foo::class, ['x' => 1]);
```

Resolving constructor dependencies:

```php
class Foo {
    public function __construct(Bar $bar) {}
}
class Bar {}

$container = new Container();

// returns a new instance of Foo,
// Foo's constructor receives a new instance of Bar
$container->get(Foo::class);
```

Sharing a specific instance:

```php
class Foo {}

$container = new Container();
$container->set(Foo::class, new Foo());
// or
$container->set(new Foo());

// everyone will get the same instance of Foo
$container->get(Foo::class);
```

#### Factories

Working with factories:

```php
class Foo {
    public $bar;
}
class Bar {}

$container = new Container();

// a factory method will get it's dependencies resolved too.
$container->set(Foo::class, function(Bar $bar) {
    $foo = new Foo();
    $foo->bar = $bar;

    return $foo;
});
```

Accessing container from within a factory:

```php
$container = new Container();
$container->set('foo', 1);

// a container can be injected the same way as any other dependency
$container->set('bar', function(IContainer $container) {
    return $container->get('foo');
));
```

#### Interfaces

Resolving interfaces:

```php
interface IFoo {}
class Foo implements IFoo {}

$container = new Container();
$container->set(IFoo::class, Foo::class);

// will return an instance of Foo
$container->get(IFoo::class);
```

Sharing specific interface implementation:

```php
interface IFoo {}
class Foo implements IFoo {}

$container = new Container();
$container->set(IFoo::class, new Foo());

// everyone will get the same instance of Foo
$container->get(IFoo::class);
```

Interfaces can have factories too:

```php
interface IFoo {}
class Foo implements IFoo {}

$container = new Container();
$container->set(IFoo::class, function() {
    return new Foo();
});

// will return a new instance of Foo
$container->get(IFoo::class);
```

Of course you can also type hint interfaces:

```php
interface IFoo {}
class Foo implements IFoo {}
class Bar {
    public function __construct(IFoo $foo) {}
}

$container = new Container();
$container->set(IFoo::class, Foo::class);

// returns an instance of Bar
// Bar receives an instance of Foo, which implements the interface IFoo
$container->get(Bar::class);
```

#### Functions and methods

Functions can get resolved by the container:

```php
class Bar {}
function foo(Bar $bar, $foo) {}

$container = new Container();

// method foo gets called and receives an instance of Bar
// as with the other container methods, you can always pass your own arguments
$container->call('foo', ['foo' => 1]);
```

The same works for closures:

```php
class Bar {}
$container = new Container();

// closure gets called and receives an instance of Bar
$container->call('foo', function(Bar $bar) {});
```

Invoking class methods is also strait forward:

```php
class Foo {}
class Bar {
    public function takeFoo(Foo $foo, $x) {}
}

$container = new Container();

$bar = new Bar();
// method takeFoo gets invoked and receives a new instance
// of Foo, as well as the custom arguments
$container->callMethod($bar, 'takeFoo', ['x' => 1]);
```

Invoking static methods:

```php
class Foo {}
class Bar {
    public static function takeFoo(Foo $foo, $x) {}
}

$container = new Container();

// method takeFoo gets invoked and receives a new instance
// of Foo, as well as the custom arguments
$container->callMethod(Bar::class, 'takeFoo', ['x' => 1]);
```

#### Singletons

Container values can be defined as singletons. A singleton definition will return the same value over and over again. Here is an example of a singleton interface definition:

```php
interface IFoo {}
class Foo implements IFoo {}

$container = new Container();

$container->set(IFoo::class, Foo::class)->singleton();
```

The same works for classes:

```php
class Foo {}

$container = new Container();

$container->set(Foo::class, Foo::class)->singleton();
```

And factories:

```php
class Foo {}

$container = new Container();

$container->set(Foo::class, function() {
    return new Foo();
})->singleton();
```

Sharing an instance always results in a singleton:

```php
class Foo {}

$container = new Container();

$container->set(Foo::class, new Foo())->singleton();
// same as
$container->set(Foo::class, new Foo()); 
```

#### Additional methods

Check if the container has a value:

```php
$container = new Container();
$container->set('foo', 'bar');

// will return true
$container->has('foo');
```

Remove a value from the container:

```php
$container = new Container();
$container->set('foo', 'bar');
$container->remove('foo');

// will return false
$container->has('foo');
```
