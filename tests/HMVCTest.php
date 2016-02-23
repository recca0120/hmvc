<?php

use Recca0120\HMVC\Request;

class HMVCTest extends PHPUnit_Framework_TestCase
{
    protected $config;

    protected $panels;

    public function setUp()
    {
    }

    public function test_hmvc()
    {
        $request = new Request($uri);
    }
}
