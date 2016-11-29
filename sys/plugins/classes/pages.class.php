<?php

/**
 * Формирование списка страниц для постраничной навигации.
 * @property string limit SQL значение для LIMIT
 * @property int start Индекс первого пункта на странице
 * @property int end Индекс последнего пункта на странице
 * @property int items_per_page Кол-во пунктов на страницу
 * @property int pages Кол-во страниц всего
 * @property int this_page Текущая страница
 */
class pages {

    public $pages = 0; // количество страниц
    public $this_page = 1; // текущая страница
    protected $_items_count = 0; // количество пунктов всего
    protected $_items_per_page = 10; // количество пунктов на одну страницу

    /**
     * @param int $items_count Кол-во пунктов
     */

    function __construct($items_count = 0) {
        global $user;
        $this->items_per_page = $user->items_per_page;
        $this->posts = $items_count;
    }

    /**
     * Рассчет текущей страницы
     * @deprecated Рассчет производится автоматически. Больше этот метод вызывать вручную нет необходимости
     */
    function this_page() {
        
    }

    /**
     * Рассчет кол-ва страниц и текущей страницы
     */
    protected function _recalcPage() {
        if (!$this->_items_count) {
            $this->pages = 1;
        } else {
            $this->pages = ceil($this->_items_count / $this->_items_per_page);
        }

        if (isset($_GET['page'])) {
            if ($_GET['page'] == 'end') {
                $this->this_page = $this->pages;
            } elseif (is_numeric($_GET['page'])) {
                $this->this_page = max(1, min($this->pages, intval($_GET['page'])));
            } else {
                $this->this_page = 1;
            }
        } elseif (isset($_GET['postnum'])) {
            if ($_GET['postnum'] == 'end') {
                $this->this_page = $this->pages;
            } elseif (is_numeric($_GET['postnum'])) {
                $this->this_page = max(1, min($this->pages, ceil($_GET['postnum'] / $this->_items_per_page)));
            } else {
                $this->this_page = 1;
            }
        } else {
            $this->this_page = 1;
        }
    }

    /**
     * Для подстановки в MYSQL LIMIT
     * @return string
     */
    function limit() {
        return $this->my_start() . ', ' . $this->_items_per_page;
    }

    /**
     * старт извлечения из базы
     * @return int
     */
    function my_start() {
        return $this->_items_per_page * ($this->this_page - 1);
    }

    /**
     * конец
     * @return int
     */
    function end() {
        return $this->_items_per_page * $this->this_page;
    }

    /**
     * пересчет кол-ва страниц
     * @deprecated
     */
    function count() {
        
    }

    /**
     * Вывод списка страниц
     * @param string $link ссылка, к которой будет добавлено page={num}
     */
    function display($link) {
        if ($this->pages > 1) {
            $list = new design();
            $list->assign('link', $link);
            $list->assign('k_page', $this->pages);
            $list->assign('page', $this->this_page);
            $list->display('design.pages.tpl');
        }
    }

    /**
     * Вывод списка страниц
     * @param string $link ссылка, к которой будет добавлено page={num}
     * @deprecated
     */
    function listing($link) {
        $this->display($link);
    }

    function __set($name, $value) {
        switch ($name) {
            case 'posts':
                $this->_items_count = $value;
                break;
            case 'items_per_page':
                $this->_items_per_page = $value;
                break;
        }
        $this->_recalcPage();
    }

    function __get($name) {
        switch ($name) {
            case 'items_per_page':
                return $this->_items_per_page;
            case 'limit':
                return $this->limit();
            case 'my_start':
                return $this->my_start();
            case 'end':
                return $this->end();
            case 'posts':
                return $this->_items_count;
        }
    }

}
