<?php
namespace wapmorgan\MediaFile\Adapters;

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

    /**
     * @return int
     */
    public function countStreams();

    /**
     * @return int
     */
    public function countVideoStreams();

    /**
     * @return int
     */
    public function countAudioStreams();

    /**
     * @return array
     */
    public function getStreams();
}
