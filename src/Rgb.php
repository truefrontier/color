<?php

namespace Spatie\Color;

class Rgb implements Color
{
    /** @var int */
    protected $red;
    protected $green;
    protected $blue;

    public function __construct(int $red, int $green, int $blue)
    {
        Validate::rgbChannelValue($red, 'red');
        Validate::rgbChannelValue($green, 'green');
        Validate::rgbChannelValue($blue, 'blue');

        $this->red = $red;
        $this->green = $green;
        $this->blue = $blue;
    }

    public static function fromString(string $string)
    {
        Validate::rgbColorString($string);

        $matches = null;
        preg_match('/rgb\( *(\d{1,3} *, *\d{1,3} *, *\d{1,3}) *\)/i', $string, $matches);

        $channels = explode(',', $matches[1]);
        [$red, $green, $blue] = array_map('trim', $channels);

        return new static($red, $green, $blue);
    }

    public function red(): int
    {
        return $this->red;
    }

    public function green(): int
    {
        return $this->green;
    }

    public function blue(): int
    {
        return $this->blue;
    }

    public function mix($mixColor, $weight = 0.5): self
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

        [$red, $green, $blue] = array_map(
            $h,
            array_map($f, [$this->red, $this->green, $this->blue]),
            array_map($g, [$mixColor->red, $mixColor->green, $mixColor->blue])
        );

        return new self($red, $green, $blue);
    }

    public function luminance(): float
    {
        return Convert::rgbValueToLuminance($this->red, $this->green, $this->blue);
    }

    public function contrastRatio(): float
    {
        $luminance = $this->luminance() / 100;
        $black = new self(0, 0, 0);
        $blackLuminance = $black->luminance() / 100;

        $contrastRatio = 0;
        if ($luminance > $blackLuminance) {
            $contrastRatio = (int) (($luminance + 0.05) / ($blackLuminance + 0.05));
        } else {
            $contrastRatio = (int) (($blackLuminance + 0.05) / ($luminance + 0.05));
        }

        return $contrastRatio;
    }

    public function toHex(): Hex
    {
        return new Hex(
            Convert::rgbChannelToHexChannel($this->red),
            Convert::rgbChannelToHexChannel($this->green),
            Convert::rgbChannelToHexChannel($this->blue)
        );
    }

    public function toHsl(): Hsl
    {
        [$hue, $saturation, $lightness] = Convert::rgbValueToHsl(
            $this->red,
            $this->green,
            $this->blue
        );

        return new Hsl($hue, $saturation, $lightness);
    }

    public function toHsla(float $alpha = 1): Hsla
    {
        [$hue, $saturation, $lightness] = Convert::rgbValueToHsl(
            $this->red,
            $this->green,
            $this->blue
        );

        return new Hsla($hue, $saturation, $lightness, $alpha);
    }

    public function toRgb(): self
    {
        return new self($this->red, $this->green, $this->blue);
    }

    public function toRgba(float $alpha = 1): Rgba
    {
        return new Rgba($this->red, $this->green, $this->blue, $alpha);
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
        $hsl = $this->toHsl();
        foreach ($scale as $key => $luminance) {
            [$hue, $saturation, $lightness] = Convert::hslValueFromLuminance(
                $hsl->hue(),
                $hsl->saturation(),
                $luminance
            );
            $newHsl = new Hsl($hue, $saturation, $lightness);
            $palette[$key] = $newHsl->toRgb();
        }

        return $palette;
    }

    public function toColorWheel(): array
    {
        $palette = [];
        $hsl = $this->toHsl();
        for ($deg = 0; $deg < 360; $deg += 30) {
            $newHue = $hsl->hue() + $deg;
            if ($newHue > 360) {
                $newHue -= 360;
            }
            $colorName = Convert::hueToColorName($newHue);
            $wheelColor = new Hsl($newHue, $hsl->saturation(), $hsl->lightness());
            $palette[$colorName] = $wheelColor->toRgb();
        }

        return $palette;
    }

    public function toColorName(): string
    {
        $hsl = $this->toHsl();
        return Convert::hueToColorName($hsl->hue());
    }

    public function __toString(): string
    {
        return "rgb({$this->red},{$this->green},{$this->blue})";
    }
}
