<?php
namespace wapmorgan\MediaFile;

/**
 * Containers should store some basic information about all streams:
 * - type
 * - codec
 * - length
 *
 * for videos:
 * - width
 * - height
 * - framerate
 *
 * for audios:
 * - channels
 * - sample_rate
 * - bit_rate
 */
interface ContainerAdapter {
    const AUDIO = 'audio';
    const VIDEO = 'video';

    public function countStreams();
    public function countVideoStreams();
    public function countAudioStreams();
    public function getStreams();
}
