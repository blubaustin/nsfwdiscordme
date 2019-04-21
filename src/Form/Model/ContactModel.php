<?php
namespace App\Form\Model;

/**
 * Class ContactModel
 */
class ContactModel
{
    /**
     * @var string
     */
    protected $name = '';

    /**
     * @var string
     */
    protected $email = '';

    /**
     * @var string
     */
    protected $subject = '';

    /**
     * @var string
     */
    protected $message = '';

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return ContactModel
     */
    public function setName(string $name): ContactModel
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     *
     * @return ContactModel
     */
    public function setEmail(string $email): ContactModel
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return string
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     *
     * @return ContactModel
     */
    public function setSubject(string $subject): ContactModel
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     *
     * @return ContactModel
     */
    public function setMessage(string $message): ContactModel
    {
        $this->message = $message;

        return $this;
    }
}
