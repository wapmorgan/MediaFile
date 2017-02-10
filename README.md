# MediaFile

Allows you easily get meta information about any media file with unified interface.

[![Composer package](http://xn--e1adiijbgl.xn--p1acf/badge/wapmorgan/media-file)](https://packagist.org/packages/wapmorgan/media-file)
[![Latest Stable Version](https://poser.pugx.org/wapmorgan/media-file/v/stable)](https://packagist.org/packages/wapmorgan/media-file)
[![License](https://poser.pugx.org/wapmorgan/media-file/license)](https://packagist.org/packages/wapmorgan/media-file)
[![Latest Unstable Version](https://poser.pugx.org/wapmorgan/media-file/v/unstable)](https://packagist.org/packages/wapmorgan/media-file)

It can retrieve following information:

- For any audio: length, bitRate, sampleRate, channels
- For any video: length, width, height, frameRate
- For any container: number of streams, type of streams, formats of streams

Table of contents:

1. Usage
2. Supported formats
3. API
4. Technical details

# Usage

```php
try {
  $media = wapmorgan\MediaFile\MediaFile::open('123.mp3');
  // for audio
  if ($media->isAudio()) {
    // calls to AudioAdapter interface
    echo 'Duration: '.$media->getAudio()->getLength().PHP_EOL;
    echo 'Bit rate: '.$media->getAudio()->getBitRate().PHP_EOL;
    echo 'Sample rate: '.$media->getAudio()->getSampleRate().PHP_EOL;
    echo 'Channels: '.$media->getAudio()->getChannels().PHP_EOL;
  }
  // for video
  else {
    // calls to VideoAdapter interface
    echo 'Duration: '.$media->getVideo()->getLength().PHP_EOL;
    echo 'Dimensions: '.$media->getVideo()->getWidth().'x'.$media->getVideo()->getHeight().PHP_EOL;
    echo 'Framerate: '.$media->getVideo()->getFramerate().PHP_EOL;
  }
} catch (wapmorgan\MediaFile\Exception $e) {
  // not a media or file is corrupted
}
```

# Supported formats

- Audio
  - wav
  - flac
  - aac
  - ogg
  - mp3
  - amr
  - wma

- Video
  - avi (also as container)
  - wmv (also as container)
  - mp4 (also as container)

Other formats support coming soon.

# API

### MediaFile

`wapmorgan\wapmorgan\MediaFile`

| Method                                   | Description                                                                       | Notes                                                                                                   |
|------------------------------------------|-----------------------------------------------------------------------------------|---------------------------------------------------------------------------------------------------------|
| `static open($filename)`                 | Detects file type and format and calls constructor with these parameters.         | Throws an `\Exception` if file is not a media or is not accessible.                                     |
| `__construct($filename, $type, $format)` | Opens file and reads metadata.                                                    | Available `$type` values: `MediaFile::AUDIO`, `MediaFile::VIDEO`. Available `$format` values see below. |
| `isAudio()`                              | Returns true if media is just audio.                                              |                                                                                                         |
| `isVideo()`                              | Returns true if media is a video with audio.                                      |                                                                                                         |
| `isContainer()`                          | Returns true if media is also a container (can store multiple audios and videos). |                                                                                                         |
| `getType()`                              | Returns media file type.                                                          |                                                                                                         |
| `getFormat()`                            | Returns media file format.                                                        |                                                                                                         |
| `getAudio()`                             | Returns an AudioAdapter interface for audio.                                      |                                                                                                         |
| `getVideo()`                             | Returns an VideoAdapter interface for video.                                      |                                                                                                         |

Available formats:

1. For `MediaFile::AUDIO`:

  | `MediaFile::WAV`     | `MediaFile::FLAC`    | `MediaFile::AAC`     | `MediaFile::OGG` |
  |----------------------|----------------------|----------------------|------------------|
  | **`MediaFile::MP3`** | **`MediaFile::AMR`** | **`MediaFile::WMA`** |                  |


2. For `MediaFile::VIDEO`:

  | `MediaFile::AVI` | `MediaFile::WMV` | `MediaFile::MP4` |
  |------------------|------------------|------------------|

### AudioAdapter

`wapmorgan\MediaFile\AudioAdapter`

| Method                | Description                                                       |
|-----------------------|-------------------------------------------------------------------|
| `getLength()`         | Returns audio length in seconds and microseconds as _float_.      |
| `getBitRate()`        | Returns audio bit rate as _int_.                                  |
| `getSampleRate()`     | Returns audio sampling rate as _int_.                             |
| `getChannels()`       | Returns number of channels used in audio as _int_.                |
| `isVariableBitRate()` | Returns whether format support VBR and file has VBR as _boolean_. |
| `isLossless()`        | Returns whether format has compression lossless as _boolean_.     |

### VideoAdapter

`wapmorgan\MediaFile\VideoAdapter`

| Method           | Description                                                  |
|------------------|--------------------------------------------------------------|
| `getLength()`    | Returns video length in seconds and microseconds as _float_. |
| `getWidth()`     | Returns width of video as _int_.                             |
| `getHeight()`    | Returns height of video as _int_.                            |
| `getFramerate()` | Returns video frame rate of video as _int_.                  |

### ContainerAdapter

`wapmorgan\MediaFile\ContainerAdapter`

| Method                | Description                                      |
|-----------------------|--------------------------------------------------|
| `countStreams()`      | Returns number of streams in container as _int_. |
| `countVideoStreams()` | Returns number of video streams as _int_.        |
| `countAudioStreams()` | Returns number of audio streams as _int_.        |
| `getStreams()`        | Returns streams information as _array_.          |

# Technical information

| Format | Full format name                                             | Specifications                                                                                                                                                                                                     | Notes                                 |
|--------|--------------------------------------------------------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|---------------------------------------|
| aac    | MPEG 4 Part 12 container with audio only                     | http://l.web.umkc.edu/lizhu/teaching/2016sp.video-communication/ref/mp4.pdf                                                                                                                                        | Does not provide support of MPEG2-AAC |
| amr    | AMR-NB                                                       | http://hackipedia.org/File%20formats/Containers/AMR,%20Adaptive%20MultiRate/AMR%20format.pdf                                                                                                                       | Does not provide support of AMR-WB    |
| avi    | -                                                            | http://www.alexander-noe.com/video/documentation/avi.pdf                                                                                                                                                           |                                       |
| flac   | -                                                            | -                                                                                                                                                                                                                  | Support based on third-party library  |
| mp3    | MPEG 1/2 Layer 1/2/3                                         | https://github.com/wapmorgan/mp3info#technical-information                                                                                                                                                         |                                       |
| mp4    | MPEG 4 Part 12/14 container with few audio and video streams | Part 12 specification: http://l.web.umkc.edu/lizhu/teaching/2016sp.video-communication/ref/mp4.pdf Part 14 extension: https://www.cmlab.csie.ntu.edu.tw/~cathyp/eBooks/14496_MPEG4/ISO_IEC_14496-14_2003-11-15.pdf |                                       |
| ogg    | Ogg container with Vorbis audio                              | https://xiph.org/vorbis/doc/Vorbis_I_spec.html                                                                                                                                                                     |                                       |
| wav    | -                                                            | -                                                                                                                                                                                                                  | Support based on third-party library  |
| wma    | ASF container with only one audio stream                     | http://go.microsoft.com/fwlink/p/?linkid=31334                                                                                                                                                                     |                                       |
| wmv    | ASF container with few audio and video streams               | http://go.microsoft.com/fwlink/p/?linkid=31334                                                                                                                                                                     |                                       |
