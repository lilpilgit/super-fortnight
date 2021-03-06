#!/usr/local/bin/php-cgi -f
<?php
/*   
    The /usr/local/etc/php.ini file contains the include_path entry
    which specifies a list of directories, separated by ":", where the functions require(),
    require_once(), include(), fopen() etc ... search for required files. In the case of a
    fresh installation of pfSense, the paths included are:
    include_path = ".:/etc/inc:/usr/local/www:/usr/local/captiveportal:/usr/local/pkg:
                      /usr/local/www/classes:/usr/local/www/classes/Form:/usr/local/share/pear:
                      /usr/local/share/openssl_x509_crl/"
    Inside / etc / inc there are the php filter.inc and config.inc files necessary for the function
    of the script.
*/

#require_once("globals.inc");
require_once("filter.inc");
#require_once("util.inc");
require_once("config.inc"); /* config.inc ==> config.lib.inc */

global $config;
global $result;
$result = array("success" => false); #by default there is an error...pessimistic approach

if (count($argv) == 1){
	echo "Pass an argument please!\n";
	echo "enable: enable rules\n";
	echo "disable: disable rules\n";
	exit(1);
}

$arg1 = $argv[1];

/***** 

 Parse /cf/conf/config.xml
 
 From source code https://github.com/pfsense/pfsense/blob/master/src/etc/inc/config.lib.inc:
 config/parse_config
 * NAME
 *   parse_config - Read in config.cache or config.xml if needed and return $config array
 * INPUTS
 *   $parse       - boolean to force parse_config() to read config.xml and generate config.cache
 * RESULT
 *   $config      - array containing all configuration variables

 ******/ 
$config = parse_config(true);
$track_id_LAN57 = '1596729173';  
$track_id_LAN58 = '1596738586';


function isolateLAN57($isolate = 'enable') {
	
	global $track_id_LAN57;
	global $track_id_LAN58;
	global $config;
	global $result;

	foreach ($config[filter][rule] as &$value) {
		
		if (strcmp($value[tracker], $track_id_LAN57) === 0 ) {
    		
		//this is the rule on lan57 that allow only packets inside LAN_57 

			if (strcmp($isolate,'disable') === 0) {
				
            			$value[disabled] = true;
			
			} else {
				
				unset($value[disabled]);
			}

		} elseif (strcmp($value[tracker], $track_id_LAN58) === 0 ) {
			
		//this is the rule on lan58 that block packets comes from any to LAN_57

                        if (strcmp($isolate,'disable') === 0) {

                                $value[disabled] = true;

                        } else {

                                unset($value[disabled]);
                        }

		}
	}
	
	# modify result's array
	$result["success"] = true;
}	
	
isolateLAN57($arg1);

write_config();


echo json_encode($result);

$retval |= filter_configure(); // https://github.com/pfsense/pfsense/blob/master/src/etc/inc/filter.inc


exit($retval);

?>
