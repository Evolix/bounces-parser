<?php

/**
 * database object
 *
 * Copyright (c) 2009 Evolix - Tous droits reserves
 * 
 * $Id: storage.php 26 2010-01-11 10:01:45Z spalma $
 * vim: expandtab softtabstop=4 tabstop=4 shiftwidth=4 showtabline=2
 *
 * @author Thomas Martin <tmartin@evolix.fr>
 * @author Sebastien Palma <spalma@evolix.fr>
 * @version 0.1
 */

require_once('../lib/Mysql.php');


class databaseStorage {
	private $mysql=NULL;
    private $connexion=NULL;

    public function __construct() {
        $this->mysql = new Mysql();
        $this->connexion = $this->mysql->MyConnect();
    }

    public function store_in_db($bounce, $mailbox='') {
	$sql = "INSERT INTO `bounces";
	
	if ($mailbox!='') $sql .= "_$mailbox";
	
	$sql.= "` VALUES(NULL,";
	$sql.= "'".$bounce->getStatus()."',";
	$sql.= "'".$bounce->getMessageID()."',";
	$sql.= "'".$bounce->getErrorCode()."',";
	$sql.= "'".$bounce->getBounceReason()."',";
	$sql.= "'".$bounce->getServerAnswer()."',";
	$sql.= "'".date('Y-m-d H:i:s')."',";
	$sql.= "'".$bounce->getBouncedEmail()."',";

	$domain='';
	$email_parts = explode('@', $bounce->getBouncedEmail());
	if (is_array($email_parts) && array_key_exists(1, $email_parts)) $domain=$email_parts[1];
	$sql.= "'".$domain."'";
	$sql.= ")";

	//echo "$sql\n";
        $this->mysql->MyReq($this->connexion, $sql);
    }
	
}

