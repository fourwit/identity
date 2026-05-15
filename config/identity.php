<?php

return [

    /*
    |--------------------------------------------------------------------------
    | User Module Configuration
    |--------------------------------------------------------------------------
    | This file contains all configurable options for the User Module.
    | You can change these values to customize the module behavior.
    */

    // ============================================
    // ID & Identifier Settings
    // ============================================
    'enable_uuid' => false,                    // Generate UUID for users
    'enable_username' => true,                 // Allow username field

    // 'enable_web_views'  => true,     // Enable Blade views + Admin routes
    // 'enable_api_routes' => true,     // Enable API routes

    'enable_web_views'  => env('USER_ENABLE_WEB_VIEWS', true),
    'enable_api_routes' => env('USER_ENABLE_API_ROUTES', true),

    // ============================================
    // Required Fields
    // ============================================
    'require_email' => true,                   // Email is required during creation
    'require_phone' => false,                  // Phone is optional
    'require_username' => false,               // Username is required
    'allow_phone_only_accounts' => false,      // Allow users without email

    // ============================================
    // Default Values
    // ============================================
    'default_status' => 'active',              // Default status when creating user
    'default_timezone' => 'UTC',
    'default_locale' => 'en',

    // ============================================
    // Two-Factor Authentication
    // ============================================
    'enable_two_factor_by_default' => false,   // Enable 2FA for new users

    // ============================================
    // Avatar Settings
    // ============================================
    'avatar_disk' => 'public',                 // Storage disk for avatars
    'max_avatar_size_kb' => 2048,              // Max avatar size (2MB)

    // ============================================
    // Status Options
    // ============================================
    'statuses' => [
        'active',
        'inactive',
        'suspended',
        'pending',
    ],

    // ============================================
    // Pagination & Search
    // ============================================
    'per_page' => 5,                          // Default pagination
    'searchable_fields' => ['name', 'email', 'phone', 'username'],

    // ============================================
    // Security & Features
    // ============================================
    'enable_login_history' => true,            // Track user login history (future)
    'enable_device_management' => true,        // Allow device management (future)
    'api_rate_limit' => 60,          // Requests per minute per IP

];