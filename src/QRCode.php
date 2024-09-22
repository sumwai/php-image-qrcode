<?php

use chillerlan\QRCode\QRCode as QR;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Output\QRGdImagePNG;


class QRCode extends QRGdImagePNG
{
    private $logo_image;
    private function __construct($string = "", $logo = false)
    {
        $option = new QROptions();
        $option->scale = 10;
        $option->imageTransparent = true;
        $option->quietzoneSize = 1;
        $option->circleRadius = 0.5;
        $option->eccLevel            = EccLevel::H;
        if ($logo) {
            $option->addLogoSpace        = true;
            $option->logoSpaceWidth      = 5;
            $option->logoSpaceHeight     = 5;
        }
        $qr = new QR($option);
        $qr->addByteSegment($string);
        parent::__construct($option, $qr->getQRMatrix());
    }

    /**
     * create qrcode image
     * @param string $text
     * @param Image $logo
     * @return Image
     * @throws Exception
     */
    public static function create(string $text, Image $logo = null): Image
    {
        if ($logo) {
            $qr = new self($text, true);
        } else {
            $qr = new self($text);
        }
        return $qr->withLogoImage($logo)->output();
    }

    public function withLogoImage(Image $logo = null)
    {
        $this->logo_image = $logo;
        return $this;
    }

    public function output(): Image
    {
        $random = uniqid();
        parent::dump($random);
        unlink($random);
        $qrcode = new Image($this->image);
        if (!$this->logo_image) {
            return $qrcode;
        }
        list($w, $h) = $this->logo_image->size();
        list($w1, $h1) = $qrcode->size();
        $x = ($w1 - $w) / 2;
        $y = ($h1 - $h) / 2;
        return Image::cover($qrcode, $this->logo_image, $x, $y);
    }
}
