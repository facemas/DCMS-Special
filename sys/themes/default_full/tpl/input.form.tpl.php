<!-- Подключаем нужные файлы css -->
<link rel="stylesheet" href="<?= $path ?>/css/form.css" type="text/css" />
<link rel="stylesheet" href="<?= $path ?>/css/input.css" type="text/css" />

<div class="ui form">
    <?=
    '<form id="' . $id . '" ng-controller="FormCtrl" ng-submit="form.onSubmit($event)" data-url="' . $ajax_url . '" ng-disabled="form.sending"' .
    ($method ? ' method="' . $method . '"' : '') .
    ($action ? ' action="' . $action . '"' : '') .
    ($files ? ' enctype="multipart/form-data"' : '') .
    ($limit_files ? ' data-limit-files="' . $limit_files . '"' : '') .
    ($limit_size ? ' data-limit-size="' . $limit_size . '"' : '')
    . '>'
    ?>

    <?php
    foreach ($el AS $element) {

        if ($element['type'] == 'checkbox') {
            echo '<link rel="stylesheet" href="' . $path . '/css/checkbox.css" type="text/css" />';
        }
        if ($element['type'] == 'submit') {
            echo '<link rel="stylesheet" href="' . $path . '/css/button.css" type="text/css" />';
        }

        if ($element['type'] == 'html') {
            echo $element['block'];
        }

        switch ($element['type']) {
            case 'text':
                echo '<div class="form_text">' . $element['value'] . '</div>';
                break;
            case 'captcha':
                echo '<div class="ui input"><div class="field">';
                if ($element['title']) {
                    echo '<label>' . $element['title'] . ':</label>';
                }
                ?>

                <input type="hidden" name="captcha_session" value="<?= $element['session'] ?>"/>
                <img id="captcha" src="/captcha.php?captcha_session=<?= $element['session'] ?>&amp;<?= SID ?>"
                     alt="captcha"/><br/>
                <?= $lang->getString("Введите число с картинки") ?>:<br/>

                <input type="text" autocomplete="off" name="captcha" size="5" maxlength="5"/>
            </div></div><br />
            <?php
            break;
        case 'input_text':
            echo '<div class="field">';
            if ($element['title']) {
                echo '<label>' . $element['title'] . ':</label>';
            }
            echo '<input type="text"' .
            ($element['info']['name'] ? ' name="' . $element['info']['name'] . '"' : '') .
            ($element['info']['value'] ? ' value="' . text::toValue($element['info']['value']) . '"' : '') .
            ($element['info']['maxlength'] ? ' maxlength="' . intval($element['info']['maxlength']) . '"' : '') .
            ($element['info']['size'] ? ' size="' . intval($element['info']['size']) . '"' : '') .
            ($element['info']['disabled'] ? ' disabled="disabled"' : '') .
            ' />';
            echo '</div>';
            break;
        case 'hidden':
            echo '<input type="hidden"' .
            ($element['info']['name'] ? ' name="' . $element['info']['name'] . '"' : '') .
            ($element['info']['value'] ? ' value="' . text::toValue($element['info']['value']) . '"' : '') .
            ' />';
            break;
        case 'password':
            echo '<div class="field">';
            if ($element['title']) {
                echo '<label>' . $element['title'] . ':</label>';
            }
            echo '<input type="password"' .
            ($element['info']['name'] ? ' name="' . $element['info']['name'] . '"' : '') .
            ($element['info']['value'] ? ' value="' . text::toValue($element['info']['value']) . '"' : '') .
            ($element['info']['maxlength'] ? ' maxlength="' . intval($element['info']['maxlength']) . '"' : '') .
            ($element['info']['size'] ? ' size="' . intval($element['info']['size']) . '"' : '') .
            ($element['info']['disabled'] ? ' disabled="disabled"' : '') .
            ' /> ';
            echo '</div>';
            break;
        case 'textarea':
            echo '<div class="field">';
            if ($element['title']) {
                echo '<label>' . $element['title'] . ':</label>';
            }
            echo '<textarea id="' . passgen() . '" data-bbcode="" data-ctrl-enter-submit="' . ($element['submit_ctrl_enter'] ? 'true' : 'false') . '" ng-keyDown="form.keyDown($event)" ng-scope="true" ng-initial="" class="animate msd-elastic: \n;" ng-model="form.values.' . $element['info']['name'] . '" ' .
            ($element['info']['name'] ? ' name="' . $element['info']['name'] . '"' : '') .
            ($element['info']['disabled'] ? ' disabled="disabled"' : '') . '>' .
            ($element['info']['value'] ? text::toValue($element['info']['value']) : '') .
            '</textarea>';
            echo '</div>';
            break;
        case 'checkbox':
            echo '<div class="ui checkbox"><input type="checkbox"' .
            ($element['info']['name'] ? ' name="' . $element['info']['name'] . '"' : '') .
            ($element['info']['value'] ? ' value="' . text::toValue($element['info']['value']) . '"' : '') .
            ($element['info']['checked'] ? ' checked="checked"' : '') .
            ' /><label>' . ($element['info']['text'] ? ' ' . $element['info']['text'] : '') . '</label></div>';
            break;
        case 'submit':
            echo '<button type="submit" ' .
            ($element['info']['name'] ? ' name="' . $element['info']['name'] . '"' : '') .
            ($element['info']['class'] ? ' class="' . $element['info']['class'] . '"' : '') . '>
                ' . ($element['info']['icon'] ? '<i class="' . ($element['info']['icon']) . '"></i> ' : '') . '
                ' . ($element['info']['value'] ? text::toValue($element['info']['value']) : '') . '
                </button>';
            break;
        case 'file':
            echo '<div class="field">';
            if ($element['title']) {
                echo '<label>' . $element['title'] . ':</label>';
            }
            echo '<input type="file"' .
            ($element['info']['name'] ? ' name="' . $element['info']['name'] . '"' : '') .
            ($element['info']['multiple'] ? ' multiple="multiple"' : '') .
            ' />';
            echo '</div>';
            break;
        case 'select':

            echo '<div class="field">';
            if ($element['title']) {
                echo '<label>' . $element['title'] . ':</label>';
            }
            echo '<select name="' . $element['info']['name'] . '">';
            foreach ($element['info']['options'] AS $option) {
                if ($option['groupstart']) {
                    echo '<optgroup label="' . $option[0] . '">';
                } elseif ($option['groupend']) {
                    echo '</optgroup>';
                } else {
                    echo '<option' . ($option[2] ? ' selected="selected"' : '') . ' value="' . $option[0] . '"' . '>' . $option[1] . '</option>';
                }
            }
            echo '</select>';
            echo '</div>';


            break;
    }

    if ($element['br']) {
        echo '<br />';
    }
}
?>
<span class="msg animate" ng-show="form.msg" ng-bind="form.msg"></span>
<span class="err animate" ng-show="form.err" ng-bind="form.err"></span>

<div class="waiter animate ng-hide" ng-show="form.sending"><i class="fa fa-spinner fa-pulse fa-2x fa-fw"></i><span class="sr-only">Loading...</span></div>
<?php if ($refresh_url && !$ajax_url) { ?>
    <a class="refresh" title="<?= __('Обновить') ?>" href="<?= $refresh_url ?>"><i class="fa fa-refresh fa-2x fa-fw"></i></a>
        <?php
    }

    echo '</form>';
    ?>
</div>