<?php

/**
 *
 * This file is part of the Apix Project.
 *
 * (c) Franck Cassedanne <franck at ouarz.net>
 *
 * @license     http://opensource.org/licenses/BSD-3-Clause  New BSD License
 *
 */

namespace Apix\Log\Logger;

use Pushy;

class PushoverTest extends \PHPUnit_Framework_TestCase
{

    protected $client;
    protected $user;

    protected function setUp()
    {
        $this->client = new Pushy\Client('AAAAAAAAAAAAAAAAAAAAAAAAAAAAAA');
        $this->user = new Pushy\User('AAAAAAAAAAAAAAAAAAAAAAAAAAAAAA');
    }

    protected function tearDown()
    {
        unset($this->client);
        unset($this->user);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage User is not valid
     */
    public function testThrowsInvalidArgumentExceptionWhenNoValidUser()
    {
        new Pushover($this->client, $this->user);
    }


    public function testWriteIsCalled()
    {
        $mock = $this->getMockBuilder('Apix\Log\Logger\Pushover')
                     ->disableOriginalConstructor()
                     ->setMethods(array('write'))
                     ->getMock();

        $mock->expects($this->exactly(2))->method('write');

        $mock->info('Log me!');
        $mock->error('Log me too!');
    }

}
