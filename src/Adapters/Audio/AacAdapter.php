<?php
namespace wapmorgan\MediaFile\Adapters\Audio;

use wapmorgan\MediaFile\Adapters\Containers\Mpeg4Part12Adapter;
use wapmorgan\MediaFile\Adapters\AudioAdapter;

/**
 * Aac uses MPEG 4 Part 12/14 as container.
 * Does not provide functionality to work with MPEG 2 Part 7 (AAC) !
 */
class AacAdapter extends Mpeg4Part12Adapter implements AudioAdapter {
    public function getLength() {
        return $this->mvhd['duration'] / $this->mvhd['timescale'];
    }

    public function getBitRate() {
        return floor($this->mdat['size'] / ($this->mvhd['duration'] / $this->mvhd['timescale']) * 8);
    }

    public function getSampleRate() {
        foreach ($this->streams as $stream)
            return $stream['sample_rate'];
    }

    public function getChannels() {
        foreach ($this->streams as $stream)
            return $stream['channels'];
    }

    public function isVariableBitRate() {
        return false;
    }

    public function isLossless() {
        return false;
    }
}
