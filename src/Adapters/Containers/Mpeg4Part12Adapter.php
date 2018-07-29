<?php
namespace wapmorgan\MediaFile\Adapters\Containers;

use wapmorgan\BinaryStream\BinaryStream;
use wapmorgan\MediaFile\Adapters\ContainerAdapter;
use wapmorgan\MediaFile\Exceptions\FileAccessException;
use wapmorgan\MediaFile\Exceptions\ParsingException;

/**
 * This class can read MPEG 4 Part 12/14 media container with only audio inside.
 * Based on specifications from http://l.web.umkc.edu/lizhu/teaching/2016sp.video-communication/ref/mp4.pdf.
 * Does not provide functionality to work with MPEG 2 Part 7 (AAC) !
 */
class Mpeg4Part12Adapter {

    const ISO = 1;
    const MP4_1 = 2;
    const MP4_2 = 3;

    protected $filename;
    protected $type;
    protected $stream;
    protected $streams;
    protected $mvhd;
    protected $stsd_audio;
    protected $mdat;

    public function __construct($filename) {
        if (!file_exists($filename) || !is_readable($filename)) throw new FileAccessException('File "'.$filename.'" is not available for reading!');
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
        $this->stream->saveGroup('mvhd_box_big', array(
            'i:creation_time' => 64,
            'i:modification_time' => 64,
            'i:timescale' => 32,
            'i:duration' => 64,
            'i:rate' => 32,
            'i:volume' => 16,
            's:_' => 70,
            'i:next_track' => 32,
        ));
        $this->stream->saveGroup('mvhd_box_little', array(
            'i:creation_time' => 32,
            'i:modification_time' => 32,
            'i:timescale' => 32,
            'i:duration' => 32,
            'i:rate' => 32,
            'i:volume' => 16,
            's:_' => 70,
            'i:next_track' => 32,
        ));
        $this->stream->saveGroup('tkhd_box_big', array(
            'i:creation_time' => 64,
            'i:modification_time' => 64,
            'i:track_id' => 32,
            'i:reserved' => 32,
            'i:duration' => 64,
            's:_' => 8,
            'i:layer' => 16,
            'i:group' => 16,
            'i:volume' => 16,
            's:__' => 38,
            'i:width' => 32,
            'i:height' => 32,
        ));
        $this->stream->saveGroup('tkhd_box_little', array(
            'i:creation_time' => 32,
            'i:modification_time' => 32,
            'i:track_id' => 32,
            'i:reserved' => 32,
            'i:duration' => 32,
            's:_' => 8,
            'i:layer' => 16,
            'i:group' => 16,
            'i:volume' => 16,
            's:__' => 38,
            'i:width' => 32,
            'i:height' => 32,
        ));
        $this->stream->saveGroup('mdhd_box_big', array(
            'i:creation_time' => 64,
            'i:modification_time' => 64,
            'i:timescale' => 32,
            'i:duration' => 64,
            'i:language' => 16,
            'i:_' => 16,
        ));
        $this->stream->saveGroup('mdhd_box_little', array(
            'i:creation_time' => 32,
            'i:modification_time' => 32,
            'i:timescale' => 32,
            'i:duration' => 32,
            'i:language' => 16,
            'i:_' => 16,
        ));
        $this->stream->saveGroup('hdlr_box', array(
            'i:defined' => 32,
            's:handler_type' => 4,
            's:_' => 12,
        ));
        $this->stream->saveGroup('AudioSampleEntry', array(
            's:_' => 6,
            'i:reference' => 16,
            's:__' => 8,
            'i:channelCount' => 16,
            'i:sampleSize' => 16,
            'i:defined' => 16,
            'i:_' => 16,
            'i:sampleRate' => 32,
        ));
        $this->stream->saveGroup('VideoSampleEntry', array(
            's:_' => 24,
            'i:width' => 16,
            'i:height' => 16,
            'i:horizontal_resolution' => 32,
            'i:vertical_resolution' => 32,
            'i:_' => 32,
            'i:frame_count' => 16,
            's:compressor' => 32,
            's:__' => 4,
        ));
        $this->scan();
    }

    protected function scan() {
        $ftyp = $this->stream->readGroup('ftyp_box');
        if ($ftyp['type'] != 'ftyp')
            throw new ParsingException('This file is not an MPEG-4 Part 12/14 container!');

        switch ($ftyp['major']) {
            case 'isom': $this->type = self::ISO; break;
            case 'mp41': $this->type = self::MP4_1; break;
            case 'mp42': $this->type = self::MP4_2; break;
        }

        $this->stream->skip($ftyp['size'] - 16);

        if ($this->getNextBoxType() == 'mdat') {
            $this->stream->mark('mdat');;
            $this->mdat = $this->stream->readGroup('box_header');
            $this->stream->skip($this->mdat['size'] - 8); // 8 - size of box header structure
        }

        $this->stream->mark('moov');
        $moov = $this->stream->readGroup('box_header');
        if ($moov['type'] != 'moov')
            throw new ParsingException('This file does not have "moov" box!');

        $this->mvhd = $this->stream->readGroup('full_box_header');
        if ($this->mvhd['type'] != 'mvhd')
            throw new ParsingException('This file does not have "mvhd" box!');
        $this->mvhd += $this->stream->readGroup('mvhd_box_'.($this->mvhd['version'] == 0 ? 'little' : 'big'));

        $i = 0;
        // tracks scanning
        while ($this->getNextBoxType() == 'trak') {
            $this->stream->mark('track_'.$i);
            $trak = $this->stream->readGroup('box_header');

            if ($this->getNextBoxType() != 'tkhd')
                throw new ParsingException('This file does not have "tkhd" box!');
            $tkhd = $this->stream->readGroup('full_box_header');
            $tkhd += $this->stream->readGroup('tkhd_box_'.($tkhd['version'] == 0 ? 'little' : 'big'));
            $this->streams[$tkhd['track_id']] = array(
                'length' => $tkhd['duration'] / $this->mvhd['timescale'],
                'codec' => null
                );

            // track headers scanning
            while (in_array($next_box = $this->getNextBoxType(), array('tref', 'edts', 'mdia'))) {
                switch ($next_box) {
                    case 'tref':
                    case 'edts':
                        $box = $this->stream->readGroup('box_header');
                        $this->stream->skip($box['size'] - 8); // 8 - size of box header structure
                        break;

                    case 'mdia':
                        $this->stream->mark('mdia');
                        $mdia = $this->stream->readGroup('box_header');

                        // media information scanning
                        while (in_array($next_box = $this->getNextBoxType(), array('mdhd', 'hdlr', 'minf', 'vmhd', 'smhd', 'hmhd', 'nmhd', 'dinf', 'dref', 'stbl', 'stsd', 'stts', 'ctts', 'stsc', 'stsz', 'stz2', 'stco', 'co64', 'stss', 'stsh', 'padb', 'stdp', 'sdtp', 'sbgp', 'sgpd', 'subs'))) {
                            switch ($next_box) {
                                case 'mdhd':
                                    $mdhd = $this->stream->readGroup('full_box_header');
                                    if ($mdhd['type'] != 'mdhd')
                                        throw new ParsingException('This file does not have "mdhd" box!');

                                    $mdhd += $this->stream->readGroup('mdhd_box_'.($mdhd['version'] == 0 ? 'little' : 'big'));
                                    break;

                                case 'hdlr':
                                    $hdlr = $this->stream->readGroup('full_box_header');
                                    $hdlr += $this->stream->readGroup('hdlr_box');
                                    $this->streams[$tkhd['track_id']]['type'] = ($hdlr['handler_type'] == 'vide') ? ContainerAdapter::VIDEO
                                        : ($hdlr['handler_type'] == 'soun' ? ContainerAdapter::AUDIO : null);
                                    $this->stream->skip($hdlr['size'] - 32); // 32 - size of full_box_header + hdlr_box
                                    break;

                                // should be here to make possible scanning of inner boxes
                                case 'minf':
                                    $minf = $this->stream->readGroup('box_header');
                                    break;

                                // should be here to make possible scanning of inner boxes
                                case 'stbl':
                                    $stbl = $this->stream->readGroup('box_header');
                                    break;

                                case 'stsd':
                                    $box = $this->stream->readGroup('full_box_header');
                                    $box['entry_count'] = $this->stream->readInteger(32);
                                    if ($box['entry_count'] > 1)
                                        throw new ParsingException('It\' strange! File has more 1 stsd entries in one track! Please, send your file text of this error as Pull Request on github to discover the problem.');

                                    $box = $this->stream->readGroup('box_header');
                                    switch ($this->streams[$tkhd['track_id']]['type']) {
                                        case ContainerAdapter::AUDIO:
                                            $box += $this->stream->readGroup('AudioSampleEntry');
                                            $this->streams[$tkhd['track_id']]['codec'] = $box['type'];
                                            $this->streams[$tkhd['track_id']]['channels'] = $box['channelCount'];
                                            $this->streams[$tkhd['track_id']]['sample_rate'] = $box['sampleRate'] >> 16;
                                            break;

                                        case ContainerAdapter::VIDEO:
                                            $box += $this->stream->readGroup('VideoSampleEntry');
                                            $this->streams[$tkhd['track_id']]['codec'] = $box['type'];
                                            $this->streams[$tkhd['track_id']]['width'] = $box['width'];
                                            $this->streams[$tkhd['track_id']]['height'] = $box['height'];
                                            $this->streams[$tkhd['track_id']]['framerate'] = 0;
                                            break;
                                    }
                                    break;

                                // skip any non-informative box
                                default:
                                    $box = $this->stream->readGroup('box_header');
                                    $this->stream->skip($box['size'] - 8);
                                    break;
                            }
                        }
                        $this->stream->go('mdia');
                        $this->stream->skip($mdia['size']);
                        break;
                }
            }

            // jump to next 2nd-level box
            $this->stream->go('track_'.$i++);
            $this->stream->skip($trak['size']);

            $i++;

        }

        // find for mdat
        if (empty($this->mdat)) {
            while (!$this->stream->isEnd() && $this->getNextBoxType() != 'mdat') {
                $box = $this->stream->readGroup('box_header');
                $this->stream->skip($box['size'] - 8); // 8 - size of box_header structure
            }
            if (!$this->stream->isEnd()) $this->mdat = $this->stream->readGroup('box_header');
        }

    }

    protected function getNextBoxType() {
        $this->stream->mark('current_box');
        $this->stream->skip(4);
        $type = $this->stream->readString(4);
        $this->stream->go('current_box');
        return $type;
    }

    protected function readUntilDoubleNull() {
        $string = null;
        $previous_null = false;
        while (!$this->stream->isEnd()) {
            $char = $this->stream->readChar();
            if ($char == "\00") {
                if ($previous_null) {
                    $string = substr($string, 0, -1);
                    break;
                }
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
}
