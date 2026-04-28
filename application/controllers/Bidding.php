<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Bidding extends CI_Controller
{
    private $app_timezone = 'Asia/Colombo';

    public function __construct()
    {
        parent::__construct();

        date_default_timezone_set($this->app_timezone);

        $this->output->set_header('X-Content-Type-Options: nosniff');
        $this->output->set_header('X-Frame-Options: SAMEORIGIN');
        $this->output->set_header('Referrer-Policy: strict-origin-when-cross-origin');
        $this->output->set_header('X-XSS-Protection: 1; mode=block');
        $this->output->set_header(
            "Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src https://fonts.gstatic.com; script-src 'self' 'unsafe-inline';"
        );

        $this->load->model('Bidding_model');
        $this->load->helper(['form', 'url', 'security']);
        $this->load->library(['form_validation', 'session', 'email']);

        // Match MySQL session timezone with app timezone.
        $this->db->query("SET time_zone = '+05:30'");
    }

    private function require_login()
    {
        if (!$this->session->userdata('logged_in')) {
            redirect('login');
            exit;
        }
    }

    private function require_alumnus()
    {
        $this->require_login();

        if ($this->session->userdata('role') !== 'alumnus') {
            show_error('Forbidden: Alumni access only.', 403);
            exit;
        }
    }

    private function json_response($data, $status = 200)
    {
        $this->output
            ->set_status_header($status)
            ->set_content_type('application/json')
            ->set_output(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    private function now_colombo()
    {
        return new DateTime('now', new DateTimeZone($this->app_timezone));
    }

    private function today_colombo()
    {
        return $this->now_colombo()->format('Y-m-d');
    }

    private function tomorrow_colombo()
    {
        $dt = $this->now_colombo();
        $dt->modify('+1 day');
        return $dt->format('Y-m-d');
    }

    private function now_datetime_colombo()
    {
        return $this->now_colombo()->format('Y-m-d H:i:s');
    }

    public function index()
    {
        $this->require_alumnus();

        $user_id = (int) $this->session->userdata('user_id');
        $target_date = $this->tomorrow_colombo();

        $max_allowed_wins = $this->Bidding_model->get_effective_max_wins($user_id);
        $monthly_wins = $this->Bidding_model->get_monthly_wins_for_date($user_id, $target_date);
        $my_bid = $this->Bidding_model->get_user_bid($user_id, $target_date);

        $current_status = 'No Bid Placed';

        if ($my_bid) {
            $current_status = $this->Bidding_model->get_current_bid_status($my_bid->id, $target_date);
        }

        $data = [
            'title'             => 'Alumni of the Day - Bidding',
            'target_date'       => $target_date,
            'my_bid'            => $my_bid,
            'monthly_wins'      => $monthly_wins,
            'max_allowed_wins'  => $max_allowed_wins,
            'remaining_slots'   => max(0, $max_allowed_wins - $monthly_wins),
            'limit_reached'     => ($monthly_wins >= $max_allowed_wins),
            'current_status'    => $current_status,
            'app_timezone'      => $this->app_timezone,
            'current_time_lk'   => $this->now_datetime_colombo()
        ];

        $this->load->view('bidding/index', $data);
    }

    public function submit_bid()
    {
        $this->require_alumnus();

        if ($this->input->method(TRUE) !== 'POST') {
            show_error('Method Not Allowed', 405);
            return;
        }

        $this->form_validation->set_rules(
            'bid_amount',
            'Bid Amount',
            'trim|required|numeric|greater_than[0]|less_than[1000000]'
        );

        if ($this->form_validation->run() !== TRUE) {
            $this->session->set_flashdata('error', strip_tags(validation_errors()));
            redirect('bidding');
            return;
        }

        $user_id = (int) $this->session->userdata('user_id');
        $user_email = $this->session->userdata('user_email');

        $target_date = $this->tomorrow_colombo();
        $now = $this->now_datetime_colombo();
        $new_amount = round((float) $this->input->post('bid_amount', TRUE), 2);

        if ($new_amount <= 0) {
            $this->session->set_flashdata('error', 'Bid amount must be greater than zero.');
            redirect('bidding');
            return;
        }

        $max_allowed_wins = $this->Bidding_model->get_effective_max_wins($user_id);
        $monthly_wins = $this->Bidding_model->get_monthly_wins_for_date($user_id, $target_date);

        if ($monthly_wins >= $max_allowed_wins) {
            $this->session->set_flashdata(
                'error',
                'You have reached your monthly feature limit. You cannot bid again this month unless you have an extra event allowance.'
            );
            redirect('bidding');
            return;
        }

        if ($this->Bidding_model->is_already_resolved($target_date)) {
            $this->session->set_flashdata('error', 'This bidding round has already been resolved.');
            redirect('bidding');
            return;
        }

        $existing_bid = $this->Bidding_model->get_user_bid($user_id, $target_date);
        $bid_successful = false;
        $action_label = 'placed';

        if ($existing_bid) {
            if (in_array($existing_bid->status, ['won', 'lost'], TRUE)) {
                $this->session->set_flashdata('error', 'This bidding round has already been resolved.');
                redirect('bidding');
                return;
            }

            if ($new_amount <= (float) $existing_bid->bid_amount) {
                $this->session->set_flashdata('error', 'You can only update your bid to a higher amount.');
                redirect('bidding');
                return;
            }

            $bid_successful = $this->Bidding_model->update_bid($existing_bid->id, $new_amount);
            $action_label = 'updated';

            if ($bid_successful) {
                $this->session->set_flashdata('success', 'Bid updated successfully. Blind bidding status has been refreshed.');
            } else {
                $this->session->set_flashdata('error', 'Bid update failed. Please try again with a higher amount.');
            }
        } else {
            $insert_id = $this->Bidding_model->place_bid([
                'user_id'     => $user_id,
                'bid_amount'  => $new_amount,
                'target_date' => $target_date,
                'status'      => 'pending',
                'created_at'  => $now,
                'updated_at'  => $now
            ]);

            $bid_successful = !empty($insert_id);

            if ($bid_successful) {
                $this->session->set_flashdata('success', 'Bid placed successfully. You can see only whether you are winning or losing.');
            } else {
                $this->session->set_flashdata('error', 'Bid placement failed. Please try again.');
            }
        }

        if ($bid_successful && !empty($user_email)) {
            $this->send_bid_confirmation_email($user_email, $new_amount, $target_date, $action_label, $now);
        }

        redirect('bidding');
    }

    private function send_bid_confirmation_email($user_email, $amount, $target_date, $action_label, $recorded_time)
    {
        $this->email->clear(TRUE);
        $this->email->from('noreply@westminster.ac.uk', 'Alumni Bidding System');
        $this->email->to($user_email);
        $this->email->subject('Bid Confirmation - Alumni of the Day');
        $this->email->set_mailtype('html');

        $message  = '<p>Your bid of <strong>£' . number_format((float) $amount, 2) . '</strong> for <strong>' . html_escape($target_date) . '</strong> has been ' . html_escape($action_label) . ' successfully.</p>';
        $message .= '<p>Recorded time: <strong>' . html_escape($recorded_time) . '</strong> (' . html_escape($this->app_timezone) . ')</p>';
        $message .= '<p>This is a blind bidding system. You will only see whether you are currently winning or losing.</p>';
        $message .= '<p>You may only increase your bid before the round is resolved.</p>';

        $this->email->message($message);
        $this->email->send();
    }

    public function history()
    {
        $this->require_alumnus();

        $user_id = (int) $this->session->userdata('user_id');
        $history = $this->Bidding_model->get_user_bid_history($user_id);

        $data = [
            'title'           => 'My Bidding History',
            'history'         => $history,
            'app_timezone'    => $this->app_timezone,
            'current_time_lk' => $this->now_datetime_colombo()
        ];

        $this->load->view('bidding/history', $data);
    }

    public function my_bid_status()
    {
        $this->require_alumnus();

        $user_id = (int) $this->session->userdata('user_id');
        $target_date = $this->tomorrow_colombo();
        $my_bid = $this->Bidding_model->get_user_bid($user_id, $target_date);

        if (!$my_bid) {
            $this->json_response([
                'target_date' => $target_date,
                'status'      => 'No Bid Placed',
                'bid_amount'  => null,
                'timezone'    => $this->app_timezone,
                'checked_at'  => $this->now_datetime_colombo()
            ]);
            return;
        }

        $this->json_response([
            'target_date' => $target_date,
            'status'      => $this->Bidding_model->get_current_bid_status($my_bid->id, $target_date),
            'bid_amount'  => (float) $my_bid->bid_amount,
            'timezone'    => $this->app_timezone,
            'checked_at'  => $this->now_datetime_colombo()
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Cron Winner Selection
    |--------------------------------------------------------------------------
    | This resolves pending bid rounds for today or older dates.
    |
    | Browser test:
    | /cron/resolve-winner?key=secret_23445
    |
    | CLI:
    | php index.php cron/resolve-winner
    */
    public function cron_resolve_winner()
    {
        if (!$this->input->is_cli_request()) {
            $cron_key = getenv('BIDDING_CRON_KEY') ?: $this->config->item('bidding_cron_key');

            $provided_key = $this->input->get('key', TRUE);

            if (empty($provided_key)) {
                $provided_key = $this->input->get_request_header('X-Cron-Key', TRUE);
            }

            if (empty($cron_key) || empty($provided_key) || !hash_equals($cron_key, $provided_key)) {
                show_error('Unauthorized cron request.', 403);
                return;
            }
        }

        $today = $this->today_colombo();

        $pending_rounds = $this->db->select('target_date')
            ->from('alumni_bids')
            ->where('status', 'pending')
            ->where('target_date <=', $today)
            ->group_by('target_date')
            ->order_by('target_date', 'ASC')
            ->get()
            ->result();

        if (empty($pending_rounds)) {
            echo 'No pending bids to resolve at this time.';
            return;
        }

        foreach ($pending_rounds as $round) {
            $target_date = $round->target_date;
            $result = $this->Bidding_model->resolve_winner($target_date);

            if ($result === false) {
                echo "Winner resolution failed for {$target_date}.\n";
                continue;
            }

            if ($result === null) {
                echo "No eligible bids found for {$target_date}.\n";
                continue;
            }

            if (!empty($result['winner']) && !empty($result['winner']->university_email)) {
                $this->send_winner_email($result['winner'], $target_date);
            }

            if (!empty($result['losers'])) {
                foreach ($result['losers'] as $loser) {
                    if (!empty($loser->university_email)) {
                        $this->send_loser_email($loser, $target_date);
                    }
                }
            }

            echo 'Winner resolved successfully for ' . $target_date . ' (Timezone: ' . $this->app_timezone . ")\n";
        }
    }

    private function send_winner_email($winner, $target_date)
    {
        $this->email->clear(TRUE);
        $this->email->from('noreply@westminster.ac.uk', 'Alumni Bidding System');
        $this->email->to($winner->university_email);
        $this->email->subject('Congratulations! You are the Alumni of the Day');
        $this->email->set_mailtype('html');

        $message  = '<h2>Congratulations ' . html_escape($winner->first_name) . '!</h2>';
        $message .= '<p>You have won the bidding round for <strong>' . html_escape($target_date) . '</strong>.</p>';
        $message .= '<p>Your profile is now featured as the <strong>Alumni of the Day</strong>.</p>';

        $this->email->message($message);
        $this->email->send();
    }

    private function send_loser_email($loser, $target_date)
    {
        $this->email->clear(TRUE);
        $this->email->from('noreply@westminster.ac.uk', 'Alumni Bidding System');
        $this->email->to($loser->university_email);
        $this->email->subject('Bidding Result - Alumni of the Day');
        $this->email->set_mailtype('html');

        $message  = '<p>Hello ' . html_escape($loser->first_name) . ',</p>';
        $message .= '<p>Your bid for <strong>' . html_escape($target_date) . '</strong> was not selected this time.</p>';
        $message .= '<p>You can place a new bid for the next available round if you still have remaining monthly slots.</p>';

        $this->email->message($message);
        $this->email->send();
    }

    public function featured_today()
    {
        $today = $this->today_colombo();
        $featured = $this->Bidding_model->get_full_featured_profile_by_date($today);

        $data = [
            'title'           => "Today's Featured Alumnus",
            'today'           => $today,
            'featured'        => $featured,
            'app_timezone'    => $this->app_timezone,
            'current_time_lk' => $this->now_datetime_colombo()
        ];

        $this->load->view('bidding/featured_today', $data);
    }
}