<?php

/**
 * bounceParser object
 *
 * Copyright (c) 2009 Evolix - Tous droits reserves
 * 
 * $Id: parser.php 27 2010-01-11 16:33:56Z spalma $
 * vim: expandtab softtabstop=4 tabstop=4 shiftwidth=4 showtabline=2
 *
 * @author Thomas Martin <tmartin@evolix.fr>
 * @author Sebastien Palma <spalma@evolix.fr>
 * @version 0.1
 */


class bounceParser {
    private $content = NULL;
    private $bounced_mail = NULL;
    private $smtp_error_code = NULL;
    private $status = BOUNCE_UNKN;
    private $boundary = NULL;
    private $boundaries = array();
    private $headers = array();
    private $mailer = NULL;
    private $bounce_server_answer = NULL;
    private $bounce_reason = NULL;
    private $message_id = NULL;


    private $bounce_official_reasons = array (
                '00' => 'Not Applicable',

                '10' => 'Other address status',
                '11' => 'Bad destination mailbox address',
                '12' => 'Bad destination system address',
                '13' => 'Bad destination mailbox address syntax',
                '14' => 'Destination mailbox address ambiguous',
                '15' => 'Destination mailbox address valid',
                '16' => 'Mailbox has moved',
                '17' => 'Bad sender\'s mailbox address syntax',
                '18' => 'Bad sender\'s system address',

                '20' => 'Other or undefined mailbox status',
                '21' => 'Mailbox disabled, not accepting messages',
                '22' => 'Mailbox full',
                '23' => 'Message length exceeds administrative limit.',
                '24' => 'Mailing list expansion problem',

                '30' => 'Other or undefined mail system status',
                '31' => 'Mail system full',
                '32' => 'System not accepting network messages',
                '33' => 'System not capable of selected features',
                '34' => 'Message too big for system',

                '40' => 'Other or undefined network or routing status',
                '41' => 'No answer from host',
                '42' => 'Bad connection',
                '43' => 'Routing server failure',
                '44' => 'Unable to route',
                '45' => 'Network congestion',
                '46' => 'Routing loop detected',
                '47' => 'Delivery time expired',

                '50' => 'Other or undefined protocol status',
                '51' => 'Invalid command',
                '52' => 'Syntax error',
                '53' => 'Too many recipients',
                '54' => 'Invalid command arguments',
                '55' => 'Wrong protocol version',

                '60' => 'Other or undefined media error',
                '61' => 'Media not supported',
                '62' => 'Conversion required and prohibited',
                '63' => 'Conversion required but not supported',
                '64' => 'Conversion with loss performed',
                '65' => 'Conversion failed',

                '70' => 'Other or undefined security status',
                '71' => 'Delivery not authorized, message refused',
                '72' => 'Mailing list expansion prohibited',
                '73' => 'Security conversion required but not possible',
                '74' => 'Security features not supported',
                '75' => 'Cryptographic failure',
                '76' => 'Cryptographic algorithm not supported',
                '77' => 'Message integrity failure', 
            );

    public function __construct($content) {
        $this->content = $content;
    }

    public function parse() {
        $this->sanitize();

        $this->message_id = $this->findMessageID();

        $this->status = $this->findStatus();
    }


    private function sanitize() {
        // Sépare les parties de mail par boundary
        $this->boundary = '--'.$this->findBoundary();

        // Nettoie les parties en mettant tout sur une ligne
        // pour améliorer la rapidité des regex
        $this->boundaries = $this->splitAndCleanBoundaries($this->boundary);

        // Décodage des textes
        // TODO
    }


    private function findMessageID() {
        if (preg_match('/.*Message-Id: <([^>]*)>.*$/', $this->boundaries[0], $bounce_infos) == 1) {
            $message_id = $bounce_infos[1];
        }

        return $message_id;
    }

    private function findBoundary() {

	$boundary='';

        // Recherche du délimiteur
        preg_match('/boundary="(.*?)"/', $this->content, $preg_results);

        // Si on le trouve, on le renvoie
        if ($preg_results[1] != '')  {
            $boundary = $preg_results[1];
        } else {
            // TODO: Gestion des erreurs ?
        }
        return $boundary;
    }

    private function splitAndCleanBoundaries($boundary_delimiter) {

        // Decoupage du mail par boundary
        $boundaries = array();
        $boundaries_orig = explode($boundary_delimiter, $this->content);

        // Nettoyage du boundary
        foreach($boundaries_orig as $boundary_part) { 
            $boundary = $boundary_part;

            // On met tout le boundary sur une seule ligne
            $boundary = preg_replace('/\n/m', ' ', $boundary);

            // On remplace toutes les tabulations par un espace
            $boundary = preg_replace('/\t/', ' ', $boundary);

            // Suppression des doubles espaces
            $boundary = preg_replace('/\s{2,}/', ' ', $boundary);

            // "Trim"
            $boundary = preg_replace('/^\s*/', '', $boundary);
            $boundary = preg_replace('/\s*$/', '', $boundary);

            // Et on le stocke dans un tableau
            $boundaries[] = $boundary;
        }

        return $boundaries;
    }


    private function findStatus () {
        // Analyse du bounce
        global $bounce_regex;
        
        // Pour le moment, on se sait pas si c'est un bounce hard|soft|spam
        $score = 9;

        // Mail de bounce classique de Postfix
        if (preg_match('/.*-Recipient: rfc822;\s?([^\s]*).*Status: ([\d\.]*).*Diagnostic-Code: (.*)$/', $this->boundaries[2], $bounce_infos) == 1) {

            $this->mailer = "Postfix";

            if (array_key_exists(1, $bounce_infos)) $this->bounced_mail = $bounce_infos[1];
            if (array_key_exists(2, $bounce_infos)) $this->smtp_error_code = $bounce_infos[2];
            if (array_key_exists(3, $bounce_infos)) $this->bounce_server_answer = $bounce_infos[3];

            
            // Construction de la regexp qui nous assure que c'est un hardbounce
            foreach ($bounce_regex as $severity_id => $severity) {
                $bounce_pattern='';
                foreach ($severity as $count => $pattern) {
                    if ($bounce_pattern!="") $bounce_pattern.='|';
                    $bounce_pattern .= $pattern;
                }

                $bounce_pattern = "/^.*($bounce_pattern).*/";
                //print "Pattern : $bounce_pattern\n\n"; 

                if (preg_match($bounce_pattern, $this->boundaries[2])==1) {
                    //print "FOUND!!!!\n\n";
                    $this->smtp_error_code="6.0.".$severity_id;
                    $this->bounce_reason="HardBounce detected by Bounce-Parser";
                    $score=6;
                }
            }

            if ($this->smtp_error_code != NULL && $this->smtp_error_code != '' && $score==9) {
                $score = substr($this->smtp_error_code, 0, 1);
                $this->bounce_reason = $this->bounce_official_reasons[substr(str_replace('.', '', $this->smtp_error_code),1,2)];
            }

        // Test de reconnaissance d'un Out of Office 
        } elseif (false) {


        // Le reste... Bah on sait pas ce que c'est (SPAM?)
        } else {

        }

        return $score;
    }

    public function getStatus() {
        return $this->status;
    }

    public function getMessageID() {
        return $this->message_id;
    }

    public function getErrorCode() {
        return $this->smtp_error_code;
    }

    public function getBouncedEmail() {
        return $this->bounced_mail;
    }

    public function getBounceReason() {
        return $this->bounce_reason;
    }

    public function getServerAnswer() {
        return $this->bounce_server_answer;
    }

    public function getBoundary() {
        return $this->boundary;
    }

    public function getBoundaries($boundary_id) {
        return $this->boundaries[$boundary_id];
    }

}

?>
