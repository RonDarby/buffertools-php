<?php

declare(strict_types=1);

namespace BitWasp\Buffertools\Types;

use BitWasp\Buffertools\Parser;

class Vector extends AbstractType
{
    /**
     * @var VarInt
     */
    private $varint;

    /**
     * @var callable
     */
    private $readFxn;

    public function __construct(VarInt $varInt, callable $readFunction)
    {
        $this->varint = $varInt;
        $this->readFxn = $readFunction;
        parent::__construct($varInt->getByteOrder());
    }

    /**
     * {@inheritdoc}
     *
     * @see \BitWasp\Buffertools\Types\TypeInterface::write()
     */
    public function write($items): string
    {
        if (is_array($items) === false) {
            throw new \InvalidArgumentException('Vector::write() must be supplied with an array');
        }

        $parser = new Parser;

        return $parser
            ->writeArray($items)
            ->getBuffer()
            ->getBinary();
    }

    /**
     * {@inheritdoc}
     *
     * @see \BitWasp\Buffertools\Types\TypeInterface::read()
     *
     * @throws \Exception
     */
    public function read(Parser $parser): array
    {
        $results = [];
        $handler = $this->readFxn;

        $varInt = $this->varint->read($parser);
        for ($i = 0; $i < $varInt; $i++) {
            $results[] = $handler($parser);
        }

        return $results;
    }
}
