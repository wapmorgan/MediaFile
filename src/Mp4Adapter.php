<?php
namespace wapmorgan\MediaFile;

class Mp4Adapter extends Mpeg4Part12Adapter implements VideoAdapter, ContainerAdapter {

    public function getLength() {
        return $this->mvhd['duration'] / $this->mvhd['timescale'];
    }

    public function getWidth() {
        foreach ($this->streams as $stream) {
            if ($stream['type'] == ContainerAdapter::VIDEO)
                return $stream['width'];
        }
    }

    public function getHeight() {
        foreach ($this->streams as $stream) {
            if ($stream['type'] == ContainerAdapter::VIDEO)
                return $stream['height'];
        }
    }

    public function getFramerate() {
        foreach ($this->streams as $stream) {
            if ($stream['type'] == ContainerAdapter::VIDEO)
                return $stream['framerate'];
        }
    }

    public function countStreams() {
        return count($this->streams);
    }

    public function countVideoStreams() {
        $count = 0;
        foreach ($this->streams as $stream)
            if ($stream['type'] == ContainerAdapter::VIDEO) $count++;
        return $count;
    }

    public function countAudioStreams() {
        $count = 0;
        foreach ($this->streams as $stream)
            if ($stream['type'] == ContainerAdapter::AUDIO) $count++;
        return $count;
    }

    public function getStreams() {
        return $this->streams;
    }
}
