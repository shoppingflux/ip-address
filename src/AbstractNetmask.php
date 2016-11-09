<?php
namespace ShoppingFeed\Ip;

use ShoppingFeed\Ip\Exception\InvalidNetworkMaskException;

abstract class AbstractNetmask implements NetmaskInterface
{
    /**
     * @var int
     */
    private $mask;

    /**
     * @param int|string $mask
     */
    final public function __construct($mask)
    {
        $class = static::class;

        if ($mask instanceof $class) {
            /** @var $mask AbstractNetmask */
            $this->mask = $mask->mask;
            return;
        }

        if (! $this->isValid($mask)) {
            throw new InvalidNetworkMaskException(
                'The given value is not a valid network mask',
                InvalidNetworkMaskException::INVALID_VALUE
            );
        }

        $this->mask = $this->prepareMask($mask);
    }

    /**
     * Convert a cidr string to a valid mask
     *
     * @param string $cidr
     * @return int
     */
    protected function cidrToInteger($cidr)
    {
        $cidr = (int) ltrim($cidr, '/');

        // '0xffffffff &' is a fix for 64 bits systems with ~
        return 0xffffffff & ~((1 << ($this->getMaskSize() * 8 - $cidr)) - 1);
    }

    /**
     * @inheritdoc
     */
    final public function toInteger()
    {
        return $this->mask;
    }

    /**
     * @inheritdoc
     */
    final public function toCidr()
    {
        $value   = $this->toInteger();
        $size    = 8 * $this->getMaskSize();
        $started = false;
        $result  = 0;

        for ($i = 0; $i < $size; ++$i) {
            if ($value & 1) {
                $started = true;
                $result++;
            } elseif ($started) {
                throw new InvalidNetworkMaskException(
                    'The mask can\'t be converted to CIDR as it is not standard.',
                    InvalidNetworkMaskException::NO_CIDR_VALUE
                );
            }

            $value >>= 1;
        }

        return '/'.$result;
    }

    /**
     * @inheritdoc
     */
    final public function __toString()
    {
        return $this->toString();
    }

    /**
     * Check if the given mask is valid
     *
     * @param mixed $mask
     * @return bool
     */
    abstract protected function isValid($mask);

    /**
     * In this method the netmask has to be casted to integer to be stored
     *
     * @param mixed $mask Any valid netmask
     * @return int
     */
    abstract protected function prepareMask($mask);

    /**
     * Get the mask size in bytes
     *
     * @return int
     */
    abstract protected function getMaskSize();
}
