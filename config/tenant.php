<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Tenant Identification Method
    |--------------------------------------------------------------------------
    |
    | This option controls how the application identifies the current tenant.
    | Supported methods: 'subdomain', 'domain', 'path', 'header'
    |
    */

    'identification_method' => env('TENANT_IDENTIFICATION_METHOD', 'subdomain'),

    /*
    |--------------------------------------------------------------------------
    | Domain Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for domain-based tenant identification
    |
    */

    'domain_prefix' => env('TENANT_DOMAIN_PREFIX', 'subdomain'),

    /*
    |--------------------------------------------------------------------------
    | Database Configuration
    |--------------------------------------------------------------------------
    |
    | If using separate databases per tenant, configure the prefix here
    |
    */

    'database_prefix' => env('TENANT_DATABASE_PREFIX', 'edusphere_'),

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Cache settings for tenant data
    |
    */

    'cache_ttl' => env('TENANT_CACHE_TTL', 3600),

];

