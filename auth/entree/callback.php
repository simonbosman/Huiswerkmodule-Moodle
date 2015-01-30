<?php

/**
 * @author Alfa & Ariss
 * @author Webskoel B.V.
 * @author Simon Bosman
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package moodle auth entree
 *
 * Entree Callback
 * Gets called from entree, 
 * gathers the required (user) information,
 * does a http request
 * and subsequently does a redirect
 *
 * 2009-03-04 Initial Version
 * 2011-04-07 Moodle 1.x plugin made suitable for Moodle 2.x
 * 2011-11-21 Logout support
 * 2014-12-10 Moodle 2.6 and upwards support
 *
 * TODO: Error messages in multiple languages
 */
    
require_once('../../config.php');
require_login();

$debug=0;

GLOBAL $SITE, $USER, $CFG;
if(isguestuser())
{
        header("Location: ../../");
        exit();
}
$config = get_config('auth/entree');
$errormsg = 0;
        
//Get the base URL,VLEID,BRIN and Shared Secret
$base_url=$config->cookiemonster;
if (empty($base_url))
    $errormsg = 'Cookiemonster setting empty, please contact your administrator !';
$shared_secret=$config->secret;
if (empty($shared_secret))
    $errormsg = 'Secret setting empty, please contact your administrator !';
$vleid=$config->vleid;
if (empty($vleid))
    $errormsg = 'VLE Id setting empty, please contact your administrator !';
$brin=$config->brin;
if (empty($brin))
    $errormsg = 'BRIN setting empty, please contact your administrator !';

//Get the remote id & uid
$rid=$_GET['rid'];
$uid=$USER->username.'@'.$vleid;

 //Build attributes
$employeeNumber=$USER->id;
$nlEduPersonProfileId=$USER->id;
$givenName=$USER->firstname;
$sn=$USER->lastname;
/* The role id of a teacher is usually 3, and the role id of a student is usually 5, 
 * but you can test this looking at the table in Site Administration-> Users -> Permissions -> Define Roles
 */
if(user_has_role_assignment($USER->id, 5)){
	$eduPersonAffiliation='student';
} 
else if (user_has_role_assignment($USER->id, 3)){
	$eduPersonAffiliation='employee';
}
else {
	$eduPersonAffiliation='student';
}	
$nlEduPersonHomeOrganizationId=$brin;       
$nlEduPersonHomeOrganization=$SITE->fullname;
$email=$USER->email;
$attributes="employeeNumber=$employeeNumber&givenName=$givenName&sn=$sn&eduPersonAffiliation=$eduPersonAffiliation&nlEduPersonHomeOrganizationId=$nlEduPersonHomeOrganizationId&nlEduPersonHomeOrganization=$nlEduPersonHomeOrganization&nlEduPersonProfileId=$USER->id";
if(isset($email))
{
	$attributes = "$attributes&mail=$email";
}
$attributes_enc=urlencode($attributes);

if (empty($errormsg)) {

    //Build the URL
   $url="$base_url?rid=$rid&uid=$uid&shared_secret=$shared_secret&attributes=$attributes_enc";

    //Build the curl
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_POST, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); 

    //Execute and get output
    $output = curl_exec($ch);      
    curl_close($ch);
    $output_dec=urldecode($output);
    $redirect=substr($output_dec,13);
    if (!$debug) {
    //  header("Location: $redirect");
    // Not used, a http redirect breaks iframes and therefor disturbs content on first access
        echo '<html><head><meta http-equiv="REFRESH" content="0;url='.$redirect.'"> </head></html>';
    } else {
        echo '<html><body><table><tr>';
        echo '<tr><td>Shared Secret</td><td>'.$shared_secret.'</td></tr>';
        echo '<tr><td>VLE Id</td><td>'.$vleid.'</td></tr>';
        echo '<tr><td>BRIN</td><td>'.$brin.'</td></tr>';
        echo '<tr><td>Atributes</td><td>'.$attributes_enc.'</td></tr>';
        echo '<tr><td>URL</td><td>'.$url.'</td></tr>';
        echo '</table>';
        echo "<br><a href='$redirect'>go</a>";
        echo '</body></html>';
    }
} else {
    echo '<table><tr>';
    echo '<tr><td>VLE Id</td><td>'.$vleid.'</td></tr>';
    echo '<tr><td>BRIN</td><td>'.$brin.'</td></tr>';
    echo '<tr><td>Atributes</td><td>'.$attributes_enc.'</td></tr>';
    echo $errormsg."<br/>";
}
  
?>