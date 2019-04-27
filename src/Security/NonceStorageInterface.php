<?php
namespace App\Security;


/**
 * Saves security related values (known as nonces) to the user's session.
 */
interface NonceStorageInterface
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

    /**
     * Returns whether the given nonce key value is valid and removes the nonce from storage
     *
     * @param string $key
     * @param string $value
     * @param bool   $remove
     *
     * @return bool
     */
    public function valid($key, $value, $remove = true);
}
