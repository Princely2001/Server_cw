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
        | Only logged-in users with developer role can access API key management.
        */
        if (!$this->session->userdata('logged_in')) {
            redirect('auth/login');
            exit;
        }

        if ($this->session->userdata('role') !== 'developer') {
            show_error('Forbidden: This section is restricted to Developers only.', 403);
            exit;
        }

        $this->load->model('Api_model');
        $this->load->helper(['form', 'url', 'security']);
        $this->load->library('session');
    }

    /*
    |--------------------------------------------------------------------------
    | Developer Dashboard
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        $user_id = (int) $this->session->userdata('user_id');

        $data['title'] = 'Developer API Dashboard';
        $data['api_keys'] = $this->Api_model->get_user_keys($user_id);
        $data['logs'] = $this->Api_model->get_usage_logs($user_id);

        /*
        |--------------------------------------------------------------------------
        | New API Key
        |--------------------------------------------------------------------------
        | The generated API key is shown once using flashdata.
        */
        $data['new_api_key'] = $this->session->flashdata('new_api_key');

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

        $user_id = (int) $this->session->userdata('user_id');

        $current_keys = $this->Api_model->get_user_keys($user_id);

        /*
        |--------------------------------------------------------------------------
        | Limit Active Keys
        |--------------------------------------------------------------------------
        | Only count active keys, not revoked keys.
        */
        $active_key_count = 0;

        foreach ($current_keys as $key) {
            if ($key->status === 'active') {
                $active_key_count++;
            }
        }

        if ($active_key_count >= 5) {
            $this->session->set_flashdata(
                'error',
                'You have reached the maximum limit of 5 active API keys.'
            );

            redirect('developer/index');
            return;
        }

        $result = $this->Api_model->generate_key($user_id);

        if (!$result || empty($result['api_key'])) {
            $this->session->set_flashdata(
                'error',
                'Failed to generate API key. Please try again.'
            );

            redirect('developer/index');
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Show Key Once
        |--------------------------------------------------------------------------
        | This makes it easy to copy the key after generation.
        */
        $this->session->set_flashdata(
            'success',
            'New API key generated successfully. Copy it now because it is shown only once.'
        );

        $this->session->set_flashdata('new_api_key', $result['api_key']);

        redirect('developer/index');
    }

    /*
    |--------------------------------------------------------------------------
    | Revoke API Key
    |--------------------------------------------------------------------------
    */
    public function revoke($key_id)
    {
        $user_id = (int) $this->session->userdata('user_id');

        if (empty($key_id) || !is_numeric($key_id)) {
            $this->session->set_flashdata('error', 'Invalid API key selected.');
            redirect('developer/index');
            return;
        }

        if ($this->Api_model->revoke_key((int) $key_id, $user_id)) {
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

        redirect('developer/index');
    }
}