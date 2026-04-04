<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Profile extends CI_Controller {

    public function __construct() {
        parent::__construct();
        
        // Security Headers 
        $this->output->set_header('X-Content-Type-Options: nosniff');
        $this->output->set_header('X-Frame-Options: SAMEORIGIN');
        $this->output->set_header('X-XSS-Protection: 1; mode=block');
        $this->output->set_header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        $this->output->set_header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self' 'unsafe-inline';");

        
        // Ensure user is logged in
        if (!$this->session->userdata('logged_in')) {
            redirect('auth/login');
        }
        
        // Load required models, helpers, and libraries
        $this->load->model('Profile_model');
        $this->load->helper(['security', 'form', 'url']); 
        $this->load->library('form_validation');
    }

    // MAIN DASHBOARD 
    public function index() {
        $user_id = $this->session->userdata('user_id');
        
        $data['title'] = 'My Alumni Profile';
        
        // Fetch all profile data using the Profile_model
        $data['profile'] = $this->Profile_model->get_profile($user_id);
        $data['degrees'] = $this->Profile_model->get_degrees($user_id);
        $data['certifications'] = $this->Profile_model->get_certifications($user_id);
        $data['licences'] = $this->Profile_model->get_licences($user_id);
        $data['courses'] = $this->Profile_model->get_courses($user_id);
        $data['employments'] = $this->Profile_model->get_employment($user_id);

        //Calculate Profile Completion Status 
        $score = 0;
        $total_criteria = 6; 

        if ($data['profile']) {
            if (!empty($data['profile']->bio)) $score++;
            if (!empty($data['profile']->linkedin_url)) $score++;
            if (!empty($data['profile']->profile_image)) $score++;
        }
        if (!empty($data['degrees'])) $score++;
        if (!empty($data['employments'])) $score++;
        if (!empty($data['certifications']) || !empty($data['licences']) || !empty($data['courses'])) $score++; 

        $data['completion_percentage'] = round(($score / $total_criteria) * 100);
       
        
        $this->load->view('profile/index', $data);
    }

    // UPDATE BASIC INFO
    public function update_basic() {
        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('bio', 'Biography', 'trim|xss_clean|max_length[1000]');
            $this->form_validation->set_rules('linkedin_url', 'LinkedIn URL', 'trim|valid_url|xss_clean');

            if ($this->form_validation->run() === TRUE) {
                $user_id = $this->session->userdata('user_id');
                
                $update_data = [
                    'bio' => $this->input->post('bio', TRUE),
                    'linkedin_url' => $this->input->post('linkedin_url', TRUE)
                ];

                // --- Handle Image Upload ---
                if (!empty($_FILES['profile_image']['name'])) {
                    
                    // Upload Configuration
                    $config['upload_path']   = './uploads/profile_images/';
                    $config['allowed_types'] = 'gif|jpg|jpeg|png';
                    $config['max_size']      = 2048; 
                    $config['encrypt_name']  = TRUE; 

                    $this->load->library('upload', $config);

                    if ($this->upload->do_upload('profile_image')) {
                        $uploadData = $this->upload->data();
                        $update_data['profile_image'] = $uploadData['file_name'];

                        // Delete the old image to save space
                        $current_profile = $this->Profile_model->get_profile($user_id);
                        if ($current_profile && !empty($current_profile->profile_image)) {
                            $old_file = './uploads/profile_images/' . $current_profile->profile_image;
                            if (file_exists($old_file)) {
                                unlink($old_file);
                            }
                        }
                    } else {
                        // Upload failed
                        $this->session->set_flashdata('error', $this->upload->display_errors('', ''));
                        redirect('profile/index');
                        return; 
                    }
                }

                $this->Profile_model->update_profile($user_id, $update_data);
                $this->session->set_flashdata('success', 'Profile updated successfully.');
            } else {
                $this->session->set_flashdata('error', validation_errors());
            }
        }
        redirect('profile/index');
    }

   
    //  DEGREES 
    public function add_degree() {
        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('degree_name', 'Degree Name', 'required|trim|xss_clean');
            $this->form_validation->set_rules('university_url', 'University URL', 'required|trim|valid_url|xss_clean');
            $this->form_validation->set_rules('completion_date', 'Completion Date', 'required|trim');

            if ($this->form_validation->run() === TRUE) {
                $degree_data = [
                    'user_id' => $this->session->userdata('user_id'),
                    'degree_name' => $this->input->post('degree_name', TRUE),
                    'university_url' => $this->input->post('university_url', TRUE),
                    'completion_date' => $this->input->post('completion_date', TRUE)
                ];

                $this->Profile_model->add_degree($degree_data);
                $this->session->set_flashdata('success', 'Degree added successfully.');
            } else {
                $this->session->set_flashdata('error', validation_errors());
            }
        }
        redirect('profile/index');
    }

    public function edit_degree($id) {
        $user_id = $this->session->userdata('user_id');
        $data['degree'] = $this->Profile_model->get_degree_by_id($id, $user_id);
        
        if (!$data['degree']) { show_404(); }
        $data['title'] = 'Edit Degree';

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('degree_name', 'Degree Name', 'required|trim|xss_clean');
            $this->form_validation->set_rules('university_url', 'University URL', 'required|trim|valid_url|xss_clean');
            $this->form_validation->set_rules('completion_date', 'Completion Date', 'required|trim');

            if ($this->form_validation->run() === TRUE) {
                $degree_data = [
                    'degree_name' => $this->input->post('degree_name', TRUE),
                    'university_url' => $this->input->post('university_url', TRUE),
                    'completion_date' => $this->input->post('completion_date', TRUE)
                ];
                $this->Profile_model->update_degree($id, $user_id, $degree_data);
                $this->session->set_flashdata('success', 'Degree updated successfully.');
                redirect('profile/index');
            } else {
                $this->session->set_flashdata('error', validation_errors());
            }
        }
        $this->load->view('profile/edit_degree', $data);
    }

    public function delete_degree($id) {
        $user_id = $this->session->userdata('user_id');
        $this->Profile_model->delete_degree($id, $user_id);
        $this->session->set_flashdata('success', 'Degree removed successfully.');
        redirect('profile/index');
    }

  
    // CERTIFICATIONS

    public function add_certification() {
        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('certification_name', 'Certification Name', 'required|trim|xss_clean');
            $this->form_validation->set_rules('course_url', 'Course URL', 'required|trim|valid_url|xss_clean');
            $this->form_validation->set_rules('completion_date', 'Completion Date', 'required|trim');

            if ($this->form_validation->run() === TRUE) {
                $cert_data = [
                    'user_id' => $this->session->userdata('user_id'),
                    'certification_name' => $this->input->post('certification_name', TRUE),
                    'course_url' => $this->input->post('course_url', TRUE),
                    'completion_date' => $this->input->post('completion_date', TRUE)
                ];

                $this->Profile_model->add_certification($cert_data);
                $this->session->set_flashdata('success', 'Certification added successfully.');
            } else {
                $this->session->set_flashdata('error', validation_errors());
            }
        }
        redirect('profile/index');
    }

    public function edit_certification($id) {
        $user_id = $this->session->userdata('user_id');
        $data['certification'] = $this->Profile_model->get_certification_by_id($id, $user_id);
        
        if (!$data['certification']) { show_404(); }
        $data['title'] = 'Edit Certification';

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('certification_name', 'Certification Name', 'required|trim|xss_clean');
            $this->form_validation->set_rules('course_url', 'Course URL', 'required|trim|valid_url|xss_clean');
            $this->form_validation->set_rules('completion_date', 'Completion Date', 'required|trim');

            if ($this->form_validation->run() === TRUE) {
                $cert_data = [
                    'certification_name' => $this->input->post('certification_name', TRUE),
                    'course_url' => $this->input->post('course_url', TRUE),
                    'completion_date' => $this->input->post('completion_date', TRUE)
                ];
                $this->Profile_model->update_certification($id, $user_id, $cert_data);
                $this->session->set_flashdata('success', 'Certification updated successfully.');
                redirect('profile/index');
            } else {
                $this->session->set_flashdata('error', validation_errors());
            }
        }
        $this->load->view('profile/edit_certification', $data);
    }

    public function delete_certification($id) {
        $user_id = $this->session->userdata('user_id');
        $this->Profile_model->delete_certification($id, $user_id);
        $this->session->set_flashdata('success', 'Certification removed successfully.');
        redirect('profile/index');
    }


    // LICENCES
  
    public function add_licence() {
        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('licence_name', 'Licence Name', 'required|trim|xss_clean');
            $this->form_validation->set_rules('awarding_body_url', 'Awarding Body URL', 'required|trim|valid_url|xss_clean');
            $this->form_validation->set_rules('completion_date', 'Completion Date', 'required|trim');

            if ($this->form_validation->run() === TRUE) {
                $licence_data = [
                    'user_id' => $this->session->userdata('user_id'),
                    'licence_name' => $this->input->post('licence_name', TRUE),
                    'awarding_body_url' => $this->input->post('awarding_body_url', TRUE),
                    'completion_date' => $this->input->post('completion_date', TRUE)
                ];

                $this->Profile_model->add_licence($licence_data);
                $this->session->set_flashdata('success', 'Licence added successfully.');
            } else {
                $this->session->set_flashdata('error', validation_errors());
            }
        }
        redirect('profile/index');
    }

    public function edit_licence($id) {
        $user_id = $this->session->userdata('user_id');
        $data['licence'] = $this->Profile_model->get_licence_by_id($id, $user_id);
        
        if (!$data['licence']) { show_404(); }
        $data['title'] = 'Edit Licence';

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('licence_name', 'Licence Name', 'required|trim|xss_clean');
            $this->form_validation->set_rules('awarding_body_url', 'Awarding Body URL', 'required|trim|valid_url|xss_clean');
            $this->form_validation->set_rules('completion_date', 'Completion Date', 'required|trim');

            if ($this->form_validation->run() === TRUE) {
                $licence_data = [
                    'licence_name' => $this->input->post('licence_name', TRUE),
                    'awarding_body_url' => $this->input->post('awarding_body_url', TRUE),
                    'completion_date' => $this->input->post('completion_date', TRUE)
                ];
                $this->Profile_model->update_licence($id, $user_id, $licence_data);
                $this->session->set_flashdata('success', 'Licence updated successfully.');
                redirect('profile/index');
            } else {
                $this->session->set_flashdata('error', validation_errors());
            }
        }
        $this->load->view('profile/edit_licence', $data);
    }

    public function delete_licence($id) {
        $user_id = $this->session->userdata('user_id');
        $this->Profile_model->delete_licence($id, $user_id);
        $this->session->set_flashdata('success', 'Licence removed successfully.');
        redirect('profile/index');
    }

   
    //SHORT COURSES 
    public function add_course() {
        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('course_name', 'Course Name', 'required|trim|xss_clean');
            $this->form_validation->set_rules('course_url', 'Course URL', 'required|trim|valid_url|xss_clean');
            $this->form_validation->set_rules('completion_date', 'Completion Date', 'required|trim');

            if ($this->form_validation->run() === TRUE) {
                $course_data = [
                    'user_id' => $this->session->userdata('user_id'),
                    'course_name' => $this->input->post('course_name', TRUE),
                    'course_url' => $this->input->post('course_url', TRUE),
                    'completion_date' => $this->input->post('completion_date', TRUE)
                ];

                $this->Profile_model->add_course($course_data);
                $this->session->set_flashdata('success', 'Course added successfully.');
            } else {
                $this->session->set_flashdata('error', validation_errors());
            }
        }
        redirect('profile/index');
    }

    public function edit_course($id) {
        $user_id = $this->session->userdata('user_id');
        $data['course'] = $this->Profile_model->get_course_by_id($id, $user_id);
        
        if (!$data['course']) { show_404(); }
        $data['title'] = 'Edit Course';

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('course_name', 'Course Name', 'required|trim|xss_clean');
            $this->form_validation->set_rules('course_url', 'Course URL', 'required|trim|valid_url|xss_clean');
            $this->form_validation->set_rules('completion_date', 'Completion Date', 'required|trim');

            if ($this->form_validation->run() === TRUE) {
                $course_data = [
                    'course_name' => $this->input->post('course_name', TRUE),
                    'course_url' => $this->input->post('course_url', TRUE),
                    'completion_date' => $this->input->post('completion_date', TRUE)
                ];
                $this->Profile_model->update_course($id, $user_id, $course_data);
                $this->session->set_flashdata('success', 'Course updated successfully.');
                redirect('profile/index');
            } else {
                $this->session->set_flashdata('error', validation_errors());
            }
        }
        $this->load->view('profile/edit_course', $data);
    }

    public function delete_course($id) {
        $user_id = $this->session->userdata('user_id');
        $this->Profile_model->delete_course($id, $user_id);
        $this->session->set_flashdata('success', 'Course removed successfully.');
        redirect('profile/index');
    }

    // EMPLOYMENT HISTORY 
    public function add_employment() {
        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('company_name', 'Company Name', 'required|trim|xss_clean');
            $this->form_validation->set_rules('role', 'Role', 'required|trim|xss_clean');
            $this->form_validation->set_rules('start_date', 'Start Date', 'required|trim');
            $this->form_validation->set_rules('end_date', 'End Date', 'trim');

            if ($this->form_validation->run() === TRUE) {
                $emp_data = [
                    'user_id' => $this->session->userdata('user_id'),
                    'company_name' => $this->input->post('company_name', TRUE),
                    'role' => $this->input->post('role', TRUE),
                    'start_date' => $this->input->post('start_date', TRUE),
                    'end_date' => $this->input->post('end_date', TRUE) ?: NULL 
                ];

                $this->Profile_model->add_employment($emp_data);
                $this->session->set_flashdata('success', 'Employment history added successfully.');
            } else {
                $this->session->set_flashdata('error', validation_errors());
            }
        }
        redirect('profile/index');
    }

    public function edit_employment($id) {
        $user_id = $this->session->userdata('user_id');
        $data['employment'] = $this->Profile_model->get_employment_by_id($id, $user_id);
        
        if (!$data['employment']) { show_404(); }
        $data['title'] = 'Edit Employment';

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('company_name', 'Company Name', 'required|trim|xss_clean');
            $this->form_validation->set_rules('role', 'Role', 'required|trim|xss_clean');
            $this->form_validation->set_rules('start_date', 'Start Date', 'required|trim');
            $this->form_validation->set_rules('end_date', 'End Date', 'trim');

            if ($this->form_validation->run() === TRUE) {
                $emp_data = [
                    'company_name' => $this->input->post('company_name', TRUE),
                    'role' => $this->input->post('role', TRUE),
                    'start_date' => $this->input->post('start_date', TRUE),
                    'end_date' => $this->input->post('end_date', TRUE) ?: NULL 
                ];
                $this->Profile_model->update_employment($id, $user_id, $emp_data);
                $this->session->set_flashdata('success', 'Employment history updated successfully.');
                redirect('profile/index');
            } else {
                $this->session->set_flashdata('error', validation_errors());
            }
        }
        $this->load->view('profile/edit_employment', $data);
    }

    public function delete_employment($id) {
        $user_id = $this->session->userdata('user_id');
        $this->Profile_model->delete_employment($id, $user_id);
        $this->session->set_flashdata('success', 'Employment history removed successfully.');
        redirect('profile/index');
    }
}