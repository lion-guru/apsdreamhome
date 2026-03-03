<?php
/**
 * APS Dream Home - Asset Configuration
 */

return [
    'css' => [
        'bootstrap' => [
            'bootstrap.min.css',
            'bootstrap-icons.css'
        ],
        'custom' => [
            'style.css',
            'responsive.css',
            'animations.css'
        ]
    ],
    'js' => [
        'vendor' => [
            'bootstrap.bundle.min.js',
            'jquery.min.js'
        ],
        'custom' => [
            'utils.js',
            'layout.js',
            'lazy-load.js'
        ]
    ],
    'bundles' => [
        'vendor' => [
            'css' => ['bootstrap.min.css', 'bootstrap-icons.css'],
            'js' => ['bootstrap.bundle.min.js', 'jquery.min.js']
        ],
        'app' => [
            'css' => ['style.css', 'responsive.css', 'animations.css'],
            'js' => ['utils.js', 'layout.js', 'lazy-load.js']
        ]
    ],
    'minification' => [
        'enabled' => true,
        'cache_busting' => true,
        'gzip' => true
    ]
];
