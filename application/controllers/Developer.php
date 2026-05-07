<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Developer extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        /*
        |--------------------------------------------------------------------------
        | Access Control
        |--------------------------------------------------------------------------
        | API key management is available to developer/admin users only.
        | This matches the Analytics controller, where both developer and admin
        | users are allowed to access dashboard/reporting pages.
        */
        $this->load->library(['session', 'form_validation']);

        if (!$this->session->userdata('logged_in')) {
            redirect('auth/login');
            exit;
        }

        if (!$this->is_allowed_role()) {
            show_error('Forbidden: This section is restricted to Developers/Admins only.', 403);
            exit;
        }

        $this->load->model('Api_model');
        $this->load->helper(['form', 'url', 'security']);
    }

    /*
    |--------------------------------------------------------------------------
    | Role Helper
    |--------------------------------------------------------------------------
    */
    private function is_allowed_role()
    {
        return in_array($this->session->userdata('role'), ['developer', 'admin'], true);
    }

    /*
    |--------------------------------------------------------------------------
    | Current User Helper
    |--------------------------------------------------------------------------
    */
    private function current_user_id()
    {
        return (int) $this->session->userdata('user_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Redirect Helper
    |--------------------------------------------------------------------------
    */
    private function redirect_dashboard()
    {
        redirect('developer');
        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | Developer/Admin API Dashboard
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        $user_id = $this->current_user_id();

        $data['title'] = 'Developer API Dashboard';
        $data['api_keys'] = $this->Api_model->get_user_keys($user_id);
        $data['logs'] = $this->Api_model->get_usage_logs($user_id);

        /*
        |--------------------------------------------------------------------------
        | API Client Types
        |--------------------------------------------------------------------------
        | Used by the dashboard form dropdown.
        */
        $data['client_types'] = [
            'ar_app' => [
                'label' => 'Mobile AR App',
                'permissions' => ['read:alumni_of_day'],
                'description' => 'Can access only the Alumni of the Day endpoint.'
            ],
            'analytics_dashboard' => [
                'label' => 'University Analytics Dashboard',
                'permissions' => ['read:alumni', 'read:analytics'],
                'description' => 'Can access alumni lists and analytics/chart endpoints.'
            ],
            'general' => [
                'label' => 'General API Client',
                'permissions' => ['read:alumni_of_day', 'read:alumni', 'read:analytics'],
                'description' => 'Testing key with all read permissions.'
            ]
        ];

        /*
        |--------------------------------------------------------------------------
        | Show Generated Key Once
        |--------------------------------------------------------------------------
        */
        $data['new_api_key'] = $this->session->flashdata('new_api_key');
        $data['new_key_client_name'] = $this->session->flashdata('new_key_client_name');
        $data['new_key_client_type'] = $this->session->flashdata('new_key_client_type');
        $data['new_key_permissions'] = $this->session->flashdata('new_key_permissions');

        $this->load->view('developer/index', $data);
    }

    /*
    |--------------------------------------------------------------------------
    | Generate API Key
    |--------------------------------------------------------------------------
    */
    public function generate()
    {
        if ($this->input->method(TRUE) !== 'POST') {
            show_error('Method Not Allowed', 405);
            return;
        }

        $this->form_validation->set_rules(
            'client_type',
            'Client Type',
            'required|trim|in_list[ar_app,analytics_dashboard,general]'
        );

        $this->form_validation->set_rules(
            'client_name',
            'Client Name',
            'trim|max_length[100]|xss_clean'
        );

        if ($this->form_validation->run() !== TRUE) {
            $this->session->set_flashdata(
                'error',
                strip_tags(validation_errors())
            );

            $this->redirect_dashboard();
        }

        $user_id = $this->current_user_id();
        $client_type = trim((string) $this->input->post('client_type', TRUE));
        $client_name = trim((string) $this->input->post('client_name', TRUE));

        /*
        |--------------------------------------------------------------------------
        | Limit Active Keys
        |--------------------------------------------------------------------------
        */
        $active_key_count = (int) $this->Api_model->count_active_keys($user_id);

        if ($active_key_count >= 5) {
            $this->session->set_flashdata(
                'error',
                'You have reached the maximum limit of 5 active API keys.'
            );

            $this->redirect_dashboard();
        }

        $result = $this->Api_model->generate_key($user_id, $client_type, $client_name);

        if (!$result || empty($result['api_key'])) {
            $this->session->set_flashdata(
                'error',
                'Failed to generate API key. Please try again.'
            );

            $this->redirect_dashboard();
        }

        /*
        |--------------------------------------------------------------------------
        | Show Key Once
        |--------------------------------------------------------------------------
        */
        $permissions = [];

        if (!empty($result['permissions']) && is_array($result['permissions'])) {
            $permissions = $result['permissions'];
        }

        $this->session->set_flashdata(
            'success',
            'New scoped API key generated successfully. Copy it now because it is shown only once.'
        );

        $this->session->set_flashdata('new_api_key', $result['api_key']);
        $this->session->set_flashdata('new_key_client_name', $result['client_name'] ?? '');
        $this->session->set_flashdata('new_key_client_type', $result['client_type'] ?? $client_type);
        $this->session->set_flashdata('new_key_permissions', implode(', ', $permissions));

        $this->redirect_dashboard();
    }

    /*
    |--------------------------------------------------------------------------
    | Revoke API Key
    |--------------------------------------------------------------------------
    */
    public function revoke($key_id = null)
    {
        $user_id = $this->current_user_id();

        if (empty($key_id) || !is_numeric($key_id)) {
            $this->session->set_flashdata(
                'error',
                'Invalid API key selected.'
            );

            $this->redirect_dashboard();
        }

        $revoked = $this->Api_model->revoke_key((int) $key_id, $user_id);

        if ($revoked) {
            $this->session->set_flashdata(
                'success',
                'API key has been revoked. It can no longer be used.'
            );
        } else {
            $this->session->set_flashdata(
                'error',
                'Failed to revoke API key.'
            );
        }

        $this->redirect_dashboard();
    }
}