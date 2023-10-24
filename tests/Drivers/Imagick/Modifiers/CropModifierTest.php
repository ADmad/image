<?php

namespace Intervention\Image\Tests\Drivers\Imagick\Modifiers;

use Intervention\Image\Drivers\Imagick\Modifiers\CropModifier;
use Intervention\Image\Tests\TestCase;
use Intervention\Image\Tests\Traits\CanCreateImagickTestImage;

/**
 * @requires extension gd
 * @covers \Intervention\Image\Drivers\Imagick\Modifiers\CropModifier
 */
class CropModifierTest extends TestCase
{
    use CanCreateImagickTestImage;

    public function testModify(): void
    {
        $image = $this->createTestImage('blocks.png');
        $image = $image->modify(new CropModifier(200, 200, 'bottom-right'));
        $image->toPng()->save('./test.png');
        $this->assertEquals(200, $image->getWidth());
        $this->assertEquals(200, $image->getHeight());
        $this->assertColor(255, 0, 0, 255, $image->pickColor(5, 5));
        $this->assertColor(255, 0, 0, 255, $image->pickColor(100, 100));
        $this->assertColor(255, 0, 0, 255, $image->pickColor(190, 190));
    }
}