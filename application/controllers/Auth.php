<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller
{
    // Define an array of allowed domains
    private $allowed_domains = ['westminster.ac.uk', 'gmail.com'];

    public function __construct()
    {
        parent::__construct();
        
        // Security Headers 
        $this->output->set_header('X-Content-Type-Options: nosniff');
        $this->output->set_header('X-Frame-Options: SAMEORIGIN');
        $this->output->set_header('X-XSS-Protection: 1; mode=block');
        $this->output->set_header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        $this->output->set_header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self' 'unsafe-inline';");
        // -----------------------------------------------

        $this->load->model('User_model');
        $this->load->helper(['url', 'form', 'security']);
        $this->load->library(['form_validation', 'session']);
    }

    public function register()
    {
        $data['title'] = 'Register';

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('first_name', 'First Name', 'required|trim|min_length[2]|max_length[100]|xss_clean');
            $this->form_validation->set_rules('last_name', 'Last Name', 'required|trim|min_length[2]|max_length[100]|xss_clean');
            $this->form_validation->set_rules('email', 'Email Address', 'required|trim|valid_email|xss_clean|callback_email_domain_check|callback_email_not_exists');
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

                // Define Bcrypt options 
                $options = [
                    'cost' => 12,
                ];

                $userData = [
                    'first_name'       => trim($this->input->post('first_name', TRUE)),
                    'last_name'        => trim($this->input->post('last_name', TRUE)),
                    'university_email' => $email, 
                    'password_hash'    => password_hash($this->input->post('password'), PASSWORD_BCRYPT, $options), 
                    'role'             => 'alumnus',
                    'email_verified'   => 0,
                    'is_active'        => 1
                ];

                $user_id = $this->User_model->create_user($userData);

                $rawToken = bin2hex(random_bytes(32));
                $tokenHash = hash('sha256', $rawToken);

                $this->User_model->store_verification_token([
                    'user_id'    => $user_id,
                    'token_hash' => $tokenHash,
                    'expires_at' => date('Y-m-d H:i:s', strtotime('+1 day'))
                ]);

                // Send Verification Email
                $verify_link = site_url('auth/verify?token=' . $rawToken);
                $this->load->library('email');
                $this->email->from('noreply@westminster.ac.uk', 'University System');
                $this->email->to($email);
                $this->email->subject('Verify Your Email Address');
                $message = "<h2>Welcome, " . html_escape($userData['first_name']) . "!</h2>";
                $message .= "<p>Please click the link below to verify your email address:</p>";
                $message .= "<p><a href='{$verify_link}'>Verify My Email</a></p>";
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

        // Check if the domain part matches any of our allowed domains
        if (count($parts) !== 2 || !in_array($parts[1], $this->allowed_domains)) {
            $this->form_validation->set_message('email_domain_check', 'You must register using a valid university or Gmail address.');
            return FALSE;
        }
        return TRUE;
    }

    public function email_not_exists($email)
    {
        if ($this->User_model->email_exists(strtolower(trim($email)))) {
            $this->form_validation->set_message('email_not_exists', 'This email is already registered.');
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
            $this->form_validation->set_message('strong_password_check', 'Password must contain uppercase, lowercase, number, and special character.');
            return FALSE;
        }
        return TRUE;
    }

    public function verify()
    {
        $rawToken = $this->input->get('token', TRUE);

        if (!$rawToken) { show_error('Invalid verification link.', 400); }

        $tokenHash = hash('sha256', $rawToken);
        $tokenRow = $this->User_model->get_valid_verification_token($tokenHash);

        if (!$tokenRow) { show_error('Verification link is invalid, expired, or already used.', 400); }

        $this->User_model->mark_email_verified($tokenRow->user_id);
        $this->User_model->mark_verification_token_used($tokenRow->id);

        echo 'Email verified successfully. <a href="'.site_url('auth/login').'">You can now log in here.</a>';
    }

    public function login()
    {
        //  Basic Rate Limiting (Check Lockout) ---
        $login_attempts = $this->session->userdata('login_attempts') ?: 0;
        $lockout_time = $this->session->userdata('lockout_time') ?: 0;

        if (time() < $lockout_time) {
            $data['title'] = 'Login';
            $remaining_time = ceil(($lockout_time - time()) / 60);
            $data['error_message'] = 'Too many failed attempts. Please try again in ' . $remaining_time . ' minute(s).';
            $this->load->view('auth/login', $data);
            return;
        }

        $data['title'] = 'Login';

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email|xss_clean');
            $this->form_validation->set_rules('password', 'Password', 'required|trim');

            if ($this->form_validation->run() === TRUE) {
                $email = strtolower(trim($this->input->post('email', TRUE)));
                $password = $this->input->post('password');

                $user = $this->User_model->get_user_by_email($email);

                if (!$user || !password_verify($password, $user->password_hash)) {
                    
                    // Rate Limiting
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

                if ((int)$user->email_verified !== 1) {
                    $data['error_message'] = 'Please verify your email before logging in.';
                    $this->load->view('auth/login', $data);
                    return;
                }

                // Rate Limiting 
                $this->session->unset_userdata('login_attempts');
                $this->session->unset_userdata('lockout_time');
                // ----------------------------------------

                $sessionData = [
                    'user_id'         => $user->id,
                    'user_email'      => $user->university_email,
                    'first_name'      => $user->first_name,
                    'last_name'       => $user->last_name,
                    'role'            => $user->role,
                    'logged_in'       => TRUE,
                    'last_activity'   => time()
                ];

                $this->session->set_userdata($sessionData);
                $this->session->sess_regenerate(TRUE);
                $this->User_model->update_last_login($user->id);

                // Redirect to dashboard upon successful login
                redirect('auth/dashboard');
                return;
            }
        }

        $this->load->view('auth/login', $data);
    }

    public function dashboard()
    {
        // Protect the dashboard route
        if (!$this->session->userdata('logged_in')) {
            redirect('auth/login');
            return;
        }

        $this->check_session_timeout();

        $data['title'] = 'Alumni Dashboard';
        
        // Load the new Dashboard view
        $this->load->view('auth/dashboard', $data);
    }

    public function logout()
    {
        $this->session->sess_destroy();
        redirect('auth/login');
    }

    private function check_session_timeout()
    {
        $lastActivity = $this->session->userdata('last_activity');
        $timeoutSeconds = 7200; 

        if ($lastActivity && (time() - $lastActivity > $timeoutSeconds)) {
            $this->session->sess_destroy();
            redirect('auth/login');
            exit;
        }

        $this->session->set_userdata('last_activity', time());
    }

    public function forgot_password()
    {
        $data['title'] = 'Forgot Password';

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('email', 'Email Address', 'required|trim|valid_email|xss_clean');

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

                    $reset_link = site_url('auth/reset_password?token=' . $rawToken);

                    $this->load->library('email');
                    $this->email->from('noreply@westminster.ac.uk', 'University System');
                    $this->email->to($email);
                    $this->email->subject('Password Reset Request');
                    $message = "<h2>Password Reset</h2>";
                    $message .= "<p>Click the link below to reset your password:</p>";
                    $message .= "<p><a href='{$reset_link}'>Reset My Password</a></p>";
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

        // Retrieve token safely from either GET or POST
        $rawToken = $this->input->get('token', TRUE) ?: $this->input->post('token', TRUE);

        if (empty($rawToken)) { show_error('Invalid reset link.', 400); }

        $tokenHash = hash('sha256', $rawToken);
        $tokenRow = $this->User_model->get_valid_password_reset_token($tokenHash);

        if (!$tokenRow) { show_error('Reset link is invalid, expired, or already used.', 400); }

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('password', 'Password', 'required|trim|min_length[8]|max_length[64]|callback_strong_password_check');
            $this->form_validation->set_rules('confirm_password', 'Confirm Password', 'required|trim|matches[password]');

            if ($this->form_validation->run() === TRUE) {
                // Define Bcrypt 
                $options = [
                    'cost' => 12,
                ];

                $passwordHash = password_hash($this->input->post('password'), PASSWORD_BCRYPT, $options);
                
                $this->User_model->update_password($tokenRow->user_id, $passwordHash);
                $this->User_model->mark_password_reset_token_used($tokenRow->id);

                echo 'Password reset successful. <a href="'.site_url('auth/login').'">Login here</a>';
                return;
            }
        }

        // Pass the token to the view so it can be added to the hidden input
        $data['token'] = $rawToken;
        $this->load->view('auth/reset_password', $data);
    }
}