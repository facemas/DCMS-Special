<?php

class widgets {

    static public function exists($name) {
        return !!self::getWidgetByName($name);
    }

    /**
     * @param bool $nocache
     * @return widget[]
     */
    static public function getAllWidgets($nocache = false) {
        static $widgets = false;
        if (!$widgets && !$nocache) {
            $widgets = cache::get('widgets');
        }

        if (!$widgets) {
            $widgets = array();
            $widgets_path = H . '/sys/widgets';
            $od = opendir($widgets_path);
            while ($el_name = readdir($od)) {
                if ($el_name{0} === '.') {
                    continue;
                }
                if (!is_dir($widgets_path . '/' . $el_name)) {
                    continue;
                }
                if (!is_file($widgets_path . '/' . $el_name . '/config.ini')) {
                    continue;
                }
                try {
                    $widget = new widget($widgets_path . '/' . $el_name);
                    $widgets[] = $widget;
                } catch (Exception $e) {
                    
                }
            }
            closedir($od);
            cache::set('widgets', $widgets, 60);
        }
        return $widgets;
    }

    /**
     * @param $name
     * @return null|widget
     */
    static public function getWidgetByName($name) {
        $widgets = self::getAllWidgets();
        foreach ($widgets AS $widget) {
            if ($widget->getName() === $name) {
                return $widget;
            }
        }
        return null;
    }

}
