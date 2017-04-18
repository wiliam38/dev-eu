<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Cookie helper.
 *
 * @package    Kohana
 * @category   Helpers
 * @author     Kohana Team
 * @copyright  (c) 2008-2011 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Kohana_Cookie {

	/**
	 * @var  string  Magic salt to add to the cookie
	 */
	public static $salt = NULL;

	/**
	 * @var  integer  Number of seconds before the cookie expires
	 */
	public static $expiration = 0;

	/**
	 * @var  string  Restrict the path that the cookie is available to
	 */
	public static $path = '/';

	/**
	 * @var  string  Restrict the domain that the cookie is available to
	 */
	public static $domain = NULL;

	/**
	 * @var  boolean  Only transmit cookies over secure connections
	 */
	public static $secure = FALSE;

	/**
	 * @var  boolean  Only transmit cookies over HTTP, disabling Javascript access
	 */
	public static $httponly = FALSE;

	/**
	 * Gets the value of a signed cookie. Cookies without signatures will not
	 * be returned. If the cookie signature is present, but invalid, the cookie
	 * will be deleted.
	 *
	 *     // Get the "theme" cookie, or use "blue" if the cookie does not exist
	 *     $theme = Cookie::get('theme', 'blue');
	 *
	 * @param   string  cookie name
	 * @param   mixed   default value to return
	 * @return  string
	 */
	public static function get($key, $default = NULL)
	{
		if ( ! isset($_COOKIE[$key]))
		{
			// The cookie does not exist
			return $default;
		}

		// Get the cookie value
		$cookie = $_COOKIE[$key];

		// Find the position of the split between salt and contents
		$split = strlen(Cookie::salt($key, NULL));

		if (isset($cookie[$split]) AND $cookie[$split] === '~')
		{
			// Separate the salt and the value
			list ($hash, $value) = explode('~', $cookie, 2);

			if (Cookie::salt($key, $value) === $hash)
			{
				// Cookie signature is valid
				return $value;
			}

			// The cookie signature is invalid, delete it
			Cookie::delete($key);
		}

		return $default;
	}

	/**
	 * Sets a signed cookie. Note that all cookie values must be strings and no
	 * automatic serialization will be performed!
	 *
	 *     // Set the "theme" cookie
	 *     Cookie::set('theme', 'red');
	 *
	 * @param   string   name of cookie
	 * @param   string   value of cookie
	 * @param   integer  lifetime in seconds
	 * @return  boolean
	 * @uses    Cookie::salt
	 */
	public static function set($name, $value, $expiration = NULL)
	{
		if ($expiration === NULL)
		{
			// Use the default expiration
			$expiration = Cookie::$expiration;
		}

		if ($expiration !== 0)
		{
			// The expiration is expected to be a UNIX timestamp
			$expiration += time();
		}

		// Add the salt to the cookie value
		$value = Cookie::salt($name, $value).'~'.$value;

		return setcookie($name, $value, $expiration, Cookie::$path, Cookie::$domain, Cookie::$secure, Cookie::$httponly);
	}

	/**
	 * Deletes a cookie by making the value NULL and expiring it.
	 *
	 *     Cookie::delete('theme');
	 *
	 * @param   string   cookie name
	 * @return  boolean
	 * @uses    Cookie::set
	 */
	public static function delete($name)
	{
		// Remove the cookie
		unset($_COOKIE[$name]);

		// Nullify the cookie and make it expire
		return setcookie($name, NULL, -86400, Cookie::$path, Cookie::$domain, Cookie::$secure, Cookie::$httponly);
	}

	/**
	 * Generates a salt string for a cookie based on the name and value.
	 *
	 *     $salt = Cookie::salt('theme', 'red');
	 *
	 * @param   string   name of cookie
	 * @param   string   value of cookie
	 * @return  string
	 */
	public static function salt($name, $value)
	{
		// Require a valid salt
		if ( ! Cookie::$salt)
		{
			throw new Kohana_Exception('A valid cookie salt is required. Please set Cookie::$salt.');
		}

		// Determine the user agent		
		// Edited by Insisoft, SIA
		//$agent = isset($_SERVER['HTTP_USER_AGENT']) ? strtolower($_SERVER['HTTP_USER_AGENT']) : 'unknown';
		$browser_data = Cookie::getBrowser();
		$agent = 'unknown';
		if (count($browser_data) > 0) {
			$agent = $browser_data['name'].' ('.ucfirst($browser_data['platform']).')';
		}

		return sha1($agent.$name.$value.Cookie::$salt);
	}
	
	/*
	 * 
	 * GET BROWSER DATA
	 * 
	 * Created by Insisoft, SIA
	 * 
	 */
	public static function getBrowser() 
	{ 
	    $u_agent = $_SERVER['HTTP_USER_AGENT']; 
	    $bname = 'Unknown';
	    $platform = 'Unknown';
	    $version= "";
	
	    //First get the platform?
	    if (preg_match('/linux/i', $u_agent)) {
	        $platform = 'linux';
	    }
	    elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
	        $platform = 'mac';
	    }
	    elseif (preg_match('/windows|win32/i', $u_agent)) {
	        $platform = 'windows';
	    }
	    
	    // Next get the name of the useragent yes seperately and for good reason
	    if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent)) 
	    { 
	        $bname = 'Internet Explorer'; 
	        $ub = "MSIE"; 
	    } 
	    elseif(preg_match('/Firefox/i',$u_agent)) 
	    { 
	        $bname = 'Mozilla Firefox'; 
	        $ub = "Firefox"; 
	    } 
	    elseif(preg_match('/Chrome/i',$u_agent)) 
	    { 
	        $bname = 'Google Chrome'; 
	        $ub = "Chrome"; 
	    } 
	    elseif(preg_match('/Safari/i',$u_agent)) 
	    { 
	        $bname = 'Apple Safari'; 
	        $ub = "Safari"; 
	    } 
	    elseif(preg_match('/Opera/i',$u_agent)) 
	    { 
	        $bname = 'Opera'; 
	        $ub = "Opera"; 
	    } 
	    elseif(preg_match('/Netscape/i',$u_agent)) 
	    { 
	        $bname = 'Netscape'; 
	        $ub = "Netscape"; 
	    } else {
	    	$bname = 'unknown'; 
	        $ub = "unknown"; 
	    }
	    
	    // finally get the correct version number
	    $known = array('Version', $ub, 'other');
	    $pattern = '#(?<browser>' . join('|', $known) .
	    ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
	    if (!preg_match_all($pattern, $u_agent, $matches)) {
	        // we have no matching number just continue
	    }
	    
	    // see how many we have
	    $i = count($matches['browser']);
		if ($i > 0) {
		    if ($i != 1) {
		        //we will have two since we are not using 'other' argument yet
		        //see if version is before or after the name
		        if (strripos($u_agent,"Version") < strripos($u_agent,$ub)){
		            $version= $matches['version'][0];
		        }
		        else {
		            $version= $matches['version'][1];
		        }
		    }
		    else {
		        $version= $matches['version'][0];
		    }
		} else {
			$version = 'unknown';
		}
	    
	    // check if we have a number
	    if ($version==null || $version=="") {$version="?";}
	    
	    return array(
	        'userAgent' => $u_agent,
	        'name'      => $bname,
	        'version'   => $version,
	        'platform'  => $platform,
	        'pattern'    => $pattern
	    );
	} 

} // End cookie
