<?php
namespace wapmorgan\MediaFile;

use wapmorgan\FileTypeDetector\Detector;
use wapmorgan\MediaFile\Adapters\Audio\AacAdapter;
use wapmorgan\MediaFile\Adapters\Audio\AmrAdapter;
use wapmorgan\MediaFile\Adapters\Audio\FlacAdapter;
use wapmorgan\MediaFile\Adapters\Audio\Mp3Adapter;
use wapmorgan\MediaFile\Adapters\Audio\OggAdapter;
use wapmorgan\MediaFile\Adapters\Audio\WavAdapter;
use wapmorgan\MediaFile\Adapters\Audio\WmaAdapter;
use wapmorgan\MediaFile\Adapters\AudioAdapter;
use wapmorgan\MediaFile\Adapters\ContainerAdapter;
use wapmorgan\MediaFile\Adapters\Containers\AsfAdapter;
use wapmorgan\MediaFile\Adapters\Video\AviAdapter;
use wapmorgan\MediaFile\Adapters\Video\MkvAdapter;
use wapmorgan\MediaFile\Adapters\Video\Mp4Adapter;
use wapmorgan\MediaFile\Adapters\Video\WmvAdapter;
use wapmorgan\MediaFile\Adapters\VideoAdapter;
use wapmorgan\MediaFile\Exceptions\FileAccessException;

class MediaFile {
    const AUDIO = 'audio';
    const VIDEO = 'video';

    static protected $formatHandlers = [
        Detector::WAV => WavAdapter::class,
        Detector::MP3 => Mp3Adapter::class,
        Detector::FLAC => FlacAdapter::class,
        Detector::AAC => AacAdapter::class,
        Detector::OGG => OggAdapter::class,
        Detector::AMR => AmrAdapter::class,
        Detector::WMA => WmaAdapter::class,
        Detector::AVI => AviAdapter::class,
        Detector::ASF => AsfAdapter::class,
        Detector::WMV => WmvAdapter::class,
        Detector::MP4 => Mp4Adapter::class,
        Detector::MKV => MkvAdapter::class,
    ];

    /** @var string */
    protected $filename;
    protected $type;

    /** @var string */
    protected $format;

    /** @var AudioAdapter|VideoAdapter */
    public $adapter;

    /**
     * @param string $filename
     * @return MediaFile
     * @throws FileAccessException
     */
    static public function open($filename)
    {
        if (!file_exists($filename) || !is_readable($filename))
            throw new FileAccessException('File "'.$filename.'" is not available for reading!');

        $type = Detector::detectByFilename($filename) ?: Detector::detectByContent($filename);

        if ($type === false)
            throw new FileAccessException('Unknown format for file "'.$filename.'"!');

        if (!isset(self::$formatHandlers[$type[1]]))
            throw new FileAccessException('File "'.$filename.'" is not supported, it\'s "'.$type[0].'/'.$type[1].'"!');

        return new self($filename, $type[1]);
    }

    /**
     * MediaFile constructor.
     *
     * @param string $filename
     * @param string $format
     * @throws FileAccessException
     */
    public function __construct($filename, $format) {
        if (!file_exists($filename) || !is_readable($filename)) throw new Exceptions\FileAccessException('File "'.$filename.'" is not available for reading!');

        if (!isset(self::$formatHandlers[$format]))
            throw new FileAccessException('Format "'.$format.'" does not have a handler!');

        $adapter_class = self::$formatHandlers[$format];
        $this->adapter = new $adapter_class($filename);

        $this->filename = $filename;
        $this->format = $format;
    }

    /**
     * @return bool
     */
    public function isAudio() {
        return $this->adapter instanceof AudioAdapter;
    }

    /**
     * @return bool
     */
    public function isVideo() {
        return $this->adapter instanceof VideoAdapter;
    }

    /**
     * @return bool
     */
    public function isContainer() {
        return $this->adapter instanceof ContainerAdapter;
    }

    /**
     * @return string
     */
    public function getFormat() {
        return $this->format;
    }

    /**
     * @return AudioAdapter
     */
    public function getAudio() {
        return $this->adapter;
    }

    /**
     * @return VideoAdapter
     * @throws FileAccessException
     */
    public function getVideo() {
        return $this->adapter;
    }
}
