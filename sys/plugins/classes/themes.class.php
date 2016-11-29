<?php

/**
 * Список тем оформления
 */
abstract class themes {

    /**
     * Проверка на существование темы оформления
     * @param string $name имя темы оформления (название папки с темой)
     * @param string $type
     * @return boolean
     */
    static public function exists($name, $type = 'all') {
        return !!self::getThemeByName($name, $type);
    }

    /**
     * @param bool $nocache
     * @return theme[]
     */
    static public function getAllThemes($nocache = false) {
        static $themes = false;
        if (!$themes && !$nocache) {
            $themes = cache::get('themes_obj');
        }

        if (!$themes) {
            $themes = array();
            $themes_path = H . '/sys/themes';
            $od = opendir($themes_path);
            while ($el_name = readdir($od)) {
                if ($el_name{0} === '.') {
                    continue;
                }
                if (!is_dir($themes_path . '/' . $el_name)) {
                    continue;
                }
                try {
                    $theme = new theme($themes_path . '/' . $el_name);
                    if ($theme->getVersion() != dcms::getInstance()->theme_version) {
                        continue;
                    }
                    $themes[] = $theme;
                } catch (Exception $e) {
                    throw $e;
                }
            }
            closedir($od);
            cache::set('themes_obj', $themes, 60);
        }
        return $themes;
    }

    /**
     * @param string $type
     * @return theme[]
     */
    static public function getThemesByType($type) {
        $themes_all = self::getAllThemes();
        $themes = array();

        foreach ($themes_all as $theme) {
            if ($type === 'all' || $theme->browserSupport($type)) {
                $themes[] = $theme;
            }
        }

        return $themes;
    }

    /**
     * @param string $name
     * @param string $type
     * @return null|theme
     */
    static public function getThemeByName($name, $type = 'all') {
        $themes_all = self::getThemesByType($type);
        foreach ($themes_all as $theme) {
            if ($theme->getName() === $name) {
                return $theme;
            }
        }
        return null;
    }

    /**
     * очистка кэша списка тем
     */
    static public function clearCache() {
        cache::set('themes_obj', false);
    }

}
