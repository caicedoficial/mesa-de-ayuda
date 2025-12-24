<?php
/**
 * Routes configuration.
 *
 * In this file, you set up routes to your controllers and their actions.
 * Routes are very important mechanism that allows you to freely connect
 * different URLs to chosen controllers and their actions (functions).
 *
 * It's loaded within the context of `Application::routes()` method which
 * receives a `RouteBuilder` instance `$routes` as method argument.
 *
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;

/*
 * This file is loaded in the context of the `Application` class.
 * So you can use `$this` to reference the application class instance
 * if required.
 */
return function (RouteBuilder $routes): void {
    /*
     * The default class to use for all routes
     *
     * The following route classes are supplied with CakePHP and are appropriate
     * to set as the default:
     *
     * - Route
     * - InflectedRoute
     * - DashedRoute
     *
     * If no call is made to `Router::defaultRouteClass()`, the class used is
     * `Route` (`Cake\Routing\Route\Route`)
     *
     * Note that `Route` does not do any inflections on URLs which will result in
     * inconsistently cased URLs when used with `{plugin}`, `{controller}` and
     * `{action}` markers.
     */
    $routes->setRouteClass(DashedRoute::class);

    $routes->scope('/', function (RouteBuilder $builder): void {
        // Enable JSON extension for API endpoints
        $builder->setExtensions(['json']);
        // Gmail OAuth callback handler
        // When Google redirects to localhost:8080/?code=xxx, redirect to admin
        $builder->connect('/', ['controller' => 'Tickets', 'action' => 'index'], [
            '_name' => 'home'
        ]);

        // Health check endpoint for Docker monitoring
        // Verifies Nginx + PHP-FPM + PostgreSQL connectivity
        $builder->connect('/health', ['controller' => 'Health', 'action' => 'check'], [
            '_name' => 'health_check'
        ]);

        // PQRS Public Routes (no authentication required)
        $builder->connect('/pqrs/formulario', ['controller' => 'Pqrs', 'action' => 'create'], [
            '_name' => 'pqrs_public_form'
        ]);
        $builder->connect('/pqrs/success/{pqrsNumber}', ['controller' => 'Pqrs', 'action' => 'success'], [
            '_name' => 'pqrs_success',
            'pass' => ['pqrsNumber']
        ]);

        // Admin routes
        $builder->prefix('Admin', function (RouteBuilder $routes) {
            $routes->connect('/', ['controller' => 'Settings', 'action' => 'index']);
            $routes->fallbacks();
        });

        // Compras routes (admin and compras roles only)
        $builder->connect('/compras', ['controller' => 'Compras', 'action' => 'index'], [
            '_name' => 'compras_index'
        ]);
        $builder->connect('/compras/view/{id}', ['controller' => 'Compras', 'action' => 'view'], [
            '_name' => 'compras_view',
            'pass' => ['id']
        ]);
        $builder->connect('/compras/add-comment/{id}', ['controller' => 'Compras', 'action' => 'addComment'], [
            '_name' => 'compras_add_comment',
            'pass' => ['id']
        ])->setMethods(['POST']);
        $builder->connect('/compras/assign/{id}', ['controller' => 'Compras', 'action' => 'assign'], [
            '_name' => 'compras_assign',
            'pass' => ['id']
        ])->setMethods(['POST']);
        $builder->connect('/compras/change-status/{id}', ['controller' => 'Compras', 'action' => 'changeStatus'], [
            '_name' => 'compras_change_status',
            'pass' => ['id']
        ])->setMethods(['POST']);
        $builder->connect('/compras/change-priority/{id}', ['controller' => 'Compras', 'action' => 'changePriority'], [
            '_name' => 'compras_change_priority',
            'pass' => ['id']
        ])->setMethods(['POST']);
        $builder->connect('/compras/download/{id}', ['controller' => 'Compras', 'action' => 'download'], [
            '_name' => 'compras_download',
            'pass' => ['id']
        ])->setMethods(['GET']);

        // Ticket to Compra conversion
        $builder->connect('/tickets/convert-to-compra/{id}', ['controller' => 'Tickets', 'action' => 'convertToCompra'], [
            '_name' => 'ticket_convert_to_compra',
            'pass' => ['id']
        ])->setMethods(['POST']);

        /*
         * Connect catchall routes for all controllers.
         */
        $builder->fallbacks();
    });

    /*
     * If you need a different set of middleware or none at all,
     * open new scope and define routes there.
     *
     * ```
     * $routes->scope('/api', function (RouteBuilder $builder): void {
     *     // No $builder->applyMiddleware() here.
     *
     *     // Parse specified extensions from URLs
     *     // $builder->setExtensions(['json', 'xml']);
     *
     *     // Connect API actions here.
     * });
     * ```
     */
};
