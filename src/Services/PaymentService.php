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

    const TIMEOUT = 2.0;

    /**
     * @var string
     */
    protected $clientID;

    /**
     * @var string
     */
    protected $clientSecret;

    /**
     * @var string
     */
    protected $baseURL;

    /**
     * Constructor
     *
     * @param string $clientID
     * @param string $clientSecret
     * @param string $baseURL
     */
    public function __construct($clientID, $clientSecret, $baseURL = 'https://yunogasai.site')
    {
        $this->clientID     = $clientID;
        $this->clientSecret = $clientSecret;
        $this->baseURL      = rtrim($baseURL, '/');
    }

    /**
     * @param string $path
     *
     * @return string
     */
    public function getURL($path)
    {
        return sprintf('%s/api/v1/%s', $this->baseURL, $path);
    }

    /**
     * @param string $token
     *
     * @return string
     */
    public function getPurchaseURL($token)
    {
        return sprintf('%s/purchase/%s', $this->baseURL, $token);
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
            || empty($values['description'])
            || empty($values['successURL'])
            || empty($values['cancelURL'])
            || empty($values['failureURL'])
            || empty($values['webhookURL'])
        ) {
            throw new InvalidArgumentException('Missing values.');
        }

        $resp = $this->doRequest('POST', $this->getURL('token'), $values);
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
        $body = compact('token', 'code', 'price', 'transactionID');
        $resp = $this->doRequest('POST', $this->getURL('verify'), $body);
        if (!isset($resp['valid'])) {
            throw new Exception('Invalid response.');
        }

        return $resp['valid'];
    }

    /**
     * @param string     $method
     * @param string     $url
     * @param array|null $body
     *
     * @return mixed
     * @throws GuzzleException
     */
    protected function doRequest($method, $url, $body = null)
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

        $this->logger->debug($method . ': ' . $url, [$options]);

        $client = new Guzzle([
            'timeout' => self::TIMEOUT
        ]);
        $response = $client->request($method, $url, $options);

        return json_decode((string)$response->getBody(), true);
    }
}
