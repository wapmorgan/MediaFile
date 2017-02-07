<?php
namespace wapmorgan\MediaFile;

use Exception;
use wapmorgan\BinaryStream\BinaryStream;

/**
 * WMV uses ASF as a container
 */
class WmvAdapter extends AsfAdapter implements VideoAdapter {
    protected $length;
    protected $width;
    protected $height;
    protected $framerate;

    protected function scan() {
        parent::scan();
        $this->length = $this->properties['send_length'];
        foreach ($this->streams as $stream) {
            if ($stream['type'] == ContainerAdapter::VIDEO && empty($this->width)) {
                $this->width = $stream['width'];
                $this->height = $stream['height'];
                $this->framerate = $stream['framerate'];
            }
        }
    }

    public function getLength() {
        return $this->length;
    }

    public function getWidth() {
        return $this->width;
    }

    public function getHeight() {
        return $this->height;
    }

    public function getFramerate() {
        return $this->framerate;
    }
}
