<?php
namespace ShoppingFeed\Ip\Ipv4;

use ShoppingFeed\Ip\Exception\InvalidIpException;
use ShoppingFeed\Ip\Exception\InvalidNetworkMaskException;
use ShoppingFeed\Ip\NetmaskInterface;

class IpTest extends \PHPUnit\Framework\TestCase
{
    public function testProvidingAnInvalidIpv4AddressWillFail()
    {
        $this->expectException(InvalidIpException::class);
        $this->expectExceptionMessage('The given value is not a valid ip address');

        new Ip('foo');
    }

    public function testProvidingAnInvalidIpv4AddressNumberWillFail()
    {
        $this->expectException(InvalidIpException::class);
        $this->expectExceptionMessage('The given value is not a valid ip address');

        new Ip('127.0.0.256');
    }

    /**
     * @dataProvider validIpDataProvider
     */
    public function testProvidingAValidStringIpv4AddressWillSucceed($ipString, $ipInteger, $formatIpString)
    {
        $instance = new Ip($ipString);

        $this->assertInstanceOf(Ip::class, $instance);
        $this->assertSame($ipInteger, $instance->toInteger());
        $this->assertSame($formatIpString, $instance->toString());
    }

    /**
     * @dataProvider validIpDataProvider
     */
    public function testProvidingAValidIntegerIpv4AddressWillSucceed($_, $ipInteger, $formatIpString)
    {
        $instance = new Ip($ipInteger);

        $this->assertInstanceOf(Ip::class, $instance);
        $this->assertSame($ipInteger, $instance->toInteger());
        $this->assertSame($formatIpString, $instance->toString());
    }

    /**
     * @dataProvider validIpDataProvider
     */
    public function testStaticIp2LongReturnsValidResultsEvenWithPaddedParts($baseIp, $ipInteger)
    {
        $this->assertSame($ipInteger, Ip::ip2long($baseIp));
    }

    /**
     * @dataProvider invalidIpStringDataProvider
     */
    public function testIp2LongMethodOnlySupportsValidIpString($badIp)
    {
        $this->assertFalse(Ip::ip2long($badIp));
    }

    public function testIp2LongSupportsValidIp()
    {
        $this->assertSame(0xdeadd00d, Ip::ip2long('222.173.208.13'));
        $this->assertSame(0xdeadd00d, Ip::ip2long(new Ip('222.173.208.13')));
    }

    /**
     * @dataProvider privateOrReservedIpDataProvider
     */
    public function testIsPublicMethodRetrunsFalseOnPrivateOrReservedIps($ipAddress)
    {
        $this->assertFalse((new Ip($ipAddress))->isPublicAddress());
    }

    /**
     * @dataProvider publicIpDataProvider
     */
    public function testAnyPublicAddressIsValidatedByIsPublicAddressMethod($ipAddress)
    {
        $this->assertTrue((new Ip($ipAddress))->isPublicAddress());
    }

    /**
     * @dataProvider networkAndBroadcastAddressDataProvider
     */
    public function testIsAddressableIpMethodIsFalseForNetworkAndBroadcastAddresses($address, $mask)
    {
        $netmask  = $this->createNetmaskMock($mask);
        $instance = new Ip($address);

        $this->assertFalse($instance->isAddressableAddress($netmask));
    }

    public function testInSingleOrTwoMachinesNetworkAllTheAddressesAreAddressable()
    {
        $instance = new Ip('127.0.0.0');

        $netmask  = $this->createNetmaskMock(0xfffffffe);
        $this->assertTrue($instance->isAddressableAddress($netmask));
        $netmask  = $this->createNetmaskMock(0xffffffff);
        $this->assertTrue($instance->isAddressableAddress($netmask));
    }

    public function testGetClassReturnsTheClassConstants()
    {
        $this->assertSame(Ip::CLASS_A, (new Ip('0.0.0.0'))->getClass());
        $this->assertSame(Ip::CLASS_A, (new Ip('127.255.255.255'))->getClass());
        $this->assertSame(Ip::CLASS_B, (new Ip('128.0.0.0'))->getClass());
        $this->assertSame(Ip::CLASS_B, (new Ip('191.255.255.255'))->getClass());
        $this->assertSame(Ip::CLASS_C, (new Ip('192.0.0.0'))->getClass());
        $this->assertSame(Ip::CLASS_C, (new Ip('223.255.255.255'))->getClass());
        $this->assertSame(Ip::CLASS_D, (new Ip('224.0.0.0'))->getClass());
        $this->assertSame(Ip::CLASS_D, (new Ip('239.255.255.255'))->getClass());
        $this->assertSame(Ip::CLASS_E, (new Ip('240.0.0.0'))->getClass());
        $this->assertSame(Ip::CLASS_E, (new Ip('255.255.255.255'))->getClass());
    }

    public function testBroadcastAddressIsTheLatestInTheIpRange()
    {
        $netmask = $this->createNetmaskMock(0xffff0000);

        $this->assertSame(0x7fffffff, (new Ip('127.255.0.5'))->getBroadcastAddress($netmask)->toInteger());
        $this->assertSame(0x7f00ffff, (new Ip('127.0.0.5'))->getBroadcastAddress($netmask)->toInteger());
        $this->assertSame(0xfeffffff, (new Ip('254.255.255.212'))->getBroadcastAddress($netmask)->toInteger());
    }
    
    public function testBroadcastAddressCannotBeComputedForTwoAddressesNetwork()
    {
        $this->expectException(InvalidNetworkMaskException::class);
        $this->expectExceptionCode(InvalidNetworkMaskException::NO_BROADCAST_ADDRESS);

        $ip = new Ip('1.2.3.4');
        $ip->getBroadcastAddress($this->createNetmaskMock(0xfffffffe));
    }

    public function testBroadcastAddressCannotBeComputedForOneAddressNetwork()
    {
        $this->expectException(InvalidNetworkMaskException::class);
        $this->expectExceptionCode(InvalidNetworkMaskException::NO_BROADCAST_ADDRESS);

        $ip = new Ip('1.2.3.4');
        $ip->getBroadcastAddress($this->createNetmaskMock(0xffffffff));
    }

    public function testNetworkAddressIsTheFirstInTheIpRange()
    {
        $netmask = $this->createNetmaskMock(0xffff0000);

        $this->assertSame(0x7fff0000, (new Ip('127.255.0.5'))->getNetworkAddress($netmask)->toInteger());
        $this->assertSame(0x7f000000, (new Ip('127.0.0.5'))->getNetworkAddress($netmask)->toInteger());
        $this->assertSame(0xfeff0000, (new Ip('254.255.255.212'))->getNetworkAddress($netmask)->toInteger());
    }

    public function testNetworkAddressCannotBeComputedForTwoAddressesNetwork()
    {
        $this->expectException(InvalidNetworkMaskException::class);
        $this->expectExceptionCode(InvalidNetworkMaskException::NO_NETWORK_ADDRESS);

        $ip = new Ip('1.2.3.4');
        $ip->getNetworkAddress($this->createNetmaskMock(0xfffffffe));
    }

    public function testNetworkAddressCannotBeComputedForOneAddressNetwork()
    {
        $this->expectException(InvalidNetworkMaskException::class);
        $this->expectExceptionCode(InvalidNetworkMaskException::NO_NETWORK_ADDRESS);

        $ip = new Ip('1.2.3.4');
        $ip->getNetworkAddress($this->createNetmaskMock(0xffffffff));
    }

    public function testIpCanBeBuiltFromAnyIp()
    {
        $ip = new Ip(0xdeadbeef);
        $this->assertEquals(0xdeadbeef, (new Ip($ip))->toInteger());
    }

    public function invalidIpStringDataProvider()
    {
        return [
            [null],
            [1],
            [new \stdClass()],
            [4.34],
            ['yolo'],
        ];
    }

    public function networkAndBroadcastAddressDataProvider()
    {
        return [
            ['0.0.0.0', 0x0],
            ['0.0.0.0', 0xff000000],
            ['0.255.255.255', 0xff000000],
            ['1.0.0.0', 0xff000000],
            ['1.255.255.255', 0xff000000],
            ['10.0.0.0', 0xff000000],
            ['1.255.255.255', 0xff000000],
            ['127.0.0.0', 0xff000000],
            ['127.255.255.255', 0xff000000],
            ['172.16.0.0', 0xffff0000],
            ['172.16.255.255', 0xffff0000],
            ['192.168.0.0', 0xffffff00],
            ['172.168.42.255', 0xffffff00],
            ['255.255.255.255', 0x0],
            ['1.2.3.4', 0x01020304],
            ['8.8.8.8', 0xdeadbeef],
        ];
    }
    
    public function validIpDataProvider()
    {
        return [
            ['0.00.0.000', 0x0, '0.0.0.0'],
            ['1.2.3.4', 0x01020304, '1.2.3.4'],
            ['8.8.8.8', 0x08080808, '8.8.8.8'],
            ['127.0.0.0', 0x7f000000, '127.0.0.0'],
            ['169.254.00.008', 0xa9fe0008, '169.254.0.8'],
            ['191.204.145.154', 0xbfcc919a, '191.204.145.154'],
            ['192.168.085.042', 0xc0a8552a, '192.168.85.42'],
            ['228.18.7.255', 0xe41207ff, '228.18.7.255'],
            ['245.87.123.5', 0xf5577b05, '245.87.123.5'],
            ['255.255.255.255', 0xffffffff, '255.255.255.255'],
        ];
    }

    public function publicIpDataProvider()
    {
        return [
            ['0.0.0.1'],
            ['0.255.255.255'],
            ['8.4.4.8'],
            ['8.8.8.8'],
            ['9.255.255.255'],
            ['11.0.0.0'],
            ['172.15.255.255'],
            ['172.32.0.0'],
            ['169.253.255.255'],
            ['169.255.0.0'],
            ['172.32.0.0'],
            ['192.167.255.255'],
            ['192.169.0.0'],
            ['223.255.255.255'],
        ];
    }

    public function privateOrReservedIpDataProvider()
    {
        return [
            ['0.0.0.0'],
            ['10.0.0.0'],
            ['10.123.46.12'],
            ['10.255.255.255'],
            ['127.0.0.0'],
            ['127.42.85.145'],
            ['127.255.255.255'],
            ['169.254.0.0'],
            ['169.254.42.49'],
            ['169.254.255.255'],
            ['172.16.0.0'],
            ['172.21.231.16'],
            ['172.31.255.255'],
            ['192.168.0.0'],
            ['192.168.42.10'],
            ['192.168.255.255'],
            ['224.0.0.0'],
            ['235.42.1.0'],
            ['239.255.255.255'],
            ['240.0.0.0'],
            ['245.45.74.248'],
            ['255.255.255.255'],
        ];
    }

    private function createNetmaskMock($value)
    {
        $netmask = $this->createMock(NetmaskInterface::class);
        $netmask
            ->expects($this->any())
            ->method('toInteger')
            ->willReturn($value);

        return $netmask;
    }
}
