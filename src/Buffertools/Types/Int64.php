<?php

declare(strict_types=1);

namespace BitWasp\Buffertools\Types;

class Int64 extends AbstractSignedInt
{
    public function getBitSize(): int
    {
        return 64;
    }
}
