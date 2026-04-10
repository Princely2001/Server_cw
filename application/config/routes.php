<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route['default_controller'] = 'auth/login';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

// Authentication Routes
$route['login'] = 'auth/login';
$route['logout'] = 'auth/logout';
$route['register'] = 'auth/register';
$route['forgot-password'] = 'auth/forgot_password';
$route['reset-password'] = 'auth/reset_password';
$route['verify-email'] = 'auth/verify';
$route['dashboard'] = 'auth/dashboard';

// Bidding Routes
$route['bidding'] = 'bidding/index';
$route['bidding/submit'] = 'bidding/submit_bid';
$route['bidding/my-status'] = 'bidding/my_bid_status';
$route['featured-today'] = 'bidding/featured_today';
$route['api-docs'] = 'api/docs';

// The actual JSON endpoint returning the winner data
$route['api/featured-today'] = 'api/alumni_of_the_day'; 
// cron routes
$route['cron/resolve-winner'] = 'bidding/cron_resolve_winner';