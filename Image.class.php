<?php

class Image {
  protected $filename;
  protected $imageInfo = array();
  protected $cachePath = '';
  protected $docRoot   = '';
  protected $width     = 0;
  protected $height    = 0;

  /**
   * [__construct description]
   * @param string $filename  relative path to docroot
   * @param string $cachePath absolute path
   * @param string $docRoot   absolute path
   */
  public function __construct($filename, $cachePath, $docRoot) {
    $this->cachePath = $cachePath;
    $this->docRoot = $docRoot;
    $this->filename = $filename;
  }

  public function isFile () {
    return file_exists($this->docRoot.$this->filename);
  }

  public function setWidth ($width) {
    return $this->width = abs((int)$width);
  }

  public function setHeight ($height) {
    return $this->height = abs((int)$height);
  }

  public function buildImage () {
    $image = getimagesize($this->docRoot.$this->filename);
    $image['width']  = $image[0];
    $image['height'] = $image[1];
    $image['ratio']  = $image['width'] / $image['height'];

    if (empty($this->width)) {
      $this->width  = floor($this->height / $image['height'] * $image['width']);
    }
    elseif (empty($this->height)) {
      $this->height = floor($this->width  / $image['width'] * $image['height']);
    }
    $this->imageInfo = $image;
    if ($this->width != $image['width'] && $this->height != $image['height']) {
      $cmd =
        'convert'
        .' -strip -interlace Plane -gaussian-blur 0.05 -quality 85%'
        .' '.escapeshellarg($this->docRoot.$this->filename)
        .' -resize '.(int)$this->width.'x'.(int)$this->height
        .' '.escapeshellarg($this->cachePath.$this->getCacheFilename())
      ;
      exec($cmd);
    }
    return TRUE;
  }

  public function getCacheFilename () {
    return
      str_replace('/','_',
        preg_replace(
          '#_\d+x\d+$#',
          '',
          preg_replace('#\.(.+?)$#','',$this->filename)
        )
      )
      .'-'.$this->width.'x'.$this->height.'.jpg'
    ;
  }

  public function hasCache () {
    return file_exists($this->getCacheFilename());
  }

  public function returnCacheUrl () {
    return dirname($_SERVER['SCRIPT_URI']).'/'.basename($this->cachePath).'/'.$this->getCacheFilename();
  }

}