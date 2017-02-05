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
    const TRIPLE = 'triple';
    const QUADRO = 'quadro';
    const FIVE = 'five';
    const SIX = 'six';
    const SEVEN = 'seven';
    const EIGHT = 'eight';

    public function getLength();
    public function getBitRate();
    public function getSampleRate();
    public function getChannelsMode();
    public function isVariableBitRate();
    public function isLossless();
}
