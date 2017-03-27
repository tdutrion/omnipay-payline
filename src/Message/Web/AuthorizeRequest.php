<?php

/*
 * Payline driver for the Omnipay PHP payment processing library
 *
 * @link      https://github.com/ck-developer/omnipay-payline
 * @package   omnipay-payline
 * @license   MIT
 * @copyright Copyright (c) 2016 - 217 Claude Khedhiri <claude@khedhiri.com>
 */

namespace Omnipay\Payline\Message\Web;
use League\Omnipay\Common\Customer;
use Omnipay\Payline\Exception\ContractNumberNotProvidedException;
use Omnipay\Payline\Exception\CustomerDetailsNotProvidedException;
use Omnipay\Payline\Exception\InvalidAmountException;

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
        return 'doWebPayment';
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
        return $this->getParameter('date');
    }

    /**
     * @param string $date
     *
     * @return $this
     */
    public function setDate($date)
    {
        if ($date instanceof \DateTime) {
            $date = $date->format('d/m/Y H:i');
        }

        return $this->setParameter('date', $date);
    }

    public function getData()
    {
        $data = $this->getBaseData();

        $data['payment'] = [
            'amount' => $this->getAmount()->getInteger(),
            'currency' => $this->getAmount()->getCurrency()->getNumeric(),
            'action' => 100,
            'mode' => $this->getPaymentMode() ?: 'CPT',
        ];

        if (!$this->getContractNumber()) {
            throw new ContractNumberNotProvidedException();
        }
        $data['payment']['contractNumber'] = $this->getContractNumber();

        $data['order'] = [
            'ref' => $this->getTransactionId(),
            'amount' => $this->getAmount()->getInteger(),
            'currency' => $this->getAmount()->getCurrency()->getNumeric(),
        ];

        if ($card = $this->getCard()) {
            $data['card'] = [
                'number' => $card->getNumber(),
                'type' => $card->getBrand(),
                'expirationDate' => $card->getExpiryDate('my'),
                'cvx' => $card->getCvv(),
            ];

            $customer = $card->getCustomer();
            if (!is_a($customer, Customer::class)) {
                throw new CustomerDetailsNotProvidedException();
            }
            $shippingCustomer = $card->getShippingCustomer();
            if (!is_a($customer, Customer::class)) {
                throw new CustomerDetailsNotProvidedException('shipping');
            }
            $billingCustomer = $card->getBillingCustomer();
            if (!is_a($customer, Customer::class)) {
                throw new CustomerDetailsNotProvidedException('billing');
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
                    'phone' => $shippingCustomer->getPhone(),
                    'phoneType' => $shippingCustomer->getPhoneExtension(),
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
                    'phone' => $billingCustomer->getPhone(),
                    'phoneType' => $billingCustomer->getPhoneExtension(),
                ],
            ];
        }

        $data['order']['date'] = $this->getDate() ?: date('d/m/Y H:i');

        if ($data['payment']['mode'] === 'NX') {
            $data['recurring'] = [
                'firstAmount' => $this->getAmount()->getInteger() / $this->getPaymentLeft(),
                'billingCycle' => $this->getPaymentCycle(),
                'billingLeft' => $this->getPaymentLeft(),
            ];
        }

        if ($items = $this->getItems()) {
            $data['order']['items'] = [];

            foreach ($items->getIterator() as $item) {
                array_push($data['order']['items'], [
                    'ref' => $item->getName(),
                    'price' => $item->getPrice(),
                    'quantity' => $item->getQuantity(),
                    'comment' => $item->getDescription(),
                ]);
            }
        }

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
