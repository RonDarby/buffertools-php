<?php

declare(strict_types=1);

namespace BitWasp\Buffertools\Types;

use BitWasp\Buffertools\ByteOrder;
use BitWasp\Buffertools\Parser;

class VarInt extends AbstractType
{
    /**
     * @return array
     */
    public function solveWriteSize(\GMP $integer)
    {
        if (gmp_cmp($integer, gmp_pow(gmp_init(2), 16)) < 0) {
            return [new Uint16(ByteOrder::LE), 0xFD];
        } elseif (gmp_cmp($integer, gmp_pow(gmp_init(2), 32)) < 0) {
            return [new Uint32(ByteOrder::LE), 0xFE];
        } elseif (gmp_cmp($integer, gmp_pow(gmp_init(2), 64)) < 0) {
            return [new Uint64(ByteOrder::LE), 0xFF];
        } else {
            throw new \InvalidArgumentException('Integer too large, exceeds 64 bit');
        }
    }

    /**
     * @return UintInterface[]
     *
     * @throws \InvalidArgumentException
     */
    public function solveReadSize(\GMP $givenPrefix)
    {
        if (gmp_cmp($givenPrefix, 0xFD) === 0) {
            return [new Uint16(ByteOrder::LE)];
        } elseif (gmp_cmp($givenPrefix, 0xFE) === 0) {
            return [new Uint32(ByteOrder::LE)];
        } elseif (gmp_cmp($givenPrefix, 0xFF) === 0) {
            return [new Uint64(ByteOrder::LE)];
        }

        throw new \InvalidArgumentException('Unknown varint prefix');
    }

    /**
     * {@inheritdoc}
     *
     * @see \BitWasp\Buffertools\Types\TypeInterface::write()
     */
    public function write($integer): string
    {
        $gmpInt = gmp_init($integer, 10);
        if (gmp_cmp($gmpInt, gmp_init(0xFD, 10)) < 0) {
            return pack('C', $integer);
        }
        [$int, $prefix] = $this->solveWriteSize($gmpInt);

        return pack('C', $prefix).$int->write($integer);
    }

    /**
     * {@inheritdoc}
     *
     * @see \BitWasp\Buffertools\Types\TypeInterface::read()
     */
    public function read(Parser $parser)
    {
        $byte = unpack('C', $parser->readBytes(1)->getBinary())[1];
        if ($byte < 0xFD) {
            return $byte;
        }

        [$uint] = $this->solveReadSize(gmp_init($byte, 10));

        return $uint->read($parser);
    }
}
