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
$route['profile/index'] = 'profile/index';
$route['profile/update-basic'] = 'profile/update_basic';

$route['profile/degrees/add'] = 'profile/add_degree';
$route['profile/degrees/edit/(:num)'] = 'profile/edit_degree/$1';
$route['profile/degrees/delete/(:num)'] = 'profile/delete_degree/$1';

$route['profile/certifications/add'] = 'profile/add_certification';
$route['profile/certifications/edit/(:num)'] = 'profile/edit_certification/$1';
$route['profile/certifications/delete/(:num)'] = 'profile/delete_certification/$1';

$route['profile/licences/add'] = 'profile/add_licence';
$route['profile/licences/edit/(:num)'] = 'profile/edit_licence/$1';
$route['profile/licences/delete/(:num)'] = 'profile/delete_licence/$1';

$route['profile/courses/add'] = 'profile/add_course';
$route['profile/courses/edit/(:num)'] = 'profile/edit_course/$1';
$route['profile/courses/delete/(:num)'] = 'profile/delete_course/$1';

$route['profile/employment/add'] = 'profile/add_employment';
$route['profile/employment/edit/(:num)'] = 'profile/edit_employment/$1';
$route['profile/employment/delete/(:num)'] = 'profile/delete_employment/$1';

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
| API Documentation
|--------------------------------------------------------------------------
*/
$route['api-docs'] = 'api/docs';
$route['api/docs'] = 'api/docs';

/*
|--------------------------------------------------------------------------
| Public API Routes
|--------------------------------------------------------------------------
*/
$route['api/featured-today'] = 'api/alumni_of_the_day';
$route['api/alumni-of-the-day'] = 'api/alumni_of_the_day';
$route['api/alumni_of_the_day'] = 'api/alumni_of_the_day';

$route['api/alumni'] = 'api/alumni';

$route['api/analytics/filters'] = 'api/analytics_filters';
$route['api/analytics/summary'] = 'api/analytics_summary';
$route['api/analytics/full'] = 'api/analytics_full';

$route['api/analytics/industry-distribution'] = 'api/analytics_industry_distribution';
$route['api/analytics/graduation-years'] = 'api/analytics_graduation_years';
$route['api/analytics/job-titles'] = 'api/analytics_job_titles';
$route['api/analytics/top-employers'] = 'api/analytics_top_employers';
$route['api/analytics/geographic-distribution'] = 'api/analytics_geographic_distribution';
$route['api/analytics/certification-trends'] = 'api/analytics_certification_trends';
$route['api/analytics/course-trends'] = 'api/analytics_course_trends';
$route['api/analytics/skills-gap'] = 'api/analytics_skills_gap';
$route['api/analytics/top-certifications'] = 'api/analytics_top_certifications';
$route['api/analytics/top-courses'] = 'api/analytics_top_courses';

/*
|--------------------------------------------------------------------------
| Analytics Web Dashboard Routes
|--------------------------------------------------------------------------
*/
$route['analytics'] = 'analytics/dashboard';
$route['analytics/dashboard'] = 'analytics/dashboard';
$route['analytics/alumni'] = 'analytics/alumni';
$route['analytics/reports'] = 'analytics/reports';
$route['analytics/export-csv'] = 'analytics/export_csv';
$route['analytics/export-summary-csv'] = 'analytics/export_summary_csv';
$route['analytics/chart-data'] = 'analytics/chart_data';

/*
|--------------------------------------------------------------------------
| Cron Routes
|--------------------------------------------------------------------------
*/
$route['cron/resolve-winner'] = 'bidding/cron_resolve_winner';