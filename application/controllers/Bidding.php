<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Bidding extends CI_Controller {

    private $app_timezone = 'Asia/Colombo';

    public function __construct() {
        parent::__construct();

        date_default_timezone_set($this->app_timezone);

        $this->output->set_header('X-Content-Type-Options: nosniff');
        $this->output->set_header('X-Frame-Options: SAMEORIGIN');
        $this->output->set_header('Referrer-Policy: strict-origin-when-cross-origin');
        $this->output->set_header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src https://fonts.gstatic.com;");

        $this->load->model('Bidding_model');
        $this->load->helper(['form', 'url', 'security']);
        $this->load->library(['form_validation', 'session', 'email']);

        $this->db->query("SET time_zone = '+05:30'");
    }

    private function require_login() {
        if (!$this->session->userdata('logged_in')) {
            redirect('login');
            exit;
        }
    }

    private function require_alumnus() {
        $this->require_login();

        if ($this->session->userdata('role') !== 'alumnus') {
            show_error('Forbidden', 403);
            exit;
        }
    }

    private function json_response($data, $status = 200) {
        $this->output
            ->set_status_header($status)
            ->set_content_type('application/json')
            ->set_output(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    private function now_colombo() {
        return new DateTime('now', new DateTimeZone($this->app_timezone));
    }

    private function today_colombo() {
        return $this->now_colombo()->format('Y-m-d');
    }

    private function tomorrow_colombo() {
        $dt = $this->now_colombo();
        $dt->modify('+1 day');
        return $dt->format('Y-m-d');
    }

    private function now_datetime_colombo() {
        return $this->now_colombo()->format('Y-m-d H:i:s');
    }

    private function require_api_key() {
        $auth_header = $this->input->get_request_header('Authorization', TRUE);
        $api_key = null;

        if (!empty($auth_header) && preg_match('/Bearer\s+(.+)/i', $auth_header, $matches)) {
            $api_key = trim($matches[1]);
        }

        if (empty($api_key)) {
            $api_key = trim((string) $this->input->get_request_header('X-API-Key', TRUE));
        }

        if (empty($api_key)) {
            $this->json_response(['error' => 'Missing API key'], 401);
            exit;
        }

        $key_record = $this->Bidding_model->get_active_api_key($api_key);

        if (!$key_record) {
            $this->json_response(['error' => 'Invalid or revoked API key'], 401);
            exit;
        }

        $this->Bidding_model->log_api_access(
            (int) $key_record->id,
            uri_string(),
            $this->input->ip_address()
        );

        return $key_record;
    }

    public function index() {
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
            'title' => 'Alumni of the Day - Bidding',
            'target_date' => $target_date,
            'my_bid' => $my_bid,
            'monthly_wins' => $monthly_wins,
            'max_allowed_wins' => $max_allowed_wins,
            'remaining_slots' => max(0, $max_allowed_wins - $monthly_wins),
            'limit_reached' => ($monthly_wins >= $max_allowed_wins),
            'current_status' => $current_status,
            'app_timezone' => $this->app_timezone,
            'current_time_lk' => $this->now_datetime_colombo()
        ];

        $this->load->view('bidding/index', $data);
    }

    public function submit_bid() {
        $this->require_alumnus();

        if ($this->input->method(TRUE) !== 'POST') {
            show_error('Method Not Allowed', 405);
            return;
        }

        $this->form_validation->set_rules('bid_amount', 'Bid Amount', 'trim|required|decimal|greater_than[0]');

        if ($this->form_validation->run() !== TRUE) {
            $this->session->set_flashdata('error', strip_tags(validation_errors()));
            redirect('bidding');
            return;
        }

        $user_id = (int) $this->session->userdata('user_id');
        $user_email = $this->session->userdata('university_email');
        if (empty($user_email)) {
            $user_email = $this->session->userdata('user_email');
        }

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
            $this->session->set_flashdata('error', 'You have reached your monthly feature limit.');
            redirect('bidding');
            return;
        }

        if ($this->Bidding_model->is_already_resolved($target_date)) {
            $this->session->set_flashdata('error', 'This bidding round has already been resolved.');
            redirect('bidding');
            return;
        }

        $existing_bid = $this->Bidding_model->get_bid_including_cancelled($user_id, $target_date);
        $bid_successful = false;
        $action_label = 'placed';

        if ($existing_bid) {
            if (in_array($existing_bid->status, ['won', 'lost'], true)) {
                $this->session->set_flashdata('error', 'This bidding round has already been resolved.');
                redirect('bidding');
                return;
            }

            if ($existing_bid->status === 'cancelled') {
                $bid_successful = $this->Bidding_model->reactivate_cancelled_bid($existing_bid->id, $new_amount);
                $action_label = 're-placed';

                if ($bid_successful) {
                    $this->session->set_flashdata('success', 'Bid placed successfully.');
                } else {
                    $this->session->set_flashdata('error', 'Bid placement failed. Please try again.');
                }
            } else {
                if ($new_amount <= (float) $existing_bid->bid_amount) {
                    $this->session->set_flashdata('error', 'You can only update your bid to a higher amount.');
                    redirect('bidding');
                    return;
                }

                $bid_successful = $this->Bidding_model->update_bid($existing_bid->id, $new_amount);
                $action_label = 'updated';

                if ($bid_successful) {
                    $this->session->set_flashdata('success', 'Bid updated successfully.');
                } else {
                    $this->session->set_flashdata('error', 'Bid update failed. Please try again with a higher amount.');
                }
            }
        } else {
            $insert_id = $this->Bidding_model->place_bid([
                'user_id' => $user_id,
                'bid_amount' => $new_amount,
                'target_date' => $target_date,
                'status' => 'pending',
                'created_at' => $now,
                'updated_at' => $now
            ]);

            $bid_successful = !empty($insert_id);

            if ($bid_successful) {
                $this->session->set_flashdata('success', 'Bid placed successfully.');
            } else {
                $this->session->set_flashdata('error', 'Bid placement failed. Please try again.');
            }
        }

        if ($bid_successful && !empty($user_email)) {
            $this->email->from('noreply@westminster.ac.uk', 'Alumni Bidding System');
            $this->email->to($user_email);
            $this->email->subject('Bid Confirmation - Alumni of the Day');
            $this->email->set_mailtype('html');
            $this->email->message(
                '<p>Your bid of <strong>£' . number_format($new_amount, 2) . '</strong> for <strong>' . html_escape($target_date) . '</strong> has been ' . html_escape($action_label) . ' successfully.</p>
                 <p>Recorded time (Sri Lanka): <strong>' . html_escape($now) . '</strong></p>
                 <p>You may only increase your bid before the round is resolved.</p>
                 <p>The system only shows whether you are currently winning or losing.</p>'
            );
            $this->email->send();
        }

        redirect('bidding');
    }

    public function cancel_bid() {
        $this->require_alumnus();

        if ($this->input->method(TRUE) !== 'POST') {
            show_error('Method Not Allowed', 405);
            return;
        }

        $user_id = (int) $this->session->userdata('user_id');
        $target_date = $this->tomorrow_colombo();

        $existing_bid = $this->Bidding_model->get_user_bid($user_id, $target_date);

        if (!$existing_bid || $existing_bid->status !== 'pending') {
            $this->session->set_flashdata('error', 'No pending bid found to cancel or the round is already resolved.');
            redirect('bidding');
            return;
        }

        $updated = $this->Bidding_model->cancel_bid($existing_bid->id, $this->now_datetime_colombo());

        if ($updated) {
            $this->session->set_flashdata('success', 'Your bid has been successfully canceled.');
        } else {
            $this->session->set_flashdata('error', 'Failed to cancel the bid.');
        }

        redirect('bidding');
    }

    public function history() {
        $this->require_alumnus();

        $user_id = (int) $this->session->userdata('user_id');

        $history = $this->Bidding_model->get_user_bid_history($user_id);

        $data = [
            'title' => 'My Bidding History',
            'history' => $history,
            'app_timezone' => $this->app_timezone,
            'current_time_lk' => $this->now_datetime_colombo()
        ];

        $this->load->view('bidding/history', $data);
    }

    public function my_bid_status() {
        $this->require_alumnus();

        $user_id = (int) $this->session->userdata('user_id');
        $target_date = $this->tomorrow_colombo();
        $my_bid = $this->Bidding_model->get_user_bid($user_id, $target_date);

        if (!$my_bid) {
            $this->json_response([
                'target_date' => $target_date,
                'status' => 'No Bid Placed',
                'bid_amount' => null,
                'timezone' => $this->app_timezone,
                'checked_at' => $this->now_datetime_colombo()
            ]);
            return;
        }

        $this->json_response([
            'target_date' => $target_date,
            'status' => $this->Bidding_model->get_current_bid_status($my_bid->id, $target_date),
            'bid_amount' => (float) $my_bid->bid_amount,
            'timezone' => $this->app_timezone,
            'checked_at' => $this->now_datetime_colombo()
        ]);
    }

    // RESOLVE WINNER FIXED LOGIC (12 MIDNIGHT)
    public function cron_resolve_winner() {
        if (!$this->input->is_cli_request()) {
            $cron_key = getenv('BIDDING_CRON_KEY') ?: $this->config->item('bidding_cron_key');
            $provided_key = $this->input->get('key', TRUE);

            if (empty($cron_key) || empty($provided_key) || !hash_equals($cron_key, $provided_key)) {
                show_error('Unauthorized', 403);
                return;
            }
        }

        $today = $this->today_colombo();

        // Loop through ALL un-resolved dates up to today to handle server restarts/missed crons safely.
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
                echo "Winner resolution failed for $target_date.\n";
                continue;
            }

            if ($result === null) {
                echo "No eligible bids found for $target_date.\n";
                continue;
            }

            // Fire off email to winner
            if (!empty($result['winner']) && !empty($result['winner']->university_email)) {
                $winner = $result['winner'];

                $this->email->from('noreply@westminster.ac.uk', 'Alumni Bidding System');
                $this->email->to($winner->university_email);
                $this->email->subject('Congratulations! You are the Alumni of the Day');
                $this->email->set_mailtype('html');
                $this->email->message(
                    '<h2>Congratulations ' . html_escape($winner->first_name) . '!</h2>
                     <p>You have won the bidding round for <strong>' . html_escape($target_date) . '</strong>.</p>
                     <p>Your profile is now featured as the <strong>Alumni of the Day</strong>.</p>'
                );
                $this->email->send();
            }

            // Fire off email to losers
            if (!empty($result['losers'])) {
                foreach ($result['losers'] as $loser) {
                    if (empty($loser->university_email)) continue;

                    $this->email->from('noreply@westminster.ac.uk', 'Alumni Bidding System');
                    $this->email->to($loser->university_email);
                    $this->email->subject('Bidding Result - Alumni of the Day');
                    $this->email->set_mailtype('html');
                    $this->email->message(
                        '<p>Hello ' . html_escape($loser->first_name) . ',</p>
                         <p>Your bid for <strong>' . html_escape($target_date) . '</strong> was not selected this time.</p>
                         <p>You can place a new bid for the next available round if you still have remaining monthly slots.</p>'
                    );
                    $this->email->send();
                }
            }
            
            echo 'Winner resolved successfully for ' . $target_date . ' (Timezone: ' . $this->app_timezone . ")\n";
        }
    }

    public function featured_today() {
        $today = $this->today_colombo();
        $featured = $this->Bidding_model->get_full_featured_profile_by_date($today);

        $data = [
            'title' => "Today's Featured Alumnus",
            'today' => $today,
            'featured' => $featured,
            'app_timezone' => $this->app_timezone,
            'current_time_lk' => $this->now_datetime_colombo()
        ];

        $this->load->view('bidding/featured_today', $data);
    }
}