<?php

declare(strict_types=1);

namespace BitWasp\Buffertools\Types;

class Int32 extends AbstractSignedInt
{
    public function getBitSize(): int
    {
        return 32;
    }
}
