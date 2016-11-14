<?php

/**
 * Class api_stat
 */
class api_stat implements api_controller
{
    /**
     * @param $request_data
     * @throws ApiException
     */
    public function write($request_data)
    {
        $data = array(
            'host' => '',
            'version_dcms' => '',
            'version_php' => '',
            'version_mysql' => '',
            'translate_length' => 0,
            'users_count' => 0,
            'hosts_full' => 0,
            'hosts_mobile' => 0,
            'hosts_light' => 0
        );

        $data = array_merge($data, $request_data);

        if (empty($data['host']))
            throw new ApiException($request_data, __('Не указан параметр %s', 'host'));

        $ips = gethostbynamel($data['host']);

        if (!$ips)
            throw new ApiException($request_data, __('Не удалось получить список IP дресов для узла %s', $data['host']));

        if (!in_array(long2ip(dcms::getInstance()->ip_long), $ips))
            throw new ApiException($request_data, __('IP адрес запроса не соответствует IP адресу хоста'));

        $res = db::me()->prepare('SELECT COUNT(*) FROM `statistic` WHERE `host` = :h AND `time` > :t LIMIT 1');
        $res->execute(array(':h' => $data['host'], ':t' => DAY_TIME));
        if ($res->fetchColumn())
            throw new ApiException($request_data, __('Данные о статистике за cегодня уже имеются'));

        $res = db::me()->prepare("INSERT INTO `statistic`
        (`time`, `host`, `version_dcms`, `version_php`, `version_mysql`,  `translate_length`, `users_count`, `hosts_full`, `hosts_mobile`, `hosts_light`)
        VALUES (:t, :h, :v_dcms, :v_php, :v_mysql, :tr_len, :us_count, :hosts_full, :hosts_mobile, :hosts_light)");

        $res->execute(array(
            ':t' => TIME,
            ':h' => $data['host'],
            ':v_dcms' => $data['version_dcms'],
            ':v_php' => $data['version_php'],
            ':v_mysql' => $data['version_mysql'],
            ':tr_len' => $data['translate_length'],
            ':us_count' => $data['users_count'],
            ':hosts_full' => $data['hosts_full'],
            ':hosts_mobile' => $data['hosts_mobile'],
            ':hosts_light' => $data['hosts_light']
        ));

        if ($err = $res->errorInfo())
            throw new ApiException($request_data, $err);
    }
} 