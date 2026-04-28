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
        | For local testing, requests with no Origin header will still work.
        | Browser CORS is restricted to localhost origins.
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

    /*
    |--------------------------------------------------------------------------
    | Public Developer API: Today's Featured Alumnus
    |--------------------------------------------------------------------------
    | Endpoint:
    | GET /api/featured-today
    |
    | Header:
    | Authorization: Bearer YOUR_API_KEY
    */
    public function alumni_of_the_day()
    {
        if ($this->input->method(TRUE) !== 'GET') {
            return $this->output_json([
                'status'  => 'error',
                'message' => 'Method not allowed. Use GET.'
            ], 405);
        }

        $ip_address = $this->input->ip_address();

        /*
        |--------------------------------------------------------------------------
        | Basic API Rate Limiting
        |--------------------------------------------------------------------------
        | 100 requests per IP address per hour.
        */
        $recent_requests = $this->db->where('ip_address', $ip_address)
            ->where('accessed_at >=', date('Y-m-d H:i:s', strtotime('-1 hour')))
            ->count_all_results('api_logs');

        if ($recent_requests >= 100) {
            return $this->output_json([
                'status'  => 'error',
                'message' => 'Rate limit exceeded. Please try again later.'
            ], 429);
        }

        $api_key = $this->get_bearer_token();

        if (empty($api_key)) {
            return $this->output_json([
                'status'  => 'error',
                'message' => 'Missing or invalid Authorization header. Use format: Bearer <your_key>'
            ], 401);
        }

        $key_record = $this->Api_model->is_valid_key($api_key);

        if (!$key_record) {
            return $this->output_json([
                'status'  => 'error',
                'message' => 'Invalid or revoked API key.'
            ], 403);
        }

        $today = (new DateTime('now', new DateTimeZone($this->app_timezone)))->format('Y-m-d');
        $featured = $this->Bidding_model->get_full_featured_profile_by_date($today);

        $this->Api_model->log_request(
            $key_record->id,
            'GET /api/featured-today',
            $ip_address
        );

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
    | API Documentation Page
    |--------------------------------------------------------------------------
    | Only developer accounts can view this page.
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