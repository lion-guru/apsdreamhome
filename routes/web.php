<?php

return [
    'public' => [
        'GET' => [
            '/' => 'HomeController@index',
            '/home' => 'HomeController@index',
            '/properties' => 'HomeController@properties',
            '/properties/' => 'HomeController@properties',
            '/about' => 'HomeController@about',
            '/contact' => 'HomeController@contact',
            '/projects' => 'HomeController@projects',
            '/sitemap' => 'HomeController@sitemap',
            '/privacy' => 'HomeController@privacy',
            '/terms' => 'HomeController@terms',
            '/news' => 'HomeController@blog',
            '/blog' => 'HomeController@blog',
            '/property' => 'HomeController@propertyDetail',
            '/property/' => 'HomeController@propertyDetail',
            '/featured-properties' => 'HomeController@featuredProperties',
        ],
        'POST' => [
            '/contact' => 'HomeController@contact',
        ],
    ],
];
