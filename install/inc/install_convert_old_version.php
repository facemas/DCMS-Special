<?php

class install_convert_old_version {

    var $users;
    var $rating;
    var $forum;
    var $forum_files;
    var $konts;
    var $mail;
    var $ban;
    var $sdt;
    var $obmen;
    var $news;

    function __construct() {
        db_connect();

        $this->users = &$_SESSION['convert_old_version']['users'];
        $this->rating = &$_SESSION['convert_old_version']['rating'];
        $this->forum = &$_SESSION['convert_old_version']['forum'];
        $this->forum_files = &$_SESSION['convert_old_version']['forum_files'];
        $this->konts = &$_SESSION['convert_old_version']['konts'];
        $this->mail = &$_SESSION['convert_old_version']['mail'];
        $this->ban = &$_SESSION['convert_old_version']['ban'];
        $this->adt = &$_SESSION['convert_old_version']['adt'];
        $this->obmen = &$_SESSION['convert_old_version']['obmen'];
        $this->news = &$_SESSION['convert_old_version']['news'];

        if (isset($_POST['users']) && empty($this->users)) {
            // получаем список групп пользователей
            $q = DB::me()->query("SELECT * FROM `" . $_SESSION['rename_prefix'] . "user_group`");
            $groups = array();
            while ($group = $q->fetch()) {
                $groups[$group['id']] = $group['level'];
            }
            // получаем список пользователей
            $q = DB::me()->query("SELECT * FROM `" . $_SESSION['rename_prefix'] . "user` ORDER BY `id`");
            //if (function_exists('set_time_limit'))
            //    set_time_limit(max(mysql_num_rows($q) / 2, 30));
            while ($user = $q->fetch()) {
                // пропускаем неактивированные учетки
                if ($user['activation'])
                    continue;

                $user['level'] = $groups[$user['group_access']];
                // уровень доступа пользователя будет ассоциирован с идентификатором группы пользователя
                switch ($user['level']) {
                    case 0: $user['group'] = 1;
                        break;
                    case 1: $user['group'] = 2;
                        break;
                    case 2: $user['group'] = 3;
                        break;
                    case 3: $user['group'] = 4;
                        break;
                    case 9: $user['group'] = 5;
                        break;
                    case 10: $user['group'] = 6;
                        break;
                    default:$user['group'] = (int) min(6, max(1, $user['level'] / 2));
                        break;
                }

                $db = DB::me();
                $res = $db->prepare("INSERT INTO `users` (`id`, `login`, `password`, `group`,
   `reg_date`, `last_visit`, `time_shift`,
  `icq_uin`, `email`, `realname`, `ank_d_r`, `ank_m_r`, `ank_g_r`, `sex`, `balls`, `description`)
  VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");

                $res->execute(Array($user['id'], $user['nick'], $user['pass'],
                    $user['group'], $user['date_reg'], $user['date_last'],
                    $user['set_timesdvig'], $user['ank_icq'], $user['ank_m_r'], $user['ank_g_r'], $user['pol'],
                    $user['balls'], $user['ank_o_sebe']));
            }
            $this->users = true;
        }

        if (isset($_POST['rating']) && empty($this->rating)) {
            $q = DB::me()->query("SELECT * FROM `" . $_SESSION['rename_prefix'] . "user_voice2` WHERE `rating` > '0'");

            // if (function_exists('set_time_limit'))
            //  set_time_limit(max(mysql_num_rows($q) / 2, 30));
            while ($voice = $q->fetch()) {
                DB::me()->query("INSERT INTO `reviews_users` (`id_user`, `id_ank`, `rating`, `time`)
  VALUES ('$voice[id_user]', '$voice[id_kont]', '$voice[rating]', '" . TIME . "')");
            }
            // echo mysql_error();
            $q2 = DB::me()->query("SELECT `id` FROM `users` ORDER BY `id`");
            while ($user = $q2->fetch()) {
                DB::me()->query("UPDATE `users` SET `rating` = (SELECT SUM(`rating`) FROM `reviews_users` WHERE `id_ank` = '$user[id]') WHERE `id` = '$user[id]'");
            }

            $this->rating = true;
        }

        if (isset($_POST['news']) && empty($this->news)) {
            $q = DB::me()->query("SELECT * FROM `" . $_SESSION['rename_prefix'] . "news` ORDER BY `id` ASC");

            //if (function_exists('set_time_limit'))
            //    set_time_limit(max(mysql_num_rows($q) / 2, 30));
            $res = DB::me()->prepare("INSERT INTO `news` (`id`, `id_user`, `time`, `title`, `text`)
  VALUES (?, '0', ?, ?, ?)");

            while ($news = $q->fetch()) {
                $res->execute(Array($news['id'], $news['time'], $news['title'], $news['msg']));
            }

            $q = DB::me()->query("SELECT * FROM `" . $_SESSION['rename_prefix'] . "news_komm` ORDER BY `id` ASC");

            // if (function_exists('set_time_limit'))
            //   set_time_limit(max(mysql_num_rows($q) / 2, 30));
            $res = DB::me()->prepare("INSERT INTO `news_comments` (`id`, `id_user`, `time`, `id_news`, `text`)
  VALUES (?, ?, ?, ?, ?)");
            while ($comm = $q->fetch()) {
                $res->execute(Array($comm['id'], $comm['id_user'], $comm['time'], $comm['id_news'], $comm['msg']));
            }

            $this->news = true;
        }

        if (isset($_POST['konts']) && empty($this->konts)) {
            $q = DB::me()->query("SELECT * FROM `" . $_SESSION['rename_prefix'] . "users_konts` WHERE `type` = 'favorite'");

            //  if (function_exists('set_time_limit'))
            //    set_time_limit(max(mysql_num_rows($q) / 2, 30));
            $res = DB::me()->prepare("INSERT INTO `friends` (`id_user`, `id_friend`, `confirm`, `time`)
  VALUES (?, ?, '1', ?)");
            while ($friend = $q->fetch()) {
                $res->execute(Array($friend['id_user'], $friend['id_kont'], $friend['time']));
            }
            // echo mysql_error();
            $this->konts = true;
        }

        if (isset($_POST['mail']) && empty($this->mail)) {
            $q = DB::me()->query("SELECT * FROM `" . $_SESSION['rename_prefix'] . "mail` ORDER BY `id`");

            //if (function_exists('set_time_limit'))
            //    set_time_limit(max(mysql_num_rows($q) / 2, 30));
            $res = DB::me()->prepare("INSERT INTO `mail` (`id_user`, `id_sender`, `time`, `is_read`, `mess`)
  VALUES (?, ?, ?, ?, ?)");
            while ($mail = $q->fetch()) {
                $res->execute(Array($mail['id_kont'], $mail['id_user'], $mail['time'], $mail['read'], $mail['msg']));
            }
            // echo mysql_error();
            $q2 = DB::me()->query("SELECT `id` FROM `users` ORDER BY `id`");
            while ($user = $q->fetch()) {
                DB::me()->query("UPDATE `users` SET `mail_new_count` = (SELECT COUNT(*) FROM `mail` WHERE `is_read` = '0' AND `id_user` = '$user[id]') WHERE `id` = '$user[id]'");
            }

            $this->mail = true;
        }

        if (isset($_POST['obmen']) && empty($this->obmen)) {
            $q = DB::me()->query("SELECT * FROM `" . $_SESSION['rename_prefix'] . "obmennik_dir` ORDER BY `id` ASC");
            while ($od = $q->fetch()) {
                $d_path = H . '/sys/files/.obmen/' . $od['dir'];
                if (!@is_dir($d_path) && !filesystem::mkdir($d_path))
                    continue;

                $dir_obj = new files($d_path);

                $dir_obj->runame = $od['name'];

                $dir_obj->group_show = 0;
                $dir_obj->group_write = $od['upload'] ? 1 : 2;
                $dir_obj->group_edit = 4;

                $q2 = DB::me()->query("SELECT * FROM `" . $_SESSION['rename_prefix'] . "obmennik_files` WHERE `id_dir` = '$od[id]'");

                // if (function_exists('set_time_limit'))
                //    set_time_limit(max(mysql_num_rows($q2) / 2, 30));

                while ($og = $q2->fetch()) {
                    $f_path = H . '/sys/obmen/files/' . $og['id'] . '.dat';

                    $dir = new files($d_path);

                    if ($files_ok = $dir->filesAdd(array($f_path => $og['name'] . '.' . $og['ras']))) {
                        $files_ok[$f_path]->id_user = $og['id_user'];
                        $files_ok[$f_path]->group_show = 0;
                        $files_ok[$f_path]->group_edit = 2;
                        $files_ok[$f_path]->description = $og['opis'];
                        unset($files_ok);
                    }
                }
            }

            filesystem::rmdir(H . '/sys/obmen');
            $this->obmen = true;
        }

        if (isset($_POST['ban']) && empty($this->ban)) {
            $q = DB::me()->query("SELECT * FROM `" . $_SESSION['rename_prefix'] . "ban` WHERE `time` > '" . TIME . "'");

            //  if (function_exists('set_time_limit'))
            //     set_time_limit(max(mysql_num_rows($q) / 2, 30));
            $res = DB::me()->prepare("INSERT INTO `ban` (`id_user`, `id_adm`, `time_start`, `time_end`, `comment`)
  VALUES (?, ?, ?, ?, ?)");
            while ($ban = $q->fetch()) {
                $res->execute(Array($ban['id_user'], $ban['id_ban'], TIME, $ban['time'], $ban['prich']));
            }
            // echo mysql_error();
            $this->ban = true;
        }

        if (isset($_POST['adt']) && empty($this->adt)) {
            $q = DB::me()->query("SELECT * FROM `" . $_SESSION['rename_prefix'] . "rekl` WHERE `time_last` > '" . TIME . "'");

            // if (function_exists('set_time_limit'))
            //     set_time_limit(max(mysql_num_rows($q) / 2, 30));
            while ($adt = $q->fetch()) {
                switch ($adt['sel']) {
                    case 1: $space = 'top';
                        $pm = 1;
                        $po = 1;
                        break;
                    case 2: $space = 'top';
                        $pm = 1;
                        $po = 1;
                        break;
                    case 3: $space = 'bottom';
                        $pm = 1;
                        $po = 0;
                        break;
                    case 4: $space = 'bottom';
                        $pm = 0;
                        $po = 1;
                        break;
                }

                $res = DB::me()->prepare("INSERT INTO `advertising` (`space`, `url_link`, `name`, `url_img`,
   `time_create`, `time_start`, `time_end`,
  `page_main`, `page_other`)
  VALUES (?,?,?, ?,?,?,?,?,?)");
                $res->execute(Array($space, $adt['link'], $adt['name'], $adt['img'], TIME, TIME, $adt['time_last'], $pm, $po));
            }
            // echo mysql_error();
            $this->adt = true;
        }

        if (isset($_POST['forum_files']) && empty($this->forum_files)) {
            $q = DB::me()->query("SELECT * FROM `" . $_SESSION['rename_prefix'] . "forum_files`");

            $forum_dir_obj = new files(FILES . '/.forum');
            // echo mysql_error();
            while ($files = $q->fetch()) {
                if (!is_file(H . '/sys/forum/files/' . $files['id'] . '.frf'))
                    continue;

                // if (function_exists('set_time_limit'))
                //    set_time_limit(max(mysql_num_rows($q) / 2, 30));

                $q2 = DB::me()->query("SELECT * FROM `forum_messages` WHERE `id` = '$files[id_post]' LIMIT 1");

                if (!$message = $q2->fetch())
                    continue;

                $theme_dir_path = FILES . '/.forum/' . $message['id_theme'];
                // если папки под файлы темы не существует и мы не можем ее создать, то проопускаем файл
                if (!is_dir($theme_dir_path) && !$ft = $forum_dir_obj->mkdir('Файлы темы #' . $message['id_theme'], $message['id_theme']))
                    continue;

                unset($ft);

                $theme_dir_obj = new files($theme_dir_path);
                $message_dir_path = FILES . '/.forum/' . $message['id_theme'] . '/' . $message['id'];
                // если папки под файлы сообщения не существует и мы не можем ее создать, то проопускаем файл
                if (!is_dir($message_dir_path) && !$fm = $theme_dir_obj->mkdir('Файлы сообщения #' . $message['id'], $message['id']))
                    continue;

                unset($fm);

                $message_dir_obj = new files($message_dir_path);
                $message_dir_obj->id_user = $message['id_user'];

                $file_path = H . '/sys/forum/files/' . $files['id'] . '.frf';
                $file_name = $files['name'] . '.' . $files['ras'];
                // если по каким то причинам файл добавить не удалось, то пропускаем
                if (!$added = $message_dir_obj->filesAdd(array($file_path => $file_name)))
                    continue;

                $added[$file_path]->id_user = $message['id_user'];

                unset($added, $theme_dir_obj, $message_dir_obj);
            }
            // установка права просмотра и скачивания файлов для гостей и выше
            // $forum_dir_obj -> setGroupShowRecurse(0);
            filesystem::rmdir(H . '/sys/forum');
            $this->forum_files = true; // типа файлы успешно конвертированы
        }

        if (isset($_POST['forum']) && empty($this->forum)) {
            $q = DB::me()->query("SELECT * FROM `" . $_SESSION['rename_prefix'] . "forum_f` ORDER BY `id`");
            // категории
            $res1 = DB::me()->prepare("INSERT INTO `forum_categories` (`id`, `position`, `name`, `description`, `group_show`)
  VALUES (?, ?,?,?,?)");
            $res2 = DB::me()->prepare("INSERT INTO `forum_topics` (`id`, `time_create`, `time_last`, `id_category`, `name`, `group_show`)
  VALUES (?,?,?,?,?,?)");
            $res3 = DB::me()->prepare("INSERT INTO `forum_messages` (`id`, `id_category`, `id_topic`, `id_theme`, `id_user`, `message`, `time`)
  VALUES (?,?,?,?,?,?,?)");
            $res4 = DB::me()->prepare("INSERT INTO `forum_themes` (`id`, `id_category`, `id_topic`, `name`,
   `top`, `id_autor`, `time_create`,
  `id_last`, `time_last`, `group_show`, `group_write`, `group_edit`)
  VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
            while ($forum_f = $q->fetch()) {
                $gsh = $forum_f['adm'] ? 2 : 0;
                $res1->execute(Array($forum_f['id'], $forum_f['pos'], $forum_f['name'], $forum_f['opis'], $gsh));
                // разделы
                $q2 = DB::me()->query("SELECT * FROM `" . $_SESSION['rename_prefix'] . "forum_r` WHERE `id_forum` = '$forum_f[id]' ORDER BY `id`");
                // echo mysql_error();
                while ($forum_r = $q2->fetch()) {
                    $res2->execute(Array($forum_r['id'], $forum_r['time'], $forum_r['time'], $forum_f['id'], $forum_r['name'], $gsh));
                    // темы
                    $q3 = DB::me()->query("SELECT * FROM `" . $_SESSION['rename_prefix'] . "forum_t` WHERE `id_razdel` = '$forum_r[id]' ORDER BY `id`");
                    while ($forum_t = $q3->fetch()) {
                        $gwr = $forum_t['close'] ? 2 : 1;
                        $ged = 2;
                        // сообщения
                        $q4 = DB::me()->query("SELECT * FROM `" . $_SESSION['rename_prefix'] . "forum_p` WHERE `id_them` = '$forum_t[id]' ORDER BY `id`");
                        // if (function_exists('set_time_limit'))
                        //    set_time_limit(max(mysql_num_rows($q4), 30));
                        while ($forum_p = $q4->fetch()) {
                            $forum_t['id_last'] = $forum_p['id_user'];
                            $forum_t['time_last'] = $forum_p['time'];
                            $res3->execute(Array($forum_p['id'], $forum_f['id'], $forum_r['id'], $forum_p['id_user'], $forum_p['msg'], $forum_p['time']));
                        }


                        $res4->execute(Array($forum_t['id'], $forum_f['id'], $forum_r['id'], $forum_t['name'],
                            $forum_t['up'], $forum_t['id_user'], $forum_t['time_create'], $forum_t['id_last'],
                            $forum_t['time_last'], $gsh, $gwr, $ged));
                    }
                }
            }
            $this->forum = true;
        }
    }

    function actions() {
        return !empty($this->users);
    }

    function form() {
        $dis_users = empty($this->users) ? " disabled='disabled'" : '';

        echo '<img src="/install/' . (empty($this->users) ? 'wait' : 'ok') . '.png" alt="" />';
        echo "<input type='submit' name='users' value='" . __('Пользователи') . "' /><br />";

        echo '<img src="/install/' . (empty($this->rating) ? 'wait' : 'ok') . '.png" alt="" />';
        echo "<input type='submit'$dis_users name='rating' value='" . __('Рейтинги') . "' /><br />";

        echo '<img src="/install/' . (empty($this->forum) ? 'wait' : 'ok') . '.png" alt="" />';
        echo "<input type='submit'$dis_users name='forum' value='" . __('Форум') . "' /><br />";

        $dis_forum = empty($this->forum) ? " disabled='disabled'" : '';
        echo '<img src="/install/' . (empty($this->forum_files) ? 'wait' : 'ok') . '.png" alt="" />';
        echo "<input type='submit'$dis_forum name='forum_files' value='" . __('Файлы форума') . "' /><br />";

        echo '<img src="/install/' . (empty($this->obmen) ? 'wait' : 'ok') . '.png" alt="" />';
        echo "<input type='submit'$dis_users name='obmen' value='" . __('Файлобменник') . "' /><br />";

        echo '<img src="/install/' . (empty($this->konts) ? 'wait' : 'ok') . '.png" alt="" />';
        echo "<input type='submit'$dis_users name='konts' value='" . __('Друзья') . "' /><br />";
        echo '<img src="/install/' . (empty($this->mail) ? 'wait' : 'ok') . '.png" alt="" />';
        echo "<input type='submit'$dis_users name='mail' value='" . __('Почта') . "' /><br />";
        echo '<img src="/install/' . (empty($this->ban) ? 'wait' : 'ok') . '.png" alt="" />';
        echo "<input type='submit'$dis_users name='ban' value='Бан' /><br />";

        echo '<img src="/install/' . (empty($this->adt) ? 'wait' : 'ok') . '.png" alt="" />';
        echo "<input type='submit' name='adt' value='" . __('Реклама и баннеры') . "' /><br />";

        echo '<img src="/install/' . (empty($this->news) ? 'wait' : 'ok') . '.png" alt="" />';
        echo "<input type='submit'$dis_users name='news' value='" . __('Новости') . "' />";

        return !empty($this->users);
    }

}

?>