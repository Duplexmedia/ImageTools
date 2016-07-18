<?php

namespace Duplexmedia\ImageTools;

/**
 * A class with a couple of useful color processing methods.
 *
 * @package Duplexmedia\ImageTools
 */
class ColorTools
{
    /**
     * Converts a HEX-color string into it's RGB components.
     *
     * @param $hex string the hex color string.
     * @return array the resulting colors.
     */
    public function hex2rgb($hex)
    {
        $hex = str_replace("#", "", $hex);

        if(strlen($hex) == 3) {
            $r = hexdec(substr($hex,0,1).substr($hex,0,1));
            $g = hexdec(substr($hex,1,1).substr($hex,1,1));
            $b = hexdec(substr($hex,2,1).substr($hex,2,1));
        } else {
            $r = hexdec(substr($hex,0,2));
            $g = hexdec(substr($hex,2,2));
            $b = hexdec(substr($hex,4,2));
        }

        return array($r, $g, $b);
    }

    /**
     * Calculates the brightness of the given color.
     *
     * @link http://www.nbdtech.com/Blog/archive/2008/04/27/Calculating-the-Perceived-Brightness-of-a-Color.aspx
     *
     * @param $color string the hex color string.
     * @return float the brightness.
     */
    public function calculateBrightness($color)
    {
        $components = is_array($color) ? $color : $this->hex2rgb($color);
        return sqrt(0.241 * pow($components[0], 2) + 0.691 * pow($components[1], 2) + 0.068 * pow($components[2], 2));
    }

    /**
     * Calculates the saturation of the given color.
     *
     * @param $color string the color as array or hex color string.
     * @return float the saturation.
     */
    public function calculateSaturation($color)
    {
        $components = is_array($color) ? $color : $this->hex2rgb($color);

        $var_Min = min($components[0], $components[1], $components[2]);
        $var_Max = max($components[0], $components[1], $components[2]);
        $del_Max = $var_Max - $var_Min;

        return $del_Max / $var_Max;
    }
}