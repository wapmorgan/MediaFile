<?php
namespace wapmorgan\MediaFile;

use wapmorgan\FileTypeDetector\Detector;

class MediaFile {
    const AUDIO = 'audio';
    const VIDEO = 'video';

    const WAV = Detector::WAV;
    const MP3 = Detector::MP3;
    const FLAC = Detector::FLAC;
    const AAC = Detector::AAC;
    const OGG = Detector::OGG;
    const AMR = Detector::AMR;
    const WMA = Detector::WMA;

    const AVI = Detector::AVI;
    const ASF = Detector::ASF;
    const WMV = Detector::WMV;
    const MP4 = Detector::MP4;
    const MKV = Detector::MKV;

    protected $filename;
    protected $type;
    protected $format;
    public $adapter;

    static public function open($filename) {
        if (!file_exists($filename) || !is_readable($filename)) throw new Exceptions\FileAccessException('File "'.$filename.'" is not available for reading!');

        // by extension
        $type = Detector::detectByFilename($filename) ?: Detector::detectByContent($filename);

        if ($type === false)
            throw new Exceptions\FileAccessException('Unknown format for file "'.$filename.'"!');

        if (!in_array($type[1], array(
            self::WAV, self::FLAC, self::AAC, self::OGG, self::MP3, self::AMR, self::WMA,
            self::AVI, self::ASF, self::WMV, self::MP4, self::MKV
            )))
            throw new Exceptions\FileAccessException('File "'.$filename.'" is not a supported video/audio, it\'s "'.$type[0].'/'.$type[1].'"!');

        return new self($filename, $type[0], $type[1]);
    }

    public function __construct($filename, $type, $format) {
        if (!file_exists($filename) || !is_readable($filename)) throw new Exceptions\FileAccessException('File "'.$filename.'" is not available for reading!');
        if (!in_array($type, array(self::AUDIO, self::VIDEO))) throw new Exceptions\FileAccessException('Type "'.$type.'" is not applicable!');

        if ($type == self::AUDIO) {
            switch ($format) {
                case self::WAV:
                    $this->adapter = new Adapters\WavAdapter($filename);
                    break;
                case self::FLAC:
                    $this->adapter = new Adapters\FlacAdapter($filename);
                    break;
                case self::AAC:
                    $this->adapter = new Adapters\AacAdapter($filename);
                    break;
                case self::OGG:
                    $this->adapter = new Adapters\OggAdapter($filename);
                    break;
                case self::MP3:
                    $this->adapter = new Adapters\Mp3Adapter($filename);
                    break;
                case self::AMR:
                    $this->adapter = new Adapters\AmrAdapter($filename);
                    break;
                case self::WMA:
                    $this->adapter = new Adapters\WmaAdapter($filename);
                    break;

                default:
                    throw new Exceptions\FileAccessException('Type "'.$type.'" does not have format "'.$format.'"!');
            }
        } else {
            switch ($format) {
                case self::AVI:
                    $this->adapter = new Adapters\AviAdapter($filename);
                    break;
                case self::ASF:
                    $this->adapter = new Adapters\AsfAdapter($filename);
                    break;
                case self::WMV:
                    $this->adapter = new Adapters\WmvAdapter($filename);
                    break;
                case self::MP4:
                    $this->adapter = new Adapters\Mp4Adapter($filename);
                    break;
                case self::MKV:
                    $this->adapter = new Adapters\MkvAdapter($filename);
                    break;

                default:
                    throw new Exceptions\FileAccessException('Type "'.$type.'" does not have format "'.$format.'"!');
            }
        }

        $this->filename = $filename;
        $this->type = $type;
        $this->format = $format;
    }

    public function isAudio() {
        return $this->type == self::AUDIO;
    }

    public function isVideo() {
        return $this->type == self::VIDEO;
    }

    public function isContainer() {
        return $this->adapter instanceof ContainerAdapter;
    }

    public function getType() {
        return $this->type;
    }

    public function getFormat() {
        return $this->format;
    }

    public function getAudio() {
        if ($this->type == self::AUDIO)
            return $this->adapter;
    }

    public function getVideo() {
        if ($this->type == self::VIDEO)
            return $this->adapter;
        else
            throw new Exceptions\FileAccessException('This is not a video file!');
    }
}
