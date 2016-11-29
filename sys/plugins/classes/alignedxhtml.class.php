<?php

/**
 * Форматирование HTML кода
 */
class alignedxhtml {

    public $SPACER;
    public $OFFSET;
    public $SKIPTAGS;

    function parse($xhtml) {
        if (is_null($this->SPACER)) {
            $this->SPACER = "  ";
        }
        if (is_null($this->OFFSET)) {
            $this->OFFSET = 0;
        }
        if (is_null($this->SKIPTAGS)) {
            $this->SKIPTAGS = array('a', 'span', 'img', 'sup', 'sub');
        }
        /*
          Теги <textarea>, <pre> и <script> - особенные, и с ними
          придется попотеть.
          Нужно защитить содержимое этих тегов от вмешательства
          при выравнивании: убрать на время, во-первых, переводы строки,
          во-вторых, HTML-теги, которые могут встретиться внутри
          строковых переменных в скриптах
          (точнее, не сами теги, а открывающие и закрывающие скобки).
         */
        $xhtml = str_replace(array("\x01", "\x02", "\x03"), '', $xhtml);
        $xhtml = preg_replace_callback(
                '/
            (<(textarea|script|pre)(?:[^>"\']*|"[^"]*"|\'[^\']*\')*>)
            (.*?)
            (<\/\2>)
            /six',
                // модификатор 's' не забываем: точка тут должна совпадать 
                // и с символом новой строки; модификатор 'x' позволяет добавлять в шаблон
                // необрабатываемые пробелы и переводы строки, чтобы он лучше читался
                create_function(
                        '$matches', '$tagbody = $matches[3];
                $tagbody = str_replace("\n", "\x01", $tagbody);
                $tagbody = str_replace("<", "\x02", $tagbody);
                $tagbody = str_replace(">", "\x03", $tagbody);
                return $matches[1] . $tagbody . $matches[4];'
                ), $xhtml);

        // регулярное выражение для HTML-тега 
        // (модификатор s не нужен, т.к. точки в выражении нет)
        $tagpattern = '/<(\/?)(\w+)(?:[^>"\']*|"[^"]*"|\'[^\']*\')*>/';

        // убираем переводы строки внутри тегов (заменяем на пробелы)
        $xhtml = preg_replace_callback(
                $tagpattern, create_function(
                        '$matches', 'return str_replace("\n", " ", $matches[0]);'
                ), $xhtml);

        // теперь обрабатыавем XHTML-код по одной строке 
        // (PHP это не умеет, поэтому пришлось вручную)
        $start = 0;
        $final_xhtml = '';
        do {
            $end = strpos($xhtml, "\n", $start);
            $line = ($end !== FALSE) ? substr($xhtml, $start, $end - ($start - 1)) : substr($xhtml, $start);
            $line = ltrim($line); // убираем ведущие пробелы, чтоб не мешали выравнивать
            $final_xhtml .= str_repeat($this->SPACER, $this->OFFSET) .
                    preg_replace_callback(
                            $tagpattern, array($this, 'alignXHTMLtags'), $line);
            $start = $end + 1;
        } while ($end !== FALSE);

        // убираем пустые строки
        $final_xhtml = preg_replace('/\n\s*(?=\n)/m', '', $final_xhtml);

        // возвращаем обратно содержимое <textarea>, <pre> и <script>
        $final_xhtml = str_replace("\x01", "\n", $final_xhtml);
        $final_xhtml = str_replace("\x02", "<", $final_xhtml);
        $final_xhtml = str_replace("\x03", ">", $final_xhtml);

        return $final_xhtml;
    }

    function alignXHTMLtags($matches) {
        $tag = $matches[0];
        $tagname = $matches[2];
        if (in_array($tagname, $this->SKIPTAGS))
            return $tag;
        $opening = FALSE;
        if ($matches[1]) {
            $this->OFFSET -= 1;
        } // тег является закрывающим
        elseif (substr($tag, -2, 1) == '/') {
            ;
        } // тег является одиночным
        else {
            $opening = TRUE;
        } // если тег не является ни одиночным, ни закрывающим, значит, он открывающий
        if ($tagname == 'textarea' OR $tagname == 'pre' OR $tagname == 'script') { // эти теги вообще не трогаем, просто перенесем их 
            // полностью (со всем содержимым) на новую строку 
            if ($opening) {
                $replacement = "\n" . $tag;
            } else
                $replacement = $tag . "\n";
        }
        else {
            $replacement = "\n"
                    . str_repeat($this->SPACER, $this->OFFSET) . $tag . "\n"
                    . str_repeat($this->SPACER, $this->OFFSET + 1);
        }
        if ($opening) {
            $this->OFFSET += 1;
        }
        return $replacement;
    }

}
