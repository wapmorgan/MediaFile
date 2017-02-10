<?php
namespace wapmorgan\MediaFile;

/**
 * All audios should contain following information:
 * - length
 * - bitRate
 * - sampleRate
 * - channels
 * - is vbr
 * - is lossless
 */
interface AudioAdapter {
    public function getLength();
    public function getBitRate();
    public function getSampleRate();
    public function getChannels();
    public function isVariableBitRate();
    public function isLossless();
}
