<?php

/**
 * Config file
 *
 * Copyright (c) 2009 Evolix - Tous droits reserves
 * 
 * $Id: config.php 27 2010-01-11 16:33:56Z spalma $
 * vim: expandtab softtabstop=4 tabstop=4 shiftwidth=4 showtabline=2
 *
 * @author Thomas Martin <tmartin@evolix.fr>
 * @author Sebastien Palma <spalma@evolix.fr>
 * @version 0.1
 */

define('BOUNCE_HARD', 1);
define('BOUNCE_SOFT', 2);
define('BOUNCE_UNKN', 9);

require_once("../config/database.php");

// Maildir path
$config = array (
    $search_maildir = '/home/vmail/example.com/no-reply',
);

$bounce_regex = array(
    1 => array( // Utilisateur n'existe pas
            'User unknown', // wanadoo, orange, 9online, neuf, voila, bluewin.ch
            'Unknown user', // tlb.sympatico.ca
            'This account has been disabled or discontinued', // yahoo
            'The email account .* does not exist', // google
            'mailbox unavailable', // hotmail, live, msn
            'blocked due to inactivity', // free, aliceadsl
            'Recipient address rejected.*User unknown', // laposte
            'skynet\.be.*quota exceeded', // Skynet
            'MAILBOX NOT FOUND', // AOL
            'IS NOT ACCEPTING ANY MAIL', // AOL
            'unknown or illegal alias', // Videotron
            'Inactive MailBox', // Numericable
            'Mailbox has moved' // divers serveurs
        ),
    2 => array( // Erreurs de domaines (mal formulés)
            'Operation timed out',
            'Connection refused',
            'Connection timed out',
            'Host not found'
        )
);



?>
