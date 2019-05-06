<?php

declare(strict_types = 1);

use PHPUnit\Framework\TestCase;
use Sop\ASN1\Type\Primitive\Boolean;

/**
 * @group encode
 * @group boolean
 *
 * @internal
 */
class BooleanEncodeTest extends TestCase
{
    public function testTrue()
    {
        $el = new Boolean(true);
        $this->assertEquals("\x1\x1\xff", $el->toDER());
    }

    public function testFalse()
    {
        $el = new Boolean(false);
        $this->assertEquals("\x1\x1\x00", $el->toDER());
    }
}
