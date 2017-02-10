<?php
namespace wapmorgan\MediaFile;

use Exception;
use wapmorgan\BinaryStream\BinaryStream;

/**
 * Based on specifications from https://xiph.org/vorbis/doc/Vorbis_I_spec.html
 */
class OggAdapter implements AudioAdapter {
    protected $filename;
    protected $stream;
    protected $header;

    static protected $channelModes = array(
        1 => self::MONO,
        2 => self::STEREO,
    );

    public function __construct($filename) {
        if (!file_exists($filename) || !is_readable($filename)) throw new Exception('File "'.$filename.'" is not available for reading!');
        $this->filename = $filename;
        $this->stream = new BinaryStream($filename);
        $this->stream->saveGroup('ogg_page', array(
            's:tag' => 4,
            'c:_' => 1,
            'c:type' => 1,
            's:_' => 8,
            'i:number' => 32,
            'i:page_sequence' => 32,
            's:crc' => 4,
            'c:segments_count' => 1,
        ));
        $this->stream->saveGroup('vorbis_identification_header', array(
            'c:type' => 1,
            's:tag' => 6,
            'i:version' => 32,
            'c:channels' => 1,
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
            'c:type' => 1,
            's:tag' => 6,
            'i:length' => 32,
        ));
        $this->stream->saveGroup('vorbis_setup_header', array(
            'c:type' => 1,
            's:tag' => 6,
            'c:codebook_count' => 1,
        ));
        $this->scan();
    }

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

    public function getLength() {
        return filesize($this->filename) / $this->getBitRate() * 8;
    }

    public function getBitRate() {
        if ($this->header['bitrate_nominal'] > 0)
            return $this->header['bitrate_nominal'];
        else
            return ($this->header['bitrate_maximum'] + $this->header['bitrate_minimum']) / 2;
    }

    public function getSampleRate() {
        return $this->header['sample_rate'];
    }

    public function getChannels() {
        return $this->header['channels'];
    }

    public function isVariableBitRate() {
        // if ($this->header['bitrate_maximum'] == $this->header['bitrate_nominal'] && $this->header['bitrate_nominal'] == $this->header['bitrate_minimum'])
        //     return false;
        if ($this->header['bitrate_nominal'] == 0 && $this->header['bitrate_maximum'] > 0 && $this->header['bitrate_minimum'] > 0)
            return true;
        return false;
    }

    public function isLossless() {
        return false;
    }
}
