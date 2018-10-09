<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class RulesTest extends TestCase
{
    // 中文正则表达式测试
    public function testRulesCN()
	{
		$p = "/^[a-zA-Z0-9\p{Han}]{1}[-a-zA-Z0-9_ \.\p{Han}]+$/u";

		$testStr1 = "中文";
		$this->assertEquals(TRUE, preg_match($p, $testStr1) > 0);	

		$testStr1 = "en";
		$this->assertEquals(TRUE, preg_match($p, $testStr1) > 0);	

		$testStr1 = "99中文";
		$this->assertEquals(TRUE, preg_match($p, $testStr1) > 0);	

		$testStr1 = "99 en中文._-";
		$this->assertEquals(TRUE, preg_match($p, $testStr1) > 0);	

		$testStr1 = ".99中文";
		$this->assertEquals(FALSE, preg_match($p, $testStr1) > 0);	

		$testStr1 = " 99中文";
		$this->assertEquals(FALSE, preg_match($p, $testStr1) > 0);	

		$testStr1 = "_99中文";
		$this->assertEquals(FALSE, preg_match($p, $testStr1) > 0);	
	}

    // 英文正则表达式测试
    public function testRulesEN()
	{
		$p = "/^[a-zA-Z0-9]{1}[-a-zA-Z0-9_ \.]+$/u";

		$testStr1 = "en";
		$this->assertEquals(TRUE, preg_match($p, $testStr1) > 0);	

		$testStr1 = "99";
		$this->assertEquals(TRUE, preg_match($p, $testStr1) > 0);	

		$testStr1 = "99 en._-";
		$this->assertEquals(TRUE, preg_match($p, $testStr1) > 0);	

		$testStr1 = ".99";
		$this->assertEquals(FALSE, preg_match($p, $testStr1) > 0);	

		$testStr1 = " 99";
		$this->assertEquals(FALSE, preg_match($p, $testStr1) > 0);	

		$testStr1 = "_99";
		$this->assertEquals(FALSE, preg_match($p, $testStr1) > 0);	

		$testStr1 = "中文";
		$this->assertEquals(FALSE, preg_match($p, $testStr1) > 0);	
	}

}
