<?php

/**
 * Класс для работы с API vk.com
 * Class vk
 */
class vk
{
    protected
        $_app_id,
        $_app_secret,
        $_version = '5.72',
        $_access_token,
        $_permissions,
        $_email = '';

    public function __construct($app_id, $app_secret)
    {
        $this->_app_id = $app_id;
        $this->_app_secret = $app_secret;

        if (!$this->_app_id || !$this->_app_secret){
            throw new Exception(__('Для работы с API необходимо указать ID приложения и Защищенный ключ'));
        }
    }

    /**
     * Получение ссылки на авторизацию через vk.com
     * @param string $redirect_uri
     * @param string|array $scope запрашиваемые права доступа
     * @return url
     */
    public function getAuthorizationUri($redirect_uri, $scope = '')
    {
        return new url(
            'https://oauth.vk.com/authorize',
            array(
                'client_id' => $this->_app_id,
                'scope' => is_array($scope) ? join(',', $scope) : $scope,
                'response_type' => 'code',
                'v' => $this->_version,
                'redirect_uri' => $redirect_uri
            )
        );
    }

    /**
     * Получение токена доступа по коду авторизации
     * @param string $redirect_uri
     * @param string $code
     * @return string
     * @throws Exception
     */
    public function getAccessToken($redirect_uri = null, $code = null)
    {
        if (!$this->_access_token) {

            if (!$redirect_uri || !$code) {
                throw new Exception(__('Для получения access_token необходимо указать request_uri и code'));
            }

            $uri = new url(
                'https://oauth.vk.com/access_token',
                array(
                    'client_id' => $this->_app_id,
                    'client_secret' => $this->_app_secret,
                    'code' => $code,
                    'v' => $this->_version,
                    'redirect_uri' => $redirect_uri
                )
            );

            $http_client = new http_client($uri);
            $json_content = $http_client->getContent();

            if (false === ($data = json_decode($json_content, true))) {
                throw new Exception(__("Не удалось распарсить данные"));
            }

            if (empty($data['access_token'])) {
                throw new Exception(__("Не удалось получить access_token"));
            }

            $this->setAccessToken($data['access_token']);
            $this->_email = (isset($data['email']))? $data['email'] : '';
        }

        return $this->_access_token;
    }

    /**
     * Установка токена и получение прав доступа
     * @param string $token
     * @throws Exception
     */
    public function setAccessToken($token)
    {
        $this->_access_token = $token;
        try {
            $this->_permissions = $this->_apiRequest('account.getAppPermissions');
        } catch (Exception $e) {
            $this->_access_token = null;
            $this->_permissions = null;
            throw $e;
        }
    }

    /**
     * Получение данных текущего пользователя
     * @return mixed
     * @throws Exception
     */
    public function getCurrentUser()
    {
        $data = $this->_apiRequest('users.get', array(
            'fields' => 'sex,email,bdate,city,country,photo_50,photo_100,photo_200_orig,photo_200,photo_400_orig,photo_max,photo_max_orig,photo_id,online,online_mobile,domain,has_mobile,contacts,connections,site,education,universities,schools,can_post,can_see_all_posts,can_see_audio,can_write_private_message,status,last_seen,common_count,relation,relatives,counters,screen_name,maiden_name,timezone,occupation,activities,interests,music,movies,tv,books,games,about,quotes'
        ));
        return $data[0];
    }

    /**
     * Получение данных произвольных пользователей
     * @param array $ids
     * @param array $fields
     * @return array
     * @throws Exception
     */
    public function getUsers($ids, $fields)
    {
        return $this->_apiRequest('users.get', array(
            'user_ids' => is_array($ids) ? join(',', $ids) : $ids,
            'fields' => is_array($fields) ? join(',', $fields) : $fields
        ));
    }

    /**
     * @param string $method
     * @param array $params
     * @return mixed
     * @throws Exception
     */
    protected function _apiRequest($method, $params = array())
    {
        if (!$this->_access_token) {
            throw new Exception(__('access token не установлен'));
        }
        $params['access_token'] = $this->_access_token;
        $http_client = new http_client(new url('https://api.vk.com/method/' . $method, $params));
        $json_content = $http_client->getContent();
        if (false === ($data = json_decode($json_content, true))) {
            throw new Exception(__("Не удалось распарсить данные"));
        }

        if (!empty($data['error'])) {
            throw new Exception($data['error_description']);
        }
        return $data['response'];
    }

    /**
     * Возвращает email. Работает только непосредственно после получения токена.
     * @return string|null
     */
    public function getEmail()
    {
        return $this->_email == '' ? false : $this->_email;
    }
}