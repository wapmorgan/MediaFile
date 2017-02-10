<?php
namespace wapmorgan\MediaFile;

/**
 * All audios should contain:
 * - length
 * - bitRate
 * - sampleRate
 * - channelsMode
 */
abstract class AudioAdapter {

    const MONO = 1;
    const STEREO = 2;
    const TRIPLE = 4;
    const QUADRO = 8;
    const FIVE = 16;
    const SIX = 32;
    const SEVEN = 64;
    const EIGHT = 128;

    public function getLength();
    public function getBitRate();
    public function getSampleRate();
    public function getChannels();

    public function getChannelsMode() {
        // calling to higher level
        $mode = 0;
        $channelsCount = $this->getChannels();
        for ($i = 1; $i <= 8; $i++)
            $mode += pow(2, $i-1);
            if ($channelsCount == $i)
                break;
        }
        return $mode;
    }

    public function isVariableBitRate();
    public function isLossless();
}
