<?php
// =begin
// Copyright (c) 2011 Litle & Co.

// Permission is hereby granted, free of charge, to any person
// obtaining a copy of this software and associated documentation
// files (the "Software"), to deal in the Software without
// restriction, including without limitation the rights to use,
// copy, modify, merge, publish, distribute, sublicense, and/or sell
// copies of the Software, and to permit persons to whom the
// Software is furnished to do so, subject to the following
// conditions:

// The above copyright notice and this permission notice shall be
// included in all copies or substantial portions of the Software.

// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
// EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
// OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
// NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
// HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
// WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
// FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
// OTHER DEALINGS IN THE SOFTWARE.
// =end

require_once("../../simpletest/autorun.php");
require_once('../../simpletest/unit_tester.php');
require_once realpath(dirname(__FILE__)) . '/../../lib/LitleOnline.php';

class echeckRedeopist_FunctionalTest extends UnitTestCase
{
	function test_simple_echeckRedepoist()
	{
		$hash_in = array(
			'litleTxnId'=>'123456789012345678',
			'amount'=>'123');

		$initilaize = &new LitleOnlineRequest();
		$echeckRedepositResponse = $initilaize->echeckRedepositRequest($hash_in);
		$response = Xml_parser::get_node($echeckRedepositResponse,'response');
		$this->assertEqual('000',$response);
	}

	function test_echeckredepoist_with_echeck()
	{
		$hash_in = array(
      'amount'=>'123456',
      'verify'=>'true',
      'orderId'=>'12345',
		'litleTxnId'=>'123456789012345678',
      'orderSource'=>'ecommerce',
      'echeck' => array('accType'=>'Checking','accNum'=>'12345657890','routingNum'=>'123456789','checkNum'=>'123455'),
      'billToAddress'=>array('name'=>'Bob','city'=>'lowell','state'=>'MA','email'=>'litle.com'));
	
		$initilaize = &new LitleOnlineRequest();
		$echeckRedepositResponse = $initilaize->echeckRedepositRequest($hash_in);
		$response = Xml_parser::get_node($echeckRedepositResponse,'response');
		$this->assertEqual('000',$response);
	}
	
	function test_echeckredepoist_with_echecktoken()
	{
		$hash_in = array(
	      'amount'=>'123456',
	      'verify'=>'true',
	      'orderId'=>'12345',
		'litleTxnId'=>'123456789012345678',
	      'orderSource'=>'ecommerce',
	      	'echeckToken' => array('accType'=>'Checking','litleToken'=>'1234565789012','routingNum'=>'123456789','checkNum'=>'123455'),
	      'billToAddress'=>array('name'=>'Bob','city'=>'lowell','state'=>'MA','email'=>'litle.com'));
	
		$initilaize = &new LitleOnlineRequest();
		$echeckRedepositResponse = $initilaize->echeckRedepositRequest($hash_in);
		$response = Xml_parser::get_node($echeckRedepositResponse,'response');
		$this->assertEqual('000',$response);
	}
	function test_echeckredepoist_missing_litleTxnId()
	{
		$hash_in = array(
		      'amount'=>'123456',
		      'verify'=>'true',
		      'orderId'=>'12345',
		      'orderSource'=>'ecommerce',
		      	'echeckToken' => array('accType'=>'Checking','litleToken'=>'1234565789012','routingNum'=>'123456789','checkNum'=>'123455'),
		      'billToAddress'=>array('name'=>'Bob','city'=>'lowell','state'=>'MA','email'=>'litle.com'));
	
		$litleTest = &new LitleOnlineRequest();
		$this->expectException(new Exception("Missing Required Field: /litleTxnId/"));
		$retOb = $litleTest->echeckRedepositRequest($hash_in);
	}

}
?>