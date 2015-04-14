<?php

return array(

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
    | * {time} => Show the remaining time to the maintenance
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
);
