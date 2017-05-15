<?php

/*
 * Payline driver for the Omnipay PHP payment processing library
 *
 * @link      https://github.com/ck-developer/omnipay-payline
 * @package   omnipay-payline
 * @license   MIT
 * @copyright Copyright (c) 2016 - 217 Claude Khedhiri <claude@khedhiri.com>
 */

namespace Omnipay\Payline;

use Omnipay\Payline\Message\Direct;

/**
 * DirectGateway.
 *
 * @author Claude Khedhiri <claude@khedhiri.com>
 */
class DirectGateway extends AbstractGateway
{
    public function getName()
    {
        return 'Payline Direct';
    }

    public function getEndpoint()
    {
        return ($this->getTestMode() ? $this->testEndpoint : $this->liveEndpoint).'/DirectPaymentAPI';
    }

    /**
     * @param array $parameters
     *
     * @return \Omnipay\Payline\Message\Direct\AuthorizeRequest
     */
    public function authorize(array $parameters = [])
    {
        return $this->createRequest(Direct\AuthorizeRequest::class, $parameters);
    }

    /**
     * @param array $parameters
     *
     * @return \Omnipay\Payline\Message\Direct\PurchaseRequest
     */
    public function purchase(array $parameters = [])
    {
        return $this->createRequest(Direct\PurchaseRequest::class, $parameters);
    }

    /**
     * @param array $parameters
     *
     * @return \Omnipay\Payline\Message\Direct\CaptureRequest
     */
    public function capture(array $parameters = [])
    {
        return $this->createRequest(Direct\CaptureRequest::class, $parameters);
    }

    /**
     * @param array $parameters
     *
     * @return \Omnipay\Payline\Message\Direct\RefundRequest
     */
    public function refund(array $parameters = [])
    {
        return $this->createRequest(Direct\RefundRequest::class, $parameters);
    }

    /**
     * @param array $parameters
     *
     * @return \Omnipay\Payline\Message\Direct\CreditRequest
     */
    public function credit(array $parameters = [])
    {
        return $this->createRequest(Direct\CreditRequest::class, $parameters);
    }
}
