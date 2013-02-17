<?php

include('../lib/util.php');

class TestUtil extends PHPUnit_Framework_TestCase {

	public function testToCurrency() {
		$this->assertEquals(to_currency(500), '$500.00');
		$this->assertEquals(to_currency(500.23), '$500.23');
		$this->assertEquals(to_currency(500.156), '$500.16');
		$this->assertEquals(to_currency(500000), '$500,000.00');
		$this->assertEquals(to_currency(5000000.1851), '$5,000,000.19');
	}

	public function testStrRepeatExt() {
		$this->assertEquals(str_repeat_ext('x', 5), 'xxxxx');
		$this->assertEquals(str_repeat_ext('z', 3), 'zzz');
		$this->assertEquals(str_repeat_ext('y', 8, '-'), 'y-y-y-y-y-y-y-y');
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testJsonNotExists() {
		//$this->setExpectedException('InvalidArgumentException');
		json_config('dummy.json');
	}

	public function testJsonConfig() {
		$json = json_config('test.json');
		$this->assertEquals($json['foo'], 'bar');
		$this->assertEquals($json['value'], 5);
		$this->assertEquals($json['arr'][1], 9);
		$this->assertEquals($json['obj']['mem'], 'twelve');
	}

	public function testTitleCase() {
		$this->assertEquals(title_case('tHiS sTUff'), 'This Stuff');
	}

	public function testCreateToken() {
		$this->assertRegExp('/^[a-zA-Z0-9_]{40}$/', create_token());
	}

	public function testNeatTrim() {
		$this->assertEquals(
			neat_trim("The quick brown fox jumped over", 16),
			"The quick brown ..."
		);
		$this->assertEquals(
			neat_trim("The quick brown fox jumped over", 21, ' [more]'),
			"The quick brown fox jumped [more]"
		);
	}
}
