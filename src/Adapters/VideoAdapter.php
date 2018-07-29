<?php
namespace wapmorgan\MediaFile\Adapters;

/**
 * All videos should contain:
 * - length
 * - width
 * - height
 */
interface VideoAdapter {

    /**
     * @return int
     */
    public function getLength();

    /**
     * @return int
     */
    public function getWidth();

    /**
     * @return int
     */
    public function getHeight();

    /**
     * @return int
     */
    public function getFrameRate();
}
