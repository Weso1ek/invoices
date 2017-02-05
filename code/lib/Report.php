<?php

/**
 * Create and send report email
 *
 * PHP version 5
 *
 * @author     Sebastian Wesołowski <sebastian.t.wesolowski@gmail.com>
 */

require_once __DIR__.'/../vendor/autoload.php';

class Report {
    
    private $db;
    
    public function __construct() {
        $this->db = new DbManager();
    }
    
    /**
     * Send email with report
     * 
     * @return boolean
     */
    public function sendReport() {
        $mail = $this->prepareEmail();
        if(!$mail->send()) {
            throw new Exception('Raport error ' . $mail->ErrorInfo);
        }
        return true;
    }
    
    /**
     * Create and prepare email object
     * 
     * @return object $email
     */
    public function prepareEmail() {
        $recipient = (!empty(getenv('RECIPIENT_EMAIL'))) ? getenv('RECIPIENT_EMAIL') : 'sebastian.t.wesolowski@gmail.com';
        
        $content = $this->prepareContent();
        
        $mail = new PHPMailer();

        $mail->IsSMTP();
        $mail->CharSet = 'UTF-8';
        $mail->Host = "mail.localdomain.com";
        $mail->SMTPAuth = true;                  
        $mail->SMTPSecure = "tls";                 
        $mail->Host = "ssl://smtp.gmail.com";
        $mail->Port = 465;                   
        $mail->Username = getenv("SMTP_USERNAME");
        $mail->Password = getenv("SMTP_PASSWORD");
        $mail->SetFrom('noreply@gmail.com', 'Raport automatyczny');
        $mail->Subject = "Raport klientów i ich transakcji";
        $mail->MsgHTML($content);

        $address = $recipient;
        $mail->AddAddress($address);

        return $mail;
    }
    
    /**
     * Create email html content
     * 
     * @return string $content
     */
    protected function prepareContent() {
        $content = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                    <html xmlns="http://www.w3.org/1999/xhtml">
                        <head>
                            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
                            <title></title>
                            <style></style>
                        </head>
                        <body>
                            <table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%" id="bodyTable">
                                <tr>
                                    <td align="center" valign="top">
                                        <table border="0" cellpadding="20" cellspacing="0" width="600" id="emailContainer">
                                            <tr>
                                                <td>
                                                    '.$this->prepareTransactionsPerDay().'
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    '.$this->prepareTransactionsPerDayPerUser().'
                                                </td>
                                            </tr>    
                                            <tr>    
                                                <td>
                                                    '.$this->prepareUsersPerEmailDomain().'
                                                </td>
                                            </tr>    
                                            <tr>    
                                                <td>
                                                    '.$this->prepareUsersTransactionCount().'
                                                </td>
                                            </tr>
                                            <tr>    
                                                <td>
                                                    '.$this->prepareTransactionsPerUser().'
                                                </td>
                                            </tr>
                                            <tr>    
                                                <td>
                                                    '.$this->prepareTransactionsStatistics().'
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </body>
                    </html>';
        return $content;
    }
    
    /**
     * Fetch and prepare html with list of transactions for single day
     * 
     * @return string $html
     */
    protected function prepareTransactionsPerDay() {
        $transactionsPerDay = $this->db->fetchTransactionCountPerDay();
        if(empty($transactionsPerDay)) {
            return '';
        }
        return $this->generateHtmlTable($transactionsPerDay, ['Dzień', 'Ilość transakcji']);
    }
    
    /**
     * Fetch and prepare html with list of unique users for single day
     * 
     * @return string $html
     */
    protected function prepareTransactionsPerDayPerUser() {
        $transactionsPerDayPerUser = $this->db->fetchTransactionCountPerDayPerUser();
        if(empty($transactionsPerDayPerUser)) {
            return '';
        }
        return $this->generateHtmlTable($transactionsPerDayPerUser, ['Dzień', 'Ilość unikalnych użytkowników']);
    }
    
    /**
     * Fetch and prepare html with list of users in email domains
     * 
     * @return string $html
     */
    protected function prepareUsersPerEmailDomain() {
        $usersPerEmailDomain = $this->db->fetchUsersPerEmailDomain();
        if(empty($usersPerEmailDomain)) {
            return '';
        }
        return $this->generateHtmlTable($usersPerEmailDomain, ['Domena email', 'Ilość użytkowników']);
    }
    
    /**
     * Fetch and prepare html with list of users with more than 3 transactions
     * 
     * @return string $html
     */
    protected function prepareUsersTransactionCount() {
        $usersTransactionCount = $this->db->fetchUsersPerTransactionCount();
        if(empty($usersTransactionCount)) {
            return '';
        }
        return $this->generateHtmlTable($usersTransactionCount, ['Imię i nazwisko', 'Email', 'Ilość transakcji (> 3)']);
    }
    
    /**
     * Fetch and prepare html with list of count transactions for users
     * 
     * @return string $html
     */
    protected function prepareTransactionsPerUser() {
        $transactionsPerUser = $this->db->fetchTransactionCountPerUser();
        if(empty($usersTransactionCount)) {
            return '';
        }
        return $this->generateHtmlTable($transactionsPerUser, ['Imię i nazwisko', 'Email', 'Ilość transakcji']);
    }
    
    /**
     * Fetch and prepare html for avereage and standard deviation for amount 
     * of transactions from the last 7 days
     * 
     * @return string $html
     */
    protected function prepareTransactionsStatistics() {
        $transactionsStatistics = $this->db->fetchTransactionStatistics();
        if(empty($transactionsStatistics)) {
            return '';
        }
        
        $html = '<table border="1" cellpadding="5" cellspacing="0">';
        $html .= '<tr>';
        $html .= '<td><strong>Średnia wartość transakcji (7 ostatnich dni)</strong></td>';
        $html .= '<td>'.$transactionsStatistics['avg'].'</td>';
        $html .= '</tr>';
        $html .= '<tr>';
        $html .= '<td><strong>Odchylenie standardowe transakcji (7 ostatnich dni)</strong></td>';
        $html .= '<td>'.$transactionsStatistics['stddev'].'</td>';
        $html .= '</tr>';
        $html .= '</table>';
        
        return $html;
    }
    
    /**
     * Prepare html table
     * 
     * @param array $data - database data
     * @param array $labels - labels names 
     * @return string $html
     */
    protected function generateHtmlTable($data, $labels) {
        $html = '<table border="1" cellpadding="5" cellspacing="0">';
        
        $html .= '<tr>';
        foreach($labels as $label) {
            $html .= '<td><strong>'.$label.'</strong></td>';
        }
        $html .= '</tr>';
        
        foreach($data as $item) {
            $html .= '<tr>';
            foreach(array_unique($item) as $value) {
                $html .= '<td>'.$value.'</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</table>';
        return $html;
    }
}