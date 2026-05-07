<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api_model extends CI_Model
{
    private $keys_table = 'api_keys';
    private $logs_table = 'api_logs';

    /*
    |--------------------------------------------------------------------------
    | Get API Keys for Developer Dashboard
    |--------------------------------------------------------------------------
    | Important:
    | Do not select or expose the raw api_key column.
    | The full API key should only ever be shown once, immediately after generation.
    */
    public function get_user_keys($user_id)
    {
        return $this->db
            ->select('
                id,
                user_id,
                client_name,
                status,
                permissions,
                created_at,
                last_used_at,
                key_hash,
                key_prefix,
                client_type,
                expires_at
            ')
            ->where('user_id', (int) $user_id)
            ->order_by('created_at', 'DESC')
            ->get($this->keys_table)
            ->result();
    }

    /*
    |--------------------------------------------------------------------------
    | Generate API Key
    |--------------------------------------------------------------------------
    | Client type options:
    | ar_app, analytics_dashboard, general
    |
    | Permissions:
    | ar_app              => ["read:alumni_of_day"]
    | analytics_dashboard => ["read:alumni", "read:analytics"]
    | general             => all read permissions
    |
    | Security:
    | - Generates a cryptographically secure API key.
    | - Stores only SHA-256 hash and safe prefix.
    | - Returns the full key once for display.
    */
    public function generate_key($user_id, $client_type = 'ar_app', $client_name = null)
    {
        $user_id = (int) $user_id;

        if ($user_id <= 0) {
            return false;
        }

        $client_type = trim((string) $client_type);
        $allowed_types = ['ar_app', 'analytics_dashboard', 'general'];

        if (!in_array($client_type, $allowed_types, true)) {
            $client_type = 'ar_app';
        }

        $permissions = $this->get_permissions_for_client_type($client_type);

        $client_name = trim((string) $client_name);

        if ($client_name === '') {
            $client_name = $this->get_client_name_for_type($client_type);
        }

        /*
         * 32 random bytes become a 64-character hex token.
         * This full token is shown once and never stored in plaintext.
         */
        try {
            $api_key = bin2hex(random_bytes(32));
        } catch (Exception $e) {
            log_message('error', 'API key generation failed: ' . $e->getMessage());
            return false;
        }

        $key_hash = hash('sha256', $api_key);
        $key_prefix = substr($api_key, 0, 12);

        /*
         * api_key is intentionally set to NULL.
         * If your database does not allow NULL for api_key, run the SQL shown below this code.
         */
        $data = [
            'user_id'     => $user_id,
            'client_name' => $client_name,
            'api_key'     => null,
            'status'      => 'active',
            'permissions' => json_encode($permissions),
            'created_at'  => date('Y-m-d H:i:s'),
            'key_hash'    => $key_hash,
            'key_prefix'  => $key_prefix,
            'client_type' => $client_type,
            'expires_at'  => null
        ];

        $inserted = $this->db->insert($this->keys_table, $data);

        if (!$inserted) {
            log_message('error', 'Failed to insert API key: ' . json_encode($this->db->error()));
            return false;
        }

        return [
            'id'          => $this->db->insert_id(),
            'api_key'     => $api_key,
            'key_prefix'  => $key_prefix,
            'client_name' => $client_name,
            'client_type' => $client_type,
            'permissions' => $permissions
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Permissions for Client Type
    |--------------------------------------------------------------------------
    */
    private function get_permissions_for_client_type($client_type)
    {
        switch ($client_type) {
            case 'analytics_dashboard':
                return ['read:alumni', 'read:analytics'];

            case 'general':
                return ['read:alumni_of_day', 'read:alumni', 'read:analytics'];

            case 'ar_app':
            default:
                return ['read:alumni_of_day'];
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Default Client Name
    |--------------------------------------------------------------------------
    */
    private function get_client_name_for_type($client_type)
    {
        switch ($client_type) {
            case 'analytics_dashboard':
                return 'University Analytics Dashboard';

            case 'general':
                return 'General API Client';

            case 'ar_app':
            default:
                return 'Mobile AR App';
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Revoke API Key
    |--------------------------------------------------------------------------
    */
    public function revoke_key($key_id, $user_id)
    {
        $key_id = (int) $key_id;
        $user_id = (int) $user_id;

        if ($key_id <= 0 || $user_id <= 0) {
            return false;
        }

        return $this->db
            ->where('id', $key_id)
            ->where('user_id', $user_id)
            ->update($this->keys_table, [
                'status' => 'revoked'
            ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Get Usage Logs for Developer Dashboard
    |--------------------------------------------------------------------------
    | Important:
    | Do not select api_keys.api_key.
    */
    public function get_usage_logs($user_id)
    {
        $user_id = (int) $user_id;

        if ($user_id <= 0) {
            return [];
        }

        $this->db->select('
            api_logs.*,
            api_keys.key_prefix,
            api_keys.client_name,
            api_keys.client_type,
            api_keys.permissions,
            api_keys.status AS key_status
        ');
        $this->db->from($this->logs_table);
        $this->db->join($this->keys_table, 'api_keys.id = api_logs.api_key_id');
        $this->db->where('api_keys.user_id', $user_id);
        $this->db->order_by('api_logs.accessed_at', 'DESC');
        $this->db->limit(100);

        return $this->db->get()->result();
    }

    /*
    |--------------------------------------------------------------------------
    | Validate Bearer Token
    |--------------------------------------------------------------------------
    | Secure validation:
    | - The incoming bearer token is hashed.
    | - The hash is compared against key_hash.
    | - The raw API key is never searched or stored.
    */
    public function is_valid_key($api_key)
    {
        $api_key = trim((string) $api_key);

        if ($api_key === '') {
            return false;
        }

        $key_hash = hash('sha256', $api_key);

        $record = $this->db
            ->where('key_hash', $key_hash)
            ->where('status', 'active')
            ->limit(1)
            ->get($this->keys_table)
            ->row();

        if (!$record) {
            return false;
        }

        if (!empty($record->expires_at)) {
            $expires_at = strtotime($record->expires_at);

            if ($expires_at !== false && $expires_at < time()) {
                return false;
            }
        }

        $this->db
            ->where('id', (int) $record->id)
            ->update($this->keys_table, [
                'last_used_at' => date('Y-m-d H:i:s')
            ]);

        return $record;
    }

    /*
    |--------------------------------------------------------------------------
    | Permission Check Helper
    |--------------------------------------------------------------------------
    */
    public function key_has_permission($key_record, $required_permission)
    {
        if (!$key_record || empty($required_permission)) {
            return false;
        }

        $permissions = [];

        if (!empty($key_record->permissions)) {
            $decoded = json_decode($key_record->permissions, true);

            if (is_array($decoded)) {
                $permissions = $decoded;
            }
        }

        if (empty($permissions)) {
            $permissions = ['read:alumni_of_day'];
        }

        return in_array($required_permission, $permissions, true);
    }

    /*
    |--------------------------------------------------------------------------
    | Log API Request
    |--------------------------------------------------------------------------
    | Compatible with old calls:
    | log_request($key_id, $endpoint, $ip_address)
    |
    | Compatible with new calls:
    | log_request($key_id, $endpoint, $ip_address, $method, $status_code, $user_agent)
    */
    public function log_request($key_id, $endpoint, $ip_address, $method = 'GET', $status_code = 200, $user_agent = null)
    {
        $key_id = (int) $key_id;

        if ($key_id <= 0) {
            return false;
        }

        $data = [
            'api_key_id'  => $key_id,
            'endpoint'    => (string) $endpoint,
            'ip_address'  => (string) $ip_address,
            'accessed_at' => date('Y-m-d H:i:s'),
            'method'      => strtoupper((string) $method),
            'status_code' => (int) $status_code,
            'user_agent'  => $user_agent
        ];

        return $this->db->insert($this->logs_table, $data);
    }

    /*
    |--------------------------------------------------------------------------
    | Count Recent Requests by IP
    |--------------------------------------------------------------------------
    */
    public function count_recent_requests_by_ip($ip_address, $minutes = 60)
    {
        $ip_address = trim((string) $ip_address);
        $minutes = (int) $minutes;

        if ($ip_address === '') {
            return 0;
        }

        if ($minutes <= 0) {
            $minutes = 60;
        }

        return $this->db
            ->where('ip_address', $ip_address)
            ->where('accessed_at >=', date('Y-m-d H:i:s', strtotime("-{$minutes} minutes")))
            ->count_all_results($this->logs_table);
    }

    /*
    |--------------------------------------------------------------------------
    | Count Active API Keys
    |--------------------------------------------------------------------------
    */
    public function count_active_keys($user_id)
    {
        $user_id = (int) $user_id;

        if ($user_id <= 0) {
            return 0;
        }

        return $this->db
            ->where('user_id', $user_id)
            ->where('status', 'active')
            ->count_all_results($this->keys_table);
    }
}