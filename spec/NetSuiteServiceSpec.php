<?php

namespace spec\NetSuite;

use NetSuite\NetSuiteService;
use PhpSpec\ObjectBehavior;

class NetSuiteServiceSpec extends ObjectBehavior
{
    function let(\SoapClient $client)
    {
        $config = require __DIR__.'/../samples/config.php';

        $this->beConstructedWith($config, [], $client);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(NetSuiteService::class);
    }
}
