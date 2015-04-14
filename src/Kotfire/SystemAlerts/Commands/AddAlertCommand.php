<?php 

namespace Kotfire\SystemAlerts\Commands;

use Kotfire\SystemAlerts\SystemAlert as SystemAlert;
use Kotfire\SystemAlerts\Alert as Alert;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

class AddAlertCommand extends Command
{
    protected $name = 'alert';
    protected $description = 'Add new Alert';
    protected $systemAlert;

    public function __construct(SystemAlert $systemAlert)
    {
        $this->systemAlert = $systemAlert;

        parent::__construct();
    }

    public function fire()
    {
        $this->systemAlert->addAlert($this->argument('message'), Alert::INFO_TYPE);
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['message', InputArgument::REQUIRED, 'Alert message']
        ];
    }
}
