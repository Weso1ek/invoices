<?php

/**
 * Import data to the database
 *
 * PHP version 5
 *
 * @author     Sebastian Wesołowski <sebastian.t.wesolowski@gmail.com>
 */

require_once 'DbManager.php';
require_once 'Logger.php';

class Import {
    
    public static $path = '/data/';
    
    private $db;
    
    public function __construct() {
        $this->db = new DbManager();
    }
    
    /**
     * Insert/update users to the database
     * 
     * @param string $file filename
     * @return boolean
     */
    public function importUsers($file) {
        if (!file_exists(getenv('DIR_CODE').self::$path.$file)) {
            throw new Exception('File: ' . $file . ' not exist.');
        }
        
        if(($handle = fopen(getenv('DIR_CODE').self::$path.$file, 'r')) !== false) {
            while(($data = fgetcsv($handle)) !== false) {

                $id = $data['0'];
                if(!is_numeric($id)) {
                    continue;
                }
                
                $user = [
                    'id' => $id,
                    'name' => $data['1'],
                    'email' => $data['2']
                ];
                
                $userDb = $this->db->select(DbManager::$usersTable, ['id', 'name', 'email'], ['id' => $id]);
                if(!empty($userDb)) {
                    $this->db->update(DbManager::$usersTable, $user, ['id' => $id]);
                } else {
                    $this->db->insert(DbManager::$usersTable, $user);
                }
                unset($data);
            }
            fclose($handle);
        }
        return true;
    }
    
    /**
     * Insert invoices to the database
     * 
     * @param string $file filename
     * @return boolean
     */
    public function importInvoices($file) {
        if (!file_exists(getenv('DIR_CODE').self::$path.$file)) {
            throw new Exception('File: ' . $file . ' not exist.');
        }
        
        return $this->db->loadFile(
            getenv('DIR_CODE').self::$path.$file, 
            DbManager::$invoicesTable,
            "user_id, amount, created_at"
        );
    }
}