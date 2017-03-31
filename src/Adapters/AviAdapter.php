<?php
namespace wapmorgan\MediaFile\Adapters;

use wapmorgan\BinaryStream\BinaryStream;
use wapmorgan\MediaFile\ContainerAdapter;
use wapmorgan\MediaFile\Exceptions\FileAccessException;
use wapmorgan\MediaFile\Exceptions\ParsingException;
use wapmorgan\MediaFile\VideoAdapter;

/**
 * Based on spcecifications from http://www.alexander-noe.com/video/documentation/avi.pdf
 * Avi format:
 * (list) RIFF
 *  \- (list) LIST
 *      \- (list) hdrl
 *          \- (chunk) avih
 *          \- (list*n) strl
 *                        \- (chunk) strh
 *                        \- (chunk) strf
 *                        \- (chunk) strn [optional]
 *                        \- (chunk) indx
 *          \- (list) odml
 *                     \- (chunk) dmlh
 *          \- (chunk) JUNK
 */
class AviAdapter implements VideoAdapter, ContainerAdapter {
    protected $filename;
    protected $stream;
    protected $avih;
    protected $length;
    protected $framerate;

    static protected $streamTypes = array(
        'vids' => ContainerAdapter::VIDEO,
        'auds' => ContainerAdapter::AUDIO,
    );

    public function __construct($filename) {
        if (!file_exists($filename) || !is_readable($filename)) throw new FileAccessException('File "'.$filename.'" is not available for reading!');
        $this->filename = $filename;
        $this->stream = new BinaryStream($filename);
        // $this->stream->setEndian(BinaryStream::BIG);
        $this->stream->saveGroup('list', array(
            's:list' => 4,
            'i:size' => 32,
            's:tag' => 4,
        ));
        $this->stream->saveGroup('chunk', array(
            's:tag' => 4,
            'i:size' => 32,
        ));
        $this->stream->saveGroup('main_avi_header', array(
            'i:microsecPerFrame' => 32,
            'i:maxBytesPerSec' => 32,
            'i:paddingGranularity' => 32,
            'i:flags' => 32,
            'i:totalFrames' => 32,
            'i:initialFrames' => 32,
            'i:streamsCount' => 32,
            'i:_' => 32,
            'i:width' => 32,
            'i:height' => 32,
            's:_' => 16,
        ));
        $this->stream->saveGroup('stream_header', array(
            's:type' => 4,
            's:codec' => 4,
            'i:flags' => 32,
            'i:priority' => 16,
            'i:language' => 16,
            'i:initialFrames' => 32,
            'i:scale' => 32,
            'i:rate' => 32,
            'i:start' => 32,
            'i:length' => 32,
            'i:_' => 32,
            'i:quality' => 32,
            'i:sampleSize' => 32,
            'i:position' => 64,
        ));
        $this->stream->saveGroup('video_strf', array(
            'i:size2' => 32,
            'i:width' => 32,
            'i:height' => 32,
            'i:planes' => 16,
            'i:bitCount' => 16,
            's:compression' => 4,
            'i:sizeImage' => 32,
            'i:xPixelsPerMeter' => 32,
            'i:yPixelsPerMeter' => 32,
            'i:clrUsed' => 32,
            'i:clrImportant' => 32,
        ));
        $this->stream->saveGroup('video_properties', array(
            'i:format' => 32,
            'i:standard' => 32,
            'i:verticalRefreshRate' => 32,
            'i:hTotal' => 32,
            'i:vTotal' => 32,
            'i:aspect' => 32,
            'i:width' => 32,
            'i:height' => 32,
            'i:fieldPerFrame' => 32,
        ));
        $this->scan();
    }

    protected function scan() {
        $list = $this->stream->readGroup('list');
        // initial list (RIFF or LIST)
        if (!in_array($list['list'], array('RIFF', 'LIST')))
            throw new ParsingException('Avi file should start with a list!');
        // following LIST
        $next = $this->stream->readGroup('list');
        if (!$next['list'] == 'LIST' || !$next['tag'] == 'hdrl')
            throw new ParsingException('Avi does not have header list!');

        // avih
        $avih = $this->stream->readGroup('chunk');
        $avih += $this->stream->readGroup('main_avi_header');
        $this->avih = $avih;
        // var_dump($avih);

        // scan for `strl` for every stream
        for ($i = 0; $i < $avih['streamsCount']; $i++) {
            $strl = $this->stream->readGroup('list');
            // var_dump($strl);
            if ($strl['list'] != 'LIST' || $strl['tag'] != 'strl')
                throw new ParsingException('Here should be "strl" tag!');
            $this->stream->mark('stream_'.$i.'_start');

            // strh
            $strh = $this->stream->readGroup('chunk');
            $strh += $this->stream->readGroup('stream_header');
            // var_dump($strh);

            // add only supported stream types
            if (isset(self::$streamTypes[$strh['type']])) {
                $this->streams[$i] = array(
                    'type' => self::$streamTypes[$strh['type']],
                    'codec' => $strh['codec'],
                    'length' => $strh['length'] / $strh['rate'] / $strh['scale'],
                );

                if ($strh['type'] == 'vids') {
                    if (empty($this->length)) {
                        $this->streams[$i]['framerate'] = $strh['rate'] / $strh['scale'];
                        $this->length = $this->streams[$i]['length'];
                        $this->framerate = $this->streams[$i]['framerate'];
                    }

                }
            }

            // strf
            $strf = $this->stream->readGroup('chunk');
            if ($strh['type'] == 'vids') {
                $strf += $this->stream->readGroup('video_strf');
                $this->streams[$i]['width'] = $strf['width'];
                $this->streams[$i]['height'] = $strf['height'];
                // $this->stream->skip($strf['size']);
                // var_dump($strf);
            }

            // strn
            if ($this->stream->compare(4, 'strn')) {
                $strn = $this->stream->readGroup('chunk');
            }

            // indx
            if ($this->stream->compare(4, 'indx')) {
                $strn = $this->stream->readGroup('chunk');
            }

            if ($this->stream->compare(4, 'vprp')) {
                $vprp = $this->stream->readGroup('chunk');
                $vprp += $this->stream->readGroup('video_properties');
                // var_dump($vprp);
            }

            // go to next strl
            $this->stream->go('stream_'.$i.'_start');
            $this->stream->skip($strl['size'] - 4);
        }

        $info = $this->stream->readGroup('list');
        // var_dump($info);
    }

    public function getLength() {
        return $this->length;
    }

    public function getWidth() {
        return $this->avih['width'];
    }

    public function getHeight() {
        return $this->avih['height'];
    }

    public function getFramerate() {
        return $this->framerate;
    }

    public function countStreams() {
        return count($this->streams);
    }

    public function getStreams() {
        return $this->streams;
    }

    public function countVideoStreams() {
        $count = 0;
        foreach ($this->streams as $stream)
            if ($stream['type'] == ContainerAdapter::VIDEO) $count++;
        return $count;
    }

    public function countAudioStreams() {
        $count = 0;
        foreach ($this->streams as $stream)
            if ($stream['type'] == ContainerAdapter::AUDIO) $count++;
        return $count;
    }
}
