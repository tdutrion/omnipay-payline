<?php

namespace Omnipay\Payline\Exception;

final class MerchantIdNotProvidedException extends InvalidRequestException
{
    public function __construct()
    {
        parent::__construct(
            'The merchant ID ("merchantId") has not been provided in the Payline gateway configuration.'
        );
    }
}
