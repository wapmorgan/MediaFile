<?php
namespace wapmorgan\MediaFile\Adapters;

use Flac;
use wapmorgan\MediaFile\AudioAdapter;
use wapmorgan\MediaFile\Exceptions\FileAccessException;

class FlacAdapter implements AudioAdapter {
    protected $filename;
    protected $flac;

    public function __construct($filename) {
        if (!file_exists($filename) || !is_readable($filename)) throw new FileAccessException('File "'.$filename.'" is not available for reading!');
        $this->filename = $filename;
        $this->flac = new Flac($filename);
    }

    public function getLength() {
        return $this->flac->streamDuration;
    }

    public function getBitRate() {
        return floor($this->flac->streamBitsPerSample * $this->flac->streamTotalSamples / $this->flac->streamDuration);
    }

    public function getSampleRate() {
        return $this->flac->streamSampleRate;
    }

    public function getChannels() {
        return $this->flac->streamChannels;
    }

    public function isVariableBitRate() {
        return true;
    }

    public function isLossless() {
        return false;
    }
}
