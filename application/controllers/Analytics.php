<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Analytics extends CI_Controller
{
    private $app_timezone = 'Asia/Colombo';

    public function __construct()
    {
        parent::__construct();

        date_default_timezone_set($this->app_timezone);

        /*
        |--------------------------------------------------------------------------
        | Security Headers
        |--------------------------------------------------------------------------
        */
        $this->output->set_header('X-Content-Type-Options: nosniff');
        $this->output->set_header('X-Frame-Options: SAMEORIGIN');
        $this->output->set_header('Referrer-Policy: strict-origin-when-cross-origin');
        $this->output->set_header('X-XSS-Protection: 1; mode=block');
        $this->output->set_header(
            "Content-Security-Policy: default-src 'self' https://cdn.jsdelivr.net; img-src 'self' data: blob:; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net;"
        );

        $this->load->model('Analytics_model');
        $this->load->helper(['url', 'form', 'security', 'download']);
        $this->load->library(['session', 'form_validation']);
    }

    /*
    |--------------------------------------------------------------------------
    | Access Guard
    |--------------------------------------------------------------------------
    | Analytics dashboard is for developers/admin users only.
    */
    private function require_developer()
    {
        if (!$this->session->userdata('logged_in')) {
            redirect('auth/login');
            exit;
        }

        $role = $this->session->userdata('role');

        if (!in_array($role, ['developer', 'admin'], true)) {
            show_error('Forbidden: Analytics dashboard is restricted to Developers/Admins only.', 403);
            exit;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Filter Helper
    |--------------------------------------------------------------------------
    */
    private function get_filters()
    {
        $filters = [
            'programme'       => $this->input->get('programme', TRUE),
            'graduation_year' => $this->input->get('graduation_year', TRUE),
            'industry_sector' => $this->input->get('industry_sector', TRUE)
        ];

        $clean = [];

        foreach ($filters as $key => $value) {
            if ($value !== null && trim((string) $value) !== '') {
                $clean[$key] = trim((string) $value);
            }
        }

        return $clean;
    }

    /*
    |--------------------------------------------------------------------------
    | Main Analytics Dashboard
    |--------------------------------------------------------------------------
    */
    public function dashboard()
    {
        $this->require_developer();

        $filters = $this->get_filters();

        $data = [
            'title'          => 'University Analytics Dashboard',
            'filters'        => $filters,
            'filter_options' => $this->Analytics_model->get_filter_options(),
            'summary'        => $this->Analytics_model->get_summary_stats($filters),
            'payload'        => $this->Analytics_model->get_full_analytics_payload($filters),
            'app_timezone'   => $this->app_timezone,
            'generated_at'   => date('Y-m-d H:i:s')
        ];

        $this->load->view('analytics/dashboard', $data);
    }

    /*
    |--------------------------------------------------------------------------
    | Alumni List Page
    |--------------------------------------------------------------------------
    */
    public function alumni()
    {
        $this->require_developer();

        $filters = $this->get_filters();

        $page = (int) $this->input->get('page', TRUE);
        if ($page <= 0) {
            $page = 1;
        }

        $limit = 25;
        $offset = ($page - 1) * $limit;

        $total = $this->Analytics_model->count_alumni_list($filters);
        $alumni = $this->Analytics_model->get_alumni_list($filters, $limit, $offset);

        $data = [
            'title'          => 'View Alumni',
            'filters'        => $filters,
            'filter_options' => $this->Analytics_model->get_filter_options(),
            'alumni'         => $alumni,
            'total'          => $total,
            'page'           => $page,
            'limit'          => $limit,
            'total_pages'    => ceil($total / $limit),
            'app_timezone'   => $this->app_timezone
        ];

        $this->load->view('analytics/alumni', $data);
    }

    /*
    |--------------------------------------------------------------------------
    | Reports Page
    |--------------------------------------------------------------------------
    */
    public function reports()
    {
        $this->require_developer();

        $filters = $this->get_filters();

        $data = [
            'title'          => 'Analytics Reports',
            'filters'        => $filters,
            'filter_options' => $this->Analytics_model->get_filter_options(),
            'summary'        => $this->Analytics_model->get_summary_stats($filters),
            'payload'        => $this->Analytics_model->get_full_analytics_payload($filters),
            'app_timezone'   => $this->app_timezone,
            'generated_at'   => date('Y-m-d H:i:s')
        ];

        $this->load->view('analytics/reports', $data);
    }

    /*
    |--------------------------------------------------------------------------
    | Export Filtered Alumni to CSV
    |--------------------------------------------------------------------------
    */
    public function export_csv()
    {
        $this->require_developer();

        $filters = $this->get_filters();
        $rows = $this->Analytics_model->get_alumni_export_rows($filters);

        $filename = 'alumni_analytics_export_' . date('Ymd_His') . '.csv';

        $csv = fopen('php://temp', 'r+');

        if (!empty($rows)) {
            fputcsv($csv, array_keys($rows[0]));

            foreach ($rows as $row) {
                fputcsv($csv, $row);
            }
        } else {
            fputcsv($csv, [
                'Message'
            ]);

            fputcsv($csv, [
                'No alumni records found for selected filters.'
            ]);
        }

        rewind($csv);
        $csv_content = stream_get_contents($csv);
        fclose($csv);

        force_download($filename, $csv_content);
    }

    /*
    |--------------------------------------------------------------------------
    | Export Analytics Summary to CSV
    |--------------------------------------------------------------------------
    */
    public function export_summary_csv()
    {
        $this->require_developer();

        $filters = $this->get_filters();
        $payload = $this->Analytics_model->get_full_analytics_payload($filters);

        $filename = 'analytics_summary_' . date('Ymd_His') . '.csv';

        $csv = fopen('php://temp', 'r+');

        fputcsv($csv, ['Section', 'Label', 'Total', 'Extra']);

        if (!empty($payload['summary'])) {
            foreach ($payload['summary'] as $key => $value) {
                if (is_array($value)) {
                    fputcsv($csv, [
                        'summary',
                        $key,
                        isset($value['total']) ? $value['total'] : '',
                        isset($value['label']) ? $value['label'] : ''
                    ]);
                } else {
                    fputcsv($csv, [
                        'summary',
                        $key,
                        $value,
                        ''
                    ]);
                }
            }
        }

        if (!empty($payload['charts'])) {
            foreach ($payload['charts'] as $chart_name => $chart_rows) {
                if (!empty($chart_rows)) {
                    foreach ($chart_rows as $row) {
                        $label = is_array($row) ? ($row['label'] ?? '') : ($row->label ?? '');
                        $total = is_array($row) ? ($row['total'] ?? '') : ($row->total ?? '');
                        $extra = is_array($row) ? ($row['severity'] ?? '') : ($row->severity ?? '');

                        fputcsv($csv, [
                            $chart_name,
                            $label,
                            $total,
                            $extra
                        ]);
                    }
                }
            }
        }

        rewind($csv);
        $csv_content = stream_get_contents($csv);
        fclose($csv);

        force_download($filename, $csv_content);
    }

    /*
    |--------------------------------------------------------------------------
    | JSON endpoint for dashboard pages without using API token
    |--------------------------------------------------------------------------
    | This is session-protected for the internal web dashboard.
    */
    public function chart_data()
    {
        $this->require_developer();

        $filters = $this->get_filters();
        $payload = $this->Analytics_model->get_full_analytics_payload($filters);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status'  => 'success',
                'filters' => $filters,
                'data'    => $payload
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }
}