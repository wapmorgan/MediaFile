<?php
namespace wapmorgan\MediaFile;

use \Exception;

class MediaFile {
    const AUDIO = 'audio';
    const VIDEO = 'video';

    const WAV = 'wav';
    const MP3 = 'mp3';
    const FLAC = 'flac';
    const AAC = 'aac';
    const OGG = 'ogg';

    protected $filename;
    protected $type;
    protected $format;
    public $adapter;

    static public function open($filename) {
        if (!file_exists($filename) || !is_readable($filename)) throw new Exception('File "'.$filename.'" is not available for reading!');

        // by extension
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (!empty($ext)) {
            switch ($ext) {
                case 'wav':
                    $type = self::AUDIO;
                    $format = self::WAV;
                    break;
                case 'flac':
                    $type = self::AUDIO;
                    $format = self::FLAC;
                    break;
                case 'aac':
                case 'm4a':
                    $type = self::AUDIO;
                    $format = self::AAC;
                    break;
                case 'ogg':
                    $type = self::AUDIO;
                    $format = self::OGG;
                    break;
                default:
                    throw new Exception('Unknown file extension "'.$ext.'"!');
            }
        }
        // by binary tag

        return new self($filename, $type, $format);
    }

    public function __construct($filename, $type, $format) {
        if (!file_exists($filename) || !is_readable($filename)) throw new Exception('File "'.$filename.'" is not available for reading!');
        if (!in_array($type, array(self::AUDIO, self::VIDEO))) throw new Exception('Type "'.$type.'" is not applicable!');



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

                default:
                    throw new Exception('Type "'.$type.'" does not have format "'.$format.'"!');
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
}
