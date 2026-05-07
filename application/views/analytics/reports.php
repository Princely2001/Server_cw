<?php
    $activeFilters = http_build_query($filters ?? []);

    function report_rows($rows) {
        $output = [];

        if (!empty($rows)) {
            foreach ($rows as $row) {
                if (is_array($row)) {
                    $output[] = [
                        'label' => $row['label'] ?? 'Unknown',
                        'total' => (int) ($row['total'] ?? 0),
                        'severity' => $row['severity'] ?? null
                    ];
                } else {
                    $output[] = [
                        'label' => $row->label ?? 'Unknown',
                        'total' => (int) ($row->total ?? 0),
                        'severity' => $row->severity ?? null
                    ];
                }
            }
        }

        return $output;
    }

    $charts = $payload['charts'] ?? [];

    $industryRows = report_rows($charts['industry_distribution'] ?? []);
    $graduationRows = report_rows($charts['graduation_year_distribution'] ?? []);
    $jobRows = report_rows($charts['job_title_distribution'] ?? []);
    $employerRows = report_rows($charts['top_employers'] ?? []);
    $geoRows = report_rows($charts['geographic_distribution'] ?? []);
    $skillsRows = report_rows($charts['skills_gap'] ?? []);
    $certRows = report_rows($charts['top_certifications'] ?? []);
    $courseRows = report_rows($charts['top_courses'] ?? []);

    $topIndustry = $summary['top_industry']['label'] ?? 'No data';
    $topEmployer = $summary['top_employer']['label'] ?? 'No data';
    $topJobTitle = $summary['top_job_title']['label'] ?? 'No data';
    $topLocation = $summary['top_location']['label'] ?? 'No data';

    function print_report_table($title, $rows, $extraColumn = false) {
        ?>
        <div class="report-section">
            <h3><?= html_escape($title); ?></h3>

            <?php if (!empty($rows)): ?>
                <div class="table-responsive">
                    <table class="report-table">
                        <thead>
                            <tr>
                                <th>Label</th>
                                <th>Total</th>
                                <?php if ($extraColumn): ?>
                                    <th>Insight Level</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rows as $row): ?>
                                <tr>
                                    <td><?= html_escape($row['label']); ?></td>
                                    <td><?= (int) $row['total']; ?></td>
                                    <?php if ($extraColumn): ?>
                                        <td>
                                            <?php if (!empty($row['severity'])): ?>
                                                <span class="severity severity-<?= html_escape($row['severity']); ?>">
                                                    <?= html_escape(ucfirst($row['severity'])); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="muted-na">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="empty-text">No data available for this section.</p>
            <?php endif; ?>
        </div>
        <?php
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= html_escape($title); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            background: #0f172a;
            color: #f8fafc;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            padding: 28px 14px;
        }

        .page-wrapper {
            max-width: 1250px;
            margin: 0 auto;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
            margin-bottom: 24px;
        }

        .page-title {
            font-size: 2rem;
            font-weight: 850;
            margin-bottom: 4px;
        }

        .page-subtitle {
            color: rgba(248,250,252,0.72);
            margin-bottom: 0;
        }

        .nav-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .nav-btn {
            color: #fff;
            text-decoration: none;
            padding: 10px 14px;
            border-radius: 12px;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.14);
            font-weight: 700;
        }

        .nav-btn:hover {
            color: #fff;
            background: rgba(255,255,255,0.16);
        }

        .glass-card {
            background: rgba(255,255,255,0.075);
            border: 1px solid rgba(255,255,255,0.13);
            border-radius: 22px;
            box-shadow: 0 18px 45px rgba(0,0,0,0.25);
            margin-bottom: 22px;
            overflow: hidden;
        }

        .card-inner {
            padding: 24px;
        }

        .section-title {
            font-size: 1.24rem;
            font-weight: 800;
            margin-bottom: 6px;
        }

        .section-desc {
            color: rgba(248,250,252,0.68);
            margin-bottom: 18px;
        }

        .filter-label {
            color: #e2e8f0;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .form-control,
        .form-select {
            background: rgba(15,23,42,0.88);
            color: #fff;
            border: 1px solid rgba(255,255,255,0.18);
            border-radius: 12px;
            padding: 11px 13px;
        }

        .form-control:focus,
        .form-select:focus {
            background: rgba(15,23,42,0.98);
            color: #fff;
            border-color: #60a5fa;
            box-shadow: 0 0 0 0.2rem rgba(96,165,250,0.2);
        }

        .primary-btn {
            border: 0;
            padding: 11px 16px;
            border-radius: 12px;
            font-weight: 800;
            color: #fff;
            background: linear-gradient(135deg, #3b82f6, #6366f1);
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .secondary-btn {
            border: 1px solid rgba(255,255,255,0.14);
            padding: 10px 16px;
            border-radius: 12px;
            font-weight: 800;
            color: #fff;
            background: rgba(255,255,255,0.08);
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .primary-btn:hover,
        .secondary-btn:hover {
            color: #fff;
            transform: translateY(-1px);
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 22px;
        }

        .summary-card {
            background: rgba(255,255,255,0.075);
            border: 1px solid rgba(255,255,255,0.13);
            border-radius: 18px;
            padding: 18px;
        }

        .summary-label {
            color: rgba(248,250,252,0.64);
            font-weight: 750;
            font-size: 0.9rem;
            margin-bottom: 8px;
        }

        .summary-value {
            font-size: 1.65rem;
            font-weight: 900;
            line-height: 1.15;
        }

        .summary-note {
            color: rgba(248,250,252,0.64);
            font-size: 0.88rem;
            margin-top: 7px;
        }

        .report-paper {
            background: #ffffff;
            color: #111827;
            border-radius: 20px;
            padding: 34px;
            margin-bottom: 22px;
        }

        .report-header {
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 18px;
            margin-bottom: 24px;
        }

        .report-title {
            font-size: 2rem;
            font-weight: 900;
            margin-bottom: 8px;
            color: #111827;
        }

        .report-meta {
            color: #4b5563;
            margin-bottom: 4px;
        }

        .report-section {
            margin-bottom: 28px;
            page-break-inside: avoid;
        }

        .report-section h3 {
            font-size: 1.25rem;
            font-weight: 850;
            color: #111827;
            margin-bottom: 12px;
        }

        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            font-size: 0.95rem;
        }

        .report-table th {
            background: #f3f4f6;
            color: #111827;
            padding: 11px 12px;
            border: 1px solid #e5e7eb;
            text-align: left;
            font-weight: 800;
        }

        .report-table td {
            padding: 10px 12px;
            border: 1px solid #e5e7eb;
            color: #1f2937;
        }

        .recommendation-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
            margin-top: 12px;
        }

        .recommendation-card {
            border: 1px solid #e5e7eb;
            background: #f9fafb;
            border-radius: 14px;
            padding: 16px;
        }

        .recommendation-card h4 {
            font-size: 1rem;
            font-weight: 850;
            margin-bottom: 8px;
            color: #111827;
        }

        .recommendation-card p {
            margin-bottom: 0;
            color: #374151;
            line-height: 1.55;
        }

        .severity {
            display: inline-block;
            padding: 5px 9px;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 850;
        }

        .severity-critical {
            background: #fee2e2;
            color: #991b1b;
        }

        .severity-significant {
            background: #ffedd5;
            color: #9a3412;
        }

        .severity-emerging {
            background: #fef3c7;
            color: #92400e;
        }

        .severity-low {
            background: #dcfce7;
            color: #166534;
        }

        .empty-text {
            color: #6b7280;
            border: 1px dashed #d1d5db;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 0;
        }

        .muted-na {
            color: #6b7280;
        }

        .print-only {
            display: none;
        }

        @media (max-width: 1100px) {
            .summary-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .recommendation-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 640px) {
            .page-title {
                font-size: 1.55rem;
            }

            .card-inner {
                padding: 18px;
            }

            .summary-grid {
                grid-template-columns: 1fr;
            }

            .nav-actions,
            .primary-btn,
            .secondary-btn {
                width: 100%;
            }

            .nav-btn {
                width: 100%;
                text-align: center;
            }

            .report-paper {
                padding: 20px;
            }
        }

        @media print {
            body {
                background: #fff;
                color: #000;
                padding: 0;
            }

            .topbar,
            .glass-card,
            .no-print {
                display: none !important;
            }

            .page-wrapper {
                max-width: 100%;
                margin: 0;
            }

            .report-paper {
                box-shadow: none;
                border-radius: 0;
                padding: 0;
                margin: 0;
            }

            .print-only {
                display: block;
            }

            .report-section {
                page-break-inside: avoid;
            }

            a {
                color: #000;
                text-decoration: none;
            }
        }
    </style>
</head>

<body>

<div class="page-wrapper">

    <div class="topbar">
        <div>
            <div class="page-title"><?= html_escape($title); ?></div>
            <p class="page-subtitle">
                Generate curriculum intelligence reports from alumni outcomes and professional development data.
            </p>
        </div>

        <div class="nav-actions">
            <a href="<?= site_url('analytics/dashboard'); ?>" class="nav-btn">Dashboard</a>
            <a href="<?= site_url('analytics/alumni'); ?>" class="nav-btn">View Alumni</a>
            <a href="<?= site_url('analytics/reports'); ?>" class="nav-btn">Reports</a>
            <a href="<?= site_url('developer/index'); ?>" class="nav-btn">API Keys</a>
            <a href="<?= site_url('auth/dashboard'); ?>" class="nav-btn">Main Dashboard</a>
        </div>
    </div>

    <div class="glass-card no-print">
        <div class="card-inner">
            <div class="section-title">Report Filters</div>
            <p class="section-desc">
                Select filters before generating or exporting the report.
            </p>

            <?= form_open('analytics/reports', ['method' => 'get']); ?>
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="filter-label" for="programme">Programme</label>
                        <select name="programme" id="programme" class="form-select">
                            <option value="">All Programmes</option>
                            <?php if (!empty($filter_options['programmes'])): ?>
                                <?php foreach ($filter_options['programmes'] as $item): ?>
                                    <?php $value = $item->programme ?? ''; ?>
                                    <?php if ($value !== ''): ?>
                                        <option value="<?= html_escape($value); ?>" <?= (($filters['programme'] ?? '') === $value) ? 'selected' : ''; ?>>
                                            <?= html_escape($value); ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="filter-label" for="graduation_year">Graduation Year</label>
                        <select name="graduation_year" id="graduation_year" class="form-select">
                            <option value="">All Years</option>
                            <?php if (!empty($filter_options['graduation_years'])): ?>
                                <?php foreach ($filter_options['graduation_years'] as $item): ?>
                                    <?php $value = $item->graduation_year ?? ''; ?>
                                    <?php if ($value !== ''): ?>
                                        <option value="<?= html_escape($value); ?>" <?= ((string)($filters['graduation_year'] ?? '') === (string)$value) ? 'selected' : ''; ?>>
                                            <?= html_escape($value); ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="filter-label" for="industry_sector">Industry Sector</label>
                        <select name="industry_sector" id="industry_sector" class="form-select">
                            <option value="">All Industries</option>
                            <?php if (!empty($filter_options['industry_sectors'])): ?>
                                <?php foreach ($filter_options['industry_sectors'] as $item): ?>
                                    <?php $value = $item->industry_sector ?? ''; ?>
                                    <?php if ($value !== ''): ?>
                                        <option value="<?= html_escape($value); ?>" <?= (($filters['industry_sector'] ?? '') === $value) ? 'selected' : ''; ?>>
                                            <?= html_escape($value); ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="col-md-3 d-grid gap-2">
                        <button type="submit" class="primary-btn">Generate Report</button>
                        <a href="<?= site_url('analytics/reports'); ?>" class="secondary-btn">Reset</a>
                    </div>
                </div>
            <?= form_close(); ?>
        </div>
    </div>

    <div class="summary-grid no-print">
        <div class="summary-card">
            <div class="summary-label">Total Alumni</div>
            <div class="summary-value"><?= (int) ($summary['total_alumni'] ?? 0); ?></div>
            <div class="summary-note">Matching selected filters</div>
        </div>

        <div class="summary-card">
            <div class="summary-label">Top Industry</div>
            <div class="summary-value" style="font-size: 1.25rem;"><?= html_escape($topIndustry); ?></div>
            <div class="summary-note"><?= (int) ($summary['top_industry']['total'] ?? 0); ?> alumni</div>
        </div>

        <div class="summary-card">
            <div class="summary-label">Top Employer</div>
            <div class="summary-value" style="font-size: 1.25rem;"><?= html_escape($topEmployer); ?></div>
            <div class="summary-note"><?= (int) ($summary['top_employer']['total'] ?? 0); ?> alumni</div>
        </div>

        <div class="summary-card">
            <div class="summary-label">Certifications</div>
            <div class="summary-value"><?= (int) ($summary['total_certifications'] ?? 0); ?></div>
            <div class="summary-note">Professional certifications</div>
        </div>
    </div>

    <div class="glass-card no-print">
        <div class="card-inner">
            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                <div>
                    <div class="section-title">Export Options</div>
                    <p class="section-desc mb-0">
                        Download data or print this page as a PDF report.
                    </p>
                </div>

                <div class="nav-actions">
                    <a href="<?= site_url('analytics/export-csv') . (!empty($activeFilters) ? '?' . $activeFilters : ''); ?>" class="primary-btn">
                        Export Alumni CSV
                    </a>

                    <a href="<?= site_url('analytics/export-summary-csv') . (!empty($activeFilters) ? '?' . $activeFilters : ''); ?>" class="secondary-btn">
                        Export Summary CSV
                    </a>

                    <button type="button" onclick="window.print()" class="secondary-btn">
                        Print / Save PDF
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="report-paper">

        <div class="report-header">
            <div class="report-title">University Analytics & Intelligence Report</div>

            <div class="report-meta">
                <strong>Generated At:</strong> <?= html_escape($generated_at ?? date('Y-m-d H:i:s')); ?>
                <span class="print-only"> | </span>
                <strong>Timezone:</strong> <?= html_escape($app_timezone ?? 'Asia/Colombo'); ?>
            </div>

            <div class="report-meta">
                <strong>Filters:</strong>
                Programme:
                <?= !empty($filters['programme']) ? html_escape($filters['programme']) : 'All'; ?>,
                Graduation Year:
                <?= !empty($filters['graduation_year']) ? html_escape($filters['graduation_year']) : 'All'; ?>,
                Industry:
                <?= !empty($filters['industry_sector']) ? html_escape($filters['industry_sector']) : 'All'; ?>
            </div>
        </div>

        <div class="report-section">
            <h3>Executive Summary</h3>

            <div class="table-responsive">
                <table class="report-table">
                    <tbody>
                        <tr>
                            <th>Total Alumni Analysed</th>
                            <td><?= (int) ($summary['total_alumni'] ?? 0); ?></td>
                        </tr>
                        <tr>
                            <th>Total Programmes Represented</th>
                            <td><?= (int) ($summary['total_programmes'] ?? 0); ?></td>
                        </tr>
                        <tr>
                            <th>Top Industry Sector</th>
                            <td><?= html_escape($topIndustry); ?></td>
                        </tr>
                        <tr>
                            <th>Top Employer</th>
                            <td><?= html_escape($topEmployer); ?></td>
                        </tr>
                        <tr>
                            <th>Most Common Job Title</th>
                            <td><?= html_escape($topJobTitle); ?></td>
                        </tr>
                        <tr>
                            <th>Top Graduate Location</th>
                            <td><?= html_escape($topLocation); ?></td>
                        </tr>
                        <tr>
                            <th>Total Certifications</th>
                            <td><?= (int) ($summary['total_certifications'] ?? 0); ?></td>
                        </tr>
                        <tr>
                            <th>Total Professional Courses</th>
                            <td><?= (int) ($summary['total_courses'] ?? 0); ?></td>
                        </tr>
                        <tr>
                            <th>Total Licences</th>
                            <td><?= (int) ($summary['total_licences'] ?? 0); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <?php print_report_table('Industry Distribution', $industryRows); ?>

        <?php print_report_table('Graduation Year Distribution', $graduationRows); ?>

        <?php print_report_table('Most Common Job Titles', $jobRows); ?>

        <?php print_report_table('Top Employers', $employerRows); ?>

        <?php print_report_table('Geographic Distribution', $geoRows); ?>

        <?php print_report_table('Top Certifications', $certRows); ?>

        <?php print_report_table('Top Professional Courses', $courseRows); ?>

        <?php print_report_table('Skills Gap Signals', $skillsRows, true); ?>

        <div class="report-section">
            <h3>Recommended Actions</h3>

            <div class="recommendation-grid">
                <div class="recommendation-card">
                    <h4>Curriculum Review</h4>
                    <p>
                        Compare high-frequency certifications and courses against current module content.
                        If alumni repeatedly complete cloud, data, cybersecurity, or agile training after graduation,
                        these topics should be considered for earlier curriculum integration.
                    </p>
                </div>

                <div class="recommendation-card">
                    <h4>Industry Partnership</h4>
                    <p>
                        Use the top employer and industry data to identify companies for guest lectures,
                        placement opportunities, live briefs, and advisory board participation.
                    </p>
                </div>

                <div class="recommendation-card">
                    <h4>Career Pathway Support</h4>
                    <p>
                        Use job title and location trends to support students with clearer career pathways,
                        targeted workshops, and employability guidance linked to actual alumni outcomes.
                    </p>
                </div>
            </div>
        </div>

        <div class="report-section">
            <h3>Methodology</h3>
            <p style="color: #374151; line-height: 1.65;">
                This report is generated from alumni profile, degree, certification, licence, course,
                employment, and bidding data stored in the application database. Filters are applied by
                programme, graduation year, and industry sector. The report is intended to support
                curriculum development, strategic planning, and alumni outcome analysis.
            </p>
        </div>

    </div>

</div>

</body>
</html>