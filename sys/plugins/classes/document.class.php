<?php

/**
 * Класс для формирования HTML документа.
 */
class document extends design {

    public $title = 'Заголовок';
    public $description = '';
    public $keywords = array();
    public $last_modified = null;
    protected $err = array();
    protected $msg = array();
    protected $info = array();
    protected $outputed = false;
    protected $actions = array();
    protected $options = array();
    protected $returns = array();
    protected $tabs = array();
    public $head = '';
    protected $_echo_content = '';

    function __construct($group = 0) {
        parent::__construct();
        global $user, $dcms;
        $this->title = $dcms->title;
        if ($group > $user->group) {
            $this->access_denied(__('Доступ к данной странице запрещен'));
        }
        ob_start();
    }

    /**
     * @param $name
     * @param string|url $url
     * @param bool $selected
     * @return document_link
     */
    function tab($name, $url, $selected = false) {
        return $this->tabs[] = new document_link(text::toValue($name), $url, $selected);
    }

    function ret($name, $url, $selected = false, $icon = false) {
        return $this->returns[] = new document_link(text::toValue($name), $url, $selected, $icon);
    }

    function opt($name, $url, $selected = false, $icon = false) {
        return $this->options[] = new document_link(text::toValue($name), $url, $selected, $icon);
    }

    function act($name, $url, $selected = false, $icon = false) {
        return $this->actions[] = new document_link(text::toValue($name), $url, $selected, $icon);
    }

    function err($text) {
        return $this->err[] = new document_message($text, true);
    }

    function info($text) {
        return $this->info[] = new document_message($text);
    }

    /**
     * @param $text
     * @return document_message
     */
    function msg($text) {
        return $this->msg[] = new document_message($text);
    }

    /**
     * Переадресация на адрес, указанный в GET параметре return или в $default_url
     * @param string $default_url
     * @param int $timeout Время, через которое произойдет переадресация
     */
    function toReturn($default_url = '/', $timeout = 2) {
        if ($default_url instanceof url) {
            $url = $default_url->getUrl();
        } else {
            $url = $default_url;
        }

        if (!empty($_GET['return'])) {
            $url_return = new url($_GET['return']);
            if ($url_return->isInternalLink()) {
                $url = $url_return->getUrl();
            }
        }
        if ($timeout) {
            header('Refresh: ' . intval($timeout) . '; url=' . $url);
        } else {
            // если задержки быть не должно, то ничего на клиент не отправляем и работу скрипта прерываем
            header('Location: ' . $url);
            $this->outputed = true;
            exit;
        }
    }

    /**
     * Отображение страницы с ошибкой
     * @param string $err Текст ошибки
     */
    function access_denied($err) {
        if (isset($_GET['return']) && $url_return = new url($_GET['return'])) {
            if ($url_return->isInternalLink()) {
                header('Refresh: 2; url=' . $_GET['return']);
            }
        }
        $this->err($err);
        $this->output();
        exit;
    }

    /**
     * Формирование HTML документа и отправка данных браузеру
     * @global dcms $dcms
     */
    private function output() {
        global $dcms;
        if ($this->outputed) {
            // повторная отправка html кода вызовет нарушение синтаксиса документа, да и вообще нам этого нафиг не надо
            return;
        }
        $this->outputed = true;
        header('Cache-Control: no-store, no-cache, must-revalidate', true);
        header('Expires: ' . date('r'), true);
        if ($this->last_modified) {
            header("Last-Modified: " . gmdate("D, d M Y H:i:s", (int) $this->last_modified) . " GMT", true);
        }

        header('X-UA-Compatible: IE=edge', true); // отключение режима совместимости в осле
        header('Content-Type: text/html; charset=utf-8', true);

        $this->assign('description', $this->description ? $this->description : $this->title, 1); // описание страницы (meta)
        $this->assign('keywords', $this->keywords ? $this->keywords : $this->title, 1); // ключевые слова (meta)

        $this->assign('actions', $this->actions); // ссылки к действию
        $this->assign('options', $this->options); // ссылки для опций
        $this->assign('returns', $this->returns); // ссылки для возврата
        $this->assign('tabs', $this->tabs); // вкладки
        $this->assign('head', $this->head, 1); // навигация

        $this->assign('err', $this->err); // сообщения об ошибке
        $this->assign('msg', $this->msg); // сообщения
        $this->assign('info', $this->info); // сообщения
        $this->assign('title', $this->title, 1); // заголовок страницы

        $this->_echo_content = ob_get_clean(); // то, что попало в буфер обмена при помощи echo (display())

        $this->assign('document_generation_time', round(microtime(true) - TIME_START, 3)); // время генерации страницы

        if ($dcms->align_html) {
            // форматирование HTML кода
            $document_content = $this->fetch('document.tpl');
            $align = new alignedxhtml();
            echo $align->parse($document_content);
        } else {
            $this->display('document.tpl');
        }
    }

    /**
     * отображение содержимого блока темы
     * @param string $section
     */
    public function displaySection($section) {
        if ($section === $this->theme->getEchoSectionKey()) {
            echo $this->_echo_content;
        }
        $widgets = $this->theme->getWidgets($section);
        foreach ($widgets as $widget_name) {
            $widget = new widget(H . '/sys/widgets/' . $widget_name); // открываем
            $widget->display(); // отображаем
        }
    }

    /**
     * Очистка вывода
     * Тема оформления применяться не будет
     */
    function clean() {
        $this->outputed = true;
        ob_clean();
    }

    /**
     * То что срабатывает при exit
     */
    function __destruct() {
        $this->output();
    }

}
