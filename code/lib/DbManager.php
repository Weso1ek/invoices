<?php

/**
 * Class to manage operations on database
 *
 * PHP version 5
 *
 * @author     Sebastian Wesołowski <sebastian.t.wesolowski@gmail.com>
 */

require_once __DIR__.'/../vendor/autoload.php';

class DbManager {
    
    private $database;
    
    public static $usersTable = 'users';
    
    public static $invoicesTable = 'invoices';
    
    public function __construct() {
        $this->database = new medoo([
            'database_type' => 'mysql',
            'database_name' => (!empty(getenv('DB_NAME'))) ? getenv('DB_NAME') : 'clients',
            'server' => (!empty(getenv('DB_HOST'))) ? getenv('DB_HOST') : 'localhost',
            'username' => (!empty(getenv('DB_USER'))) ? getenv('DB_USER') : 'root',
            'password' => (!empty(getenv('DB_PASSWORD'))) ? getenv('DB_PASSWORD') : 'secret',
            'charset' => 'utf8',
            'port' => '3306',
            'option' => [
                PDO::MYSQL_ATTR_LOCAL_INFILE => true,
            ]
        ]);
    }
    
    /**
     * Select data from database
     * 
     * @param string $table - database table name
     * @param array $fields - list of selected fields
     * @param array $where - where conditions
     * @return array
     */
    public function select($table, $fields, $where) {
        return $this->database->select($table, $fields, $where);
    }
    
    /**
     * Insert data to the database
     * 
     * @param string $table - database table name
     * @param array $data - row data
     * @return integer $id
     */
    public function insert($table, $data) {
        $logger = new Logger();
        
        $data['created_at'] = date('Y-m-d H:i:s');
        
        $id = $this->database->insert($table, $data);
        if(!empty($id)) {
            $logger->logSuccess('Insert ' . $table . ' success ' . serialize($data)); 
        } else {
            $logger->logError('Insert ' . $table . ' error ' . serialize($data) . serialize($this->database->error())); 
        }
        return $id;
    }
    
    /**
     * Update row
     * 
     * @param string $table - database table name
     * @param array $data - row data
     * @param array $where - where condition
     * @return integer $id
     */
    public function update($table, $data, $where) {
        $logger = new Logger();
        
        $data['modified_at'] = date('Y-m-d H:i:s');
        unset($data['id']);
        
        $id = $this->database->update($table, $data, $where);
        if(!empty($id)) {
            $logger->logSuccess('Update ' . $table . ' success ' . serialize($data)); 
        } else {
            $logger->logError('Update ' . $table . ' error ' . serialize($data) . serialize($this->database->error())); 
        }
        return $id;
    }
    
    /**
     * Load csv file to the database
     * 
     * @param string $file - destination of csv file with transactions
     * @param array $table - table name
     * @param array $fields - csv column list inserted to database
     * @return boolean
     */
    public function loadFile($file, $table, $fields) {
        $logger = new Logger();
        
        $invoices = $this->database->query("LOAD DATA LOCAL INFILE '".$file."' "
                . "INTO TABLE ".$table." "
                . "FIELDS TERMINATED BY ',' "
                . "LINES TERMINATED BY '\n' "
                . "(".$fields.")");
        
        $dbError = $this->database->error();
        
        if(!empty($dbError['2'])) {
            $logger->logError('Load file error: ' . $file . serialize($this->database->error())); 
            return false;
        } else {
            $logger->logSuccess('Load file success: ' . $file);
            return true;
        }
    }
    
    /**
     * Fetch transaction count for every day
     * 
     * @return array
     */
    public function fetchTransactionCountPerDay() {
        return $this->database->query("SELECT created_at, COUNT(*) as count FROM invoices GROUP BY created_at")->fetchAll();
    }
    
    /**
     * Fetch count of users with transactions per day
     * 
     * @return array
     */
    public function fetchTransactionCountPerDayPerUser() {
        return $this->database->query("SELECT created_at, COUNT(DISTINCT user_id) as count FROM invoices GROUP BY created_at")->fetchAll();
    }
    
    /**
     * Fetch count of users for every email domain
     * 
     * @return array
     */
    public function fetchUsersPerEmailDomain() {
        return $this->database->query("SELECT SUBSTRING_INDEX(email,'@',-1) as domain, COUNT(*) as count "
                . "FROM users group by domain")->fetchAll();
    }
    
    /**
     * Fetch users with count of transactions more than $count
     * 
     * @return array
     */
    public function fetchUsersPerTransactionCount($count = 3) {
        return $this->database->query("SELECT u.name, u.email, COUNT(i.user_id) as count "
                . "FROM invoices AS i "
                . "LEFT JOIN users AS u "
                . "ON u.id = i.user_id "
                . "GROUP BY i.user_id "
                . "HAVING count > " . $count
        )->fetchAll();
    }
    
    /**
     * Fetch count of transactions for users
     * 
     * @return array
     */
    public function fetchTransactionCountPerUser() {
        return $this->database->query("SELECT u.name, u.email, COUNT(i.user_id) "
                . "FROM invoices AS i "
                . "LEFT JOIN users AS u "
                . "ON u.id = i.user_id "
                . "GROUP BY i.user_id"
        )->fetchAll();
    }
    
    /**
     * Calculate average and standard deviation for transaction ammounts for last
     * number of $days 
     * 
     * @param string $days - number of days 
     * @return array
     */
    public function fetchTransactionStatistics($days = 7) {
        return $this->database->query("SELECT AVG(amount) as avg, STDDEV(amount) as stddev "
                . "FROM invoices "
                . "WHERE created_at > DATE("
                . "(SELECT * FROM ( "
                . "     SELECT created_at FROM invoices GROUP BY created_at) AS tmp_table "
                . "ORDER BY created_at DESC LIMIT 1 OFFSET ".$days.")"
                . ")"
        )->fetch();
    }
}

