<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= html_escape($title) ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

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
            max-width: 1180px;
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
            font-weight: 700;
            margin-bottom: 4px;
            color: #ffffff;
        }

        .dashboard-subtitle {
            color: rgba(255,255,255,0.75);
            font-size: 0.95rem;
        }

        .back-link {
            text-decoration: none;
            color: #fff;
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.15);
            padding: 12px 18px;
            border-radius: 12px;
            transition: all 0.3s ease;
            font-weight: 600;
        }

        .back-link:hover {
            background: rgba(255,255,255,0.2);
            color: #fff;
            transform: translateY(-2px);
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
            font-weight: 700;
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
        }

        .modern-btn {
            border: none;
            outline: none;
            padding: 12px 20px;
            border-radius: 12px;
            font-weight: 700;
            color: #fff;
            background: linear-gradient(135deg, #3b82f6, #6366f1);
            box-shadow: 0 10px 25px rgba(59,130,246,0.35);
            transition: all 0.3s ease;
        }

        .modern-btn:hover {
            transform: translateY(-2px) scale(1.02);
            box-shadow: 0 16px 30px rgba(99,102,241,0.45);
        }

        .alert-modern {
            border: none;
            border-radius: 14px;
            padding: 14px 18px;
            margin-bottom: 18px;
            font-weight: 600;
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

        .table-responsive {
            border-radius: 18px;
            overflow: hidden;
        }

        .modern-table {
            width: 100%;
            margin: 0;
            color: #fff;
            border-collapse: collapse;
        }

        .modern-table thead {
            background: rgba(255,255,255,0.1);
        }

        .modern-table th {
            padding: 16px;
            font-size: 0.92rem;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            font-weight: 700;
            border: none;
            color: #e2e8f0;
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
            font-size: 0.8rem;
            font-weight: 700;
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

        .key-text {
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
            display: inline-block;
            background: rgba(255,255,255,0.08);
            color: #e2e8f0;
            padding: 5px 9px;
            border-radius: 8px;
            font-family: monospace;
            font-size: 0.9rem;
        }

        .btn-danger-modern {
            text-decoration: none;
            padding: 9px 14px;
            border-radius: 10px;
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-block;
            box-shadow: 0 8px 18px rgba(239,68,68,0.28);
        }

        .btn-danger-modern:hover {
            transform: translateY(-2px);
            color: white;
            box-shadow: 0 12px 22px rgba(239,68,68,0.4);
        }

        .empty-text {
            color: rgba(255,255,255,0.7);
            margin-top: 12px;
            font-size: 0.96rem;
        }

        .muted-na {
            color: rgba(255,255,255,0.5);
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .dashboard-title {
                font-size: 1.6rem;
            }

            .card-inner {
                padding: 20px;
            }

            .modern-table th,
            .modern-table td {
                padding: 12px 10px;
                font-size: 0.88rem;
            }

            .back-link,
            .modern-btn {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>

<div class="dashboard-wrapper">

    <div class="dashboard-header">
        <div>
            <div class="dashboard-title"><?= html_escape($title) ?></div>
            <div class="dashboard-subtitle">
                Manage API keys, revoke access, and monitor API usage logs.
            </div>
        </div>

        <a href="<?= site_url('auth/dashboard') ?>" class="back-link">← Back to Dashboard</a>
    </div>

    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert-modern alert-success-modern">
            <?= html_escape($this->session->flashdata('success')); ?>
        </div>
    <?php endif; ?>

    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert-modern alert-danger-modern">
            <?= html_escape($this->session->flashdata('error')); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($new_api_key)): ?>
        <div class="alert-modern alert-warning-modern">
            <strong>New API Key Generated</strong>
            <p class="mb-1 mt-2">
                Copy this key now. It is shown only once:
            </p>
            <div class="new-key-box">
                <?= html_escape($new_api_key); ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="glass-card">
        <div class="card-inner">
            <h2 class="section-title">Your API Keys</h2>

            <p class="section-desc">
                Use these keys to authenticate with the Alumni Data API.<br>
                Include the key in your request headers as:
                <br>
                <span class="code-inline">Authorization: Bearer YOUR_API_KEY</span>
            </p>

            <?= form_open('developer/generate') ?>
                <button type="submit" class="modern-btn">+ Generate New API Key</button>
            <?= form_close() ?>

            <?php if (!empty($api_keys)): ?>
                <div class="table-responsive mt-4">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>API Key Preview</th>
                                <th>Status</th>
                                <th>Created At</th>
                                <th>Revoked At</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($api_keys as $key): ?>
                                <tr>
                                    <td>
                                        <span class="key-text">
                                            <?= html_escape(substr($key->api_key, 0, 12)); ?>...
                                        </span>
                                    </td>

                                    <td>
                                        <?php if ($key->status === 'active'): ?>
                                            <span class="badge-modern badge-active">Active</span>
                                        <?php else: ?>
                                            <span class="badge-modern badge-revoked">Revoked</span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?= !empty($key->created_at)
                                            ? html_escape(date('M j, Y h:i A', strtotime($key->created_at)))
                                            : '<span class="muted-na">N/A</span>'; ?>
                                    </td>

                                    <td>
                                        <?= !empty($key->revoked_at)
                                            ? html_escape(date('M j, Y h:i A', strtotime($key->revoked_at)))
                                            : '<span class="muted-na">N/A</span>'; ?>
                                    </td>

                                    <td>
                                        <?php if ($key->status === 'active'): ?>
                                            <a
                                                href="<?= site_url('developer/revoke/' . (int) $key->id) ?>"
                                                class="btn-danger-modern"
                                                onclick="return confirm('Are you sure you want to revoke this key? Any applications using it will lose access immediately.');"
                                            >
                                                Revoke
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
            <?php else: ?>
                <p class="empty-text">You have not generated any API keys yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="glass-card">
        <div class="card-inner">
            <h2 class="section-title">Recent API Usage Logs</h2>

            <p class="section-desc">
                Track when and how your API keys are being used by third-party clients.
            </p>

            <?php if (!empty($logs)): ?>
                <div class="table-responsive">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>Date & Time</th>
                                <th>Endpoint Accessed</th>
                                <th>IP Address</th>
                                <th>Key Used</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td>
                                        <?= !empty($log->accessed_at)
                                            ? html_escape(date('M j, Y h:i:s A', strtotime($log->accessed_at)))
                                            : '<span class="muted-na">N/A</span>'; ?>
                                    </td>

                                    <td>
                                        <span class="endpoint-code">
                                            <?= html_escape($log->endpoint); ?>
                                        </span>
                                    </td>

                                    <td><?= html_escape($log->ip_address); ?></td>

                                    <td>
                                        <?php if (!empty($log->api_key)): ?>
                                            <?= html_escape(substr($log->api_key, 0, 12)); ?>...
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
                <p class="empty-text">No API activity recorded yet.</p>
            <?php endif; ?>
        </div>
    </div>

</div>

</body>
</html>