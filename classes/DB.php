<?php
class DB {

    protected $db;
    
    public function __construct(string $host, string $username, string $pass, string $dbname) {
        $this->db = new PDO("mysql:host=" . $host . ";dbname=" . $dbname . ";charset=utf8", $username, $pass);
    }
    
    public function query (string $sql, array $params = []): object {
        $result = $this->db->prepare($sql);
        
        if ($params) {
            foreach ($params as $key => $val) {
                $result->bindValue(":" . $key, $val);
            }
        }
        
        $result->execute();
        
        return $result;
    }
    
    public function fetch (string $sql, array $params = []): array {
        $result = $this->query($sql, $params);
        
        return $result->fetchAll(PDO::FETCH_BOTH);
    }

    public function select (
            string $table, 
            string $columns = '*', 
            string $where = "", 
            array $values = [],
            int $limit = 0, 
            int $offset = 0,
            string $order = ''
        ): array {

        $sql = ["SELECT $columns FROM $table"];

        if ($where)
            $sql[] = "WHERE $where";

        if ($order)
            $sql[] = "ORDER BY $order";

        if ($limit)
            $sql[] = "LIMIT $limit";
        
        if ($offset)
            $sql[] = "OFFSET $offset";

        return $this->fetch(implode(" ", $sql), $values);

    }

    public function update (
            string $table, 
            string $params = "", 
            string $where = "", 
            array $values = [], 
            int $limit = 0
        ): void {

        $sql = ["UPDATE $table SET $params"];

        if ($where)
            $sql[] = "WHERE $where";

        if ($limit)
            $sql[] = "LIMIT $limit";

        $this->query(implode(" ", $sql), $values);

    }

    public function delete (
            string $table, string $where, array $values, int $limit = 0
        ): void {

            $sql = ["DELETE FROM $table WHERE $where"];
    
            if ($limit)
                $sql[] = "LIMIT $limit";
    
            $this->query(implode(" ", $sql), $values);

    }

    public function insert (string $table, string $values1, array $values2): void {

        $sql = "INSERT INTO $table VALUES $values1";
        $this->query($sql, $values2);

    }
    
    public function last_column (string $sql, array $params = []) {
        $result = $this->query($sql, $params);
        
        return $result->fetchColumn();
    }

}
?>