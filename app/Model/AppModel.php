<?php
/**
 * Application model for CakePHP.
 *
 * This file is application-wide model file. You can put all
 * application-wide model-related methods here.
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Model
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

App::uses('Model', 'Model');

/**
 * Application model for Cake.
 *
 * Add your application-wide methods in the class below, your models
 * will inherit them.
 *
 * @package       app.Model
 */
class AppModel extends Model {
    /**
     * const
     */
    const ANDROID = 1;
    const IOS = 2;

    /**
     * @param $tokenDevice
     * @param $message
     * @param $data
     * @return mixed
     */
    public function send_push_notification($tokenDevice, $message, $data, $osType)
    {
        // Set POST variables
        $url = FIREBASE_CLOULD_MESSAGEING;
        $fields = array();
        switch($osType) {
            case self::ANDROID:
                $data['body'] = $message;
                $fields = array (
                    'to' => $tokenDevice,
                    'priority' => 'high',
                    'data' => $data
                );
                break;
            case self::IOS:
                $fields = array (
                    'to' => $tokenDevice,
                    'priority' => 'high',
                    'notification' => array (
                        'body'  => $message
                    ),
                    'data' => $data
                );
                break;
        }

        $headers = array(
            'Authorization:key=' . GOOGLE_API_KEY,
            'Content-Type: application/json'
        );

        $pathLogHeader = WWW_ROOT . 'files' . DS . 'log_header_push_notif.txt';
        $f = fopen($pathLogHeader, 'w');

        // Open connection
        $ch = curl_init();
        // Set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // Disabling SSL Certificate support temporary
        curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_STDERR, $f);

        // Execute post
        $result = curl_exec($ch);
        // Close connection
        curl_close($ch);
        return $result;
    }
}
