<?php 

namespace Kotfire\SystemAlerts\Commands;

use Kotfire\SystemAlerts\SystemAlert as SystemAlert;
use Kotfire\SystemAlerts\Alert as Alert;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

class DeleteAlertCommand extends Command
{
    protected $name = 'alert:delete';
    protected $description = 'Delete Alert';
    protected $systemAlert;

    public function __construct(SystemAlert $systemAlert)
    {
        $this->systemAlert = $systemAlert;

        parent::__construct();
    }

    public function fire()
    {
        if ($this->systemAlert->deleteAlert($this->argument('id'))) {
            $this->comment('Alert deleted');
        } else {
            $this->comment('Alert does not exists or cannot be deleted');
        }
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['id', InputArgument::REQUIRED, 'Alert ID']
        ];
    }
}
