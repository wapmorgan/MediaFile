<?php
namespace wapmorgan\MediaFile;

use Exception;
use wapmorgan\BinaryStream\BinaryStream;

/**
 * WMA uses ASF as a container
 */
class WmaAdapter extends AsfAdapter implements AudioAdapter {
    protected $length;
    protected $bitRate;
    protected $sampleRate;
    protected $channels;

    protected function scan() {
        parent::scan();
        $this->length = $this->properties['send_length'];
        foreach ($this->streams as $stream) {
            if ($stream['type'] == ContainerAdapter::AUDIO) {
                $this->bitRate = $stream['bit_rate'];
                $this->sampleRate = $stream['sample_rate'];
                $this->channels = $stream['channels'];
                break;
            }
        }
    }

    public function getLength() {
        return $this->length;
    }

    public function getBitRate() {
        return $this->bitRate;
    }

    public function getSampleRate() {
        return $this->sampleRate;
    }

    public function getChannelsMode() {
        return $this->channels;
    }

    public function isVariableBitRate() {
        return false;
    }

    public function isLossless() {
        return false;
    }
}
