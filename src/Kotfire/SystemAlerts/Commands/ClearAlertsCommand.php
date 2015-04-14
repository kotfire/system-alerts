<?php 

namespace Kotfire\SystemAlerts\Commands;

use Kotfire\SystemAlerts\SystemAlert as SystemAlert;
use Kotfire\SystemAlerts\Alert as Alert;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

class ClearAlertsCommand extends Command
{
    protected $name = 'alert:clear';
    protected $description = 'Delete all Alerts';
    protected $systemAlert;

    public function __construct(SystemAlert $systemAlert)
    {
        $this->systemAlert = $systemAlert;

        parent::__construct();
    }

    public function fire()
    {
        if ($this->confirm(
            'Warning: This action will delete all alerts. Are you sure you want to continue? [Y/N]', 
            false
        )) {
            if ($this->systemAlert->deleteAlerts()) {
                $this->comment('Alerts deleted');
            } else {
                $this->comment('Alerts cannot be deleted');
            }
            
        }
    }
}
