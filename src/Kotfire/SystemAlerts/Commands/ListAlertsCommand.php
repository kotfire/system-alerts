<?php 

namespace Kotfire\SystemAlerts\Commands;

use Kotfire\SystemAlerts\SystemAlert as SystemAlert;
use Kotfire\SystemAlerts\Alert as Alert;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

class ListAlertsCommand extends Command
{
    protected $name = 'alert:list';
    protected $description = 'View all Alerts';
    protected $systemAlert;

    public function __construct(SystemAlert $systemAlert)
    {
        $this->systemAlert = $systemAlert;

        parent::__construct();
    }

    public function fire()
    {
        if ($this->systemAlert->hasAlerts()) {
            $alerts = $this->systemAlert->loadAlerts();

            $table = $this->getHelper('table');
            $table->setHeaders(array('ID', 'Message', 'Type', 'DateTime', 'Created at'))
                ->setRows($alerts);
            $table->render($this->output);
        } else {
            $this->info('There are no alerts');
        }
    }
}
