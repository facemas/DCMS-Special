<?php

class menu
{
    protected
        $_require_keys = array(
        'id',
        'id_parent',
        'position',
        'title',
        'url'
    );
    protected
        $_menu_key,
        $_items,
        $_last_item_id = 0;

    function __construct($key_or_items = null)
    {
        if (is_array($key_or_items)) {
            $this->_items = $this->_parseItems($key_or_items);
            $this->_menu_key = $this->_parseKeyFromItems($this->_items);
        } elseif (is_scalar($key_or_items)) {
            $items = $this->_getItemsFromBase($key_or_items);
            $this->_items = $this->_parseItems($items);
            $this->_menu_key = $key_or_items;
        } else {
            $this->_items = array();
            $this->_menu_key = null;
        }
    }

    private function _parseItems($its)
    {
        $items = array();
        foreach ($its as $item) {
            foreach ($this->_require_keys as $require_key) {
                if (!array_key_exists($require_key, $item)) {
                    throw new Exception(__('У элемента отсутствует параметр %s', $require_key));
                }
            }

            if (!is_numeric($item['id'])) {
                throw new Exception(__('Параметр %s должен быть %s', 'id', 'integer'));
            }

            $items[] = $item;
            $this->_last_item_id = max($this->_last_item_id, $item['id']);
        }
        return $items;
    }

    private function _parseKeyFromItems($items)
    {
        foreach ($items AS $item) {
            if (array_key_exists('menu_key', $item)) {
                return $item['menu_key'];
            }
        }
        return null;
    }

    private function _getItemsFromBase($menu_key)
    {
        $db = db::me();
        $res = $db->prepare("SELECT * FROM `menu` WHERE `menu_key` = :menu_key");
        $res->execute(array(':menu_key' => $menu_key));
        return $res->fetchAll();
    }

    private function _setItemId($old_id, $now_id)
    {
        foreach ($this->_items AS $index => $item) {
            if ($item['id'] === $old_id) {
                $this->_items[$index]['id'] = $now_id;
            }
            if ($item['id_parent'] === $old_id) {
                $this->_items[$index]['id_parent'] = $now_id;
            }
        }
    }

    private function _saveByParent($id_parent = null, $level = 0)
    {
        if ($level > 10) {
            throw new Exception(__("Слишком большая вложенность меню"));
        }

        $items = $this->getItems($id_parent);
        $db = db::me();
        $res = $db->prepare("INSERT INTO `menu` (`menu_key`, `id_parent`, `position`, `title`, `url`, `data`) VALUES (:menu_key, :id_parent, :position, :title, :url, :dt)");
        $position = 0;
        foreach ($items AS $item) {
            $res->execute(array(
                ':menu_key' => $this->_menu_key,
                ':id_parent' => $id_parent,
                ':position' => ++$position,
                ':title' => $item['title'],
                ':url' => $item['url'],
                ':dt' => array_key_exists('data', $item) ? $item['data'] : null
            ));
            $id = $db->lastInsertId();
            $this->_setItemId($item['id'], $id);
            $this->setItemPosition($id, $position);
            $this->_saveByParent($id, $level + 1);
        }
    }

    /**
     * @return null|string
     */
    public function getMenuKey()
    {
        return $this->_menu_key;
    }

    private function _position_cmp($item1, $item2)
    {
        $a = $item1['position'];
        $b = $item2['position'];

        switch (true) {
            case $a == $b:
                return 0;
            case $a > $b:
                return 1;
            case $a < $b:
                return -1;
        }
    }

    /**
     * Сохранение меню в базе
     * @param string $key Ключ меню (можно не указывать, если меню инициализировано по ключу)
     * @throws Exception
     */
    public function save($key = null)
    {
        if (!$key && !$this->_menu_key) {
            throw new Exception(__('Ключ меню не задан'));
        }

        if ($this->_menu_key) {
            $db = db::me();
            $res = $db->prepare("DELETE FROM `menu` WHERE `menu_key` = :menu_key");
            $res->execute(array(':menu_key' => $this->_menu_key));
        }

        if ($key && is_scalar($key)) {
            $this->_menu_key = $key;
        }
        $this->_saveByParent(null); // рекурсивное сохранение от корня
    }

    /**
     * Добавление пункта меню
     * @param string $url Ссылка
     * @param string $title Заголовок
     * @param int $position Позиция на уровне
     * @param int|null $id_parent Родитель (null-корень)
     * @return int Идентификатор нового пункта
     * @throws Exception
     */
    public function addItem($title, $url, $position = 0, $id_parent = null)
    {
        if (!is_null($id_parent) && !$this->getItemById($id_parent)) {
            throw new Exception(__("Родитель с id %s не найден", $id_parent));
        }

        $id = ++$this->_last_item_id;
        $this->_items[] = array(
            'id' => $id,
            'menu_key' => $this->_menu_key,
            'id_parent' => $id_parent,
            'position' => $position,
            'title' => $title,
            'url' => $url
        );
        return $id;
    }

    /**
     * Установка позиции пункта меню
     * @param int $id Идентификатор
     * @param int $position позиция на уровне
     */
    public function setItemPosition($id, $position)
    {
        foreach ($this->_items AS $key => $item) {
            if ($item['id'] === $id) {
                $this->_items[$key]['position'] = $position;
            }
        }
    }

    /**
     * Удаление пункта меню со всеми дочерними пунктами
     * @param int $id
     */
    public function removeItemById($id)
    {
        $child_items = $this->getItems($id);
        foreach ($child_items as $item) {
            $this->removeItemById($item['id']);
        }
        foreach ($this->_items as $key => $item) {
            if ($item['id'] === $id) {
                unset($this->_items[$key]);
            }
        }
        $this->_items = array_values($this->_items); // сбрасываем ключи элементов.
    }

    /**
     * Получение пункта меню по его идентификатору
     * @param int $id Идентификатор
     * @return array|null
     */
    public function getItemById($id)
    {
        foreach ($this->_items as $item) {
            if ($item['id'] === $id) {
                return $item;
            }
        }
        return null;
    }

    /**
     * Получение списка пунктов меню
     * @param int|null $id_parent Родитель (null - корневые элементы)
     * @param bool $recursive Рекурсивное получение всех дочерних пунктов меню
     * @return array
     */
    public function getItems($id_parent = null, $recursive = false)
    {
        $items = array();
        foreach ($this->_items AS $item) {
            if ($item['id_parent'] === $id_parent) {
                $it = $item;
                if ($recursive) {
                    $it['items'] = $this->getItems($it['id'], true);
                }
                $items[] = $it;
            }
        }
        usort($items, array($this, '_position_cmp'));
        return $items;
    }
}