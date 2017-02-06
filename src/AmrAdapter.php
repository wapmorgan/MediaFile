<?php
namespace wapmorgan\MediaFile;

use Exception;
use wapmorgan\BinaryStream\BinaryStream;

/**
 * Based on information from http://hackipedia.org/File%20formats/Containers/AMR,%20Adaptive%20MultiRate/AMR%20format.pdf
 */
class AmrAdapter implements AudioAdapter {
    protected $filename;
    protected $stream;
    protected $bitrates;
    protected $length;

    static protected $modes = array(
        0 => 4750,
        1 => 5150,
        2 => 5900,
        3 => 6700,
        4 => 7400,
        5 => 7950,
        6 => 10200,
        7 => 12200,
    );

    static protected $frameSizes = array(
        0 => 13,
        1 => 14,
        2 => 16,
        3 => 18,
        4 => 20,
        5 => 21,
        6 => 27,
        7 => 32,
    );

    public function __construct($filename) {
        if (!file_exists($filename) || !is_readable($filename)) throw new Exception('File "'.$filename.'" is not available for reading!');
        $this->filename = $filename;
        $this->stream = new BinaryStream($filename);
        $this->stream->saveGroup('frame', array(
            '_' => 1,
            'mode' => 3,
            '__' => 4,
        ));
        $this->scan();
    }

    protected function scan() {
        if (!$this->stream->compare(5, '#!AMR'))
            throw new Exception('File is not an amr file!');
        $this->stream->readString(6);

        $bitrates = array();

        $frames = 0;
        while (!$this->stream->isEnd()) {
            $frames++;
            $frame = $this->stream->readGroup('frame');
            $bitrate = self::$modes[$frame['mode']];
            if (isset($bitrates[$bitrate])) $bitrates[$bitrate]++;
            else $bitrates[$bitrate] = 1;
            $this->stream->readString(self::$frameSizes[$frame['mode']]);
        }
        $this->bitrates = $bitrates;
        $this->length = 0.02 * $frames;
    }

    public function getLength() {
        return $this->length;
    }

    public function getBitRate() {
        return array_sum(array_keys($this->bitrates)) / count($this->bitrates);
    }

    public function getSampleRate() {
        return 8000;
    }

    public function getChannelsMode() {
        return self::MONO;
    }

    public function isVariableBitRate() {
        return true;
    }

    public function isLossless() {
        return false;
    }
}
