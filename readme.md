# System Alerts for Laravel 4

This is a Laravel 4 package to add an Alert system to a web application.
It is designed to easily display one or more messages in a section of the web page. These messages are managed
with artisan commands.

It includes a ServiceProvider and some commands to manage the alerts and automatically attach them to the output.


## Installation

This package is available on [packagist](http://packagist.org), just add it to your composer.json

```json
"kotfire/system-alerts": "0.2.*"
```

After updating composer, add the ServiceProvider to the providers array in __app/config/app.php__

```php
'Kotfire\SystemAlerts\SystemAlertsServiceProvider',
```

The alerts will be automatically injected to the output, but if you want to load them manually or do some extra
actions you can add the facade to your facades in __app/config/app.php__

```php
'SystemAlert' => 'Kotfire\SystemAlerts\Facades\SystemAlert',
```

## Configuration
You can publish assets and configure it through Laravel.

To publish config file use

```
$ php artisan config:publish kotfire/system-alerts
```

In the config file you may modify the default values used in the package

To publish views use

```
$ php artisan view:publish kotfire/system-alerts
```

By publishing the views you may edit the template used to display the alert messages and make them your own style

## Usage

### Commands

The package was designed to display a Maintenance alert message before performing artisan down, but these are not the only type of alerts that can be added.

#### Maintenance alerts

**_Add new maintenance alert_**

```
$ php artisan alert:maintenance [-m "Custom message"] [-t "Next Maintenance (in minutes)"]
```

> Note: Parameters are optional, default values will be used if not present

Example:

```
$ php artisan alert:maintenance
```

or 

```
$ php artisan alert:maintenance -m "Maintenance in {time}" -t 15
```

This add new maintenance alert:
- Message: "Maintenance in {time}"
- Datetime: NOW + 15min

Example:

```
"Maintenance in {time}" will be "Maintenance in 14 minutes"
```

After putting app in maintenance mode using ```php artisan down``` the maintenance alert message will be deleted

> Note: This will not occur if you use Artisan::call('down').

> **Important:** You only can add one maintenance alert at time, if you try to add another you will get ```Maintenance alert already exists: "Message" ```

**_Delete maintenance alert_**

```
$ php artisan alert:maintenance -d
```

#### Information alerts

Information alerts are a simple way to show some information to the web app users.
This kind of alerts are not deleted automatically.

**_Add new alert_**

```
$ php artisan alert "Message" [-d "datetime"]
```

Example:

```
$ php artisan alert "This is an alert"
```

or 

```
$ php artisan alert "This message will self-destruct in {time}" -d "12:50"
```

#### Common

**_Delete alert by ID_**

```
$ php artisan alert:delete ID
```

**_View all alerts_**

```
$ php artisan alert:list
```

**_Delete all alerts_**

```
$ php artisan alert:clear
```

#### Modifiers

There are some modifiers you can use and will be replaced when the message is displayed:

| Modifier                   | Description                                           | Example                 | Result              |
| ---------------------------| ----------------------------------------------------- | ----------------------- | --------------------|
| {time}                     | Show the remaining time to the maintenance datetime   | {time}                  | 10 minutes          |
| {date}                     | Show the date to the maintenance as 'Y-m-d'           | {date}                  | 2015-04-01          |
| {datetime}                 | Show the datetime to the maintenance as 'Y-m-d H:i:s' | {datetime}              | 2015-04-01 12:50:20 |
| {format&#124;'dateformat'} | Show the datetime to the maintenance as 'date format' | {format&#124;d/m/Y H:i} | 01/04/2015 12:50    |

### Display

There are two ways to display the alerts: auto and manual

#### Auto

In this way alerts will be injected in all the responses containing a html element with the ID speficied in the config file (by default: 'alerts-container') using the template view to render them.

You just need to add a html container with the ID to all the views where you want to show the alerts.

Example:

```html
<div id="alerts-container"></div>
```

#### Manual

If you want to inject the alerts yourself, this is perfect for you.

Ensure you have been added the facade ([Installation](#installation)) and set the config option 'inject' to false.

Now you may load the alerts using

```php
$alerts = SystemAlert::loadAlerts();
```

and attach them to a View or do some logic with them.

## License

System Alerts is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)
