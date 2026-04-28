<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller
{
    /*
    |--------------------------------------------------------------------------
    | Allowed Domains
    |--------------------------------------------------------------------------
    | Keep gmail.com for local testing.
    | For final coursework submission, you can remove gmail.com if required.
    */
    private $allowed_domains = ['westminster.ac.uk', 'gmail.com'];

    private $session_timeout_seconds = 7200; // 2 hours

    public function __construct()
    {
        parent::__construct();

        $this->output->set_header('X-Content-Type-Options: nosniff');
        $this->output->set_header('X-Frame-Options: SAMEORIGIN');
        $this->output->set_header('X-XSS-Protection: 1; mode=block');
        $this->output->set_header('Referrer-Policy: strict-origin-when-cross-origin');
        $this->output->set_header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        $this->output->set_header(
            "Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self' 'unsafe-inline'; script-src 'self' 'unsafe-inline';"
        );

        $this->load->model('User_model');
        $this->load->helper(['url', 'form', 'security']);
        $this->load->library(['form_validation', 'session', 'email']);
    }

    public function register()
    {
        $data['title'] = 'Register';

        if ($this->input->method(TRUE) === 'POST') {
            $this->form_validation->set_rules(
                'first_name',
                'First Name',
                'required|trim|min_length[2]|max_length[100]|xss_clean'
            );

            $this->form_validation->set_rules(
                'last_name',
                'Last Name',
                'required|trim|min_length[2]|max_length[100]|xss_clean'
            );

            $this->form_validation->set_rules(
                'email',
                'Email Address',
                'required|trim|valid_email|xss_clean|callback_email_domain_check|callback_email_not_exists'
            );

            $this->form_validation->set_rules(
                'password',
                'Password',
                'required|trim|min_length[8]|max_length[64]|callback_strong_password_check'
            );

            $this->form_validation->set_rules(
                'confirm_password',
                'Confirm Password',
                'required|trim|matches[password]'
            );

            if ($this->form_validation->run() === TRUE) {
                $email = strtolower(trim($this->input->post('email', TRUE)));

                /*
                 * Security fix:
                 * Public registration should create alumnus accounts only.
                 * Developer accounts should be created manually in the database for testing/admin use.
                 */
                $assigned_role = 'alumnus';

                $passwordHash = password_hash(
                    $this->input->post('password'),
                    PASSWORD_BCRYPT,
                    ['cost' => 12]
                );

                $userData = [
                    'first_name'       => trim($this->input->post('first_name', TRUE)),
                    'last_name'        => trim($this->input->post('last_name', TRUE)),
                    'university_email' => $email,
                    'password_hash'    => $passwordHash,
                    'role'             => $assigned_role,
                    'email_verified'   => 0,
                    'is_active'        => 1
                ];

                $user_id = $this->User_model->create_user($userData);

                if (!$user_id) {
                    $data['error_message'] = 'Registration failed. Please try again.';
                    $this->load->view('auth/register', $data);
                    return;
                }

                $rawToken = bin2hex(random_bytes(32));
                $tokenHash = hash('sha256', $rawToken);

                $this->User_model->store_verification_token([
                    'user_id'    => $user_id,
                    'token_hash' => $tokenHash,
                    'expires_at' => date('Y-m-d H:i:s', strtotime('+1 day'))
                ]);

                $verify_link = site_url('auth/verify?token=' . urlencode($rawToken));

                $this->email->clear(TRUE);
                $this->email->from('noreply@westminster.ac.uk', 'Alumni Influencers Platform');
                $this->email->to($email);
                $this->email->subject('Verify Your Email Address');
                $this->email->set_mailtype('html');

                $message  = '<h2>Welcome, ' . html_escape($userData['first_name']) . '!</h2>';
                $message .= '<p>Please click the link below to verify your email address:</p>';
                $message .= '<p><a href="' . html_escape($verify_link) . '">Verify My Email</a></p>';
                $message .= '<p>This verification link will expire in 24 hours.</p>';

                $this->email->message($message);
                $this->email->send();

                $data['success_message'] = 'Registration successful. Please check your email to verify your account.';
            }
        }

        $this->load->view('auth/register', $data);
    }

    public function email_domain_check($email)
    {
        $email = strtolower(trim($email));
        $parts = explode('@', $email);

        if (count($parts) !== 2) {
            $this->form_validation->set_message(
                'email_domain_check',
                'Please enter a valid email address.'
            );
            return FALSE;
        }

        $domain = $parts[1];

        if (!in_array($domain, $this->allowed_domains, TRUE)) {
            $this->form_validation->set_message(
                'email_domain_check',
                'Only Westminster university emails or Gmail test emails are allowed.'
            );
            return FALSE;
        }

        return TRUE;
    }

    public function email_not_exists($email)
    {
        $email = strtolower(trim($email));

        if ($this->User_model->email_exists($email)) {
            $this->form_validation->set_message(
                'email_not_exists',
                'This email is already registered.'
            );
            return FALSE;
        }

        return TRUE;
    }

    public function strong_password_check($password)
    {
        $hasUpper = preg_match('/[A-Z]/', $password);
        $hasLower = preg_match('/[a-z]/', $password);
        $hasNumber = preg_match('/[0-9]/', $password);
        $hasSpecial = preg_match('/[\W_]/', $password);

        if (!$hasUpper || !$hasLower || !$hasNumber || !$hasSpecial) {
            $this->form_validation->set_message(
                'strong_password_check',
                'Password must contain uppercase, lowercase, number, and special character.'
            );
            return FALSE;
        }

        return TRUE;
    }

    public function verify()
    {
        $rawToken = $this->input->get('token', TRUE);

        if (empty($rawToken)) {
            show_error('Invalid verification link.', 400);
            return;
        }

        $tokenHash = hash('sha256', $rawToken);
        $tokenRow = $this->User_model->get_valid_verification_token($tokenHash);

        if (!$tokenRow) {
            show_error('Verification link is invalid, expired, or already used.', 400);
            return;
        }

        $this->User_model->mark_email_verified($tokenRow->user_id);
        $this->User_model->mark_verification_token_used($tokenRow->id);

        echo 'Email verified successfully. <a href="' . site_url('auth/login') . '">You can now log in here.</a>';
    }

    public function login()
    {
        $data['title'] = 'Login';

        $login_attempts = (int) ($this->session->userdata('login_attempts') ?: 0);
        $lockout_time = (int) ($this->session->userdata('lockout_time') ?: 0);

        if (time() < $lockout_time) {
            $remaining_time = ceil(($lockout_time - time()) / 60);
            $data['error_message'] = 'Too many failed attempts. Please try again in ' . $remaining_time . ' minute(s).';
            $this->load->view('auth/login', $data);
            return;
        }

        if ($this->input->method(TRUE) === 'POST') {
            $this->form_validation->set_rules(
                'email',
                'Email',
                'required|trim|valid_email|xss_clean'
            );

            $this->form_validation->set_rules(
                'password',
                'Password',
                'required|trim'
            );

            if ($this->form_validation->run() === TRUE) {
                $email = strtolower(trim($this->input->post('email', TRUE)));
                $password = $this->input->post('password');

                $user = $this->User_model->get_user_by_email($email);

                if (!$user || !password_verify($password, $user->password_hash)) {
                    $login_attempts++;
                    $this->session->set_userdata('login_attempts', $login_attempts);

                    if ($login_attempts >= 5) {
                        $this->session->set_userdata('lockout_time', time() + 300);
                        $data['error_message'] = 'Too many failed attempts. You are locked out for 5 minutes.';
                    } else {
                        $attempts_left = 5 - $login_attempts;
                        $data['error_message'] = 'Invalid email or password. You have ' . $attempts_left . ' attempt(s) left.';
                    }

                    $this->load->view('auth/login', $data);
                    return;
                }

                if ((int) $user->is_active !== 1) {
                    $data['error_message'] = 'Your account is inactive. Please contact support.';
                    $this->load->view('auth/login', $data);
                    return;
                }

                if ((int) $user->email_verified !== 1) {
                    $data['error_message'] = 'Please verify your email before logging in.';
                    $this->load->view('auth/login', $data);
                    return;
                }

                $this->session->unset_userdata('login_attempts');
                $this->session->unset_userdata('lockout_time');

                $this->session->sess_regenerate(TRUE);

                $sessionData = [
                    'user_id'       => (int) $user->id,
                    'user_email'    => $user->university_email,
                    'first_name'    => $user->first_name,
                    'last_name'     => $user->last_name,
                    'role'          => $user->role,
                    'logged_in'     => TRUE,
                    'last_activity' => time()
                ];

                $this->session->set_userdata($sessionData);
                $this->User_model->update_last_login($user->id);

                if ($user->role === 'developer') {
                    redirect('developer/index');
                    return;
                }

                redirect('auth/dashboard');
                return;
            }
        }

        $this->load->view('auth/login', $data);
    }

    public function dashboard()
    {
        $this->require_login();

        $data['title'] = 'Alumni Dashboard';
        $this->load->view('auth/dashboard', $data);
    }

    public function logout()
    {
        $this->session->sess_destroy();
        redirect('auth/login');
    }

    public function forgot_password()
    {
        $data['title'] = 'Forgot Password';

        if ($this->input->method(TRUE) === 'POST') {
            $this->form_validation->set_rules(
                'email',
                'Email Address',
                'required|trim|valid_email|xss_clean'
            );

            if ($this->form_validation->run() === TRUE) {
                $email = strtolower(trim($this->input->post('email', TRUE)));
                $user = $this->User_model->get_user_by_email($email);

                if ($user) {
                    $rawToken = bin2hex(random_bytes(32));
                    $tokenHash = hash('sha256', $rawToken);

                    $this->User_model->store_password_reset_token([
                        'user_id'    => $user->id,
                        'token_hash' => $tokenHash,
                        'expires_at' => date('Y-m-d H:i:s', strtotime('+1 hour'))
                    ]);

                    $reset_link = site_url('auth/reset_password?token=' . urlencode($rawToken));

                    $this->email->clear(TRUE);
                    $this->email->from('noreply@westminster.ac.uk', 'Alumni Influencers Platform');
                    $this->email->to($email);
                    $this->email->subject('Password Reset Request');
                    $this->email->set_mailtype('html');

                    $message  = '<h2>Password Reset</h2>';
                    $message .= '<p>Click the link below to reset your password:</p>';
                    $message .= '<p><a href="' . html_escape($reset_link) . '">Reset My Password</a></p>';
                    $message .= '<p>This password reset link will expire in 1 hour.</p>';

                    $this->email->message($message);
                    $this->email->send();
                }

                $data['success_message'] = 'If that email exists, a password reset link has been sent.';
            }
        }

        $this->load->view('auth/forgot_password', $data);
    }

    public function reset_password()
    {
        $data['title'] = 'Reset Password';

        $rawToken = $this->input->get('token', TRUE) ?: $this->input->post('token', TRUE);

        if (empty($rawToken)) {
            show_error('Invalid reset link.', 400);
            return;
        }

        $tokenHash = hash('sha256', $rawToken);
        $tokenRow = $this->User_model->get_valid_password_reset_token($tokenHash);

        if (!$tokenRow) {
            show_error('Reset link is invalid, expired, or already used.', 400);
            return;
        }

        if ($this->input->method(TRUE) === 'POST') {
            $this->form_validation->set_rules(
                'password',
                'Password',
                'required|trim|min_length[8]|max_length[64]|callback_strong_password_check'
            );

            $this->form_validation->set_rules(
                'confirm_password',
                'Confirm Password',
                'required|trim|matches[password]'
            );

            if ($this->form_validation->run() === TRUE) {
                $passwordHash = password_hash(
                    $this->input->post('password'),
                    PASSWORD_BCRYPT,
                    ['cost' => 12]
                );

                $this->User_model->update_password($tokenRow->user_id, $passwordHash);
                $this->User_model->mark_password_reset_token_used($tokenRow->id);

                echo 'Password reset successful. <a href="' . site_url('auth/login') . '">Login here</a>';
                return;
            }
        }

        $data['token'] = $rawToken;
        $this->load->view('auth/reset_password', $data);
    }

    private function require_login()
    {
        if (!$this->session->userdata('logged_in')) {
            redirect('auth/login');
            exit;
        }

        $this->check_session_timeout();
    }

    private function check_session_timeout()
    {
        $lastActivity = (int) $this->session->userdata('last_activity');

        if ($lastActivity && (time() - $lastActivity > $this->session_timeout_seconds)) {
            $this->session->sess_destroy();
            redirect('auth/login');
            exit;
        }

        $this->session->set_userdata('last_activity', time());
    }
}