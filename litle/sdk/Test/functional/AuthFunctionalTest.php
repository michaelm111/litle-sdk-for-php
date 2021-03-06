<?php
/*
 * Copyright (c) 2011 Litle & Co.
 *
 * Permission is hereby granted, free of charge, to any person
 * obtaining a copy of this software and associated documentation
 * files (the "Software"), to deal in the Software without
 * restriction, including without limitation the rights to use,
 * copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following
 * conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 * OTHER DEALINGS IN THE SOFTWARE.
 */
namespace litle\sdk\Test\functional;

use litle\sdk\LitleOnlineRequest;
use litle\sdk\XmlParser;

class AuthFunctionalTest extends \PHPUnit_Framework_TestCase
{
    public function test_simple_auth_with_card()
    {
        $hash_in = array('id' => 'id',
            'card' => array('type' => 'VI',
                'number' => '4100000000000000',
                'expDate' => '1213',
                'cardValidationNum' => '1213'),
            'id' => '1211',
            'orderId' => '22@33',
            'reportGroup' => 'Planets',
            'orderSource' => 'ecommerce',
            'amount' => '0');

        $initialize = new LitleOnlineRequest();
        $authorizationResponse = $initialize->authorizationRequest($hash_in);
        $response = XmlParser::getNode($authorizationResponse, 'response');
        $this->assertEquals('000', $response);
    }

    public function test_simple_auth_with_detail_tax()
    {
        $lineItemData = array(
            array('itemSequenceNumber' => '1',
                'itemDescription' => 'desc',
                'itemDescription' => 'flamethrower',
                'productCode' => '112233',
                'quantity' => '52',
                'unitOfMeasure' => 'pounds',
                'taxAmount' => '55'
            ),
            array('itemSequenceNumber' => '2', 'itemDescription' => 'desc2'));
        $detailTax = array(
            array('taxAmount' => '0', 'cardAcceptorTaxId' => '0'),
            array('taxAmount' => '1', 'cardAcceptorTaxId' => '1'));

        $hash_in = array('id' => 'id',
            'card' => array('type' => 'VI',
                'number' => '4100000000000000',
                'expDate' => '1213',
                'cardValidationNum' => '1213'),
            'id' => '1211',
            'orderId' => '22@33',
            'reportGroup' => 'Planets',
            'orderSource' => 'ecommerce',
            'amount' => '0',
            'enhancedData' => array('salesTax' => '123',
                'shippingAmount' => '123',
                'detailTax' => $detailTax,
                'lineItemData' => $lineItemData
            )
        );

        $initialize = new LitleOnlineRequest();
        $authorizationResponse = $initialize->authorizationRequest($hash_in);
        $response = XmlParser::getNode($authorizationResponse, 'response');
        $this->assertEquals('000', $response);
    }

    public function test_simple_auth_with_paypal()
    {
        $hash_in = array('id' => 'id',
            'paypal' => array("payerId" => '123@litle.com', "token" => '12321312',
                "transactionId" => '123123'),
            'id' => '1211',
            'orderId' => '2111',
            'reportGroup' => 'Planets',
            'orderSource' => 'ecommerce',
            'amount' => '123');

        $initialize = new LitleOnlineRequest();
        $authorizationResponse = $initialize->authorizationRequest($hash_in);
        $message = XmlParser::getNode($authorizationResponse, 'message');
        $this->assertEquals('Approved', $message);
    }

    public function test_simple_auth_with_litleTxnId()
    {
        $hash_in = array('id' => 'id', 'reportGroup' => 'planets', 'litleTxnId' => '1234567891234567891');

        $initialize = new LitleOnlineRequest();
        $authorizationResponse = $initialize->authorizationRequest($hash_in);
        $message = XmlParser::getAttribute($authorizationResponse, 'litleOnlineResponse', 'message');
        $this->assertEquals("Valid Format", $message);
    }

    public function test_illegal_orderSource()
    {
        $hash_in = array('id' => 'id',
            'paypal' => array("payerId" => '123', "token" => '12321312',
                "transactionId" => '123123'),
            'id' => '1211',
            'orderId' => '2111',
            'reportGroup' => 'Planets',
            'orderSource' => 'notecommerce',
            'amount' => '123');

        $initialize = new LitleOnlineRequest();
        $authorizationResponse = $initialize->authorizationRequest($hash_in);
        $message = XmlParser::getAttribute($authorizationResponse, 'litleOnlineResponse', 'message');
        $this->assertRegExp('/Error validating xml data against the schema/', $message);
    }

    public function test_fields_out_of_order()
    {
        $hash_in = array('id' => 'id',
            'paypal' => array("payerId" => '123', "token" => '12321312',
                "transactionId" => '123123'),
            'id' => '1211',
            'orderId' => '2111',
            'reportGroup' => 'Planets',
            'orderSource' => 'ecommerce',
            'amount' => '123');

        $initialize = new LitleOnlineRequest();
        $authorizationResponse = $initialize->authorizationRequest($hash_in);
        $message = XmlParser::getNode($authorizationResponse, 'message');
        $this->assertEquals('Approved', $message);
    }

    public function test_invalid_field()
    {
        $hash_in = array('id' => 'id',
            'paypal' => array("payerId" => '123', "token" => '12321312',
                "transactionId" => '123123'),
            'id' => '1211',
            'orderId' => '2111',
            'nonexistant' => 'novalue',
            'reportGroup' => 'Planets',
            'orderSource' => 'ecommerce',
            'amount' => '123');

        $initialize = new LitleOnlineRequest();
        $authorizationResponse = $initialize->authorizationRequest($hash_in);
        $message = XmlParser::getNode($authorizationResponse, 'message');
        $this->assertEquals('Approved', $message);
    }

    public function test_pos_missing_field()
    {
        $hash_in = array('id' => 'id',
            'reportGroup' => 'Planets',
            'orderId' => '12344',
            'amount' => '106',
            'orderSource' => 'ecommerce',
            'pos' => array('entryMode' => '123'),
            'card' => array(
                'type' => 'VI',
                'number' => '4100000000000000',
                'expDate' => '1210'));
        $litleTest = new LitleOnlineRequest();
        $this->setExpectedException('InvalidArgumentException', 'Missing Required Field: /capability/');
        $retOb = $litleTest->authorizationRequest($hash_in);
    }

    public function test_auth_with_applepay()
    {
        $hash_in = array('id' => 'id',
            'applepay' => array(
                'data' => 'string data here',
                'header' => array('applicationData' => '454657413164',
                    'ephemeralPublicKey' => '1',
                    'publicKeyHash' => '1234',
                    'transactionId' => '12345'),
                'signature' => 'signature',
                'version' => 'version 1'),
            'orderId' => '2111',
            'orderSource' => 'ecommerce',
            'id' => '654',
            'amount' => '1000');

        $initialize = new LitleOnlineRequest();
        $authorizationResponse = $initialize->authorizationRequest($hash_in);
        $response = XmlParser::getNode($authorizationResponse, 'response');
        $this->assertEquals('000', $response);
    }

    public function test_auth_with_applepay_issuer_unavailable()
    {
        $hash_in = array('id' => 'id',
            'applepay' => array(
                'data' => 'string data here',
                'header' => array('applicationData' => '454657413164',
                    'ephemeralPublicKey' => '1',
                    'publicKeyHash' => '1234',
                    'transactionId' => '12345'),
                'signature' => 'signature',
                'version' => 'version 1'),
            'orderId' => '2111',
            'orderSource' => 'ecommerce',
            'id' => '654',
            'amount' => '1101');

        $initialize = new LitleOnlineRequest();
        $authorizationResponse = $initialize->authorizationRequest($hash_in);
        $response = XmlParser::getNode($authorizationResponse, 'response');
        $this->assertEquals('101', $response);
    }

    public function test_auth_with_applepay_approved()
    {
        $hash_in = array('id' => 'id',
            'applepay' => array(
                'data' => 'string data here',
                'header' => array('applicationData' => '454657413164',
                    'ephemeralPublicKey' => '1',
                    'publicKeyHash' => '1234',
                    'transactionId' => '12345'),
                'signature' => 'signature',
                'version' => 'version 1'),
            'orderId' => '2111',
            'orderSource' => 'ecommerce',
            'id' => '654',
            'amount' => '12312');

        $initialize = new LitleOnlineRequest();
        $authorizationResponse = $initialize->authorizationRequest($hash_in);
        $response = XmlParser::getNode($authorizationResponse, 'response');
        $this->assertEquals('000', $response);
    }

    public function test_simple_auth_with_card_processingType_originalNetworkTransactionId_originalTransactionAmount()
    {
        $hash_in = array('id' => 'id',
            'card' => array('type' => 'VI',
                'number' => '4100000000000000',
                'expDate' => '1213',
                'cardValidationNum' => '1213'),
            'id' => '1211',
            'orderId' => '2111',
            'reportGroup' => 'Planets',
            'orderSource' => 'ecommerce',
            'amount' => '0',
            'processingType' => 'initialRecurring',
            'originalNetworkTransactionId' => 'abcdefghijklmnopqrstuvwxyz',
            'originalTransactionAmount' => '1000'
        );

        $initialize = new LitleOnlineRequest();
        $authorizationResponse = $initialize->authorizationRequest($hash_in);
        $response = XmlParser::getNode($authorizationResponse, 'response');
        $this->assertEquals('000', $response);
    }

    public function test_simple_auth_with_networkTransactionId()
    {
        $hash_in = array(
            'id' => 'id',
            'card' => array('type' => 'VI',
                'number' => '4100000000000000',
                'expDate' => '1213',
                'cardValidationNum' => '1213'),
            'id' => '1211',
            'orderId' => '2111',
            'reportGroup' => 'Planets',
            'orderSource' => 'ecommerce',
            'amount' => '0',
            'processingType' => 'initialRecurring',
            'originalNetworkTransactionId' => 'abcdefghijklmnopqrstuvwxyz',
            'originalTransactionAmount' => '1000'
        );

        $initialize = new LitleOnlineRequest();
        $authorizationResponse = $initialize->authorizationRequest($hash_in);
        $response = XmlParser::getNode($authorizationResponse, 'networkTransactionId');
        $this->assertEquals('63225578415568556365452427825', $response);
    }

    public function test_simple_auth_with_enhancedAuthResponse()
    {
        $hash_in = array(
            'id' => 'id',
            'card' => array(
                'type' => 'VI',
                'number' => '4100800000000000',
                'expDate' => '1213',
                'cardValidationNum' => '1213'

            ),
            'id' => '1211',
            'orderId' => '2111',
            'reportGroup' => 'Planets',
            'orderSource' => 'ecommerce',
            'amount' => '0',
            'processingType' => 'initialRecurring',
            'originalNetworkTransactionId' => 'abcdefghijklmnopqrstuvwxyz',
            'originalTransactionAmount' => '1000'
        );

        $initialize = new LitleOnlineRequest();
        $authorizationResponse = $initialize->authorizationRequest($hash_in);
        $endpoint = XmlParser::getNode($authorizationResponse, 'endpoint');
        $fieldValue = XmlParser::getNode($authorizationResponse, 'fieldValue');
        $fieldNumber = XmlParser::getAttribute($authorizationResponse, 'networkField', 'fieldNumber');
        $fieldName = XmlParser::getAttribute($authorizationResponse, 'networkField', 'fieldName');

        $this->assertEquals('visa', $endpoint);
        $this->assertEquals('135798642', $fieldValue);
        $this->assertEquals('4', $fieldNumber);
        $this->assertEquals('Transaction Amount', $fieldName);

    }

    public function test_simple_auth_with_card_pin()
    {
        $hash_in = array('id' => 'id',
            'card' => array('type' => 'VI',
                'number' => '4100000000000000',
                'expDate' => '1213',
                'cardValidationNum' => '1213',
                'pin' => '34'
            ),
            'id' => '1211',
            'orderId' => '2111',
            'reportGroup' => 'Planets',
            'orderSource' => 'ecommerce',
            'amount' => '0');

        $initialize = new LitleOnlineRequest();
        $authorizationResponse = $initialize->authorizationRequest($hash_in);
        $message = XmlParser::getAttribute($authorizationResponse, 'litleOnlineResponse', 'message');
        $this->assertRegExp('/Error validating xml data against the schema/', $message);

    }

    public function test_simple_auth_with_card_IdRestrictions()
    {
        $hash_in = array('id' => 'id',
            'card' => array('type' => 'VI',
                'number' => '4100000000000000',
                'expDate' => '1213',
                'cardValidationNum' => '1213'),
            'id' => '1234567890          1234561234567890',
            'orderId' => '2111',
            'reportGroup' => 'Planets',
            'orderSource' => 'ecommerce',
            'amount' => '0');

        $initialize = new LitleOnlineRequest();
        $authorizationResponse = $initialize->authorizationRequest($hash_in);
        $message = XmlParser::getAttribute($authorizationResponse, 'litleOnlineResponse', 'message');
        $this->assertRegExp('/Error validating xml data against the schema/', $message);
    }


}
