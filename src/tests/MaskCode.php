<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use App\Models\Order;

class MaskCodeTest extends TestCase
{
    public function testToNumber()
	{
		$this->assertEquals(0, Order::newModelInstance()->toNumber('A00'));
		$this->assertEquals(111, Order::newModelInstance()->toNumber('B11'));
		$this->assertEquals(2599, Order::newModelInstance()->toNumber('Z99'));
	}

    public function testToCode()
	{
		$this->assertEquals('A00', Order::newModelInstance()->toCode(0));
		$this->assertEquals('B11', Order::newModelInstance()->toCode(111));
		$this->assertEquals('Z99', Order::newModelInstance()->toCode(2599));
	}
}
