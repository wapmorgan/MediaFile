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
## MediaFile

