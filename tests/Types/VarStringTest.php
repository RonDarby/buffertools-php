<?php

declare(strict_types=1);

namespace BitWasp\Buffertools\Tests\Types;

use BitWasp\Buffertools\Buffer;
use BitWasp\Buffertools\Parser;
use BitWasp\Buffertools\Tests\BinaryTest;
use BitWasp\Buffertools\Types\VarInt;
use BitWasp\Buffertools\Types\VarString;

class VarStringTest extends BinaryTest
{
    public function getSampleVarStrings(): array
    {
        return array_map(function (string $value) {
            return [$value];
        }, [
            '',
            '00',
            '00010203040506070809',
            '00010203040506070809000102030405060708090001020304050607080900010203040506070809000102030405060708090001020304050607080900010203040506070809000102030405060708090001020304050607080900010203040506070809000102030405060708090001020304050607080900010203040506070809000102030405060708090001020304050607080900010203040506070809000102030405060708090001020304050607080900010203040506070809000102030405060708090001020304050607080900010203040506070809000102030405060708090001020304050607080900010203040506070809000102',
        ]);
    }

    /**
     * @throws \BitWasp\Buffertools\Exceptions\ParserOutOfRange
     * @throws \Exception
     *
     * @dataProvider getSampleVarStrings
     */
    public function test_get_var_string(string $input)
    {
        $varstring = new VarString(new VarInt);
        $binary = $varstring->write(Buffer::hex($input));

        $parser = new Parser(new Buffer($binary));
        $original = $varstring->read($parser);

        $this->assertSame($input, $original->getHex());
    }

    /**
     * @expectedException \BitWasp\Buffertools\Exceptions\ParserOutOfRange
     *
     * @expectedExceptionMessage Insufficient data remaining for VarString
     */
    public function test_aborts_with_invalid_var_int_length()
    {
        $buffer = new Buffer("\x05\x00");

        $varstring = new VarString(new VarInt);
        $varstring->read(new Parser($buffer));
    }

    /**
     * @expectedException \InvalidArgumentException
     *
     * @expectedExceptionMessage Must provide a buffer
     */
    public function test_fails_without_buffer()
    {
        $varstring = new VarString(new VarInt);
        $varstring->write('');
    }
}
