<?php
namespace wapmorgan\MediaFile;

/**
 * All videos should contain:
 * - length
 * - width
 * - height
 */
interface VideoAdapter {
    public function getLength();
    public function getWidth();
    public function getHeight();
    public function getFramerate();
}
