<?php
/**
 * First Data Connect Purchase Request
 */

namespace Omnipay\FirstData\Message;

use Omnipay\Common\Message\AbstractRequest;

/**
 * First Data Connect Purchase Request
 */
class PurchaseRequest extends AbstractRequest
{
    protected $liveEndpoint = 'https://www.ipg-online.com/connect/gateway/processing';
    protected $testEndpoint = 'https://test.ipg-online.com/connect/gateway/processing';

    protected function getDateTime()
    {
        return date("Y:m:d-H:i:s");
    }

    /**
     * Set Store ID
     *
     * Calls to the Connect Gateway API are secured with a store ID and
     * shared secret.
     *
     * @return PurchaseRequest provides a fluent interface
     */
    public function setStoreId($value)
    {
        return $this->setParameter('storeId', $value);
    }

    /**
     * Get Store ID
     *
     * Calls to the Connect Gateway API are secured with a store ID and
     * shared secret.
     *
     * @return string
     */
    public function getStoreId()
    {
        return $this->getParameter('storeId');
    }

    /**
     * Set Shared Secret
     *
     * Calls to the Connect Gateway API are secured with a store ID and
     * shared secret.
     *
     * @return PurchaseRequest provides a fluent interface
     */
    public function setSharedSecret($value)
    {
        return $this->setParameter('sharedSecret', $value);
    }

    /**
     * Get Shared Secret
     *
     * Calls to the Connect Gateway API are secured with a store ID and
     * shared secret.
     *
     * @return string
     */
    public function getSharedSecret()
    {
        return $this->getParameter('sharedSecret');
    }

    public function getData()
    {
        $this->validate('amount', 'card');

        $data = array();
        $data['storename'] = $this->getStoreId();
        $data['txntype'] = 'sale';
        $data['timezone'] = 'GMT';
        $data['chargetotal'] = $this->getAmount();
        $data['txndatetime'] = $this->getDateTime();
        $data['hash'] = $this->createHash($data['txndatetime'], $data['chargetotal']);
        $data['currency'] = $this->getCurrencyNumeric();
        $data['mode'] = 'payonly';
        $data['full_bypass'] = 'true';
        $data['oid'] = $this->getParameter('transactionId');

        // FIXME: This makes no sense.
        $this->getCard()->validate();

        $data['cardnumber'] = $this->getCard()->getNumber();
        $data['cvm'] = $this->getCard()->getCvv();
        $data['expmonth'] = $this->getCard()->getExpiryDate('m');
        $data['expyear'] = $this->getCard()->getExpiryDate('y');

        $data['responseSuccessURL'] = $this->getParameter('returnUrl');
        $data['responseFailURL'] = $this->getParameter('returnUrl');

        return $data;
    }

    /**
     * Returns a SHA-1 hash of the transaction data.
     *
     * @param $dateTime
     * @param $amount
     * @return string
     */
    public function createHash($dateTime, $amount)
    {
        $storeId = $this->getStoreId();
        $sharedSecret = $this->getSharedSecret();
        $currency = $this->getCurrencyNumeric();
        $stringToHash = $storeId . $dateTime . $amount . $currency . $sharedSecret;
        $ascii = bin2hex($stringToHash);

        return sha1($ascii);
    }

    public function sendData($data)
    {
        return $this->response = new PurchaseResponse($this, $data);
    }

    public function getEndpoint()
    {
        return $this->getTestMode() ? $this->testEndpoint : $this->liveEndpoint;
    }
}
