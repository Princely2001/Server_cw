<?php
    $activeFilters = http_build_query($filters ?? []);

    $currentPage = isset($page) ? (int) $page : 1;
    $totalPages = isset($total_pages) ? (int) $total_pages : 1;

    if ($currentPage < 1) {
        $currentPage = 1;
    }

    if ($totalPages < 1) {
        $totalPages = 1;
    }

    function build_alumni_page_url($pageNumber, $filters) {
        $query = $filters ?? [];
        $query['page'] = $pageNumber;
        return site_url('analytics/alumni') . '?' . http_build_query($query);
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
            max-width: 1380px;
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

        .stats-row {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 22px;
        }

        .stat-card {
            background: rgba(255,255,255,0.075);
            border: 1px solid rgba(255,255,255,0.13);
            border-radius: 18px;
            padding: 18px;
        }

        .stat-label {
            color: rgba(248,250,252,0.64);
            font-weight: 750;
            font-size: 0.9rem;
            margin-bottom: 8px;
        }

        .stat-value {
            font-size: 1.7rem;
            font-weight: 900;
        }

        .table-responsive {
            border-radius: 18px;
            overflow-x: auto;
        }

        .modern-table {
            width: 100%;
            margin: 0;
            color: #fff;
            border-collapse: collapse;
            min-width: 1150px;
        }

        .modern-table thead {
            background: rgba(255,255,255,0.1);
        }

        .modern-table th {
            padding: 15px;
            font-size: 0.82rem;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            font-weight: 800;
            border: none;
            color: #e2e8f0;
            white-space: nowrap;
        }

        .modern-table td {
            padding: 15px;
            border: none;
            border-top: 1px solid rgba(255,255,255,0.08);
            vertical-align: middle;
        }

        .modern-table tbody tr:hover {
            background: rgba(255,255,255,0.06);
        }

        .name-cell {
            font-weight: 850;
            color: #fff;
        }

        .email-cell {
            font-size: 0.86rem;
            color: rgba(248,250,252,0.68);
        }

        .pill {
            display: inline-block;
            padding: 5px 9px;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 750;
            background: rgba(59,130,246,0.18);
            border: 1px solid rgba(96,165,250,0.28);
            color: #dbeafe;
            margin: 2px;
            white-space: nowrap;
        }

        .pill-green {
            background: rgba(34,197,94,0.18);
            border-color: rgba(74,222,128,0.28);
            color: #dcfce7;
        }

        .pill-orange {
            background: rgba(245,158,11,0.18);
            border-color: rgba(251,191,36,0.28);
            color: #fef3c7;
        }

        .pill-gray {
            background: rgba(148,163,184,0.18);
            border-color: rgba(203,213,225,0.22);
            color: #e2e8f0;
        }

        .profile-progress {
            height: 10px;
            background: rgba(255,255,255,0.12);
            border-radius: 999px;
            overflow: hidden;
            min-width: 95px;
        }

        .profile-progress-bar {
            height: 100%;
            background: linear-gradient(135deg, #22c55e, #3b82f6);
            border-radius: 999px;
        }

        .progress-text {
            font-size: 0.8rem;
            color: rgba(248,250,252,0.68);
            margin-top: 5px;
        }

        .muted-na {
            color: rgba(255,255,255,0.45);
            font-size: 0.9rem;
        }

        .empty-box {
            text-align: center;
            padding: 50px 20px;
            color: rgba(248,250,252,0.68);
            border: 1px dashed rgba(255,255,255,0.18);
            border-radius: 18px;
        }

        .pagination-wrap {
            display: flex;
            justify-content: space-between;
            gap: 14px;
            align-items: center;
            flex-wrap: wrap;
            margin-top: 18px;
        }

        .pagination-info {
            color: rgba(248,250,252,0.68);
            font-weight: 650;
        }

        .pagination-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .page-link-custom {
            color: #fff;
            text-decoration: none;
            padding: 9px 13px;
            border-radius: 10px;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.14);
            font-weight: 750;
        }

        .page-link-custom:hover {
            color: #fff;
            background: rgba(255,255,255,0.16);
        }

        .page-link-disabled {
            opacity: 0.45;
            pointer-events: none;
        }

        .page-link-active {
            background: linear-gradient(135deg, #3b82f6, #6366f1);
            border-color: transparent;
        }

        @media (max-width: 1100px) {
            .stats-row {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 640px) {
            .page-title {
                font-size: 1.55rem;
            }

            .card-inner {
                padding: 18px;
            }

            .stats-row {
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

            .pagination-actions {
                width: 100%;
            }

            .page-link-custom {
                flex: 1;
                text-align: center;
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
                View and filter alumni by programme, graduation year, and industry sector.
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
            <div class="section-title">Alumni Filters</div>
            <p class="section-desc">
                Apply filters to inspect graduate outcomes for a specific programme, year, or sector.
            </p>

            <?= form_open('analytics/alumni', ['method' => 'get']); ?>
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
                        <a href="<?= site_url('analytics/alumni'); ?>" class="secondary-btn">Reset</a>
                    </div>
                </div>
            <?= form_close(); ?>
        </div>
    </div>

    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-label">Total Matching Alumni</div>
            <div class="stat-value"><?= (int) ($total ?? 0); ?></div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Current Page</div>
            <div class="stat-value"><?= (int) $currentPage; ?></div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Total Pages</div>
            <div class="stat-value"><?= (int) $totalPages; ?></div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Export</div>
            <a href="<?= site_url('analytics/export-csv') . (!empty($activeFilters) ? '?' . $activeFilters : ''); ?>" class="primary-btn w-100">
                Export CSV
            </a>
        </div>
    </div>

    <div class="glass-card">
        <div class="card-inner">
            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-3">
                <div>
                    <div class="section-title">Filtered Alumni Records</div>
                    <p class="section-desc mb-0">
                        Showing <?= count($alumni ?? []); ?> record(s) on this page.
                    </p>
                </div>

                <div class="nav-actions">
                    <a href="<?= site_url('api/alumni'); ?>" class="secondary-btn" target="_blank">
                        API Endpoint
                    </a>
                    <a href="<?= site_url('analytics/dashboard') . (!empty($activeFilters) ? '?' . $activeFilters : ''); ?>" class="secondary-btn">
                        View Charts
                    </a>
                </div>
            </div>

            <?php if (!empty($alumni)): ?>
                <div class="table-responsive">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>Alumnus</th>
                                <th>Programme</th>
                                <th>Graduation</th>
                                <th>Industry</th>
                                <th>Job Title</th>
                                <th>Employer</th>
                                <th>Location</th>
                                <th>Profile</th>
                                <th>Featured</th>
                                <th>LinkedIn</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($alumni as $row): ?>
                                <?php
                                    $fullName = trim(($row->first_name ?? '') . ' ' . ($row->last_name ?? ''));
                                    $completion = isset($row->profile_completion) ? (int) $row->profile_completion : 0;

                                    if ($completion < 0) {
                                        $completion = 0;
                                    }

                                    if ($completion > 100) {
                                        $completion = 100;
                                    }

                                    $locationParts = [];

                                    if (!empty($row->city)) {
                                        $locationParts[] = $row->city;
                                    }

                                    if (!empty($row->country)) {
                                        $locationParts[] = $row->country;
                                    }

                                    $location = !empty($locationParts) ? implode(', ', $locationParts) : '';
                                ?>

                                <tr>
                                    <td>
                                        <div class="name-cell">
                                            <?= html_escape($fullName !== '' ? $fullName : 'Unnamed Alumnus'); ?>
                                        </div>

                                        <div class="email-cell">
                                            <?= html_escape($row->university_email ?? ''); ?>
                                        </div>
                                    </td>

                                    <td>
                                        <?php if (!empty($row->programme)): ?>
                                            <span class="pill"><?= html_escape($row->programme); ?></span>
                                        <?php else: ?>
                                            <span class="muted-na">N/A</span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?php if (!empty($row->graduation_year)): ?>
                                            <span class="pill pill-gray"><?= html_escape($row->graduation_year); ?></span>
                                        <?php else: ?>
                                            <span class="muted-na">N/A</span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?php if (!empty($row->industry_sector)): ?>
                                            <span class="pill pill-green"><?= html_escape($row->industry_sector); ?></span>
                                        <?php else: ?>
                                            <span class="muted-na">N/A</span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?= !empty($row->current_job_title)
                                            ? html_escape($row->current_job_title)
                                            : '<span class="muted-na">N/A</span>'; ?>
                                    </td>

                                    <td>
                                        <?= !empty($row->current_employer)
                                            ? html_escape($row->current_employer)
                                            : '<span class="muted-na">N/A</span>'; ?>
                                    </td>

                                    <td>
                                        <?= !empty($location)
                                            ? html_escape($location)
                                            : '<span class="muted-na">N/A</span>'; ?>
                                    </td>

                                    <td>
                                        <div class="profile-progress">
                                            <div class="profile-progress-bar" style="width: <?= (int) $completion; ?>%;"></div>
                                        </div>
                                        <div class="progress-text"><?= (int) $completion; ?>% complete</div>
                                    </td>

                                    <td>
                                        <?php if ((int) ($row->is_featured ?? 0) === 1): ?>
                                            <span class="pill pill-orange">
                                                Featured <?= !empty($row->featured_for_date) ? html_escape($row->featured_for_date) : ''; ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="muted-na">No</span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?php if (!empty($row->linkedin_url)): ?>
                                            <a href="<?= html_escape($row->linkedin_url); ?>" target="_blank" rel="noopener noreferrer" class="secondary-btn">
                                                Open
                                            </a>
                                        <?php else: ?>
                                            <span class="muted-na">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="pagination-wrap">
                    <div class="pagination-info">
                        Page <?= (int) $currentPage; ?> of <?= (int) $totalPages; ?> —
                        <?= (int) ($total ?? 0); ?> total record(s)
                    </div>

                    <div class="pagination-actions">
                        <a
                            href="<?= build_alumni_page_url(1, $filters ?? []); ?>"
                            class="page-link-custom <?= ($currentPage <= 1) ? 'page-link-disabled' : ''; ?>"
                        >
                            First
                        </a>

                        <a
                            href="<?= build_alumni_page_url(max(1, $currentPage - 1), $filters ?? []); ?>"
                            class="page-link-custom <?= ($currentPage <= 1) ? 'page-link-disabled' : ''; ?>"
                        >
                            Previous
                        </a>

                        <?php
                            $startPage = max(1, $currentPage - 2);
                            $endPage = min($totalPages, $currentPage + 2);
                        ?>

                        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                            <a
                                href="<?= build_alumni_page_url($i, $filters ?? []); ?>"
                                class="page-link-custom <?= ($i === $currentPage) ? 'page-link-active' : ''; ?>"
                            >
                                <?= (int) $i; ?>
                            </a>
                        <?php endfor; ?>

                        <a
                            href="<?= build_alumni_page_url(min($totalPages, $currentPage + 1), $filters ?? []); ?>"
                            class="page-link-custom <?= ($currentPage >= $totalPages) ? 'page-link-disabled' : ''; ?>"
                        >
                            Next
                        </a>

                        <a
                            href="<?= build_alumni_page_url($totalPages, $filters ?? []); ?>"
                            class="page-link-custom <?= ($currentPage >= $totalPages) ? 'page-link-disabled' : ''; ?>"
                        >
                            Last
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="empty-box">
                    <h4>No alumni found</h4>
                    <p class="mb-0">
                        Try changing the filters or update alumni profile analytics fields such as programme, graduation year, and industry sector.
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

</body>
</html>