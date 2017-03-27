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
use Omnipay\Payline\Exception\CustomerDetailsNotProvidedException;

/**
 * CreditRequest.
 *
 * @method CreditResponse send()
 *
 * @author Claude Khedhiri <claude@khedhiri.com>
 */
class CreditRequest extends AuthorizeRequest
{
    /**
     * @return string
     */
    public function getPaymentMethod()
    {
        return 'doCredit';
    }

    /**
     * @return array
     */
    public function getData()
    {
        $data = $this->getBaseData();

        $data['payment'] = [
            'amount' => $this->getAmount()->getInteger(),
            'currency' => $this->getAmount()->getCurrency()->getNumeric(),
            'action' => 422,
            'mode' => $this->getPaymentMode() ?: 'CPT',
        ];

        if ($this->getContractNumber()) {
            $data['payment']['contractNumber'] = $this->getContractNumber();
        }

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

        return $data;
    }

    /**
     * @param \stdClass $data
     *
     * @return CreditResponse
     */
    protected function createResponse($data)
    {
        return $this->response = new CreditResponse($this, $data);
    }
}
