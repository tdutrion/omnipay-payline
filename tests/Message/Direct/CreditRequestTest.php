<?php

/*
 * Payline driver for the Omnipay PHP payment processing library
 *
 * @link      https://github.com/ck-developer/omnipay-payline
 * @package   omnipay-payline
 * @license   MIT
 * @copyright Copyright (c) 2016 - 217 Claude Khedhiri <claude@khedhiri.com>
 */

namespace Omnipay\Payline\Test\Message\Direct;

use League\Omnipay\Common\CreditCard;
use League\Omnipay\Common\Customer;
use Omnipay\Payline\Test\Message\MessageTestCase;

/**
 * CreditRequestTest.
 *
 * @author Claude Khedhiri <claude@khedhiri.com>
 */
class CreditRequestTest extends MessageTestCase
{
    /**
     * @var \Omnipay\Payline\Message\Direct\AuthorizeRequest
     */
    private $request;

    public function setUp()
    {
        parent::setUp();

        $this->request = $this->instanceRequest('Omnipay\Payline\Message\Direct\CreditRequest');
    }

    public function testData()
    {
        $this->request->initialize(array(
            'contractNumber' => '1234567',
            'transactionid' => $ref = sprintf('ORDER_%s', rand(1, 100)),
            'amount' => '300.00',
            'currency' => 'EUR',
            'date' => $date = new \DateTime(),
            'card' => $card = new CreditCard(
                $this->getValidCard() + ['customer' => new Customer($this->getShippingCustomer())]
            ),
        ));

        $data = $this->request->getData();

        $this->assertEquals('1234567', $data['payment']['contractNumber']);

        $this->assertEquals(30000, $data['payment']['amount']);
        $this->assertEquals(978, $data['payment']['currency']);
        $this->assertEquals(422, $data['payment']['action']);
        $this->assertEquals('CPT', $data['payment']['mode']);

        $this->assertEquals(30000, $data['order']['amount']);
        $this->assertEquals(978, $data['order']['currency']);
        $this->assertEquals($date->format('d/m/Y H:i'), $data['order']['date']);

        $this->assertEquals($card->getNumber(), $data['card']['number']);
        $this->assertEquals($card->getBrand(), $data['card']['type']);
        $this->assertEquals($card->getExpiryDate('my'), $data['card']['expirationDate']);
        $this->assertEquals($card->getCvv(), $data['card']['cvx']);

        $this->assertEquals($card->getCustomer()->getTitle(), $data['buyer']['title']);
        $this->assertEquals($card->getCustomer()->getFirstName(), $data['buyer']['firstName']);
        $this->assertEquals($card->getCustomer()->getLastName(), $data['buyer']['lastName']);
        $this->assertEquals($card->getCustomer()->getEmail(), $data['buyer']['email']);

        $this->assertEquals($card->getShippingCustomer()->getTitle(), $data['buyer']['shippingAdress']['title']);
        $this->assertEquals($card->getShippingCustomer()->getName(), $data['buyer']['shippingAdress']['name']);
        $this->assertEquals($card->getShippingCustomer()->getFirstName(), $data['buyer']['shippingAdress']['firstName']);
        $this->assertEquals($card->getShippingCustomer()->getLastName(), $data['buyer']['shippingAdress']['lastName']);
        $this->assertEquals($card->getShippingCustomer()->getAddress1(), $data['buyer']['shippingAdress']['street1']);
        $this->assertEquals($card->getShippingCustomer()->getAddress2(), $data['buyer']['shippingAdress']['street2']);
        $this->assertEquals($card->getShippingCustomer()->getCity(), $data['buyer']['shippingAdress']['cityName']);
        $this->assertEquals($card->getShippingCustomer()->getPostcode(), $data['buyer']['shippingAdress']['zipCode']);
        $this->assertEquals($card->getShippingCustomer()->getState(), $data['buyer']['shippingAdress']['state']);
        $this->assertEquals($card->getShippingCustomer()->getCountry(), $data['buyer']['shippingAdress']['country']);
        $this->assertEquals($card->getShippingCustomer()->getPhone(), $data['buyer']['shippingAdress']['phone']);
        $this->assertEquals($card->getShippingCustomer()->getPhoneExtension(), $data['buyer']['shippingAdress']['phoneType']);

        $this->assertEquals($card->getBillingCustomer()->getTitle(), $data['buyer']['billingAddress']['title']);
        $this->assertEquals($card->getBillingCustomer()->getName(), $data['buyer']['billingAddress']['name']);
        $this->assertEquals($card->getBillingCustomer()->getFirstName(), $data['buyer']['billingAddress']['firstName']);
        $this->assertEquals($card->getBillingCustomer()->getLastName(), $data['buyer']['billingAddress']['lastName']);
        $this->assertEquals($card->getBillingCustomer()->getAddress1(), $data['buyer']['billingAddress']['street1']);
        $this->assertEquals($card->getBillingCustomer()->getAddress2(), $data['buyer']['billingAddress']['street2']);
        $this->assertEquals($card->getBillingCustomer()->getCity(), $data['buyer']['billingAddress']['cityName']);
        $this->assertEquals($card->getBillingCustomer()->getPostcode(), $data['buyer']['billingAddress']['zipCode']);
        $this->assertEquals($card->getBillingCustomer()->getState(), $data['buyer']['billingAddress']['state']);
        $this->assertEquals($card->getBillingCustomer()->getCountry(), $data['buyer']['billingAddress']['country']);
        $this->assertEquals($card->getBillingCustomer()->getPhone(), $data['buyer']['billingAddress']['phone']);
        $this->assertEquals($card->getBillingCustomer()->getPhoneExtension(), $data['buyer']['billingAddress']['phoneType']);
    }
}
