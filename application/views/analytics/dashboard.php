<?php
    $activeFilters = http_build_query($filters ?? []);

    function chart_labels($rows) {
        $labels = [];

        if (!empty($rows)) {
            foreach ($rows as $row) {
                if (is_array($row)) {
                    $labels[] = $row['label'] ?? 'Unknown';
                } else {
                    $labels[] = $row->label ?? 'Unknown';
                }
            }
        }

        return $labels;
    }

    function chart_totals($rows) {
        $totals = [];

        if (!empty($rows)) {
            foreach ($rows as $row) {
                if (is_array($row)) {
                    $totals[] = (int) ($row['total'] ?? 0);
                } else {
                    $totals[] = (int) ($row->total ?? 0);
                }
            }
        }

        return $totals;
    }

    $charts = $payload['charts'] ?? [];

    $industryLabels = chart_labels($charts['industry_distribution'] ?? []);
    $industryTotals = chart_totals($charts['industry_distribution'] ?? []);

    $graduationLabels = chart_labels($charts['graduation_year_distribution'] ?? []);
    $graduationTotals = chart_totals($charts['graduation_year_distribution'] ?? []);

    $jobLabels = chart_labels($charts['job_title_distribution'] ?? []);
    $jobTotals = chart_totals($charts['job_title_distribution'] ?? []);

    $employerLabels = chart_labels($charts['top_employers'] ?? []);
    $employerTotals = chart_totals($charts['top_employers'] ?? []);

    $geoLabels = chart_labels($charts['geographic_distribution'] ?? []);
    $geoTotals = chart_totals($charts['geographic_distribution'] ?? []);

    $certTrendLabels = chart_labels($charts['certification_trends'] ?? []);
    $certTrendTotals = chart_totals($charts['certification_trends'] ?? []);

    $courseTrendLabels = chart_labels($charts['course_trends'] ?? []);
    $courseTrendTotals = chart_totals($charts['course_trends'] ?? []);

    $skillsLabels = chart_labels($charts['skills_gap'] ?? []);
    $skillsTotals = chart_totals($charts['skills_gap'] ?? []);

    $topIndustry = $summary['top_industry']['label'] ?? 'No data';
    $topEmployer = $summary['top_employer']['label'] ?? 'No data';
    $topJobTitle = $summary['top_job_title']['label'] ?? 'No data';
    $topLocation = $summary['top_location']['label'] ?? 'No data';
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

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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

        .dashboard-wrapper {
            max-width: 1380px;
            margin: 0 auto;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 24px;
        }

        .page-title {
            font-size: 2rem;
            font-weight: 800;
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
            background: rgba(255,255,255,0.078);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 20px;
            padding: 20px;
            min-height: 130px;
        }

        .summary-label {
            color: rgba(248,250,252,0.66);
            font-size: 0.9rem;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .summary-value {
            font-size: 2rem;
            font-weight: 900;
            line-height: 1.1;
        }

        .summary-note {
            color: rgba(248,250,252,0.66);
            font-size: 0.88rem;
            margin-top: 8px;
            word-break: break-word;
        }

        .chart-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 22px;
        }

        .chart-card {
            background: rgba(255,255,255,0.078);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 22px;
            padding: 20px;
            min-height: 430px;
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: flex-start;
            margin-bottom: 14px;
        }

        .chart-title {
            font-weight: 850;
            font-size: 1.05rem;
            margin-bottom: 3px;
        }

        .chart-subtitle {
            color: rgba(248,250,252,0.62);
            font-size: 0.88rem;
        }

        .chart-download {
            border: 0;
            border-radius: 10px;
            padding: 7px 10px;
            background: rgba(255,255,255,0.1);
            color: #fff;
            font-size: 0.84rem;
            font-weight: 700;
        }

        .chart-box {
            height: 330px;
            position: relative;
        }

        .insight-list {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
        }

        .insight-box {
            border-radius: 16px;
            background: rgba(15,23,42,0.72);
            border: 1px solid rgba(255,255,255,0.12);
            padding: 16px;
        }

        .insight-title {
            font-weight: 850;
            margin-bottom: 8px;
        }

        .insight-text {
            color: rgba(248,250,252,0.72);
            margin: 0;
        }

        .empty-chart {
            color: rgba(248,250,252,0.62);
            display: flex;
            align-items: center;
            justify-content: center;
            height: 280px;
            text-align: center;
            border: 1px dashed rgba(255,255,255,0.18);
            border-radius: 16px;
        }

        .badge-soft {
            display: inline-block;
            border-radius: 999px;
            padding: 5px 9px;
            background: rgba(59,130,246,0.18);
            border: 1px solid rgba(96,165,250,0.28);
            color: #dbeafe;
            font-size: 0.8rem;
            font-weight: 750;
        }

        @media (max-width: 1100px) {
            .summary-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .chart-grid {
                grid-template-columns: 1fr;
            }

            .insight-list {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 640px) {
            .summary-grid {
                grid-template-columns: 1fr;
            }

            .page-title {
                font-size: 1.55rem;
            }

            .card-inner {
                padding: 18px;
            }

            .chart-card {
                min-height: 390px;
            }

            .chart-box {
                height: 280px;
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
        }
    </style>
</head>
<body>

<div class="dashboard-wrapper">

    <div class="topbar">
        <div>
            <div class="page-title"><?= html_escape($title); ?></div>
            <p class="page-subtitle">
                Real-time alumni intelligence for curriculum planning, skills gap detection, and career outcome analysis.
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

    <div class="glass-card">
        <div class="card-inner">
            <div class="section-title">Analytics Filters</div>
            <p class="section-desc">
                Filter alumni by programme, graduation year, and industry sector. These filters update all cards, charts, and exports.
            </p>

            <?= form_open('analytics/dashboard', ['method' => 'get']); ?>
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
                        <button type="submit" class="primary-btn">Apply Filters</button>
                        <a href="<?= site_url('analytics/dashboard'); ?>" class="secondary-btn">Reset</a>
                    </div>
                </div>
            <?= form_close(); ?>
        </div>
    </div>

    <div class="summary-grid">
        <div class="summary-card">
            <div class="summary-label">Total Alumni</div>
            <div class="summary-value"><?= (int) ($summary['total_alumni'] ?? 0); ?></div>
            <div class="summary-note">Active alumni profiles in selected filters</div>
        </div>

        <div class="summary-card">
            <div class="summary-label">Total Programmes</div>
            <div class="summary-value"><?= (int) ($summary['total_programmes'] ?? 0); ?></div>
            <div class="summary-note">Distinct programmes represented</div>
        </div>

        <div class="summary-card">
            <div class="summary-label">Top Industry</div>
            <div class="summary-value" style="font-size: 1.35rem;"><?= html_escape($topIndustry); ?></div>
            <div class="summary-note"><?= (int) ($summary['top_industry']['total'] ?? 0); ?> alumni</div>
        </div>

        <div class="summary-card">
            <div class="summary-label">Top Employer</div>
            <div class="summary-value" style="font-size: 1.35rem;"><?= html_escape($topEmployer); ?></div>
            <div class="summary-note"><?= (int) ($summary['top_employer']['total'] ?? 0); ?> alumni</div>
        </div>

        <div class="summary-card">
            <div class="summary-label">Common Job Title</div>
            <div class="summary-value" style="font-size: 1.3rem;"><?= html_escape($topJobTitle); ?></div>
            <div class="summary-note">Most common current role</div>
        </div>

        <div class="summary-card">
            <div class="summary-label">Top Location</div>
            <div class="summary-value" style="font-size: 1.3rem;"><?= html_escape($topLocation); ?></div>
            <div class="summary-note">Most common graduate location</div>
        </div>

        <div class="summary-card">
            <div class="summary-label">Certifications</div>
            <div class="summary-value"><?= (int) ($summary['total_certifications'] ?? 0); ?></div>
            <div class="summary-note">Professional certifications completed</div>
        </div>

        <div class="summary-card">
            <div class="summary-label">Courses</div>
            <div class="summary-value"><?= (int) ($summary['total_courses'] ?? 0); ?></div>
            <div class="summary-note">Post-graduation courses completed</div>
        </div>
    </div>

    <div class="glass-card">
        <div class="card-inner">
            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                <div>
                    <div class="section-title">Exports & Reports</div>
                    <p class="section-desc mb-0">
                        Export filtered alumni or analytics data for evidence in curriculum planning reports.
                    </p>
                </div>

                <div class="nav-actions">
                    <a href="<?= site_url('analytics/export-csv') . (!empty($activeFilters) ? '?' . $activeFilters : ''); ?>" class="primary-btn">
                        Export Alumni CSV
                    </a>
                    <a href="<?= site_url('analytics/export-summary-csv') . (!empty($activeFilters) ? '?' . $activeFilters : ''); ?>" class="secondary-btn">
                        Export Summary CSV
                    </a>
                    <a href="<?= site_url('analytics/reports') . (!empty($activeFilters) ? '?' . $activeFilters : ''); ?>" class="secondary-btn">
                        Generate Report
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="chart-grid">

        <div class="chart-card">
            <div class="chart-header">
                <div>
                    <div class="chart-title">Alumni by Industry Sector</div>
                    <div class="chart-subtitle">Doughnut chart showing graduate destination sectors</div>
                </div>
                <button class="chart-download" onclick="downloadChart('industryChart', 'industry_distribution')">Download</button>
            </div>
            <div class="chart-box">
                <?php if (!empty($industryLabels)): ?>
                    <canvas id="industryChart"></canvas>
                <?php else: ?>
                    <div class="empty-chart">No industry data available. Update alumni profile industry sectors.</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="chart-card">
            <div class="chart-header">
                <div>
                    <div class="chart-title">Alumni by Graduation Year</div>
                    <div class="chart-subtitle">Bar chart showing alumni distribution by year</div>
                </div>
                <button class="chart-download" onclick="downloadChart('graduationChart', 'graduation_years')">Download</button>
            </div>
            <div class="chart-box">
                <?php if (!empty($graduationLabels)): ?>
                    <canvas id="graduationChart"></canvas>
                <?php else: ?>
                    <div class="empty-chart">No graduation year data available.</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="chart-card">
            <div class="chart-header">
                <div>
                    <div class="chart-title">Most Common Job Titles</div>
                    <div class="chart-subtitle">Horizontal bar chart showing career outcomes</div>
                </div>
                <button class="chart-download" onclick="downloadChart('jobTitleChart', 'job_titles')">Download</button>
            </div>
            <div class="chart-box">
                <?php if (!empty($jobLabels)): ?>
                    <canvas id="jobTitleChart"></canvas>
                <?php else: ?>
                    <div class="empty-chart">No job title data available.</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="chart-card">
            <div class="chart-header">
                <div>
                    <div class="chart-title">Top Employers</div>
                    <div class="chart-subtitle">Bar chart showing employers hiring alumni</div>
                </div>
                <button class="chart-download" onclick="downloadChart('employerChart', 'top_employers')">Download</button>
            </div>
            <div class="chart-box">
                <?php if (!empty($employerLabels)): ?>
                    <canvas id="employerChart"></canvas>
                <?php else: ?>
                    <div class="empty-chart">No employer data available.</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="chart-card">
            <div class="chart-header">
                <div>
                    <div class="chart-title">Geographic Distribution</div>
                    <div class="chart-subtitle">Pie chart showing current alumni locations</div>
                </div>
                <button class="chart-download" onclick="downloadChart('geoChart', 'geographic_distribution')">Download</button>
            </div>
            <div class="chart-box">
                <?php if (!empty($geoLabels)): ?>
                    <canvas id="geoChart"></canvas>
                <?php else: ?>
                    <div class="empty-chart">No location data available.</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="chart-card">
            <div class="chart-header">
                <div>
                    <div class="chart-title">Certification Trends</div>
                    <div class="chart-subtitle">Line chart showing professional certification growth</div>
                </div>
                <button class="chart-download" onclick="downloadChart('certTrendChart', 'certification_trends')">Download</button>
            </div>
            <div class="chart-box">
                <?php if (!empty($certTrendLabels)): ?>
                    <canvas id="certTrendChart"></canvas>
                <?php else: ?>
                    <div class="empty-chart">No certification trend data available.</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="chart-card">
            <div class="chart-header">
                <div>
                    <div class="chart-title">Professional Course Trends</div>
                    <div class="chart-subtitle">Line chart showing post-graduation learning activity</div>
                </div>
                <button class="chart-download" onclick="downloadChart('courseTrendChart', 'course_trends')">Download</button>
            </div>
            <div class="chart-box">
                <?php if (!empty($courseTrendLabels)): ?>
                    <canvas id="courseTrendChart"></canvas>
                <?php else: ?>
                    <div class="empty-chart">No course trend data available.</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="chart-card">
            <div class="chart-header">
                <div>
                    <div class="chart-title">Skills Gap Radar</div>
                    <div class="chart-subtitle">Radar chart grouping certifications and courses into curriculum signals</div>
                </div>
                <button class="chart-download" onclick="downloadChart('skillsRadarChart', 'skills_gap')">Download</button>
            </div>
            <div class="chart-box">
                <?php if (!empty($skillsLabels)): ?>
                    <canvas id="skillsRadarChart"></canvas>
                <?php else: ?>
                    <div class="empty-chart">No skills gap data available.</div>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <div class="glass-card mt-4">
        <div class="card-inner">
            <div class="section-title">Key Intelligence Insights</div>
            <p class="section-desc">
                Use these insights in your viva to explain how the dashboard supports curriculum improvement.
            </p>

            <div class="insight-list">
                <div class="insight-box">
                    <div class="insight-title">Skills Gap Detection</div>
                    <p class="insight-text">
                        High counts in cloud, cyber, data analytics, or agile certifications suggest areas where graduates are learning skills after graduation.
                    </p>
                </div>

                <div class="insight-box">
                    <div class="insight-title">Career Pathway Analysis</div>
                    <p class="insight-text">
                        Job-title and industry charts show whether graduates are entering new roles not directly reflected in the curriculum.
                    </p>
                </div>

                <div class="insight-box">
                    <div class="insight-title">Strategic Planning</div>
                    <p class="insight-text">
                        Employer, location, and certification trends can guide programme updates, partnerships, and alumni engagement strategy.
                    </p>
                </div>
            </div>

            <div class="mt-4">
                <span class="badge-soft">Generated: <?= html_escape($generated_at ?? date('Y-m-d H:i:s')); ?></span>
                <span class="badge-soft">Timezone: <?= html_escape($app_timezone ?? 'Asia/Colombo'); ?></span>
            </div>
        </div>
    </div>

</div>

<script>
    const chartDefaults = {
        responsive: true,
        maintainAspectRatio: false,
        animation: {
            duration: 900,
            easing: 'easeOutQuart'
        },
        plugins: {
            legend: {
                labels: {
                    color: '#f8fafc'
                }
            },
            tooltip: {
                enabled: true
            }
        },
        scales: {
            x: {
                ticks: { color: '#cbd5e1' },
                grid: { color: 'rgba(255,255,255,0.08)' }
            },
            y: {
                ticks: { color: '#cbd5e1' },
                grid: { color: 'rgba(255,255,255,0.08)' },
                beginAtZero: true
            }
        }
    };

    const palette = [
        '#60a5fa', '#34d399', '#f59e0b', '#f472b6',
        '#a78bfa', '#22d3ee', '#fb7185', '#c084fc',
        '#facc15', '#4ade80', '#38bdf8', '#e879f9'
    ];

    function createChart(canvasId, type, labels, data, label, extraOptions = {}) {
        const el = document.getElementById(canvasId);

        if (!el) {
            return;
        }

        const config = {
            type: type,
            data: {
                labels: labels,
                datasets: [{
                    label: label,
                    data: data,
                    backgroundColor: type === 'line' ? 'rgba(96,165,250,0.18)' : palette,
                    borderColor: type === 'line' ? '#60a5fa' : palette,
                    borderWidth: 2,
                    tension: 0.35,
                    fill: type === 'line'
                }]
            },
            options: {
                ...chartDefaults,
                ...extraOptions
            }
        };

        if (['doughnut', 'pie', 'radar'].includes(type)) {
            delete config.options.scales;
        }

        if (type === 'radar') {
            config.options.scales = {
                r: {
                    angleLines: { color: 'rgba(255,255,255,0.12)' },
                    grid: { color: 'rgba(255,255,255,0.12)' },
                    pointLabels: { color: '#f8fafc' },
                    ticks: {
                        color: '#cbd5e1',
                        backdropColor: 'transparent',
                        beginAtZero: true
                    }
                }
            };

            config.data.datasets[0].backgroundColor = 'rgba(96,165,250,0.20)';
            config.data.datasets[0].borderColor = '#60a5fa';
            config.data.datasets[0].pointBackgroundColor = '#60a5fa';
        }

        new Chart(el, config);
    }

    function downloadChart(canvasId, filename) {
        const canvas = document.getElementById(canvasId);

        if (!canvas) {
            alert('Chart is not available to download.');
            return;
        }

        const link = document.createElement('a');
        link.download = filename + '.png';
        link.href = canvas.toDataURL('image/png');
        link.click();
    }

    const industryLabels = <?= json_encode($industryLabels, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
    const industryTotals = <?= json_encode($industryTotals, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;

    const graduationLabels = <?= json_encode($graduationLabels, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
    const graduationTotals = <?= json_encode($graduationTotals, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;

    const jobLabels = <?= json_encode($jobLabels, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
    const jobTotals = <?= json_encode($jobTotals, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;

    const employerLabels = <?= json_encode($employerLabels, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
    const employerTotals = <?= json_encode($employerTotals, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;

    const geoLabels = <?= json_encode($geoLabels, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
    const geoTotals = <?= json_encode($geoTotals, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;

    const certTrendLabels = <?= json_encode($certTrendLabels, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
    const certTrendTotals = <?= json_encode($certTrendTotals, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;

    const courseTrendLabels = <?= json_encode($courseTrendLabels, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
    const courseTrendTotals = <?= json_encode($courseTrendTotals, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;

    const skillsLabels = <?= json_encode($skillsLabels, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
    const skillsTotals = <?= json_encode($skillsTotals, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;

    createChart('industryChart', 'doughnut', industryLabels, industryTotals, 'Alumni');
    createChart('graduationChart', 'bar', graduationLabels, graduationTotals, 'Alumni');
    createChart('jobTitleChart', 'bar', jobLabels, jobTotals, 'Alumni', {
        indexAxis: 'y'
    });
    createChart('employerChart', 'bar', employerLabels, employerTotals, 'Alumni');
    createChart('geoChart', 'pie', geoLabels, geoTotals, 'Alumni');
    createChart('certTrendChart', 'line', certTrendLabels, certTrendTotals, 'Certifications');
    createChart('courseTrendChart', 'line', courseTrendLabels, courseTrendTotals, 'Courses');
    createChart('skillsRadarChart', 'radar', skillsLabels, skillsTotals, 'Skill Signals');
</script>

</body>
</html>