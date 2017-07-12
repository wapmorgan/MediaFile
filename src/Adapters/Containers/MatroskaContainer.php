<?php
namespace wapmorgan\MediaFile\Adapters\Containers;

use wapmorgan\BinaryStream\BinaryStream;
use wapmorgan\MediaFile\ContainerAdapter;
use wapmorgan\MediaFile\Exceptions\FileAccessException;
use wapmorgan\MediaFile\Exceptions\ParsingException;

/**
 * Based on spcecifications from https://www.matroska.org/technical/specs/index.html
 */
class MatroskaContainer {
    protected $filename;
    protected $type;
    protected $stream;
    protected $streams;
    protected $duration;
    protected $header = array();

    // 1 level elements
    const SEEK_HEAD_ID = 0x114D9B74;
    const SEGMENT_INFO_ID = 0x1549A966;
    const TRACKS_ID = 0x1654AE6B;

    const INFO_SEGMENT_UID = 0x73A4;
    const INFO_SEGMENT_FILENAME = 0x7384;
    const INFO_PREV_UID = 0x3CB923;
    const INFO_PREV_FILENAME = 0x3C83AB;
    const INFO_NEXT_UID = 0x3EB923;
    const INFO_NEXT_FILENAME = 0x3E83BB;
    const INFO_SEGMENT_FAMILY = 0x4444;
    const INFO_CHAPTER_TRANSLATE = 0x6924;
    const INFO_CHAPTER_TRANSLATEEDITIONUID = 0x69FC;
    const INFO_CHAPTER_TRANSLATECODEC = 0x69BF;
    const INFO_CHAPTER_TRANSLATEID = 0x69A5;
    const INFO_TIMECODE_SCALE = 0x2AD7B1;
    const INFO_DURATION = 0x4489;
    const INFO_DATE_UTC = 0x4461;
    const INFO_TITLE = 0x7BA9;
    const INFO_MUXING_APP = 0x4D80;
    const INFO_WRITING_APP = 0x5741;

    // 2 level in Tracks
    const TRACK_ENTRY_ID = 0xAE;

    // 3 level in TrackEntry
    const TRACK_NUMBER_ID = 0xD7;
    const TRACK_UID_ID = 0x73C5;
    const TRACK_TYPE_ID = 0x83;
    const FLAG_ENABLED_ID = 0xB9;
    const FLAG_DEFAULT_ID = 0x88;
    const FLAG_FORCED_ID = 0x55AA;
    const FLAG_LACING_ID = 0x9C;
    const MIN_CACHE_ID = 0x6DE7;
    const MAX_CACHE_ID = 0x6DF8;
    const DEFAULT_DURATION_ID = 0x23E383;
    const DEFAULT_DECODED_FIELD_DURATION_ID = 0x234E7A;
    const TRACK_TIMECODE_SCALE_ID = 0x23314F;
    const TRACK_OFFSET_ID = 0x537F;
    const MAX_BLOCK_ADDITION_ID_ID = 0x55EE;
    const NAME_ID = 0x536E;
    const LANGUAGE_ID = 0x22B59C;
    const CODEC_ID_ID = 0x86;
    const CODEC_PRIVATE_ID = 0x63A2;
    const CODEC_NAME_ID = 0x258688;
    const ATTACHMENT_LINK_ID = 0x7446;
    const CODEC_SETTINGS_ID = 0x3A9697;
    const CODEC_INFO_URL_ID = 0x3B4040;
    const CODEC_DOWNLOAD_URL_ID = 0x26B240;
    const CODEC_DECODE_ALL_ID = 0xAA;
    const TRACK_OVERLAY_ID = 0x6FAB;
    const CODEC_DELAY_ID = 0x56AA;
    const SEEK_PRE_ROLL_ID = 0x56BB;
    const TRACK_TRANSLATE_ID = 0x6624;
    const VIDEO_ID = 0xE0;
    const AUDIO_ID = 0xE1;
    const TRACK_OPERATION_ID = 0xE2;
    const TRICK_TRACK_SEGMENT_UI_D_ID = 0xC1;
    const TRICK_TRACK_FLAG_ID = 0xC6;
    const TRICK_MASTER_TRACK_UI_D_ID = 0xC7;
    const TRICK_MASTER_TRACK_SEGMENT_UI_D_ID = 0xC4;
    const CONTENT_ENCODINGS_ID = 0x6D80;

    // 4 level in TrackTranslate
    const TRACK_TRANSLATE_EDITION_UI_D = 0x66FC;
    const TRACK_TRANSLATE_CODEC = 0x66BF;
    const TRACK_TRANSLATE_TRACK_ID = 0x66A5;

    // 4 level in Video
    const FLAG_INTERLACED = 0x9A;
    const FIELD_ORDER = 0x9D;
    const STEREO_MODE = 0x53B8;
    const ALPHA_MODE = 0x53C0;
    const OLD_STEREO_MODE = 0x53B9;
    const PIXEL_WIDTH = 0xB0;
    const PIXEL_HEIGHT = 0xBA;
    const PIXEL_CROP_BOTTOM = 0x54AA;
    const PIXEL_CROP_TOP = 0x54BB;
    const PIXEL_CROP_LEFT = 0x54CC;
    const PIXEL_CROP_RIGHT = 0x54DD;
    const DISPLAY_WIDTH = 0x54B0;
    const DISPLAY_HEIGHT = 0x54BA;
    const DISPLAY_UNIT = 0x54B2;
    const ASPECT_RATIO_TYPE = 0x54B3;
    const COLOUR_SPACE = 0x2EB524;
    const GAMMA_VALUE = 0x2FB523;
    const FRAME_RATE = 0x2383E3;
    const COLOUR = 0x55B0;

    // 5 level in Colour
    const MATRIX_COEFFICIENTS = 0x55B1;
    const BITS_PER_CHANNEL = 0x55B2;
    const CHROMA_SUBSAMPLING_HORZ = 0x55B3;
    const CHROMA_SUBSAMPLING_VERT = 0x55B4;
    const CB_SUBSAMPLING_HORZ = 0x55B5;
    const CB_SUBSAMPLING_VERT = 0x55B6;
    const CHROMA_SITING_HORZ = 0x55B7;
    const CHROMA_SITING_VERT = 0x55B8;
    const RANGE = 0x55B9;
    const TRANSFER_CHARACTERISTICS = 0x55BA;
    const PRIMARIES = 0x55BB;
    const MAX_C_L_L = 0x55BC;
    const MAX_F_A_L_L = 0x55BD;
    const MASTERING_METADATA = 0x55D0;

    // 6 level in MasteringMetadata
    const PRIMARY_R_CHROMATICITY_X = 0x55D1;
    const PRIMARY_R_CHROMATICITY_Y = 0x55D2;
    const PRIMARY_G_CHROMATICITY_X = 0x55D3;
    const PRIMARY_G_CHROMATICITY_Y = 0x55D4;
    const PRIMARY_B_CHROMATICITY_X = 0x55D5;
    const PRIMARY_B_CHROMATICITY_Y = 0x55D6;
    const WHITE_POINT_CHROMATICITY_X = 0x55D7;
    const WHITE_POINT_CHROMATICITY_Y = 0x55D8;
    const LUMINANCE_MAX = 0x55D9;
    const LUMINANCE_MIN = 0x55DA;

    // 4 level in Audio
    const SAMPLING_FREQUENCY = 0xB5;
    const OUTPUT_SAMPLING_FREQUENCY = 0x78B5;
    const CHANNELS = 0x9F;
    const CHANNEL_POSITIONS = 0x7D7B;
    const BIT_DEPTH = 0x6264;

    // 4 level in TrackOperation
    const TRACK_COMBINE_PLANES = 0xE3;

    // 5 level in TrackPlane
    const TRACK_PLANE = 0xE4;
    const TRACK_PLANE_UI_D = 0xE5;
    const TRACK_PLANE_TYPE = 0xE6;
    const TRACK_JOIN_BLOCKS = 0xE9;
    const TRACK_JOIN_UI_D = 0xED;
    const TRICK_TRACK_UI_D = 0xC0;

    // 4 level in ContentEncodings
    const CONTENT_ENCODING = 0x6240;

    // 5 level
    const CONTENT_ENCODING_ORDER = 0x5031;
    const CONTENT_ENCODING_SCOPE = 0x5032;
    const CONTENT_ENCODING_TYPE = 0x5033;
    const CONTENT_COMPRESSION = 0x5034;
    const CONTENT_ENCRYPTION = 0x5035;

    // 6 level
    const CONTENT_COMP_ALGO = 0x4254;
    const CONTENT_COMP_SETTINGS = 0x4255;
    const CONTENT_ENC_ALGO = 0x47E1;
    const CONTENT_ENC_KEY_ID = 0x47E2;
    const CONTENT_SIGNATURE = 0x47E3;
    const CONTENT_SIG_KEY_ID = 0x47E4;
    const CONTENT_SIG_ALGO = 0x47E5;
    const CONTENT_SIG_HASH_ALGO = 0x47E6;

    public function __construct($filename) {
        if (!file_exists($filename) || !is_readable($filename)) throw new FileAccessException('File "'.$filename.'" is not available for reading!');
        $this->filename = $filename;
        $this->stream = new BinaryStream($filename);
        $this->stream->setEndian(BinaryStream::BIG);
        $this->scan();
    }

    protected function scan() {
        if (!$this->stream->compare(4, array(0x1A, 0x45, 0xDF, 0xA3)))
            throw new ParsingException('This file is not a Matroska container!');
        $this->stream->skip(4);
        // master size
        $size = $this->readEbmlElementSize($bytesForSize);
        $this->stream->markOffset(4 + $bytesForSize + $size, 'data');

        // read element size
        $this->ensureEbmlElementId(array(0x42, 0x86));
        $size = $this->readEbmlElementSize();
        $this->header['version'] = $this->stream->readInteger($size * 8);

        $this->ensureEbmlElementId(array(0x42, 0xF7));
        $size = $this->readEbmlElementSize();
        $this->header['minVersion'] = $this->stream->readInteger($size * 8);

        $this->ensureEbmlElementId(array(0x42, 0xF2));
        $size = $this->readEbmlElementSize();
        $this->header['maxIdLength'] = $this->stream->readInteger($size * 8);

        $this->ensureEbmlElementId(array(0x42, 0xF3));
        $size = $this->readEbmlElementSize();
        $this->header['maxSizeLength'] = $this->stream->readInteger($size * 8);

        $this->ensureEbmlElementId(array(0x42, 0x82));
        $size = $this->readEbmlElementSize();
        $this->header['docType'] = $this->stream->readString($size);

        $this->ensureEbmlElementId(array(0x42, 0x87));
        $size = $this->readEbmlElementSize();
        $this->header['docTypeVersion'] = $this->stream->readInteger($size * 8);

        $this->ensureEbmlElementId(array(0x42, 0x85));
        $size = $this->readEbmlElementSize();
        $this->header['docTypeMinVersion'] = $this->stream->readInteger($size * 8);

        // go to data
        $this->stream->go('data');
        // check for segment
        $this->ensureEbmlElementId(array(0x18, 0x53, 0x80, 0x67));
        $size = $this->readEbmlElementSize($b);
        // var_dump($b);

        $segments = array();
        $i = 1;
        while (!$this->stream->isEnd()) {
            if ($i++ % 1000 == 0) break; //var_dump(dechex($id), memory_get_usage(), ftell($this->stream->fp));
            $pos = ftell($this->stream->fp);
            $id = $this->readEbmlElementId();
            $size = $this->readEbmlElementSize();
            // var_dump(dechex($id), $size, $before_size_pos, ftell($this->stream->fp));
            // $segments[dechex($id)];
            // switch ($id) {
            //     case self::SEGMENT_INFO_ID:
            //         var_dump($size);
            //         break;

            //     default:
            //         var_dump(dechex($id), $size);
            //         $this->stream->skip($size);
            //         break;
            // }
            $segment_i = 0;
            // var_dump(dechex($id));
            switch ($id) {
                case self::SEGMENT_INFO_ID:
                    // find for duration
                    while (!$this->stream->isEnd()) {
                        $this->stream->mark('next_id');
                        $inner_id = $this->readEbmlElementId($bytesForId);

                        switch ($inner_id) {
                            case self::INFO_SEGMENT_UID:
                            case self::INFO_SEGMENT_FILENAME:
                            case self::INFO_PREV_UID:
                            case self::INFO_PREV_FILENAME:
                            case self::INFO_NEXT_UID:
                            case self::INFO_NEXT_FILENAME:
                            case self::INFO_SEGMENT_FAMILY:
                            case self::INFO_CHAPTER_TRANSLATE:
                            case self::INFO_CHAPTER_TRANSLATEEDITIONUID:
                            case self::INFO_CHAPTER_TRANSLATECODEC:
                            case self::INFO_CHAPTER_TRANSLATEID:
                            case self::INFO_DATE_UTC:
                            case self::INFO_TITLE:
                            case self::INFO_MUXING_APP:
                            case self::INFO_WRITING_APP:
                            case self::INFO_TITLE: //  here is title!
                                $this->stream->skip($this->readEbmlElementSize($bytesForSize));
                                break;

                            case self::INFO_TIMECODE_SCALE:
                                // $data = $this->stream->readBits(array('scale' => $inner_size * 8));
                                // $timecode_scale = $data['scale'];
                                $timecode_scale = $this->stream->readInteger($this->readEbmlElementSize($bytesForSize) * 8);
                                $timecode_scale = 1000000000 / $timecode_scale;
                                break;

                            case self::INFO_DURATION:
                                $duration = $this->stream->readFloat($this->readEbmlElementSize($bytesForSize) * 8);
                                break;

                            default:
                                // var_dump(dechex($inner_id));
                                $this->stream->go('next_id');
                                break(2);
                        }
                    }
                    if (isset($duration)) {
                        $this->duration = isset($timecode_scale) ? $duration / $timecode_scale : $duration / 1000;
                    }
                    $segment_i++;
                    break;

                case self::TRACKS_ID:
                    $track_id = 0;
                    // var_dump('Tracks block. size: '.$size.', bitOffset: '.$this->stream->bitOffset.' offset: '.$this->stream->offset, $bytesForSize, ftell($this->stream->fp));
                        while (!$this->stream->isEnd()) {
                            $this->stream->mark('next_id');
                            $inner_id = $this->readEbmlElementId($bytesForId);
                            $inner_size = $this->readEbmlElementSize($bytesForSize);
                            // var_dump(dechex($id), $size, dechex($inner_id), $inner_size, ftell($this->stream->fp));
                            $track_i = 0;
                            if ($inner_id != self::TRACK_ENTRY_ID) {
                                $this->stream->go('next_id');
                                break(1);
                            }

                            while (!$this->stream->isEnd()) {
                                $this->stream->mark('next_id');
                                $inner_id = $this->readEbmlElementId($bytesForId);
                                $inner_size = $this->readEbmlElementSize($bytesForSize);
                                // var_dump(dechex($inner_id), $inner_size);
                                switch ($inner_id) {
                                    case self::TRACK_UID_ID:
                                    case self::FLAG_ENABLED_ID:
                                    case self::FLAG_DEFAULT_ID:
                                    case self::FLAG_FORCED_ID:
                                    case self::FLAG_LACING_ID:
                                    case self::MIN_CACHE_ID:
                                    case self::MAX_CACHE_ID:
                                    case self::DEFAULT_DURATION_ID:
                                    case self::DEFAULT_DECODED_FIELD_DURATION_ID:
                                    case self::TRACK_TIMECODE_SCALE_ID:
                                    case self::TRACK_OFFSET_ID:
                                    case self::MAX_BLOCK_ADDITION_ID_ID:
                                    case self::NAME_ID:
                                    case self::LANGUAGE_ID:
                                    case self::CODEC_ID_ID:
                                    case self::CODEC_PRIVATE_ID:
                                    case self::CODEC_NAME_ID:
                                    case self::ATTACHMENT_LINK_ID:
                                    case self::CODEC_SETTINGS_ID:
                                    case self::CODEC_INFO_URL_ID:
                                    case self::CODEC_DOWNLOAD_URL_ID:
                                    case self::CODEC_DECODE_ALL_ID:
                                    case self::TRACK_OVERLAY_ID:
                                    case self::CODEC_DELAY_ID:
                                    case self::SEEK_PRE_ROLL_ID:
                                    case self::TRACK_TRANSLATE_ID:
                                    case self::TRACK_OPERATION_ID:
                                    case self::TRICK_TRACK_SEGMENT_UI_D_ID:
                                    case self::TRICK_TRACK_FLAG_ID:
                                    case self::TRICK_MASTER_TRACK_UI_D_ID:
                                    case self::TRICK_MASTER_TRACK_SEGMENT_UI_D_ID:
                                    case self::CONTENT_ENCODINGS_ID:
                                        $this->stream->skip($inner_size);
                                        break;

                                    case self::TRACK_TYPE_ID:
                                        $type = $this->stream->readInteger($inner_size * 8);
                                        switch ($type) {
                                            case 1:
                                                $this->streams[$track_i]['type'] = 'video';
                                                $this->streams[$track_i]['framerate'] = 0;
                                                break;
                                            case 2:
                                                $this->streams[$track_i]['type'] = 'audio';
                                                break;
                                        }
                                        break;

                                    case self::VIDEO_ID:
                                        while (!$this->stream->isEnd()) {
                                            $this->stream->mark('next_id');
                                            $inner_id = $this->readEbmlElementId($bytesForId);
                                            $inner_size = $this->readEbmlElementSize($bytesForSize);
                                            // var_dump(dechex($inner_id));
                                            switch ($inner_id) {
                                                case self::FLAG_INTERLACED:
                                                case self::FIELD_ORDER:
                                                case self::STEREO_MODE:
                                                case self::ALPHA_MODE:
                                                case self::OLD_STEREO_MODE:
                                                case self::PIXEL_CROP_BOTTOM:
                                                case self::PIXEL_CROP_TOP:
                                                case self::PIXEL_CROP_LEFT:
                                                case self::PIXEL_CROP_RIGHT:
                                                case self::DISPLAY_WIDTH:
                                                case self::DISPLAY_HEIGHT:
                                                case self::DISPLAY_UNIT:
                                                case self::ASPECT_RATIO_TYPE:
                                                case self::COLOUR_SPACE:
                                                case self::GAMMA_VALUE:
                                                case self::COLOUR:
                                                    $this->stream->skip($inner_size);
                                                    break;

                                                case self::PIXEL_WIDTH:
                                                    $this->streams[$track_i]['width'] = $this->stream->readInteger($inner_size * 8);
                                                    break;

                                                case self::PIXEL_HEIGHT:
                                                    $this->streams[$track_i]['height'] = $this->stream->readInteger($inner_size * 8);
                                                    break;

                                                case self::FRAME_RATE:
                                                    $this->streams[$track_i]['framerate'] = $this->stream->readFloat($inner_size * 8);
                                                    break;

                                                default:
                                                    $this->stream->go('next_id');
                                                    break(2);
                                            }
                                        }
                                        break;

                                    case self::AUDIO_ID:
                                        while (!$this->stream->isEnd()) {
                                            $this->stream->mark('next_id');
                                            $inner_id = $this->readEbmlElementId($bytesForId);
                                            $inner_size = $this->readEbmlElementSize($bytesForSize);
                                            // var_dump(dechex($inner_id));
                                            switch ($inner_id) {
                                                case self::SAMPLING_FREQUENCY:
                                                case self::CHANNEL_POSITIONS:
                                                    $this->stream->skip($inner_size);
                                                    break;

                                                case self::CHANNELS:
                                                    $this->streams[$track_i]['channels'] = $this->stream->readInteger($inner_size * 8);
                                                    break;

                                                case self::PIXEL_WIDTH:
                                                    $this->streams[$track_i]['sample_rate'] = $this->stream->readInteger($inner_size * 8);
                                                    break;

                                                case self::BIT_DEPTH:
                                                    $this->streams[$track_i]['bit_rate'] = $this->stream->readInteger($inner_size * 8);
                                                    break;

                                                default:
                                                    $this->stream->go('next_id');
                                                    break(2);
                                            }
                                        }
                                        break;

                                    case self::TRACK_NUMBER_ID:
                                        $track_id = $this->stream->readInteger($inner_size * 8);
                                        break;

                                    default:
                                    // var_dump(dechex($inner_id));
                                        $this->stream->go('next_id');
                                        break(2);
                                }
                            }
                            $track_i++;
                        }
                        return true;
                    break;

                default:
                    $this->stream->skip($size);
                    break;
            }
            // var_dump(dechex($id), $size, ftell($this->stream->fp));
            if ($i == 5) break;
        }
        // var_dump($segments);
    }

    protected function ensureEbmlElementId($bytes) {
        if (!$this->stream->compare(count($bytes), $bytes)) {
            throw new ParsingException('File should contain element "'.implode('-', array_map('dechex', $bytes)).'" at this offset!');
        }
        $this->stream->skip(count($bytes));
    }

    protected function readEbmlElementId(&$bytesForId = 0) {
        $id = ord($this->stream->readChar());
        if ($id & 0x80) $bytesForId = 1;
        else if ($id & 0x40) $bytesForId = 2;
        else if ($id & 0x20) $bytesForId = 3;
        else if ($id & 0x10) $bytesForId = 4;
        $i = $bytesForId - 1;
        while ($i-- > 0)
            $id = ($id << 8) + ord($this->stream->readChar());
        return $id;
    }

    protected function readEbmlElementSize(&$bytesForSize = 0, $debuf = false) {
        // $this->stream->readString(1);
        // $this->stream->skip(-1);
        $bytesForSize = 0;
        $bit = false;
        while ($bit === false) {
            $bit = $this->stream->readBit();
            $bytesForSize++;
        }
        // read data until the end of byte
        $end = 8 * $bytesForSize;
        // var_dump($bytesForSize, $end);
        $size = 0;
        $i = $bytesForSize;
        while ($i++ < $end) {
            $bit = $this->stream->readBit();
            $size = ($size << 1) + $bit;
        }
        // read
        return $size;
    }
}
