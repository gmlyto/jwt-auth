<?php

/*
 * This file is part of jwt-auth.
 *
 * (c) Sean D19sp <dinho19sp@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace D19sp\JWTAuth\Test\Providers\User;

use Mockery;
use D19sp\JWTAuth\Providers\User\EloquentUserAdapter;

class EloquentUserAdapterTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->builder = Mockery::mock('Illuminate\Database\Query\Builder');
        $this->model = Mockery::mock('Illuminate\Database\Eloquent\Model');
        $this->user = new EloquentUserAdapter($this->model);
    }

    public function tearDown()
    {
        Mockery::close();
    }

    /** @test */
    public function it_should_return_the_user_if_found()
    {
        $this->builder->shouldReceive('first')->once()->withNoArgs()->andReturn((object) ['id' => 1]);
        $this->model->shouldReceive('where')->once()->with('foo', 'bar')->andReturn($this->builder);

        $user = $this->user->getBy('foo', 'bar');

        $this->assertEquals(1, $user->id);
    }
}
