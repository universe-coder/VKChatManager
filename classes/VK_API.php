<?php
require_once 'Main.php';

class VK extends Main {

    public $v = "5.92";
    public $apiurl = "https://api.vk.com/method/";
    public $random_id;

    public function __construct() {
        parent::__construct();
        $this->random_id = mt_rand(0000000000, 999999999999);
    }

    public function call (string $method, array $parms = []): object {
        
        $parms['v'] = $this->v;
        
        if (!isset($parms['access_token']))
            $parms['access_token'] = $this->config->group_token;
        
        $url = $this->apiurl . $method . '?' 
                . http_build_query($parms, '&');
        
        $result = $this->curl_send($url);

        return json_decode($result);
        
    }

    
    public function get_conversation_by_id (int $peer_id): object {
        
        $result = $this->call('messages.getConversationsById', 
                        [
                            'peer_ids' => $peer_id,
                            'group_id' => $this->config->group_id
                        ]);
        
        return $result->response;
        
    }

    public function get_conversation_members (int $peer_id): object {
        
        $result = $this->call('messages.getConversationMembers', 
                        [
                            'peer_id' => $peer_id,
                            'fields' => 'screen_name',
                            'group_id' => $this->config->group_id,
                            'lang' => 'ru'
                        ]);
        
        return $result->response;
        
    }

    
    public function users_get (int $usr_id): object {
        
        if ($usr_id > 0) {
        
            $user_info = $this->call('users.get', 
                                [
                                    'user_ids'        => $usr_id,
                                    'lang'            => 'ru',
                                    'access_token'    => $this->config->access_token
                                ]);

            return $user_info->response;
            
        }
        
    }

    public function send_message (string $message, array $data, array $attachments = []): object|int {
        
        if ($message || $attachments) {
                    
            $parms = [
                'peer_id' => $data['chat_id'],
                'group_id' => $this->config->group_id,
                'message' => $message,
                'random_id' => $this->random_id,
                'lang' => 'ru'
            ];

            if ($attachments) {

                $parms['attachment'] = "";

                foreach ($attachments as $attachment) {
                    $parms['attachment'] .= $attachment['type'] . $attachment['owner_id'] . "_" . $attachment['media_id'] . ",";
                }

            }

            $result = $this->call('messages.send', $parms);

            return $result->response;
            
        }
        
    }

    public function search_conversation_messages (string $q, int $peer_id, int $offset = 0, int $count = 0): object {
        
        $result = $this->call('messages.search', 
                        [
                            'q'          => $q,
                            'peer_id'    => $peer_id,
                            'offset'     => $offset,
                            'count'      => $count,
                            'group_id'   => $this->config->group_id,
                            'lang'       => 'ru'
                        ]);
        
        return $result;
        
    }

    public function delete_message (int $message_id, int $spam=0, int $del_for_all=1): object {
        
        $result = $this->call('messages.delete', 
                        [
                            'message_ids' => $message_id,
                            'spam' => $spam,
                            'delete_for_all' => $del_for_all,
                            'group_id' => $this->config->group_id
                        ]);
        
        return $result;
        
    }

    public function removeChatUser (int $chat_local_id, int $user_id): object {

        $result = $this->call('messages.removeChatUser', 
                        [
                            'chat_id'    => $chat_local_id,
                            'member_id'  => $user_id,
                            'group_id'   => $this->config->group_id
                        ]);

        return $result;

    }
    
}
?>