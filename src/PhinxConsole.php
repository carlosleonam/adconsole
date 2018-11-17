<?php
/**
 * Created by PhpStorm.
 * User: Indev
 * Date: 17/11/18
 * Time: 14:18
 */

namespace App\Command;


use Symfony\Component\Console\Application;
use Phinx\Console\Command;
use App\Command\CreateModelsCommand;

class PhinxConsole extends Application
{

    public function __construct($version = null)
    {


        if ($version === null) {
            $composerConfig = json_decode(file_get_contents(dirname(__FILE__,2).'/composer.json'));
            $version = $composerConfig->version;
        }

        parent::__construct('Phinx  - https://phinx.org.', $version);
        $this->addCommands([
            new Command\Init(),
            new Command\Create(),
            new Command\Migrate(),
            new Command\Rollback(),
            new Command\Status(),
            new Command\Breakpoint(),
            new Command\Test(),
            new Command\SeedCreate(),
            new Command\SeedRun(),
            new CreateModelsCommand()
        ]);

    }



}