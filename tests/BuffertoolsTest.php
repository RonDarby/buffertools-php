<?php

declare(strict_types=1);

namespace BitWasp\Buffertools\Tests;

use BitWasp\Buffertools\Buffer;
use BitWasp\Buffertools\Buffertools;
use PHPUnit\Framework\TestCase;

class BuffertoolsTest extends TestCase
{
    private function getUnsortedList(): array
    {
        return [
            '0101',
            '4102',
            'a43e',
            '0000',
            '0120',
            'd01b',
        ];
    }

    private function getSortedList(): array
    {
        return [
            '0000',
            '0101',
            '0120',
            '4102',
            'a43e',
            'd01b',
        ];
    }

    private function getUnsortedBufferList(): array
    {
        $results = [];
        foreach ($this->getUnsortedList() as $hex) {
            $results[] = Buffer::hex($hex);
        }

        return $results;
    }

    private function getSortedBufferList(): array
    {
        $results = [];
        foreach ($this->getSortedList() as $hex) {
            $results[] = Buffer::hex($hex);
        }

        return $results;
    }

    public function test_sort_default()
    {
        $items = $this->getUnsortedBufferList();
        $v = Buffertools::sort($items);

        $this->assertEquals($this->getSortedBufferList(), $v);
    }

    public function test_sort_callable()
    {
        $items = $this->getUnsortedList();
        $sorted = Buffertools::sort($items, function ($a) {
            return Buffer::hex($a);
        });

        $this->assertEquals($this->getSortedList(), $sorted);
    }

    public function test_num_to_var_int()
    {
        // Should not prefix with anything. Just return chr($decimal);
        for ($i = 0; $i < 253; $i++) {
            $decimal = 1;
            $expected = chr($decimal);
            $val = Buffertools::numToVarInt($decimal)->getBinary();

            $this->assertSame($expected, $val);
        }
    }

    public function test_num_to_var_int1_lower_failure()
    {
        // This decimal should NOT return a prefix
        $decimal = 0xFC; // 252;
        $val = Buffertools::numToVarInt($decimal)->getBinary();
        $this->assertSame($val[0], chr(0xFC));
    }

    public function test_num_to_var_int1_lowest()
    {
        // Decimal > 253 requires a prefix
        $decimal = 0xFD;
        $expected = chr(0xFD).chr(0xFD).chr(0x00);
        $val = Buffertools::numToVarInt($decimal); // ->getBinary();
        $this->assertSame($expected, $val->getBinary());
    }

    public function test_num_to_var_int1_upper()
    {
        // This prefix is used up to 0xffff, because if we go higher,
        // the prefixes are no longer in agreement
        $decimal = 0xFFFF;
        $expected = chr(0xFD).chr(0xFF).chr(0xFF);
        $val = Buffertools::numToVarInt($decimal)->getBinary();
        $this->assertSame($expected, $val);
    }

    public function test_num_to_var_int2_lower_failure()
    {
        // We can check that numbers this low don't yield a 0xfe prefix
        $decimal = 0xFFFE;
        $expected = chr(0xFE).chr(0xFE).chr(0xFF);
        $val = Buffertools::numToVarInt($decimal);

        $this->assertNotSame($expected, $val);
    }

    public function test_num_to_var_int2_lowest()
    {
        // With this prefix, check that the lowest for this field IS prefictable.
        $decimal = 0xFFFF0001;
        $expected = chr(0xFE).chr(0x01).chr(0x00).chr(0xFF).chr(0xFF);
        $val = Buffertools::numToVarInt($decimal);

        $this->assertSame($expected, $val->getBinary());
    }

    public function test_num_to_var_int2_upper()
    {
        // Last number that will share 0xfe prefix: 2^32
        $decimal = 0xFFFFFFFF;
        $expected = chr(0xFE).chr(0xFF).chr(0xFF).chr(0xFF).chr(0xFF);
        $val = Buffertools::numToVarInt($decimal); // ->getBinary();

        $this->assertSame($expected, $val->getBinary());
    }

    public function test_flip_bytes()
    {
        $buffer = Buffer::hex('41');
        $string = $buffer->getBinary();
        $flip = Buffertools::flipBytes($string);
        $this->assertSame($flip, $string);

        $buffer = Buffer::hex('4141');
        $string = $buffer->getBinary();
        $flip = Buffertools::flipBytes($string);
        $this->assertSame($flip, $string);

        $buffer = Buffer::hex('4142');
        $string = $buffer->getBinary();
        $flip = Buffertools::flipBytes($string);
        $this->assertSame($flip, chr(0x42).chr(0x41));

        $buffer = Buffer::hex('0102030405060708');
        $string = $buffer->getBinary();
        $flip = Buffertools::flipBytes($string);
        $this->assertSame($flip, chr(0x08).chr(0x07).chr(0x06).chr(0x05).chr(0x04).chr(0x03).chr(0x02).chr(0x01));
    }

    public function test_concat()
    {
        $a = Buffer::hex('1100');
        $b = Buffer::hex('0011');
        $c = Buffer::hex('11', 2);

        $this->assertEquals('11000011', Buffertools::concat($a, $b)->getHex());
        $this->assertEquals('00111100', Buffertools::concat($b, $a)->getHex());

        $this->assertEquals('11000011', Buffertools::concat($a, $c)->getHex());
        $this->assertEquals('00111100', Buffertools::concat($c, $a)->getHex());
    }
}
