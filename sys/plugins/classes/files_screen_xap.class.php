<?php

/**
 * Получение скриншота из иконки XAP приложения (WindowsPhone)
 */
class files_screen_xap {

    protected $_path_abs;

    function __construct($path_abs) {
        $this->_path_abs = $path_abs;
    }

    /**
     * Возвращает иконку приложения в GD
     * @return gd2
     */
    public function getScreen() {
        $pclzip = new PclZip($this->_path_abs);
        $manifest_xml = $pclzip->extract(PCLZIP_OPT_BY_NAME, 'WMAppManifest.xml', PCLZIP_OPT_EXTRACT_AS_STRING);
        if (!($manifest = simplexml_load_string($manifest_xml[0]['content'])))
            return;
        $icon = $pclzip->extract(PCLZIP_OPT_BY_NAME, (string)$manifest->App->IconPath, PCLZIP_OPT_EXTRACT_AS_STRING);
        if (!($img = imagecreatefromstring($icon[0]['content'])))
            return;
        return $img;
    }

}