<?php

declare(strict_types=1);

namespace BitWasp\Buffertools\Types;

use BitWasp\Buffertools\ByteOrder;

abstract class AbstractType implements TypeInterface
{
    /**
     * @var int
     */
    private $byteOrder;

    public function __construct(int $byteOrder = ByteOrder::BE)
    {
        if (in_array($byteOrder, [ByteOrder::BE, ByteOrder::LE]) === false) {
            throw new \InvalidArgumentException('Must pass valid flag for endianness');
        }

        $this->byteOrder = $byteOrder;
    }

    public function getByteOrder(): int
    {
        return $this->byteOrder;
    }

    public function isBigEndian(): bool
    {
        return $this->getByteOrder() == ByteOrder::BE;
    }

    /**
     * @throws \Exception
     */
    public function flipBits(string $bitString): string
    {
        $length = strlen($bitString);

        if ($length % 8 !== 0) {
            throw new \Exception('Bit string length must be a multiple of 8');
        }

        $newString = '';
        for ($i = $length; $i >= 0; $i -= 8) {
            $newString .= substr($bitString, $i, 8);
        }

        return $newString;
    }
}
