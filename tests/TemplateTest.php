<?php

declare(strict_types=1);

namespace BitWasp\Buffertools\Tests;

use BitWasp\Buffertools\Buffer;
use BitWasp\Buffertools\ByteOrder;
use BitWasp\Buffertools\Parser;
use BitWasp\Buffertools\Template;
use BitWasp\Buffertools\Types\ByteString;
use BitWasp\Buffertools\Types\Uint32;
use BitWasp\Buffertools\Types\Uint64;
use BitWasp\Buffertools\Types\VarInt;
use BitWasp\Buffertools\Types\VarString;

class TemplateTest extends BinaryTest
{
    public function test_template()
    {
        $template = new Template;
        $this->assertEmpty($template->getItems());
    }

    /**
     * @expectedException \RuntimeException
     *
     * @expectedExceptionMessage No items in template
     */
    public function test_template_empty_parse()
    {
        $template = new Template;
        $parser = new Parser('010203040a0b0c0d');
        $template->parse($parser);
    }

    public function test_add_item_to_template()
    {
        $item = new Uint64;
        $template = new Template;

        $this->assertEmpty($template->getItems());
        $this->assertEquals(0, $template->count());
        $template->addItem($item);

        $items = $template->getItems();
        $this->assertEquals(1, count($template));

        $this->assertEquals($item, $items[0]);
    }

    public function test_add_through_constructor()
    {
        $item = new Uint64;
        $template = new Template([$item]);

        $items = $template->getItems();
        $this->assertEquals(1, count($items));
        $this->assertEquals($item, $items[0]);
    }

    public function test_parse()
    {
        $value = '50c3000000000000';
        $varint = '19';
        $script = '76a914d04b020dab70a7dd7055db3bbc70d27c1b25a99c88ac';

        $buffer = Buffer::hex($value.$varint.$script);
        $parser = new Parser($buffer);

        $uint64le = new Uint64(ByteOrder::LE);
        $varstring = new VarString(new VarInt);
        $template = new Template([$uint64le, $varstring]);

        [$foundValue, $foundScript] = $template->parse($parser);

        $this->assertInternalType('string', $foundValue);
        $this->assertEquals(50000, $foundValue);
        $this->assertEquals($script, $foundScript->getHex());
    }

    public function test_write()
    {
        $value = '50c3000000000000';
        $varint = '19';
        $script = '76a914d04b020dab70a7dd7055db3bbc70d27c1b25a99c88ac';
        $hex = $value.$varint.$script;

        $uint64le = new Uint64(ByteOrder::LE);
        $varstring = new VarString(new VarInt);
        $template = new Template([$uint64le, $varstring]);

        $binary = $template->write([50000, Buffer::hex($script)]);
        $this->assertEquals(pack('H*', $hex), $binary->getBinary());
    }

    /**
     * @expectedException \RuntimeException
     *
     * @expectedExceptionMessage Number of items must match template
     */
    public function test_write_incomplete()
    {
        $uint64le = new Uint64(ByteOrder::LE);
        $varstring = new VarString(new VarInt);
        $template = new Template([$uint64le, $varstring]);

        $template->write([50000]);
    }

    public function test_fixed_length_string()
    {
        $txin = '58891e8f28100642464417f53845c3953a43e31b35d061bdbf6ca3a64fffabb8000000008c493046022100a9d501a6f59c45a24e65e5030903cfd80ba33910f24d6a505961d64fa5042b4f02210089fa7cc00ab2b5fc15499fa259a057e6d0911d4e849f1720cc6bc58e941fe7e20141041a2756dd506e45a1142c7f7f03ae9d3d9954f8543f4c3ca56f025df66f1afcba6086cec8d4135cbb5f5f1d731f25ba0884fc06945c9bbf69b9b543ca91866e79ffffffff';
        $txinBuf = Buffer::hex($txin);
        $txinParser = new Parser($txinBuf);

        $template = new Template(
            [
                new ByteString(32, ByteOrder::LE),
                new Uint32(ByteOrder::LE),
                new VarString(new VarInt),
            ]
        );

        $out = $template->parse($txinParser);

        /**
         * @var Buffer $txhash
         */
        $txhash = $out[0];
        /**
         * @var Buffer $script
         */
        $script = $out[2];

        $this->assertEquals('b8abff4fa6a36cbfbd61d0351be3433a95c34538f5174446420610288f1e8958', $txhash->getHex());
        $this->assertEquals(0, $out[1]);
        $this->assertEquals('493046022100a9d501a6f59c45a24e65e5030903cfd80ba33910f24d6a505961d64fa5042b4f02210089fa7cc00ab2b5fc15499fa259a057e6d0911d4e849f1720cc6bc58e941fe7e20141041a2756dd506e45a1142c7f7f03ae9d3d9954f8543f4c3ca56f025df66f1afcba6086cec8d4135cbb5f5f1d731f25ba0884fc06945c9bbf69b9b543ca91866e79', $script->getHex());
    }
}
