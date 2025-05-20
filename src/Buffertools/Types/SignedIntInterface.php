<?php

declare(strict_types=1);

namespace BitWasp\Buffertools\Types;

interface SignedIntInterface extends TypeInterface
{
    public function getBitSize(): int;
}
