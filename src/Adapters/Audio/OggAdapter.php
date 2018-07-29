<?php
namespace wapmorgan\MediaFile\Adapters\Audio;

use wapmorgan\BinaryStream\BinaryStream;
use wapmorgan\MediaFile\Adapters\AudioAdapter;
use wapmorgan\MediaFile\Exceptions\FileAccessException;

/**
 * Based on specifications from https://xiph.org/vorbis/doc/Vorbis_I_spec.html
 */
class OggAdapter implements AudioAdapter {
    protected $filename;
    protected $stream;
    protected $header;

    /**
     * OggAdapter constructor.
     *
     * @param $filename
     *
     * @throws \wapmorgan\MediaFile\Exceptions\FileAccessException
     * @throws \Exception
     */
    public function __construct($filename) {
        if (!file_exists($filename) || !is_readable($filename)) throw new FileAccessException('File "'.$filename.'" is not available for reading!');
        $this->filename = $filename;
        $this->stream = new BinaryStream($filename);
        $this->stream->saveGroup('ogg_page', array(
            's:tag' => 4,
            's:_' => 1,
            'i:type' => 8,
            's:__' => 8,
            'i:number' => 32,
            'i:page_sequence' => 32,
            's:crc' => 4,
            'i:segments_count' => 8,
        ));
        $this->stream->saveGroup('vorbis_identification_header', array(
            'i:type' => 8,
            's:tag' => 6,
            'i:version' => 32,
            'i:channels' => 8,
            'i:sample_rate' => 32,
            'i:bitrate_maximum' => 32,
            'i:bitrate_nominal' => 32,
            'i:bitrate_minimum' => 32,
            'blocksize_0' => 4,
            'blocksize_1' => 4,
            'framing' => 1,
            'align' => 7,
        ));
        $this->stream->saveGroup('vorbis_comments_header', array(
            'i:type' => 8,
            's:tag' => 6,
            'i:length' => 32,
        ));
        $this->stream->saveGroup('vorbis_setup_header', array(
            'i:type' => 8,
            's:tag' => 6,
            'i:codebook_count' => 8,
        ));
        $this->scan();
    }

    /**
     *
     */
    protected function scan() {
        $identification = $this->stream->readGroup('ogg_page');
        $identification['segments'] = $this->stream->readString($identification['segments_count']);
        $header = $this->stream->readGroup('vorbis_identification_header');
        $this->header = $header;

        $comments = $this->stream->readGroup('ogg_page');
        $comments['segments'] = $this->stream->readString($comments['segments_count']);
        $header = $this->stream->readGroup('vorbis_comments_header');
        $header['vendor'] = $this->stream->readString($header['length']);
        $header['list_length'] = $this->stream->readInteger(32);
        for ($i = 0; $i < $header['list_length']; $i++) {
            $header['list'][$i]['length'] = $this->stream->readInteger(32);
            $header['list'][$i]['value'] = $this->stream->readString($header['list'][$i]['length']);
        }
        $this->stream->skip(1); // skip 1 byte (with framing bit)

        // if ($this->stream->compare(4, 'OggS')) { // setup header is in third Ogg-page
        //     $setup = $this->stream->readGroup('ogg_page');
        //     $setup['segments'] = $this->stream->readString($setup['segments_count']);
        // }
        // $header = $this->stream->readGroup('vorbis_setup_header');
    }

    /**
     * @return float|int
     */
    public function getLength() {
        return filesize($this->filename) / $this->getBitRate() * 8;
    }

    /**
     * @return float|int
     */
    public function getBitRate() {
        if ($this->header['bitrate_nominal'] > 0)
            return $this->header['bitrate_nominal'];
        else
            return ($this->header['bitrate_maximum'] + $this->header['bitrate_minimum']) / 2;
    }

    /**
     * @return int
     */
    public function getSampleRate() {
        return $this->header['sample_rate'];
    }

    /**
     * @return int
     */
    public function getChannels() {
        return $this->header['channels'];
    }

    /**
     * @return bool
     */
    public function isVariableBitRate() {
        // if ($this->header['bitrate_maximum'] == $this->header['bitrate_nominal'] && $this->header['bitrate_nominal'] == $this->header['bitrate_minimum'])
        //     return false;
        if ($this->header['bitrate_nominal'] == 0 && $this->header['bitrate_maximum'] > 0 && $this->header['bitrate_minimum'] > 0)
            return true;
        return false;
    }

    /**
     * @return bool
     */
    public function isLossless() {
        return false;
    }
}
