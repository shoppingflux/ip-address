<?php
namespace ShoppingFeed\Ip\Ipv4;

use ShoppingFeed\Ip\Exception\InvalidNetworkMaskException;

class NetmaskTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider ipv4CIDRDataProvider
     */
    public function testCIDRFormatFromMaskIsCorrect($mask, $cidr)
    {
        $instance = new Netmask($mask);
        $this->assertSame($cidr, $instance->toCidr());
    }

    /**
     * @dataProvider ipv4CIDRDataProvider
     */
    public function testMaskFormatFromCIDRIsCorrect($mask, $cidr)
    {
        $instance = new Netmask($cidr);
        $this->assertSame($mask, $instance->toInteger());
    }

    public function testCIDRIsNotSupportedOnNotCompatibleMasks()
    {
        $this->expectException(InvalidNetworkMaskException::class);
        $this->expectExceptionCode(InvalidNetworkMaskException::NO_CIDR_VALUE);

        $mask = new Netmask(0xfefefefe);
        $mask->toCidr();
    }

    public function testInvalidStringIsFailing()
    {
        $this->expectException(InvalidNetworkMaskException::class);
        $this->expectExceptionCode(InvalidNetworkMaskException::INVALID_VALUE);

        new Netmask('foo');
    }

    public function testMaskCanBeBuiltFromAnotherInstance()
    {
        $mask = new Netmask(0xdeadbabe);
        $this->assertEquals(0xdeadbabe, (new Netmask($mask))->toInteger());
    }

    public function testMaskCanBeCastedToString()
    {
        $this->assertSame('255.254.255.254', (string) new Netmask(0xfffefffe));
    }

    public function ipv4CIDRDataProvider()
    {
        return [
            [0, '/0'],
            [0x80000000, '/1'],
            [0xc0000000, '/2'],
            [0xe0000000, '/3'],
            [0xf0000000, '/4'],
            [0xf8000000, '/5'],
            [0xfc000000, '/6'],
            [0xfe000000, '/7'],
            [0xff000000, '/8'],
            [0xff800000, '/9'],
            [0xffc00000, '/10'],
            [0xffe00000, '/11'],
            [0xfff00000, '/12'],
            [0xfff80000, '/13'],
            [0xfffc0000, '/14'],
            [0xfffe0000, '/15'],
            [0xffff0000, '/16'],
            [0xffff8000, '/17'],
            [0xffffc000, '/18'],
            [0xffffe000, '/19'],
            [0xfffff000, '/20'],
            [0xfffff800, '/21'],
            [0xfffffc00, '/22'],
            [0xfffffe00, '/23'],
            [0xffffff00, '/24'],
            [0xffffff80, '/25'],
            [0xffffffc0, '/26'],
            [0xffffffe0, '/27'],
            [0xfffffff0, '/28'],
            [0xfffffff8, '/29'],
            [0xfffffffc, '/30'],
            [0xfffffffe, '/31'],
            [0xffffffff, '/32'],
        ];
    }
}
