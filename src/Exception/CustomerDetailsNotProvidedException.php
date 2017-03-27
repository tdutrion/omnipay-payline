<?php

namespace Omnipay\Payline\Exception;

final class CustomerDetailsNotProvidedException extends InvalidRequestException
{
    public function __construct($type = null, $message = null)
    {
        if (!$message) {
            $message = '';
            if ($type) {
                $message .= ucfirst("{$type} ");
            }
            $message .= 'customer details not provided.';
        }
        parent::__construct($message);
    }
}
