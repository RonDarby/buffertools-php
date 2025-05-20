<?php

declare(strict_types=1);

namespace BitWasp\Buffertools;

interface BufferInterface
{
    /**
     * @throws \Exception
     */
    public function slice(int $start, ?int $end = null): BufferInterface;

    /**
     * Get the size of the buffer to be returned
     */
    public function getSize(): int;

    /**
     * Get the size of the value stored in the buffer
     */
    public function getInternalSize(): int;

    public function getBinary(): string;

    public function getHex(): string;

    /**
     * @return int|string
     */
    public function getInt();

    public function getGmp(): \GMP;

    /**
     * @return Buffer
     */
    public function flip(): BufferInterface;

    public function equals(BufferInterface $other): bool;
}
