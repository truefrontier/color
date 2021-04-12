<?php

namespace Spatie\Color;

class Hex implements Color
{
    /** @var string */
    protected $red;
    protected $green;
    protected $blue;

    public function __construct(string $red, string $green, string $blue)
    {
        Validate::hexChannelValue($red, 'red');
        Validate::hexChannelValue($green, 'green');
        Validate::hexChannelValue($blue, 'blue');

        $this->red = strtolower($red);
        $this->green = strtolower($green);
        $this->blue = strtolower($blue);
    }

    public static function fromString(string $string)
    {
        Validate::hexColorString($string);

        [$red, $green, $blue] = str_split(ltrim($string, '#'), 2);

        return new static($red, $green, $blue);
    }

    public function red(): string
    {
        return $this->red;
    }

    public function green(): string
    {
        return $this->green;
    }

    public function blue(): string
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
            array_map($f, [
                Convert::hexChannelToRgbChannel($this->red),
                Convert::hexChannelToRgbChannel($this->green),
                Convert::hexChannelToRgbChannel($this->blue),
            ]),
            array_map($g, [
                Convert::hexChannelToRgbChannel($mixColor->red()),
                Convert::hexChannelToRgbChannel($mixColor->green()),
                Convert::hexChannelToRgbChannel($mixColor->blue()),
            ])
        );

        $rgb = new Rgb($red, $green, $blue);

        return $rgb->toHex();
    }

    public function luminance(): float
    {
        $rgb = $this->toRgb();
        return Convert::rgbValueToLuminance($rgb->red(), $rgb->green(), $rgb->blue());
    }

    public function contrastRatio(): float
    {
        $luminance = $this->luminance() / 100;
        $black = new Rgb(0, 0, 0);
        $blackLuminance = $black->luminance() / 100;

        $contrastRatio = 0;
        if ($luminance > $blackLuminance) {
            $contrastRatio = (int) (($luminance + 0.05) / ($blackLuminance + 0.05));
        } else {
            $contrastRatio = (int) (($blackLuminance + 0.05) / ($luminance + 0.05));
        }

        return $contrastRatio;
    }

    public function toHex(): self
    {
        return new self($this->red, $this->green, $this->blue);
    }

    public function toHsl(): Hsl
    {
        [$hue, $saturation, $lightness] = Convert::rgbValueToHsl(
            Convert::hexChannelToRgbChannel($this->red),
            Convert::hexChannelToRgbChannel($this->green),
            Convert::hexChannelToRgbChannel($this->blue)
        );

        return new Hsl($hue, $saturation, $lightness);
    }

    public function toHsla(float $alpha = 1): Hsla
    {
        [$hue, $saturation, $lightness] = Convert::rgbValueToHsl(
            Convert::hexChannelToRgbChannel($this->red),
            Convert::hexChannelToRgbChannel($this->green),
            Convert::hexChannelToRgbChannel($this->blue)
        );

        return new Hsla($hue, $saturation, $lightness, $alpha);
    }

    public function toRgb(): Rgb
    {
        return new Rgb(
            Convert::hexChannelToRgbChannel($this->red),
            Convert::hexChannelToRgbChannel($this->green),
            Convert::hexChannelToRgbChannel($this->blue)
        );
    }

    public function toRgba(float $alpha = 1): Rgba
    {
        return $this->toRgb()->toRgba($alpha);
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
            $palette[$key] = $newHsl->toHex();
        }

        return $palette;
    }

    public function __toString(): string
    {
        return "#{$this->red}{$this->green}{$this->blue}";
    }
}
