<?php
class Proxy extends Main {

    private $url = 'https://api.getproxylist.com/proxy';

    public function __construct() {

        parent::__construct();
        
    }

    public function delete (int $id): void {

        $this->db->delete(
            'proxy',
            'id=:id',
            ['id' => $id], 1
        );

    }

    public function update_proxy (array $info): void {
        
        $this->db->update(
            'proxy',
            'is_active=:is_active',
            'id=:id',
            [
                'is_active' => $info['is_active'],
                'id' => $info['id']
            ], 1
        );
        
    }

    public function get_proxy (): array {
        
        $proxy_info = $this->db->select(
            'proxy', '*', 
            'is_active<:date', 
            [
                'date' => ($this->date - 30)
            ], 1
        )[0];
        
        if (!$proxy_info) {
            
            $new_proxy = json_decode(file_get_contents($this->url));
            
            if ($new_proxy && $new_proxy->ip) {
                
                $this->db->insert(
                    'proxy', 
                    '(NULL, :ip, :port, :protocol, :country, 0, :date)',
                    [
                        'ip' => $new_proxy->ip,
                        'port' => $new_proxy->port,
                        'protocol' => $new_proxy->protocol,
                        'country' => $new_proxy->country,
                        'date' => $this->date
                    ]
                );
                
            }
            
        }
        
        return $proxy_info;
        
    }

}
?>