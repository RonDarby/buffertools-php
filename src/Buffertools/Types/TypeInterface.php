<?php

declare(strict_types=1);

namespace BitWasp\Buffertools\Types;

use BitWasp\Buffertools\Parser;

interface TypeInterface
{
    /**
     * Flip whatever bitstring is given to us
     */
    public function flipBits(string $bitString): string;

    /**
     * @param  mixed  $integer
     */
    public function write($integer): string;

    /**
     * @return mixed
     */
    public function read(Parser $parser);

    public function getByteOrder(): int;
}
