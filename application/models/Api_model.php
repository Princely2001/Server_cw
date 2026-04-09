<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api_model extends CI_Model {

    public function get_user_keys($user_id) {
        return $this->db->where('user_id', $user_id)
                        ->order_by('created_at', 'DESC')
                        ->get('api_keys')
                        ->result();
    }

    public function generate_key($user_id) {
        $api_key = bin2hex(random_bytes(32)); 
        
        $data = [
            'user_id' => $user_id,
            'api_key' => $api_key,
            'status'  => 'active'
        ];
        
        return $this->db->insert('api_keys', $data);
    }

    public function revoke_key($key_id, $user_id) {
        return $this->db->where('id', $key_id)
                        ->where('user_id', $user_id)
                        ->update('api_keys', ['status' => 'revoked']);
    }

    public function get_usage_logs($user_id) {
        $this->db->select('api_logs.*, api_keys.api_key');
        $this->db->from('api_logs');
        $this->db->join('api_keys', 'api_keys.id = api_logs.api_key_id');
        $this->db->where('api_keys.user_id', $user_id);
        $this->db->order_by('api_logs.accessed_at', 'DESC');
        $this->db->limit(50);
        
        return $this->db->get()->result();
    }
    
    public function is_valid_key($api_key) {
        return $this->db->where('api_key', $api_key)
                        ->where('status', 'active')
                        ->get('api_keys')
                        ->row();
    }

    public function log_request($key_id, $endpoint, $ip_address) {
        $data = [
            'api_key_id' => $key_id,
            'endpoint'   => $endpoint,
            'ip_address' => $ip_address
        ];
        return $this->db->insert('api_logs', $data);
    }
}