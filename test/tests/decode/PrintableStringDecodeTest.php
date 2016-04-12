<?php

use ASN1\Type\Primitive\PrintableString;


/**
 * @group decode
 */
class PrintableStringDecodeTest extends PHPUnit_Framework_TestCase
{
	public function testType() {
		$el = PrintableString::fromDER("\x13\x0");
		$this->assertInstanceOf('ASN1\Type\Primitive\PrintableString', $el);
	}
	
	public function testValue() {
		$str = "Hello World.";
		$el = PrintableString::fromDER("\x13\x0c$str");
		$this->assertEquals($str, $el->str());
	}
	
	/**
	 * @expectedException ASN1\Exception\DecodeException
	 */
	public function testInvalidValue() {
		$str = "Hello World!";
		PrintableString::fromDER("\x13\x0c$str");
	}
}
