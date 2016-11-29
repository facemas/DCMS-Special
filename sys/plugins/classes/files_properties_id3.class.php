<?php

include_once H . '/sys/plugins/id3/getid3/getid3.php';

/**
 * Получение свойств из медиа файлов с использованием библиотеки getid3
 */
class files_properties_id3 {

    protected $_path_abs;

    function __construct($path_abs) {
        $this->_path_abs = $path_abs;
    }

    /**
     * Получение свойств из файла
     * @return array
     */
    public function getProperties() {
        $properties = array();
        $getID3 = new getID3;
        $ThisFileInfo = $getID3->analyze($this->_path_abs);
        $properties['properties'] = array();

        if (!empty($ThisFileInfo['playtime_string']) && !empty($ThisFileInfo['playtime_seconds'])) {
            $properties['properties'][] = $properties['playtime_string'] = $ThisFileInfo['playtime_string'];
            $properties['playtime_seconds'] = $ThisFileInfo['playtime_seconds'];
        }

        if (!empty($ThisFileInfo['video']['resolution_x']) && !empty($ThisFileInfo['video']['resolution_y'])) {
            $properties['width'] = $ThisFileInfo['video']['resolution_x'];
            $properties['height'] = $ThisFileInfo['video']['resolution_y'];
            $properties['properties'][] = $properties['width'] . 'x' . $properties['height'];
        }

        if (!empty($ThisFileInfo['video']['bitrate']))
            $properties['video_bitrate'] = $ThisFileInfo['video']['bitrate'];

        if (!empty($ThisFileInfo['video']['bitrate_mode']))
            $properties['video_bitrate_mode'] = $ThisFileInfo['video']['bitrate_mode'];

        if (!empty($ThisFileInfo['video']['codec']))
            $properties['properties'][] = $properties['video_codec'] = $ThisFileInfo['video']['codec'];

        if (!empty($ThisFileInfo['video']['frame_rate']))
            $properties['video_frame_rate'] = $ThisFileInfo['video']['frame_rate'];

        if (!empty($ThisFileInfo['audio']['bitrate']))
            $properties['audio_bitrate'] = $ThisFileInfo['audio']['bitrate'];

        if (!empty($ThisFileInfo['audio']['bitrate_mode']))
            $properties['audio_bitrate_mode'] = $ThisFileInfo['audio']['bitrate_mode'];

        if (!empty($ThisFileInfo['audio']['codec']))
            $properties['properties'][] = $properties['audio_codec'] = $ThisFileInfo['audio']['codec'];
        $tags = array();
        if (!empty($ThisFileInfo['tags'])) {
            foreach ($ThisFileInfo['tags'] as $key => $value) {
                foreach ($value as $key2 => $value2)
                    $tags[$key2] = implode(', ', $value2);
            }
        }

        if (!empty($tags['title']))
            $properties['title'] = $tags['title'];
        if (!empty($tags['artist']))
            $properties['artist'] = $tags['artist'];
        if (!empty($tags['band']))
            $properties['band'] = $tags['band'];
        if (!empty($tags['album']))
            $properties['album'] = $tags['album'];
        if (!empty($tags['year']))
            $properties['year'] = $tags['year'];
        if (!empty($tags['genre']))
            $properties['genre'] = $tags['genre'];
        if (!empty($tags['comment']))
            $properties['comment'] = $tags['comment'];
        elseif (!empty($tags['comments']))
            $properties['comment'] = $tags['comments'];
        if (!empty($tags['track_number']))
            $properties['track_number'] = (int) $tags['track_number'];
        elseif (!empty($tags['track']))
            $properties['track_number'] = (int) $tags['track'];
        if (!empty($tags['language']))
            $properties['language'] = $tags['language'];
        if (!empty($tags['url_user']))
            $properties['url'] = strtolower($tags['url_user']);
        if (!empty($tags['copyright']))
            $properties['copyright'] = $tags['copyright'];
        $properties['properties'] = implode(' / ', $properties['properties']);

        return $properties;
    }

}
