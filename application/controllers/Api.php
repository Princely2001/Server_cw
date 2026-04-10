<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api extends CI_Controller {

    public function __construct() {
        parent::__construct();
        
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE');
        header('Access-Control-Allow-Headers: Authorization, Content-Type, X-Requested-With');
        
        // This sets the default content type to JSON for the entire controller
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }

        $this->load->model('Api_model');
        $this->load->model('Bidding_model');
        
        // Load the session library and URL helper so we can use them in the docs() method
        $this->load->library('session');
        $this->load->helper('url');
    }

    private function output_json($data, $status_code = 200) {
        $this->output
            ->set_content_type('application/json')
            ->set_status_header($status_code)
            ->set_output(json_encode($data));
    }

    // This endpoint remains accessible to clients via Bearer Token
    public function alumni_of_the_day() {
        $ip_address = $this->input->ip_address();
        
        $recent_requests = $this->db->where('ip_address', $ip_address)
                                    ->where('accessed_at >=', date('Y-m-d H:i:s', strtotime('-1 hour')))
                                    ->count_all_results('api_logs');
        
        if ($recent_requests > 100) {
            return $this->output_json([
                'status' => 'error', 
                'message' => 'Rate limit exceeded. Please try again later.'
            ], 429);
        }

        $auth_header = $this->input->get_request_header('Authorization');
        
        if (empty($auth_header) && isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $auth_header = $_SERVER['HTTP_AUTHORIZATION'];
        }

        if (empty($auth_header) || !preg_match('/Bearer\s(\S+)/', $auth_header, $matches)) {
            return $this->output_json([
                'status' => 'error', 
                'message' => 'Missing or invalid Authorization header. Use format: Bearer <your_key>'
            ], 401);
        }

        $api_key = $matches[1];
        $key_record = $this->Api_model->is_valid_key($api_key);

        if (!$key_record) {
            return $this->output_json([
                'status' => 'error', 
                'message' => 'Invalid or Revoked API Key.'
            ], 403);
        }

        $this->Api_model->log_request($key_record->id, 'GET /api/alumni_of_the_day', $ip_address);

        // Fetch Today's Winner (Safely using existing structure in Bidding Model)
        $today = (new DateTime('now', new DateTimeZone('Asia/Colombo')))->format('Y-m-d');
        $featured = $this->Bidding_model->get_full_featured_profile_by_date($today);

        if (!$featured || empty($featured['profile'])) {
            return $this->output_json([
                'status' => 'success', 
                'message' => 'No alumni featured today.', 
                'data' => null
            ], 200);
        }

        // Compile payload
        $profile_data = $featured['profile'];
        $response_data = [
            'first_name'   => $profile_data->first_name,
            'last_name'    => $profile_data->last_name,
            'email'        => $profile_data->university_email,
            'bio'          => $profile_data->bio,
            'linkedin_url' => $profile_data->linkedin_url,
            'profile_image_url' => ($profile_data->profile_image) 
                                   ? base_url('uploads/profile_images/' . $profile_data->profile_image) 
                                   : null,
            'education'    => $featured['degrees'],
            'employment'   => $featured['employment'],
            'courses'      => $featured['courses'],
            'certifications'=> $featured['certifications']
        ];

        return $this->output_json([
            'status' => 'success', 
            'data'   => $response_data
        ], 200);
    }
     
    // This section is strictly locked down to developers
    public function docs() {
        // OVERRIDE JSON HEADER: Force the browser to read this specific method as HTML 
        // We do this BEFORE the security checks so that show_error() renders correctly.
        header('Content-Type: text/html');
        $this->output->set_content_type('text/html');

        // STRICT ACCESS CONTROL: Require login
        if (!$this->session->userdata('logged_in')) {
            redirect('auth/login');
            exit;
        }

        // STRICT ACCESS CONTROL: Only allow developers
        if ($this->session->userdata('role') !== 'developer') {
            show_error('Forbidden: API Documentation and testing is restricted to Developers only.', 403);
            exit;
        }

        // Load the Swagger/API Docs view if they pass the checks
        $this->load->view('api/docs');
    }
}