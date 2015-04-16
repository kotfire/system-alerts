<?php 

namespace Kotfire\SystemAlerts;

use Illuminate\Support\ServiceProvider;

class SystemAlertsServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('kotfire/system-alerts', 'kotfire/system-alerts');

		$app = $this->app;

        $systemAlert = $app['system-alert'];
        $systemAlert->boot();

		$app['router']->after(function ($request, $response) use ($systemAlert) {
            $systemAlert->modifyResponse($request, $response);
    	});

        $app['events']->listen('artisan.start', function($consoleApp) use ($systemAlert) {
            if( isset($_SERVER['argv'][1]) && $_SERVER['argv'][1] == 'down' ) {
                $systemAlert->removeMaintenanceAlerts();
            }
        });
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
    {
        $this->app['system-alert'] = $this->app->share(
            function ($app) {
                return new SystemAlert($app);
            }
        );

        $this->app['command.system-alert.add'] = $this->app->share(
            function ($app) {
                return new Commands\AddAlertCommand($app['system-alert']);
            }
        );

        $this->app['command.system-alert.list'] = $this->app->share(
            function ($app) {
                return new Commands\ListAlertsCommand($app['system-alert']);
            }
        );

        $this->app['command.system-alert.delete'] = $this->app->share(
            function ($app) {
                return new Commands\DeleteAlertCommand($app['system-alert']);
            }
        );

        $this->app['command.system-alert.clear'] = $this->app->share(
            function ($app) {
                return new Commands\ClearAlertsCommand($app['system-alert']);
            }
        );

        $this->app['command.system-alert.maintenance'] = $this->app->share(
            function ($app) {
                return new Commands\MaintenanceAlertCommand($app['system-alert']);
            }
        );

        $this->commands([
            'command.system-alert.add', 
            'command.system-alert.list', 
            'command.system-alert.delete', 
            'command.system-alert.clear', 
            'command.system-alert.maintenance'
        ]);
    }

	/**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('system-alert');
    }

}
