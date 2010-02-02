<?php
class Ga {

    const URL = 'http://www.google-analytics.com/__utm.gif';

    public $http_code;

    private $_options;

    function __construct($options) {
        $this->_options = array_merge(
            (is_array($options)) ? $options : array(),
            array(
                'version' => '4.4sh',
                'cookie' => array(
                    'name' => '__utmmobile',
                    'path' => '/',
                    'expiration' => 63072000
                )
            )
        );
    }

    function track() {
        $visitor_id = $this->_get_visitor();
        $headers = array(
            'Content-Type' => 'image/gif',
            'Content-Length' => 0,
            'Cache-Control' => 'private, no-cache, no-cache=Set-Cookie, proxy-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => 'Thu, 01 Jan 1970 01:00:00 GMT'
        );
        $params = array(
            'utmip' => (isset($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : '',
            'utmhn' => (isset($_SERVER['SERVER_NAME'])) ? $_SERVER['SERVER_NAME'] : '',
            'utmr' => (isset($_GET['utmr'])) ? $_GET['utmr'] : '',
            'utmp' => (isset($_GET['utmp'])) ? $_GET['utmp'] : '',
            'utmac' => (isset($_GET['utmac'])) ? $_GET['utmac'] : '',
            'utmwv' => $this->_options['version'],
            'utmn' => $this->_rand(),
            'utmvid' => $visitor_id,
            'utmcc' => '__utma%3D999.999.999.999.999.1%3B',
        );
        $url = self::URL . '?' . http_build_query($params);

        $this->_cookie($visitor_id);

        // Since Google respond with a 1x1 transparent gif image we can use that data
        // in order to serve our visitor an transparent image.
        $response = $this->_curl(
            $url,
            null,
            array('Accept-Language' => $_SERVER['HTTP_ACCEPT_LANGUAGE']),
            array(CURLOPT_USERAGENT => $_SERVER['HTTP_USER_AGENT'])
        );

        // Add the correct length of the response.
        $headers['Content-Length'] += strlen($response);

        if (isset($_GET['utmdebug'])) {
            $headers['X-GA-MOBILE-URL'] = $url;
        }

        foreach ($headers as $key => $val) {
            header($key . ': ' . $val);
        }

        echo $response;
    }

    function url($path = null) {
        $params = array(
            'utmac' => $this->_options['account'],
            'utmn' => $this->_rand(),
            'utmr' => (isset($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : '-',
            'utmp' => (isset($_SERVER['REQUEST_URI'])) ? $_SERVER['REQUEST_URI'] : null,
            'guid' => 'ON'
        );
        $uri = $path . '?';
        $uri .= http_build_query($params, '', '&amp;');

        return sprintf('<img src="%s" alt="" />', $uri);
    }

    private function _cookie($visitor_id) {
        setrawcookie(
            $this->_options['cookie']['name'],
            $visitor_id,
            time() + $this->_options['cookie']['expiration'],
            $this->_options['cookie']['path']
        );
    }

    private function _curl($url, $post_data = NULL, $headers = NULL, $options = NULL) {
        $ch = curl_init();
        $default_options = array(
            CURLOPT_URL => $url,
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true
        );

        if (is_array($headers)) {
            $default_options[CURLOPT_HTTPHEADER] = $headers;
        }

        if (is_array($options)) {
            foreach ($options as $key => $val) {
                $default_options[$key] = $val;
            }
        }

        if (isset($post_data)) {
            $default_options[CURLOPT_POST] = true;
            $default_options[CURLOPT_POSTFIELDS] = $post_data;
        }

        curl_setopt_array($ch, $default_options);

        $response = curl_exec($ch);
        $this->http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        return ($this->http_code == 200) ? $response : false;
    }

    private function _get_visitor() {
        $id = '';

        if (isset($_COOKIE[$this->_options['cookie']['name']])) {
            return $_COOKIE[$this->_options['cookie']['name']];
        } elseif (isset($_SERVER['HTTP_X_DCMGUID'])) {
            $id .= $_SERVER['HTTP_X_DCMGUID'] . $_GET['utmac'];
        } else {
            $id .= $_SERVER['HTTP_USER_AGENT'] . uniqid($this->_rand(), true);
        }

        $id = md5($id);

        return '0x' . substr($id, 0, 16);
    }

    private function _rand() {
        return rand(0, 0x7fffffff);
    }

}
?>