<?php

use ASN1\Element;
use ASN1\Type\TimeType;


/**
 * @group decode
 */
class TimeTypeDecodeTest extends PHPUnit_Framework_TestCase
{
	public function testType() {
		$el = TimeType::fromDER("\x17\x0d" . "060102220405Z");
		$this->assertInstanceOf('ASN1\Type\TimeType', $el);
	}
	
	public function testValue() {
		$date = strtotime("Mon Jan 2 15:04:05 MST 2006");
		$el = TimeType::fromDER("\x17\x0d" . "060102220405Z");
		$this->assertEquals($date, $el->dateTime()
			->getTimestamp());
	}
	
	public function testExpectation() {
		$el = TimeType::fromDER("\x17\x0d" . "060102220405Z");
		$el->expectType(Element::TYPE_TIME);
	}
}
