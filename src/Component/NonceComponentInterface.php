<?php
namespace App\Component;


/**
 * Saves security related values (known as nonces) to the user's session.
 */
interface NonceComponentInterface
{
    /**
     * @param string $key
     *
     * @return string|null
     */
    public function get($key);

    /**
     * @param string $key
     * @param string $value
     */
    public function set($key, $value);

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function remove($key);

    /**
     * @param string $key
     *
     * @return bool
     */
    public function has($key);
}
