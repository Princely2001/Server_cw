<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Profile_model extends CI_Model {

    // MAIN PROFILE METHODS 
    public function get_profile($user_id) {
        return $this->db->where('user_id', $user_id)->get('alumni_profiles')->row();
    }

    public function update_profile($user_id, $data) {
        $exists = $this->db->where('user_id', $user_id)->count_all_results('alumni_profiles');
        if ($exists > 0) {
            return $this->db->where('user_id', $user_id)->update('alumni_profiles', $data);
        } else {
            $data['user_id'] = $user_id;
            return $this->db->insert('alumni_profiles', $data);
        }
    }

    // DEGREE METHODS 
    public function get_degrees($user_id) {
        return $this->db->where('user_id', $user_id)->get('alumni_degrees')->result();
    }
    
    public function get_degree_by_id($id, $user_id) {
        return $this->db->where('id', $id)->where('user_id', $user_id)->get('alumni_degrees')->row();
    }

    public function add_degree($data) {
        return $this->db->insert('alumni_degrees', $data);
    }
    
    public function update_degree($id, $user_id, $data) {
        return $this->db->where('id', $id)->where('user_id', $user_id)->update('alumni_degrees', $data);
    }

    public function delete_degree($id, $user_id) {
        return $this->db->where('id', $id)->where('user_id', $user_id)->delete('alumni_degrees');
    }

    // --- CERTIFICATION METHODS ---
    public function get_certifications($user_id) {
        return $this->db->where('user_id', $user_id)->get('alumni_certifications')->result();
    }
    
    public function get_certification_by_id($id, $user_id) {
        return $this->db->where('id', $id)->where('user_id', $user_id)->get('alumni_certifications')->row();
    }

    public function add_certification($data) {
        return $this->db->insert('alumni_certifications', $data);
    }
    
    public function update_certification($id, $user_id, $data) {
        return $this->db->where('id', $id)->where('user_id', $user_id)->update('alumni_certifications', $data);
    }

    public function delete_certification($id, $user_id) {
        return $this->db->where('id', $id)->where('user_id', $user_id)->delete('alumni_certifications');
    }
    //LICENCE METHODS 
   
    public function get_licences($user_id) {
        return $this->db->where('user_id', $user_id)->get('alumni_licences')->result();
    }
    
    public function get_licence_by_id($id, $user_id) {
        return $this->db->where('id', $id)->where('user_id', $user_id)->get('alumni_licences')->row();
    }

    public function add_licence($data) {
        return $this->db->insert('alumni_licences', $data);
    }
    
    public function update_licence($id, $user_id, $data) {
        return $this->db->where('id', $id)->where('user_id', $user_id)->update('alumni_licences', $data);
    }

    public function delete_licence($id, $user_id) {
        return $this->db->where('id', $id)->where('user_id', $user_id)->delete('alumni_licences');
    }


    // SHORT COURSE METHODS 
    public function get_courses($user_id) {
        return $this->db->where('user_id', $user_id)->get('alumni_courses')->result();
    }
    
    public function get_course_by_id($id, $user_id) {
        return $this->db->where('id', $id)->where('user_id', $user_id)->get('alumni_courses')->row();
    }

    public function add_course($data) {
        return $this->db->insert('alumni_courses', $data);
    }
    
    public function update_course($id, $user_id, $data) {
        return $this->db->where('id', $id)->where('user_id', $user_id)->update('alumni_courses', $data);
    }

    public function delete_course($id, $user_id) {
        return $this->db->where('id', $id)->where('user_id', $user_id)->delete('alumni_courses');
    }

    // EMPLOYMENT METHODS 
    public function get_employment($user_id) {
        // Order by start_date descending (newest first)
        return $this->db->where('user_id', $user_id)->order_by('start_date', 'DESC')->get('alumni_employment')->result();
    }
    
    public function get_employment_by_id($id, $user_id) {
        return $this->db->where('id', $id)->where('user_id', $user_id)->get('alumni_employment')->row();
    }

    public function add_employment($data) {
        return $this->db->insert('alumni_employment', $data);
    }
    
    public function update_employment($id, $user_id, $data) {
        return $this->db->where('id', $id)->where('user_id', $user_id)->update('alumni_employment', $data);
    }

    public function delete_employment($id, $user_id) {
        return $this->db->where('id', $id)->where('user_id', $user_id)->delete('alumni_employment');
    }
}