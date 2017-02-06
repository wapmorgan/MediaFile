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
    const MONO = 'mono';
    const DUAL_MONO = 'dual_mono';
    const STEREO = 'stereo';
    const JOINT_STEREO = 'joint_stereo';
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
