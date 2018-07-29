<?php
namespace wapmorgan\MediaFile\Adapters\Audio;

use wapmorgan\BinaryStream\BinaryStream;
use wapmorgan\MediaFile\Adapters\AudioAdapter;
use wapmorgan\MediaFile\Exceptions\FileAccessException;
use wapmorgan\MediaFile\Exceptions\ParsingException;

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

    /**
     * AmrAdapter constructor.
     *
     * @param $filename
     *
     * @throws \wapmorgan\MediaFile\Exceptions\FileAccessException
     * @throws \wapmorgan\MediaFile\Exceptions\ParsingException
     */
    public function __construct($filename) {
        if (!file_exists($filename) || !is_readable($filename)) throw new FileAccessException('File "'.$filename.'" is not available for reading!');
        $this->filename = $filename;
        $this->stream = new BinaryStream($filename);
        $this->stream->saveGroup('frame', array(
            '_' => 1,
            'mode' => 3,
            '__' => 4,
        ));
        $this->scan();
    }

    /**
     * @throws \wapmorgan\MediaFile\Exceptions\ParsingException
     */
    protected function scan() {
        if (!$this->stream->compare(5, '#!AMR'))
            throw new ParsingException('File is not an amr file!');
        $this->stream->skip(6);

        $bitrates = array();
        $frames = 0;
        while (!$this->stream->isEnd()) {
            $frames++;
            $frame = $this->stream->readGroup('frame');
            if ($this->stream->isEnd())
                break;
            $bitrate = self::$modes[$frame['mode']];
            if (isset($bitrates[$bitrate])) $bitrates[$bitrate]++;
            else $bitrates[$bitrate] = 1;
            $this->stream->skip(self::$frameSizes[$frame['mode']] - 1);
        }
        $this->bitrates = $bitrates;
        $this->length = 0.02 * $frames;
    }

    /**
     * @return int
     */
    public function getLength() {
        return $this->length;
    }

    /**
     * @return float|int
     */
    public function getBitRate() {
        return array_sum(array_keys($this->bitrates)) / count($this->bitrates);
    }

    /**
     * @return int
     */
    public function getSampleRate() {
        return 8000;
    }

    /**
     * @return int
     */
    public function getChannels() {
        return 1;
    }

    /**
     * @return bool
     */
    public function isVariableBitRate() {
        return true;
    }

    /**
     * @return bool
     */
    public function isLossless() {
        return false;
    }
}
