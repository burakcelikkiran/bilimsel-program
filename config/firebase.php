<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Firebase Cloud Messaging
    |--------------------------------------------------------------------------
    |
    | Service account JSON (sunucu) google-services.json (Android uygulama)
    | dosyasından farklıdır. Path boşsa push gönderimi atlanır.
    |
    */

    'credentials' => env('FIREBASE_CREDENTIALS'),

    'project_id' => env('FIREBASE_PROJECT_ID'),

    'default_event_slug' => env('MOBILE_EVENT_SLUG', 'turkpediatri-kongresi-2026'),

    'app_event_map' => [
        'tpk2026' => env('MOBILE_EVENT_SLUG', 'turkpediatri-kongresi-2026'),
    ],

    'android_notification_icon' => env('FCM_ANDROID_NOTIFICATION_ICON', 'ic_stat_tpk'),

    'notification_image_url' => env('FCM_NOTIFICATION_IMAGE_URL'),

];
