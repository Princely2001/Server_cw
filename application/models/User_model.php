<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model
{
    private $users_table = 'users';
    private $verification_table = 'email_verification_tokens';
    private $reset_table = 'password_reset_tokens';

    public function email_exists($email)
    {
        $email = strtolower(trim($email));

        return $this->db
            ->where('university_email', $email)
            ->count_all_results($this->users_table) > 0;
    }

    public function create_user($data)
    {
        if (empty($data['university_email']) || empty($data['password_hash'])) {
            return false;
        }

        $data['university_email'] = strtolower(trim($data['university_email']));

        if ($this->email_exists($data['university_email'])) {
            return false;
        }

        if (empty($data['created_at'])) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }

        if (empty($data['updated_at'])) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }

        $inserted = $this->db->insert($this->users_table, $data);

        if (!$inserted) {
            return false;
        }

        return $this->db->insert_id();
    }

    public function store_verification_token($data)
    {
        if (empty($data['user_id']) || empty($data['token_hash']) || empty($data['expires_at'])) {
            return false;
        }

        /*
         * Security improvement:
         * Invalidate older unused verification tokens for the same user.
         */
        $this->db->where('user_id', (int) $data['user_id'])
                 ->where('used_at IS NULL', null, false)
                 ->update($this->verification_table, [
                     'used_at' => date('Y-m-d H:i:s')
                 ]);

        $data['user_id'] = (int) $data['user_id'];

        if (empty($data['created_at'])) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }

        return $this->db->insert($this->verification_table, $data);
    }

    public function get_valid_verification_token($tokenHash)
    {
        return $this->db
            ->where('token_hash', $tokenHash)
            ->where('used_at IS NULL', null, false)
            ->where('expires_at >', date('Y-m-d H:i:s'))
            ->limit(1)
            ->get($this->verification_table)
            ->row();
    }

    public function mark_email_verified($userId)
    {
        return $this->db
            ->where('id', (int) $userId)
            ->update($this->users_table, [
                'email_verified' => 1,
                'updated_at'     => date('Y-m-d H:i:s')
            ]);
    }

    public function mark_verification_token_used($tokenId)
    {
        return $this->db
            ->where('id', (int) $tokenId)
            ->update($this->verification_table, [
                'used_at' => date('Y-m-d H:i:s')
            ]);
    }

    public function get_user_by_email($email)
    {
        $email = strtolower(trim($email));

        return $this->db
            ->where('university_email', $email)
            ->where('is_active', 1)
            ->limit(1)
            ->get($this->users_table)
            ->row();
    }

    public function get_user_by_id($userId)
    {
        return $this->db
            ->where('id', (int) $userId)
            ->where('is_active', 1)
            ->limit(1)
            ->get($this->users_table)
            ->row();
    }

    public function update_last_login($userId)
    {
        return $this->db
            ->where('id', (int) $userId)
            ->update($this->users_table, [
                'last_login_at' => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s')
            ]);
    }

    public function store_password_reset_token($data)
    {
        if (empty($data['user_id']) || empty($data['token_hash']) || empty($data['expires_at'])) {
            return false;
        }

        /*
         * Security improvement:
         * Invalidate older unused password reset tokens for the same user.
         */
        $this->db->where('user_id', (int) $data['user_id'])
                 ->where('used_at IS NULL', null, false)
                 ->update($this->reset_table, [
                     'used_at' => date('Y-m-d H:i:s')
                 ]);

        $data['user_id'] = (int) $data['user_id'];

        if (empty($data['created_at'])) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }

        return $this->db->insert($this->reset_table, $data);
    }

    public function get_valid_password_reset_token($tokenHash)
    {
        return $this->db
            ->where('token_hash', $tokenHash)
            ->where('used_at IS NULL', null, false)
            ->where('expires_at >', date('Y-m-d H:i:s'))
            ->limit(1)
            ->get($this->reset_table)
            ->row();
    }

    public function mark_password_reset_token_used($tokenId)
    {
        return $this->db
            ->where('id', (int) $tokenId)
            ->update($this->reset_table, [
                'used_at' => date('Y-m-d H:i:s')
            ]);
    }

    public function update_password($userId, $passwordHash)
    {
        if (empty($passwordHash)) {
            return false;
        }

        return $this->db
            ->where('id', (int) $userId)
            ->update($this->users_table, [
                'password_hash' => $passwordHash,
                'updated_at'    => date('Y-m-d H:i:s')
            ]);
    }
}