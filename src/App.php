<?php
namespace ScriptingThoughts;

use ScriptingThoughts\Routes;

class App
{
    // Like main
    public function run(): void
    {
        $routes = new Routes();

        $routes->registerRoutes();

    }
}
