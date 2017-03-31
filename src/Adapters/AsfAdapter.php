<?php
namespace wapmorgan\MediaFile\Adapters;

use wapmorgan\BinaryStream\BinaryStream;
use wapmorgan\MediaFile\ContainerAdapter;
use wapmorgan\MediaFile\Exceptions\FileAccessException;
use wapmorgan\MediaFile\Exceptions\ParsingException;

/**
 * WMA and WMV uses ASF as a container
 * Based on specifications from http://go.microsoft.com/fwlink/p/?linkid=31334
 */
class AsfAdapter implements ContainerAdapter {
    protected $filename;
    protected $stream;
    protected $properties;
    protected $streams_bitrates;
    protected $streams;

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

    const EXTENDED_STREAM_PROPERTIES = 'extended_stream_properties_uuid';
    const ADVANCED_MUTUAL_EXCLUSION = 'advanced_mutual_exclusion_uuid';
    const GROUP_MUTUAL_EXCLUSION = 'group_mutual_exclusion_uuid';
    const STREAM_PRIORITIZATION = 'stream_prioritization_uuid';
    const BANDWIDTH_SHARING = 'bandwidth_sharing_uuid';
    const LANGUAGE_LIST = 'language_list_uuid';
    const METADATA = 'metadata_uuid';
    const METADATA_LIBRARY = 'metadata_library_uuid';
    const INDEX_PARAMETERS = 'index_parameters_uuid';
    const MEDIA_OBJECT_INDEX_PARAMETERS = 'media_object_index_parameters_uuid';
    const TIMECODE_INDEX_PARAMETERS = 'timecode_index_parameters_uuid';
    const COMPATIBILITY = 'compatibility_uuid';
    const ADVANCED_CONTENT_ENCRYPTION = 'advanced_content_encryption_uuid';

    const AUDIO_MEDIA = 'audio_media_uuid';
    const VIDEO_MEDIA = 'video_media_uuid';
    const COMMAND_MEDIA = 'command_media_uuid';
    const JFIF_MEDIA = 'jfif_media_uuid';
    const DEGRADABLE_JPEG_MEDIA = 'degradable_jpeg_media_uuid';
    const FILE_TRANSFER_MEDIA = 'file_transfer_media_uuid';
    const BINARY_MEDIA = 'binary_media_uuid';

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

    static protected $extension_uuids = array(
        self::EXTENDED_STREAM_PROPERTIES => 'cba5e61472c632438399a96952065b5a',
        self::ADVANCED_MUTUAL_EXCLUSION => 'cf4986a0754770468a166e35357566cd',
        self::GROUP_MUTUAL_EXCLUSION => '405a46d1795a3843b71be36b8fd6c249',
        self::STREAM_PRIORITIZATION => '5bd1fed4d3884f4581f0ed5c45999e24',
        self::BANDWIDTH_SHARING => 'e60996a67b51d211b6af00c04fd908e9',
        self::LANGUAGE_LIST => 'a946437ce0effc4bb229393ede415c85',
        self::METADATA => 'eacbf8c5af5b77488467aa8c44fa4cca',
        self::METADATA_LIBRARY => '941c23449894d149a1411d134e457054',
        self::INDEX_PARAMETERS => 'df29e2d6da35d111903400a0c90349be',
        self::MEDIA_OBJECT_INDEX_PARAMETERS => 'ad3b206b113fe448aca8d7613de2cfa7',
        self::TIMECODE_INDEX_PARAMETERS => '6d495ef597975d4b8c8b604dfe9bfb24',
        self::COMPATIBILITY => '3026b2758e66cf11a6d900aa0062ce6c',
        self::ADVANCED_CONTENT_ENCRYPTION => '338505438169e6499b74ad12cb86d58c',
    );

    static protected $stream_type_uuids = array(
        self::AUDIO_MEDIA => '409e69f84d5bcf11a8fd00805f5c442b',
        self::VIDEO_MEDIA => 'c0ef19bc4d5bcf11a8fd00805f5c442b',
        self::COMMAND_MEDIA => 'c0cfda59e659d011a3ac00a0c90348f6',
        self::JFIF_MEDIA => '00e11bb64e5bcf11a8fd00805f5c442b',
        self::DEGRADABLE_JPEG_MEDIA => 'e07d903515e4cf11a91700805f5c442b',
        self::FILE_TRANSFER_MEDIA => '2c22bd911cf27a498b6d5aa86bfc0185',
        self::BINARY_MEDIA => 'e265fb3aef47f240ac2c70a90d71d343',
    );

    public function __construct($filename) {
        if (!file_exists($filename) || !is_readable($filename)) throw new FileAccessException('File "'.$filename.'" is not available for reading!');
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
            'broadcast' => 1,
            'seekable' => 1,
            '_' => 30,
            'i:min_packet_size' => 32,
            'i:max_packet_size' => 32,
            'i:max_bit_rate' => 32,
        ));
        $this->stream->saveGroup('stream_properties_object', array(
            's:guid' => 16,
            'i:size' => 64,
            's:type' => 16,
            's:error_correction_type' => 16,
            'i:time_offset' => 64,
            'i:type_specific_data_length' => 32,
            'i:error_correction_data_length' => 32,
            'stream_number' => 7,
            '_' => 8,
            'encrypted' => 1,
            'i:_' => 32,
        ));
        $this->stream->saveGroup('stream_bitrate_properties_object', array(
            's:guid' => 16,
            'i:size' => 64,
            'i:count' => 16,
        ));
        $this->stream->saveGroup('bitrate_record', array(
            'stream_number' => 7,
            '_' => 9,
            'i:bitrate' => 32,
        ));
        $this->stream->saveGroup('video_media_object', array(
            'i:width' => 32,
            'i:height' => 32,
            'c:reserved' => 1,
            'i:data_size' => 16,
        ));
        $this->stream->saveGroup('BITMAPINFOHEADER', array(
            'i:format_data_size' => 32,
            'i:image_width' => 32,
            'i:image_height' => 32,
            'i:reserved2' => 16,
            'i:bits_per_pixel' => 16,
            's:compression_id' => 4,
            'i:image_size' => 32,
            'i:horizontal_pixels_per_meter' => 32,
            'i:vertical_pixels_per_meter' => 32,
            'i:colors_used_count' => 32,
            'i:important_colors_count' => 32,
        ));
        $this->stream->saveGroup('WAVEFORMATEX', array(
            'i:codec' => 16,
            'i:channels_count' => 16,
            'i:sample_rate' => 32,
            'i:byte_rate' => 32,
            'i:alignment' => 16,
            'i:bits_per_sample' => 16,
            'i:codec_data_size' => 16,
        ));
        $this->stream->saveGroup('header_extension_object', array(
            's:guid' => 16,
            'i:size' => 64,
            's:guid2' => 16,
            'i:reserved' => 16,
            'i:extension_size' => 32,
        ));
        $this->stream->saveGroup('extended_stream_properties_object', array(
            's:guid' => 16,
            'i:size' => 64,
            'i:start_time' => 64,
            'i:end_time' => 64,
            'i:data_bitrate' => 32,
            'i:buffer_size' => 32,
            'i:initial_buffer_fullness' => 32,
            'i:alternate_data_bitrate' => 32,
            'i:alternate_buffer_size' => 32,
            'i:alternate_initial_buffer_fullness' => 32,
            'i:maximum_object_size' => 32,
            'i:flags' => 32,
            'i:stream_number' => 16,
            'i:lang_id' => 16,
            'i:avg_time_per_frame' => 64,
            'i:names_count' => 16,
            'i:payext_count' => 16,
        ));
        $this->stream->saveGroup('stream_name', array(
            'i:id' => 16,
            'i:length' => 16,
        ));
        $this->stream->saveGroup('payload_extension', array(
            's:guid' => 16,
            'i:size' => 16,
            'i:length' => 32,
        ));
        $this->scan();
    }

    protected function scan() {
        if (!$this->stream->compare(16, array(0x30, 0x26, 0xB2, 0x75, 0x8E, 0x66, 0xCF, 0x11, 0xA6, 0xD9, 0x00, 0xAA, 0x00, 0x62, 0xCE, 0x6C)))
            throw new ParsingException('This file is not an ASF file!');

        $header = $this->stream->readGroup('header_object');

        while (true) {
            $this->stream->mark('current_object');
            $object_uuid = $this->stringToUUid($this->stream->readString(16));
            $this->stream->go('current_object');


            if (!in_array($object_uuid, self::$uuids)) {
                $object = $this->stream->readGroup('object');
                $this->stream->skip($object['size'] - 24);
                break;
            }

            $object_type = array_search($object_uuid, self::$uuids);
            if (defined('DEBUG') && DEBUG) var_dump($object_type);
            switch ($object_type) {
                case self::FILE_PROPERTIES:
                    $file_properties = $this->stream->readGroup('file_properties_object');
                    $file_properties['send_length'] = $file_properties['send_length'] / 10000000;
                    $this->properties = $file_properties;
                    break;
                case self::STREAM_BITRATE_PROPERTIES:
                    $stream_bitrate_properties = $this->stream->readGroup('stream_bitrate_properties_object');
                    $bitrates = array();
                    for ($i = 0; $i < $stream_bitrate_properties['count']; $i++) {
                        $bitrate = $this->stream->readGroup('bitrate_record');
                        $bitrates[$bitrate['stream_number']] = $bitrate['bitrate'];
                    }
                    $this->streams_bitrates = $bitrates;
                    break;
                case self::STREAM_PROPERTIES:
                    $stream_properties = $this->stream->readGroup('stream_properties_object');
                    $stream_properties['type'] = $this->stringToUUid($stream_properties['type']);

                    if (!in_array($stream_properties['type'], self::$stream_type_uuids)) {
                        $this->stream->skip($stream_properties['type_specific_data_length']);
                        $this->stream->skip($stream_properties['error_correction_data_length']);
                        continue;
                    }

                    switch (array_search($stream_properties['type'], self::$stream_type_uuids)) {
                        case self::VIDEO_MEDIA:
                            $stream_properties += $this->stream->readGroup('video_media_object');
                            $stream_properties += $this->stream->readGroup('BITMAPINFOHEADER');
                            $this->stream->skip($stream_properties['format_data_size'] - 40); // 40 - size of BITMAPINFOHEADER structure
                            $this->streams[$stream_properties['stream_number']] = array(
                                'type' => ContainerAdapter::VIDEO,
                                'codec' => $stream_properties['compression_id'],
                                'length' => 0,
                                'framerate' => 0,
                                'width' => $stream_properties['width'],
                                'height' => $stream_properties['height'],
                            );
                            break;

                        case self::AUDIO_MEDIA:
                            $stream_properties += $this->stream->readGroup('WAVEFORMATEX');
                            $this->stream->skip($stream_properties['codec_data_size']);
                            $this->streams[$stream_properties['stream_number']] = array(
                                'type' => ContainerAdapter::AUDIO,
                                'codec' => null,
                                'length' => 0,
                                'channels' => $stream_properties['channels_count'],
                                'sample_rate' => $stream_properties['sample_rate'],
                                'bits_per_sample' => $stream_properties['bits_per_sample'],
                                'bit_rate' => $stream_properties['byte_rate'] * 8,
                            );
                            break;

                        default:
                            // another media type, just skip it
                            $this->stream->skip($stream_properties['type_specific_data_length']);
                            break;
                    }
                    $this->stream->skip($stream_properties['error_correction_data_length']);
                    // var_dump($stream_properties);
                    break;
                case self::HEADER_EXTENSION:
                    $header_extension = $this->stream->readGroup('header_extension_object');

                    $extended_stream_properties_count = 0;

                    while (true) {
                        $this->stream->mark('current_extension_object');
                        $extension_object_uuid = $this->stringToUUid($this->stream->readString(16));
                        $this->stream->go('current_extension_object');
                        // skip unknown extension object
                        if (!in_array($extension_object_uuid, self::$extension_uuids)) {
                            $extension_object = $this->stream->readGroup('object');
                            $this->stream->skip($extension_object['size'] - 24);
                            break;
                        }

                        $extension_object_type = array_search($extension_object_uuid, self::$extension_uuids);
                        switch ($extension_object_type) {
                            case self::EXTENDED_STREAM_PROPERTIES:
                                $extended_stream_properties = $this->stream->readGroup('extended_stream_properties_object');
                                // var_dump($extended_stream_properties);
                                $this->stream->go('current_extension_object');
                                $this->stream->skip($extended_stream_properties['size']); // 82 - size of extended stream properties structure

                                $this->streams[$extended_stream_properties['stream_number'] - 1]['length'] = ($extended_stream_properties['end_time'] - $extended_stream_properties['start_time']);

                                // jump out when all stream extended properties retrieved
                                if (++$extended_stream_properties_count >= count($this->streams)) break(2);
                                continue;

                            // skipping these objects to arrive extended stream properties
                            case self::ADVANCED_MUTUAL_EXCLUSION:
                            case self::GROUP_MUTUAL_EXCLUSION:
                            case self::STREAM_PRIORITIZATION:
                            case self::BANDWIDTH_SHARING:
                            case self::LANGUAGE_LIST:
                            case self::METADATA:
                            case self::METADATA_LIBRARY:
                            case self::INDEX_PARAMETERS:
                            case self::MEDIA_OBJECT_INDEX_PARAMETERS:
                            case self::TIMECODE_INDEX_PARAMETERS:
                            case self::COMPATIBILITY:
                            case self::ADVANCED_CONTENT_ENCRYPTION:
                                $extension_object = $this->stream->readGroup('object');
                                $this->stream->skip($extension_object['size'] - 24); // 24 - size of "object" structure
                                continue;

                            // another object, jump out of header extension handling
                            default:
                                break(2);

                        }
                    }

                    break;

                case self::HEADER:
                case self::FILE_PROPERTIES:
                case self::STREAM_PROPERTIES:
                case self::HEADER_EXTENSION:
                case self::CODEC_LIST:
                case self::SCRIPT_COMMAND:
                case self::MARKER:
                case self::BITRATE_MUTUAL_EXCLUSION:
                case self::ERROR_CORRECTION:
                case self::CONTENT_DESCRIPTION:
                case self::EXTENDED_CONTENT_DESCRIPTION:
                case self::CONTENT_BRANDING:
                case self::STREAM_BITRATE_PROPERTIES:
                case self::CONTENT_ENCRYPTION:
                case self::EXTENDED_CONTENT_ENCRYPTION:
                case self::DIGITAL_SIGNATURE:
                case self::PADDING:
                    $object = $this->stream->readGroup('object');
                    $this->stream->skip($object['size'] - 24);
                    continue;

                default:
                    if (defined('DEBUG') && DEBUG) var_dump($object_type);
                    break(2);
            }

            // if (!in_array($object_type, array(self::FILE_PROPERTIES, self::STREAM_BITRATE_PROPERTIES, self::STREAM_PROPERTIES, self::HEADER_EXTENSION))) break;
        }
    }

    protected function stringToUUid($object_uuid_string) {
        $object_uuid = null;
        for ($i = 0; $i < 16; $i++)
            $object_uuid .= str_pad(dechex(ord($object_uuid_string[$i])), 2, '0', STR_PAD_LEFT);
        return $object_uuid;
    }

    public function countStreams() {
        return count($this->streams);
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

    public function getStreams() {
        return $this->streams;
    }
}
