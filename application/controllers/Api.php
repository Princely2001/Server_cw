<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api extends CI_Controller
{
    private $app_timezone = 'Asia/Colombo';

    public function __construct()
    {
        parent::__construct();

        /*
        |--------------------------------------------------------------------------
        | API Security Headers
        |--------------------------------------------------------------------------
        */
        $allowed_origins = [
            'http://localhost',
            'http://127.0.0.1',
            'http://localhost:3000',
            'http://localhost:8080'
        ];

        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

        if (in_array($origin, $allowed_origins, TRUE)) {
            header('Access-Control-Allow-Origin: ' . $origin);
        }

        header('Access-Control-Allow-Methods: GET, OPTIONS');
        header('Access-Control-Allow-Headers: Authorization, Content-Type, X-Requested-With');
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('Referrer-Policy: no-referrer');
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }

        date_default_timezone_set($this->app_timezone);

        $this->load->model('Api_model');
        $this->load->model('Bidding_model');
        $this->load->model('Analytics_model');

        $this->load->library('session');
        $this->load->helper(['url', 'security']);
    }

    private function output_json($data, $status_code = 200)
    {
        return $this->output
            ->set_content_type('application/json', 'utf-8')
            ->set_status_header($status_code)
            ->set_output(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    private function get_bearer_token()
    {
        $auth_header = $this->input->get_request_header('Authorization', TRUE);

        if (empty($auth_header) && isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $auth_header = $_SERVER['HTTP_AUTHORIZATION'];
        }

        if (empty($auth_header) && isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            $auth_header = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        }

        if (empty($auth_header)) {
            return null;
        }

        if (!preg_match('/Bearer\s+(.+)/i', $auth_header, $matches)) {
            return null;
        }

        return trim($matches[1]);
    }

    private function get_filters()
    {
        return [
            'programme'       => $this->input->get('programme', TRUE),
            'graduation_year' => $this->input->get('graduation_year', TRUE),
            'industry_sector' => $this->input->get('industry_sector', TRUE)
        ];
    }

    private function clean_filters($filters)
    {
        $clean = [];

        foreach ($filters as $key => $value) {
            if ($value !== null && trim((string) $value) !== '') {
                $clean[$key] = trim((string) $value);
            }
        }

        return $clean;
    }

    private function log_api($key_record, $endpoint, $status_code = 200)
    {
        if ($key_record && !empty($key_record->id)) {
            $this->Api_model->log_request(
                $key_record->id,
                $endpoint,
                $this->input->ip_address(),
                $this->input->method(TRUE),
                $status_code,
                isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null
            );
        }
    }

    private function rate_limit_check()
    {
        $ip_address = $this->input->ip_address();

        $recent_requests = $this->db->where('ip_address', $ip_address)
            ->where('accessed_at >=', date('Y-m-d H:i:s', strtotime('-1 hour')))
            ->count_all_results('api_logs');

        if ($recent_requests >= 100) {
            return false;
        }

        return true;
    }

    private function require_get_method()
    {
        if ($this->input->method(TRUE) !== 'GET') {
            $this->output_json([
                'status'  => 'error',
                'message' => 'Method not allowed. Use GET.'
            ], 405);
            return false;
        }

        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | API Key + Permission Guard
    |--------------------------------------------------------------------------
    */
    private function require_api_permission($required_permission)
    {
        if (!$this->require_get_method()) {
            return false;
        }

        if (!$this->rate_limit_check()) {
            $this->output_json([
                'status'  => 'error',
                'message' => 'Rate limit exceeded. Please try again later.'
            ], 429);
            return false;
        }

        $api_key = $this->get_bearer_token();

        if (empty($api_key)) {
            $this->output_json([
                'status'  => 'error',
                'message' => 'Missing or invalid Authorization header. Use format: Bearer <your_key>'
            ], 401);
            return false;
        }

        $key_record = $this->Api_model->is_valid_key($api_key);

        if (!$key_record) {
            $this->output_json([
                'status'  => 'error',
                'message' => 'Invalid or revoked API key.'
            ], 403);
            return false;
        }

        $permissions = [];

        if (!empty($key_record->permissions)) {
            $decoded = json_decode($key_record->permissions, TRUE);

            if (is_array($decoded)) {
                $permissions = $decoded;
            }
        }

        /*
         * Backward compatibility:
         * If an old AR key has NULL permissions, allow only Alumni of the Day.
         */
        if (empty($permissions)) {
            $permissions = ['read:alumni_of_day'];
        }

        if (!in_array($required_permission, $permissions, TRUE)) {
            $this->log_api($key_record, uri_string(), 403);

            $this->output_json([
                'status'              => 'error',
                'message'             => 'Forbidden. Your API key does not have the required permission.',
                'required_permission' => $required_permission,
                'your_permissions'    => $permissions
            ], 403);

            return false;
        }

        return $key_record;
    }

    /*
    |--------------------------------------------------------------------------
    | Public API: Alumni of the Day
    |--------------------------------------------------------------------------
    | Required permission: read:alumni_of_day
    */
    public function alumni_of_the_day()
    {
        $key_record = $this->require_api_permission('read:alumni_of_day');

        if (!$key_record) {
            return;
        }

        $today = (new DateTime('now', new DateTimeZone($this->app_timezone)))->format('Y-m-d');
        $featured = $this->Bidding_model->get_full_featured_profile_by_date($today);

        $this->log_api($key_record, 'GET /api/featured-today', 200);

        if (!$featured || empty($featured['profile'])) {
            return $this->output_json([
                'status'  => 'success',
                'message' => 'No alumni featured today.',
                'data'    => null
            ], 200);
        }

        $profile_data = $featured['profile'];

        $response_data = [
            'featured_date' => $today,
            'profile' => [
                'first_name'        => $profile_data->first_name,
                'last_name'         => $profile_data->last_name,
                'email'             => $profile_data->university_email,
                'bio'               => $profile_data->bio,
                'linkedin_url'      => $profile_data->linkedin_url,
                'profile_image_url' => !empty($profile_data->profile_image)
                    ? base_url('uploads/profile_images/' . $profile_data->profile_image)
                    : null
            ],
            'education'      => isset($featured['degrees']) ? $featured['degrees'] : [],
            'employment'     => isset($featured['employment']) ? $featured['employment'] : [],
            'courses'        => isset($featured['courses']) ? $featured['courses'] : [],
            'certifications' => isset($featured['certifications']) ? $featured['certifications'] : [],
            'licences'       => isset($featured['licences']) ? $featured['licences'] : []
        ];

        return $this->output_json([
            'status' => 'success',
            'data'   => $response_data
        ], 200);
    }

    /*
    |--------------------------------------------------------------------------
    | API: Alumni List
    |--------------------------------------------------------------------------
    | Required permission: read:alumni
    */
    public function alumni()
    {
        $key_record = $this->require_api_permission('read:alumni');

        if (!$key_record) {
            return;
        }

        $filters = $this->clean_filters($this->get_filters());

        $limit = (int) $this->input->get('limit', TRUE);
        $offset = (int) $this->input->get('offset', TRUE);

        if ($limit <= 0 || $limit > 200) {
            $limit = 100;
        }

        if ($offset < 0) {
            $offset = 0;
        }

        $rows = $this->Analytics_model->get_alumni_list($filters, $limit, $offset);
        $total = $this->Analytics_model->count_alumni_list($filters);

        $this->log_api($key_record, 'GET /api/alumni', 200);

        return $this->output_json([
            'status' => 'success',
            'filters' => $filters,
            'pagination' => [
                'total'  => (int) $total,
                'limit'  => (int) $limit,
                'offset' => (int) $offset
            ],
            'data' => $rows
        ], 200);
    }

    /*
    |--------------------------------------------------------------------------
    | API: Filter Options
    |--------------------------------------------------------------------------
    | Required permission: read:analytics
    */
    public function analytics_filters()
    {
        $key_record = $this->require_api_permission('read:analytics');

        if (!$key_record) {
            return;
        }

        $this->log_api($key_record, 'GET /api/analytics/filters', 200);

        return $this->output_json([
            'status' => 'success',
            'data'   => $this->Analytics_model->get_filter_options()
        ], 200);
    }

    /*
    |--------------------------------------------------------------------------
    | API: Analytics Summary
    |--------------------------------------------------------------------------
    | Required permission: read:analytics
    */
    public function analytics_summary()
    {
        $key_record = $this->require_api_permission('read:analytics');

        if (!$key_record) {
            return;
        }

        $filters = $this->clean_filters($this->get_filters());
        $summary = $this->Analytics_model->get_summary_stats($filters);

        $this->log_api($key_record, 'GET /api/analytics/summary', 200);

        return $this->output_json([
            'status'  => 'success',
            'filters' => $filters,
            'data'    => $summary
        ], 200);
    }

    /*
    |--------------------------------------------------------------------------
    | API: Full Analytics Payload
    |--------------------------------------------------------------------------
    | Required permission: read:analytics
    */
    public function analytics_full()
    {
        $key_record = $this->require_api_permission('read:analytics');

        if (!$key_record) {
            return;
        }

        $filters = $this->clean_filters($this->get_filters());
        $payload = $this->Analytics_model->get_full_analytics_payload($filters);

        $this->log_api($key_record, 'GET /api/analytics/full', 200);

        return $this->output_json([
            'status' => 'success',
            'data'   => $payload
        ], 200);
    }

    /*
    |--------------------------------------------------------------------------
    | Individual Chart Endpoints
    |--------------------------------------------------------------------------
    */

    public function analytics_industry_distribution()
    {
        $this->chart_response('read:analytics', 'GET /api/analytics/industry-distribution', 'industry_distribution');
    }

    public function analytics_graduation_years()
    {
        $this->chart_response('read:analytics', 'GET /api/analytics/graduation-years', 'graduation_year_distribution');
    }

    public function analytics_job_titles()
    {
        $this->chart_response('read:analytics', 'GET /api/analytics/job-titles', 'job_title_distribution');
    }

    public function analytics_top_employers()
    {
        $this->chart_response('read:analytics', 'GET /api/analytics/top-employers', 'top_employers');
    }

    public function analytics_geographic_distribution()
    {
        $this->chart_response('read:analytics', 'GET /api/analytics/geographic-distribution', 'geographic_distribution');
    }

    public function analytics_certification_trends()
    {
        $this->chart_response('read:analytics', 'GET /api/analytics/certification-trends', 'certification_trends');
    }

    public function analytics_course_trends()
    {
        $this->chart_response('read:analytics', 'GET /api/analytics/course-trends', 'course_trends');
    }

    public function analytics_skills_gap()
    {
        $this->chart_response('read:analytics', 'GET /api/analytics/skills-gap', 'skills_gap');
    }

    public function analytics_top_certifications()
    {
        $this->chart_response('read:analytics', 'GET /api/analytics/top-certifications', 'top_certifications');
    }

    public function analytics_top_courses()
    {
        $this->chart_response('read:analytics', 'GET /api/analytics/top-courses', 'top_courses');
    }

    private function chart_response($permission, $endpoint_label, $chart_type)
    {
        $key_record = $this->require_api_permission($permission);

        if (!$key_record) {
            return;
        }

        $filters = $this->clean_filters($this->get_filters());

        switch ($chart_type) {
            case 'industry_distribution':
                $data = $this->Analytics_model->get_industry_distribution($filters);
                break;

            case 'graduation_year_distribution':
                $data = $this->Analytics_model->get_graduation_year_distribution($filters);
                break;

            case 'job_title_distribution':
                $data = $this->Analytics_model->get_job_title_distribution($filters);
                break;

            case 'top_employers':
                $data = $this->Analytics_model->get_top_employers($filters);
                break;

            case 'geographic_distribution':
                $data = $this->Analytics_model->get_geographic_distribution($filters);
                break;

            case 'certification_trends':
                $data = $this->Analytics_model->get_certification_trends($filters);
                break;

            case 'course_trends':
                $data = $this->Analytics_model->get_course_trends($filters);
                break;

            case 'skills_gap':
                $data = $this->Analytics_model->get_skills_gap_data($filters);
                break;

            case 'top_certifications':
                $data = $this->Analytics_model->get_top_certifications($filters);
                break;

            case 'top_courses':
                $data = $this->Analytics_model->get_top_courses($filters);
                break;

            default:
                $data = [];
                break;
        }

        $this->log_api($key_record, $endpoint_label, 200);

        return $this->output_json([
            'status'  => 'success',
            'filters' => $filters,
            'data'    => $data
        ], 200);
    }

    /*
    |--------------------------------------------------------------------------
    | API Documentation Page
    |--------------------------------------------------------------------------
    */
    public function docs()
    {
        header('Content-Type: text/html; charset=utf-8');
        $this->output->set_content_type('text/html', 'utf-8');

        if (!$this->session->userdata('logged_in')) {
            redirect('auth/login');
            exit;
        }

        if ($this->session->userdata('role') !== 'developer') {
            show_error('Forbidden: API documentation and testing is restricted to Developers only.', 403);
            exit;
        }

        $this->load->view('api/docs');
    }
}