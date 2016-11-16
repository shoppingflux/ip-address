<?php
namespace ShoppingFeed\Ip\Ipv4;

use ShoppingFeed\Ip\AbstractIp;
use ShoppingFeed\Ip\Exception\InvalidNetworkMaskException;
use ShoppingFeed\Ip\NetmaskInterface;

final class Ip extends AbstractIp
{
    /**
     * Pattern for validating 0-255 number part
     */
    const PART_PATTERN = '([01]?\d{1,2}|2([0-4]\d|5[0-5]))';

    /**
     * Full pattern that validates addresses from 0.0.0.0 to 255.255.255.255
     */
    const PATTERN = '/^(?P<ip>('.self::PART_PATTERN.'\.){3}'.self::PART_PATTERN.')$/';

    /**
     * Collection of all the local or reserved addresses of IPv4
     */
    const LOCAL_OR_RESERVED_NETWORKS = [
        0x00000000 => 0xffffffff, // Local any address 0.0.0.0/32
        0x0a000000 => 0xff000000, // Class A private network 10.0.0.0/8
        0x7f000000 => 0xff000000, // local loop 127.0.0.0/8
        0xa9fe0000 => 0xffff0000, // 'link local' addresses. Used when DHCP is not found in network. 169.254.0.0/16
        0xac100000 => 0xfff00000, // Class B private networks, validates 172.16-31.0.0/16
        0xc0a80000 => 0xffff0000, // Class C private networks 192.168.0-255.0/24
        0xe0000000 => 0xf0000000, // Class D (multicast)
        0xf0000000 => 0xf0000000, // Class E (reserved)
    ];

    /**
     * Constant that represents an A class IP address (0.0.0.0 - 127.255.255.255)
     * Networks with a default /8 netmask
     */
    const CLASS_A = 'A';

    /**
     * Constant that represents a B class IP address (128.0.0.0 - 191.255.255.255)
     * Networks with a default /16 netmask
     */
    const CLASS_B = 'B';

    /**
     * Constant that represents a C class IP address (192.0.0.0 - 223.255.255.255)
     * Networks with a default /24 netmask
     */
    const CLASS_C = 'C';

    /**
     * Constant that represents a D class IP address (224.0.0.0 - 239.255.255.255)
     * Addresses used for multicast
     */
    const CLASS_D = 'D';

    /**
     * Constant that represents a E class IP address (240.0.0.0 - 255.255.255.255)
     * Reserved for experimental purposes only
     */
    const CLASS_E = 'E';

    /**
     * @param mixed $address
     * @return bool Whether $address is a valid IPv4 address or not
     */
    protected function isValid($address)
    {
        return static::isValidIp($address);
    }

    /**
     * @inheritdoc
     */
    public function isPublicAddress()
    {
        foreach (static::LOCAL_OR_RESERVED_NETWORKS as $address => $netmask) {
            $networkAddress = new static($address);
            $network        = new Network($networkAddress, new Netmask($netmask));

            if ($network->isInNetwork($this)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function isAddressableAddress(NetmaskInterface $netmask)
    {
        $thisIp = $this->toInteger();

        try {
            $thisNetworkAddress   = $this->getNetworkAddress($netmask)->toInteger();
            $thisBroadcastAddress = $this->getBroadcastAddress($netmask)->toInteger();

            return $thisIp !== $thisNetworkAddress && $thisIp !== $thisBroadcastAddress;
        } catch (InvalidNetworkMaskException $_) {
            return true;
        }
    }

    /**
     * @inheritdoc
     */
    protected function prepareAddress($address)
    {
        if (is_int($address)) {
            return $address;
        }

        return static::ip2long((string) $address);
    }

    /**
     * @inheritdoc
     */
    public function toString()
    {
        return long2ip($this->toInteger());
    }

    /**
     * Get the network address of the network defined by the netmask
     *
     * @param NetmaskInterface $netmask
     * @return Ip The network address
     * @throws InvalidNetworkMaskException If the netmask is not valid
     */
    public function getNetworkAddress(NetmaskInterface $netmask)
    {
        /**
         * Networks with mask 255.255.255.255 or 255.255.255.254 are considered to have no
         * network and no broadcast as can only assign respectively 1 and 2 addresses.
         *
         * Here we have to check the two values independently, as in a 32bits context they are negative and in
         * 64 bits their are positive, so we can't use >= nor <=
         */
        if (in_array($netmask->toInteger(), [0xfffffffe, 0xffffffff])) {
            throw new InvalidNetworkMaskException(
                sprintf('A network with \'%s\' netmask doesn\'t have a network address', $netmask->toString()),
                InvalidNetworkMaskException::NO_NETWORK_ADDRESS
            );
        }

        return new Ip($this->toInteger() & $netmask->toInteger());
    }

    /**
     * Get the broadcast address of the network defined by the netmask
     *
     * @param NetmaskInterface $netmask
     * @return Ip The broadcast address
     * @throws InvalidNetworkMaskException If the netmask is not valid
     */
    public function getBroadcastAddress(NetmaskInterface $netmask)
    {
        /**
         * Networks with mask 255.255.255.255 or 255.255.255.254 are considered to have no
         * network and no broadcast as can only assign respectively 1 and 2 addresses.
         *
         * Here we have to check the two values independently, as in a 32bits context they are negative and in
         * 64 bits their are positive, so we can't use >= nor <=
         */
        if (in_array($netmask->toInteger(), [0xfffffffe, 0xffffffff])) {
            throw new InvalidNetworkMaskException(
                sprintf(
                    'A network with a netmask equal to \'%s\' doesn\'t have a broadcast address',
                    $netmask->toString()
                ),
                InvalidNetworkMaskException::NO_BROADCAST_ADDRESS
            );
        }

        // The 0xffffffff & x is a patch for 64 bits php version
        return new Ip($this->toInteger() | 0xffffffff & ~$netmask->toInteger());
    }

    /**
     * Get the IPv4 class letter
     *
     * @return string
     */
    public function getClass()
    {
        $ip = $this->toInteger();

        switch (true) {
            case $ip < 0x80000000:
                return static::CLASS_A;
            case $ip < 0xc0000000:
                return static::CLASS_B;
            case $ip < 0xe0000000:
                return static::CLASS_C;
            case $ip < 0xf0000000:
                return static::CLASS_D;
            default:
                return static::CLASS_E;
        }
    }

    /**
     * @param mixed $address
     * @return bool Whether the given value is a valid IPv4 or not
     */
    public static function isValidIp($address)
    {
        return is_int($address) && $address >= 0 && $address <= 0xffffffff ||
            is_string($address) && 0 !== preg_match(static::PATTERN, $address);
    }

    /**
     * Reimplementation of ip2long method with support of zero padded ip components.
     *
     * Example: 192.168.000.001
     *
     * @param string $ip
     * @return int
     */
    public static function ip2long($ip)
    {
        if (!is_string($ip) && !(is_object($ip) && method_exists($ip, '__toString'))) {
            return false;
        }

        $ip = (string) $ip;

        if (!static::isValidIp($ip)) {
            return false;
        }

        $ipLength    = strlen($ip);
        $ipLong      = 0;
        $currentByte = 3;
        $part        = '';

        for ($i = 0; $i <= $ipLength; ++$i) {
            if ($i === $ipLength || '.' === $ip[$i]) {
                $ipLong += ((int) $part) << (8 * $currentByte--);
                $part    = '';
                continue;
            }

            $part .= $ip[$i];
        }

        return $ipLong;
    }

    /**
     * Return the handled IP version.
     *
     * @return int
     */
    public function getVersion()
    {
        return 4;
    }
}
