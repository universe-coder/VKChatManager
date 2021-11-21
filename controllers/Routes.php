<?php
require_once 'Controller.php';
require_once 'CheckMessage.php';
require_once 'Manager.php';

class Routes extends Controller {

    public function __construct ($data) {

        parent::__construct();
        $this->data = $data;
        $this->peer_id = (int) $this->filstr((string) $data->object->peer_id);
        $this->from_id = (int) $this->filstr((string) $data->object->from_id);
        $this->mess = $this->filstr((string) $data->object->text);
        $this->data_new_message = ['chat_id' => $this->peer_id];
        $this->chat = (object) [
            'admins'    => (object) ["groups" => [], "users" => []],
            'info'      => (object) [
                'db' => (object) $this->get_chat_info($this->peer_id), 
                'vk' => $this->vk->get_conversation_by_id($this->peer_id)->items[0]
            ],
            'members'   => $this->vk->get_conversation_members($this->peer_id)
        ];
        $this->chat->local_id = $this->chat->info->vk->peer->local_id;
        $this->chat->security = $this->db->select(
            'control_chats', '*', 
            'local_chat_id=:local_chat_id', 
            [
                "local_chat_id" => $this->chat->local_id
            ], 1
        )[0];
    
        
        if ($this->from_id > 0) {

            $from_user_info = $this->get_info_user($this->from_id);
            $this->from_profile_reply = "@id$this->from_id ($from_user_info->first_name $from_user_info->last_name)";

        }else {
            $this->from_profile_reply = "";
        }

    }

    public function route (): void {

        if ($this->chat->info && !$this->chat->info->db->is_active) {
            
            $this->chat->info->db->is_active = 1;
        
            $this->chat_update_info($this->chat->info->db);
            
            $this->check_messages = new CheckMessage($this);

            if ($this->check_messages->run_check()) {

                $manager = new Manager($this);

                $this->mess = trim($this->mess);
                $lower_command = mb_strtolower($this->mess);

                if ($lower_command == $this->commands->help 
                    || ($this->data->object->conversation_message_id == 1 && $this->messages->welcome_invite)) {

                    $manager->help();

                }elseif ($lower_command == $this->commands->clear) {

                    if ($this->check_messages->access->is_admin)
                        $this->clear_chat($this->peer_id);
                    else 
                        $this->check_messages->access_denied = true;

                }elseif ($this->findCommand($this->commands->all)) {

                    if ($this->check_messages->access->is_admin)
                        $manager->call_everyone($this->mess);
                    else
                        $this->check_messages->access_denied = true;

                }elseif ($this->mess == "+" && $this->from_id > 0) {

                    $manager->vote_poll();

                }elseif ($this->findCommand($this->commands->votekick) && $this->from_id > 0) {

                    if (isset($this->data->object->reply_message->from_id))
                        $manager->create_voting($this->data->object->reply_message->from_id);
                    else
                        $manager->create_voting($this->mess);

                }elseif ($this->findCommand($this->commands->kick)) {

                    if ($this->check_messages->access->kick) {

                        if (isset($this->data->object->reply_message->from_id))
                            $manager->kick($this->data->object->reply_message->from_id);
                        else
                            $manager->kick_by_screen_name($this->mess);

                    }else {

                        $this->check_messages->access_denied = true;

                    }

                }elseif ($this->findCommand($this->commands->admin_add)) {

                    if ($this->check_messages->access->is_owner) {

                        if (isset($this->data->object->reply_message->from_id))
                            $manager->add_admin($this->data->object->reply_message->from_id);
                        else
                            $manager->add_admin($this->mess);

                    }else {

                        $this->check_messages->access_denied = true;

                    }

                }elseif ($this->findCommand($this->commands->admin_remove)) {

                    if ($this->check_messages->access->is_owner) {

                        if (isset($this->data->object->reply_message->from_id))
                            $manager->remove_admin($this->data->object->reply_message->from_id);
                        else
                            $manager->remove_admin($this->mess);

                    }else {

                        $this->check_messages->access_denied = true;

                    }

                }elseif ($this->findCommand($this->commands->admin_list)) {

                    $manager->show_admins();

                }elseif ($this->findCommand($this->commands->show_secure)) {
                    
                    if ($this->check_messages->access->is_admin)
                        $manager->show_status_security();
                    else
                        $this->check_messages->access_denied = true;

                }elseif ($this->findCommand($this->commands->show_active)) {

                    $manager->show_last_active();

                }elseif ($this->findCommand($this->commands->show_unactive)) {

                    if ($this->check_messages->access->is_admin)
                        $manager->show_not_active();
                    else
                        $this->check_messages->access_denied = true;

                }else {

                    $secure_module = false;

                    if ($this->findCommand($this->commands->secure_links))
                        $secure_module = "links";
                    elseif ($this->findCommand($this->commands->secure_invites))
                        $secure_module = "invites";
                    elseif ($this->findCommand($this->commands->secure_bots))
                        $secure_module = "bots";
                    elseif ($this->findCommand($this->commands->secure_nude))
                        $secure_module = "nude";
                    elseif ($this->findCommand($this->commands->secure))
                        $secure_module = "security";

                    if ($secure_module) {

                        if ($this->check_messages->access->is_admin)
                            if ($this->findCommand($this->commands->enable))
                                $manager->$secure_module(1);
                            elseif ($this->findCommand($this->commands->disable))
                                $manager->$secure_module(0);
                        else
                            $this->check_messages->access_denied = true;

                    }

                }

                

                if ($this->check_messages->access_denied) {

                    $message_rule_kick = $this->messages->access_denied;

                    if (!$this->check_messages->access->kick && $this->check_messages->access->is_admin)
                        $message_rule_kick = $this->messages->limit_denied;

                    $this->vk->send_message(($this->check_messages->from_profile_reply . $message_rule_kick), $this->data_new_message);

                }

                $this->update_last_activity($this->from_id);

            }
            
            
            $this->chat->info->db->is_active = 0;
        
            $this->chat_update_info($this->chat->info->db);
            
        }

    }

    public function findCommand (string $command): bool {

        $result = mb_stristr($this->mess, $command) 
               && mb_stripos($this->mess, $command) === 0;

        if ($result)
            $this->mess = trim(str_replace($command, '', $this->mess));

        return $result;

    }

}