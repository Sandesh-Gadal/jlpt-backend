<?php

return [
    /*
     * You can enable CORS for 1 or multiple paths.
     * Example: ['api/*']
     */
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    /*
     * Matches the request method.
     * Example: ['GET', 'POST', 'PUT', 'DELETE']
     */
    'allowed_methods' => ['*'],

    /*
     * Matches the request origin. Wildcards can be used.
     * Example: ['http://localhost:3000', 'https://spatie.be']
     */
    'allowed_origins' => [env('FRONTEND_URL', 'http://localhost:3000'  )],

    /*
     * Matches the request origin with preg_match.
     */
    'allowed_origins_patterns' => [],

    /*
     * Sets the Access-Control-Allow-Headers response header.
     * Example: ['Content-Type', 'Authorization']
     */
    'allowed_headers' => ['*'],

    /*
     * Sets the Access-Control-Expose-Headers response header.
     * Example: ['Content-Type', 'Authorization']
     */
    'exposed_headers' => [],

    /*
     * Sets the Access-Control-Max-Age response header when > 0.
     */
    'max_age' => 0,

    /*
     * Sets the Access-Control-Allow-Credentials header.
     */
    'supports_credentials' => true,
];
