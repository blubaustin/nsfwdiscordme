<?php
namespace App\Form\Model;

/**
 * Class AdminLoginModal
 */
class AdminLoginModal
{
    /**
     * @var string
     */
    protected $code;

    /**
     * @return string
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * @param string $code
     *
     * @return AdminLoginModal
     */
    public function setCode(string $code): AdminLoginModal
    {
        $this->code = $code;

        return $this;
    }
}
