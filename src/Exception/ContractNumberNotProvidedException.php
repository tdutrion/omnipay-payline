<?php

namespace Omnipay\Payline\Exception;


final class ContractNumberNotProvidedException extends InvalidRequestException
{
    public function __construct()
    {
        parent::__construct(
            'The contract number ("contractNumber") has not been provided in the Payline gateway configuration.'
        );
    }
}
