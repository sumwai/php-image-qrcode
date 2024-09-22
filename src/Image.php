<?php


class Image
{
    private GdImage $image;
    private $type;
    public function __construct(GdImage $image, $type = "jpeg")
    {
        $this->image = $image;
        $this->type = $type ?? "jpeg";
    }

    /**
     * return image width and height, it's an array like [1920, 1080]
     * @return array
     */
    public function size(): array
    {
        return [imagesx($this->image), imagesy($this->image)];
    }

    /**
     * return format string with base64_encoded binary data
     * 返回将二进制数据使用Base64编码后的格式化文本
     * @param mixed $format
     * @return string
     */
    public function string($format = "<img src='data:image/png;base64,%s'>\n"): string
    {
        return sprintf($format, base64_encode($this->__tostring()));
    }
    public function __tostring(): string
    {

        ob_start();
        switch ($this->type) {
            case "png":
                imagepng($this->image);
                break;
            default:
                imagejpeg($this->image);
        }
        $stringdata = ob_get_contents();
        ob_end_clean();
        return $stringdata;
    }

    public function __destruct()
    {
        imagedestroy($this->image);
    }
    /**
     * return image from url, file or string
     * 从文本、文件或URL读取图片
     * @return Image
     */
    public static function from($url = ""): Image
    {
        $file = $string = $url;
        if (file_exists($file)) {
            return new self(imagecreatefromstring(file_get_contents($file)));
        }
        if (substr($url, 0, 5) == "http:" or substr($url, 0, 6) == "https:") {
            return new self(imagecreatefromstring(Request::GET($file)));
        }
        return new self(imagecreatefromstring($string));
    }

    /**
     * resize image to $w*$h
     * 缩放图片大小为指定的宽高
     * @param Image $image
     * @param mixed $w
     * @param mixed $h
     * @return Image
     */
    public static function resize(Image $image, $w = 60, $h = 60, $crop = FALSE): Image
    {
        list($width, $height) = $image->size();
        $r = $width / $height;
        if ($crop) {
            if ($width > $height) {
                $width = ceil($width - ($width * abs($r - $w / $h)));
            } else {
                $height = ceil($height - ($height * abs($r - $w / $h)));
            }
            $newwidth = $w;
            $newheight = $h;
        } else {
            if ($w / $h > $r) {
                $newwidth = $h * $r;
                $newheight = $h;
            } else {
                $newheight = $w / $r;
                $newwidth = $w;
            }
        }
        $dst = imagecreatetruecolor($newwidth, $newheight);
        imagecopyresampled($dst, $image->image, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
        return new Image($dst);
    }

    /**
     * cover dst($w, $h) on base($x, $y)
     * 将dst覆盖到base之上，偏移宽高分别为x, y, dst宽高分别为w, h
     * @param Image $base
     * @param Image $dst
     * @param mixed $x
     * @param mixed $y
     * @param mixed $w
     * @param mixed $h
     * @return Image
     */
    public static function cover(Image $base, Image $dst,  $x,  $y, int $w = 0, int $h = 0): Image
    {
        $src = imagecreatefromstring($base);
        list($width, $height) = $dst->size();
        $dst = $dst->image;
        $x = intval($x);
        $y = intval($y);
        print "x: {$x}, y: {$y}, w: {$width}, h: {$height} <br>";
        imagecopy($src, $dst, $x, $y, 0, 0, $w == 0 ? $width : $w, $h == 0 ? $height : $h);
        return new Image($src);
    }

    public function save($filename = "")
    {
        switch (substr($filename, -3)) {
            case "png":
                imagepng($this->image, $filename);
                break;
            case "jpg":
                imagejpeg($this->image, $filename);
                break;
            case "gif":
                imagegif($this->image, $filename);
                break;
            default:
                imagejpeg($this->image, $filename);
        }
    }
}
