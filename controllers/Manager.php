<?php
class Manager extends Controller {

    public function __construct(Routes $route) {
        parent::__construct($route);
    }

    public function kick (int $user_id): void {

        if (!in_array($user_id, $this->chat->admins->users) 
                && !in_array($user_id, $this->chat->admins->groups)) {

            if ($user_id > 0) {
                
                $user_info = $this->get_info_user($user_id);
                $profile_reply = "@id$user_id ($user_info->first_name $user_info->last_name)";
                
            }

            $this->kick_user(
                $this->chat->local_id, 
                $user_id, 
                true,
                ($user_id > 0) ? $profile_reply . $this->messages->kick : ''
            );

        }else {

            $this->vk->send_message($this->from_profile_reply . $this->messages->cant_kick_admin, $this->data_new_message);
            
        }

    }

    public function help (): void {

        $help_text = file_get_contents('./help.txt', true);

        if ($help_text) {

            $message = "";

            if ($this->data->object->action->type == "chat_invite_user")
                $user_info = $this->get_info_user($this->data->object->action->member_id);
            else
                $user_info = $this->get_info_user($this->from_id);


            $profile_reply = "@id" . $this->from_id . " (" . $user_info->first_name . " " . $user_info->last_name . ")";
            $message .= $profile_reply . ", " . $this->new_line;

            if ($this->data->object->conversation_message_id == 1 
                    || $this->data->object->action->type == "chat_invite_user")
                $message .= $this->messages->welcome_invite . $this->new_line;

            $this->vk->send_message($message . $help_text, $this->data_new_message);

        }

    }

    public function call_everyone (string $text = ""): void {

        $users = [];

        for ($i = 0; $i < 20; $i++) {
            
            $count_users_chat = count($this->chat->members->profiles);
            
            if ($count_users_chat > 0) {
            
                $rand_usr = mt_rand(0, ($count_users_chat - 1));

                $users[] = $this->chat->members->profiles[$rand_usr];

                unset($this->chat->members->profiles[$rand_usr]);
                sort($this->chat->members->profiles);
                
            }else {
                
                break;
                
            }
            
        }

        $message_text = "";

        foreach ($users as $user)
            $message_text .= "@id" . $user->id . " (" . $user->first_name . "), ";

        $message_text .= (!empty($text)) ? $text : $this->messages->call_everyone;

        $this->vk->send_message($message_text, $this->data_new_message);

    }

    public function bots (int $status): void {

        if ($this->check_security()) {

            $this->db->update(
                'control_chats',
                'bots=:bots',
                'local_chat_id=:local_chat_id',
                [
                    "bots" => $status, 
                    "local_chat_id" => $this->chat->local_id
                ], 1
            );
            
            $message = ($status) ? $this->messages->bots_enabled : $this->messages->bots_disabled;
            $this->vk->send_message($message, $this->data_new_message);

        }

    }

    public function invites (int $invites): void {

        if ($this->check_security()) {
            
            $this->db->update(
                'control_chats',
                'invites=:invites',
                'local_chat_id=:local_chat_id',
                [
                    "invites" => $invites, 
                    "local_chat_id" => $this->chat->local_id
                ], 1
            );
            
            $message = ($invites) ? $this->messages->invites_enabled : $this->messages->invites_disabled;

            $this->vk->send_message($message, $this->data_new_message);

        }

    }

    public function links (int $links): void {

        if ($this->check_security()) {
            
            $this->db->update(
                'control_chats',
                'links=:links',
                'local_chat_id=:local_chat_id',
                [
                    "links" => $links, 
                    "local_chat_id" => $this->chat->local_id
                ], 1
            );
            
            $message = ($links) ? $this->messages->links_enabled : $this->messages->links_disabled;
            $this->vk->send_message($message, $this->data_new_message);

        }

    }

    public function nude (int $nude): void {

        if ($this->check_security()) {

            $message = $this->messages->error_nude_enable;

            if ($this->config->algorithmia) {

                $this->db->update(
                    'control_chats',
                    'nude_security=:nude_security',
                    'local_chat_id=:local_chat_id',
                    [
                        "nude_security" => $nude, 
                        "local_chat_id" => $this->chat->local_id
                    ], 1
                );
                
                $message = ($nude) ? $this->messages->nude_enabled : $this->messages->nude_disabled;
    
            }

            $this->vk->send_message($message, $this->data_new_message);

        }

    }

    public function security (int $security): void {

        if (isset($this->chat->local_id) && $this->check_messages->access->is_admin) {
    
            if (!$this->check_messages->access->control) {
    
                $this->db->insert(
                    'control_chats',
                    '(NULL, :local_chat_id, :security, 0, 0, 0, 0, :added_usr_id, :date_add)',
                    [
                        "local_chat_id" => $this->chat->local_id, 
                        "security"      => $security, 
                        "added_usr_id"  => $this->from_id, 
                        "date_add"      => $this->date
                    ]
                );
    
            }else {
    
                $this->db->update(
                    'control_chats',
                    'security=:security',
                    'local_chat_id=:local_chat_id',
                    [
                        "security"      => $security, 
                        "local_chat_id" => $this->chat->local_id
                    ], 1
                );
    
            }
    
    
            $message = ($security) ? $this->messages->security_enabled : $this->messages->security_disabled;
        
        }elseif (!$this->check_messages->access->is_admin) {
        
            $message = $this->messages->manage_secure_can_admin;
        
        }else {
            
            $message = $this->messages->bot_not_admin;
            
        }
        
        $this->vk->send_message($message, $this->data_new_message);

    }

    public function show_status_security () {

        $sim_spam_security = ($this->chat->security['security']) ? $this->checkmark : $this->error_sim;
        $sim_link_security = ($this->chat->security['links']) ? $this->checkmark : $this->error_sim;
        $sim_invite_security = ($this->chat->security['invites']) ? $this->checkmark : $this->error_sim;
        $sim_invite_bots = ($this->chat->security['bots']) ? $this->checkmark : $this->error_sim;
        $sim_nude_security = ($this->chat->security['nude_security']) ? $this->checkmark : $this->error_sim;

        $message = "-----Статус защиты-----" . $this->new_line 
                . "Основная спам-защита: " . $sim_spam_security . " " . $this->new_line 
                . "Защита от ссылок: " . $sim_link_security . " " . $this->new_line 
                . "Защита от ивайт-ссылок в другие беседы: " . $sim_invite_security . " " . $this->new_line 
                . "Защита от ботов: " . $sim_invite_bots . " " . $this->new_line 
                . "Защиты от контента для взрослых: " . $sim_nude_security;

        $this->vk->send_message($message, $this->data_new_message);

    }

    public function show_not_active () {

        $unactive_users = 0;

        foreach ($this->chat->members->profiles as $member_info) {

            $is_kick = false;

            $get_last_activity = $this->db->select(
                    'last_activity', '*',
                    'user_id=:user_id AND chat_id=:chat_id',
                    [
                        "user_id" => $member_info->id, 
                        "chat_id" => $this->chat->local_id
                    ], 1
                )[0];

            if (isset($get_last_activity)) {

                if ($this->date_last_actv > $get_last_activity['date_last_acivity'])
                    $is_kick = true;

            }else {

                $is_kick = true;

            }

            if ($is_kick) {

                $unactive_users++;

            }

        }

        $message = $this->from_profile_reply . ", " . $this->declime_unactive_users($unactive_users);

        $this->vk->send_message($message, $this->data_new_message);

    }

    public function show_last_active () {

        $users_last_activity = $this->db->select(
            'last_activity', '*', 
            'chat_id=:chat_id', 
            ["chat_id" => $this->chat->local_id], 15, 0, 
            'date_last_acivity DESC'
        );

        $message_text = "";

        foreach ($users_last_activity as $user_last_activity) {
            
            $user_info = $this->get_info_user($user_last_activity['user_id']);
            
            if (isset($user_info)) {
                
                $time = $this->date - $user_last_activity['date_last_acivity'];

                if ($time > 60)
                    $time = $this->parse_timer($time);
                else
                    $time = "только что";

                $message_text .= $user_info->first_name . " - " . $time . " " . $this->new_line;
                
            }
            
        }

        $this->vk->send_message($message_text, $this->data_new_message);

    }

    public function remove_admin ($user_id) {

        $user_id = $this->screen_name_parse($user_id);

        if (isset($user_id)) {
            
            $user_id = $this->search_user_in_chat($user_id);

            if ($user_id != 0) {
                
                $check_user_in_admin = $this->db->select(
                    'chat_admins', '*', 
                    'chat_id=:chat_id AND user_id=:user_id', 
                    [
                        "chat_id" => $this->chat->local_id, 
                        "user_id" => $user_id
                    ], 1
                )[0];
                
                if (isset($check_user_in_admin)) {
                    
                    $this->db->delete(
                        'chat_admins',
                        'chat_id=:chat_id AND user_id=:user_id',
                        [
                            "chat_id" => $this->chat->local_id, 
                            "user_id" => $user_id
                        ], 1
                    );
                    
                    $message_text = $this->from_profile_reply . $this->messages->admin_removed;
                    
                }else {
                    
                    $message_text = $this->from_profile_reply . $this->messages->user_not_admin;
                    
                }
                
            }else {

                $message_text = $this->from_profile_reply . $this->messages->cannot_find_user;

            }
            
            $this->vk->send_message($message_text, $this->data_new_message);
            
        }

    }

    public function add_admin ($user_id) {

        $user_id = $this->screen_name_parse($user_id);

        if (isset($user_id)) {
            
            $user_id = $this->search_user_in_chat($user_id);

            if ($user_id != 0) {
                
                $check_user_admin = $this->db->select(
                    'chat_admins', '*',
                    'chat_id=:chat_id AND user_id=:user_id',
                    [
                        "chat_id" => $this->chat->local_id, 
                        "user_id" => $user_id
                    ], 1
                )[0];
                
                if (empty($check_user_admin)) {

                    $this->db->insert(
                        'chat_admins',
                        '(NULL, :user_id, :chat_id, :added_id, :date)',
                        [
                            "user_id"   => $user_id,
                            "chat_id"   => $this->chat->local_id,
                            "added_id"  => $this->from_id,
                            "date"      => $this->date
                        ]
                    );
                    
                    $message_text = $this->from_profile_reply . $this->messages->added_admin;
                    
                }else {
                    
                    $message_text = $this->from_profile_reply . $this->messages->already_admin;
                    
                }
                
            }else {

                $message_text = $this->from_profile_reply . $this->messages->cannot_find_user;

            }
            
            $this->vk->send_message($message_text, $this->data_new_message);
            
        }

    }

    public function show_admins () {

        $message = $this->from_profile_reply . $this->messages->list_admins . $this->new_line;

        foreach ($this->chat->admins->users as $admin)
            $message .= $this->add_adm_in_status($admin);

        $admins_db = $this->db->select(
            'chat_admins', '*', 
            'chat_id=:chat_id',
            ["chat_id" => $this->chat->local_id]
        );

        foreach ($admins_db as $admin)
            $message .= $this->add_adm_in_status($admin['user_id']);

        $this->vk->send_message($message, $this->data_new_message);

    }

    public function screen_name_parse (string $text): string {

        $screen_name = explode("[", $text, 2)[1];
        $screen_name = explode("|", $screen_name, 2)[0];
        $screen_name = str_replace("club", "", $screen_name);

        if (empty($screen_name))
            $screen_name = $text;

        return $screen_name;

    }

    public function kick_by_screen_name ($user_id) {

        $user_id = $this->screen_name_parse($user_id);

        if (isset($user_id)) {

            if (mb_stristr($user_id, "неактив") 
                    && mb_stripos($user_id, "неактив") === 0) {
                
                $date_check_start = $this->date - (86400 * 5);
                
                if ($this->date_added_chat > 0 && $date_check_start > $this->date_added_chat) {
                
                    $limited = 5;
                    $count_kicked = 0;

                    $count_by_user = explode(" ", $user_id, 2)[1];

                    if (isset($count_by_user)) {

                        if (intval($count_by_user) > 0)
                            $limited = $count_by_user;

                    }

                    if ($limited > 10)
                        $limited = 10;
                    
                    foreach ($this->chat->members->profiles as $member_info) {

                        if ($limited > 0) {
                            
                            $is_kick = false;
                            
                            $get_last_activity = $this->db->select(
                                'last_activity', '*',
                                'user_id=:user_id AND chat_id=:chat_id',
                                [
                                    "user_id" => $member_info->id, 
                                    "chat_id" => $this->chat->local_id
                                ], 1
                            )[0];

                            $is_kick = (isset($get_last_activity) && $this->date_last_actv > $get_last_activity['date_last_acivity']) || true;
                            
                            if ($is_kick) {
                                
                                $result = $this->kick_user($this->chat->local_id, $member_info->id, true, true);

                                if ($result->response == 1) {

                                    $count_kicked++;

                                    $limited--;
                                    
                                    $this->db->delete(
                                        'last_activity',
                                        'user_id=:user_id AND chat_id=:chat_id',
                                        [
                                            "user_id" => $member_info->id, 
                                            "chat_id" => $this->chat->local_id
                                        ], 1
                                    );

                                }
                                
                            }

                        }else {

                            break;

                        }

                    }

                    $message_text = $this->from_profile_reply . ", Было кикнуто: " . $this->declime_unactive_users($count_kicked);
                    $this->vk->send_message($message_text, $this->data_new_message);
                    
                }else {
                    
                    $message_text = $this->error_sim . " " . $this->from_profile_reply . ", Вы можете воспользоваться данной функцией через: " . $this->parse_timer(($this->date_added_chat - $date_check_start));
                    $this->vk->send_message($message_text, $this->data_new_message);
                    
                }
                
            }else {
                
                $id_usr_kick = $this->search_user_in_chat($user_id);

                if ($id_usr_kick != 0) {

                    if (!in_array($id_usr_kick, $this->chat->admins->users) 
                            && !in_array($id_usr_kick,  $this->chat->admins->groups)) {

                        if ($id_usr_kick > 0) {

                            $user_info = $this->get_info_user($id_usr_kick);

                            $profile_reply = "@id" . $id_usr_kick . " (" . $user_info->last_name . ")";
                            $message_text = $profile_reply . $this->messages->kick;
                            $this->vk->send_message($message_text, $this->data_new_message);

                        }

                        $this->kick_user($this->chat->local_id, $id_usr_kick, true);

                    }else {

                        $message_text = $this->from_profile_reply . $this->messages->cannot_kick_admin;
                        $this->vk->send_message($message_text, $this->data_new_message);
                        
                    }

                }else {

                    $message_text = $this->from_profile_reply . $this->messages->cannot_find_user;
                    $this->vk->send_message($message_text, $this->data_new_message);
                    
                }
                
            }
                
        }

    }

    public function vote_poll (): void {

        $check_current_polls = $this->db->select(
            'kick_polls', '*',
            'chat_id=:chat_id AND date_create>:date_create AND reresolved=0',
            [
                "chat_id" => $this->chat->local_id, 
                "date_create" => ($this->date - 600)
            ], 1
        )[0];
                    
        if (isset($check_current_polls)) {

            $poll_id = $check_current_polls['id'];
            $current_votes = $check_current_polls['current_votes'];
            $needed_votes = $check_current_polls['needed_votes'];
            $id_usr_kick = $check_current_polls['kick_usr_id'];
            $kick = false;

            $check_vote = $this->db->select(
                'kick_poll_votes', 'COUNT(*)',
                'poll_id=:poll_id AND author_id=:author_id',
                [
                    "poll_id" => $poll_id, 
                    "author_id" => $this->from_id
                ], 1
            )[0][0];

            if ($check_vote == 0) {

                $this->db->insert(
                    'kick_poll_votes',
                    '(NULL, :poll_id, :author_id, :date)',
                    [
                        "poll_id" => $poll_id, 
                        "author_id" => $this->from_id, 
                        "date" => $this->date
                    ]  
                );

                $current_votes++;

                $this->db->update(
                    'kick_polls',
                    'current_votes=:current_votes',
                    'id=:id',
                    [
                        "current_votes" => ($current_votes), 
                        "id" => $poll_id
                    ], 1
                );

                if ($current_votes == $needed_votes) {

                    if ($id_usr_kick > 0) {
                        
                        $user_info = $this->get_info_user($id_usr_kick);

                        $profile_reply = "@id$id_usr_kick ($user_info->first_name $user_info->last_name)";
                        $message_text = $profile_reply . $this->messages->vote_kicked;
                        
                    }

                    $kick = true;
                    
                    $this->db->update(
                        'kick_polls',
                        'reresolved=1',
                        'id=:id',
                        ['id' => $poll_id], 1
                    );
                    
                    $this->db->delete(
                        'kick_poll_votes',
                        'poll_id=:poll_id',
                        ["poll_id" => $poll_id]
                    );

                }else {

                    $message_text = $this->from_profile_reply . $this->messages->success_voted
                     . $this->declime_vote(($needed_votes - $current_votes));

                }


            }else {

                $message_text = $this->from_profile_reply . $this->messages->already_voted;

            }

            $this->vk->send_message($message_text, $this->data_new_message);

            if ($kick)
                $this->kick_user($this->chat->local_id, $id_usr_kick, true);

        }

    }

    public function create_voting (string $user_id): void {

        $user_id = $this->screen_name_parse($user_id);
                            
        if (isset($user_id)) {
            
            $date_create = 0;

            $id_usr_kick = $this->search_user_in_chat($user_id);

            if ($id_usr_kick) {
                
                if (!in_array($id_usr_kick, $this->chat->admins->users) 
                        && !in_array($id_usr_kick,  $this->chat->admins->groups)) {
                
                    $access_polls = 0;
                    
                    $check_current_polls = $this->db->select(
                        'kick_polls', 'COUNT(*)',
                        'chat_id=:chat_id AND date_create>:date_create AND reresolved=0',
                        [
                            "chat_id" => $this->chat->local_id, 
                            "date_create" => ($this->date - 600)
                        ], 1
                    )[0][0];
                    
                    if ($check_current_polls > 0) {
                        
                        $access_polls = 1;
                        
                    }else {
                        
                        $check_last_polls = $this->db->select(
                            'kick_polls', '*',
                            'chat_id=:chat_id AND date_create>:date_create', 
                            [
                                "chat_id" => $this->chat->local_id, 
                                "date_create" => ($this->date - 3600)
                            ], 
                            1, 0, 'id DESC'
                        )[0];
                        
                        if (isset($check_last_polls)) {
                            
                            $date_create = $check_last_polls['date_create'];
                            $access_polls = 2;
                            
                        }
                        
                    }
                    
                    

                    if ($access_polls === 0) {

                        $conversation_get_active = $this->vk->call('messages.getConversationsById', 
                                                            [
                                                                'peer_ids'  => $this->peer_id,
                                                                'group_id'  => $this->config->group_id,
                                                                'long'      => 'ru'
                                                            ]);

                        if (isset($conversation_get_active->response)) {

                            $needed_votes = count($conversation_get_active->response->items[0]->chat_settings->active_ids);

                            $this->db->insert(
                                'kick_polls',
                                '(NULL, :chat_id, :author_id, :kick_usr_id, :needed_votes, 0, 0, :date_create)',
                                [
                                    "chat_id" => $this->chat->local_id,
                                    "author_id" => $this->from_id,
                                    "kick_usr_id" => $id_usr_kick, 
                                    "needed_votes" => $needed_votes,
                                    "date_create" => $this->date
                                ]
                            );

                            $msg_screen_name_kick_usr = ($id_usr_kick > 0) ? 
                                ("id" . $id_usr_kick) : ("club" . str_replace("-", "", $id_usr_kick));

                            $message_text = $this->checkmark . $this->from_profile_reply . ", Голосование за исключение @$msg_screen_name_kick_usr (пользователя) активировано!" . $this->new_line
                                                                        . "Чтобы проголосовать за исключение, отправьте '+' в чат." . $this->new_line
                                                                        . "Голосование будет активно 10 минут!" . $this->new_line 
                                                                        . "Голосов до исключения: " . $this->declime_vote($needed_votes);

                        }

                    }elseif ($access_polls === 1) {

                        $message_text = $this->error_sim . $this->from_profile_reply . ", В беседе еще активно другое голосование!";

                    }elseif ($access_polls === 2) {
                        
                        if ($date_create > 0) {
                            
                            $last_time = $date_create - ($this->date - 3600);
                            
                            $message_text = $this->error_sim . $this->from_profile_reply . ", Следующее голосование будет доступно через: " . $this->parse_timer($last_time);
                            
                        }
                        
                    }
                    
                }else {

                    $message_text = $this->from_profile_reply . $this->messages->cannot_kick_admin;

                }
                
            }else {
                
                $message_text = $this->from_profile_reply . $this->messages->cannot_find_user;
                
            }
            
            $this->vk->send_message($message_text, $this->data_new_message);

        }

    }

    private function add_adm_in_status (int $user_id): string {
    
        $info_admin = $this->get_info_user($user_id);
        
        return " - @id" . $user_id . " (" . $info_admin->first_name . " " . $info_admin->last_name . ") " . $this->new_line;
        
    }

    public function check_security (): bool {

        if ($this->chat->security['security'])
            return true;

        $this->vk->send_message($this->messages->security_not_enabled, $this->data_new_message);
        return false;
    
    }

}
?>