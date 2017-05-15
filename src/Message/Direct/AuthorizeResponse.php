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

use Omnipay\Payline\Message\AbstractResponse;

/**
 * AuthorizeResponse.
 *
 * @author Claude Khedhiri <claude@khedhiri.com>
 */
class AuthorizeResponse extends AbstractResponse
{
    /**
     * @return bool
     */
    public function isRedirect()
    {
        return false;
    }

    /**
     * @return stdClass
     */
    public function getTransaction()
    {
        return $this->data->transaction;
    }

    public function getTransactionId()
    {
        return $this->data->transaction->id;
    }

    /**
     * @return bool
     */
    public function isPossibleFraud()
    {
        return intval($this->data->transaction->isPossibleFraud) !== 0;
    }

    /**
     * @return bool
     */
    public function isDuplicated()
    {
        return intval($this->data->transaction->isDuplicated) !== 0;
    }

    /**
     * @return bool
     */
    public function isCompleted()
    {
        return '00000' == $this->data->result->code;
    }

    /**
     * @return string
     */
    public function getFraudResult()
    {
        return (string) $this->data->transaction->fraudResult;
    }

    /**
     * @return string
     */
    public function getExplanation()
    {
        return (string) $this->data->transaction->explanation;
    }

    /**
     * @return int
     */
    public function getScore()
    {
        return (int) $this->data->transaction->score;
    }

    /**
     * @return bool
     */
    public function isThreeDSecure()
    {
        return
            isset($this->data->transaction->threeDSecure) &&
            strtolower($this->data->transaction->threeDSecure) === 'y';
    }

    /**
     * @return string
     */
    public function getCardNumber()
    {
        return (string) $this->data->card->number;
    }

    /**
     * @return string
     */
    public function getCardType()
    {
        return (string) $this->data->card->type;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getCardExpiration()
    {
        return new \DateTimeImmutable($this->data->card->expiration);
    }

    /**
     * @return string
     */
    public function getCardToken()
    {
        return (string) $this->data->card->token;
    }

    /**
     * @return string
     */
    public function getExtendedCardCountry()
    {
        return (string) $this->data->extendedCard->country;
    }

    /**
     * @return string
     */
    public function isExtendedCardCvd()
    {
        return
            isset($this->data->transaction->threeDSecure) &&
            strtolower($this->data->transaction->threeDSecure) === 'y';
    }

    /**
     * @return string
     */
    public function getExtendedCardBank()
    {
        return (string) $this->data->extendedCard->bank;
    }

    /**
     * @return string
     */
    public function getExtendedCardType()
    {
        return (string) $this->data->extendedCard->type;
    }

    /**
     * @return string
     */
    public function getExtendedCardNetwork()
    {
        return (string) $this->data->extendedCard->network;
    }

    /**
     * @return string
     */
    public function getExtendedCardProduct()
    {
        return (string) $this->data->extendedCard->product;
    }

    /**
     * @return string
     */
    public function getAuthorizationNumber()
    {
        return (string) $this->data->authorization->number;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getAuthorizationDate()
    {
        return new \DateTimeImmutable($this->data->authorization->date);
    }

    /**
     * @return string
     */
    public function getPrivateDataList()
    {
        return (string) $this->data->authorization->number;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return 'authorisation';
    }
}
