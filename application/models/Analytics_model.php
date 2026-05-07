<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Analytics_model extends CI_Model
{
    private $users_table = 'users';
    private $profiles_table = 'alumni_profiles';
    private $degrees_table = 'alumni_degrees';
    private $certifications_table = 'alumni_certifications';
    private $courses_table = 'alumni_courses';
    private $licences_table = 'alumni_licences';
    private $employment_table = 'alumni_employment';
    private $bids_table = 'alumni_bids';

    /*
    |--------------------------------------------------------------------------
    | Filter Helper
    |--------------------------------------------------------------------------
    | Supported filters:
    | programme
    | graduation_year
    | industry_sector
    */
    private function apply_profile_filters($filters = [])
    {
        if (!empty($filters['programme'])) {
            $this->db->where('p.programme', trim($filters['programme']));
        }

        if (!empty($filters['graduation_year'])) {
            $this->db->where('p.graduation_year', (int) $filters['graduation_year']);
        }

        if (!empty($filters['industry_sector'])) {
            $this->db->where('p.industry_sector', trim($filters['industry_sector']));
        }
    }

    private function apply_employment_filters($filters = [])
    {
        if (!empty($filters['programme'])) {
            $this->db->where('p.programme', trim($filters['programme']));
        }

        if (!empty($filters['graduation_year'])) {
            $this->db->where('p.graduation_year', (int) $filters['graduation_year']);
        }

        if (!empty($filters['industry_sector'])) {
            $this->db->where('e.industry_sector', trim($filters['industry_sector']));
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Filter Dropdown Data
    |--------------------------------------------------------------------------
    */
    public function get_filter_options()
    {
        return [
            'programmes' => $this->get_distinct_profile_values('programme'),
            'graduation_years' => $this->get_distinct_profile_values('graduation_year'),
            'industry_sectors' => $this->get_distinct_profile_values('industry_sector')
        ];
    }

    private function get_distinct_profile_values($column)
    {
        $allowed = [
            'programme',
            'graduation_year',
            'industry_sector',
            'current_job_title',
            'current_employer',
            'country',
            'city'
        ];

        if (!in_array($column, $allowed, true)) {
            return [];
        }

        return $this->db->select($column)
                        ->from($this->profiles_table)
                        ->where($column . ' IS NOT NULL', null, false)
                        ->where($column . ' !=', '')
                        ->group_by($column)
                        ->order_by($column, 'ASC')
                        ->get()
                        ->result();
    }

    /*
    |--------------------------------------------------------------------------
    | Summary Cards
    |--------------------------------------------------------------------------
    */
    public function get_summary_stats($filters = [])
    {
        $this->db->from($this->profiles_table . ' p');
        $this->db->join($this->users_table . ' u', 'u.id = p.user_id');
        $this->db->where('u.role', 'alumnus');
        $this->db->where('u.is_active', 1);
        $this->apply_profile_filters($filters);
        $total_alumni = $this->db->count_all_results();

        $this->db->select('COUNT(DISTINCT p.programme) AS total_programmes', false)
                 ->from($this->profiles_table . ' p')
                 ->join($this->users_table . ' u', 'u.id = p.user_id')
                 ->where('u.role', 'alumnus')
                 ->where('u.is_active', 1)
                 ->where('p.programme IS NOT NULL', null, false)
                 ->where('p.programme !=', '');
        $this->apply_profile_filters($filters);
        $programme_row = $this->db->get()->row();

        $top_industry = $this->get_top_single_value('industry_sector', $filters);
        $top_employer = $this->get_top_single_value('current_employer', $filters);
        $top_job_title = $this->get_top_single_value('current_job_title', $filters);
        $top_location = $this->get_top_location($filters);

        $cert_count = $this->count_related_records($this->certifications_table, $filters);
        $course_count = $this->count_related_records($this->courses_table, $filters);
        $licence_count = $this->count_related_records($this->licences_table, $filters);

        return [
            'total_alumni' => (int) $total_alumni,
            'total_programmes' => $programme_row ? (int) $programme_row->total_programmes : 0,
            'top_industry' => $top_industry,
            'top_employer' => $top_employer,
            'top_job_title' => $top_job_title,
            'top_location' => $top_location,
            'total_certifications' => (int) $cert_count,
            'total_courses' => (int) $course_count,
            'total_licences' => (int) $licence_count
        ];
    }

    private function get_top_single_value($column, $filters = [])
    {
        $allowed = [
            'industry_sector',
            'current_job_title',
            'current_employer',
            'programme',
            'country',
            'city'
        ];

        if (!in_array($column, $allowed, true)) {
            return null;
        }

        $row = $this->db->select('p.' . $column . ' AS label, COUNT(*) AS total', false)
                        ->from($this->profiles_table . ' p')
                        ->join($this->users_table . ' u', 'u.id = p.user_id')
                        ->where('u.role', 'alumnus')
                        ->where('u.is_active', 1)
                        ->where('p.' . $column . ' IS NOT NULL', null, false)
                        ->where('p.' . $column . ' !=', '')
                        ->group_by('p.' . $column)
                        ->order_by('total', 'DESC')
                        ->limit(1);

        $this->apply_profile_filters($filters);

        $row = $this->db->get()->row();

        if (!$row) {
            return [
                'label' => 'No data',
                'total' => 0
            ];
        }

        return [
            'label' => $row->label,
            'total' => (int) $row->total
        ];
    }

    private function get_top_location($filters = [])
    {
        $this->db->select("CONCAT(COALESCE(p.city, ''), CASE WHEN p.city IS NOT NULL AND p.country IS NOT NULL THEN ', ' ELSE '' END, COALESCE(p.country, '')) AS label, COUNT(*) AS total", false)
                 ->from($this->profiles_table . ' p')
                 ->join($this->users_table . ' u', 'u.id = p.user_id')
                 ->where('u.role', 'alumnus')
                 ->where('u.is_active', 1)
                 ->where("(p.city IS NOT NULL OR p.country IS NOT NULL)", null, false)
                 ->group_by('p.city, p.country')
                 ->order_by('total', 'DESC')
                 ->limit(1);

        $this->apply_profile_filters($filters);

        $row = $this->db->get()->row();

        if (!$row || trim($row->label) === '') {
            return [
                'label' => 'No data',
                'total' => 0
            ];
        }

        return [
            'label' => $row->label,
            'total' => (int) $row->total
        ];
    }

    private function count_related_records($table, $filters = [])
    {
        $allowed_tables = [
            $this->certifications_table,
            $this->courses_table,
            $this->licences_table
        ];

        if (!in_array($table, $allowed_tables, true)) {
            return 0;
        }

        $this->db->from($table . ' r')
                 ->join($this->profiles_table . ' p', 'p.user_id = r.user_id')
                 ->join($this->users_table . ' u', 'u.id = r.user_id')
                 ->where('u.role', 'alumnus')
                 ->where('u.is_active', 1);

        $this->apply_profile_filters($filters);

        return $this->db->count_all_results();
    }

    /*
    |--------------------------------------------------------------------------
    | Alumni List
    |--------------------------------------------------------------------------
    */
    public function get_alumni_list($filters = [], $limit = 100, $offset = 0)
    {
        $this->db->select('
                    u.id AS user_id,
                    u.first_name,
                    u.last_name,
                    u.university_email,
                    p.bio,
                    p.linkedin_url,
                    p.profile_image,
                    p.programme,
                    p.graduation_year,
                    p.industry_sector,
                    p.current_job_title,
                    p.current_employer,
                    p.country,
                    p.city,
                    p.profile_completion,
                    p.is_featured,
                    p.featured_for_date,
                    p.updated_at
                ')
                ->from($this->profiles_table . ' p')
                ->join($this->users_table . ' u', 'u.id = p.user_id')
                ->where('u.role', 'alumnus')
                ->where('u.is_active', 1);

        $this->apply_profile_filters($filters);

        $this->db->order_by('p.updated_at', 'DESC');
        $this->db->limit((int) $limit, (int) $offset);

        return $this->db->get()->result();
    }

    public function count_alumni_list($filters = [])
    {
        $this->db->from($this->profiles_table . ' p')
                 ->join($this->users_table . ' u', 'u.id = p.user_id')
                 ->where('u.role', 'alumnus')
                 ->where('u.is_active', 1);

        $this->apply_profile_filters($filters);

        return $this->db->count_all_results();
    }

    /*
    |--------------------------------------------------------------------------
    | Chart 1: Industry Distribution
    |--------------------------------------------------------------------------
    */
    public function get_industry_distribution($filters = [])
    {
        $this->db->select('p.industry_sector AS label, COUNT(*) AS total', false)
                 ->from($this->profiles_table . ' p')
                 ->join($this->users_table . ' u', 'u.id = p.user_id')
                 ->where('u.role', 'alumnus')
                 ->where('u.is_active', 1)
                 ->where('p.industry_sector IS NOT NULL', null, false)
                 ->where('p.industry_sector !=', '')
                 ->group_by('p.industry_sector')
                 ->order_by('total', 'DESC');

        $this->apply_profile_filters($filters);

        return $this->db->get()->result();
    }

    /*
    |--------------------------------------------------------------------------
    | Chart 2: Graduation Year Distribution
    |--------------------------------------------------------------------------
    */
    public function get_graduation_year_distribution($filters = [])
    {
        $this->db->select('p.graduation_year AS label, COUNT(*) AS total', false)
                 ->from($this->profiles_table . ' p')
                 ->join($this->users_table . ' u', 'u.id = p.user_id')
                 ->where('u.role', 'alumnus')
                 ->where('u.is_active', 1)
                 ->where('p.graduation_year IS NOT NULL', null, false)
                 ->group_by('p.graduation_year')
                 ->order_by('p.graduation_year', 'ASC');

        $this->apply_profile_filters($filters);

        return $this->db->get()->result();
    }

    /*
    |--------------------------------------------------------------------------
    | Chart 3: Most Common Job Titles
    |--------------------------------------------------------------------------
    */
    public function get_job_title_distribution($filters = [], $limit = 10)
    {
        $this->db->select('p.current_job_title AS label, COUNT(*) AS total', false)
                 ->from($this->profiles_table . ' p')
                 ->join($this->users_table . ' u', 'u.id = p.user_id')
                 ->where('u.role', 'alumnus')
                 ->where('u.is_active', 1)
                 ->where('p.current_job_title IS NOT NULL', null, false)
                 ->where('p.current_job_title !=', '')
                 ->group_by('p.current_job_title')
                 ->order_by('total', 'DESC')
                 ->limit((int) $limit);

        $this->apply_profile_filters($filters);

        return $this->db->get()->result();
    }

    /*
    |--------------------------------------------------------------------------
    | Chart 4: Top Employers
    |--------------------------------------------------------------------------
    */
    public function get_top_employers($filters = [], $limit = 10)
    {
        $this->db->select('p.current_employer AS label, COUNT(*) AS total', false)
                 ->from($this->profiles_table . ' p')
                 ->join($this->users_table . ' u', 'u.id = p.user_id')
                 ->where('u.role', 'alumnus')
                 ->where('u.is_active', 1)
                 ->where('p.current_employer IS NOT NULL', null, false)
                 ->where('p.current_employer !=', '')
                 ->group_by('p.current_employer')
                 ->order_by('total', 'DESC')
                 ->limit((int) $limit);

        $this->apply_profile_filters($filters);

        return $this->db->get()->result();
    }

    /*
    |--------------------------------------------------------------------------
    | Chart 5: Geographic Distribution
    |--------------------------------------------------------------------------
    */
    public function get_geographic_distribution($filters = [])
    {
        $this->db->select("
                    CASE 
                        WHEN p.city IS NOT NULL AND p.city != '' AND p.country IS NOT NULL AND p.country != ''
                            THEN CONCAT(p.city, ', ', p.country)
                        WHEN p.country IS NOT NULL AND p.country != ''
                            THEN p.country
                        WHEN p.city IS NOT NULL AND p.city != ''
                            THEN p.city
                        ELSE 'Unknown'
                    END AS label,
                    COUNT(*) AS total
                ", false)
                ->from($this->profiles_table . ' p')
                ->join($this->users_table . ' u', 'u.id = p.user_id')
                ->where('u.role', 'alumnus')
                ->where('u.is_active', 1)
                ->group_by('label')
                ->order_by('total', 'DESC');

        $this->apply_profile_filters($filters);

        return $this->db->get()->result();
    }

    /*
    |--------------------------------------------------------------------------
    | Chart 6: Certification Trends
    |--------------------------------------------------------------------------
    */
    public function get_certification_trends($filters = [])
    {
        $this->db->select("DATE_FORMAT(c.completion_date, '%Y-%m') AS label, COUNT(*) AS total", false)
                 ->from($this->certifications_table . ' c')
                 ->join($this->profiles_table . ' p', 'p.user_id = c.user_id')
                 ->join($this->users_table . ' u', 'u.id = c.user_id')
                 ->where('u.role', 'alumnus')
                 ->where('u.is_active', 1)
                 ->where('c.completion_date IS NOT NULL', null, false)
                 ->group_by("DATE_FORMAT(c.completion_date, '%Y-%m')", false)
                 ->order_by('label', 'ASC');

        $this->apply_profile_filters($filters);

        return $this->db->get()->result();
    }

    /*
    |--------------------------------------------------------------------------
    | Chart 7: Professional Course Trends
    |--------------------------------------------------------------------------
    */
    public function get_course_trends($filters = [])
    {
        $this->db->select("DATE_FORMAT(c.completion_date, '%Y-%m') AS label, COUNT(*) AS total", false)
                 ->from($this->courses_table . ' c')
                 ->join($this->profiles_table . ' p', 'p.user_id = c.user_id')
                 ->join($this->users_table . ' u', 'u.id = c.user_id')
                 ->where('u.role', 'alumnus')
                 ->where('u.is_active', 1)
                 ->where('c.completion_date IS NOT NULL', null, false)
                 ->group_by("DATE_FORMAT(c.completion_date, '%Y-%m')", false)
                 ->order_by('label', 'ASC');

        $this->apply_profile_filters($filters);

        return $this->db->get()->result();
    }

    /*
    |--------------------------------------------------------------------------
    | Chart 8: Skills Gap / Professional Development Radar
    |--------------------------------------------------------------------------
    | This groups certifications and courses into skill categories.
    */
    public function get_skills_gap_data($filters = [])
    {
        $raw_items = [];

        $this->db->select('c.certification_name AS item_name')
                 ->from($this->certifications_table . ' c')
                 ->join($this->profiles_table . ' p', 'p.user_id = c.user_id')
                 ->join($this->users_table . ' u', 'u.id = c.user_id')
                 ->where('u.role', 'alumnus')
                 ->where('u.is_active', 1);

        $this->apply_profile_filters($filters);

        $certifications = $this->db->get()->result();

        foreach ($certifications as $row) {
            $raw_items[] = $row->item_name;
        }

        $this->db->select('c.course_name AS item_name')
                 ->from($this->courses_table . ' c')
                 ->join($this->profiles_table . ' p', 'p.user_id = c.user_id')
                 ->join($this->users_table . ' u', 'u.id = c.user_id')
                 ->where('u.role', 'alumnus')
                 ->where('u.is_active', 1);

        $this->apply_profile_filters($filters);

        $courses = $this->db->get()->result();

        foreach ($courses as $row) {
            $raw_items[] = $row->item_name;
        }

        $categories = [
            'Cloud Computing' => 0,
            'Cybersecurity' => 0,
            'Data Analytics' => 0,
            'Software Engineering' => 0,
            'Project Management' => 0,
            'Networking' => 0,
            'AI / Machine Learning' => 0,
            'Professional Skills' => 0
        ];

        foreach ($raw_items as $item) {
            $name = strtolower((string) $item);

            if (
                strpos($name, 'aws') !== false ||
                strpos($name, 'azure') !== false ||
                strpos($name, 'gcp') !== false ||
                strpos($name, 'cloud') !== false ||
                strpos($name, 'docker') !== false ||
                strpos($name, 'kubernetes') !== false
            ) {
                $categories['Cloud Computing']++;
            } elseif (
                strpos($name, 'security') !== false ||
                strpos($name, 'cyber') !== false ||
                strpos($name, 'ethical hacking') !== false ||
                strpos($name, 'cissp') !== false
            ) {
                $categories['Cybersecurity']++;
            } elseif (
                strpos($name, 'data') !== false ||
                strpos($name, 'analytics') !== false ||
                strpos($name, 'sql') !== false ||
                strpos($name, 'tableau') !== false ||
                strpos($name, 'power bi') !== false
            ) {
                $categories['Data Analytics']++;
            } elseif (
                strpos($name, 'java') !== false ||
                strpos($name, 'python') !== false ||
                strpos($name, 'software') !== false ||
                strpos($name, 'web') !== false ||
                strpos($name, 'programming') !== false
            ) {
                $categories['Software Engineering']++;
            } elseif (
                strpos($name, 'agile') !== false ||
                strpos($name, 'scrum') !== false ||
                strpos($name, 'project') !== false ||
                strpos($name, 'prince2') !== false
            ) {
                $categories['Project Management']++;
            } elseif (
                strpos($name, 'network') !== false ||
                strpos($name, 'cisco') !== false ||
                strpos($name, 'ccna') !== false
            ) {
                $categories['Networking']++;
            } elseif (
                strpos($name, 'ai') !== false ||
                strpos($name, 'machine learning') !== false ||
                strpos($name, 'deep learning') !== false
            ) {
                $categories['AI / Machine Learning']++;
            } else {
                $categories['Professional Skills']++;
            }
        }

        $result = [];

        foreach ($categories as $label => $total) {
            $result[] = [
                'label' => $label,
                'total' => (int) $total,
                'severity' => $this->get_gap_severity((int) $total)
            ];
        }

        return $result;
    }

    private function get_gap_severity($total)
    {
        if ($total >= 10) {
            return 'critical';
        }

        if ($total >= 5) {
            return 'significant';
        }

        if ($total >= 2) {
            return 'emerging';
        }

        return 'low';
    }

    /*
    |--------------------------------------------------------------------------
    | Most Common Certifications / Courses
    |--------------------------------------------------------------------------
    */
    public function get_top_certifications($filters = [], $limit = 10)
    {
        $this->db->select('c.certification_name AS label, COUNT(*) AS total', false)
                 ->from($this->certifications_table . ' c')
                 ->join($this->profiles_table . ' p', 'p.user_id = c.user_id')
                 ->join($this->users_table . ' u', 'u.id = c.user_id')
                 ->where('u.role', 'alumnus')
                 ->where('u.is_active', 1)
                 ->group_by('c.certification_name')
                 ->order_by('total', 'DESC')
                 ->limit((int) $limit);

        $this->apply_profile_filters($filters);

        return $this->db->get()->result();
    }

    public function get_top_courses($filters = [], $limit = 10)
    {
        $this->db->select('c.course_name AS label, COUNT(*) AS total', false)
                 ->from($this->courses_table . ' c')
                 ->join($this->profiles_table . ' p', 'p.user_id = c.user_id')
                 ->join($this->users_table . ' u', 'u.id = c.user_id')
                 ->where('u.role', 'alumnus')
                 ->where('u.is_active', 1)
                 ->group_by('c.course_name')
                 ->order_by('total', 'DESC')
                 ->limit((int) $limit);

        $this->apply_profile_filters($filters);

        return $this->db->get()->result();
    }

    /*
    |--------------------------------------------------------------------------
    | Featured Alumni / Bidding Analytics
    |--------------------------------------------------------------------------
    */
    public function get_featured_alumni_count_by_month($filters = [])
    {
        $this->db->select("DATE_FORMAT(b.target_date, '%Y-%m') AS label, COUNT(*) AS total", false)
                 ->from($this->bids_table . ' b')
                 ->join($this->profiles_table . ' p', 'p.user_id = b.user_id')
                 ->join($this->users_table . ' u', 'u.id = b.user_id')
                 ->where('b.status', 'won')
                 ->where('u.role', 'alumnus')
                 ->where('u.is_active', 1)
                 ->group_by("DATE_FORMAT(b.target_date, '%Y-%m')", false)
                 ->order_by('label', 'ASC');

        $this->apply_profile_filters($filters);

        return $this->db->get()->result();
    }

    /*
    |--------------------------------------------------------------------------
    | CSV Export Data
    |--------------------------------------------------------------------------
    */
    public function get_alumni_export_rows($filters = [])
    {
        $rows = $this->get_alumni_list($filters, 10000, 0);

        $export = [];

        foreach ($rows as $row) {
            $export[] = [
                'User ID' => $row->user_id,
                'First Name' => $row->first_name,
                'Last Name' => $row->last_name,
                'Email' => $row->university_email,
                'Programme' => $row->programme,
                'Graduation Year' => $row->graduation_year,
                'Industry Sector' => $row->industry_sector,
                'Current Job Title' => $row->current_job_title,
                'Current Employer' => $row->current_employer,
                'Country' => $row->country,
                'City' => $row->city,
                'Profile Completion' => $row->profile_completion,
                'Featured' => ((int) $row->is_featured === 1) ? 'Yes' : 'No',
                'Featured Date' => $row->featured_for_date,
                'LinkedIn URL' => $row->linkedin_url
            ];
        }

        return $export;
    }

    /*
    |--------------------------------------------------------------------------
    | Full Analytics Payload
    |--------------------------------------------------------------------------
    | Useful for one API endpoint returning everything for Chart.js.
    */
    public function get_full_analytics_payload($filters = [])
    {
        return [
            'summary' => $this->get_summary_stats($filters),
            'filters' => $filters,
            'charts' => [
                'industry_distribution' => $this->get_industry_distribution($filters),
                'graduation_year_distribution' => $this->get_graduation_year_distribution($filters),
                'job_title_distribution' => $this->get_job_title_distribution($filters),
                'top_employers' => $this->get_top_employers($filters),
                'geographic_distribution' => $this->get_geographic_distribution($filters),
                'certification_trends' => $this->get_certification_trends($filters),
                'course_trends' => $this->get_course_trends($filters),
                'skills_gap' => $this->get_skills_gap_data($filters),
                'top_certifications' => $this->get_top_certifications($filters),
                'top_courses' => $this->get_top_courses($filters),
                'featured_alumni_by_month' => $this->get_featured_alumni_count_by_month($filters)
            ]
        ];
    }
}