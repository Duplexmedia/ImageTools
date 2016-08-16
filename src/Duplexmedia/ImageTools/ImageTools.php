<?php

namespace Duplexmedia\ImageTools;
use League\ColorExtractor\ColorExtractor;
use League\ColorExtractor\Palette;

/**
 * A class with a couple of useful image processing functions.
 *
 * @package Duplexmedia\ImageTools
 */
class ImageTools {
    /**
     * Initializes the class and loads the given image.
     *
     * @param $imagePath string the relative path to the image.
     */
    public function __construct($imagePath)
    {
        $this->colorTools = new ColorTools();
        $this->imagePath = $imagePath;
        $this->image = new \Imagick();
        $this->image->setBackgroundColor(new \ImagickPixel('transparent'));
        $this->image->readImage($imagePath);
    }

    /**
     * Calculates the brightness of the given color. This is a passthrough
     * to ColorTools\calculateBrightness for backwards compatibility.
     *
     * @deprecated Use the ColorTools class directly.
     * @link http://www.nbdtech.com/Blog/archive/2008/04/27/Calculating-the-Perceived-Brightness-of-a-Color.aspx
     *
     * @param $color string the hex color string.
     * @return float the brightness.
     */
    public function calculateBrightness($color)
    {
        return $this->colorTools->calculateBrightness($color);
    }

    /**
     * Calculates the saturation of the given color. This is a passthrough
     * to ColorTools\calculateBrightness for backwards compatibility.
     *
     * @deprecated Use the ColorTools class directly.
     *
     * @param $color string the color as array or hex color string.
     * @return float the saturation.
     */
    public function calculateSaturation($color)
    {
        return $this->colorTools->calculateSaturation($color);
    }

    /**
     * Returns the size of the image.
     *
     * @return array the x and y sizes of the image.
     */
    public function getImageSize()
    {
        return array($this->image->getImageWidth(), $this->image->getImageHeight());
    }

    /**
     * Automatically trims the image to remove whitespace.
     *
     * @param float $fuzz a double between 0 and 1 which indicates the color tolerance for cropping.
     * @return array the size of the cropped image.
     */
    public function trimImage($fuzz = 0.1)
    {
        $this->image->trimImage($fuzz);
        $this->image->setImagePage(0, 0, 0, 0);
        return array($this->image->getImageWidth(), $this->image->getImageHeight());
    }

    /**
     *
     * Returns an array with the most used colors in the image.
     *
     * @param int $number the number of colors to find.
     * @param int $sampleSize the size to downsample the image to.
     * @param int $maxBrightness the maximum brightness of the color to be returned.
     *
     * @return array|bool the most used colors of the image, or false, if an error has occured.
     */
    public function getAccentColors($number = 5, $sampleSize = 500, $maxBrightness = -1)
    {
        $fileName = md5(microtime()) . ".png";
        $this->image->setImageFormat("png");
        $this->image->resizeImage($sampleSize, $sampleSize, \Imagick::FILTER_LANCZOS, 1, true);
        $this->image->writeImage($fileName);
        $image = new ColorExtractor(Palette::fromFilename($fileName));

        $results = array_filter($image->extract($number), function ($color) use ($maxBrightness) {
            return $maxBrightness < 0 || $this->colorTools->calculateBrightness($color) < $maxBrightness;
        });

        unlink($fileName);
        return $results;
    }

    /**
     * Returns the average color of the image.
     *
     * @return mixed the average color of the image.
     */
    public function getAverageColor()
    {
        $this->image->resizeImage(1, 1, \Imagick::FILTER_CATROM, 1);
        $color = $this->image->getImagePixelColor(0, 0);
        return $color->getColor();
    }

    /**
     * Checks, whether the image has transparent areas.
     *
     * @return bool true, if the image has transparent areas, otherwise false.
     */
    public function hasTransparency()
    {
        return $this->image->getImageAlphaChannel() == 1;
    }

    /**
     * Converts a HEX-color string into it's RGB components. This is a passthrough to
     * ColorTools\hex2rgb for backwards compatibility.
     *
     * @deprecated Use the ColorTools class directly.
     *
     * @param $hex string the hex color string.
     * @return array the resulting colors.
     */
    public function hex2rgb($hex)
    {
        return $this->colorTools->hex2rgb($hex);
    }

    /**
     * Returns the format of the image.
     *
     * @return string the image format.
     */
    public function getFormat()
    {
        return $this->image->getImageFormat();
    }

    /**
     * Colorizes a transparent logo with black or white.
     *
     * @param bool $black shall the image be colored black?
     * @return bool the result
     */
    public function colorizeImage($black = true)
    {
        return $this->image->modulateImage($black ? 0 : 255, 100, 0);
    }

    /**
     * Applies a blur to the image.
     *
     * @param int $spread the intensity of the kernel
     * @param int $radius the size of the kernel
     * @param bool $gaussian use gaussian blur? (slower, but better)
     * @return bool whether the blur has been successful.
     */
    public function blurImage($spread = 8, $radius = 0, $gaussian = false)
    {
        return $gaussian ?
            $this->image->gaussianBlurImage($radius, $spread) :
            $this->image->blurImage($radius, $spread);
    }

    /**
     * Resizes the image.
     *
     * @param $width int the new width.
     * @param $height int the new height
     * @param $filter int the resize filter to use
     * @param $proportionalResize bool resize proportionally?
     * @return bool true, if the resize operation succeeded.
     */
    public function resizeImage($width, $height, $filter, $proportionalResize = false)
    {
        return $this->image->resizeImage($width, $height, $filter, 1, $proportionalResize);
    }

    /**
     * Saves the processed image to the given file path.
     *
     * @param $path string the file path.
     * @param string $format the image format (default: png)
     * @return bool true, if the image has been saved, otherwise false.
     */
    public function saveImage($path, $format = "png")
    {
        try {
            $this->image->setImageFormat($format);
            $this->image->writeImage($path);
            return true;
        } catch (\Exception $ex) {
            return false;
        }
    }
}