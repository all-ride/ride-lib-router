# Ride: Router Library

Router library of the PHP Ride framework.

Routing is used to translate a incoming HTTP request to a callback.

## What's In This Library

### Route

A _Route_ defines a request path with the callback. 

The definition of a route provides 2 ways for passing arguments to the action:
 
* A placeholder in the path with the name of the variable for a dynamic value. The name of the variable should be between %.
* A static value in the definition 

You can optionally set an id to a route to make retrieval easy in your code. 
By using ids, you are able to override a path through your configuration without changing your code.

Keep your code clean by implementing only one action in a callback. 
Limiting a route to a specific, or multiple request methods (GET, POST, ...) can help you with this.

You can set a base URL to a route to limit your action to a certain domain. 

A locale can also be set to a route. 
This is usable to act on localized paths later on the process.

### Alias

An _Alias_ defines an aliased path for existing paths with query parameters.

It can be forced in order to redirect the original path to the alias.

### RouteContainer

A _RouteContainer_ is the collection of your routes. 
It offers an easy interface to manage the routes and aliases.

Use the route container to generate an URL in order for the aliases to be handled.

### Router

The _Router_ is what performes the actual translating of the incoming request to the route.
It's a simple interface but a generic implementation is added to the library.

### RouterResult 

The result of a route action on the router is a _RouterResult_ object.

This object has 3 possible states:

* __empty__: no route matched the incoming request
* __allowed methods are set__: a route matched but not for the incoming request method
* __alias is set__: a forced alias is matched and the request should be redirected
* __route is set__: a route matched and the callback should be invoked

### Url

An _Url_ is a mutable object to update and manipulate a generated URL.

## Code Sample

Check this code sample to see the possibilities of this library:

```php
<?php

use ride\library\router\GenericRouter;
use ride\library\router\RouteContainer;

// create a route container
$routeContainer = new RouteContainer();

// create a route with a path and a php callback
$route = $routeContainer->createRoute('/path/to/%action%', 'callback', 'id');
// single method allowed
$route->setAllowedMethods('GET'); 
// multiple methods allowed, case does not matter
$route->setAllowedMethods(array('GET', 'post'));

// add the route to the route container
$routeContainer->setRoute($route);

// create an alias
$alias = $routeContainer->createAlias('/path/to/content', '/ptc');

// add the alias to the route container
$routeContainer->setAlias($alias);

// create the router
$router = new GenericRouter($routeContainer);
// set a default action for the / request
$router->setDefaultCallback('callback');

// match a route
$result = $router->route('GET', '/foo/bar');

// no match
$result->isEmpty(); // true

// let's try again
$result = $router->route('PUT', '/path/to/content');

// a match but nothing to invoke
$result->isEmpty(); // false
$result->getAlias(); // null
$result->getRoute(); // null
$result->getAllowedMethods(); // array('GET' => true, 'POST' => true)

// now with the right method
$result = $router->route('GET', '/path/to/content');

// a match with arguments set to the route
$result->isEmpty(); // false
$result->getAlias(); // null
$result->getRoute(); // Route instance
$result->getRoute()->getArguments(); // array('action' => 'content');

// what about the alias?
$result = $router->route('GET', '/ptc');

// the same match will be generated
$result->isEmpty(); // false
$result->getAlias(); // null
$result->getRoute(); // Route instance
$result->getRoute()->getArguments(); // array('action' => 'content');

// let's force the alias
$alias->setIsForced(true);

// what about the alias now?
$result = $router->route('GET', '/ptc');

// still the same
$result->isEmpty(); // false
$result->getAlias(); // null
$result->getRoute(); // Route instance
$result->getRoute()->getArguments(); // array('action' => 'content');

// but when we take our original request ...
$result = $router->route('GET', '/path/to/content');

// ... we see we need to redirect
$result->isEmpty(); // false
$result->getAlias(); // Alias instance

// let's test multi domain support
$route = new Route('/path', 'callback', 'id2');
$route->setBaseUrl('http://some-server.com');    
$routeContainer->setRoute($route);

$result = $router->route('GET', '/path', 'http://other-server.com');
$result->isEmpty(); // true

$result = $router->route('GET', '/path', 'http://some-server.com');
$result->isEmpty(); // false

// create some urls

// http://some-server.com/path
$url = $routeContainer->getUrl('http://my-server.com', 'id2');
 
// http://my-server.com/ptc
$url = $routeContainer->getUrl('http://my-server.com', 'id', array('action' => 'content'));
 
// http://my-server.com/path/to/my-action
$routeContainer->getUrl('http://my-server.com', 'id', array('action' => 'my-action'));
 
// http://my-server.com/path/to/my-action?limit=20&page=1
$url = $routeContainer->getUrl('http://my-server.com', 'id', array('action' => 'my-action'), array('page' => 1, 'limit' => 20));
 
// http://my-server.com/path/to/your-action?limit=20&amp;page=2
$url = $routeContainer->getUrl('http://my-server.com', 'id', array('action' => 'my-action'), array('page' => 1, 'limit' => 20), '&amp;');
$url->setArgument('action', 'your-action');
$url->setQueryParameter('page', 2);
 
// translates an URL to it's alias if available and needed
$url = $routeContainer->getUrlAlias($url);
```

### Implementations

For more examples, you can check the following implementation of this library:
- [ride/web](https://github.com/all-ride/ride-web)

## Installation

You can use [Composer](http://getcomposer.org) to install this library.

```
composer require ride/lib-router
```
