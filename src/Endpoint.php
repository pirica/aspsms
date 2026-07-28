<?php

namespace jblond\aspsms;


/**
 * Class Endpoint
 * Endpoints can be found at https://webapi.aspsms.com/index.html
 * There are more endpoints to the API, but I did not require them.
 * @package jblond\aspsms
 */
class Endpoint
{
    /**
     * @var Client
     */
    protected Client $client;

    /**
     * @var string
     */
    protected string $baseurl = 'https://webapi.aspsms.com';

    /**
     * @var string
     */
    private string $userKey;

    /**
     * @var string
     */
    private string $password;

    /**
     * @var string
     */
    private string $sender;

    /**
     * @var string
     */
    private string $affiliateId = '';

    /**
     * Endpoint constructor.
     */
    public function __construct()
    {
        $this->client = new Client();
    }

    /**
     * @param string $userKey
     * @param string $password
     * @param string $sender
     */
    public function setCredentials(string $userKey, string $password, string $sender): void
    {
        $this->userKey = $userKey;
        $this->password = $password;
        $this->sender = $sender;
    }

    /**
     * @param string $affiliateId
     */
    public function setAffiliateId(string $affiliateId): void
    {
        $this->affiliateId = $affiliateId;
    }

    /**
     * @return bool|string
     */
    public function getCredits(): bool|string
    {
        return $this->client->get(
            $this->baseurl . '/ASPSMSCredits?UserKey=' . $this->userKey . '&Password=' . $this->password
        );
    }

    /**
     * @param string $message
     * @param array $recipients
     * @param string $when Y-m-dTH:i:sP or date("c", timestamp)
     * @param string $notifyUrl URL that will be called when a message is delivered instantly. The submitted TransactionReferenceNumber will be added to the URL.
     * @param string $notDeliveredUrl URL that will be connected when a message is not delivered. The submitted TransactionReferenceNumber will be added to the URL.
     * @param callable|null $errorHandler Optional callback invoked with the raw response when the request was not successful.
     * @return bool|string
     */
    public function sendSMS(
        string $message,
        array $recipients,
        string $when,
        string $notifyUrl = '',
        string $notDeliveredUrl = '',
        ?callable $errorHandler = null
    ): bool|string
    {

        $who = [];
        foreach ($recipients as $recipient) {
            $who[]['PhoneNumber'] =  $recipient;
        }
        $data = [
            'Operation' => 'SendTextSMS',
            'Recipients' => $who,
            'DeferredDeliveryTime' => $when, //"2021-06-30T10:18:07+01:00",
            'MessageData' => $message,
            'Originator' => $this->sender,
        ];
        if ($this->affiliateId !== '') {
            $data['AffiliateId'] = $this->affiliateId;
        }
        if ($notifyUrl !== '') {
            $data['URLDeliveryNotification'] = $notifyUrl;
        }
        if ($notDeliveredUrl !== '') {
            $data['URLNonDeliveryNotification'] = $notDeliveredUrl;
        }
        return $this->client->post(
            $this->baseurl . '/ASPSMSSendSMS?UserKey=' . $this->userKey . '&Password=' . $this->password,
            $data,
            $errorHandler
        );
    }

    /**
     * @return mixed
     */
    public function getTrafficStat(): mixed
    {
        return json_decode($this->client->get(
            $this->baseurl .
            '/TrafficStat01/Get?UserKey=' . $this->userKey . '&Password=' . $this->password . '&CalculateCredits=true'
        ), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @param int $year
     * @param int $month
     * @param int $day
     * @return mixed
     */
    public function getSendingStat(int $year = 0, int $month = 0, int $day = 0): mixed
    {
        $url = '';
        if ($year !== 0) {
            $url .= '&year=' . $year;
        }
        if ($month !== 0) {
            $url .= '&month=' . $month;
        }
        if ($day !== 0) {
            $url .= '&day=' . $day;
        }
        return json_decode($this->client->get(
            $this->baseurl .
            '/SendingStat01/Get?UserKey=' . $this->userKey . '&Password=' . $this->password . $url
        ), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @return mixed
     */
    public function getStats(): mixed
    {
        return json_decode($this->client->get(
            $this->baseurl .
            '/MSISDNDLRStat/Get?UserKey=' . $this->userKey . '&Password=' . $this->password
        ), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @return mixed
     */
    public function getPhoneNumbers(): mixed
    {
        return json_decode($this->client->get(
            $this->baseurl . '/PhoneNumbers/Read?UserKey=' . $this->userKey . '&Password=' . $this->password
        ), true, 512, JSON_THROW_ON_ERROR);
    }
}
