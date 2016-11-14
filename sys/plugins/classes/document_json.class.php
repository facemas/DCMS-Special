<?php

class document_json extends document {

    protected $form = array();
    protected $add = array();
    protected $remove = array();
    protected $pages;

    function __construct() {
        parent::__construct();
        $skip_ids_str = @$_POST['skip_ids'];
        $this->remove = $skip_ids_str ? explode(',', $skip_ids_str) : array();
    }

    /**
     * @param pages $pages
     */
    function set_pages($pages) {
        if ($pages instanceof pages)
            $this->pages = $pages;
    }

    /**
     * @param listing_post $post
     * @param $id_after
     */
    function add_post($post, $id_after = false) {
        if (!in_array($post->id, $this->remove)) {
            $this->add[] = array('html' => $post->fetch(), 'after_id' => $id_after);
        } elseif (false !== ($key = array_search($post->id, $this->remove))) {
            unset($this->remove[$key]);
        }
    }

    function form_value($name, $value) {
        $this->form[$name] = $value;
    }

    function __destruct() {
        $this->clean();
        header('Content-type: application/json; charset=utf-8', true);
        echo json_encode(array(
            'add' => $this->add,
            'remove' => array_values($this->remove),
            'msg' => $this->msg ? $this->msg[count($this->msg) - 1]->text : array(),
            'err' => $this->err ? $this->err[count($this->err) - 1]->text : array(),
            'form' => $this->form,
            'pages' => $this->pages
        ));
    }

}
