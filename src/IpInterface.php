<?php
namespace ShoppingFeed\Ip;

interface IpInterface
{
    /**
     * Check that the current IP is a public address of A, B or C class.
     *
     * @return bool Whether the current IP is public or not
     */
    public function isPublicAddress();

    /**
     * Check that the current IP is addressable is the network defined by the netmask argument
     *
     * @param NetmaskInterface $netmask
     * @return bool
     */
    public function isAddressableAddress(NetmaskInterface $netmask);

    /**
     * Alias of toString(), the object must can be casted as a string
     *
     * @return string
     */
    public function __toString();

    /**
     * Get the ip as a string
     *
     * @return string
     */
    public function toString();

    /**
     * @return int The IP represented as an integer. It can be a gmp int for ipv
     */
    public function toInteger();

    /**
     * Return the handled IP version.
     *
     * @return int
     */
    public function getVersion();
}
