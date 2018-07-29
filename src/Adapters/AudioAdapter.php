<?php
namespace wapmorgan\MediaFile\Adapters;

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

    /**
     * @return int
     */
    public function getLength();

    /**
     * @return int
     */
    public function getBitRate();

    /**
     * @return int
     */
    public function getSampleRate();

    /**
     * @return int
     */
    public function getChannels();

    /**
     * @return boolean
     */
    public function isVariableBitRate();

    /**
     * @return boolean
     */
    public function isLossless();
}
