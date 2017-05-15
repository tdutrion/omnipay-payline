<?php

/*
 * Payline driver for the Omnipay PHP payment processing library
 *
 * @link      https://github.com/ck-developer/omnipay-payline
 * @package   omnipay-payline
 * @license   MIT
 * @copyright Copyright (c) 2016 - 217 Claude Khedhiri <claude@khedhiri.com>
 */

namespace Omnipay\Payline\Message\Direct;

use League\Omnipay\Common\Customer;
use Omnipay\Payline\Exception\ContractNumberNotProvidedException;
use Omnipay\Payline\Exception\CustomerDetailsNotProvidedException;
use Omnipay\Payline\Message\AbstractRequest;
use \DateTime;

/**
 * AuthorizeRequest.
 *
 * @method AuthorizeResponse send()
 *
 * @author Claude Khedhiri <claude@khedhiri.com>
 */
class AuthorizeRequest extends AbstractRequest
{
    /**
     * @return bool
     */
    public function getPaymentMethod()
    {
        return 'doAuthorization';
    }

    /**
     * @return string
     */
    public function getPaymentMode()
    {
        return $this->getParameter('paymentMode');
    }

    /**
     * @param string $mode
     *
     * @return $this
     */
    public function setPaymentMode($mode)
    {
        return $this->setParameter('paymentMode', $mode);
    }

    /**
     * @return int
     */
    public function getPaymentCycle()
    {
        return $this->getParameter('paymentCycle');
    }

    /**
     * @param int $cycle
     *
     * @return $this
     */
    public function setPaymentCycle($cycle)
    {
        return $this->setParameter('paymentCycle', $cycle);
    }

    /**
     * @return int
     */
    public function getPaymentLeft()
    {
        return $this->getParameter('paymentLeft');
    }

    /**
     * @param int $left
     *
     * @return $this
     */
    public function setPaymentLeft($left)
    {
        return $this->setParameter('paymentLeft', $left);
    }

    /**
     * @return string
     */
    public function getDate()
    {
        return $this->getParameter('date') ?: date_create()->format('d/m/Y H:i');
    }

    /**
     * @param string $date
     *
     * @return $this
     */
    public function setDate($date)
    {
        if ($date instanceof DateTime) {
            $date = $date->format('d/m/Y H:i');
        }

        return $this->setParameter('date', $date);
    }

    /**
     * @return array
     * @throws ContractNumberNotProvidedException
     * @throws CustomerDetailsNotProvidedException
     */
    public function getData()
    {
        $data = $this->getBaseData();
        if (!$this->getContractNumber()) {
            throw new ContractNumberNotProvidedException();
        }
        $data['payment'] = [
            'amount' => $this->getAmount()->getInteger(),
            'currency' => $this->getAmount()->getCurrency()->getNumeric(),
            'action' => 100,
            'mode' => $this->getPaymentMode() ?: 'CPT',
            'contractNumber' => $this->getContractNumber(),
        ];

        $data['order'] = [
            'ref' => $this->getTransactionId(),
            'amount' => $this->getAmount()->getInteger(),
            'currency' => $this->getAmount()->getCurrency()->getNumeric(),
        ];

        $card = $this->getCard();
        $data['card'] = [
            'number' => $card->getNumber(),
            'type' => $card->getBrand(),
            'expirationDate' => $card->getExpiryDate('my'),
            'cvx' => $card->getCvv(),
        ];


        $customerSources = [
            [$card, 'getCustomer'],
            [$this, 'getCustomer'],
        ];
        do {
            $callable = array_shift($customerSources);
            /* @var $customer Customer|null */
            $customer = $callable();
        } while (!$customer && !empty($customerSources));

        if (!is_a($customer, Customer::class)) {
            throw new CustomerDetailsNotProvidedException();
        }

        $billingCustomerSources = [
            [$card, 'getBillingCustomer'],
            [$card, 'getCustomer'],
            [$this, 'getCustomer'],
        ];
        do {
            $callable = array_shift($billingCustomerSources);
            /* @var $billingCustomer Customer|null */
            $billingCustomer = $callable();
        } while (!$billingCustomer && !empty($billingCustomerSources));

        if (!is_a($billingCustomer, Customer::class)) {
            throw new CustomerDetailsNotProvidedException('billing');
        }

        $shippingCustomerSources = [
            [$card, 'getShippingCustomer'],
            [$card, 'getCustomer'],
            [$this, 'getCustomer'],
        ];
        do {
            $callable = array_shift($shippingCustomerSources);
            /* @var $shippingCustomer Customer|null */
            $shippingCustomer = $callable();
        } while (!$shippingCustomer && !empty($shippingCustomerSources));

        if (!is_a($shippingCustomer, Customer::class)) {
            throw new CustomerDetailsNotProvidedException('shipping');
        }

        $data['buyer'] = [
            'title' => $customer->getTitle(),
            'firstName' => $customer->getFirstName(),
            'lastName' => $customer->getLastName(),
            'email' => $customer->getEmail(),
            'shippingAdress' => [
                'title' => $shippingCustomer->getTitle(),
                'name' => $shippingCustomer->getName(),
                'firstName' => $shippingCustomer->getFirstName(),
                'lastName' => $shippingCustomer->getLastName(),
                'street1' => $shippingCustomer->getAddress1(),
                'street2' => $shippingCustomer->getAddress2(),
                'cityName' => $shippingCustomer->getCity(),
                'zipCode' => $shippingCustomer->getPostcode(),
                'state' => $shippingCustomer->getState(),
                'country' => $shippingCustomer->getCountry(),
                'phone' => $shippingCustomer->getPhone()
            ],
            'billingAddress' => [
                'title' => $billingCustomer->getTitle(),
                'name' => $billingCustomer->getName(),
                'firstName' => $billingCustomer->getFirstName(),
                'lastName' => $billingCustomer->getLastName(),
                'street1' => $billingCustomer->getAddress1(),
                'street2' => $billingCustomer->getAddress2(),
                'cityName' => $billingCustomer->getCity(),
                'zipCode' => $billingCustomer->getPostcode(),
                'state' => $billingCustomer->getState(),
                'country' => $billingCustomer->getCountry(),
                'phone' => $billingCustomer->getPhone()
            ],
        ];
        // Omnipay-common < 2.5 do not have the following methods
        if ( method_exists($card, 'getShippingPhoneExtension') ) {
            $data['buyer']['shippingAdress']['phoneType'] = $shippingCustomer->getPhoneExtension();
            $data['buyer']['billingAddress']['phoneType'] = $billingCustomer->getPhoneExtension();
        }

        $data['order']['date'] = $this->getDate();

        if ($data['payment']['mode'] === 'NX') {
            $data['recurring'] = array(
                'firstAmount' => $this->getAmount()->getInteger() / $this->getPaymentLeft(),
                'billingCycle' => $this->getPaymentCycle(),
                'billingLeft' => $this->getPaymentLeft(),
            );
        }

        if ($items = $this->getItems()) {
            $data['order']['items'] = array();

            foreach ($items->getIterator() as $item) {
                array_push($data['order']['items'], array(
                    'ref' => $item->getName(),
                    'price' => $item->getPrice(),
                    'quantity' => $item->getQuantity(),
                    'comment' => $item->getDescription(),
                ));
            }
        }

        if (!isset($data['privateDataList'])) {
            $data['privateDataList'] = [];
        }
        if ($orderId = $this->getParameter('orderId')) {
            $data['privateDataList'][] = ['key' => 'orderId', 'value' => $orderId];
        }
        $data['version'] = 5;

        return $data;
    }

    /**
     * @param \stdClass $data
     *
     * @return AuthorizeResponse
     */
    protected function createResponse($data)
    {
        return $this->response = new AuthorizeResponse($this, $data);
    }
}
