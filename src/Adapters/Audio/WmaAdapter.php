<?php
namespace wapmorgan\MediaFile\Adapters\Audio;

use wapmorgan\BinaryStream\BinaryStream;
use wapmorgan\MediaFile\Adapters\Containers\AsfAdapter;
use wapmorgan\MediaFile\Adapters\AudioAdapter;
use wapmorgan\MediaFile\Adapters\ContainerAdapter;

/**
 * WMA uses ASF as a container
 */
class WmaAdapter extends AsfAdapter implements AudioAdapter {
    protected $length;
    protected $bitRate;
    protected $sampleRate;
    protected $channels;

    /**
     * @throws \wapmorgan\MediaFile\Exceptions\ParsingException
     */
    protected function scan() {
        parent::scan();
        $this->length = $this->properties['send_length'];
        if (defined('DEBUG') && DEBUG) var_dump($this->streams);
        foreach ($this->streams as $stream) {
            if ($stream['type'] == ContainerAdapter::AUDIO) {
                $this->bitRate = $stream['bit_rate'];
                $this->sampleRate = $stream['sample_rate'];
                $this->channels = $stream['channels'];
                break;
            }
        }
    }

    /**
     * @return int
     */
    public function getLength() {
        return $this->length;
    }

    /**
     * @return int
     */
    public function getBitRate() {
        return $this->bitRate;
    }

    /**
     * @return int
     */
    public function getSampleRate() {
        return $this->sampleRate;
    }

    /**
     * @return int
     */
    public function getChannels() {
        return $this->channels;
    }

    /**
     * @return bool
     */
    public function isVariableBitRate() {
        return false;
    }

    /**
     * @return bool
     */
    public function isLossless() {
        return false;
    }
}
