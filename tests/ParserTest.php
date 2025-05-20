<?php

declare(strict_types=1);

namespace BitWasp\Buffertools\Tests;

use BitWasp\Buffertools\Buffer;
use BitWasp\Buffertools\Parser;
use PHPUnit\Framework\TestCase;

class ParserTest extends TestCase
{
    public function test_parser_empty()
    {
        $parser = new Parser;
        $this->assertInstanceOf(Parser::class, $parser);

        $this->assertSame(0, $parser->getPosition());
        $this->assertInstanceOf(Buffer::class, $parser->getBuffer());
        $this->assertEmpty($parser->getBuffer()->getHex());
    }

    public function test_get_buffer()
    {
        $buffer = Buffer::hex('41414141');

        $parser = new Parser($buffer);
        $this->assertSame($parser->getBuffer()->getBinary(), $buffer->getBinary());
    }

    public function test_get_buffer_empty_null()
    {
        $buffer = new Buffer;
        $parser = new Parser($buffer);
        $parserData = $parser->getBuffer()->getBinary();
        $bufferData = $buffer->getBinary();
        $this->assertSame($parserData, $bufferData);
    }

    public function test_write_bytes()
    {
        $bytes = '41424344';
        $parser = new Parser;
        $parser->writeBytes(4, Buffer::hex($bytes));
        $returned = $parser->getBuffer()->getHex();
        $this->assertSame($returned, '41424344');
    }

    public function test_write_bytes_flip()
    {
        $bytes = '41424344';
        $parser = new Parser;
        $parser->writeBytes(4, Buffer::hex($bytes), true);
        $returned = $parser->getBuffer()->getHex();
        $this->assertSame($returned, '44434241');
    }

    public function test_write_bytes_padded()
    {
        $parser = new Parser;
        $parser->writeBytes(4, Buffer::hex('34'));
        $this->assertEquals('00000034', $parser->getBuffer()->getHex());
    }

    public function test_write_bytes_flip_padded()
    {
        $parser = new Parser;
        $parser->writeBytes(4, Buffer::hex('34'), true);
        $this->assertEquals('34000000', $parser->getBuffer()->getHex());
    }

    public function test_read_bytes()
    {
        $bytes = '41424344';

        $parser = new Parser($bytes);
        $read = $parser->readBytes(4);
        $this->assertInstanceOf(Buffer::class, $read);

        $hex = $read->getHex();
        $this->assertSame($bytes, $hex);
    }

    public function test_read_bytes_flip()
    {
        $bytes = '41424344';

        $parser = new Parser($bytes);
        $read = $parser->readBytes(4, true);
        $this->assertInstanceOf(Buffer::class, $read);

        $hex = $read->getHex();
        $this->assertSame('44434241', $hex);
    }

    /**
     * @expectedException \BitWasp\Buffertools\Exceptions\ParserOutOfRange
     *
     * @expectedExceptionMessage Could not parse string of required length (empty)
     */
    public function test_read_bytes_empty()
    {
        // Should return false because position is zero,
        // and length is zero.

        $parser = new Parser;
        $data = $parser->readBytes(0);
        $this->assertFalse((bool) $data);
    }

    /**
     * @expectedException \BitWasp\Buffertools\Exceptions\ParserOutOfRange
     *
     * @expectedExceptionMessage Could not parse string of required length (empty)
     */
    public function test_read_bytes_end_of_string()
    {
        $parser = new Parser('4041414142414141');
        $bytes1 = $parser->readBytes(4);
        $bytes2 = $parser->readBytes(4);
        $this->assertSame($bytes1->getHex(), '40414141');
        $this->assertSame($bytes2->getHex(), '42414141');
        $parser->readBytes(1);
    }

    /**
     * @expectedException \Exception
     */
    public function test_read_bytes_beyond_length()
    {
        $bytes = '41424344';
        $parser = new Parser($bytes);
        $parser->readBytes(5);
    }
}
