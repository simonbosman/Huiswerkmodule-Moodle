<?php

/**
 * @author Alfa & Ariss
 * @author Webskoel B.V.
 * @author Simon Bosman
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package moodle auth entree
 *
 * Authentication Plugin: Entree Authentication
 *
 * Authenticates against an Entree Server
 *
 * 2009-03-04 Initial Version
 * 2011-04-07 Moodle 1.x plugin made suitable for Moodle 2.x
 * 2011-11-21 Logout support
 * 2014-12-10 Moodle 2.6 and upwards support
 *
 * Note: auth/entree is not to most ideal place for this module to reside as it doesn't offer an authentication method
 * However, it is a logical place for administrators to look for this feature and in this way it is possible 
 * to implement the SSO functionality as a plugin.
 */
if (! defined ( 'MOODLE_INTERNAL' )) {
	die ( 'Direct access to this script is forbidden.' ); // / It must be included from a Moodle page
}

require_once ($CFG->libdir . '/authlib.php');

/**
 * Entree SSO authentication plugin.
 */
class auth_plugin_entree extends auth_plugin_base {
	
	/**
	 * Constructor.
	 */
	function auth_plugin_entree() {
		$this->authtype = 'entree';
		$this->config = get_config ( 'auth/entree' );
	}
	
	/**
	 * Returns true if the username and password work and false if they are
	 * wrong or don't exist.
	 *
	 * @param string $username
	 *        	The username (with system magic quotes)
	 * @param string $password
	 *        	The password (with system magic quotes)
	 * @return bool Authentication success or failure.
	 */
	function user_login($username, $password) {
		return false;
	}
	
	/**
	 * Returns true if this authentication plugin is 'internal'.
	 *
	 * @return bool
	 */
	function is_internal() {
		return false;
	}
	
	/**
	 * Returns true if this authentication plugin can change the user's
	 * password.
	 *
	 * @return bool
	 */
	function can_change_password() {
		return false;
	}
	
	/**
	 * Hook for overriding the loginpage. Function is called before the login page
	 * Function sets the needed SSO prelogin cookie from kennisnet
	 * see http://developers.wiki.kennisnet.nl/index.php/KNF:SSOnotifications
	 * 
	 */
	
	function loginpage_hook()  {
		$config = get_config ( 'auth/entree' );
		if (empty ( $_GET ['testsession'] ) && $_SERVER['REQUEST_METHOD'] === 'GET') {
			echo "<div id=\"ssonot\" style=\"display: none;\"></div>";
			echo "<script type=\"text/javascript\" charset=\"utf-8\">";
			echo "function createCookie(name, value)";
			echo "{document.cookie = name+\"=\"+value+\"; path=/\";}";
			echo "function readCookie(name){";
			echo "var nameEQ = name + \"=\";";
			echo "var ca = document.cookie.split(';');";
			echo "for(var i=0;i < ca.length;i++){";
			echo "var c = ca[i];";
			echo "while (c.charAt(0)==' ')";
			echo "c = c.substring(1,c.length);";
			echo "if (c.indexOf(nameEQ) == 0)";
			echo "return c.substring(nameEQ.length,c.length);";
			echo "}	return null; }";
			echo "if (readCookie('entreePrelogin') != 'true'){";
			echo "createCookie('entreePrelogin', 'true');";
			echo "document.getElementById('ssonot').innerHTML = '<iframe src=\"".$config->login.$config->vleid."&amp;url=".$config->callback."\"></iframe>';";
			echo "}</script>";
			flush ();
			sleep ( 2 );
		}
	}
	
	/**
	 * Hook for overriding behaviour of logout page.
	 * This method is called from login/logout.php page for all enabled auth plugins.
	 *
	 * @global object
	 * @global string
	 *        
	 *         Hook is used to call the logout url for entree users, except for guests
	 *         Cookie is only deleted when Entree SSO parameters are set
	 *        
	 *         The logout url is called by loading an iframe immediately, the iframe is loaded in the hook because:
	 *         1. no patching required
	 *         2. we don't know where the user will be redirected to next, so it isn't possible to
	 *         to set the iframe in the "next" page
	 */
	function prelogout_hook() {
		$config = get_config ( 'auth/entree' );
		// isguestuser() is still set to the previous user, so it can't be used!
		if ($user->username != 'guest' && isset ( $config->logout )) {
			echo "<script type=\"text/javascript\" charset=\"utf-8\">";
			echo "function createCookie(name, value)";
			echo "{document.cookie = name+\"=\"+value+\"; path=/\";}";
			echo "function readCookie(name){";
			echo "var nameEQ = name + \"=\";";
			echo "var ca = document.cookie.split(';');";
			echo "for(var i=0;i < ca.length;i++){";
			echo "var c = ca[i];";
			echo "while (c.charAt(0)==' ')";
			echo "c = c.substring(1,c.length);";
			echo "if (c.indexOf(nameEQ) == 0)";
			echo "return c.substring(nameEQ.length,c.length);";
			echo "}	return null; }";
			echo "if (readCookie('entreePrelogin') == 'true'){";
			echo "createCookie('entreePrelogin', 'false');";
			echo "}</script>";
			echo '<body><html><br/><iframe src="' . $config->logout . '" frameborder="0px;" width="0px;" height="0px;"></iframe>';
			$padstr = str_pad ( "", 2048, "&nbsp;" );
			echo $padstr;
			echo "</body></html>";
			flush ();
			// Wait for two seconds for the iframe to actually load
			sleep ( 2 );
		}
	}
	
	/**
	 * Prints a form for configuring this authentication plugin.
	 *
	 * This function is called from admin/auth.php, and outputs a full page with
	 * a form for configuring this plugin.
	 *
	 * @param array $page
	 *        	An object containing all the data for this page.
	 */
	function config_form($config, $err, $user_fields) {
		include "config.html";
	}
	
	/**
	 * Processes and stores configuration data for this authentication plugin.
	 */
	function process_config($config) {
		// set to defaults if undefined
		if (! isset ( $config->notification )) {
			$config->notification = 'https://aselect.entree.kennisnet.nl/aselectserver/server/ssonotificationservice?elo=';
		}
		if (! isset ( $config->cookiemonster )) {
			$config->cookiemonster = 'https://aselect.entree.kennisnet.nl/aselectserver/server/cookiemonster';
		}
		if (!isset($config->login)) {
			$config->login = 'https://aselect.entree.kennisnet.nl/openaselect/profiles/entree?id=';
		}		
		if (! isset ( $config->logout )) {
			$config->logout = 'https://aselect.entree.kennisnet.nl/openaselect/profiles/entree/logout';
		}
		if (!isset($config->callback)) {
			$config->callback = 'https://www.yourmoodlesite.com/auth/entree/callback.php';
		}
		if (! isset ( $config->secret )) {
			$config->secret = '';
		}
		if (! isset ( $config->vleid )) {
			$config->vleid = '';
		}
		if (! isset ( $config->brin )) {
			$config->brin = '';
		}
	
		// save settings
		set_config ( 'notification', $config->notification, 'auth/entree' );
		set_config ( 'cookiemonster', $config->cookiemonster, 'auth/entree' );
		set_config ( 'login', $config->login, 'auth/entree' );
		set_config ( 'logout', $config->logout, 'auth/entree' );
		set_config ( 'callback', $config->callback, 'auth/entree' );
		set_config ( 'secret', $config->secret, 'auth/entree' );
		set_config ( 'vleid', $config->vleid, 'auth/entree' );
		set_config ( 'brin', $config->brin, 'auth/entree' );
		
		return true;
	}
}// End of the class