<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Developer extends CI_Controller {

    public function __construct() {
        parent::__construct();
        
        // Protect the entire controller - must be logged in
        if (!$this->session->userdata('logged_in')) {
            redirect('auth/login');
        }
        
        $this->load->model('Api_model');
        $this->load->helper(['form', 'url', 'security']);
        $this->load->library('session');
    }

    // Main Developer Dashboard View
    public function index() {
        $user_id = $this->session->userdata('user_id');
        
        $data['title'] = 'Developer API Dashboard';
        $data['api_keys'] = $this->Api_model->get_user_keys($user_id);
        $data['logs'] = $this->Api_model->get_usage_logs($user_id);
        
        $this->load->view('developer/index', $data);
    }

    // Generate a new API Key
    public function generate() {
        if ($this->input->method() === 'post') {
            $user_id = $this->session->userdata('user_id');
            
            // Optional: Limit users to a maximum of 5 keys to prevent spam
            $current_keys = $this->Api_model->get_user_keys($user_id);
            if (count($current_keys) >= 5) {
                $this->session->set_flashdata('error', 'You have reached the maximum limit of 5 API keys.');
            } else {
                $this->Api_model->generate_key($user_id);
                $this->session->set_flashdata('success', 'New API Key generated successfully.');
            }
        }
        redirect('developer/index');
    }

    // Revoke an API Key
    public function revoke($key_id) {
        $user_id = $this->session->userdata('user_id');
        
        // Model handles the user_id check to prevent revoking other people's keys
        if ($this->Api_model->revoke_key($key_id, $user_id)) {
            $this->session->set_flashdata('success', 'API Key has been revoked. It can no longer be used.');
        } else {
            $this->session->set_flashdata('error', 'Failed to revoke API Key.');
        }
        
        redirect('developer/index');
    }
}