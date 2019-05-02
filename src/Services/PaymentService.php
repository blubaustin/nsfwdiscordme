<?php
namespace App\Services;

use Exception;
use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerAwareTrait;

/**
 * Class PaymentService
 */
class PaymentService
{
    use LoggerAwareTrait;

    const BASE_URL_API      = 'http://dev.yunogasai.site/api/v1';
    const BASE_URL_REDIRECT = 'http://dev.yunogasai.site/purchase';
    const TIMEOUT           = 2.0;

    /**
     * @param array $details
     *
     * @return string
     * @throws Exception
     * @throws GuzzleException
     */
    public function getRedirectURL(array $details)
    {
        $resp = $this->doRequest('POST', 'token', $details);
        if (empty($resp['token'])) {
            throw new Exception('Invalid response.');
        }

        return sprintf('%s/%s', self::BASE_URL_REDIRECT, $resp['token']);
    }

    /**
     * @param string $token
     * @param string $code
     *
     * @return array
     * @throws Exception
     * @throws GuzzleException
     */
    public function getDetails($token, $code)
    {
        $resp = $this->doRequest('POST', 'verify', compact('token', 'code'));
        if (!isset($resp['success'])) {
            throw new Exception('Invalid response.');
        }

        return $resp;
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
            'Accept'       => 'application/json',
            'Content-Type' => 'application/json'
        ];
        $options = [
            'headers' => $headers,
            'body'    => json_encode($body)
        ];

        $url = $this->buildURL($path);
        $this->logger->debug($method . ': ' . $url, [$headers]);

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
