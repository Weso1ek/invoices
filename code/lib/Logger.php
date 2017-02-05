<?php

/**
 * Log informations to the files
 *
 * PHP version 5
 *
 * @author     Sebastian Wesołowski <sebastian.t.wesolowski@gmail.com>
 */

class Logger {
    
    public static $path = '/logs/';

    public function __construct() {
    }
    
    /**
     * Pass success message to the log file
     * 
     * @param string $message - log content
     * @return boolean
     */
    public function logSuccess($message) {
        return $this->log('success', $message);
    }
    
    /**
     * Pass error message to the log file
     * 
     * @param string $message - log content
     * @return boolean
     */
    public function logError($message) {
        return $this->log('error', $message);
    }
        
    /**
     * Insert message to the log file
     * 
     * @param string $prefix - log type
     * @param string $message - log content
     * @return boolean
     */
    public function log($prefix, $message) {
        $filename = $this->getFilename($prefix);
        $handle = fopen($filename, "a+");
        $cos = fputcsv($handle, [date('Y-m-d H:i:s'), $message]);
        fclose($handle);
        return true;
    }
    
    /**
     * Get log file destination
     * 
     * @param string $prefix - log type
     * @return string log file destination
     */
    public function getFilename($prefix) {
        return getenv('DIR_CODE').self::$path . 'logs_' . $prefix . '_' . date('Y_m_d') . '.csv';
    }
}