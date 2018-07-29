
# MediaFile

Allows you easily get meta information about any media file with unified interface.
The library has no requirements of external libs or system unitilies.

[![Composer package](http://composer.network/badge/wapmorgan/media-file)](https://packagist.org/packages/wapmorgan/media-file)
[![Latest Stable Version](https://poser.pugx.org/wapmorgan/media-file/v/stable)](https://packagist.org/packages/wapmorgan/media-file)
[![License](https://poser.pugx.org/wapmorgan/media-file/license)](https://packagist.org/packages/wapmorgan/media-file)
[![Latest Unstable Version](https://poser.pugx.org/wapmorgan/media-file/v/unstable)](https://packagist.org/packages/wapmorgan/media-file)
[![Tests](https://travis-ci.org/wapmorgan/MediaFile.svg?branch=master)](https://travis-ci.org/wapmorgan/MediaFile)

**Supported formats**:

- **Audio:** aac, amr, flac, mp3, ogg, wav, wma
- **Video** avi, mkv, mp4, wmv

_Other formats support coming soon._

It can retrieve following information:

- For any audio:
  - length
  - bitRate
  - sampleRate
  - channels

- For any video:
  - length
  - width
  - height
  - frameRate

**Table of contents:**
1. Usage
2. API
3. Why not using getID3?
4. Technical details

# Usage

```php
use wapmorgan\MediaFile\MediaFile;

try {
  $media = MediaFile::open('123.mp3');
  // for audio
  if ($media->isAudio()) {
    $audio = $media->getAudio();
    echo 'Duration: '.$audio->getLength().PHP_EOL;
    echo 'Bit rate: '.$audio->getBitRate().PHP_EOL;
    echo 'Sample rate: '.$audio->getSampleRate().PHP_EOL;
    echo 'Channels: '.$audio->getChannels().PHP_EOL;
  }
  // for video
  else {
    $video = $media->getVideo();
    // calls to VideoAdapter interface
    echo 'Duration: '.$video->getLength().PHP_EOL;
    echo 'Dimensions: '.$video->getWidth().'x'.$video->getHeight().PHP_EOL;
    echo 'Framerate: '.$video->getFramerate().PHP_EOL;
  }
} catch (wapmorgan\MediaFile\Exceptions\FileAccessException $e) {
  // FileAccessException throws when file is not a detected media
} catch (wapmorgan\MediaFile\Exceptions\ParsingException $e) {
   echo 'File is propably corrupted: '.$e->getMessage().PHP_EOL;
}
```

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
| `getAudio()`                             | Returns an `AudioAdapter` interface for audio.                                      |                                                                                                         |
| `getVideo()`                             | Returns an `VideoAdapter` interface for video.                                      |                                                                                                         |

Available formats:

1. For `MediaFile::AUDIO`:

  | `MediaFile::WAV`     | `MediaFile::FLAC`    | `MediaFile::AAC`     | `MediaFile::OGG` |
  |----------------------|----------------------|----------------------|------------------|
  | **`MediaFile::MP3`** | **`MediaFile::AMR`** | **`MediaFile::WMA`** |                  |


2. For `MediaFile::VIDEO`:

  | `MediaFile::AVI` | `MediaFile::WMV` | `MediaFile::MP4` | `MediaFile::MKV` |
  |------------------|------------------|------------------|------------------|
  |                  |                  |                  |                  |

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

# Why not using getID3?

getID3 library is very popular and has a lot of features, but it's old and too slow.

Following table shows comparation of analyzing speed of fixtures, distributed with first release of MediaFile:

| File       | getID3 | MediaFile | Speed gain |
|------------|--------|-----------|------------|
| video.avi  | 0.215  | 0.126     | 1.71x      |
| video.mp4  | 3.055  | 0.429     | 7.12x      |
| video.wmv  | 0.354  | 0.372     | 0.95x      |
| audio.aac  | 0.560  | 0.262     | 2.13x      |
| audio.amr  | 8.241  | 12.248    | 0.67x      |
| audio.flac | 1.880  | 0.071     | 26.41x     |
| audio.m4a  | 13.372 | 0.169     | 79.14x     |
| audio.mp3  | 10.931 | 0.077     | 141.54x    |
| audio.ogg  | 0.170  | 0.096     | 1.78x      |
| audio.wav  | 0.114  | 0.070     | 1.64x      |
| audio.wma  | 0.195  | 0.158     | 1.23x      |

# Technical information

| Format | Full format name                                             | Specifications                                                                                                                                                                                                     | Notes                                 |
|--------|--------------------------------------------------------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|---------------------------------------|
| aac    | MPEG 4 Part 12 container with audio only                     | http://l.web.umkc.edu/lizhu/teaching/2016sp.video-communication/ref/mp4.pdf                                                                                                                                        | Does not provide support of MPEG2-AAC |
| amr    | AMR-NB                                                       | http://hackipedia.org/File%20formats/Containers/AMR,%20Adaptive%20MultiRate/AMR%20format.pdf                                                                                                                       | Does not provide support of AMR-WB    |
| avi    | -                                                            | http://www.alexander-noe.com/video/documentation/avi.pdf                                                                                                                                                           |                                       |
| flac   | -                                                            | -                                                                                                                                                                                                                  | Support based on third-party library  |
| mkv    | Matroska container                                           | https://www.matroska.org/technical/specs/index.html                                                                                                                                                                |                                       |
| mp3    | MPEG 1/2 Layer 1/2/3                                         | https://github.com/wapmorgan/mp3info#technical-information                                                                                                                                                         |                                       |
| mp4    | MPEG 4 Part 12/14 container with few audio and video streams | Part 12 specification: http://l.web.umkc.edu/lizhu/teaching/2016sp.video-communication/ref/mp4.pdf Part 14 extension: https://www.cmlab.csie.ntu.edu.tw/~cathyp/eBooks/14496_MPEG4/ISO_IEC_14496-14_2003-11-15.pdf |                                       |
| ogg    | Ogg container with Vorbis audio                              | https://xiph.org/vorbis/doc/Vorbis_I_spec.html                                                                                                                                                                     |                                       |
| wav    | -                                                            | -                                                                                                                                                                                                                  | Support based on third-party library  |
| wma    | ASF container with only one audio stream                     | http://go.microsoft.com/fwlink/p/?linkid=31334                                                                                                                                                                     |                                       |
| wmv    | ASF container with few audio and video streams               | http://go.microsoft.com/fwlink/p/?linkid=31334                                                                                                                                                                     |                                       |
