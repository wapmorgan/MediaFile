<?php
namespace wapmorgan\MediaFile;

use Exception;
use wapmorgan\BinaryStream\BinaryStream;

/**
 * WMA and WMV uses ASF as a container
 * Based on specifications from http://go.microsoft.com/fwlink/p/?linkid=31334
 */
class AsfAdapter implements ContainerAdapter {
    protected $filename;
    protected $stream;

    const HEADER = 'header_uuid';
    const FILE_PROPERTIES = 'file_properties_uuid';
    const STREAM_PROPERTIES = 'stream_properties_uuid';
    const HEADER_EXTENSION = 'header_extension_uuid';
    const CODEC_LIST = 'codec_list_uuid';
    const SCRIPT_COMMAND = 'script_command_uuid';
    const MARKER = 'marker_uuid';
    const BITRATE_MUTUAL_EXCLUSION = 'bitrate_mutual_exclusion_uuid';
    const ERROR_CORRECTION = 'error_correction_uuid';
    const CONTENT_DESCRIPTION = 'content_description_uuid';
    const EXTENDED_CONTENT_DESCRIPTION = 'extended_content_description_uuid';
    const CONTENT_BRANDING = 'content_branding_uuid';
    const STREAM_BITRATE_PROPERTIES = 'stream_bitrate_properties_uuid';
    const CONTENT_ENCRYPTION = 'content_encryption_uuid';
    const EXTENDED_CONTENT_ENCRYPTION = 'extended_content_encryption_uuid';
    const DIGITAL_SIGNATURE = 'digital_signature_uuid';
    const PADDING = 'padding_uuid';
    const DATA = 'data_uuid';

    static protected $uuids = array(
        self::HEADER => '3026b2758e66cf11a6d900aa0062ce6c',
        self::FILE_PROPERTIES => 'a1dcab8c47a9cf118ee400c00c205365',
        self::STREAM_PROPERTIES => '9107dcb7b7a9cf118ee600c00c205365',
        self::HEADER_EXTENSION => 'b503bf5f2ea9cf118ee300c00c205365',
        self::CODEC_LIST => '4052d1861d31d011a3a400a0c90348f6',
        self::SCRIPT_COMMAND => '301afb1e620bd011a39b00a0c90348f6',
        self::MARKER => '01cd87f451a9cf118ee600c00c205365',
        self::BITRATE_MUTUAL_EXCLUSION => 'dc29e2d6da35d111903400a0c90349be',
        self::ERROR_CORRECTION => '3526b2758e66cf11a6d900aa0062ce6c',
        self::CONTENT_DESCRIPTION => '3326b2758e66cf11a6d900aa0062ce6c',
        self::EXTENDED_CONTENT_DESCRIPTION => '40a4d0d207e3d21197f000a0c95ea850',
        self::CONTENT_BRANDING => 'fab3112223bdd211b4b700a0c955fc6e',
        self::STREAM_BITRATE_PROPERTIES => 'ce75f87b8d46d1118d82006097c9a2b2',
        self::CONTENT_ENCRYPTION => 'fbb3112223bdd211b4b700a0c955fc6e',
        self::EXTENDED_CONTENT_ENCRYPTION => '14e68a292226174cb935dae07ee9289c',
        self::DIGITAL_SIGNATURE => 'fcb3112223bdd211b4b700a0c955fc6e',
        self::PADDING => '74d40618dfca0945a4ba9aabcb96aae8',
        self::DATA => '3626b2758e66cf11a6d900aa0062ce6c',
    );

    public function __construct($filename) {
        if (!file_exists($filename) || !is_readable($filename)) throw new Exception('File "'.$filename.'" is not available for reading!');
        $this->filename = $filename;
        $this->stream = new BinaryStream($filename);
        $this->stream->saveGroup('object', array(
            's:guid' => 16,
            'i:size' => 64,
        ));
        $this->stream->saveGroup('header_object', array(
            's:guid' => 16,
            'i:size' => 64,
            'i:count' => 32,
            's:_' => 2,
        ));
        $this->stream->saveGroup('file_properties_object', array(
            's:guid' => 16,
            'i:size' => 64,
            's:file_id' => 16,
            'i:file_size' => 64,
            'i:creation_date' => 64,
            'i:data_packets_count' => 64,
            'i:length' => 64,
            'i:send_length' => 64,
            'i:preroll' => 64,
            'i:flags' => 32,
            'i:min_packet_size' => 32,
            'i:max_packet_size' => 32,
            'i:max_bit_rate' => 32,
        ));
        $this->scan();
    }

    protected function scan() {
        if (!$this->stream->compare(16, array(0x30, 0x26, 0xB2, 0x75, 0x8E, 0x66, 0xCF, 0x11, 0xA6, 0xD9, 0x00, 0xAA, 0x00, 0x62, 0xCE, 0x6C)))
            throw new Exception('This file is not an ASF file!');

        $header = $this->stream->readGroup('header_object');
        while (true) {
            $this->stream->mark('current_object');
            $object_uuid_string = $this->stream->readString(16);
            $this->stream->go('current_object');
            $object_uuid = null;
            for ($i = 0; $i < 16; $i++)
                $object_uuid .= str_pad(dechex(ord($object_uuid_string[$i])), 2, '0', STR_PAD_LEFT);

            if (!in_array($object_uuid, self::$uuids)) {
                $object = $this->stream->readGroup('object');
                $this->stream->skip($object['size'] - 24);
                var_dump($object_uuid);
                continue;
            }

            $object_type = array_search($object_uuid, self::$uuids);
            switch ($object_type) {
                case self::FILE_PROPERTIES:
                    $file_properties = $this->stream->readGroup('file_properties_object');
                    var_dump($file_properties);
                    break;
            }

            break;
        }
        var_dump($header);
    }

    public function countStreams() {}
    public function countVideoStreams() {}
    public function countAudioStreams() {}
    public function getStreams() {}
}
