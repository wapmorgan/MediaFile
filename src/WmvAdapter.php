<?php
namespace wapmorgan\MediaFile;

use Exception;
use wapmorgan\BinaryStream\BinaryStream;

/**
 * WMV uses ASF as a container
 */
class WmvAdapter extends AsfAdapter implements VideoAdapter {
    public function getLength() {}
    public function getWidth() {}
    public function getHeight() {}
    public function getFramerate() {}
}
