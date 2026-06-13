<?php

return [
    '401' => [
        'title' => 'Session Expired',
        'message' => 'Your session is no longer active. Please rejoin the game to continue.',
        'action' => 'Go Home',
    ],
    '402' => [
        'title' => 'Payment Required',
        'message' => 'This action requires a valid subscription or payment.',
        'action' => 'Go Home',
    ],
    '403' => [
        'title' => 'Access Denied',
        'message' => 'You don\'t have permission to access this area.',
        'action' => 'Go Home',
    ],
    '404' => [
        'title' => 'Page Not Found',
        'message' => 'The page you\'re looking for doesn\'t exist or has been moved.',
        'action' => 'Go Home',
    ],
    '419' => [
        'title' => 'Session Expired',
        'message' => 'Your session has expired. Please refresh and try again.',
        'action' => 'Go Home',
    ],
    '429' => [
        'title' => 'Too Many Requests',
        'message' => 'Please wait a moment before trying again.',
        'action' => 'Go Home',
    ],
    '500' => [
        'title' => 'Something Went Wrong',
        'message' => 'An unexpected error occurred. Please try again or return to the home page.',
        'action' => 'Go Home',
    ],
    '503' => [
        'title' => 'Service Unavailable',
        'message' => 'The game server is temporarily unavailable. Please try again shortly.',
        'action' => 'Go Home',
    ],
];
