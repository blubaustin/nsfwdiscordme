<?php
namespace App\Security;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Class NonceComponent
 */
class NonceStorage implements NonceStorageInterface
{
    /**
     * @var SessionInterface
     */
    protected $session;

    /**
     * Constructor
     *
     * @param SessionInterface $session
     */
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * {@inheritDoc}
     */
    public function get($key)
    {
        return $this->session->get($this->getSessionKey($key));
    }

    /**
     * {@inheritDoc}
     */
    public function set($key, $value)
    {
        $this->session->set($this->getSessionKey($key), $value);
    }

    /**
     * {@inheritDoc}
     */
    public function remove($key)
    {
        return $this->session->remove($this->getSessionKey($key));
    }

    /**
     * {@inheritDoc}
     */
    public function has($key)
    {
        return $this->session->has($this->getSessionKey($key));
    }

    /**
     * {@inheritDoc}
     */
    public function valid($key, $value, $remove = true)
    {
        $valid = $this->get($key) === $value;
        if ($remove) {
            $this->remove($key);
        }

        return $valid;
    }

    /**
     * @param string $key
     *
     * @return string
     */
    private function getSessionKey($key)
    {
        return "nonce.${key}";
    }
}
