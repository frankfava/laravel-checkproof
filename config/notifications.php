<?php

return [
    'system_admins' => [
        [
            'name' => env('SYSTEM_ADMIN_NOTIFICATION_NAME', 'System Admin @ '.env('APP_NAME').''),
            'email' => env('SYSTEM_ADMIN_NOTIFICATION_EMAIL', env('MAIL_FROM_ADDRESS')),
        ],
        // ...more
    ],
];
