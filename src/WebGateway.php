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

use Omnipay\Payline\Message\Web;

/**
 * Class WebGateway.
 *
 * @author Claude Khedhiri <claude@khedhiri.com>
 */
class WebGateway extends AbstractGateway
{
    public function getName()
    {
        return 'Payline Web';
    }

    public function supportsRefund()
    {
        return false;
    }

    public function supportsUpdateCard()
    {
        return false;
    }

    public function supportsDeleteCard()
    {
        return false;
    }

    /**
     * @return string
     */
    public function getEndpoint()
    {
        return ($this->getTestMode() ? $this->testEndpoint : $this->liveEndpoint).'/WebPaymentAPI.wsdl';
    }

    /**
     * @return \Omnipay\Payline\Message\Web\AuthorizeRequest
     */
    public function authorize(array $parameters = [])
    {
        return $this->createRequest(Web\AuthorizeRequest::class, $parameters);
    }

    /**
     * @param array $parameters
     *
     * @return \Omnipay\Payline\Message\Web\CaptureRequest
     */
    public function capture(array $parameters = [])
    {
        return $this->createRequest(Web\CaptureRequest::class, $parameters);
    }

    /**
     * @param array $parameters
     *
     * @return \Omnipay\Payline\Message\Web\CaptureRequest
     */
    public function purchase(array $parameters = [])
    {
        return $this->createRequest(Web\CaptureRequest::class, $parameters);
    }

    /**
     * @param array $parameters
     *
     * @return \Omnipay\Payline\Message\Web\CompleteAuthorizeRequest
     */
    public function completeAuthorize(array $parameters = [])
    {
        return $this->createRequest(Web\CompleteAuthorizeRequest::class, $parameters);
    }
}
