<?php

declare(strict_types=1);

namespace BitWasp\Buffertools\Tests;

use BitWasp\Buffertools\Buffer;
use PHPUnit\Framework\TestCase;

class BufferTest extends TestCase
{
    public function test_buffer_debug()
    {
        $buffer = new Buffer('AAAA', 4);
        $debug = $buffer->__debugInfo();
        $this->assertTrue(isset($debug['buffer']));
        $this->assertTrue(isset($debug['size']));

        $str = $debug['buffer'];
        $this->assertEquals('0x', substr($str, 0, 2));
        $this->assertEquals('41414141', substr($str, 2));
    }

    public function test_create_empty_buffer()
    {
        $buffer = new Buffer;
        $this->assertInstanceOf(Buffer::class, $buffer);
        $this->assertEmpty($buffer->getBinary());
    }

    public function test_create_empty_hex_buffer()
    {
        $buffer = Buffer::hex();
        $this->assertInstanceOf(Buffer::class, $buffer);
        $this->assertEmpty($buffer->getBinary());
    }

    public function test_create_buffer()
    {
        $hex = '80000000';
        $buffer = Buffer::hex($hex);
        $this->assertInstanceOf(Buffer::class, $buffer);
        $this->assertNotEmpty($buffer->getBinary());
    }

    /**
     * @expectedException \Exception
     *
     * @expectedExceptionMessage Byte string exceeds maximum size
     */
    public function test_create_max_buffer_exceeded()
    {
        $lim = 4;
        Buffer::hex('4141414111', $lim);
    }

    public function test_create_hex_buffer()
    {
        $hex = '41414141';
        $buffer = Buffer::hex($hex);
        $this->assertInstanceOf(Buffer::class, $buffer);
        $this->assertNotEmpty($buffer->getBinary());
    }

    public function test_padding()
    {
        $buffer = Buffer::hex('41414141', 6);

        $this->assertEquals(4, $buffer->getInternalSize());
        $this->assertEquals(6, $buffer->getSize());
        $this->assertEquals('000041414141', $buffer->getHex());
    }

    public function test_serialize()
    {
        $hex = '41414141';
        $dec = gmp_strval(gmp_init($hex, 16), 10);
        $bin = pack('H*', $hex);
        $buffer = Buffer::hex($hex);

        // Check Binary
        $retBinary = $buffer->getBinary();
        $this->assertSame($bin, $retBinary);

        // Check Hex
        $this->assertSame($hex, $buffer->getHex());

        // Check Decimal
        $this->assertSame($dec, $buffer->getInt());
        $this->assertInstanceOf(\GMP::class, $buffer->getGmp());
    }

    public function test_get_size()
    {
        $this->assertEquals(1, Buffer::hex('41')->getSize());
        $this->assertEquals(4, Buffer::hex('41414141')->getSize());
        $this->assertEquals(4, Buffer::hex('41', 4)->getSize());
    }

    public function getIntVectors(): array
    {
        return [
            ['1',  '01', 1],
            ['1',  '01', null],
            ['20', '14', 1],
        ];
    }

    /**
     * @dataProvider getIntVectors
     *
     * @param  int|string  $int
     */
    public function test_int_construct($int, string $expectedHex, ?int $size = null)
    {
        $buffer = Buffer::int($int, $size);
        $this->assertEquals($expectedHex, $buffer->getHex());
    }

    public function getGmpVectors(): array
    {
        return [
            [gmp_init('0A', 16)],
            [gmp_init('237852977508946591877284351678975096651401224047304305322504192889595623579202', 10)],
        ];
    }

    /**
     * @dataProvider getGmpVectors
     */
    public function test_gmp_construction(\GMP $gmp)
    {
        $this->assertTrue(gmp_cmp($gmp, Buffer::gmp($gmp)->getGmp()) === 0);
    }

    public function test_gmp_construction_negative()
    {
        $gmp = gmp_init('-1234', 10);

        $this->expectException(\InvalidArgumentException::class);
        Buffer::gmp($gmp);
    }

    public function test_slice()
    {
        $a = Buffer::hex('11000011');
        $this->assertEquals('1100', $a->slice(0, 2)->getHex());
        $this->assertEquals('0011', $a->slice(2, 4)->getHex());

        $b = Buffer::hex('00111100');
        $this->assertEquals('0011', $b->slice(0, 2)->getHex());
        $this->assertEquals('1100', $b->slice(2, 4)->getHex());

        $c = Buffer::hex('111100', 4);
        $this->assertEquals('0011', $c->slice(0, 2)->getHex());
        $this->assertEquals('1100', $c->slice(2, 4)->getHex());
    }

    public function test_equals()
    {
        $first = Buffer::hex('ab');
        $second = Buffer::hex('ab');
        $firstExtraLong = Buffer::hex('ab', 10);
        $firstShort = new Buffer('', 0);
        $this->assertTrue($first->equals($second));
        $this->assertFalse($first->equals($firstExtraLong));
        $this->assertFalse($first->equals($firstExtraLong));
        $this->assertFalse($first->equals($firstShort));
    }
}
