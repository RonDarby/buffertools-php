<?php

declare(strict_types=1);

namespace BitWasp\Buffertools\Types;

interface UintInterface extends TypeInterface
{
    public function getBitSize(): int;
}
