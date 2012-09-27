<?php

interface PassportServiceInterface {
    public function find_user_by_user_id($user_id);
    public function find_user_by_user_name($user_name);
    public function find_users_by_mobile($mobile);
    public function update_password_by_user_id($user_id, $password);
    public function update_mobile_by_user_id($user_id, $mobile);
    public function update_auth_state_by_user_id($user_id, $auth_state=true);
    public function register($user_name, $password, $mobile);
    public function login($user_name, $password, $ip, $user_agent);
    public function login_from_old_mobile($sessionhash, $user_name, $password, $ip, $user_agent);
    public function logout($sessionhash);
    public function find_session_by_sessionhash($sessionhash);
    public function find_sessions_by_user_id($user_id);
    public function find_sessions_by_ip($ip);
    public function count_sessions($seconds=1800);
    public function count_users($seconds=1800);
    public function update_last_visit_time($sessionhash);
}

class PassportService implements PassportServiceInterface {

    private $http_status = 404;

    private $result = '';

    private $prefix_api_url = "http://a.liba.com/v2/";

    private $auth_user_name_and_password = "passportSite:PaR97Oboli23AWLSjaVa";

    public function __construct() {}

    public function get_http_status() {
        return $this->http_status;
    }

    /**
     * 根据用户ID获取单个用户信息
     * @return array('id' => '', 'name' => '', 'email' => '', 'authState' => '')
     */
    public function find_user_by_user_id($user_id) {
        if ($user_id <= 0)
            return array();
        $url = $this->prefix_api_url . "user/{$user_id}";
        $this->do_get($url);
        return ($this->http_status == 200) ? json_decode($this->result, TRUE) : array();
    }

    /**
     * 根据用户名获取单个用户信息
     * @return array('id' => '', 'name' => '', 'email' => '', 'authState' => '')
     */
    public function find_user_by_user_name($user_name) {
        if (!$user_name)
            return array();
        $url = $this->prefix_api_url . "user?name=" . urlencode($user_name);
        $this->do_get($url);
        return ($this->http_status == 200) ? json_decode($this->result, TRUE) : array();
    }

    /**
     * 根据用户手机获取多个用户信息（理论上最多5个）
     * @return array(0 => array('id' => '', 'name' => '', 'email' => '', 'authState' => '')
     *               1 => array('id' => '', 'name' => '', 'email' => '', 'authState' => ''))
     */
    public function find_users_by_mobile($mobile) {
        if (!$mobile)
            return array();
        $url = $this->prefix_api_url . "user?mobile={$mobile}";
        $this->do_get($url);
        return ($this->http_status == 200) ? json_decode($this->result, TRUE) : array();
    }

    /**
     * 根据用户ID修改密码
     * @return true => success, false => fail
     */
    public function update_password_by_user_id($user_id, $password) {
        if ($user_id <= 0 || !$password)
            return false;
        $url = $this->prefix_api_url . "user/{$user_id}";
        $data = array('password' => md5($password));
        $this->do_put($url, $data);
        return ($this->http_status == 204) ? true : false;
    }

    /**
     * 根据用户ID修改手机
     * @return true => success, false => fail
     */
    public function update_mobile_by_user_id($user_id, $mobile) {
        if ($user_id <= 0 || !$mobile)
            return false;
        $url = $this->prefix_api_url . "user/{$user_id}";
        $data = array('mobile' => $mobile);
        $this->do_put($url, $data);
        return ($this->http_status == 204) ? true : false;
    }

    /**
     * 根据用户ID认证手机
     * @return true => success, false => fail
     */
    public function update_auth_state_by_user_id($user_id, $auth_state=true) {
        if ($user_id <= 0)
            return false;
        $url = $this->prefix_api_url . "user/{$user_id}";
        $auth_state = $auth_state ? 'true' : 'false';
        $data = array('authState' => $auth_state);
        $this->do_put($url, $data);
        return ($this->http_status == 204) ? true : false;
    }

    /**
     * 注册用户
     * @return user_id (0表示失败)
     */
    public function register($user_name, $password, $mobile) {
        if (!$user_name || !$password || !$mobile)
            return array('code' => 0, 'message' => 'NEED_DATA');
        $url = $this->prefix_api_url . 'user';
        $data = array(
            'name' => $user_name,
            'password' => md5($password),
            'mobile' => $mobile,
        );
        $this->do_post($url, $data);
        if ($this->http_status == 201) {
            return array('code' => 1, 'message' => $this->result);
        } else {
            $this->result = json_decode($this->result, TRUE);
            return array('code' => 0, 'message' => $this->result['message']);
        }
    }

    /**
     * 登录
     * @return array
     * array(
     *      'code' => int, 'message' => string
     * )
     * code => 0 失败, code => 1 成功
     */
    public function login($user_name, $password, $ip, $user_agent) {
        if (!$user_name || !$password || !$ip || !$user_agent)
            return array('code' => 0, 'message' => 'NEED_DATA');
        $url = $this->prefix_api_url . 'session';
        $data = array(
            'username' => $user_name,
            'password' => md5($password),
            'ip' => $ip,
            'agent' => $user_agent,
        );
        $this->do_post($url, $data);

        if ($this->http_status == 201) {
            return array('code' => 1, 'message' => $this->result);
        } else {
            $this->result = json_decode($this->result, TRUE);
            return array('code' => 0, 'message' => $this->result['message']);
        }
    }

    /**
     * 老版手机登录
     * @return array
     * array(
     *      'code' => int, 'message' => string
     * )
     * code => 0 失败, code => 1 成功
     */
    public function login_from_old_mobile($sessionhash, $user_name, $password, $ip, $user_agent) {
        if (!$sessionhash || !$user_name || !$password || !$ip || !$user_agent)
            return false;
        $url = $this->prefix_api_url . "session/{$sessionhash}";
        $data = array(
            'username' => $user_name,
            'password' => md5($password),
            'ip' => $ip,
            'agent' => $user_agent,
        );
        $this->do_put($url, $data);

        return ($this->http_status == 201) ? true : false;
    }

    /**
     * 登出
     * @return true => success, false => fail
     */
    public function logout($sessionhash) {
        if (!$sessionhash)
            return false;
        $url = $this->prefix_api_url . "session/{$sessionhash}";
        $this->do_delete($url);
        return ($this->http_status == 204) ? true : false;
    }

    /**
     * 根据sessionhash获取session
     * @return array('id' => '', 'userId' => '', 'userName' => '',
     *               'ip' => '', 'userAgentCode' => '', 'lastVisitTime' => '')
     */
    public function find_session_by_sessionhash($sessionhash) {
        if (!$sessionhash)
            return array();
        $url = $this->prefix_api_url . "session/{$sessionhash}";
        $this->do_get($url);
        return ($this->http_status == 200) ? json_decode($this->result, TRUE) : array();
    }

    /**
     * 根据用户ID获取sessions
     * @return array(0 => array('id' => '', 'userId' => '', 'userName' => '',
     *                          'ip' => '', 'userAgentCode' => '', 'lastVisitTime' => ''))
     */
    public function find_sessions_by_user_id($user_id) {
        if ($user_id <= 0)
            return array();
        $url = $this->prefix_api_url . "session?userId={$user_id}";
        $this->do_get($url);
        return ($this->http_status == 200) ? json_decode($this->result, TRUE) : array();
    }

    /**
     * 根据用户IP获取sessions
     * @return array(0 => array('id' => '', 'userId' => '', 'userName' => '',
     *                          'ip' => '', 'userAgentCode' => '', 'lastVisitTime' => ''))
     */
    public function find_sessions_by_ip($ip) {
        if (!$ip)
            return array();
        $url = $this->prefix_api_url . "session?ip={$ip}";
        $this->do_get($url);
        return ($this->http_status == 200) ? json_decode($this->result, TRUE) : array();
    }

    /**
     * 统计session数
     * @return int
     */
    public function count_sessions($seconds=1800) {
        $url = $this->prefix_api_url . 'session';
        $data = array(
            '_service' => 'countSessions',
            'seconds' => $seconds,
        );
        $this->do_post($url, $data);
        return ($this->http_status == 200) ? intval(json_decode($this->result, TRUE)) : 0;
    }

    /**
     * 统计user数
     * @return int
     */
    public function count_users($seconds=1800) {
        $url = $this->prefix_api_url . 'session';
        $data = array(
            '_service' => 'countUsers',
            'seconds' => $seconds,
        );
        $this->do_post($url, $data);
        return ($this->http_status == 200) ? intval(json_decode($this->result, TRUE)) : 0;
    }

    /**
     * 更新最后访问时间
     * @return boolean
     */
    public function update_last_visit_time($sessionhash) {
        if (!$sessionhash)
            return false;
        $url = $this->prefix_api_url . "session/{$sessionhash}/lastVisitTime";
        $this->do_put($url);
        return ($this->http_status == 200) ? true : false;
    }

    private function do_post($url, $postfields=array()) {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
        curl_setopt($ch, CURLOPT_FAILONERROR, 0);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_COOKIEFILE, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                        'Content-type: application/x-www-form-urlencoded; charset=utf-8',
                        'Authorization: Basic ' . base64_encode($this->auth_user_name_and_password)));

        $this->result = curl_exec($ch);
        $this->http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);
    }

    private function do_get($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                        'Content-type: application/x-www-form-urlencoded; charset=utf-8',
                        'Authorization: Basic ' . base64_encode($this->auth_user_name_and_password)));

        $this->result = curl_exec($ch);
        $this->http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        curl_close($ch);
    }

    private function do_put($url, $postfields=array()) {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                        'Content-type: application/x-www-form-urlencoded; charset=utf-8',
                        'Authorization: Basic ' . base64_encode($this->auth_user_name_and_password)));

        $this->result = curl_exec($ch);
        $this->http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);
    }

    private function do_delete($url, $postfields=array()) {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                        'Content-type: application/x-www-form-urlencoded; charset=utf-8',
                        'Authorization: Basic ' . base64_encode($this->auth_user_name_and_password)));

        $this->result = curl_exec($ch);
        $this->http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);
    }

}

//$api = new PassportService();

//$result = $api->find_user_by_user_id(3511941);
//$result = $api->find_user_by_user_name('wbfwbf');
//$result = $api->find_users_by_mobile('13681916869');
//$result = $api->update_password_by_user_id(7184253, '123456');
//$result = $api->update_mobile_by_user_id(7184253, '13681916869');
//$result = $api->update_auth_state_by_user_id(7184253, true);
//$result = $api->register('wbfwbf3', '123456', '13681916869');
//$result = $api->login('wbfwbf1', '123456', '127.0.0.1', $_SERVER['HTTP_USER_AGENT']);
//$result = $api->login_from_old_mobile('362e5c0a8e2f816ec551107475c8f3a2', 'wbfwbf1', '123456', '127.0.0.1', 'mobile');
//$result = $api->logout('acc5b108dff0e14c29e85f28464c2ea1');
//$result = $api->find_session_by_sessionhash('0f5cb13a2f4d52837432403de6f398f6');
//$result = $api->find_sessions_by_user_id(3511941);
//$result = $api->find_sessions_by_ip('58.25.20.80');
//$result = $api->count_sessions();
//$result = $api->count_users();
//$result = $api->update_last_visit_time('756f6d54e78aef1ff97a265fa6e4acdf');

/*
echo $api->get_http_status();
echo "\n";
var_dump($result);
print_r($result);
exit;
*/