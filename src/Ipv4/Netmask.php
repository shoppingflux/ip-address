<?php
namespace ShoppingFeed\Ip\Ipv4;

use ShoppingFeed\Ip\AbstractNetmask;

final class Netmask extends AbstractNetmask
{
    /**
     * Check if the given mask is valid
     *
     * @param mixed $mask
     * @return bool
     */
    protected function isValid($mask)
    {
        // The netmask has the same format as an ip address, but it can be a cidr too
        return Ip::isValidIp($mask) || is_string($mask) && 0 !== preg_match('/^\/([0-2]?\d|3[0-2])$/', $mask);
    }

    /**
     * In this method the netmask has to be casted to integer to be stored
     *
     * @param mixed $mask Any valid netmask
     * @return int
     */
    protected function prepareMask($mask)
    {
        if (is_int($mask)) {
            return $mask;
        } elseif ('/' === $mask[0]) {
            return $this->cidrToInteger($mask);
        }

        return Ip::ip2long($mask);
    }

    /**
     * Get the mask size in bytes
     *
     * @return int
     */
    protected function getMaskSize()
    {
        // IPv4 is based on 32 bits objects (address & mask), so a netmask is 4 bytes long
        return 4;
    }

    /**
     * Get the mask as string representation.
     * It should be on xxx.xxx.xxx.xxx form for ipv4 and an alias of toCIDR() for ipv6
     *
     * @return string
     */
    public function toString()
    {
        return long2ip($this->toInteger());
    }
}
