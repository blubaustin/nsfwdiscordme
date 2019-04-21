<?php
namespace App\Services;

use Exception;
use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class RecaptchaService
 */
class RecaptchaService
{
    const BASE_URL = 'https://www.google.com/recaptcha/api/siteverify';

    /**
     * @var string
     */
    protected $secretKey;

    /**
     * Constructor
     *
     * @param string $secretKey
     */
    public function __construct($secretKey)
    {
        $this->secretKey = $secretKey;
    }

    /**
     * @param string $token
     *
     * @return bool
     * @throws Exception
     * @throws GuzzleException
     */
    public function verify($token)
    {
        $client = new Guzzle();
        $response = $client->request('POST', self::BASE_URL, [
            'form_params' => [
                'secret'   => $this->secretKey,
                'response' => $token
            ]
        ]);

        $json = json_decode((string)$response->getBody(), true);
        if (!isset($json['success'])) {
            throw new Exception('Invalid recaptcha response.');
        }

        return $json['success'];
    }
}
