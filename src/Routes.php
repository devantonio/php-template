<?php 
namespace ScriptingThoughts;

use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;

class Routes {

    public function registerRoutes() {

        // Set up the dispatcher
        $dispatcher = simpleDispatcher(function(RouteCollector $r) {

            // Define the routes
            $r->addRoute('GET', '/', ['ScriptingThoughts\Controllers\HomeController', 'home']);

            // Add route for /users/{id}, where {id} must be a number
            // $r->addRoute('GET', '/users/{id:\d+}', [$this, 'get_user_handler']);
            // $r->addRoute('GET', '/articles/{id:\d+}[/{title}]', [$this, 'get_article_handler']);
        });
        
        // Fetch method and URI
        $httpMethod = $_SERVER['REQUEST_METHOD'];
        $uri = $_SERVER['REQUEST_URI'];
        
        // Strip query string (?foo=bar) and decode URI
        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }
        $uri = rawurldecode($uri);
        
        // Dispatch the route
        $routeInfo = $dispatcher->dispatch($httpMethod, $uri);
        switch ($routeInfo[0]) {
            case \FastRoute\Dispatcher::NOT_FOUND:
                // Handle 404 Not Found
                echo "404 Not Found";
                break;
            case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                // Handle 405 Method Not Allowed
                $allowedMethods = $routeInfo[1];
                echo "405 Method Not Allowed. Allowed methods: " . implode(', ', $allowedMethods);
                break;
            case \FastRoute\Dispatcher::FOUND:
                // Call the handler with any route variables
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];

                // If the handler is an array (i.e., a class and method)
                if (is_array($handler)) {
                    // Create an instance of the class and call the method
                    $controller = new $handler[0]; // Instantiate the controller
                    call_user_func_array([$controller, $handler[1]], $vars); // Call the method with vars
                } else {
                    // If the handler is a function
                    call_user_func($handler, $vars);
                }
                break;
        }
    }

    // Define handler functions as class methods
    // public function get_user_handler($vars) {
    //     echo "User with ID: " . $vars['id'];
    // }

    // public function get_article_handler($vars) {
    //     $articleId = $vars['id'];
    //     $title = isset($vars['title']) ? $vars['title'] : 'No Title';
    //     echo "Article with ID: $articleId, Title: $title";
    // }
}
