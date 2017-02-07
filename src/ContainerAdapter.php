<?php
namespace wapmorgan\MediaFile;

/**
 * Containers should store some basic information about all streams:
 * for video:
 * - length
 * - width
 * - height
 * - framerate
 */
interface ContainerAdapter {
    const AUDIO = 'audio';
    const VIDEO = 'video';

    public function countStreams();
    public function countVideoStreams();
    public function countAudioStreams();
    public function getStreams();
}
