<?php

namespace Intervention\Image\Colors\Rgb;

use Intervention\Image\Colors\Rgb\Channels\Blue;
use Intervention\Image\Colors\Rgb\Channels\Green;
use Intervention\Image\Colors\Rgb\Channels\Red;
use Intervention\Image\Colors\Rgb\Channels\Alpha;
use Intervention\Image\Colors\Traits\CanHandleChannels;
use Intervention\Image\Drivers\Abstract\AbstractInputHandler;
use Intervention\Image\Interfaces\ColorChannelInterface;
use Intervention\Image\Interfaces\ColorInterface;
use Intervention\Image\Interfaces\ColorspaceInterface;

class Color implements ColorInterface
{
    use CanHandleChannels;

    /**
     * Color channels
     */
    protected array $channels;

    /**
     * Create new instance
     *
     * @param int $r
     * @param int $g
     * @param int $b
     * @param int $a
     * @return ColorInterface
     */
    public function __construct(int $r, int $g, int $b, int $a = 255)
    {
        $this->channels = [
            new Red($r),
            new Green($g),
            new Blue($b),
            new Alpha($a),
        ];
    }

    public function colorspace(): ColorspaceInterface
    {
        return new Colorspace();
    }

    /**
     * {@inheritdoc}
     *
     * @see ColorInterface::create()
     */
    public static function create(mixed $input): ColorInterface
    {
        return (new class ([
            Decoders\HexColorDecoder::class,
            Decoders\StringColorDecoder::class,
            Decoders\TransparentColorDecoder::class,
            Decoders\HtmlColornameDecoder::class,
        ]) extends AbstractInputHandler
        {
        })->handle($input);
    }

    /**
     * Return the RGB red color channel
     *
     * @return ColorChannelInterface
     */
    public function red(): ColorChannelInterface
    {
        return $this->channel(Red::class);
    }

    /**
     * Return the RGB green color channel
     *
     * @return ColorChannelInterface
     */
    public function green(): ColorChannelInterface
    {
        return $this->channel(Green::class);
    }

    /**
     * Return the RGB blue color channel
     *
     * @return ColorChannelInterface
     */
    public function blue(): ColorChannelInterface
    {
        return $this->channel(Blue::class);
    }

    /**
     * Return the colors alpha channel
     *
     * @return ColorChannelInterface
     */
    public function alpha(): ColorChannelInterface
    {
        return $this->channel(Alpha::class);
    }

    /**
     * {@inheritdoc}
     *
     * @see ColorInterface::toArray()
     */
    public function toArray(): array
    {
        return array_map(function (ColorChannelInterface $channel) {
            return $channel->value();
        }, $this->channels());
    }

    /**
     * {@inheritdoc}
     *
     * @see ColorInterface::toHex()
     */
    public function toHex(string $prefix = ''): string
    {
        if ($this->isFullyOpaque()) {
            return sprintf(
                '%s%02x%02x%02x',
                $prefix,
                $this->red()->value(),
                $this->green()->value(),
                $this->blue()->value()
            );
        }

        return sprintf(
            '%s%02x%02x%02x%02x',
            $prefix,
            $this->red()->value(),
            $this->green()->value(),
            $this->blue()->value(),
            $this->alpha()->value()
        );
    }

    /**
     * {@inheritdoc}
     *
     * @see ColorInterface::convertTo()
     */
    public function convertTo(string|ColorspaceInterface $colorspace): ColorInterface
    {
        $colorspace = match (true) {
            is_object($colorspace) => $colorspace,
            default => new $colorspace(),
        };

        return $colorspace->convertColor($this);
    }

    public function isFullyOpaque(): bool
    {
        return $this->alpha()->value() === 255;
    }

    /**
     * {@inheritdoc}
     *
     * @see ColorInterface::toString()
     */
    public function toString(): string
    {
        if ($this->isFullyOpaque()) {
            return sprintf(
                'rgb(%d, %d, %d)',
                $this->red()->value(),
                $this->green()->value(),
                $this->blue()->value()
            );
        }

        return sprintf(
            'rgba(%d, %d, %d, %.1F)',
            $this->red()->value(),
            $this->green()->value(),
            $this->blue()->value(),
            $this->alpha()->normalize(),
        );
    }

    /**
     * {@inheritdoc}
     *
     * @see ColorInterface::isGreyscale()
     */
    public function isGreyscale(): bool
    {
        $values = [$this->red()->value(), $this->green()->value(), $this->blue()->value()];

        return count(array_unique($values, SORT_REGULAR)) === 1;
    }

    /**
     * {@inheritdoc}
     *
     * @see ColorInterface::__toString()
     */
    public function __toString(): string
    {
        return $this->toString();
    }
}