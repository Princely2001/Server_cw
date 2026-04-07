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

// Public Developer API
$route['api/featured-today'] = 'bidding/get_todays_winner';

// Cron Route
$route['cron/resolve-winner'] = 'bidding/cron_resolve_winner';