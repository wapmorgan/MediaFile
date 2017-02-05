# MediaFile

Allows you easily get meta information about any media file with unified interface.

It can retrieve following information:

- For any audio:
  - length
  - bitRate
  - sampleRate
  - channelsMode

Supported formats:

- Audio
  - wav
  - flac
  - aac

How to use:

```php
try {
  $media = wapmorgan\MediaFile\MediaFile::open('123.mp3');
  // for audio
  if ($media->isAudio()) {
    // calls to AudioAdapter interface
    echo 'Duration: '.$media->getAudio()->getLength().PHP_EOL;
    echo 'Bit rate: '.$media->getAudio()->getBitRate().PHP_EOL;
    echo 'Sample rate: '.$media->getAudio()->getSampleRate().PHP_EOL;
    echo 'Channels: '.$media->getAudio()->getChannelsMode().PHP_EOL;
  }
} catch (Exception $e) {
  // not a media
}
```

# API
### MediaFile

`wapmorgan\wapmorgan\MediaFile`

| Method                                   | Description                                                               | Notes                                                                                                   |
|------------------------------------------|---------------------------------------------------------------------------|---------------------------------------------------------------------------------------------------------|
| `static open($filename)`                 | Detects file type and format and calls constructor with these parameters. | Throws an `\Exception` if file is not a media or is not accessible.                                     |
| `__construct($filename, $type, $format)` | Opens file and reads metadata.                                            | Available `$type` values: `MediaFile::AUDIO`, `MediaFile::VIDEO`. Available `$format` values see below. |
| `isAudio()`                              | Returns true if media is just audio.                                      |                                                                                                         |
| `isVideo()`                              | Returns true if media is a video with audio.                              |                                                                                                         |
| `getType()`                              | Returns media file type.                                                  |                                                                                                         |
| `getFormat()`                            | Returns media file format.                                                |                                                                                                         |
| `getAudio()`                             | Returns an AudioAdapter interface for audio.                              |                                                                                                         |

## AudioAdapter

`wapmorgan\MediaFile\AudioAdapter`

| Method                | Description                                                       | Notes                                                                                                                                                                          |
|-----------------------|-------------------------------------------------------------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `getLength()`         | Returns audio length in seconds and microseconds in _float_.      |                                                                                                                                                                                |
| `getBitRate()`        | Returns audio bit rate as _int_.                                  |                                                                                                                                                                                |
| `getSampleRate()`     | Returns audio sampling rate as _int_.                             |                                                                                                                                                                                |
| `getChannelsMode()`   | Returns channes mode as one of `AudioAdapter` constant.           | Available modes: `AudioAdapter::MONO`, `AudioAdapter::STEREO`, `AudioAdapter::QUADRO`, `AudioAdapter::FIVE`, `AudioAdapter::SIX`, `AudioAdapter::SEVEN`, `AudioAdapter::EIGHT` |
| `isVariableBitRate()` | Returns whether format support VBR and file has VBR as _boolean_. |                                                                                                                                                                                |
| `isLossless()`        | Returns whether format has compression lossless as _boolean_.     |                                                                                                                                                                                |
