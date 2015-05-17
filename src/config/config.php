<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Alerts storage
    |--------------------------------------------------------------------------
    |
    | Alerts will be stored in a disk file. Here you may set the folder.
    |
    */

    'storage' => storage_path().'/systemalert',

    /*
    |--------------------------------------------------------------------------
    | Default Time
    |--------------------------------------------------------------------------
    |
    | When calling the command to add new maintenance alert you can set
    | custom time remaining to the maintenance, either this time will be used.
    | 
    | Time unit are minutes
    |
    */

    'default_time' => 15,

    /*
    |--------------------------------------------------------------------------
    | Default Maintenance Message
    |--------------------------------------------------------------------------
    |
    | When calling the command to add new maintenance alert you can set
    | custom message, either this message will be used.
    |
    | There are some modifiers you can use and will be replaced when the message
    | is displayed:
    |
    | {time}                => Show the remaining time to the maintenance datetime
    | {date}                => Show the date to the maintenance as 'Y-m-d'
    | {datetime}            => Show the datetime to the maintenance as 'Y-m-d H:i:s'
    | {format|'dateformat'} => Show the datetime to the maintenance as 'date format'
    |
    */

    'default_message' => 'Maintenance in {time}',

    /*
    |--------------------------------------------------------------------------
    | Over time message
    |--------------------------------------------------------------------------
    |
    | When using {time} modifier and time is reached a message will be
    | displayed. Here you may set the message content. 
    |
    */

    'over_time_message' => '0 seconds (IMMINENT)',

    /*
    |--------------------------------------------------------------------------
    | Inject
    |--------------------------------------------------------------------------
    |
    | By default, alerts are added inside a html container by listening to the
    | response. If you disable this, you have to load and add them in your
    | controllers/views yourself.
    |
    */

    'inject' => true,

    /*
    |--------------------------------------------------------------------------
    | Container ID
    |--------------------------------------------------------------------------
    |
    | When injection is active all the alerts will be displayed inside a html
    | container. Here you may set up that container ID. 
    |
    */

    'container_id' => 'alerts-container',

    /*
    |--------------------------------------------------------------------------
    | Order
    |--------------------------------------------------------------------------
    |
    | Configure alert order
    |
    */

    'sorting' => [

        /*
        |--------------------------------------------------------------------------
        | Sort By
        |--------------------------------------------------------------------------
        |
        | Sort using this alert property
        | 
        | String    => 'type', 'datetime' or 'created_at'
        | Array     => Combine properties ['type', 'datetime']
        |
        */
        'sort_by' => ['type', 'datetime', 'created_at'],

        /*
        |--------------------------------------------------------------------------
        | Order
        |--------------------------------------------------------------------------
        |
        | Sort direction
        | 
        | String    => 'asc' or 'desc'
        | Array     => Combine orders ['asc', 'desc'] (When using sort_by as Array)
        |
        */
        'order' => 'asc',

        /*
        |--------------------------------------------------------------------------
        | Type priority
        |--------------------------------------------------------------------------
        |
        | Set up type priority (Only used if sorting by type)
        | Types => 'maintenance' and 'info'
        |
        */
        'type_priority' => ['maintenance', 'info'],

    ],
];
