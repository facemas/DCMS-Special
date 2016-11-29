<?php

/**
 * Class stat
 */
abstract class stat
{
    /**
     * Отправка статистики на сервер dcms.su
     */
    public static function send()
    {
        $data = array(
            'host' => dcms::getInstance()->subdomain_main,
            'version_dcms' => dcms::getInstance()->version,
            'version_php' => PHP_VERSION,
            'version_mysql' => db::me()->getAttribute(PDO::ATTR_SERVER_VERSION),
            'translate_length' => filesize(H . '/sys/languages/for_translate.lng'),
            'users_count' => db::me()->query('SELECT COUNT(*) FROM `users`')->fetchColumn()
        );

        $res = db::me()->query("SELECT * FROM `log_of_visits_for_days` ORDER BY `time_day` DESC LIMIT 1");
        if ($stat = $res->fetch()) {
            $data['hosts_full'] = $stat['hosts_full'];
            $data['hosts_mobile'] = $stat['hosts_mobile'];
            $data['hosts_light'] = $stat['hosts_light'];
        }

        $client = new http_client('http://dcms.su/sys/api.php');
        $requests = array(
            array(
                'module' => 'api_stat',
                'method' => 'write',
                'data' => $data
            )
        );

        $client->set_post('requests', json_encode($requests));
        $client->getHeaders();
    }
} 