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

use Omnipay\Payline\Message\AbstractRequest;
use DateTime;

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
        if ($date instanceof \DateTime) {
            $date = $date->format('d/m/Y H:i');
        }

        return $this->setParameter('date', $date);
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
            'action' => 100,
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
            'title' => $customer->getTitle(),
            'firstName' => $customer->getFirstName(),
            'lastName' => $customer->getLastName(),
            'email' => $customer->getEmail(),
            'shippingAdress' => array(
                'title' => $customer->getTitle(),
                'name' => $customer->getName(),
                'firstName' => $customer->getFirstName(),
                'lastName' => $customer->getLastName(),
                'street1' => $customer->getAddress1(),
                'street2' => $customer->getAddress2(),
                'cityName' => $customer->getCity(),
                'zipCode' => $customer->getPostcode(),
                'state' => $customer->getState(),
                'country' => $customer->getCountry(),
                'phone' => $customer->getPhone(),
            ),
            'billingAddress' => array(
                'title' => $customer->getTitle(),
                'name' => $customer->getName(),
                'firstName' => $customer->getFirstName(),
                'lastName' => $customer->getLastName(),
                'street1' => $customer->getAddress1(),
                'street2' => $customer->getAddress2(),
                'cityName' => $customer->getCity(),
                'zipCode' => $customer->getPostcode(),
                'state' => $customer->getState(),
                'country' => $customer->getCountry(),
                'phone' => $customer->getPhone(),
            ),
        );
        // Omnipay-common < 2.5 do not have the following methods
        if ( method_exists($card, 'getShippingPhoneExtension') ) {
            $data['buyer']['shippingAdress']['phoneType'] =
                $customer->getPhoneExtension();
            $data['buyer']['billingAddress']['phoneType'] =
                $customer->getPhoneExtension();
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
