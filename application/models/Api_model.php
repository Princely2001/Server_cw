<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api_model extends CI_Model
{
    private $keys_table = 'api_keys';
    private $logs_table = 'api_logs';

    /*
    |--------------------------------------------------------------------------
    | Get API Keys for Developer
    |--------------------------------------------------------------------------
    */
    public function get_user_keys($user_id)
    {
        return $this->db->where('user_id', (int) $user_id)
                        ->order_by('created_at', 'DESC')
                        ->get($this->keys_table)
                        ->result();
    }

    /*
    |--------------------------------------------------------------------------
    | Generate API Key
    |--------------------------------------------------------------------------
    | Returns the raw API key so the dashboard can show it to the developer.
    */
    public function generate_key($user_id)
    {
        $api_key = bin2hex(random_bytes(32));

        $data = [
            'user_id'    => (int) $user_id,
            'api_key'    => $api_key,
            'status'     => 'active',
            'created_at' => date('Y-m-d H:i:s')
        ];

        $inserted = $this->db->insert($this->keys_table, $data);

        if (!$inserted) {
            return false;
        }

        return [
            'id'      => $this->db->insert_id(),
            'api_key' => $api_key
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Revoke API Key
    |--------------------------------------------------------------------------
    */
    public function revoke_key($key_id, $user_id)
    {
        return $this->db->where('id', (int) $key_id)
                        ->where('user_id', (int) $user_id)
                        ->update($this->keys_table, [
                            'status'     => 'revoked',
                            'revoked_at' => date('Y-m-d H:i:s')
                        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Get Usage Logs for Developer Dashboard
    |--------------------------------------------------------------------------
    */
    public function get_usage_logs($user_id)
    {
        $this->db->select('api_logs.*, api_keys.api_key, api_keys.status AS key_status');
        $this->db->from($this->logs_table);
        $this->db->join($this->keys_table, 'api_keys.id = api_logs.api_key_id');
        $this->db->where('api_keys.user_id', (int) $user_id);
        $this->db->order_by('api_logs.accessed_at', 'DESC');
        $this->db->limit(100);

        return $this->db->get()->result();
    }

    /*
    |--------------------------------------------------------------------------
    | Validate Bearer Token
    |--------------------------------------------------------------------------
    */
    public function is_valid_key($api_key)
    {
        $api_key = trim((string) $api_key);

        if ($api_key === '') {
            return false;
        }

        return $this->db->where('api_key', $api_key)
                        ->where('status', 'active')
                        ->limit(1)
                        ->get($this->keys_table)
                        ->row();
    }

    /*
    |--------------------------------------------------------------------------
    | Log API Request
    |--------------------------------------------------------------------------
    */
    public function log_request($key_id, $endpoint, $ip_address)
    {
        $data = [
            'api_key_id' => (int) $key_id,
            'endpoint'   => $endpoint,
            'ip_address' => $ip_address,
            'accessed_at'=> date('Y-m-d H:i:s')
        ];

        return $this->db->insert($this->logs_table, $data);
    }

    /*
    |--------------------------------------------------------------------------
    | Count Recent Requests by IP
    |--------------------------------------------------------------------------
    | Optional helper for rate limiting.
    */
    public function count_recent_requests_by_ip($ip_address, $minutes = 60)
    {
        return $this->db->where('ip_address', $ip_address)
                        ->where('accessed_at >=', date('Y-m-d H:i:s', strtotime("-{$minutes} minutes")))
                        ->count_all_results($this->logs_table);
    }
}