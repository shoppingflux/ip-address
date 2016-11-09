<?php
namespace ShoppingFeed\Ip\Ipv4;

use ShoppingFeed\Ip\IpInterface;
use ShoppingFeed\Ip\NetworkInterface;

final class Network implements NetworkInterface
{
    /**
     * @var Ip The network address
     */
    private $address;

    /**
     * @var Netmask
     */
    private $mask;

    /**
     * @param Ip $address
     * @param Netmask $netmask
     */
    public function __construct(Ip $address, Netmask $netmask)
    {
        // Ensure that we have a network address
        $this->address = new Ip($address->toInteger() & $netmask->toInteger());
        $this->mask    = $netmask;
    }

    /**
     * @inheritdoc
     */
    public function isInNetwork(IpInterface $ip, IpInterface ...$otherIps)
    {
        array_unshift($otherIps, $ip);

        foreach ($otherIps as $ip) {
            if (($ip->toInteger() & $this->mask->toInteger()) !== $this->address->toInteger()) {
                return false;
            }
        }

        return true;
    }
}
