<?php
namespace wapmorgan\MediaFile;

interface ContainerAdapter {
    const AUDIO = 'audio';
    const VIDEO = 'video';

    public function countStreams();
    public function countVideoStreams();
    public function countAudioStreams();
    public function getStreams();
}
