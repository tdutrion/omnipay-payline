<?php

namespace Omnipay\Payline\Exception;

final class AccessKeyNotProvidedException extends InvalidRequestException
{
    public function __construct()
    {
        parent::__construct(
            'The access key ("accessKey") has not been provided in the Payline gateway configuration.'
        );
    }
}
