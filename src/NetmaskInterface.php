<?php
namespace ShoppingFeed\Ip;

interface NetmaskInterface
{
    /**
     * Get the integer representation of the mask
     *
     * @return int
     */
    public function toInteger();

    /**
     * Get the CIDR representation of a netmask
     *
     * @return string
     *
     * @throws \ShoppingFeed\Ip\Exception\InvalidNetworkMaskException if the mask can't be
     *          represented as a CIDR (example: 1.2.3.4)
     */
    public function toCidr();

    /**
     * Get the mask as string representation.
     * It should be on xxx.xxx.xxx.xxx form for ipv4 and an alias of toCIDR() for ipv6
     *
     * @return string
     */
    public function toString();

    /**
     * Alias of toString() method
     *
     * @return string
     */
    public function __toString();
}
