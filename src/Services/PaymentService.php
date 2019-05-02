<?php
namespace App\Services;

use Exception;
use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;
use Psr\Log\LoggerAwareTrait;

/**
 * Class PaymentService
 */
class PaymentService
{
    use LoggerAwareTrait;

    const BASE_URL_API      = 'http://dev.yunogasai.site/api/v1';
    const BASE_URL_PURCHASE = 'http://dev.yunogasai.site/purchase';
    const TIMEOUT           = 2.0;

    /**
     * @var string
     */
    protected $clientID;

    /**
     * @var string
     */
    protected $clientSecret;

    /**
     * Constructor
     *
     * @param string $clientID
     * @param string $clientSecret
     */
    public function __construct($clientID, $clientSecret)
    {
        $this->clientID     = $clientID;
        $this->clientSecret = $clientSecret;
    }

    /**
     * @param array $values
     *
     * @return string
     * @throws Exception
     * @throws GuzzleException
     */
    public function getToken(array $values)
    {
        if (empty($values['transactionID'])
            || empty($values['price'])
            || empty($values['successURL'])
            || empty($values['failureURL'])
            || empty($values['webhookURL'])
        ) {
            throw new InvalidArgumentException('Missing values.');
        }

        $resp = $this->doRequest('POST', 'token', $values);
        if (empty($resp['token'])) {
            throw new Exception('Invalid response.');
        }

        return $resp['token'];
    }

    /**
     * @param string $token
     * @param string $code
     * @param string $price
     * @param string $transactionID
     *
     * @return array
     * @throws Exception
     * @throws GuzzleException
     */
    public function verify($token, $code, $price, $transactionID)
    {
        $resp = $this->doRequest('POST', 'verify', compact('token', 'code', 'price', 'transactionID'));
        if (!isset($resp['valid'])) {
            throw new Exception('Invalid response.');
        }

        return $resp['valid'];
    }

    /**
     * @param string           $method
     * @param string           $path
     * @param array|null       $body
     *
     * @return mixed
     * @throws GuzzleException
     */
    protected function doRequest($method, $path, $body = null)
    {
        $headers = [
            'X-Client-ID'     => $this->clientID,
            'X-Client-Secret' => $this->clientSecret,
            'Accept'          => 'application/json',
            'Content-Type'    => 'application/json'
        ];
        $options = [
            'headers' => $headers,
            'body'    => json_encode($body)
        ];

        $url = $this->buildURL($path);
        $this->logger->debug($method . ': ' . $url, [$options]);

        $client = new Guzzle([
            'timeout' => self::TIMEOUT
        ]);
        $response = $client->request($method, $url, $options);

        return json_decode((string)$response->getBody(), true);
    }

    /**
     * @param string $path
     *
     * @return string
     */
    private function buildURL($path)
    {
        return sprintf('%s/%s', self::BASE_URL_API, $path);
    }
}
