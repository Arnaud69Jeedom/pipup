<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

/* * ***************************Includes********************************* */
require_once __DIR__  . '/../../../../core/php/core.inc.php';

class pipup extends eqLogic
{
    public function preSave()
    {
        log::add('pipup', 'debug', 'preSave eqLogic');
        $position = $this->getConfiguration('position');

        if (!is_numeric($position)) {
            $this->setConfiguration('position', 2);
        }

        $cmds = $this->getCmd();
        foreach ($cmds as $cmd) {
            log::add('pipup', 'debug', 'foreach cmd getLogicalId: ' . $cmd->getLogicalId());

            if ($cmd->getLogicalId() == 'notify') {
                log::add('pipup', 'debug', 'preSave cmd notify');

                if (empty($cmd->getConfiguration('type_media'))) {
                    log::add('pipup', 'debug', 'preSave cmd. notify. avant type_media');

                    $cmd->setConfiguration('type_media', 'image');
                    log::add('pipup', 'debug', 'preSave cmd. notify. apres type_media: ' . $cmd->getConfiguration('type_media'));
                }
                if (empty($cmd->getConfiguration('titleColor'))) {
                    log::add('pipup', 'debug', 'preSave cmd. notify. avant titlecolor');

                    $cmd->setConfiguration('titleColor', "#000000");
                    log::add('pipup', 'debug', 'preSave cmd. notify. apres titlecolor: ' . $cmd->getConfiguration('titleColor'));
                }
                if (empty($cmd->getConfiguration('messageColor'))) {
                    $cmd->setConfiguration('messageColor', "#000000");
                }
                if (empty($cmd->getConfiguration('backgroundColor'))) {
                    $cmd->setConfiguration('backgroundColor', "#ffffff");
                }
                if (empty($cmd->getConfiguration('url'))) {
                    $cmd->setConfiguration('url', 'https://www.pinclipart.com/picdir/big/85-851186_push-notifications-push-notification-icon-png-clipart.png');
                }
            } else {
                if (empty($cmd->getConfiguration('type_media'))) {
                    $cmd->setConfiguration('type_media', 'image');
                    log::add('pipup', 'debug', 'preSave cmd. other. apres type_media: ' . $cmd->getConfiguration('type_media'));
                }
            }

            $cmd->setType('action');
            $cmd->setSubType('message');

            $cmd->save();
        }
    }

    public function postSave()
    {
        // pipup_action
        log::add('pipup', 'debug', 'postSave eqLogic');

        $cmds = $this->getCmd();
        $cmdsCount = count($cmds);

        if ($cmdsCount === 0) {
            // notify
            $notify = $this->getCmd(null, 'notify');
            if (!is_object($notify)) {
                $notify = new pipupCmd();
                $notify->setLogicalId('notify');
                $notify->setIsVisible(1);
                $notify->setName(__('notify', __FILE__));
                $notify->setOrder(0);

                $notify->setConfiguration('type_media', "image");
                $notify->setConfiguration('titleColor', "#000000");
                $notify->setConfiguration('messageColor', "#000000");
                $notify->setConfiguration('backgroundColor', "#ffffff");
                $notify->setConfiguration('url', 'https://www.pinclipart.com/picdir/big/85-851186_push-notifications-push-notification-icon-png-clipart.png');
            }
            $notify->setEqLogic_id($this->getId());
            $notify->setType('action');
            $notify->setSubType('message');
            $notify->save();
            unset($notify);

            // alerte
            $alert = $this->getCmd(null, 'alert');
            if (!is_object($alert)) {
                $alert = new pipupCmd();
                $alert->setLogicalId('alert');
                $alert->setIsVisible(1);
                $alert->setName(__('alert', __FILE__));
                $alert->setOrder(1);

                $alert->setConfiguration('type_media', "image");
                $alert->setConfiguration('titleColor', "#ff0000");
                $alert->setConfiguration('messageColor', "#000000");
                $alert->setConfiguration('backgroundColor', "#ffffff");
                $alert->setConfiguration('url', 'https://www.pinclipart.com/picdir/big/94-941341_open-animated-gif-alert-icon-clipart.png');
            }
            $alert->setEqLogic_id($this->getId());
            $alert->setType('action');
            $alert->setSubType('message');
            $alert->save();
            unset($alert);
        } else {
        }
    }

    public function preUpdate()
    {
    }

    public function postUpdate()
    {
    }

    public function preRemove()
    {
    }

    public function postRemove()
    {
    }
}

class pipupCmd extends cmd
{

    public function preSave()
    {
        if ($this->getLogicalId() !== 'alert' && $this->getLogicalId() !== 'notify') {
            log::add('pipup', 'debug', 'preSave cmd '.$this->getLogicalId());

            $name = $this->getName();

            $this->setIsVisible(1);
            $this->setName($name);
            $this->setLogicalId($name);
        }

        $this->setType('action');
        $this->setSubType('message');
    }


    /**
     * Récupérer la configuration de l'équipement
     */
    private function getMyConfiguration()
    {
        $configuration = new StdClass();

        $eqlogic = $this->getEqLogic(); //récupère l'éqlogic de la commande $this

        // Lecture et Analyse de la configuration

        // IP TV
        log::add('pipup', 'debug', ' Récupération iptv');
        $iptv = $eqlogic->getConfiguration('iptv');
        if ($iptv != '') {
            if (filter_var($iptv, FILTER_VALIDATE_IP)) {
                $configuration->iptv = $iptv;
            } else {
                log::add('pipup', 'error', ' Mauvaise valeur de iptv : ' . $iptv);
                return;
            }
        } else {
            log::add('pipup', 'error', ' Pas de iptv');
            return;
        }
        unset($iptv);

        // duration
        log::add('pipup', 'debug', ' Récupération duration');
        $duration = $eqlogic->getConfiguration('duration');
        if ($duration != '') {
            if (filter_var($duration, FILTER_VALIDATE_INT)) {
                $configuration->duration = $duration;
            } else {
                log::add('pipup', 'error', ' Mauvaise valeur de duration : ' . $duration);
                return;
            }
        } else {
            $configuration->duration = 30;
        }
        unset($duration);

        // Position
        log::add('pipup', 'debug', ' Récupération position');
        $position = $eqlogic->getConfiguration('position');
        if ($position != '') {
            if (filter_var($position, FILTER_VALIDATE_INT, ["options" => ["min_range" => 0,  "max_range" => 4]])  !== false) {
                $configuration->position = $position;
            } else {
                log::add('pipup', 'error', ' Mauvaise valeur de position : ' . $position);
                return;
            }
        } else {
            $configuration->position = 2; // BottomRight
        }
        unset($position);

        // titleSize
        log::add('pipup', 'debug', ' Récupération titleSize');
        $titleSize = $eqlogic->getConfiguration('titleSize');
        if ($titleSize != '') {
            if (filter_var($titleSize, FILTER_VALIDATE_INT)) {
                $configuration->titleSize = $titleSize;
            } else {
                log::add('pipup', 'error', ' Mauvaise valeur de titleSize : ' . $titleSize);
                return;
            }
        } else {
            $configuration->titleSize = 20;
        }
        unset($titleSize);

        // messageSize
        log::add('pipup', 'debug', ' Récupération messageSize');
        $messageSize = $eqlogic->getConfiguration('messageSize');
        if ($messageSize != '') {
            if (filter_var($messageSize, FILTER_VALIDATE_INT)) {
                $configuration->messageSize = $messageSize;
            } else {
                log::add('pipup', 'error', ' Mauvaise valeur de messageSize : ' . $messageSize);
                return;
            }
        } else {
            $configuration->messageSize = 14;
        }
        unset($messageSize);

        // imageSize
        log::add('pipup', 'debug', ' Récupération imageSize');
        $imageSize = $eqlogic->getConfiguration('imageSize');
        if ($imageSize != '') {
            if (filter_var($imageSize, FILTER_VALIDATE_INT)) {
                $configuration->imageSize = $imageSize;
            } else {
                log::add('pipup', 'error', ' Mauvaise valeur de imageSize : ' . $imageSize);
                return;
            }
        } else {
            $configuration->imageSize = 240;
        }
        unset($imageSize);

        // webWidth
        log::add('pipup', 'debug', ' Récupération webWidth');
        $webWidth = $eqlogic->getConfiguration('webWidth');
        if ($webWidth != '') {
            if (filter_var($webWidth, FILTER_VALIDATE_INT)) {
                $configuration->webWidth = $webWidth;
            } else {
                log::add('pipup', 'error', ' Mauvaise valeur de webWidth : ' . $webWidth);
                return;
            }
        } else {
            $configuration->webWidth = 640;
        }
        unset($webWidth);

        // webHeight
        log::add('pipup', 'debug', ' Récupération webHeight');
        $webHeight = $eqlogic->getConfiguration('webHeight');
        if ($webHeight != '') {
            if (filter_var($webHeight, FILTER_VALIDATE_INT)) {
                $configuration->webHeight = $webHeight;
            } else {
                log::add('pipup', 'error', ' Mauvaise valeur de webHeight : ' . $webHeight);
                return;
            }
        } else {
            $configuration->webHeight = 480;
        }
        unset($webHeight);


        return $configuration;
    }

    function action($configuration, $options, $type = 'notify')
    {
        $eqlogic = $this->getEqLogic();
        $cmd = $eqlogic->getCmd(null, $type);

        $title = $options['title'];
        $message = $options['message'];

        $tmp = new stdClass();
        // Paramétrage Generique
        $tmp->duration = $configuration->duration;
        $tmp->position = $configuration->position;
        $tmp->titleSize = $configuration->titleSize;
        $tmp->messageSize = $configuration->messageSize;

        // Paramètre Action
        $tmp->title = $title;
        $tmp->message = $message;

        // Paramétrage Commande
        $tmp->titleColor = $cmd->getConfiguration('titleColor');
        if (empty($tmp->titleColor)) {
            $tmp->titleColor = "#000000";
        }

        $tmp->messageColor = $cmd->getConfiguration('messageColor');
        if (empty($tmp->messageColor)) {
            $tmp->messageColor = "#000000";
        }

        $tmp->backgroundColor = $cmd->getConfiguration('backgroundColor');
        if (empty($tmp->backgroundColor)) {
            $tmp->backgroundColor = "#ffffff";
        }

        $duration = $cmd->getConfiguration('duration');
        if ($duration != '') {
            if (filter_var($duration, FILTER_VALIDATE_INT)) {
                $tmp->duration = $duration;
            }
        }

        $type_media = $cmd->getConfiguration('type_media');
        if (empty($type_media)) {
            $type_media = "image";
        }
        log::add('pipup', 'debug', ' type_media: ' . $type_media);

        if (!empty($cmd->getConfiguration('url'))) {
            switch ($type_media)  {
                case 'image':
                    $image = new stdClass();
                    $image->uri = $cmd->getConfiguration('url');
                    $image->width = $configuration->imageSize;

                    $tmp->media = new StdClass();
                    $tmp->media->image = $image;
                    break;  
                case 'video':
                    // Ne fonctionne pas
                    $video = new stdClass();
                    $video->uri = $cmd->getConfiguration('url');
                    $video->width = $configuration->imageSize;

                    $tmp->media = new StdClass();
                    $tmp->media->video = $video;
                    break;
                case 'web':
                    $url = new stdClass();
                    $url->uri = $cmd->getConfiguration('url');

                    $width = $configuration->webWidth;
                    log::add('pipup', 'debug', ' webWidth: ' . $width);
                    if (empty($width)) {
                        $width = 640;
                    }
                    $height = $configuration->webHeight;
                    log::add('pipup', 'debug', ' webHeight: ' . $height);
                    if (empty($height)) {
                        $height = 480;
                    }
                    $url->width = $width;
                    $url->height = $height;

                    $tmp->media = new StdClass();
                    $tmp->media->web = $url;
                    
                    break;
            }
        }
        
        $data = json_encode($tmp);
        log::add('pipup', 'debug', ' data: ' . $data);

        $tuCurl = curl_init();
        curl_setopt($tuCurl, CURLOPT_URL, "http://" . $configuration->iptv . ":7979/notify");
        // curl_setopt($tuCurl, CURLOPT_PORT , 7979);
        curl_setopt($tuCurl, CURLOPT_VERBOSE, 0);
        curl_setopt($tuCurl, CURLOPT_HEADER, 0);
        // curl_setopt($tuCurl, CURLOPT_SSLVERSION, 3);
        // curl_setopt($tuCurl, CURLOPT_SSLCERT, getcwd() . "/client.pem");
        // curl_setopt($tuCurl, CURLOPT_SSLKEY, getcwd() . "/keyout.pem");
        // curl_setopt($tuCurl, CURLOPT_CAINFO, getcwd() . "/ca.pem");
        curl_setopt($tuCurl, CURLOPT_POST, 1);
        // curl_setopt($tuCurl, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($tuCurl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($tuCurl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($tuCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Content-length: " . strlen($data)));

        $tuData = curl_exec($tuCurl);
        $errno = curl_errno($tuCurl);
        if (!$errno) {
            $info = curl_getinfo($tuCurl);
            // log::add('pipup', 'info', 'info : ' . json_encode($info));

            if ($info["http_code"] == 200) {
                log::add('pipup', 'info', 'Took ' . $info['total_time'] . ' seconds to send a request to ' . $info['url']);
                log::add('pipup', 'debug', ' data : ' . $tuData);
            } else {
                log::add('pipup', 'error', ' data : ' . $tuData);
            }
        } elseif ($errno == 7) {
            // CURLE_COULDNT_CONNECT
            log::add('pipup', 'info', 'Connexion impossible sur :' . $configuration->iptv);
        } else {
            log::add('pipup', 'error', 'erreurNo: ' . curl_errno($tuCurl) . ' : ' . curl_error($tuCurl));
        }

        curl_close($tuCurl);
    }

    // Exécution d'une commande  
    public function execute($_options = array())
    {
        log::add('pipup', 'info', ' **** execute ****' . $this->getLogicalId());

        $eqlogic = $this->getEqLogic(); //récupère l'éqlogic de la commande $this
        log::add('pipup', 'info', ' Objet : ' . $eqlogic->getName());

        // Lecture et Analyse de la configuration
        $configuration = $this->getMyConfiguration();
        log::add('pipup', 'debug', ' configuration :' . json_encode((array)$configuration));

        $this->action($configuration, $_options, $this->getLogicalId());
    }
}
