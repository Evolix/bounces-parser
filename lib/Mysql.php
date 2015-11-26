<?php

/**
 * Bibliotheques MySQL (PHP4/PHP5)
 *
 * $Id: Mysql.php 18 2009-12-21 17:21:08Z spalma $
 * 
 * <pre>A definir :
 * define('SERVEUR','localhost');
 * define('SERVEURPORT',3306);
 * define('BASE','example');
 * define('NOM', 'root');
 * define('PASSE', 'nopass');</pre>
 *
 * @author Gregory Colpart <reg@evolix.fr>
 * @author Alexandre Anriot <aanriot@evolix.fr>
 * @copyright Copyright (c) 2004,2005,2006 Evolix
 * @version 1.0
 */

class Mysql
{

	/*
	 * Connexion a une base MySQL 
	 * les constantes SERVEUR, NOM, PASSE et BASE devront etre definies
	 * il convient de les definir dans un fichier connect.php
	 */

	function MyConnect()
	{
		$connexion = mysql_connect(SERVEUR, NOM, PASSE);

		if (! $connexion)
		{
			echo "Une erreur s'est produite : ".mysql_error()."\n";
			exit;
		}

		if (! mysql_select_db(BASE, $connexion))
		{
			echo "Une erreur s'est produite : ".mysql_error()."\n";
			exit;
		}

		return $connexion;
	}


    /**
     *  Executer une requete SQL quelconque
     *
     * A noter que cette fonction peut s'ecrire en une ligne
     * mais reste sous cette forme pour plus de clarté
     *
     * @param resource $connexion
     * @param string $req
     * @return mixed (resource or boolean)
     */
    function MyReq($connexion,$req) {

        if ($query = mysql_query($req,$connexion)) {
            return $query;
        } else {
            // print "erreur requete SQL";
            return FALSE;
        }
    } 


	/*
	 * Requete MySQL optimale renvoyant une seule variable
	 */

	function MyExecReq($connexion,$req) {
		if ($r = mysql_fetch_row(mysql_query($req,$connexion))) {
			return current($r);
		} else {
			return FALSE;
        }
	}

	/*
	 * Requete MySQL renvoyant un objet
	 *
	 * Exemple d'utilisation :
	 * $req = 'SELECT * FROM main'; 
	 * $obj = Mysql::MyObjectReq($con,$req);
	 * print("result = $obj->title");
	 */

	function MyObjectReq($connexion,$req) {
        if ($query = mysql_query($req,$connexion)) {
            return mysql_fetch_object($query);
        } else {
            return FALSE;
        }
	}

	/*
	 * Requete MySQL renvoyant un tableau associatif
	 *
	 * Exemple d'utilisation :
	 * $req = 'SELECT * FROM main'; 
	 * $assoc = Mysql::MyAssocReq($con,$req);
	 * print($assoc['title']);
	 */

	function MyAssocReq($connexion,$req) {
        if ($query = mysql_query($req,$connexion)) {
            return mysql_fetch_assoc($query);
        } else {
            return FALSE;
        }
	}

	/**
	 * Requete insertion MySQL (INSERT ou UPDATE)
	 * renvoie 1 si insertion correcte, 0 sinon
	 */
	function MyInsertReq($connexion,$req) {
		return (mysql_query(Html::sqlclean($req),$connexion)) ? TRUE : FALSE;
	}

    /**
     * Executer une requete SQL renvoyant UNE réponse sur plusieurs lignes
     *
     * @param resource $connexion
     * @param string $req
     * @return array
     */
    function MyGetArray($connexion,$req) {
        
        $result = array();
        $query = Mysql::MyReq($connexion,$req);
        while ($res = mysql_fetch_row($query)) {
            array_push($result,current($res));
        }
        return $result;
    }

    /**
     * Executer une requete SQL renvoyant DEUX réponses sur plusieurs lignes
     *
     * @param resource $connexion
     * @param string $req
     * @return array
     */
    function MyGetHash($connexion,$req) {
        
        $result = array();
        $query = Mysql::MyReq($connexion,$req);
        while ($res = mysql_fetch_row($query)) {
            $result[current($res)] = next($res);
        }
        return $result;
    }

    /**
     * Parcourir les differentes lignes pour les requetes complexes
     * (plusieurs réponses et plusieurs lignes)
     *
     * Exemple d'utilisation :
     * while ($data = Mysql::MyFetchObject($query)) { ...
     *
     * @param resource
     * @return object
     */
	function MyFetchObject($query) {
		return mysql_fetch_object($query);
	}



}

?>
