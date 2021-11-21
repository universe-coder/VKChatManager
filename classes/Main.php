<?php
require_once './classes/DB.php';
require_once './classes/Proxy.php';
require_once './config.php';
require_once './messages.php';
require_once './commands.php';

class Main {

    public $db;
    public $date;
    public $config;
    public $messages;
    public $proxy;
    public $commands;

    public function __construct() {
        $this->config = $GLOBALS['config'];
        $this->messages = $GLOBALS['messages'];
        $this->commands = $GLOBALS['commands'];
        $this->db = new DB($this->config->DB->host, $this->config->DB->username, $this->config->DB->password, $this->config->DB->dbname);
        $this->date = time();
    }

    public function curl_send (string $url, bool $ignore = true, string $proxy = ""): string {
        
        $myCurl = curl_init();
        
        curl_setopt_array($myCurl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => http_build_query(array())
        ));
        
        if ($proxy) {
            
            $curl_proxy = $proxy['protocol'] . "://" . $proxy['ip'] . ":" . $proxy['port'];
            curl_setopt($myCurl, CURLOPT_PROXY, $curl_proxy);
            
        }
        
        $response = curl_exec($myCurl);
        
        curl_close($myCurl);
        
        if (!$response || (isset(json_decode($response)->error) && json_decode($response)->error->error_code == 6)) {

            if ($proxy) {

                if (!$response)
                    $this->proxy->delete($proxy['id']);

                $proxy = $this->proxy->get_proxy();

                $proxy['is_active'] = $this->date;
                $this->proxy->update_proxy($proxy);

                $response = $this->curl_send($url, true, $proxy);

                if ($response) {

                    $proxy['is_active'] = 0;
                    $this->proxy->update_proxy($proxy);

                }

            }

        }
        
        return $response;
        
    }

    

}
?>