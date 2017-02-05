<?php
namespace wapmorgan\MediaFile;

use Exception;
use wapmorgan\BinaryStream\BinaryStream;

class AacAdapter implements AudioAdapter {
    protected $filename;
    protected $stream;
    protected $mvhd;
    protected $stsd_audio;
    protected $mdat;

    static protected $channelModes = array(
        1 => self::MONO,
        2 => self::STEREO,
    );

    public function __construct($filename) {
        if (!file_exists($filename) || !is_readable($filename)) throw new Exception('File "'.$filename.'" is not available for reading!');
        $this->filename = $filename;
        $this->stream = new BinaryStream($filename);
        $this->stream->setEndian(BinaryStream::BIG);
        $this->stream->saveGroup('box_header', array(
            'i:size' => 32,
            's:type' => 4,
        ));
        $this->stream->saveGroup('full_box_header', array(
            'i:size' => 32,
            's:type' => 4,
            'c:version' => 1,
            'flags' => 24,
        ));
        $this->stream->saveGroup('ftyp_box', array(
            'i:size' => 32,
            's:type' => 4,
            's:major' => 4,
            's:minor' => 4,
        ));
        $this->scan();
    }

    protected function scan() {
        $ftyp = $this->stream->readGroup('ftyp_box');
        if ($ftyp['type'] != 'ftyp')
            throw new Exception('This file is not an MPEG-4 Part 12/14 container!');

        $compatible_count = ($ftyp['size'] - 16) / 4;
        for ($i = 0; $i < $compatible_count; $i++)
            $ftyp['compatible'][] = $this->stream->readString(4);

        $this->stream->mark('moov');
        $moov = $this->stream->readGroup('box_header');
        if ($moov['type'] != 'moov')
            throw new Exception('This file does not have "moov" box!');

        $mvhd = $this->stream->readGroup('full_box_header');
        if ($mvhd['type'] != 'mvhd')
            throw new Exception('This file does not have "mvhd" box!');
        $mvhd += $this->stream->readGroup(array(
            'i:creation_time' => ($mvhd['version'] == 0 ? 32 : 64),
            'i:modification_time' => ($mvhd['version'] == 0 ? 32 : 64),
            'i:timescale' => 32,
            'i:duration' => ($mvhd['version'] == 0 ? 32 : 64),
            'i:rate' => 32,
            'i:volume' => 16,
            's:_' => 70,
            'i:next_track' => 32,
        ));
        $this->mvhd = $mvhd;

        $trak = $this->stream->readGroup('box_header');
        if ($trak['type'] != 'trak')
            throw new Exception('This file does not have "trak" box!');

        $tkhd = $this->stream->readGroup('full_box_header');
        if ($tkhd['type'] != 'tkhd')
            throw new Exception('This file does not have "tkhd" box!');
        $tkhd += $this->stream->readGroup(array(
            'i:creation_time' => ($tkhd['version'] == 0 ? 32 : 64),
            'i:modification_time' => ($tkhd['version'] == 0 ? 32 : 64),
            'i:track_id' => 32,
            'i:reserved' => 32,
            'i:duration' => ($tkhd['version'] == 0 ? 32 : 64),
            's:_' => 8,
            'i:layer' => 16,
            'i:group' => 16,
            'i:volume' => 16,
            's:__' => 38,
            'i:width' => 32,
            'i:height' => 32,
        ));

        $mdia = $this->stream->readGroup('box_header');
        if ($mdia['type'] != 'mdia')
            throw new Exception('This file does not have "mdia" box!');

        $mdhd = $this->stream->readGroup('full_box_header');
        if ($mdhd['type'] != 'mdhd')
            throw new Exception('This file does not have "mdhd" box!');

        $mdhd += $this->stream->readGroup(array(
            'i:creation_time' => ($mdhd['version'] == 0 ? 32 : 64),
            'i:modification_time' => ($mdhd['version'] == 0 ? 32 : 64),
            'i:timescale' => 32,
            'i:duration' => ($mdhd['version'] == 0 ? 32 : 64),
            'i:language' => 16,
            'i:_' => 16,
        ));

        $hdlr = $this->stream->readGroup('full_box_header');
        if ($hdlr['type'] != 'hdlr')
            throw new Exception('This file does not have "hdlr" box!');
        $hdlr += $this->stream->readGroup(array(
            'i:defined' => 32,
            's:handler_type' => 4,
            's:_' => 12,
        ));
        $hdlr['name'] = $this->readUntilDoubleNull();

        $minf = $this->stream->readGroup('box_header');
        if ($minf['type'] != 'minf')
            throw new Exception('This file does not have "minf" box!');

        $smhd = $this->stream->readGroup('full_box_header');
        if ($smhd['type'] != 'smhd')
            throw new Exception('This file does not have "smhd" box!');
        $smhd += $this->stream->readGroup(array(
            'i:balance' => 16,
            'i:_' => 16,
        ));

        $box = $this->stream->readGroup('box_header');
        if ($box['type'] == 'dinf') {
            $this->stream->skip($box['size'] - 8);
            $box = $this->stream->readGroup('box_header');
        }
        if ($box['type'] != 'stbl')
            throw new Exception('This file does not have "stbl" box!');

        $box = $this->stream->readGroup('full_box_header');
        if ($box['type'] != 'stsd')
            throw new Exception('This file does not have "stsd" box!');
        $box['entry_count'] = $this->stream->readInteger(32);

        $box = $this->stream->readGroup('box_header');
        $box += $this->stream->readGroup(array(
            's:_' => 6,
            'i:reference' => 16,
            's:__' => 8,
            'i:channelCount' => 16,
            'i:sampleSize' => 16,
            'i:defined' => 16,
            'i:_' => 16,
            'i:sampleRate' => 32,
        ));
        $this->stsd_audio = $box;

        // calculate bit rate
        $this->stream->go('moov');
        $this->stream->skip($moov['size']);

        while (true) {
            $box = $this->stream->readGroup('box_header');
            if ($box['type'] != 'mdat') {
                $this->stream->skip($box['size'] - 8);
                continue;
            } else {
                $this->mdat = $box;
                break;
            }
        }
    }

    protected function readUntilDoubleNull() {
        $string = null;
        $previous_null = false;
        while (true) {
            $char = $this->stream->readChar();
            if ($char == "\00") {
                if ($previous_null)
                    break;
                else
                    $previous_null = true;
            } else
                $previous_null = false;
            $string .= $char;
        }
        return $string;
    }

    protected function readBox() {
        $box = $this->stream->readGroup('box_header');
        if ($box['size'] == 1)
            $box['extended_size'] = $this->stream->readInt(64);
        if ($box['type'] == 'uuid')
            $box['usertype'] = $this->string->readString(16);
        return $box;
    }

    public function getLength() {
        return $this->mvhd['duration'] / $this->mvhd['timescale'];
    }

    public function getBitRate() {
        return floor($this->mdat['size'] / ($this->mvhd['duration'] / $this->mvhd['timescale']) * 8);
    }

    public function getSampleRate() {
        return $this->mvhd['timescale'];
    }

    public function getChannelsMode() {
        return self::$channelModes[$this->stsd_audio['channelCount']];
    }

    public function isVariableBitRate() {
        return false;
    }

    public function isLossless() {
        return false;
    }
}
