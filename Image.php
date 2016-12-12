<?php
namespace fboes\SmallPhpHelpers;

class Image
{
    protected $filename;
    protected $width     = 100;
    protected $height    = 100;
    protected $mode;
    protected $background;

    const MODE_CROP          = 'crop';
    const MODE_ENLARGE_CROP  = 'enlarge-crop';
    const MODE_BORDER        = 'border';

    protected $pathImageMagick = '';

    /**
    * [__construct description]
    * @param string $filename  absolute path
    */
    public function __construct($filename)
    {
        $this->filename = $filename;
        $this->setMode(self::MODE_CROP);
    }

    /**
    * [init description]
    * @param  [type] $filename [description]
    * @return self             Object
    */
    public static function init($filename)
    {
        return new Image($filename);
    }

    /**
    * [setWidth description]
    * @param  integer $width [description]
    * @return self           Object
    */
    public function setWidth($width)
    {
        $this->width = abs((int)$width);
        return $this;
    }

    /**
    * [setHeight description]
    * @param  integer $height [description]
    * @return self            Object
    */
    public function setHeight($height)
    {
        $this->height = abs((int)$height);
        return $this;
    }

    /**
    * [setMode description]
    * @param  string $mode       either Image::MODE_CROP or Image::MODER_BORDER
    * @param  string $background
    * @return self         Object
    * @throws \Exception
    */
    public function setMode($mode, $background = 'white')
    {
        if (!in_array($mode, array(
            self::MODE_CROP,
            self::MODE_ENLARGE_CROP,
            self::MODE_BORDER
        ))) {
            throw new \Exception('Illegal mode selected for image resizing');
        }
        $this->mode       = $mode;
        $this->background = $background;
        return $this;
    }

    /**
    * Resize image, by either cropping or adding a border.
    * @param  string  $target absolute filename of target file
    * @param  array   $config with keys ('gravity', 'background')
    * @return boolean         success
    */
    public function buildImage($target, array $config = array())
    {
        $src = $this->filename;
        if (empty($this->mode) || $this->mode === self::MODE_BORDER || $this->mode === self::MODE_CROP) {
            $enlargeAndCrop = null;
        } else {
            $enlargeAndCrop = '^';
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $enlargeAndCrop .= '^';
            }
        }
        $tgtWidth  = $this->width;
        $tgtHeight = $this->height;

        if (!empty($this->mode) && $this->mode === self::MODE_CROP) {
            $imgProp = getimagesize($src);
            $srcRatio = $imgProp[0] / $imgProp[1];
            $ratio = $this->width / $this->height;
            if ($srcRatio < $ratio) {
                $tgtWidth  = $tgtWidth  * $srcRatio / $ratio;
            } else {
                $tgtHeight = $tgtHeight / $srcRatio * $ratio;
            }
        }

        $cmd =
            $this->pathImageMagick . 'convert'
            .' -strip -interlace Plane -quality 85%'
            .' '.escapeshellarg($src)
            .' -resize '    .((int)$this->width).'x'.((int)$this->height).$enlargeAndCrop
            .' -gravity '   .escapeshellarg(!empty($config['gravity'])? $config['gravity']: 'center')
            .' -background '.escapeshellarg(!empty($this->background) ? $this->background : 'white')
            .' -extent '    .((int)$tgtWidth).'x'.((int)$tgtHeight)
            .' '.escapeshellarg($target)
        ;
        $success = system($cmd, $retval);
        return $success;
    }
}
