<?php
namespace wapmorgan\MediaFile;

use wapmorgan\FileTypeDetector\Detector;

class MediaFile {
    const AUDIO = 'audio';
    const VIDEO = 'video';

    const WAV = 'wav';
    const MP3 = 'mp3';
    const FLAC = 'flac';
    const AAC = 'aac';
    const OGG = 'ogg';
    const AMR = 'amr';
    const WMA = 'wma';

    const AVI = 'avi';
    const ASF = 'asf';
    const WMV = 'wmv';
    const MP4 = 'mp4';

    protected $filename;
    protected $type;
    protected $format;
    public $adapter;

    static public function open($filename) {
        if (!file_exists($filename) || !is_readable($filename)) throw new FileAccessException('File "'.$filename.'" is not available for reading!');

        // by extension
        $type = Detector::detectByFilename($filename);
        // by binary tag
        if ($type === false)
            $type = Detector::detectByContent($filename);

        switch ($type[1]) {
            case Detector::WAV:
                $type = self::AUDIO;
                $format = self::WAV;
                break;
            case Detector::FLAC:
                $type = self::AUDIO;
                $format = self::FLAC;
                break;
            case Detector::AAC:
                $type = self::AUDIO;
                $format = self::AAC;
                break;
            case Detector::OGG:
                $type = self::AUDIO;
                $format = self::OGG;
                break;
            case Detector::MP3:
                $type = self::AUDIO;
                $format = self::MP3;
                break;
            case Detector::AMR:
                $type = self::AUDIO;
                $format = self::AMR;
                break;
            case Detector::WMA:
                $type = self::AUDIO;
                $format = self::WMA;
                break;

            case Detector::AVI:
                $type = self::VIDEO;
                $format = self::AVI;
                break;
            case Detector::ASF:
                $type = self::VIDEO;
                $format = self::ASF;
                break;
            case Detector::WMV:
                $type = self::VIDEO;
                $format = self::WMV;
                break;
            case Detector::MP4:
                $type = self::VIDEO;
                $format = self::MP4;
                break;

            default:
                throw new FileAccessException('Unknown format for file "'.$filename.'"!');
        }

        return new self($filename, $type, $format);
    }

    public function __construct($filename, $type, $format) {
        if (!file_exists($filename) || !is_readable($filename)) throw new FileAccessException('File "'.$filename.'" is not available for reading!');
        if (!in_array($type, array(self::AUDIO, self::VIDEO))) throw new FileAccessException('Type "'.$type.'" is not applicable!');



        if ($type == self::AUDIO) {
            switch ($format) {
                case self::WAV:
                    $this->adapter = new WavAdapter($filename);
                    break;
                case self::FLAC:
                    $this->adapter = new FlacAdapter($filename);
                    break;
                case self::AAC:
                    $this->adapter = new AacAdapter($filename);
                    break;
                case self::OGG:
                    $this->adapter = new OggAdapter($filename);
                    break;
                case self::MP3:
                    $this->adapter = new Mp3Adapter($filename);
                    break;
                case self::AMR:
                    $this->adapter = new AmrAdapter($filename);
                    break;
                case self::WMA:
                    $this->adapter = new WmaAdapter($filename);
                    break;

                default:
                    throw new FileAccessException('Type "'.$type.'" does not have format "'.$format.'"!');
            }
        } else {
            switch ($format) {
                case self::AVI:
                    $this->adapter = new AviAdapter($filename);
                    break;
                case self::ASF:
                    $this->adapter = new AsfAdapter($filename);
                    break;
                case self::WMV:
                    $this->adapter = new WmvAdapter($filename);
                    break;
                case self::MP4:
                    $this->adapter = new Mp4Adapter($filename);
                    break;

                default:
                    throw new FileAccessException('Type "'.$type.'" does not have format "'.$format.'"!');
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
            throw new FileAccessException('This is not a video file!');
    }
}
