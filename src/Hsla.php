<?php

namespace Spatie\Color;

class Hsla implements Color
{
    /** @var float */
    protected $hue;
    protected $saturation;
    protected $lightness;
    protected $alpha;

    public function __construct(float $hue, float $saturation, float $lightness, float $alpha = 1.0)
    {
        Validate::hslValue($saturation, 'saturation');
        Validate::hslValue($lightness, 'lightness');
        Validate::alphaChannelValue($alpha);

        $this->hue = $hue;
        $this->saturation = $saturation;
        $this->lightness = $lightness;
        $this->alpha = $alpha;
    }

    public static function fromString(string $string)
    {
        Validate::hslaColorString($string);

        $matches = null;
        preg_match('/hsla\( *(\d{1,3}) *, *(\d{1,3})%? *, *(\d{1,3})%? *, *([0-1](\.\d{1,2})?) *\)/i', $string, $matches);

        return new static($matches[1], $matches[2], $matches[3], $matches[4]);
    }

    public function hue(): float
    {
        return $this->hue;
    }

    public function saturation(): float
    {
        return $this->saturation;
    }

    public function lightness(): float
    {
        return $this->lightness;
    }

    public function red(): int
    {
        return Convert::hslValueToRgb($this->hue, $this->saturation, $this->lightness)[0];
    }

    public function green(): int
    {
        return Convert::hslValueToRgb($this->hue, $this->saturation, $this->lightness)[1];
    }

    public function blue(): int
    {
        return Convert::hslValueToRgb($this->hue, $this->saturation, $this->lightness)[2];
    }

    public function mix(self $mixColor, $weight = 0.5): self
    {
        $f = function ($x) use ($weight) {
            return $weight * $x;
        };

        $g = function ($x) use ($weight) {
            return (1 - $weight) * $x;
        };

        $h = function ($x, $y) {
            return round($x + $y);
        };

        $rgb = new Rgb(
            array_map(
                $h,
                array_map($f, [$this->red, $this->green, $this->blue]),
                array_map($g, [$mixColor->red, $mixColor->green, $mixColor->blue])
            )
        );

        return $rgb->toHsla($this->alpha);
    }

    public function alpha(): float
    {
        return $this->alpha;
    }

    public function toHex(): Hex
    {
        return new Hex(
            Convert::rgbChannelToHexChannel($this->red()),
            Convert::rgbChannelToHexChannel($this->green()),
            Convert::rgbChannelToHexChannel($this->blue())
        );
    }

    public function toHsla(float $alpha = 1): self
    {
        return new self($this->hue(), $this->saturation(), $this->lightness(), $alpha);
    }

    public function toHsl(): Hsl
    {
        return new Hsl($this->hue(), $this->saturation(), $this->lightness());
    }

    public function toRgb(): Rgb
    {
        return new Rgb($this->red(), $this->green(), $this->blue());
    }

    public function toRgba(float $alpha = 1): Rgba
    {
        return new Rgba($this->red(), $this->green(), $this->blue(), $alpha);
    }

    public function toLuminanceScale(
        array $scale = [
            50 => 93.0,
            100 => 86.0,
            200 => 74.0,
            300 => 59.0,
            400 => 39.0,
            500 => 24.0,
            600 => 15.0,
            700 => 11.5,
            800 => 7.0,
            900 => 3.0,
        ]
    ): array {
        $palette = [];
        foreach ($scale as $key => $luminance) {
            $palette[$key] = new self(
                Convert::hslValueFromLuminance($this->hue, $this->saturation, $luminance)
            );
        }

        return $palette;
    }

    public function __toString(): string
    {
        $hue = round($this->hue);
        $saturation = round($this->saturation);
        $lightness = round($this->lightness);
        $alpha = round($this->alpha, 2);

        return "hsla({$hue},{$saturation}%,{$lightness}%,{$alpha})";
    }
}
