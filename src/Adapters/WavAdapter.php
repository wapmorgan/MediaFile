<?php
namespace wapmorgan\MediaFile\Adapters;

use BoyHagemann\Wave\Wave;
use wapmorgan\MediaFile\AudioAdapter;
use wapmorgan\MediaFile\Exceptions\FileAccessException;

class WavAdapter implements AudioAdapter {
    protected $filename;
    protected $wav;
    protected $metadata;

    public function __construct($filename) {
        if (!file_exists($filename) || !is_readable($filename)) throw new FileAccessException('File "'.$filename.'" is not available for reading!');
        $this->filename = $filename;
        $this->wav = new Wave();
        $this->wav->setFilename($filename);
        $this->metadata = $this->wav->getMetadata();
    }

    public function getMetadata() {
        return $this->metadata;
    }

    public function getLength() {
        $bytesPerSecond = $this->metadata->getBytesPerSecond();
        return (filesize($this->filename) - 44) / $bytesPerSecond;
    }

    public function getBitRate() {
        return floor($this->metadata->getBytesPerSecond() / 1000) * 1000;
    }

    public function getSampleRate() {
        return $this->metadata->getSampleRate();
    }

    public function getChannels() {
        return $this->metadata->getChannels();
    }

    public function isVariableBitRate() {
        return false;
    }

    public function isLossless() {
        return false;
    }
}
