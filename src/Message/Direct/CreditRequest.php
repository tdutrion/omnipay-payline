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

        $data['payment'] = array(
            'amount' => $this->getAmount()->getInteger(),
            'currency' => $this->getAmount()->getCurrency()->getNumeric(),
            'action' => 422,
            'mode' => $this->getPaymentMode() ?: 'CPT',
        );

        if ($this->getContractNumber()) {
            $data['payment']['contractNumber'] = $this->getContractNumber();
        }

        $data['order'] = array(
            'ref' => $this->getTransactionId(),
            'amount' => $this->getAmount()->getInteger(),
            'currency' => $this->getAmount()->getCurrency()->getNumeric(),
        );

        $card = $this->getCard();
        $data['card'] = array(
            'number' => $card->getNumber(),
            'type' => $card->getBrand(),
            'expirationDate' => $card->getExpiryDate('my'),
            'cvx' => $card->getCvv(),
        );

        $data['buyer'] = array(
            'title' => $card->getCustomer()->getTitle(),
            'firstName' => $card->getCustomer()->getFirstName(),
            'lastName' => $card->getCustomer()->getLastName(),
            'email' => $card->getCustomer()->getEmail(),
            'shippingAdress' => array(
                'title' => $card->getShippingCustomer()->getTitle(),
                'name' => $card->getShippingCustomer()->getName(),
                'firstName' => $card->getShippingCustomer()->getFirstName(),
                'lastName' => $card->getShippingCustomer()->getLastName(),
                'street1' => $card->getShippingCustomer()->getAddress1(),
                'street2' => $card->getShippingCustomer()->getAddress2(),
                'cityName' => $card->getShippingCustomer()->getCity(),
                'zipCode' => $card->getShippingCustomer()->getPostcode(),
                'state' => $card->getShippingCustomer()->getState(),
                'country' => $card->getShippingCustomer()->getCountry(),
                'phone' => $card->getShippingCustomer()->getPhone(),
                'phoneType' => $card->getShippingCustomer()->getPhoneExtension(),
            ),
            'billingAddress' => array(
                'title' => $card->getBillingCustomer()->getTitle(),
                'name' => $card->getBillingCustomer()->getName(),
                'firstName' => $card->getBillingCustomer()->getFirstName(),
                'lastName' => $card->getBillingCustomer()->getLastName(),
                'street1' => $card->getBillingCustomer()->getAddress1(),
                'street2' => $card->getBillingCustomer()->getAddress2(),
                'cityName' => $card->getBillingCustomer()->getCity(),
                'zipCode' => $card->getBillingCustomer()->getPostcode(),
                'state' => $card->getBillingCustomer()->getState(),
                'country' => $card->getBillingCustomer()->getCountry(),
                'phone' => $card->getBillingCustomer()->getPhone(),
                'phoneType' => $card->getBillingCustomer()->getPhoneExtension(),
            ),
        );

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
