<?php

/**
 * Реализует стратегию no-cache.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Twig_Cache_Null implements Twig_CacheInterface {

    /**
     * {@inheritdoc}
     */
    public function generateKey($name, $className) {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function write($key, $content) {
        
    }

    /**
     * {@inheritdoc}
     */
    public function load($key) {
        
    }

    /**
     * {@inheritdoc}
     */
    public function getTimestamp($key) {
        return 0;
    }

}
