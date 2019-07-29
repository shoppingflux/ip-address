<?php
namespace ShoppingFeed\Ip\Ipv4;

use ShoppingFeed\Ip\Ipv4\Ip;
use ShoppingFeed\Ip\Ipv4\Netmask;
use ShoppingFeed\Ip\Ipv4\Network;

class NetworkTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider networkAndAddressesDataProvider
     */
    public function testIsInSameNetworkProcessesTwoIpsComparingWithNetmask($ip, $netmaskInteger, $expectedResult)
    {
        $baseIp   = new Ip('127.52.45.32');
        $netmask  = new Netmask($netmaskInteger);
        $instance = new Network($baseIp, $netmask);

        $this->assertSame(
            $expectedResult,
            $instance->isInNetwork(new Ip($ip)),
            sprintf(
                'Failed asserting that %s %s in the same network than %s with netmask 0x%x',
                $baseIp->toString(),
                $expectedResult ? 'is' : 'is not',
                $ip,
                $netmaskInteger
            )
        );
    }

    public function networkAndAddressesDataProvider()
    {
        return [
            ['127.0.0.1', 0xff000000, true],
            ['127.0.0.1', 0xffff0000, false],
            ['127.0.0.1', 0xffffff00, false],
            ['127.0.0.1', 0xffffffff, false],
            ['127.0.0.1', 0xdeadbeef, false],
            ['94.116.109.48', 0xff000000, false],
            ['94.116.109.48', 0xffff0000, false],
            ['94.116.109.48', 0xffffff00, false],
            ['94.116.109.48', 0xffffffff, false],
            ['94.116.109.48', 0xdeadbeef, true],
            ['127.52.45.90', 0xff000000, true],
            ['127.52.45.90', 0xffff0000, true],
            ['127.52.45.90', 0xffffff00, true],
            ['127.52.45.90', 0xffffffff, false],
            ['127.52.45.90', 0xdeadbeef, false],
            ['127.52.156.42', 0xff000000, true],
            ['127.52.156.42', 0xffff0000, true],
            ['127.52.156.42', 0xffffff00, false],
            ['127.52.156.42', 0xffffffff, false],
            ['127.52.156.42', 0xdeadbeef, false],
            ['127.254.45.1', 0xff000000, true],
            ['127.254.45.1', 0xffff0000, false],
            ['127.254.45.1', 0xffffff00, false],
            ['127.254.45.1', 0xffffffff, false],
            ['127.254.45.1', 0xff00ff00, true],
        ];
    }
}
