<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$pageTitle = isset($title) && trim((string) $title) !== ''
    ? $title
    : 'Developer API Dashboard';

$currentRole = $this->session->userdata('role') ?: 'developer';

if (!function_exists('developer_safe_date')) {
    function developer_safe_date($dateValue, $format = 'M j, Y h:i A')
    {
        if (empty($dateValue)) {
            return null;
        }

        $timestamp = strtotime($dateValue);

        if (!$timestamp) {
            return null;
        }

        return date($format, $timestamp);
    }
}

if (!function_exists('developer_decode_permissions')) {
    function developer_decode_permissions($permissionsValue)
    {
        if (empty($permissionsValue)) {
            return ['read:alumni_of_day'];
        }

        if (is_array($permissionsValue)) {
            return $permissionsValue;
        }

        $decoded = json_decode($permissionsValue, true);

        if (!is_array($decoded) || empty($decoded)) {
            return ['read:alumni_of_day'];
        }

        return $decoded;
    }
}

if (!function_exists('developer_key_preview')) {
    function developer_key_preview($key)
    {
        if (!empty($key->key_prefix)) {
            return $key->key_prefix . '...';
        }

        /*
         * Do not fall back to displaying the raw API key.
         * After the API model security fix, raw API keys should not be stored.
         */
        return 'Stored securely';
    }
}

if (!function_exists('developer_client_type_label')) {
    function developer_client_type_label($clientType)
    {
        if (empty($clientType)) {
            return 'General';
        }

        return ucwords(str_replace('_', ' ', $clientType));
    }
}

$isAnalyticsKey =
    (($new_key_client_type ?? '') === 'analytics_dashboard') ||
    (strpos(($new_key_permissions ?? ''), 'read:analytics') !== false);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= html_escape($pageTitle); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #0f172a;
            color: #fff;
            padding: 30px 15px;
        }

        .dashboard-wrapper {
            max-width: 1250px;
            margin: auto;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }

        .dashboard-title {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 4px;
            color: #ffffff;
        }

        .dashboard-subtitle {
            color: rgba(255,255,255,0.75);
            font-size: 0.95rem;
            line-height: 1.55;
        }

        .header-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .back-link,
        .secondary-btn {
            text-decoration: none;
            color: #fff;
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.15);
            padding: 12px 18px;
            border-radius: 12px;
            transition: all 0.3s ease;
            font-weight: 700;
            display: inline-block;
            text-align: center;
        }

        .back-link:hover,
        .secondary-btn:hover {
            background: rgba(255,255,255,0.2);
            color: #fff;
            transform: translateY(-2px);
        }

        .role-pill {
            display: inline-block;
            margin-top: 8px;
            padding: 6px 10px;
            border-radius: 999px;
            background: rgba(59,130,246,0.18);
            border: 1px solid rgba(96,165,250,0.28);
            color: #dbeafe;
            font-size: 0.82rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .glass-card {
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.14);
            box-shadow: 0 12px 40px rgba(0,0,0,0.25);
            border-radius: 22px;
            overflow: hidden;
            margin-bottom: 25px;
        }

        .card-inner {
            padding: 28px;
        }

        .section-title {
            font-size: 1.4rem;
            font-weight: 800;
            margin-bottom: 8px;
            color: #fff;
        }

        .section-desc {
            color: rgba(255,255,255,0.76);
            margin-bottom: 20px;
            line-height: 1.6;
        }

        .code-inline {
            display: inline-block;
            background: rgba(255,255,255,0.1);
            color: #cbd5e1;
            padding: 6px 10px;
            border-radius: 8px;
            font-family: monospace;
            margin-top: 6px;
            word-break: break-all;
        }

        .modern-btn {
            border: none;
            outline: none;
            padding: 12px 20px;
            border-radius: 12px;
            font-weight: 800;
            color: #fff;
            background: linear-gradient(135deg, #3b82f6, #6366f1);
            box-shadow: 0 10px 25px rgba(59,130,246,0.35);
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            cursor: pointer;
        }

        .modern-btn:hover {
            color: #fff;
            transform: translateY(-2px) scale(1.02);
            box-shadow: 0 16px 30px rgba(99,102,241,0.45);
        }

        .alert-modern {
            border: none;
            border-radius: 14px;
            padding: 14px 18px;
            margin-bottom: 18px;
            font-weight: 600;
            line-height: 1.55;
        }

        .alert-success-modern {
            background: rgba(34, 197, 94, 0.18);
            color: #dcfce7;
            border-left: 4px solid #22c55e;
        }

        .alert-danger-modern {
            background: rgba(239, 68, 68, 0.18);
            color: #fee2e2;
            border-left: 4px solid #ef4444;
        }

        .alert-warning-modern {
            background: rgba(245, 158, 11, 0.20);
            color: #fef3c7;
            border-left: 4px solid #f59e0b;
        }

        .new-key-box {
            background: rgba(0,0,0,0.28);
            border: 1px solid rgba(255,255,255,0.18);
            border-radius: 14px;
            padding: 14px;
            margin-top: 10px;
            word-break: break-all;
            font-family: monospace;
            color: #fef9c3;
        }

        .form-label {
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
            padding: 12px 14px;
        }

        .form-control:focus,
        .form-select:focus {
            background: rgba(15,23,42,0.98);
            color: #fff;
            border-color: #60a5fa;
            box-shadow: 0 0 0 0.2rem rgba(96,165,250,0.2);
        }

        .form-control::placeholder {
            color: rgba(255,255,255,0.45);
        }

        .form-help {
            color: rgba(255,255,255,0.62);
            font-size: 0.88rem;
            margin-top: 6px;
            line-height: 1.5;
        }

        .analytics-shortcuts,
        .button-row {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 14px;
        }

        .table-responsive {
            border-radius: 18px;
            overflow-x: auto;
        }

        .modern-table {
            width: 100%;
            min-width: 980px;
            margin: 0;
            color: #fff;
            border-collapse: collapse;
        }

        .modern-table thead {
            background: rgba(255,255,255,0.1);
        }

        .modern-table th {
            padding: 16px;
            font-size: 0.82rem;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            font-weight: 800;
            border: none;
            color: #e2e8f0;
            white-space: nowrap;
        }

        .modern-table td {
            padding: 16px;
            border: none;
            border-top: 1px solid rgba(255,255,255,0.08);
            vertical-align: middle;
        }

        .modern-table tbody tr:hover {
            background: rgba(255,255,255,0.06);
        }

        .badge-modern {
            display: inline-block;
            padding: 7px 14px;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 800;
            letter-spacing: 0.4px;
            text-transform: uppercase;
        }

        .badge-active {
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: white;
        }

        .badge-revoked {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
        }

        .badge-type {
            background: rgba(20,184,166,0.22);
            color: #ccfbf1;
            border: 1px solid rgba(45,212,191,0.35);
        }

        .permission-pill {
            display: inline-block;
            background: rgba(59,130,246,0.22);
            border: 1px solid rgba(96,165,250,0.35);
            color: #dbeafe;
            padding: 5px 9px;
            border-radius: 999px;
            font-size: 0.78rem;
            font-family: monospace;
            margin: 2px;
        }

        .key-text,
        .endpoint-code {
            display: inline-block;
            font-family: monospace;
            background: rgba(255,255,255,0.08);
            color: #bfdbfe;
            padding: 8px 12px;
            border-radius: 10px;
            word-break: break-all;
            border: 1px solid rgba(255,255,255,0.08);
        }

        .endpoint-code {
            color: #e2e8f0;
            padding: 5px 9px;
            font-size: 0.9rem;
        }

        .btn-danger-modern {
            border: none;
            text-decoration: none;
            padding: 9px 14px;
            border-radius: 10px;
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            font-weight: 700;
            transition: all 0.3s ease;
            display: inline-block;
            box-shadow: 0 8px 18px rgba(239,68,68,0.28);
            cursor: pointer;
        }

        .btn-danger-modern:hover {
            transform: translateY(-2px);
            color: white;
            box-shadow: 0 12px 22px rgba(239,68,68,0.4);
        }

        .empty-text,
        .muted-na {
            color: rgba(255,255,255,0.6);
            font-size: 0.9rem;
        }

        .analysis-result {
            margin-top: 18px;
            background: rgba(15,23,42,0.72);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 16px;
            padding: 18px;
            display: none;
        }

        .analysis-result.show {
            display: block;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
            margin-top: 16px;
        }

        .summary-box {
            background: rgba(255,255,255,0.07);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 14px;
            padding: 14px;
        }

        .summary-label {
            color: rgba(255,255,255,0.62);
            font-size: 0.84rem;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .summary-value {
            color: #fff;
            font-size: 1.35rem;
            font-weight: 900;
            word-break: break-word;
        }

        .json-box {
            margin-top: 16px;
            background: rgba(0,0,0,0.35);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 14px;
            padding: 14px;
            overflow-x: auto;
            color: #dbeafe;
            font-size: 0.86rem;
            max-height: 420px;
            white-space: pre-wrap;
        }

        .notice-box {
            background: rgba(59,130,246,0.12);
            border: 1px solid rgba(96,165,250,0.24);
            color: #dbeafe;
            border-radius: 14px;
            padding: 14px 16px;
            margin-bottom: 18px;
            line-height: 1.6;
        }

        @media (max-width: 900px) {
            .summary-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 768px) {
            .dashboard-title {
                font-size: 1.6rem;
            }

            .card-inner {
                padding: 20px;
            }

            .back-link,
            .modern-btn,
            .secondary-btn {
                width: 100%;
                text-align: center;
            }

            .summary-grid {
                grid-template-columns: 1fr;
            }

            .header-actions {
                width: 100%;
            }
        }
    </style>
</head>

<body>

<div class="dashboard-wrapper">

    <div class="dashboard-header">
        <div>
            <div class="dashboard-title"><?= html_escape($pageTitle); ?></div>
            <div class="dashboard-subtitle">
                Generate scoped API keys, test protected API access, and monitor endpoint usage.
            </div>
            <span class="role-pill"><?= html_escape($currentRole); ?> access</span>
        </div>

        <div class="header-actions">
            <a href="<?= site_url('dashboard'); ?>" class="back-link">← Main Dashboard</a>
            <a href="<?= site_url('analytics/dashboard'); ?>" class="back-link">Analytics</a>
        </div>
    </div>

    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert-modern alert-success-modern">
            <?= html_escape($this->session->flashdata('success')); ?>
        </div>
    <?php endif; ?>

    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert-modern alert-danger-modern">
            <?= html_escape(strip_tags($this->session->flashdata('error'))); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($new_api_key)): ?>
        <div class="alert-modern alert-warning-modern">
            <strong>New API Key Generated</strong>

            <p class="mb-1 mt-2">
                Copy this key now. It is shown only once:
            </p>

            <div class="new-key-box" id="newApiKeyText">
                <?= html_escape($new_api_key); ?>
            </div>

            <?php if (!empty($new_key_client_name) || !empty($new_key_client_type) || !empty($new_key_permissions)): ?>
                <div class="mt-3">
                    <div><strong>Client Name:</strong> <?= html_escape($new_key_client_name ?? 'N/A'); ?></div>
                    <div><strong>Client Type:</strong> <?= html_escape($new_key_client_type ?? 'N/A'); ?></div>
                    <div><strong>Permissions:</strong> <?= html_escape($new_key_permissions ?? 'N/A'); ?></div>
                </div>
            <?php endif; ?>

            <div class="analytics-shortcuts">
                <button type="button" class="modern-btn" onclick="copyNewKeyToTester()">
                    Use This Key for API Test
                </button>

                <?php if ($isAnalyticsKey): ?>
                    <a href="<?= site_url('analytics/dashboard'); ?>" class="secondary-btn">
                        Open Analytics Dashboard
                    </a>

                    <a href="<?= site_url('analytics/alumni'); ?>" class="secondary-btn">
                        View Alumni
                    </a>

                    <a href="<?= site_url('analytics/reports'); ?>" class="secondary-btn">
                        View Reports
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="notice-box">
        <strong>Permission model:</strong>
        Mobile AR App keys should use <code>read:alumni_of_day</code>.
        Analytics Dashboard keys should use <code>read:alumni</code> and <code>read:analytics</code>.
        This supports the coursework requirement for scoped client access.
    </div>

    <div class="glass-card">
        <div class="card-inner">
            <h2 class="section-title">Generate Scoped API Key</h2>

            <p class="section-desc">
                Choose the client platform. Each client type receives only the permissions it needs.
            </p>

            <?= form_open('developer/generate'); ?>
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="client_type" class="form-label">Client Type</label>

                        <select name="client_type" id="client_type" class="form-select" required>
                            <option value="analytics_dashboard">University Analytics Dashboard</option>
                            <option value="ar_app">Mobile AR App</option>
                            <option value="general">General API Client</option>
                        </select>

                        <div class="form-help">
                            Analytics Dashboard gets <code>read:alumni</code> and <code>read:analytics</code>.
                            Mobile AR App gets <code>read:alumni_of_day</code>.
                        </div>
                    </div>

                    <div class="col-md-5">
                        <label for="client_name" class="form-label">Client Name</label>

                        <input
                            type="text"
                            name="client_name"
                            id="client_name"
                            class="form-control"
                            maxlength="100"
                            placeholder="Example: University Analytics Dashboard"
                        >

                        <div class="form-help">
                            Leave blank to use the default client name.
                        </div>
                    </div>

                    <div class="col-md-3">
                        <button type="submit" class="modern-btn w-100">
                            + Generate Key
                        </button>
                    </div>
                </div>
            <?= form_close(); ?>
        </div>
    </div>

    <div class="glass-card">
        <div class="card-inner">
            <h2 class="section-title">Test API Key Access</h2>

            <p class="section-desc">
                Paste an API key to test whether it can access protected API endpoints.
                <br>
                <span class="code-inline">Authorization: Bearer YOUR_API_KEY</span>
            </p>

            <div class="row g-3 align-items-end">
                <div class="col-md-8">
                    <label for="apiKeySearch" class="form-label">API Key</label>

                    <input
                        type="password"
                        id="apiKeySearch"
                        class="form-control"
                        placeholder="Paste your API key here"
                        autocomplete="off"
                    >

                    <div class="form-help">
                        Analytics keys can access analytics/alumni endpoints. AR keys should only access Alumni of the Day.
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="button-row">
                        <button type="button" class="secondary-btn" onclick="toggleApiKeyVisibility()">
                            Show / Hide
                        </button>

                        <button type="button" class="modern-btn" onclick="testAnalyticsSummary()">
                            Test Summary
                        </button>
                    </div>
                </div>
            </div>

            <div class="button-row">
                <button type="button" class="secondary-btn" onclick="testAlumniList()">
                    Test Alumni API
                </button>

                <button type="button" class="secondary-btn" onclick="testFullAnalytics()">
                    Test Full Analytics
                </button>

                <button type="button" class="secondary-btn" onclick="testAlumniOfDay()">
                    Test Alumni of the Day
                </button>

                <a href="<?= site_url('analytics/dashboard'); ?>" class="secondary-btn">
                    Open Web Dashboard
                </a>

                <a href="<?= site_url('analytics/reports'); ?>" class="secondary-btn">
                    Open Reports
                </a>
            </div>

            <div id="analysisResult" class="analysis-result">
                <h4 id="analysisTitle">API Test Result</h4>
                <p id="analysisMessage" class="section-desc mb-0"></p>

                <div id="summaryGrid" class="summary-grid"></div>

                <pre id="rawJsonBox" class="json-box"></pre>
            </div>
        </div>
    </div>

    <div class="glass-card">
        <div class="card-inner">
            <h2 class="section-title">Your API Keys</h2>

            <p class="section-desc">
                Only a safe preview is displayed. Full keys are shown once immediately after generation.
            </p>

            <?php if (!empty($api_keys)): ?>
                <div class="table-responsive mt-4">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>Key Preview</th>
                                <th>Client</th>
                                <th>Type</th>
                                <th>Permissions</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Last Used</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($api_keys as $key): ?>
                                <?php
                                    $preview = developer_key_preview($key);
                                    $permissions = developer_decode_permissions($key->permissions ?? null);
                                    $clientType = developer_client_type_label($key->client_type ?? null);
                                    $createdAt = developer_safe_date($key->created_at ?? null);
                                    $lastUsedAt = developer_safe_date($key->last_used_at ?? null);
                                    $status = $key->status ?? 'unknown';
                                ?>

                                <tr>
                                    <td>
                                        <span class="key-text">
                                            <?= html_escape($preview); ?>
                                        </span>
                                    </td>

                                    <td>
                                        <?php if (!empty($key->client_name)): ?>
                                            <?= html_escape($key->client_name); ?>
                                        <?php else: ?>
                                            <span class="muted-na">N/A</span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <span class="badge-modern badge-type">
                                            <?= html_escape($clientType); ?>
                                        </span>
                                    </td>

                                    <td>
                                        <?php foreach ($permissions as $permission): ?>
                                            <span class="permission-pill"><?= html_escape($permission); ?></span>
                                        <?php endforeach; ?>
                                    </td>

                                    <td>
                                        <?php if ($status === 'active'): ?>
                                            <span class="badge-modern badge-active">Active</span>
                                        <?php else: ?>
                                            <span class="badge-modern badge-revoked"><?= html_escape(ucfirst($status)); ?></span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?php if ($createdAt): ?>
                                            <?= html_escape($createdAt); ?>
                                        <?php else: ?>
                                            <span class="muted-na">N/A</span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?php if ($lastUsedAt): ?>
                                            <?= html_escape($lastUsedAt); ?>
                                        <?php else: ?>
                                            <span class="muted-na">Never</span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?php if ($status === 'active'): ?>
                                            <?= form_open('developer/revoke/' . (int) $key->id, [
                                                'onsubmit' => "return confirm('Are you sure you want to revoke this key?');"
                                            ]); ?>
                                                <button type="submit" class="btn-danger-modern">
                                                    Revoke
                                                </button>
                                            <?= form_close(); ?>
                                        <?php else: ?>
                                            <span class="muted-na">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="empty-text">You have not generated any API keys yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="glass-card">
        <div class="card-inner">
            <h2 class="section-title">Recent API Usage Logs</h2>

            <p class="section-desc">
                Track endpoint access, IP address, method, response status, and key usage.
            </p>

            <?php if (!empty($logs)): ?>
                <div class="table-responsive">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>Date & Time</th>
                                <th>Endpoint</th>
                                <th>Method</th>
                                <th>Status</th>
                                <th>IP Address</th>
                                <th>Client</th>
                                <th>Key Used</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($logs as $log): ?>
                                <?php
                                    $logPreview = developer_key_preview($log);
                                    $logDate = developer_safe_date($log->accessed_at ?? null, 'M j, Y h:i:s A');
                                ?>

                                <tr>
                                    <td>
                                        <?php if ($logDate): ?>
                                            <?= html_escape($logDate); ?>
                                        <?php else: ?>
                                            <span class="muted-na">N/A</span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <span class="endpoint-code">
                                            <?= html_escape($log->endpoint ?? 'N/A'); ?>
                                        </span>
                                    </td>

                                    <td>
                                        <?= html_escape(!empty($log->method) ? $log->method : 'GET'); ?>
                                    </td>

                                    <td>
                                        <?= html_escape(!empty($log->status_code) ? $log->status_code : '200'); ?>
                                    </td>

                                    <td><?= html_escape($log->ip_address ?? 'N/A'); ?></td>

                                    <td>
                                        <?php if (!empty($log->client_name)): ?>
                                            <?= html_escape($log->client_name); ?>
                                        <?php else: ?>
                                            <span class="muted-na">N/A</span>
                                        <?php endif; ?>
                                    </td>

                                    <td><?= html_escape($logPreview); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="empty-text">No API activity recorded yet.</p>
            <?php endif; ?>
        </div>
    </div>

</div>

<script>
    const analyticsSummaryUrl = "<?= site_url('api/analytics/summary'); ?>";
    const alumniApiUrl = "<?= site_url('api/alumni'); ?>";
    const analyticsFullUrl = "<?= site_url('api/analytics/full'); ?>";
    const alumniOfDayUrl = "<?= site_url('api/featured-today'); ?>";

    function getApiKeyValue() {
        const input = document.getElementById('apiKeySearch');
        return input ? input.value.trim() : '';
    }

    function toggleApiKeyVisibility() {
        const input = document.getElementById('apiKeySearch');

        if (!input) {
            return;
        }

        input.type = input.type === 'password' ? 'text' : 'password';
    }

    function copyNewKeyToTester() {
        const keyBox = document.getElementById('newApiKeyText');
        const input = document.getElementById('apiKeySearch');

        if (!keyBox || !input) {
            return;
        }

        input.value = keyBox.innerText.trim();
        input.scrollIntoView({ behavior: 'smooth', block: 'center' });
        input.focus();
    }

    async function callProtectedApi(url, title) {
        const apiKey = getApiKeyValue();

        const resultBox = document.getElementById('analysisResult');
        const titleBox = document.getElementById('analysisTitle');
        const messageBox = document.getElementById('analysisMessage');
        const summaryGrid = document.getElementById('summaryGrid');
        const rawBox = document.getElementById('rawJsonBox');

        if (!resultBox || !titleBox || !messageBox || !summaryGrid || !rawBox) {
            return;
        }

        resultBox.classList.add('show');
        titleBox.innerText = title;
        messageBox.innerText = 'Loading...';
        summaryGrid.innerHTML = '';
        rawBox.innerText = '';

        if (!apiKey) {
            messageBox.innerText = 'Please paste an API key first.';
            return;
        }

        try {
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'Authorization': 'Bearer ' + apiKey,
                    'Accept': 'application/json'
                }
            });

            let data = null;

            try {
                data = await response.json();
            } catch (jsonError) {
                messageBox.innerText = 'The server did not return valid JSON.';
                rawBox.innerText = jsonError.toString();
                return;
            }

            if (!response.ok || data.status !== 'success') {
                messageBox.innerText = data.message || 'API request failed.';
                rawBox.innerText = JSON.stringify(data, null, 2);
                return;
            }

            messageBox.innerText = 'API key is valid for this endpoint. Data loaded successfully.';
            renderSummaryCards(data);
            rawBox.innerText = JSON.stringify(data, null, 2);

        } catch (error) {
            messageBox.innerText = 'Request failed. Check your route, API key, CORS settings, or server console.';
            rawBox.innerText = error.toString();
        }
    }

    function renderSummaryCards(data) {
        const summaryGrid = document.getElementById('summaryGrid');

        if (!summaryGrid) {
            return;
        }

        summaryGrid.innerHTML = '';

        let summary = null;

        if (data.data && data.data.total_alumni !== undefined) {
            summary = data.data;
        } else if (data.data && data.data.summary) {
            summary = data.data.summary;
        } else if (data.data && data.data.profile) {
            summary = {
                featured_date: data.data.featured_date || 'N/A',
                alumnus: ((data.data.profile.first_name || '') + ' ' + (data.data.profile.last_name || '')).trim() || 'N/A',
                email: data.data.profile.email || 'N/A'
            };
        }

        if (!summary) {
            return;
        }

        let cards = [];

        if (summary.total_alumni !== undefined) {
            cards = [
                ['Total Alumni', summary.total_alumni ?? 0],
                ['Programmes', summary.total_programmes ?? 0],
                ['Top Industry', summary.top_industry?.label ?? 'No data'],
                ['Top Employer', summary.top_employer?.label ?? 'No data'],
                ['Top Job Title', summary.top_job_title?.label ?? 'No data'],
                ['Top Location', summary.top_location?.label ?? 'No data'],
                ['Certifications', summary.total_certifications ?? 0],
                ['Courses', summary.total_courses ?? 0]
            ];
        } else {
            cards = [
                ['Featured Date', summary.featured_date ?? 'N/A'],
                ['Alumnus', summary.alumnus ?? 'N/A'],
                ['Email', summary.email ?? 'N/A']
            ];
        }

        cards.forEach(function(card) {
            const box = document.createElement('div');
            box.className = 'summary-box';

            box.innerHTML =
                '<div class="summary-label">' + escapeHtml(card[0]) + '</div>' +
                '<div class="summary-value">' + escapeHtml(String(card[1])) + '</div>';

            summaryGrid.appendChild(box);
        });
    }

    function testAnalyticsSummary() {
        callProtectedApi(analyticsSummaryUrl, 'Analytics Summary');
    }

    function testAlumniList() {
        callProtectedApi(alumniApiUrl, 'Alumni API Result');
    }

    function testFullAnalytics() {
        callProtectedApi(analyticsFullUrl, 'Full Analytics Payload');
    }

    function testAlumniOfDay() {
        callProtectedApi(alumniOfDayUrl, 'Alumni of the Day API Result');
    }

    function escapeHtml(value) {
        return String(value)
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }
</script>

</body>
</html>