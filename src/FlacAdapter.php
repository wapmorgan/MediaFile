<?php
namespace wapmorgan\MediaFile;

use Exception;
use Flac;

class FlacAdapter implements AudioAdapter {
    protected $filename;
    protected $flac;

    static protected $channelModes = array(
        1 => self::MONO,
        2 => self::STEREO,
        3 => self::TRIPLE,
        4 => self::QUADRO,
        5 => self::FIVE,
        6 => self::SIX,
        7 => self::SEVEN,
        8 => self::EIGHT,
    );

    public function __construct($filename) {
        if (!file_exists($filename) || !is_readable($filename)) throw new Exception('File "'.$filename.'" is not available for reading!');
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

    public function getChannelsMode() {
        return self::$channelModes[$this->flac->streamChannels];
    }

    public function isVariableBitRate() {
        return true;
    }

    public function isLossless() {
        return false;
    }
}
