<?php
namespace wapmorgan\MediaFile\Adapters\Video;

use wapmorgan\BinaryStream\BinaryStream;
use wapmorgan\MediaFile\Adapters\Containers\AsfAdapter;
use wapmorgan\MediaFile\Adapters\ContainerAdapter;
use wapmorgan\MediaFile\Adapters\VideoAdapter;

/**
 * WMV uses ASF as a container
 */
class WmvAdapter extends AsfAdapter implements VideoAdapter {
    protected $length;
    protected $width;
    protected $height;
    protected $framerate;

    /**
     * @throws \wapmorgan\MediaFile\Exceptions\ParsingException
     */
    protected function scan() {
        parent::scan();
        $this->length = $this->properties['send_length'];
        foreach ($this->streams as $stream) {
            if ($stream['type'] == ContainerAdapter::VIDEO && empty($this->width)) {
                $this->width = $stream['width'];
                $this->height = $stream['height'];
                $this->framerate = $stream['framerate'];
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
    public function getWidth() {
        return $this->width;
    }

    /**
     * @return int
     */
    public function getHeight() {
        return $this->height;
    }

    /**
     * @return int
     */
    public function getFrameRate() {
        return $this->framerate;
    }
}
