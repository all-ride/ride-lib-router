# Pallo: Router Library

Router library of the PHP Pallo framework.

Routing is used to translate a incoming HTTP request to a callback.

## Route

A route defines a request path with the callback. 

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

## RouteContainer

A route container is the collection of your routes. 
It offers an easy interface to manage the routes and is used by the router to see what routes are available.

## Router

The router is what performes the actual translating of the incoming request to the route.
It's a simple interface but a generic implementation is added to the library.

## RouterResult 

The result of a route action on the router is a router result object.
This object has 3 possible states:

* __empty__: no route matched the incoming request
* __allowed methods are set__: a route matched but not for the incoming request method
* __route is set__: a route matched and should be invoked

## Code Sample

Check this code sample to see the possibilities of this library:

    <?php
    
    use pallo\library\router\GenericRouter;
    use pallo\library\router\Route;
    use pallo\library\router\RouteContainer;
        
    // create a route with a path and a php callback
    $route = new Route('/path/to/%action%', 'callback', 'id');
    // single method allowed
    $route->setAllowedMethods('GET'); 
    // multiple methods allowed, case does not matter
    $route->setAllowedMethods(array('GET', 'post'));
    
    // create a route container
    $routeContainer = new RouteContainer();
    // add the route to it
    $routeContainer->addRoute($route);
    
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
    $result->getRoute(); // null
    $result->getAllowedMethods(); // array('GET' => true, 'POST' => true)
    
    // now with the right method
    $result = $router->route('PUT', '/path/to/content');
    
    // a match but nothing to invoke
    $result->isEmpty(); // false
    $result->getAllowedMethods(); // null
    $result->getRoute(); // Route instance
    $result->getRoute()->getArguments(); // array('action' => 'content');
    
    // let's test multi domain
    $route = new Route('/path', 'callback');
    $route->setBaseUrl('http://some-server.com');    
    $routeContainer->addRoute($route);
    
    $result = $router->route('GET', '/path', 'http://other-server.com');
    $result->isEmpty(); // true
    
    $result = $router->route('GET', '/path', 'http://some-server.com');
    $result->isEmpty(); // false