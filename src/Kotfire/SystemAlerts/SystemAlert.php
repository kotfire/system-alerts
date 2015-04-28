<?php

namespace Kotfire\SystemAlerts;

use Illuminate\Filesystem\Filesystem;
use \Exception;
use \View;
use Carbon\Carbon;
use \Config;
use \Log;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SystemAlert {
    /**
     * The Laravel application instance.
     *
     * @var \Illuminate\Foundation\Application
     */
    protected $app;

    /**
     * System Alert booted
     *
     * @var bool
     */
    protected $booted = false;

    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * The path to the alerts.
     *
     * @var string
     */
    protected $alertsStorage;

    /**
     * Illuminate config repository.
     *
     * @var Illuminate\Config\Repository
     */
    protected $config;

    /**
     * Replacer methods
     *
     * @var Array
     */
    protected $replacers = ['time', 'date', 'datetime', 'dateformat'];

    /**
     * Create a new instance.
     *
     * @param  \Illuminate\Filesystem\Filesystem  $files
     * @param  string  $alertsStorage
     * @return void
     */
    public function __construct($app)
    {
        $this->app = $app;
        $path = $app['config']->get('kotfire/system-alerts::storage');
        $this->alertsStorage = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $this->files = $this->app['files'];
        $this->config = $app['config'];
    }

    public function boot()
    {
        if ($this->booted) {
            return;
        }

        try {
            $this->initStorage();
            $this->booted = true;
        } catch(Exception $e) {
            $app['log']->error('SystemAlert exception: ' . $e->getMessage());
        }
    }

    private function initStorage()
    {
        if (!$this->files->isDirectory($this->alertsStorage)) {
            if ($this->files->makeDirectory($this->alertsStorage, 0777, true)) {
                $this->files->put($this->alertsStorage . '.gitignore', "*\n!.gitignore");
            } else {
                throw new Exception("Cannot create directory '$this->alertsStorage'..");
            }
        }
    }

    public function getConfig($key)
    {
        return $this->config->get("kotfire/system-alerts::$key");
    }
  
    /**
     * Load the alerts JSON file.
     *
     * @param  boolean  $parse
     *
     * @return array
     */
    public function loadAlerts($parse = true)
    {
        $path = $this->alertsStorage.'/alerts.json';

        // Alerts are a file containing a JSON representation of every
        // alert alert that should be displayed by the app
        if ($this->files->exists($path))
        {
            $alerts = json_decode($this->files->get($path), true);
        }

        if (empty($alerts)) {
            $alerts = [];
        }

        // Parse alerts
        if ($parse) {
            return $this->parseAlerts($alerts);
        } else {
            return $alerts;
        }
    }

    /**
     * Write alerts to file in disk.
     *
     * @param  array  $alerts
     *
     * @return array
     */
    public function writeAlerts($alerts)
    {
        $alerts = $this->sortAlerts($alerts);

        $path = $this->alertsStorage.'/alerts.json';

        $this->files->put($path, json_encode($alerts));

        return $alerts;
    }

    /**
     * Add alert
     *
     * @param  string  $msg
     * @param  string  $type
     * @param  string  $datetime
     *
     * @return boolean
     */
    public function addAlert($msg, $type = Alert::INFO_TYPE, $datetime = null)
    {
        try {
            if ($type === Alert::MAINTENANCE_TYPE && $this->hasMaintenanceAlert()) {
                throw new Exception("Maintenance alert already exists");
            }
            $alert = new Alert($msg, $type, $datetime);
            $loaded = $this->loadAlerts(false);
            $alerts = array_merge($loaded, $alert->toArray());
            $this->writeAlerts($alerts);

            return true;
        } catch(Exception $e) {
            return false;
        }
    }

    /**
     * Delete alert
     *
     * @param  string  $id
     *
     * @return boolean
     */
    public function deleteAlert($id)
    {
        try {
            $alerts = $this->loadAlerts(false);

            if (!isset($alerts[$id])) {
                throw new Exception("Alert does not exists");
            }

            unset($alerts[$id]);
            $this->writeAlerts($alerts);

            return true;
        } catch(Exception $e) {
            return false;
        }
    }

    /**
     * Delete alerts
     *
     * @return boolean
     */
    public function deleteAlerts()
    {
        try {
            $this->writeAlerts([]);
            return true;
        } catch(Exception $e) {
            $this->app['log']->debug($e->getMessage());
            return false;
        }
    }

    /**
     * Check if maintenance alert exists
     *
     * @return boolean
     */
    public function hasMaintenanceAlert()
    {
        $alerts = $this->loadAlerts(false);
        foreach ($alerts as $alert) {
            if ($alert['type'] === Alert::MAINTENANCE_TYPE) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get Maintenance alert
     *
     * @return Alert
     */
    public function getMaintenanceAlert()
    {
        $alerts = $this->loadAlerts();
        foreach ($alerts as $alert) {
            if ($alert['type'] === Alert::MAINTENANCE_TYPE) {
                return $alert;
            }
        }
        
        return false;
    }

    /**
     * Check if app has Alerts
     *
     * @return boolean
     */
    public function hasAlerts()
    {
        return !empty($this->loadAlerts(false));
    }

    /**
     * Modify the response and inject the alerts
     *
     * @param  \Symfony\Component\HttpFoundation\Request $request
     * @param  \Symfony\Component\HttpFoundation\Response $response
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function modifyResponse($request, $response)
    {
        $app = $this->app;

        // Do not inject
        if ($app->runningInConsole() or $request->ajax() or !$this->hasAlerts()) {
            return $response;
        }

        if ($app['config']->get('kotfire/system-alerts::inject', true)) {
            try {
                $this->injectAlerts($response);
            } catch (\Exception $e) {
                $app['log']->error('SystemAlert exception: ' . $e->getMessage());
            }
        }

        return $response;
    }

    /**
     * Remove maintenance alert
     *
     * @return void
     */
    public function removeMaintenanceAlerts()
    {
        $alerts = $this->loadAlerts(false);
        foreach ($alerts as $id => $alert) {
            if ($alert['type'] === Alert::MAINTENANCE_TYPE) {
                unset($alerts[$id]);
            }
        }

        $this->writeAlerts($alerts);
    }

    /**
     * Sort Alert
     *
     * @param Array $alerts
     *
     * @return Array
     */
    protected function sortAlerts(Array $alerts)
    {
        $app = $this->app;

        uasort($alerts, ['self', 'compareAlerts']);

        return $alerts;
    }

    private static function compareAlerts($a, $b)
    {
        $sortConfig = Config::get('kotfire/system-alerts::sorting.sort_by');
        $orderConfig = Config::get('kotfire/system-alerts::sorting.order');

        do {
            if (is_array($sortConfig)) {
                $sortBy = array_shift($sortConfig); 
            } else {
                $sortBy = $sortConfig;
            }

            if (is_array($orderConfig)) {
                $order = array_shift($orderConfig); 
            } else {
                $order = $orderConfig;
            }
            $compare = self::compareAlertsBy($a, $b, $sortBy, $order);
        } while ($compare == 0 && (!is_null($sortBy) && is_array($sortConfig)));

        return $compare;
    }

    private static function compareAlertsBy($a, $b, $sortBy, $order)
    {
        if (is_null($sortBy) || is_null($order)) {
            return 0;
        }

        if ($order === 'desc') {
            $order = -1;
        } else {
            $order = 1;
        }

        switch ($sortBy) {
            case 'type':
                $priority = Config::get('kotfire/system-alerts::sorting.type_priority');
                $typeA = array_search($a['type'], $priority) ?: count($priority);
                $typeB = array_search($b['type'], $priority) ?: count($priority);

                if ($typeA == $typeB) {
                    return 0;
                }
                return ($typeA < $typeB) ? 1 * $order : -1 * $order;

                break;
            case 'datetime':
                if (is_null($a['datetime']) && is_null($b['datetime'])) {
                    return 0;
                } else if (is_null($a['datetime'])) {
                    return 1 * $order;
                } else if (is_null($b['datetime'])) {
                    return -1 * $order;
                }

                $dateA = Carbon::createFromFormat('Y-m-d H:i:s', $a['datetime']);
                $dateB = Carbon::createFromFormat('Y-m-d H:i:s', $b['datetime']);

                if ($dateA == $dateB) {
                    return 0;
                }
                return ($dateA > $dateB) ? 1 * $order : -1 * $order;

                break;
            case 'created_at':
                $createdA = Carbon::createFromFormat('Y-m-d H:i:s', $a['created_at']);
                $createdB = Carbon::createFromFormat('Y-m-d H:i:s', $b['created_at']);

                if ($createdA == $createdB) {
                    return 0;
                }
                return ($createdA > $createdB) ? 1 * $order : -1 * $order;

                break;
            default:
                return 0;
                break;
        }
    }

    /**
     * Injects the alerts into the given Response.
     * Based on https://github.com/barryvdh/laravel-debugbar/blob/master/src/LaravelDebugbar.php
     *
     * @param \Symfony\Component\HttpFoundation\Response $response A Response instance
     */
    protected function injectAlerts(Response $response)
    {
        $app = $this->app;
        $containerId = $this->config->get('kotfire/system-alerts::container_id');
        $htmlContainer = 'id="'.$containerId.'"';

        $content = $response->getContent();

        $pos = strripos($content, $htmlContainer);
        
        if ($pos !== false) {

            // Search end of the open html tag
            $pos = strpos($content, '>', $pos);

            if ($pos !== false) {
                $alerts = $this->loadAlerts();

                // Load view
                $view = View::make('kotfire/system-alerts::template')
                    ->with('alerts', $alerts)
                    ->render();

                // Set position at the end of the container open tag
                $pos += 1;

                // Inject view into content
                $content = substr($content, 0, $pos) . $view . substr($content, $pos);
            }
        }

        $response->setContent($content);
    }

    /**
     * Parses alerts
     *
     * @param  array  $alerts
     *
     * @return array
     */
    protected function parseAlerts(Array $alerts)
    {
        foreach ($this->replacers as $replacer) {
            $method = "replace".ucfirst($replacer);
            if (method_exists($this, $method)) {
                $alerts = $this->$method($alerts);
            }
        }

        return $alerts;
    }

    /**
     * Replaces string with the difference between alert date and now
     *
     * @param  array  $alerts
     *
     * @return array
     */
    private function replaceTime(Array $alerts)
    {
        $config = $this->config;
        return array_map(
            function($alert) use ($config) {
                $alert['message'] = preg_replace_callback(
                    '/{time}/',
                    function($match) use ($alert, $config) {
                        if (!is_null($alert['datetime'])) {
                            $dateTime = Carbon::createFromFormat('Y-m-d H:i:s', $alert['datetime']);
                            if ($dateTime->isFuture()) {
                                return $dateTime->diffForHumans(null, true);
                            } else {
                                return $config->get('kotfire/system-alerts::over_time_message');
                            }
                        } else {
                            return $match[0];
                        }
                    },
                    $alert['message']
                );
                return $alert;
            },
            $alerts
        );
    }

    /**
     * Replaces string with the alert date
     *
     * @param  array  $alerts
     *
     * @return array
     */
    private function replaceDate(Array $alerts)
    {
        $config = $this->config;
        return array_map(
            function($alert) use ($config) {
                $alert['message'] = preg_replace_callback(
                    '/{date}/',
                    function($match) use ($alert, $config) {
                        if (!is_null($alert['datetime'])) {
                            $dateTime = Carbon::createFromFormat('Y-m-d H:i:s', $alert['datetime']);
                            return $dateTime->toDateString();
                        } else {
                            return $match[0];
                        }
                    },
                    $alert['message']
                );
                return $alert;
            },
            $alerts
        );
    }

    /**
     * Replaces string with the alert datetime
     *
     * @param  array  $alerts
     *
     * @return array
     */
    private function replaceDatetime(Array $alerts)
    {
        $config = $this->config;
        return array_map(
            function($alert) use ($config) {
                $alert['message'] = preg_replace_callback(
                    '/{datetime}/',
                    function($match) use ($alert, $config) {
                        if (!is_null($alert['datetime'])) {
                            $dateTime = Carbon::createFromFormat('Y-m-d H:i:s', $alert['datetime']);
                            return $dateTime->toDateTimeString();
                        } else {
                            return $match[0];
                        }
                    },
                    $alert['message']
                );
                return $alert;
            },
            $alerts
        );
    }

    /**
     * Replaces string with the alert date with custom format
     *
     * @param  array  $alerts
     *
     * @return array
     */
    private function replaceDateformat(Array $alerts)
    {
        $config = $this->config;
        return array_map(
            function($alert) use ($config) {
                $alert['message'] = preg_replace_callback(
                    '/{format\|(.+)}/',
                    function($match) use ($alert, $config) {
                        if (!is_null($alert['datetime']) && isset($match[1])) {
                            $dateTime = Carbon::createFromFormat('Y-m-d H:i:s', $alert['datetime']);
                            return $dateTime->format($match[1]);
                        } else {
                            return $match[0];
                        }
                    },
                    $alert['message']
                );
                return $alert;
            },
            $alerts
        );
    }
}