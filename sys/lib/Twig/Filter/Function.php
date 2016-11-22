<?php

@trigger_error('The Twig_Filter_Function class is deprecated since version 1.12 and will be removed in 2.0. Use Twig_SimpleFilter instead.', E_USER_DEPRECATED);

/**
 * Представляет шаблон функции фильтра.
 *
 * Вместо этого используйте Twig_SimpleFilter.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @deprecated since 1.12 (to be removed in 2.0)
 */
class Twig_Filter_Function extends Twig_Filter {

    protected $function;

    public function __construct($function, array $options = array()) {
        $options['callable'] = $function;

        parent::__construct($options);

        $this->function = $function;
    }

    public function compile() {
        return $this->function;
    }

}
