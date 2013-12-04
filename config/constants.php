<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
define('FILE_READ_MODE', 0644);
define('FILE_WRITE_MODE', 0666);
define('DIR_READ_MODE', 0755);
define('DIR_WRITE_MODE', 0777);

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/

define('FOPEN_READ', 							'rb');
define('FOPEN_READ_WRITE',						'r+b');
define('FOPEN_WRITE_CREATE_DESTRUCTIVE', 		'wb'); // truncates existing file data, use with care
define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE', 	'w+b'); // truncates existing file data, use with care
define('FOPEN_WRITE_CREATE', 					'ab');
define('FOPEN_READ_WRITE_CREATE', 				'a+b');
define('FOPEN_WRITE_CREATE_STRICT', 			'xb');
define('FOPEN_READ_WRITE_CREATE_STRICT',		'x+b');

define('APP_DOMAIN_LINK',"snapittoday.com");


define('FB_APPID','127409457364009');
define('FB_SECRET','d85747b3cf547800c8653de33d0770ad');

define('TW_APPID','eX5CKmOfXvYEHYQHAcnyZw');
define('TW_SECRET','LiR1FJA1DsdtGajwRxeEWMVyIpoHWc7swcAIyXuaDc');

define("IN_APPID",'w99pxd2mhu3y');
define('IN_SECRET','PNTbSxUOoWTDKo8X');

define("GM_SITEID",'16118413262428341303');
define("GM_APPID",'16118413262428341303');
define('GM_SECRET','t6mpkZ5R0xo=');


define('GMAP_KEY','ABQIAAAAeNP67zzDfiHO9x8bftOY2hR2xb83VVuvy_z2dBdLSs4PrqlsDRTq2xdzrgkulx3f5mTw1MTDO-tH9Q');

define("CS_TELEPHONE","+91-92-4340-4342");
define("CS_EMAIL","hello@snapittoday.com");

define('REQUEST_URI',$_SERVER['REQUEST_URI']);

define('IMAGES_URL',"http://static.snapittoday.com/");

define('ERP_IMAGES_URL',"http://".$_SERVER['HTTP_HOST']."/images/erp_images/");
define('ERP_PHYSICAL_IMAGES',"images/erp_images/");



define('HTTP_IS_AJAX', isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');

define('GLOBAL_BATCH_ID', 5000);

/* End of file constants.php */
/* Location: ./system/application/config/constants.php */