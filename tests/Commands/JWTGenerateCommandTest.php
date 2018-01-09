<?php

/*
 * This file is part of jwt-auth.
 *
 * (c) Sean D19sp <dinho19sp@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace D19sp\JWTAuth\Test;

use Illuminate\Foundation\Application;
use D19sp\JWTAuth\Commands\JWTGenerateCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Tester\CommandTester;

class JWTGenerateCommandTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->command = new JWTGenerateCommand();
        $this->tester = new CommandTester($this->command);
    }

    /** @test */
    public function it_shoud_generate_random_key()
    {
        // $app = new Application();

        // $app['path.base'] = '';

        // $this->command->setLaravel($app);

        // $this->runCommand($this->command);
    }

    protected function runCommand($command, $input = [])
    {
        return $command->run(new ArrayInput($input), new NullOutput);
    }
}
