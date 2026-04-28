<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route['default_controller'] = 'auth/login';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
$route['login'] = 'auth/login';
$route['logout'] = 'auth/logout';
$route['register'] = 'auth/register';
$route['forgot-password'] = 'auth/forgot_password';
$route['reset-password'] = 'auth/reset_password';
$route['verify-email'] = 'auth/verify';
$route['auth/verify'] = 'auth/verify';
$route['dashboard'] = 'auth/dashboard';

/*
|--------------------------------------------------------------------------
| Alumni Profile Routes
|--------------------------------------------------------------------------
*/
$route['profile'] = 'profile/index';
$route['profile/save'] = 'profile/save';
$route['profile/delete/(:num)/(:any)'] = 'profile/delete/$1/$2';

/*
|--------------------------------------------------------------------------
| Bidding Routes
|--------------------------------------------------------------------------
*/
$route['bidding'] = 'bidding/index';
$route['bidding/submit'] = 'bidding/submit_bid';
$route['bidding/my-status'] = 'bidding/my_bid_status';
$route['bidding/history'] = 'bidding/history';
$route['featured-today'] = 'bidding/featured_today';

/*
|--------------------------------------------------------------------------
| Developer API Key Routes
|--------------------------------------------------------------------------
*/
$route['developer'] = 'developer/index';
$route['developer/index'] = 'developer/index';
$route['developer/generate'] = 'developer/generate';
$route['developer/revoke/(:num)'] = 'developer/revoke/$1';

/*
|--------------------------------------------------------------------------
| Public API Routes
|--------------------------------------------------------------------------
*/
$route['api-docs'] = 'api/docs';
$route['api/featured-today'] = 'api/alumni_of_the_day';
$route['api/alumni-of-the-day'] = 'api/alumni_of_the_day';
$route['api/alumni_of_the_day'] = 'api/alumni_of_the_day';

/*
|--------------------------------------------------------------------------
| Cron Routes
|--------------------------------------------------------------------------
*/
$route['cron/resolve-winner'] = 'bidding/cron_resolve_winner';