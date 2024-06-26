<?php
class Database {
    private $servername = "";
    private $dbusername = "";
    private $dbpassword = "";
    private $database = "";
    private $conn;
    public function __construct() {
        $this->connect();
    }
    private function connect() {
        $this->conn = new mysqli($this->servername, $this->dbusername, $this->dbpassword, $this->database);
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }
    private function disconnect() {
        $this->conn->close();
    }
    public function getLastInsertedId() {
        return $this->conn->insert_id;
    }

    // for non-select queries, set returnResult to false on call.
    public function executeQuery($sql, $params = [], $returnResult = true) {
        $stmt = $this->conn->prepare($sql);
        if ($stmt === false) {
            die("Error in prepare statement: " . $this->conn->error);
        }
        if (!empty($params)) {
            $types = '';
            $bindParams = [&$types];
            foreach ($params as &$param) {
                if (is_int($param)) {
                    $types .= 'i';
                } elseif (is_float($param)) {
                    $types .= 'd';
                } elseif (is_string($param)) {
                    $types .= 's';
                } else {
                    $types .= 'b';
                }
                $bindParams[] = &$param;
            }
            call_user_func_array([$stmt, 'bind_param'], $bindParams);
        }
        $stmt->execute();
        if ($stmt->error) {
            die("Error in execute statement: " . $stmt->error);
        }
        if ($returnResult) {
            $result = $stmt->get_result();
            if ($result === false) {
                die("Error in getting result: " . $stmt->error);
            }
            return $result;
        }

        $success = $stmt->affected_rows > 0;
        $stmt->close();
        return $success;
    }
    public function fetchRows($result) {
        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }
    public function fetchSingleRow($result) {
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        } else {
            return null;
        }
    }
}

$db = new Database();
