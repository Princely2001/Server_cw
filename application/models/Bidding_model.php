<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Bidding_model extends CI_Model
{
    private $bids_table = 'alumni_bids';
    private $limits_table = 'alumni_monthly_limits';
    private $profiles_table = 'alumni_profiles';
    private $users_table = 'users';

    private $degrees_table = 'alumni_degrees';
    private $certifications_table = 'alumni_certifications';
    private $licences_table = 'alumni_licences';
    private $courses_table = 'alumni_courses';
    private $employment_table = 'alumni_employment';

    public function get_user_bid($user_id, $target_date)
    {
        return $this->db->where('user_id', (int) $user_id)
                        ->where('target_date', $target_date)
                        ->limit(1)
                        ->get($this->bids_table)
                        ->row();
    }

    public function place_bid(array $data)
    {
        if (empty($data['user_id']) || empty($data['target_date']) || empty($data['bid_amount'])) {
            return false;
        }

        $existing = $this->get_user_bid($data['user_id'], $data['target_date']);

        if ($existing) {
            return false;
        }

        $data['user_id'] = (int) $data['user_id'];
        $data['bid_amount'] = round((float) $data['bid_amount'], 2);

        if (empty($data['status'])) {
            $data['status'] = 'pending';
        }

        if (empty($data['created_at'])) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }

        if (empty($data['updated_at'])) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }

        $inserted = $this->db->insert($this->bids_table, $data);

        if (!$inserted) {
            return false;
        }

        return $this->db->insert_id();
    }

    public function update_bid($bid_id, $new_amount)
    {
        $new_amount = round((float) $new_amount, 2);

        if ($new_amount <= 0) {
            return false;
        }

        return $this->db->where('id', (int) $bid_id)
                        ->where('status', 'pending')
                        ->where('bid_amount <', $new_amount)
                        ->update($this->bids_table, [
                            'bid_amount' => $new_amount,
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);
    }

    public function get_monthly_wins($user_id, $month, $year)
    {
        $record = $this->db->where('user_id', (int) $user_id)
                           ->where('win_month', (int) $month)
                           ->where('win_year', (int) $year)
                           ->limit(1)
                           ->get($this->limits_table)
                           ->row();

        return $record ? (int) $record->appearance_count : 0;
    }

    public function get_monthly_wins_for_date($user_id, $target_date)
    {
        $month = (int) date('n', strtotime($target_date));
        $year = (int) date('Y', strtotime($target_date));

        return $this->get_monthly_wins($user_id, $month, $year);
    }

    public function increment_monthly_wins($user_id, $month, $year)
    {
        $record = $this->db->where('user_id', (int) $user_id)
                           ->where('win_month', (int) $month)
                           ->where('win_year', (int) $year)
                           ->limit(1)
                           ->get($this->limits_table)
                           ->row();

        if ($record) {
            return $this->db->set('appearance_count', 'appearance_count + 1', false)
                            ->set('updated_at', date('Y-m-d H:i:s'))
                            ->where('id', (int) $record->id)
                            ->update($this->limits_table);
        }

        return $this->db->insert($this->limits_table, [
            'user_id'          => (int) $user_id,
            'win_month'        => (int) $month,
            'win_year'         => (int) $year,
            'appearance_count' => 1,
            'created_at'       => date('Y-m-d H:i:s'),
            'updated_at'       => date('Y-m-d H:i:s')
        ]);
    }

    public function get_effective_max_wins($user_id)
    {
        $user = $this->db->select('has_attended_event')
                         ->where('id', (int) $user_id)
                         ->limit(1)
                         ->get($this->users_table)
                         ->row();

        return (!empty($user) && (int) $user->has_attended_event === 1) ? 4 : 3;
    }

    public function get_current_leader($target_date)
    {
        return $this->db->where('target_date', $target_date)
                        ->where('status', 'pending')
                        ->order_by('bid_amount', 'DESC')
                        ->order_by('created_at', 'ASC')
                        ->order_by('id', 'ASC')
                        ->limit(1)
                        ->get($this->bids_table)
                        ->row();
    }

    public function get_current_bid_status($bid_id, $target_date)
    {
        $my_bid = $this->db->where('id', (int) $bid_id)
                           ->where('target_date', $target_date)
                           ->limit(1)
                           ->get($this->bids_table)
                           ->row();

        if (!$my_bid) {
            return 'No Bid Placed';
        }

        if ($my_bid->status === 'won') {
            return 'Won';
        }

        if ($my_bid->status === 'lost') {
            return 'Lost';
        }

        $leader = $this->get_current_leader($target_date);

        if (!$leader) {
            return 'Pending';
        }

        return ((int) $leader->id === (int) $my_bid->id) ? 'Winning!' : 'Losing';
    }

    public function is_already_resolved($target_date)
    {
        return $this->db->where('target_date', $target_date)
                        ->where('status', 'won')
                        ->count_all_results($this->bids_table) > 0;
    }

    public function get_winning_bid_for_date($target_date)
    {
        return $this->db->where('target_date', $target_date)
                        ->where('status', 'won')
                        ->limit(1)
                        ->get($this->bids_table)
                        ->row();
    }

    public function activate_featured_profile($user_id, $target_date)
    {
        $this->db->where('is_featured', 1)
                 ->update($this->profiles_table, [
                     'is_featured'       => 0,
                     'featured_for_date' => null,
                     'updated_at'        => date('Y-m-d H:i:s')
                 ]);

        return $this->db->where('user_id', (int) $user_id)
                        ->update($this->profiles_table, [
                            'is_featured'       => 1,
                            'featured_for_date' => $target_date,
                            'updated_at'        => date('Y-m-d H:i:s')
                        ]);
    }

    public function resolve_winner($target_date)
    {
        $month = (int) date('n', strtotime($target_date));
        $year = (int) date('Y', strtotime($target_date));

        $this->db->trans_begin();

        if ($this->is_already_resolved($target_date)) {
            $winner = $this->get_resolved_winner_with_user($target_date);
            $losers = $this->get_losers_with_user($target_date);

            $this->db->trans_commit();

            return [
                'winner' => $winner,
                'losers' => $losers
            ];
        }

        $bids = $this->db->where('target_date', $target_date)
                         ->where('status', 'pending')
                         ->order_by('bid_amount', 'DESC')
                         ->order_by('created_at', 'ASC')
                         ->order_by('id', 'ASC')
                         ->get($this->bids_table)
                         ->result();

        if (empty($bids)) {
            $this->db->trans_commit();
            return null;
        }

        $winner = null;

        foreach ($bids as $bid) {
            $wins = $this->get_monthly_wins($bid->user_id, $month, $year);
            $max_allowed = $this->get_effective_max_wins($bid->user_id);

            if ($wins < $max_allowed) {
                $winner = $bid;
                break;
            }
        }

        if (!$winner) {
            $this->db->where('target_date', $target_date)
                     ->where('status', 'pending')
                     ->update($this->bids_table, [
                         'status'     => 'lost',
                         'updated_at' => date('Y-m-d H:i:s')
                     ]);

            if ($this->db->trans_status() === false) {
                $this->db->trans_rollback();
                return false;
            }

            $this->db->trans_commit();
            return null;
        }

        $this->db->where('id', (int) $winner->id)
                 ->where('status', 'pending')
                 ->update($this->bids_table, [
                     'status'     => 'won',
                     'updated_at' => date('Y-m-d H:i:s')
                 ]);

        $this->db->where('target_date', $target_date)
                 ->where('status', 'pending')
                 ->where('id !=', (int) $winner->id)
                 ->update($this->bids_table, [
                     'status'     => 'lost',
                     'updated_at' => date('Y-m-d H:i:s')
                 ]);

        $this->increment_monthly_wins($winner->user_id, $month, $year);
        $this->activate_featured_profile($winner->user_id, $target_date);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            log_message('error', 'Winner resolution failed for ' . $target_date);
            return false;
        }

        $resolved_winner = $this->get_resolved_winner_with_user($target_date);
        $losers = $this->get_losers_with_user($target_date);

        $this->db->trans_commit();

        return [
            'winner' => $resolved_winner,
            'losers' => $losers
        ];
    }

    public function get_resolved_winner_with_user($target_date)
    {
        return $this->db->select('
                            b.id AS bid_id,
                            b.user_id,
                            b.bid_amount,
                            b.target_date,
                            u.first_name,
                            u.last_name,
                            u.university_email
                        ')
                        ->from($this->bids_table . ' b')
                        ->join($this->users_table . ' u', 'u.id = b.user_id')
                        ->where('b.target_date', $target_date)
                        ->where('b.status', 'won')
                        ->limit(1)
                        ->get()
                        ->row();
    }

    public function get_losers_with_user($target_date)
    {
        return $this->db->select('
                            b.id AS bid_id,
                            b.user_id,
                            b.target_date,
                            u.first_name,
                            u.last_name,
                            u.university_email
                        ')
                        ->from($this->bids_table . ' b')
                        ->join($this->users_table . ' u', 'u.id = b.user_id')
                        ->where('b.target_date', $target_date)
                        ->where('b.status', 'lost')
                        ->get()
                        ->result();
    }

    public function get_featured_profile_by_date($date)
    {
        return $this->db->select('
                            p.user_id,
                            p.bio,
                            p.linkedin_url,
                            p.profile_image,
                            p.featured_for_date,
                            u.first_name,
                            u.last_name,
                            u.university_email
                        ')
                        ->from($this->profiles_table . ' p')
                        ->join($this->users_table . ' u', 'u.id = p.user_id')
                        ->where('p.is_featured', 1)
                        ->where('p.featured_for_date', $date)
                        ->limit(1)
                        ->get()
                        ->row();
    }

    public function get_full_featured_profile_by_date($date)
    {
        $profile = $this->get_featured_profile_by_date($date);

        if (!$profile) {
            return null;
        }

        $user_id = (int) $profile->user_id;

        return [
            'profile'        => $profile,
            'degrees'        => $this->get_user_degrees($user_id),
            'certifications' => $this->get_user_certifications($user_id),
            'licences'       => $this->get_user_licences($user_id),
            'courses'        => $this->get_user_courses($user_id),
            'employment'     => $this->get_user_employment($user_id)
        ];
    }

    public function get_user_degrees($user_id)
    {
        return $this->db->where('user_id', (int) $user_id)
                        ->order_by('completion_date', 'DESC')
                        ->get($this->degrees_table)
                        ->result();
    }

    public function get_user_certifications($user_id)
    {
        return $this->db->where('user_id', (int) $user_id)
                        ->order_by('completion_date', 'DESC')
                        ->get($this->certifications_table)
                        ->result();
    }

    public function get_user_licences($user_id)
    {
        return $this->db->where('user_id', (int) $user_id)
                        ->order_by('completion_date', 'DESC')
                        ->get($this->licences_table)
                        ->result();
    }

    public function get_user_courses($user_id)
    {
        return $this->db->where('user_id', (int) $user_id)
                        ->order_by('completion_date', 'DESC')
                        ->get($this->courses_table)
                        ->result();
    }

    public function get_user_employment($user_id)
    {
        return $this->db->where('user_id', (int) $user_id)
                        ->order_by('start_date', 'DESC')
                        ->get($this->employment_table)
                        ->result();
    }

    public function get_todays_winner()
    {
        $today = (new DateTime('now', new DateTimeZone('Asia/Colombo')))->format('Y-m-d');
        return $this->get_resolved_winner_with_user($today);
    }

    public function get_user_bid_history($user_id)
    {
        return $this->db->where('user_id', (int) $user_id)
                        ->order_by('target_date', 'DESC')
                        ->order_by('updated_at', 'DESC')
                        ->get($this->bids_table)
                        ->result();
    }
}