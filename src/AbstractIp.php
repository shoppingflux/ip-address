<?php
namespace ShoppingFeed\Ip;

use ShoppingFeed\Ip\Exception\InvalidIpException;

abstract class AbstractIp implements IpInterface
{
    /**
     * @var int
     */
    private $address;

    /**
     * @param int|string $address
     */
    final public function __construct($address)
    {
        $class = static::class;

        if ($address instanceof $class) {
            /** @var $address AbstractIp */
            $this->address = $address->address;
            return;
        }

        if (! $this->isValid($address)) {
            throw new InvalidIpException(
                'The given value is not a valid ip address'
            );
        }

        $this->address = $this->prepareAddress($address);
    }

    /**
     * @inheritdoc
     */
    final public function toInteger()
    {
        return $this->address;
    }

    /**
     * @inheritdoc
     */
    final public function __toString()
    {
        return $this->toString();
    }

    /**
     * Check if the given address is valid
     *
     * @param mixed $address
     * @return bool
     */
    abstract protected function isValid($address);

    /**
     * In this method the address has to be casted to integer to be stored
     *
     * @param mixed $address Any valid address
     * @return int
     */
    abstract protected function prepareAddress($address);
}
