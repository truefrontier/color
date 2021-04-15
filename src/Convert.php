<?php

namespace Spatie\Color;

class Convert
{
    public static function hexChannelToRgbChannel(string $hexValue): int
    {
        return hexdec($hexValue);
    }

    public static function rgbChannelToHexChannel(int $rgbValue): string
    {
        return str_pad(dechex($rgbValue), 2, '0', STR_PAD_LEFT);
    }

    public static function hslValueToRgb(float $hue, float $saturation, float $lightness): array
    {
        $h = (360 + ($hue % 360)) % 360;  // hue values can be less than 0 and greater than 360. This normalises them into the range 0-360.

        $c = (1 - abs(2 * ($lightness / 100) - 1)) * ($saturation / 100);
        $x = $c * (1 - abs(fmod($h / 60, 2) - 1));
        $m = ($lightness / 100) - ($c / 2);

        if ($h >= 0 && $h <= 60) {
            return [round(($c + $m) * 255), round(($x + $m) * 255), round($m * 255)];
        }

        if ($h > 60 && $h <= 120) {
            return [round(($x + $m) * 255), round(($c + $m) * 255), round($m * 255)];
        }

        if ($h > 120 && $h <= 180) {
            return [round($m * 255), round(($c + $m) * 255), round(($x + $m) * 255)];
        }

        if ($h > 180 && $h <= 240) {
            return [round($m * 255), round(($x + $m) * 255), round(($c + $m) * 255)];
        }

        if ($h > 240 && $h <= 300) {
            return [round(($x + $m) * 255), round($m * 255), round(($c + $m) * 255)];
        }

        if ($h > 300 && $h <= 360) {
            return [round(($c + $m) * 255), round($m * 255), round(($x + $m) * 255)];
        }
    }

    public static function rgbValueToHsl($red, $green, $blue): array
    {
        $r = $red / 255;
        $g = $green / 255;
        $b = $blue / 255;

        $cmax = max($r, $g, $b);
        $cmin = min($r, $g, $b);
        $delta = $cmax - $cmin;

        $hue = 0;
        if ($delta != 0) {
            if ($r === $cmax) {
                $hue = 60 * fmod(($g - $b) / $delta, 6);
            }

            if ($g === $cmax) {
                $hue = 60 * ((($b - $r) / $delta) + 2);
            }

            if ($b === $cmax) {
                $hue = 60 * ((($r - $g) / $delta) + 4);
            }
        }

        $lightness = ($cmax + $cmin) / 2;

        $saturation = 0;

        if ($lightness > 0 && $lightness < 1) {
            $saturation = $delta / (1 - abs((2 * $lightness) - 1));
        }

        return [$hue, min($saturation, 1) * 100, min($lightness, 1) * 100];
    }

    public static function rgbValueToLuminance($red, $green, $blue): float
    {
        $red /= 255;
        $green /= 255;
        $blue /= 255;

        $red = $red < 0.03928 ? $red / 12.92 : pow(($red + 0.055) / 1.055, 2.4);
        $green = $green < 0.03928 ? $green / 12.92 : pow(($green + 0.055) / 1.055, 2.4);
        $blue = $blue < 0.03928 ? $blue / 12.92 : pow(($blue + 0.055) / 1.055, 2.4);

        return 21.26 * $red + 71.52 * $green + 7.22 * $blue;
    }

    public static function hslValueToLuminance(
        float $hue,
        float $saturation,
        float $lightness
    ): float {
        [$red, $green, $blue] = self::hslValueToRgb($hue, $saturation, $lightness);

        return self::rgbValueToLuminance($red, $green, $blue);
    }

    public static function hslValueFromLuminance(
        float $hue,
        float $saturation,
        float $luminance,
        float $precision = 0.01
    ): array {
        $closest = 100;
        $lightness = 100;

        for ($sampleLightness = 100; $sampleLightness >= 0; $sampleLightness--) {
            $sampleLuminance = self::hslValueToLuminance($hue, $saturation, $sampleLightness);
            $difference = abs($luminance - $sampleLuminance);
            if ($difference < $closest) {
                $lightness = $sampleLightness;
                $closest = $difference;
            }
        }

        $max = $closest + $precision * 100;
        $min = $closest - $precision * 100;
        for ($sampleLightness = $max; $sampleLightness >= $min; $sampleLightness -= $precision) {
            $sampleLuminance = self::hslValueToLuminance($hue, $saturation, $sampleLightness);
            $difference = abs($luminance - $sampleLuminance);
            if ($difference < $closest) {
                $lightness = $sampleLightness;
                $closest = $difference;
            }
        }

        return [$hue, $saturation, $lightness];
    }

    public static function hueToColorName(float $hue): string
    {
        if ($hue >= 345 || $hue < 15) {
            return 'red';
        } elseif ($hue >= 15 && $hue < 45) {
            return 'orange';
        } elseif ($hue >= 45 && $hue < 75) {
            return 'yellow';
        } elseif ($hue >= 75 && $hue < 105) {
            return 'lime';
        } elseif ($hue >= 105 && $hue < 135) {
            return 'green';
        } elseif ($hue >= 135 && $hue < 165) {
            return 'turquoise';
        } elseif ($hue >= 165 && $hue < 195) {
            return 'cyan';
        } elseif ($hue >= 195 && $hue < 225) {
            return 'cobalt';
        } elseif ($hue >= 225 && $hue < 255) {
            return 'blue';
        } elseif ($hue >= 255 && $hue < 285) {
            return 'violet';
        } elseif ($hue >= 285 && $hue < 315) {
            return 'magenta';
        } elseif ($hue >= 315 && $hue < 345) {
            return 'rose';
        }
    }
}
