<?php
namespace wapmorgan\MediaFile;

/**
 * All audios should contain:
 * - length
 * - bitRate
 * - sampleRate
 * - channelsMode
 */
interface AudioAdapter {
    const STEREO = 'stereo';
    const MONO = 'mono';

    public function getLength();
    public function getBitRate();
    public function getSampleRate();
    public function getChannelsMode();
}
