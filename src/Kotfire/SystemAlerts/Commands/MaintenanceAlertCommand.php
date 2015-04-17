<?php 

namespace Kotfire\SystemAlerts\Commands;

use Kotfire\SystemAlerts\SystemAlert as SystemAlert;
use Kotfire\SystemAlerts\Alert as Alert;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Carbon\Carbon;

class MaintenanceAlertCommand extends Command
{
    protected $name = 'alert:maintenance';
    protected $description = 'Add Maintenance alert';
    protected $systemAlert;

    public function __construct(SystemAlert $systemAlert)
    {
        $this->systemAlert = $systemAlert;

        parent::__construct();
    }

    public function fire()
    {
        if ($this->option('delete')) {
            $this->deleteMaintenance();
        } else {
            $this->addMaintenance();
        }
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['message', 'm', InputOption::VALUE_OPTIONAL, 'Alert Maintenance message', $this->systemAlert->getConfig('default_message')],
            ['time', 't', InputOption::VALUE_OPTIONAL, 'Next Maintenance (in minutes)', $this->systemAlert->getConfig('default_time')],
            ['delete', 'd', InputOption::VALUE_NONE, 'Delete Alert Maintenance'],
        ];
    }

    private function addMaintenance()
    {
        if ($this->systemAlert->hasMaintenanceAlert()) {
            $alert = $this->systemAlert->getMaintenanceAlert();
            $message = $alert['message'];
            $this->error("Maintenance alert already exists: $message");
        } else {
            if ($this->option('time')) {
                $minutes = intval($this->option('time'));
            }

            if (isset($minutes) && is_integer($minutes)) {
                $dt = Carbon::now();
                $dt->addMinutes($minutes);
                $datetime = $dt->toDateTimeString();
            } else {
                $datetime = null;
            }

            $this->systemAlert->addAlert(
                $this->option('message'), 
                Alert::MAINTENANCE_TYPE, 
                $datetime
            );
            $this->info('Maintenance Alert added');
        }
    }

    private function deleteMaintenance()
    {
        if (!$this->systemAlert->hasMaintenanceAlert()) {
            $this->error("Maintenance alert does not exists");
        } else {
            $alert = $this->systemAlert->getMaintenanceAlert();
            $this->systemAlert->deleteAlert($alert['id']);
            $this->comment('Maintenance Alert deleted');
        }
    }
}
