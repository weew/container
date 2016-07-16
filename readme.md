# Dependency injection container

[![Build Status](https://img.shields.io/travis/weew/container.svg)](https://travis-ci.org/weew/container)
[![Code Quality](https://img.shields.io/scrutinizer/g/weew/container.svg)](https://scrutinizer-ci.com/g/weew/container)
[![Test Coverage](https://img.shields.io/coveralls/weew/container.svg)](https://coveralls.io/github/weew/container)
[![Version](https://img.shields.io/packagist/v/weew/container.svg)](https://packagist.org/packages/weew/container)
[![Licence](https://img.shields.io/packagist/l/weew/container.svg)](https://packagist.org/packages/weew/container)

## Table of contents

- [Installation](#installation)
- [Creating a container](#creating-a-container)
- [Primitive data](#primitive-data)
- [Classes](#classes)
- [Factories](#factories)
- [Interfaces](#interfaces)
- [Functions and methods](#functions-and-methods)
- [Singletons](#singletons)
- [Wildcards](#wildcards)
- [Aliases](#aliases)
- [Additional methods](#additional-methods)
- [Extensions](#extensions)
    - [Doctrine integration](#doctrine-integration)

## Installation

`composer require weew/container`

## Creating a container

The container has no additional dependencies, so a simple instantiation will do the trick.

```php
$container = new Container();
```

## Primitive data

Storing any type of data:

```php
$container->set('foo', 'bar');

// returns bar
$container->get('foo');
```

## Classes

Retrieving classes:

```php
class Foo {}

// returns a new instance of Foo
$container->get(Foo::class);
```

Passing additional parameters:

```php
class Foo {
    public function __construct($x, $y = 2) {};
}

// parameters are matched by name
$container->get(Foo::class, ['x' => 1]);
```

Resolving constructor dependencies:

```php
class Foo {
    public function __construct(Bar $bar) {}
}
class Bar {}

// returns a new instance of Foo,
// Foo's constructor receives a new instance of Bar
$container->get(Foo::class);
```

Sharing a specific instance:

```php
class Foo {}

$container->set(Foo::class, new Foo());
// or
$container->set(new Foo());

// everyone will get the same instance of Foo
$container->get(Foo::class);
```

## Factories

Working with factories:

```php
class Foo {
    public $bar;
}
class Bar {}

// a factory method will get it's dependencies resolved too.
$container->set(Foo::class, function(Bar $bar) {
    $foo = new Foo();
    $foo->bar = $bar;

    return $foo;
});
```

Accessing container from within a factory:

```php
$container->set('foo', 1);

// a container can be injected the same way as any other dependency
$container->set('bar', function(IContainer $container) {
    return $container->get('foo');
));
```

Container is not limited to closure factories, it supports class method and static method factories too:

```php
class MyFactoryClass {
    public function factoryMethod(AnotherDependency $dependency) {}
    public function staticFactoryMethod(AnotherDependency $dependency) {}
}

$container->set(Foo::class, new MyFactoryClass(), 'factoryMethod');
$container->set(Foo::class, MyFactoryClass::class, 'factoryMethod');
$container->set(Foo::class, MyFactoryClass::class, 'staticFactoryMethod');
```

Traditional callable array syntax is also supported. It does exactly the same as the examples above, but with a slightly different syntax:

```php
$container->set(Foo::class, [new MyFactoryClass(), 'factoryMethod']);
$container->set(Foo::class, [MyFactoryClass::class, 'factoryMethod']);
$container->set(Foo::class, [MyFactoryClass::class, 'staticFactoryMethod']);
```
All facotires benefit from dependency injection. Additionaly, if you let the container instantiate your factory, it will be resolved trough the container too.

## Interfaces

Resolving interfaces:

```php
interface IFoo {}
class Foo implements IFoo {}

$container->set(IFoo::class, Foo::class);

// will return an instance of Foo
$container->get(IFoo::class);
```

Sharing specific interface implementation:

```php
interface IFoo {}
class Foo implements IFoo {}

$container->set(IFoo::class, new Foo());

// everyone will get the same instance of Foo
$container->get(IFoo::class);
```

Interfaces can have factories too:

```php
interface IFoo {}
class Foo implements IFoo {}

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

$container->set(IFoo::class, Foo::class);

// returns an instance of Bar
// Bar receives an instance of Foo, which implements the interface IFoo
$container->get(Bar::class);
```

## Functions and methods

Functions can get resolved by the container:

```php
class Bar {}
function foo(Bar $bar, $foo) {}

// method foo gets called and receives an instance of Bar
// as with the other container methods, you can always pass your own arguments
$container->callFunction('foo', ['foo' => 1]);
```

The same works for closures:

```php
class Bar {}

// closure gets called and receives an instance of Bar
$container->callFunction(function(Bar $bar) {});
```

Invoking class methods is also strait forward:

```php
class Foo {}
class Bar {
    public function takeFoo(Foo $foo, $x) {}
}

$bar = new Bar();
// method takeFoo gets invoked and receives a new instance
// of Foo, as well as the custom arguments
$container->callMethod($bar, 'takeFoo', ['x' => 1]);
// you could also let the container create an instance
$container->callMethod(Bar::class, 'takeFoo', ['x' => 1]);
```

Invoking static methods:

```php
class Foo {}
class Bar {
    public static function takeFoo(Foo $foo, $x) {}
}

// method takeFoo gets invoked and receives a new instance
// of Foo, as well as the custom arguments
$container->callStaticMethod(Bar::class, 'takeFoo', ['x' => 1]);
```

It is possible to use PHP's traditional callable syntax for invocation of functions and methods:

```php
// same as $container->callFunction($functionName, $args)
$container->call($functionName, $args);
// same as $container->callFunction($closure, $args)
$container->call($closure, $args);
// same as $container->callMethod($instance, $method, $args)
$container->call([$instance, $method], $args);
// same as $container->callMethod($className, $method, $args)
$container->call([$className, $method], $args);
// same as $container->callStaticMethod($className, $staticMethod, $args)
$container->call([$className, $staticMethod], $args);
```

## Singletons

Container values can be defined as singletons. A singleton definition will return the same value over and over again. Here is an example of a singleton interface definition:

```php
interface IFoo {}
class Foo implements IFoo {}

$container->set(IFoo::class, Foo::class)->singleton();
```

The same works for classes:

```php
class Foo {}

$container->set(Foo::class)->singleton();
```

And factories:

```php
class Foo {}

$container->set(Foo::class, function() {
    return new Foo();
})->singleton();
```

Sharing an instance always results in a singleton:

```php
class Foo {}

$container->set(Foo::class, new Foo())->singleton();
// same as
$container->set(Foo::class, new Foo());
```

## Wildcards

This one might be especially useful when working with factories. Lets take Doctrine for example. You can not simply instantiate a repository by yourself. But still, it would be great if you could have them resolved by the container. Unfortunately, this will throw an error, since the repository requires a special parameter that can and should not be resolved by the container:

```php
class MyRepository {
    public function __construct(SpecialUnresolvableValue $value) {}
}

$container->get(MyRepository::class);
```

However, you might use a wildcard factory. You can use any regex pattern as a mask. Right now, the only supported regex delimiters are `/` and `#`.

```php
class MyRepository implements IRepository {
    public function __construct(SpecialUnresolvableValue $value) {}
}
class YoursRepository implements IRepository {
    public function __construct(SpecialUnresolvableValue $value) {}
}

$container->set('/Repository$/', function(RepositoryFactory $factory, $abstract) {
    return $factory->createRepository($abstract);
});

$container->get(MyRepository::class);
$container->get(YourRepository::class);
```

As you see here, the actual class name `MyRepository` was passed to the custom factory as the `$abstract` parameter. From there, we call the `RepositoryFactory` and tell it to create us a new instance of `MyRepository`. Afterwards the same factory can be used to create an instance of `YourRepository`.

Telling the container that all instances produced within this factory should be singletons is very simple:

```php
$container->set('/Repository$/', function(RepositoryFactory $factory, $abstract) {
    return $factory->createRepository($abstract);
})->singleton();
```

Wildcards are very powerful, however, they should be used with caution, since they could break your application if you configure them wrong. (for example: if the regex mask is not precise enough and matches unwanted classes). Thanks to regex, creating precise masks shouldn't be a big deal though.

Wildcards can also be used in combination of class names and instances. But I find the usecases for this very limited:

```php
$container->set('/Repository$/', EntityRepository::class);
$container->set('/Repository$/', $instance);
```

## Aliases

If you need to create an alias for a definition, for example when you want to provide a factory for a class as well as for it's interface, and don't want to do it twice for each one, you could create a definition with an alias (or two, or ten). Just provide an array of identifiers. The first element in the array is considered as "the id" and the others are aliases.

```php
$container->set([MyImplementation::class, IImplementation::class], function() {
    return new MyImplementation('foo');
});

// both calls will return a value from the same factory
$container->get(MyImplementation::class);
$container->get(IImplementation::class);
```

The same would work with singletons, primitive values and so on.

## Additional methods

Check if the container has a value:

```php
$container->set('foo', 'bar');

// will return true
$container->has('foo');
```

Remove a value from the container:

```php
$container->set('foo', 'bar');
$container->remove('foo');

// will return false
$container->has('foo');
```

## Extensions

There are additional extension available to make the container even more powerful.

### Doctrine integration

The [weew/container-doctrine-integration](https://github.com/weew/container-doctrine-integration) package makes doctrine repositories injectable.
