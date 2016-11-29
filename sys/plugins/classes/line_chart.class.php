<?php

/**
 * Class line_chart_series
 * @property int[] data
 */
class line_chart_series
{
    public $name;
    public $data = array();

    function __construct($name)
    {
        $this->name = $name;
    }
}

/**
 * Class line_chart
 * @property line_chart_series series
 * @property string[] categories
 */
class line_chart extends ui
{
    public $series = array();
    public $categories = array();
    public $y_text = '';
    public $value_suffix = '';
    public $title;

    public function __construct($title)
    {
        $this->title = $title;
        parent::__construct();
        $this->_tpl_file = 'chart.line.tpl';
    }

    public function fetch()
    {
        $this->_data['y_text'] = $this->y_text;
        $this->_data['value_suffix'] = $this->value_suffix;
        $this->_data['series'] = $this->series;
        $this->_data['categories'] = $this->categories;
        $this->_data['title'] = $this->title;
        return parent::fetch();
    }

} 