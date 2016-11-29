<?php

/**
 * Обработчик BBCODE
 */
class bbcode {
    /*
      Описания тегов. Каждое описание - масив свойств:
      'handler'  - название функции - обработчика тегов.
      'is_close' - true, если тег всегда считается закрытым (например [hr]).
      'lbr'       - число переводов строк, которые следует игнорировать перед
      элементом.
      'rbr'      - число переводов строк, которые следует игнорировать после
      элемента.
      'ends'     - список тегов, начало которых обязательно закрывает данный.
      'permission_top_level' - true, если тегу разрешено находиться в корне
      дерева элементов.
      'children' - список тегов, которым разрешено быть вложенными в данный.
     */

    var $info_about_tags = array(
        'nobb' => array(
            'handler' => 'nobb_2html',
            'is_close' => false,
            'lbr' => 0,
            'rbr' => 0,
            'ends' => array(),
            'permission_top_level' => true,
            'children' => array()
        ),
        'u' => array(
            'handler' => 'u_2html',
            'is_close' => false,
            'lbr' => 0,
            'rbr' => 0,
            'ends' => array(),
            'permission_top_level' => true,
            'children' => array(
                'big',
                'u',
                'i',
                'b',
                'small',
                'url',
                'when',
                'user',
                'no',
                'smile',
                'color',
                'red',
                'green',
                'blue',
                'yellow',
                'gradient',
                'font',
                'mark'
            )
        ),
        'no' => array(
            'handler' => 'no_2html',
            'is_close' => false,
            'lbr' => 0,
            'rbr' => 0,
            'ends' => array(),
            'permission_top_level' => true,
            'children' => array(
                'big',
                'u',
                'i',
                'b',
                'small',
                'url',
                'when',
                'user',
                'smile',
                'color',
                'red',
                'green',
                'blue',
                'yellow',
                'gradient',
                'font',
                'mark'
            )
        ),
        'i' => array(
            'handler' => 'i_2html',
            'is_close' => false,
            'lbr' => 0,
            'rbr' => 0,
            'ends' => array(),
            'permission_top_level' => true,
            'children' => array(
                'big',
                'u',
                'i',
                'b',
                'small',
                'url',
                'when',
                'user',
                'no',
                'smile',
                'color',
                'red',
                'green',
                'blue',
                'yellow',
                'gradient',
                'font',
                'mark'
            )
        ),
        'b' => array(
            'handler' => 'b_2html',
            'is_close' => false,
            'lbr' => 0,
            'rbr' => 0,
            'ends' => array(),
            'permission_top_level' => true,
            'children' => array(
                'big',
                'u',
                'i',
                'b',
                'small',
                'url',
                'when',
                'user',
                'no',
                'smile',
                'color',
                'red',
                'green',
                'blue',
                'yellow',
                'gradient',
                'font',
                'mark'
            )
        ),
        'big' => array(
            'handler' => 'big_2html',
            'is_close' => false,
            'lbr' => 0,
            'rbr' => 0,
            'ends' => array(),
            'permission_top_level' => true,
            'children' => array(
                'big',
                'u',
                'i',
                'b',
                'small',
                'url',
                'when',
                'user',
                'no',
                'smile',
                'color',
                'red',
                'green',
                'blue',
                'yellow',
                'gradient',
                'font'
            )
        ),
        'mark' => array(
            'handler' => 'mark_2html',
            'is_close' => false,
            'lbr' => 0,
            'rbr' => 0,
            'ends' => array(),
            'permission_top_level' => true,
            'children' => array(
                'big',
                'u',
                'i',
                'b',
                'small',
                'url',
                'when',
                'user',
                'no',
                'smile',
                'font',
                'left',
                'center',
                'right'
            )
        ),
        'small' => array(
            'handler' => 'small_2html',
            'is_close' => false,
            'lbr' => 0,
            'rbr' => 0,
            'ends' => array(),
            'permission_top_level' => true,
            'children' => array(
                'big',
                'u',
                'i',
                'b',
                'small',
                'url',
                'when',
                'user',
                'no',
                'smile',
                'color',
                'red',
                'green',
                'blue',
                'yellow',
                'gradient',
                'font',
                'mark'
            )
        ),
        'user' => array(
            'handler' => 'user_2html',
            'is_close' => false,
            'lbr' => 0,
            'rbr' => 0,
            'ends' => array(),
            'permission_top_level' => true,
            'children' => array()
        ),
        'smile' => array(
            'handler' => 'smile_2html',
            'is_close' => false,
            'lbr' => 0,
            'rbr' => 0,
            'ends' => array(),
            'permission_top_level' => true,
            'children' => array()
        ),
        'spoiler' => array(
            'handler' => 'spoiler_2html',
            'is_close' => false,
            'lbr' => 1,
            'rbr' => 1,
            'ends' => array(),
            'permission_top_level' => true,
            'children' => array(
                'big',
                'u',
                'i',
                'b',
                'quote',
                'small',
                'user',
                'smile',
                'url',
                'when',
                'no',
                'php',
                'hide',
                'spoiler',
                'color',
                'red',
                'green',
                'blue',
                'yellow',
                'localimg',
                'gradient',
                'youtube',
                'font',
                'mark',
                'left',
                'center',
                'right'
            )
        ),
        'youtube' => array(
            'handler' => 'youtube_2html',
            'is_close' => false,
            'lbr' => 1,
            'rbr' => 1,
            'ends' => array(),
            'permission_top_level' => true,
            'children' => array()
        ),
        'vk_video' => array(
            'handler' => 'vk_video_2html',
            'is_close' => true,
            'lbr' => 1,
            'rbr' => 1,
            'ends' => array(),
            'permission_top_level' => true,
            'children' => array()
        ),
        'localimg' => array(
            'handler' => 'img_2html',
            'is_close' => false,
            'lbr' => 1,
            'rbr' => 1,
            'ends' => array(),
            'permission_top_level' => true,
            'children' => array()
        ),
        'url' => array(
            'handler' => 'url_2html',
            'is_close' => false,
            'lbr' => 0,
            'rbr' => 0,
            'ends' => array(),
            'permission_top_level' => true,
            'children' => array(
                'big',
                'u',
                'i',
                'b',
                'small',
                'smile',
                'color',
                'red',
                'green',
                'blue',
                'yellow',
                'gradient',
                'localimg',
                'font',
                'mark'
            )
        ),
        'php' => array(
            'handler' => 'php_2html',
            'is_close' => false,
            'lbr' => 1,
            'rbr' => 1,
            'ends' => array(),
            'permission_top_level' => true,
            'children' => array()
        ),
        'quote' => array(
            'handler' => 'quote_2html',
            'is_close' => false,
            'lbr' => 1,
            'rbr' => 1,
            'ends' => array(),
            'permission_top_level' => true,
            'children' => array(
                'big',
                'u',
                'i',
                'b',
                'small',
                'user',
                'smile',
                'url',
                'quote',
                'when',
                'no',
                'php',
                'gradient',
                'localimg',
                'spoiler',
                'color',
                'font',
                'mark',
                'nobb',
                'red',
                'green',
                'blue',
                'yellow',
                'left',
                'center',
                'right'
            )
        ),
        'color' => array(
            'handler' => 'color_2html',
            'is_close' => false,
            'lbr' => 0,
            'rbr' => 0,
            'ends' => array('user', 'smile', 'quote'),
            'permission_top_level' => true,
            'children' => array('big', 'u', 'i', 'b', 'small', 'when', 'no', 'url', 'font')
        ),
        'gradient' => array(
            'handler' => 'gradient_2html',
            'is_close' => false,
            'lbr' => 0,
            'rbr' => 0,
            'ends' => array('user', 'smile', 'url', 'quote'),
            'permission_top_level' => true,
            'children' => array('big', 'u', 'i', 'b', 'small', 'when', 'no', 'font', 'mark')
        ),
        'red' => array(
            'handler' => 'color_2html',
            'is_close' => false,
            'lbr' => 0,
            'rbr' => 0,
            'ends' => array('user', 'smile', 'quote'),
            'permission_top_level' => true,
            'children' => array('big', 'u', 'i', 'b', 'small', 'when', 'no', 'url', 'font')
        ),
        'green' => array(
            'handler' => 'color_2html',
            'is_close' => false,
            'lbr' => 0,
            'rbr' => 0,
            'ends' => array('user', 'smile', 'quote'),
            'permission_top_level' => true,
            'children' => array('big', 'u', 'i', 'b', 'small', 'when', 'no', 'url', 'font')
        ),
        'blue' => array(
            'handler' => 'color_2html',
            'is_close' => false,
            'lbr' => 0,
            'rbr' => 0,
            'ends' => array('user', 'smile', 'quote'),
            'permission_top_level' => true,
            'children' => array('big', 'u', 'i', 'b', 'small', 'when', 'no', 'url', 'font')
        ),
        'yellow' => array(
            'handler' => 'color_2html',
            'is_close' => false,
            'lbr' => 0,
            'rbr' => 0,
            'ends' => array('user', 'smile', 'quote'),
            'permission_top_level' => true,
            'children' => array('big', 'u', 'i', 'b', 'small', 'when', 'no', 'url', 'font')
        ),
        'when' => array(
            'handler' => 'vremja_2html',
            'is_close' => false,
            'lbr' => 0,
            'rbr' => 0,
            'ends' => array(),
            'permission_top_level' => true,
            'children' => array()
        ),
        'font' => array(
            'handler' => 'font_2html',
            'is_close' => false,
            'lbr' => 0,
            'rbr' => 0,
            'ends' => array(),
            'permission_top_level' => true,
            'children' => array()
        ),
        'hr' => array(
            'handler' => 'hr_2html',
            'is_close' => true,
            'lbr' => 0,
            'rbr' => 0,
            'ends' => array(),
            'permission_top_level' => true,
            'children' => array()
        ),
        'left' => array(
            'handler' => 'left_2html',
            'is_close' => false,
            'lbr' => 1,
            'rbr' => 1,
            'ends' => array(),
            'permission_top_level' => true,
            'children' => array(
                'big',
                'u',
                'i',
                'b',
                'small',
                'user',
                'smile',
                'url',
                'quote',
                'when',
                'no',
                'php',
                'gradient',
                'localimg',
                'spoiler',
                'color',
                'font',
                'mark'
            )
        ),
        'center' => array(
            'handler' => 'center_2html',
            'is_close' => false,
            'lbr' => 1,
            'rbr' => 1,
            'ends' => array(),
            'permission_top_level' => true,
            'children' => array(
                'big',
                'u',
                'i',
                'b',
                'small',
                'user',
                'smile',
                'url',
                'quote',
                'when',
                'no',
                'php',
                'gradient',
                'localimg',
                'spoiler',
                'color',
                'font',
                'mark'
            )
        ),
        'right' => array(
            'handler' => 'right_2html',
            'is_close' => false,
            'lbr' => 1,
            'rbr' => 1,
            'ends' => array(),
            'permission_top_level' => true,
            'children' => array(
                'big',
                'u',
                'i',
                'b',
                'small',
                'user',
                'smile',
                'url',
                'quote',
                'when',
                'no',
                'php',
                'gradient',
                'localimg',
                'spoiler',
                'color',
                'font',
                'mark'
            )
        ),
        'indent' => array(
            'handler' => 'indent_2html',
            'is_close' => false,
            'lbr' => 1,
            'rbr' => 1,
            'ends' => array(),
            'permission_top_level' => true,
            'children' => array(
                'big',
                'u',
                'i',
                'b',
                'small',
                'user',
                'smile',
                'url',
                'quote',
                'when',
                'no',
                'php',
                'gradient',
                'localimg',
                'spoiler',
                'color',
                'font',
                'mark'
            )
        ),
    );
    var $mnemonics = array();
    var $syntax = array();

    function getArrayOfTokens($code) {
        $length = @strlen($code);
        $tokens = array();
        $token_key = -1;
        $type_of_char = null;
        for ($i = 0; $i < $length; ++$i) {
            $previous_type = $type_of_char;
            switch ($code{$i}) {
                case '[':
                    $type_of_char = 0;
                    break;
                case ']':
                    $type_of_char = 1;
                    break;
                case '"':
                    $type_of_char = 2;
                    break;
                case "'":
                    $type_of_char = 3;
                    break;
                case "=":
                    $type_of_char = 4;
                    break;
                case '/':
                    $type_of_char = 5;
                    break;
                case ' ':
                    $type_of_char = 6;
                    break;
                case "\t":
                    $type_of_char = 6;
                    break;
                case "\n":
                    $type_of_char = 6;
                    break;
                case "\r":
                    $type_of_char = 6;
                    break;
                case "\0":
                    $type_of_char = 6;
                    break;
                case "\x0B":
                    $type_of_char = 6;
                    break;
                default:
                    $type_of_char = 7;
            }
            if (7 == $previous_type && $type_of_char != $previous_type) {
                $word = strtolower($tokens[$token_key][1]);
                if (isset($this->info_about_tags[$word])) {
                    $tokens[$token_key][0] = 8;
                }
            }
            switch ($type_of_char) {
                case 6:
                    if (6 == $previous_type) {
                        $tokens[$token_key][1] .= $code{$i};
                    } else {
                        $tokens[++$token_key] = array(6, $code{$i});
                    }
                    break;
                case 7:
                    if (7 == $previous_type) {
                        $tokens[$token_key][1] .= $code{$i};
                    } else {
                        $tokens[++$token_key] = array(7, $code{$i});
                    }
                    break;
                default:
                    $tokens[++$token_key] = array($type_of_char, $code{$i});
            }
        }
        return $tokens;
    }

    function __construct($code) {

        $finite_automaton = array(
            // Предыдущие |   Состояния для текущих событий (лексем)   |
            //  состояния |  0 |  1 |  2 |  3 |  4 |  5 |  6 |  7 |  8 |
            0 => array(1, 0, 0, 0, 0, 0, 0, 0, 0)
            ,
            1 => array(2, 3, 3, 3, 3, 4, 3, 3, 5)
            ,
            2 => array(2, 3, 3, 3, 3, 4, 3, 3, 5)
            ,
            3 => array(1, 0, 0, 0, 0, 0, 0, 0, 0)
            ,
            4 => array(2, 6, 3, 3, 3, 3, 3, 3, 7)
            ,
            5 => array(2, 6, 3, 3, 8, 9, 10, 3, 3)
            ,
            6 => array(1, 0, 0, 0, 0, 0, 0, 0, 0)
            ,
            7 => array(2, 6, 3, 3, 3, 3, 3, 3, 3)
            ,
            8 => array(13, 13, 11, 12, 13, 13, 14, 13, 13)
            ,
            9 => array(2, 6, 3, 3, 3, 3, 3, 3, 3)
            ,
            10 => array(2, 6, 3, 3, 8, 9, 3, 15, 15)
            ,
            11 => array(16, 16, 17, 16, 16, 16, 16, 16, 16)
            ,
            12 => array(18, 18, 18, 17, 18, 18, 18, 18, 18)
            ,
            13 => array(19, 6, 19, 19, 19, 19, 17, 19, 19)
            ,
            14 => array(2, 3, 11, 12, 13, 13, 3, 13, 13)
            ,
            15 => array(2, 6, 3, 3, 8, 9, 10, 3, 3)
            ,
            16 => array(16, 16, 17, 16, 16, 16, 16, 16, 16)
            ,
            17 => array(2, 6, 3, 3, 3, 9, 20, 15, 15)
            ,
            18 => array(18, 18, 18, 17, 18, 18, 18, 18, 18)
            ,
            19 => array(19, 6, 19, 19, 19, 19, 20, 19, 19)
            ,
            20 => array(2, 6, 3, 3, 3, 9, 3, 15, 15)
        );
        // Получаем массив лексем:
        $array_of_tokens = $this->getArrayOfTokens($code);
        // Сканируем его с помощью построенного автомата:
        $mode = 0;
        $result = array();
        $tag_decomposition = array();
        $token_key = -1;
        foreach ($array_of_tokens as $token) {
            $previous_mode = $mode;
            $mode = $finite_automaton[$previous_mode][$token[0]];
            switch ($mode) {
                case 0:
                    if (-1 < $token_key && 'text' == $result[$token_key]['type']) {
                        $result[$token_key]['str'] .= $token[1];
                    } else {
                        $result[++$token_key] = array(
                            'type' => 'text',
                            'str' => $token[1]
                        );
                    }
                    break;
                case 1:
                    $tag_decomposition['name'] = '';
                    $tag_decomposition['type'] = '';
                    $tag_decomposition['str'] = '[';
                    $tag_decomposition['layout'][] = array(0, '[');
                    break;
                case 2:
                    if (-1 < $token_key && 'text' == $result[$token_key]['type']) {
                        $result[$token_key]['str'] .= $tag_decomposition['str'];
                    } else {
                        $result[++$token_key] = array(
                            'type' => 'text',
                            'str' => $tag_decomposition['str']
                        );
                    }
                    $tag_decomposition = array();
                    $tag_decomposition['name'] = '';
                    $tag_decomposition['type'] = '';
                    $tag_decomposition['str'] = '[';
                    $tag_decomposition['layout'][] = array(0, '[');
                    break;
                case 3:
                    if (-1 < $token_key && 'text' == $result[$token_key]['type']) {
                        $result[$token_key]['str'] .= $tag_decomposition['str'];
                        $result[$token_key]['str'] .= $token[1];
                    } else {
                        $result[++$token_key] = array(
                            'type' => 'text',
                            'str' => $tag_decomposition['str'] . $token[1]
                        );
                    }
                    $tag_decomposition = array();
                    break;
                case 4:
                    $tag_decomposition['type'] = 'close';
                    $tag_decomposition['str'] .= '/';
                    $tag_decomposition['layout'][] = array(1, '/');
                    break;
                case 5:
                    $tag_decomposition['type'] = 'open';
                    $name = strtolower($token[1]);
                    $tag_decomposition['name'] = $name;
                    $tag_decomposition['str'] .= $token[1];
                    $tag_decomposition['layout'][] = array(2, $token[1]);
                    $tag_decomposition['attrib'][$name] = '';
                    break;
                case 6:
                    if (!isset($tag_decomposition['name'])) {
                        $tag_decomposition['name'] = '';
                    }
                    if (13 == $previous_mode || 19 == $previous_mode) {
                        $tag_decomposition['layout'][] = array(7, $value);
                    }
                    $tag_decomposition['str'] .= ']';
                    $tag_decomposition['layout'][] = array(0, ']');
                    $result[++$token_key] = $tag_decomposition;
                    $tag_decomposition = array();
                    break;
                case 7:
                    $tag_decomposition['name'] = strtolower($token[1]);
                    $tag_decomposition['str'] .= $token[1];
                    $tag_decomposition['layout'][] = array(2, $token[1]);
                    break;
                case 8:
                    $tag_decomposition['str'] .= '=';
                    $tag_decomposition['layout'][] = array(3, '=');
                    break;
                case 9:
                    $tag_decomposition['type'] = 'open/close';
                    $tag_decomposition['str'] .= '/';
                    $tag_decomposition['layout'][] = array(1, '/');
                    break;
                case 10:
                    $tag_decomposition['str'] .= $token[1];
                    $tag_decomposition['layout'][] = array(4, $token[1]);
                    break;
                case 11:
                    $tag_decomposition['str'] .= '"';
                    $tag_decomposition['layout'][] = array(5, '"');
                    break;
                case 12:
                    $tag_decomposition['str'] .= "'";
                    $tag_decomposition['layout'][] = array(5, "'");
                    break;
                case 13:
                    $tag_decomposition['attrib'][$name] = $token[1];
                    $value = $token[1];
                    $tag_decomposition['str'] .= $token[1];
                    break;
                case 14:
                    $tag_decomposition['str'] .= $token[1];
                    $tag_decomposition['layout'][] = array(4, $token[1]);
                    break;
                case 15:
                    $name = strtolower($token[1]);
                    $tag_decomposition['str'] .= $token[1];
                    $tag_decomposition['layout'][] = array(6, $token[1]);
                    $tag_decomposition['attrib'][$name] = '';
                    break;
                case 16:
                    $tag_decomposition['str'] .= $token[1];
                    $tag_decomposition['attrib'][$name] .= $token[1];
                    @$value .= $token[1];
                    break;
                case 17:
                    $tag_decomposition['str'] .= $token[1];
                    $tag_decomposition['layout'][] = array(7, @$value);
                    @$value = '';
                    $tag_decomposition['layout'][] = array(5, $token[1]);
                    break;
                case 18:
                    $tag_decomposition['str'] .= $token[1];
                    $tag_decomposition['attrib'][$name] .= $token[1];
                    @$value .= $token[1];
                    break;
                case 19:
                    $tag_decomposition['str'] .= $token[1];
                    $tag_decomposition['attrib'][$name] .= $token[1];
                    @$value .= $token[1];
                    break;
                case 20:
                    $tag_decomposition['str'] .= $token[1];
                    if (13 == $previous_mode || 19 == $previous_mode) {
                        $tag_decomposition['layout'][] = array(7, $value);
                    }
                    $value = '';
                    $tag_decomposition['layout'][] = array(4, $token[1]);
                    break;
            }
        }
        if (count($tag_decomposition)) {
            if (-1 < $token_key && 'text' == $result[$token_key]['type']) {
                $result[$token_key]['str'] .= $tag_decomposition['str'];
            } else {
                $result[++$token_key] = array(
                    'type' => 'text',
                    'str' => $tag_decomposition['str']
                );
            }
        }
        $this->syntax = $result;
    }

    // Функция возвращает нормализует и возвращает дерево элементов
    function get_tree_of_elems() {
        /* Первый этап нормализации: превращаем $this -> syntax в правильную
          скобочную структуру */
        $structure = array();
        $structure_key = -1;
        $level = 0;
        $open_tags = array();
        foreach ($this->syntax as $syntax_key => $val) {
            unset($val['layout']);
            switch ($val['type']) {
                case 'text':
                    $type = (-1 < $structure_key) ? $structure[$structure_key]['type'] : false;
                    if ('text' == $type) {
                        $structure[$structure_key]['str'] .= $val['str'];
                    } else {
                        $structure[++$structure_key] = $val;
                        $structure[$structure_key]['level'] = $level;
                    }
                    break;
                case 'open/close':
                    foreach (array_reverse($open_tags, true) as $ult_key => $ultimate) {
                        $ends = $this->info_about_tags[$ultimate]['ends'];
                        if (in_array($val['name'], $ends)) {
                            $structure[++$structure_key] = array(
                                'type' => 'close',
                                'name' => $ultimate,
                                'str' => '',
                                'level' => --$level
                            );
                            unset($open_tags[$ult_key]);
                        } else {
                            break;
                        }
                    }
                    $structure[++$structure_key] = $val;
                    $structure[$structure_key]['level'] = $level;
                    break;
                case 'open':
                    foreach (array_reverse($open_tags, true) as $ult_key => $ultimate) {
                        $ends = $this->info_about_tags[$ultimate]['ends'];
                        if (in_array($val['name'], $ends)) {
                            $structure[++$structure_key] = array(
                                'type' => 'close',
                                'name' => $ultimate,
                                'str' => '',
                                'level' => --$level
                            );
                            unset($open_tags[$ult_key]);
                        } else {
                            break;
                        }
                    }
                    if ($this->info_about_tags[$val['name']]['is_close']) {
                        $val['type'] = 'open/close';
                        $structure[++$structure_key] = $val;
                        $structure[$structure_key]['level'] = $level;
                    } else {
                        $structure[++$structure_key] = $val;
                        $structure[$structure_key]['level'] = $level++;
                        $open_tags[] = $val['name'];
                    }
                    break;
                case 'close':
                    if (!count($open_tags)) {
                        $type = (-1 < $structure_key) ? $structure[$structure_key]['type'] : false;
                        if ('text' == $type) {
                            $structure[$structure_key]['str'] .= $val['str'];
                        } else {
                            $structure[++$structure_key] = array(
                                'type' => 'text',
                                'str' => $val['str'],
                                'level' => 0
                            );
                        }
                        break;
                    }
                    if (!$val['name']) {
                        end($open_tags);
                        list($ult_key, $ultimate) = each($open_tags);
                        $val['name'] = $ultimate;
                        $structure[++$structure_key] = $val;
                        $structure[$structure_key]['level'] = --$level;
                        unset($open_tags[$ult_key]);
                        break;
                    }
                    if (!in_array($val['name'], $open_tags)) {
                        $type = (-1 < $structure_key) ? $structure[$structure_key]['type'] : false;
                        if ('text' == $type) {
                            $structure[$structure_key]['str'] .= $val['str'];
                        } else {
                            $structure[++$structure_key] = array(
                                'type' => 'text',
                                'str' => $val['str'],
                                'level' => $level
                            );
                        }
                        break;
                    }
                    foreach (array_reverse($open_tags, true) as $ult_key => $ultimate) {
                        if ($ultimate != $val['name']) {
                            $structure[++$structure_key] = array(
                                'type' => 'close',
                                'name' => $ultimate,
                                'str' => '',
                                'level' => --$level
                            );
                            unset($open_tags[$ult_key]);
                        } else {
                            break;
                        }
                    }
                    $structure[++$structure_key] = $val;
                    $structure[$structure_key]['level'] = --$level;
                    unset($open_tags[$ult_key]);
            }
        }
        foreach (array_reverse($open_tags, true) as $ult_key => $ultimate) {
            $structure[++$structure_key] = array(
                'type' => 'close',
                'name' => $ultimate,
                'str' => '',
                'level' => --$level
            );
            unset($open_tags[$ult_key]);
        }
        /* Второй этап нормализации: Отслеживаем, имеют ли элементы
          неразрешенные подэлементы. Соответственно этому исправляем
          $structure. */
        $normalized = array();
        $normal_key = -1;
        $level = 0;
        $open_tags = array();
        $not_tags = array();
        foreach ($structure as $structure_key => $val) {
            switch ($val['type']) {
                case 'text':
                    $type = (-1 < $normal_key) ? $normalized[$normal_key]['type'] : false;
                    if ('text' == $type) {
                        $normalized[$normal_key]['str'] .= $val['str'];
                    } else {
                        $normalized[++$normal_key] = $val;
                        $normalized[$normal_key]['level'] = $level;
                    }
                    break;
                case 'open/close':
                    $is_open = count($open_tags);
                    end($open_tags);
                    $info = $this->info_about_tags[$val['name']];
                    $children = $is_open ? $this->info_about_tags[current($open_tags)]['children'] : array();
                    $not_normal = !$level && !$info['permission_top_level'] || $is_open && !in_array($val['name'], $children);
                    if ($not_normal) {
                        $type = (-1 < $normal_key) ? $normalized[$normal_key]['type'] : false;
                        if ('text' == $type) {
                            $normalized[$normal_key]['str'] .= $val['str'];
                        } else {
                            $normalized[++$normal_key] = array(
                                'type' => 'text',
                                'str' => $val['str'],
                                'level' => $level
                            );
                        }
                        break;
                    }
                    $normalized[++$normal_key] = $val;
                    $normalized[$normal_key]['level'] = $level;
                    break;
                case 'open':
                    $is_open = count($open_tags);
                    end($open_tags);
                    $info = $this->info_about_tags[$val['name']];
                    $children = $is_open ? $this->info_about_tags[current($open_tags)]['children'] : array();
                    $not_normal = !$level && !$info['permission_top_level'] || $is_open && !in_array($val['name'], $children);
                    if ($not_normal) {
                        $not_tags[$val['level']] = $val['name'];
                        $type = (-1 < $normal_key) ? $normalized[$normal_key]['type'] : false;
                        if ('text' == $type) {
                            $normalized[$normal_key]['str'] .= $val['str'];
                        } else {
                            $normalized[++$normal_key] = array(
                                'type' => 'text',
                                'str' => $val['str'],
                                'level' => $level
                            );
                        }
                        break;
                    }
                    $normalized[++$normal_key] = $val;
                    $normalized[$normal_key]['level'] = $level++;
                    $ult_key = count($open_tags);
                    $open_tags[$ult_key] = $val['name'];
                    break;
                case 'close':
                    $not_normal = isset($not_tags[$val['level']]) && $not_tags[$val['level']] = $val['name'];
                    if ($not_normal) {
                        unset($not_tags[$val['level']]);
                        $type = (-1 < $normal_key) ? $normalized[$normal_key]['type'] : false;
                        if ('text' == $type) {
                            $normalized[$normal_key]['str'] .= $val['str'];
                        } else {
                            $normalized[++$normal_key] = array(
                                'type' => 'text',
                                'str' => $val['str'],
                                'level' => $level
                            );
                        }
                        break;
                    }
                    $normalized[++$normal_key] = $val;
                    $normalized[$normal_key]['level'] = --$level;
                    $ult_key = count($open_tags) - 1;
                    unset($open_tags[$ult_key]);
                    break;
            }
        }
        // Формируем дерево элементов
        $result = array();
        $result_key = -1;
        $open_tags = array();
        $val_key = -1;
        foreach ($normalized as $normal_key => $val) {
            switch ($val['type']) {
                case 'text':
                    if (!$val['level']) {
                        $result[++$result_key] = array(
                            'type' => 'text',
                            'str' => $val['str']
                        );
                        break;
                    }
                    $open_tags[$val['level'] - 1]['val'][] = array(
                        'type' => 'text',
                        'str' => $val['str']
                    );
                    break;
                case 'open/close':
                    if (!$val['level']) {
                        $result[++$result_key] = array(
                            'type' => 'item',
                            'name' => $val['name'],
                            'attrib' => $val['attrib'],
                            'val' => array()
                        );
                        break;
                    }
                    $open_tags[$val['level'] - 1]['val'][] = array(
                        'type' => 'item',
                        'name' => $val['name'],
                        'attrib' => $val['attrib'],
                        'val' => array()
                    );
                    break;
                case 'open':
                    $open_tags[$val['level']] = array(
                        'type' => 'item',
                        'name' => $val['name'],
                        'attrib' => $val['attrib'],
                        'val' => array()
                    );
                    break;
                case 'close':
                    if (!$val['level']) {
                        $result[++$result_key] = $open_tags[0];
                        unset($open_tags[0]);
                        break;
                    }
                    $open_tags[$val['level'] - 1]['val'][] = $open_tags[$val['level']];
                    unset($open_tags[$val['level']]);
                    break;
            }
        }
        return $result;
    }

    /*
      Функция мнемонизирует HTML-код, вставляет в текст разрывы <br />, смайлики и
      "автоматические ссылки".
     */

    function insert_smiles($text) {
        $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8', false);
        $text = nl2br($text);
        //$text = str_replace('  ', '  ', $text);
        foreach ($this->mnemonics as $mnemonic => $value) {
            $text = str_replace($mnemonic, $value, $text);
        }
        return $text;
    }

    // Функция конвертит дерево элементов BBCode в HTML и возвращает результат
    function get_html($tree_of_elems = false) {
        if (!is_array($tree_of_elems)) {
            $tree_of_elems = $this->get_tree_of_elems();
        }
        $result = '';
        $lbr = 0;
        $rbr = 0;
        foreach ($tree_of_elems as $elem) {
            if ('text' == $elem['type']) {
                $elem['str'] = $this->insert_smiles($elem['str']);
                for ($i = 0; $i < $rbr; ++$i) {
                    $elem['str'] = ltrim($elem['str']);
                    if ('<br />' == substr($elem['str'], 0, 6)) {
                        $elem['str'] = substr_replace($elem['str'], '', 0, 6);
                    }
                }
                $result .= $elem['str'];
            } else {
                $lbr = $this->info_about_tags[$elem['name']]['lbr'];
                $rbr = $this->info_about_tags[$elem['name']]['rbr'];
                for ($i = 0; $i < $lbr; ++$i) {
                    $result = rtrim($result);
                    if ('<br />' == substr($result, -6)) {
                        $result = substr_replace($result, '', -6, 6);
                    }
                }
                $func_name = $this->info_about_tags[$elem['name']]['handler'];
                $result .= call_user_func(array(&$this, $func_name), $elem);
            }
        }
        return $result;
    }

    function nobb_2html($elem) {
        return $this->get_html($elem['val']);
    }

    function youtube_2html($elem) {
        return '<div class="youtube"><iframe src="//www.youtube.com/embed/' . text::toValue($elem['val'][0]['str']) . '" frameborder="0" allowfullscreen></iframe></div>';
    }

    function vk_video_2html($elem) {
        $allow_params = array('oid', 'id', 'hash', 'hd');
        $params = array();
        foreach ($elem['attrib'] AS $key => $value) {
            if (!in_array($key, $allow_params)) {
                continue;
            }
            $params[] = $key . '=' . $value;
        }
        return '<div class="vk_video"><iframe src="//vk.com/video_ext.php?' . text::toValue(join('&', $params)) . '"  frameborder="0"></iframe></div>';
    }

    function font_2html($elem) {

        if (!empty($elem['attrib']['name'])) {
            $name = $elem['attrib']['name'];
        } elseif (!empty($elem['attrib']['font'])) {
            $name = $elem['attrib']['font'];
        } else {
            $name = __('Arial');
        }
        return '<span style="font-family:' . text::toValue($name) . ';">' . $this->get_html($elem['val']) . '</span>';
    }

    function left_2html($elem) {
        return '<div align="left">' . $this->get_html($elem['val']) . '</div>';
    }

    function hr_2html($elem) {
        return '<div class="desc"></div>';
    }

    function center_2html($elem) {
        return '<div align="center">' . $this->get_html($elem['val']) . '</div>';
    }

    function right_2html($elem) {
        return '<div align="right">' . $this->get_html($elem['val']) . '</div>';
    }

    function indent_2html($elem) {
        return '<div style="margin:1em;">' . $this->get_html($elem['val']) . '</div>';
    }

    function u_2html($elem) {
        return '<span style="text-decoration:underline">' . $this->get_html($elem['val']) . '</span>';
    }

    function i_2html($elem) {
        return '<span style="font-style:italic">' . $this->get_html($elem['val']) . '</span>';
    }

    function b_2html($elem) {
        return '<span style="font-weight:bolder">' . $this->get_html($elem['val']) . '</span>';
    }

    function no_2html($elem) {
        return '<span style="text-decoration:line-through">' . $this->get_html($elem['val']) . '</span>';
    }

    function mark_2html($elem) {
        return '<span class="mark">' . $this->get_html($elem['val']) . '</span>';
    }

    function big_2html($elem) {
        return '<span style="font-size:larger">' . $this->get_html($elem['val']) . '</span>';
    }

    function small_2html($elem) {
        return '<span style="font-size:smaller">' . $this->get_html($elem['val']) . '</span>';
    }

    function vremja_2html($elem) {
        return misc::when($elem['val'][0]['str']);
        //return '<span style="font-size:smaller">' . $this->get_html($elem['val']) . '</span>';
    }

    function user_2html($elem) {

        //return '<pre>'.print_r($elem,1).'</pre>';

        $ank = new user((int) $elem['val'][0]['str']);
        return '<a href="/profile.view.php?id=' . $ank->id . '">' . $ank->nick() . '</a>';
    }

    function smile_2html($elem) {
        return smiles::bbcode($elem['val'][0]['str']);
        // return $arr[0];
    }

    function spoiler_2html($elem) {
        //return '<pre>'.print_r($elem,1).'</pre>';

        if (!empty($elem['attrib']['title'])) {
            $title = $elem['attrib']['title'];
        } elseif (!empty($elem['attrib']['spoiler'])) {
            $title = $elem['attrib']['spoiler'];
        } else {
            $title = __('Скрытый текст');
        }
        return '<div class="spoiler"><span class="spoiler_title">' . text::toValue($title) . '</span><div class="spoiler_content">' . $this->get_html($elem['val']) . '</div></div>';


        //return smiles::bbcode($elem['val'][0]['str']);
        // return $arr[0];
    }

    function url_2html($elem) {

        if (empty($elem['attrib']['url']) || empty($elem['val'][0]['str'])) {
            return false;
        }
        $aturl = $elem['attrib']['url'];
        $text = @$elem['val'][0]['str'];

        if (!$text) {
            return false;
        }

        $text = text::substr($text, 40);


        global $dcms;
        $aturl = str_replace(array("\n", "\r", "\t"), '', $aturl);

        if (preg_match('#^ *(javascript|data)#i', $aturl)) {
            return '!!! Javascript запрещен !!!';
        }

        if (preg_match('#://#', $aturl)) {
            // внешняя ссылка
            $url = '//' . $_SERVER ['HTTP_HOST'] . '/link.ext.php?url=' . urlencode($aturl);
            $new_window = @$dcms->browser_type == 'full' ? ' target="_blank"' : '';
            if ($parse_url = @parse_url($aturl)) {
                if (!empty($parse_url['host']) && @$dcms->subdomain_main && strpos($parse_url['host'], '.' . $dcms->subdomain_main) !== false
                ) {

                    if (@$dcms->subdomain_replace_url) {
                        // вырезаем поддомен из локальных ссылок
                        $aturl = str_replace($parse_url['host'], $dcms->subdomain_main, $aturl);
                        $url = 'https?://' . $_SERVER ['HTTP_HOST'] . '/link.ext.php?url=' . urlencode($aturl);
                    }

                    $new_window = '';
                }
            }

            return '<a' . $new_window . ' href="' . $url . '">' . text::toValue($text) . '</a>';
        } else {
            // внутренняя 
            $url = preg_replace('#^https?://' . preg_quote($_SERVER ['HTTP_HOST']) . '(/|$)#ui', '/', $aturl);
            return '<a href="' . text::toValue($url) . '">' . text::toValue($text) . '</a>';
        }
    }

    function quote_2html($elem) {
        if (!empty($elem['attrib']['quote'])) {
            if (preg_match('#^([0-9]+):([0-9]+):(.+)$#ui', $elem['attrib']['quote'], $log)) {
                $time = (int) $log [1];
                $ank = new user((int) $log[2]);
            } else {
                return '<div class="quote">' . $this->get_html($elem['val']) . '</div>';
            }
        } elseif (!empty($elem['attrib']['time']) && !empty($elem['attrib']['id_user'])) {
            $time = (int) $elem['attrib']['time'];
            $ank = new user((int) $elem['attrib']['id_user']);
        } else {
            return '<div class="quote">' . $this->get_html($elem['val']) . '</div>';
        }


        if ($time && $ank->id) {
            // Работает версия без времени
            //$title = "<span class='quote_title'><a href='/profile.view.php?id=$ank->id'>" . $ank->nick() . "</a> <small>(" . misc::timek($time) . ")</small></span>:";
            $title = "<span class='quote_title'><a href='/profile.view.php?id=$ank->id'>" . $ank->nick() . "</a></span>: ";
        } else {
            $title = '';
        }
        return '<div class="quote">' . $title . $this->get_html($elem['val']) . '</div>';
    }

    function img_2html($elem) {
        static $design = false;
        if ($design === false) {
            $design = new design ();
        }

        if (empty($elem['attrib']['file'])) {
            return false;
        }
        if (empty($elem['val'][0]['str'])) {
            return false;
        }
        $file = basename($elem['attrib']['file'], '.jpg');

        if (!@file_exists(H . '/sys/files/.bbcode/' . $file . '.jpg')) {
            return '<div class="error" style="color:gray">[' . __('тут была картинка') . ']</div>';
        }

        $file = new files_file(FILES . '/.bbcode', $file . '.jpg');

        if ($screen = $file->getScreen($design->img_max_width())) {
            return '<img class="bb_image" src="' . $screen . '" alt="' . text::toValue($elem['val'][0]['str']) . '" data-origin="' . text::toValue(@$elem['attrib']['origin']) . '" />';
        }
    }

    function php_2html($elem) {
        $code = "<?php\n" . trim(preg_replace('#^\<\?(php)?|\?\>$#i', '', @$elem['val'][0]['str'])) . "\n?>";
        $code = highlight_string($code, true);
        $code = preg_replace('#<code>(.*?)</code>#si', '<div class="phpcode">\\1</div>', $code);
        $code = preg_replace("#[\n\r\t]+#", '', $code);
        return $code;
    }

    function color_2html($elem) {

        // название тега является цветом (так как сама по себе данная функция вызваться не может, дополнительную проверку делать не будем.)
        if ($elem['name'] !== 'color') {
            return '<span style="color:' . $elem['name'] . '">' . $this->get_html($elem['val']) . '</span>';
        }

        // нет аттрибута с цветом
        if (empty($elem['attrib']['color'])) {
            return '[color="' . __('Цвет не указан') . '"]' . $this->get_html($elem['val']) . '[/color]';
        }

        // шестнадцатеричное указание цвета
        if (preg_match('/^#([0-9a-f]{6}|[0-9a-f]{3})$/ui', $elem['attrib']['color'])) {
            return '<span style="color:' . $elem['attrib']['color'] . '">' . $this->get_html($elem['val']) . '</span>';
        }

        // не корректный цвет
        return '[color="' . text::toValue($elem['attrib']['color']) . '"]' . $this->get_html($elem['val']) . '[/color]';
    }

    function gradient_2html($elem) {

        if (empty($elem['val'][0]['str'])) {
            return false;
        }
        $str = $elem['val'][0]['str'];
        $str_len = text::strlen($str);

        if (empty($elem['attrib']['from']) || empty($elem['attrib']['to'])) {
            return '[gradient="' . __('Не указан один из цветов') . '"]' . $this->get_html($elem['val']) . '[/gradient]';
        }
        $from = $elem['attrib']['from'];
        $to = $elem['attrib']['to'];

        if (!preg_match('/^#([0-9a-f]{6}|[0-9a-f]{3})$/ui', $from) || !preg_match('/^#([0-9a-f]{6}|[0-9a-f]{3})$/ui', $to)
        ) {
            return '[gradient="' . __('Один из цветов указан не корректно') . '"]' . $this->get_html($elem['val']) . '[/gradient]';
        }

        $from = str_replace('#', '', $from);
        $to = str_replace('#', '', $to);

        $from_col_len = text::strlen($from) / 3;
        $to_col_len = text::strlen($to) / 3;


        $from2 = array();
        $to2 = array();
        for ($i = 0; $i < 3; $i++) {
            $from2[] = hexdec(str_repeat(text::substr($from, $from_col_len, $from_col_len * $i, ''), 2 / $from_col_len));
            $to2[] = hexdec(str_repeat(text::substr($to, $to_col_len, $to_col_len * $i, ''), 2 / $to_col_len));
        }

        $colors = array();
        for ($i = 0; $i < 3; $i++) {
            $iteration_col = ($to2[$i] - $from2[$i]) / $str_len;
            for ($s = 0; $s < $str_len; $s++) {
                $hex = dechex(round($from2[$i] + $iteration_col * $s));
                //$colors[$s][$i] = $iteration_col;
                $colors[$s][$i] = text::strlen($hex) == 1 ? '0' . $hex : $hex;
            }
        }

        $return = '';
        for ($i = 0; $i < $str_len; $i++) {
            $return .= '<span style="color:#' . implode('', $colors[$i]) . '">' . text::substr($str, 1, $i, '') . '</span>';
        }
        return $return;
    }

}
