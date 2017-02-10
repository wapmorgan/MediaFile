<?php
namespace wapmorgan\MediaFile;

use wapmorgan\Mp3Info\Mp3Info;

class Mp3Adapter implements AudioAdapter {
    protected $filename;
    protected $mp3;

    public function __construct($filename) {
        if (!file_exists($filename) || !is_readable($filename)) throw new FileAccessException('File "'.$filename.'" is not available for reading!');
        $this->filename = $filename;
        $this->mp3 = new Mp3Info($filename);
    }

    public function getLength() {
        return $this->mp3->duration;
    }

    public function getBitRate() {
        return $this->mp3->bitRate;
    }

    public function getSampleRate() {
        return $this->mp3->sampleRate;
    }

    public function getChannels() {
        return $this->mp3->channel == 'mono' ? 1 : 2;
    }

    public function isVariableBitRate() {
        return $this->mp3->isVbr;
    }

    public function isLossless() {
        return false;
    }
}
