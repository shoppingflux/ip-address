<?php
namespace ShoppingFeed\Ip;

interface NetworkInterface
{
    /**
     * Check if one or more ip addresses are in the network
     *
     * @param IpInterface $ip
     * @param IpInterface[] ...$otherIps
     * @return bool
     */
    public function isInNetwork(IpInterface $ip, IpInterface ...$otherIps);
}
