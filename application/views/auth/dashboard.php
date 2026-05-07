<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$role = $this->session->userdata('role') ?: 'alumnus';

$firstName = trim((string) $this->session->userdata('first_name'));
$lastName  = trim((string) $this->session->userdata('last_name'));
$fullName  = trim($firstName . ' ' . $lastName);

if ($fullName === '') {
    $fullName = 'User';
}

$isAlumnus = ($role === 'alumnus');
$isStaff   = in_array($role, ['developer', 'admin'], true);

$pageTitle = isset($title) && trim((string) $title) !== ''
    ? $title
    : 'Alumni Dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= html_escape($pageTitle); ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <style>
        :root {
            --bg-1: #0b1220;
            --bg-2: #111827;
            --card-bg: rgba(255, 255, 255, 0.08);
            --card-border: rgba(255, 255, 255, 0.14);
            --text: #f8fafc;
            --muted: #94a3b8;
            --primary: #6366f1;
            --primary-2: #8b5cf6;
            --danger: #ef4444;
            --success: #22c55e;
            --warning: #f59e0b;
            --shadow: 0 20px 60px rgba(0,0,0,0.35);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at top left, rgba(99,102,241,0.25), transparent 30%),
                radial-gradient(circle at bottom right, rgba(139,92,246,0.22), transparent 30%),
                linear-gradient(135deg, var(--bg-1), var(--bg-2));
            overflow-x: hidden;
        }

        body::before,
        body::after {
            content: "";
            position: fixed;
            width: 320px;
            height: 320px;
            border-radius: 50%;
            filter: blur(90px);
            z-index: 0;
            opacity: 0.35;
            pointer-events: none;
            animation: floatGlow 10s ease-in-out infinite alternate;
        }

        body::before {
            background: #6366f1;
            top: -80px;
            left: -80px;
        }

        body::after {
            background: #8b5cf6;
            right: -80px;
            bottom: -80px;
            animation-delay: 2s;
        }

        @keyframes floatGlow {
            from {
                transform: translateY(0) translateX(0) scale(1);
            }

            to {
                transform: translateY(25px) translateX(15px) scale(1.08);
            }
        }

        .page {
            position: relative;
            z-index: 1;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        .dashboard-container {
            width: 100%;
            max-width: 1150px;
            background: rgba(255,255,255,0.07);
            border: 1px solid rgba(255,255,255,0.14);
            backdrop-filter: blur(20px);
            border-radius: 28px;
            padding: 36px;
            box-shadow: var(--shadow);
            animation: fadeUp 0.8s ease;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(22px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            flex-wrap: wrap;
            margin-bottom: 28px;
        }

        .heading h1 {
            margin: 0;
            font-size: clamp(2rem, 4vw, 3rem);
            font-weight: 800;
            line-height: 1.1;
            letter-spacing: -0.03em;
            background: linear-gradient(90deg, #ffffff, #c4b5fd, #93c5fd);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .welcome-text {
            margin: 14px 0 0;
            max-width: 760px;
            color: var(--muted);
            font-size: 1rem;
            line-height: 1.7;
        }

        .badge {
            padding: 10px 16px;
            border-radius: 999px;
            background: rgba(99,102,241,0.16);
            border: 1px solid rgba(99,102,241,0.28);
            color: #c7d2fe;
            font-size: 0.92rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .hero-panel {
            display: grid;
            grid-template-columns: 1.3fr 0.7fr;
            gap: 24px;
            margin-bottom: 30px;
        }

        .hero-card,
        .info-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 24px;
            padding: 24px;
            box-shadow: 0 16px 40px rgba(0,0,0,0.22);
        }

        .hero-card h2,
        .info-card h3 {
            margin-top: 0;
            margin-bottom: 10px;
        }

        .hero-card p,
        .info-card p {
            color: var(--muted);
            line-height: 1.7;
            margin-bottom: 0;
        }

        .quick-stats {
            margin-top: 18px;
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
        }

        .stat-chip {
            padding: 12px 14px;
            border-radius: 16px;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.08);
            min-width: 130px;
        }

        .stat-chip strong {
            display: block;
            font-size: 1.1rem;
            color: #fff;
        }

        .stat-chip span {
            color: var(--muted);
            font-size: 0.9rem;
        }

        .action-cards {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .card {
            position: relative;
            overflow: hidden;
            background: rgba(255,255,255,0.07);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 22px;
            padding: 24px;
            text-decoration: none;
            color: var(--text);
            transition: transform 0.35s ease, box-shadow 0.35s ease, border-color 0.35s ease;
            box-shadow: 0 14px 30px rgba(0,0,0,0.2);
            animation: fadeUp 0.8s ease;
        }

        .card::before {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(99,102,241,0.14), rgba(139,92,246,0.08));
            opacity: 0;
            transition: opacity 0.35s ease;
        }

        .card::after {
            content: "";
            position: absolute;
            top: -40%;
            right: -20%;
            width: 180px;
            height: 180px;
            background: radial-gradient(circle, rgba(255,255,255,0.12), transparent 65%);
            transform: rotate(20deg);
        }

        .card:hover {
            transform: translateY(-8px) scale(1.01);
            border-color: rgba(255,255,255,0.22);
            box-shadow: 0 24px 44px rgba(0,0,0,0.28);
            color: var(--text);
        }

        .card:hover::before {
            opacity: 1;
        }

        .card-content {
            position: relative;
            z-index: 1;
        }

        .card-icon {
            width: 54px;
            height: 54px;
            display: grid;
            place-items: center;
            border-radius: 16px;
            margin-bottom: 16px;
            font-size: 1.4rem;
            font-weight: 800;
            color: white;
            background: linear-gradient(135deg, var(--primary), var(--primary-2));
            box-shadow: 0 12px 24px rgba(99,102,241,0.28);
        }

        .card-title {
            display: block;
            font-size: 1.18rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .card-desc {
            display: block;
            font-size: 0.96rem;
            font-weight: 400;
            color: var(--muted);
            line-height: 1.65;
        }

        .card-arrow {
            margin-top: 18px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #c7d2fe;
            font-weight: 600;
            font-size: 0.95rem;
            transition: transform 0.3s ease;
        }

        .card:hover .card-arrow {
            transform: translateX(6px);
        }

        .footer-bar {
            margin-top: 34px;
            padding-top: 22px;
            border-top: 1px solid rgba(255,255,255,0.10);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }

        .footer-note {
            color: var(--muted);
            font-size: 0.94rem;
        }

        .logout-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 12px 20px;
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            text-decoration: none;
            border-radius: 14px;
            font-weight: 700;
            box-shadow: 0 12px 24px rgba(239,68,68,0.26);
            transition: 0.25s ease;
        }

        .logout-btn:hover {
            color: white;
            transform: translateY(-2px);
        }

        .role-warning {
            background: rgba(245,158,11,0.16);
            border: 1px solid rgba(245,158,11,0.32);
            color: #fde68a;
            border-radius: 18px;
            padding: 18px;
            line-height: 1.65;
        }

        @media (max-width: 900px) {
            .hero-panel {
                grid-template-columns: 1fr;
            }

            .action-cards {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 640px) {
            .dashboard-container {
                padding: 22px;
                border-radius: 22px;
            }

            .heading h1 {
                font-size: 2rem;
            }

            .welcome-text {
                font-size: 0.96rem;
            }

            .card {
                padding: 20px;
            }
        }
    </style>
</head>

<body>

<div class="page">
    <div class="dashboard-container">

        <div class="topbar">
            <div class="heading">
                <h1>
                    Welcome, <?= html_escape($fullName); ?>!
                </h1>

                <p class="welcome-text">
                    This is your central dashboard. It shows only the modules available for your account role,
                    helping you navigate the platform without broken or forbidden links.
                </p>
            </div>

            <div class="badge">
                <?= html_escape(ucfirst($role)); ?> Portal
            </div>
        </div>

        <div class="hero-panel">
            <div class="hero-card">
                <h2>Your Control Center</h2>

                <?php if ($isAlumnus): ?>
                    <p>
                        Manage your alumni profile, participate in blind bidding, check your bid status,
                        view your bidding history, and see the current Alumni of the Day.
                    </p>
                <?php elseif ($isStaff): ?>
                    <p>
                        Access the University Analytics Dashboard, view filtered alumni data, generate reports,
                        manage scoped API keys, review usage logs, and open API documentation.
                    </p>
                <?php else: ?>
                    <p>
                        Your account role is not fully configured. Please contact an administrator if you cannot
                        access the correct dashboard tools.
                    </p>
                <?php endif; ?>

                <div class="quick-stats">
                    <?php if ($isAlumnus): ?>
                        <div class="stat-chip">
                            <strong>Profile</strong>
                            <span>Alumni data</span>
                        </div>

                        <div class="stat-chip">
                            <strong>Bidding</strong>
                            <span>Featured slot</span>
                        </div>

                        <div class="stat-chip">
                            <strong>Status</strong>
                            <span>Winning/Losing</span>
                        </div>
                    <?php elseif ($isStaff): ?>
                        <div class="stat-chip">
                            <strong>Analytics</strong>
                            <span>Charts & trends</span>
                        </div>

                        <div class="stat-chip">
                            <strong>Reports</strong>
                            <span>CSV/PDF exports</span>
                        </div>

                        <div class="stat-chip">
                            <strong>API</strong>
                            <span>Keys & logs</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="info-card">
                <h3>Quick Access</h3>
                <p>
                    Use the cards below to open each module. Alumni and staff users see different options
                    based on role-based access control.
                </p>
            </div>
        </div>

        <?php if (!$isAlumnus && !$isStaff): ?>
            <div class="role-warning">
                No dashboard modules are available for your current role:
                <strong><?= html_escape($role); ?></strong>.
                Please ask an administrator to assign a valid role.
            </div>
        <?php endif; ?>

        <div class="action-cards">

            <?php if ($isAlumnus): ?>
                <a href="<?= site_url('profile'); ?>" class="card">
                    <div class="card-content">
                        <div class="card-icon">👤</div>
                        <span class="card-title">Manage Profile</span>
                        <span class="card-desc">
                            Update your biography, LinkedIn URL, profile image, degrees, certifications,
                            licences, courses, and employment history.
                        </span>
                        <span class="card-arrow">Open Profile →</span>
                    </div>
                </a>

                <a href="<?= site_url('bidding'); ?>" class="card">
                    <div class="card-content">
                        <div class="card-icon">🏆</div>
                        <span class="card-title">Bidding System</span>
                        <span class="card-desc">
                            Place or increase a blind bid for tomorrow’s Alumni of the Day featured slot.
                        </span>
                        <span class="card-arrow">Open Bidding →</span>
                    </div>
                </a>

                <a href="<?= site_url('bidding/my-status'); ?>" class="card">
                    <div class="card-content">
                        <div class="card-icon">📍</div>
                        <span class="card-title">My Bid Status</span>
                        <span class="card-desc">
                            Check whether your active bid is currently winning or losing without seeing other bid amounts.
                        </span>
                        <span class="card-arrow">Check Status →</span>
                    </div>
                </a>

                <a href="<?= site_url('bidding/history'); ?>" class="card">
                    <div class="card-content">
                        <div class="card-icon">📜</div>
                        <span class="card-title">Bidding History</span>
                        <span class="card-desc">
                            Review your previous bid records, target dates, amounts, and final results.
                        </span>
                        <span class="card-arrow">View History →</span>
                    </div>
                </a>

                <a href="<?= site_url('featured-today'); ?>" class="card">
                    <div class="card-content">
                        <div class="card-icon">⭐</div>
                        <span class="card-title">Featured Today</span>
                        <span class="card-desc">
                            View the current Alumni of the Day profile that is visible to students and clients.
                        </span>
                        <span class="card-arrow">View Featured →</span>
                    </div>
                </a>
            <?php endif; ?>

            <?php if ($isStaff): ?>
                <a href="<?= site_url('analytics/dashboard'); ?>" class="card">
                    <div class="card-content">
                        <div class="card-icon">📊</div>
                        <span class="card-title">Analytics Dashboard</span>
                        <span class="card-desc">
                            View charts, skills gap signals, alumni outcomes, trends, filters, and visual insights.
                        </span>
                        <span class="card-arrow">Open Analytics →</span>
                    </div>
                </a>

                <a href="<?= site_url('analytics/alumni'); ?>" class="card">
                    <div class="card-content">
                        <div class="card-icon">🎓</div>
                        <span class="card-title">View Alumni</span>
                        <span class="card-desc">
                            Browse and filter alumni records by programme, graduation year, and industry sector.
                        </span>
                        <span class="card-arrow">View Alumni →</span>
                    </div>
                </a>

                <a href="<?= site_url('analytics/reports'); ?>" class="card">
                    <div class="card-content">
                        <div class="card-icon">📝</div>
                        <span class="card-title">Reports</span>
                        <span class="card-desc">
                            Generate curriculum intelligence reports and export alumni or summary data.
                        </span>
                        <span class="card-arrow">Open Reports →</span>
                    </div>
                </a>

                <a href="<?= site_url('developer'); ?>" class="card">
                    <div class="card-content">
                        <div class="card-icon">🔑</div>
                        <span class="card-title">API Keys & Logs</span>
                        <span class="card-desc">
                            Generate scoped API keys for Analytics Dashboard or Mobile AR App and review usage logs.
                        </span>
                        <span class="card-arrow">Manage API →</span>
                    </div>
                </a>

                <a href="<?= site_url('api-docs'); ?>" class="card" target="_blank" rel="noopener noreferrer">
                    <div class="card-content">
                        <div class="card-icon">📘</div>
                        <span class="card-title">API Documentation</span>
                        <span class="card-desc">
                            Open API documentation and review how external clients connect using bearer tokens.
                        </span>
                        <span class="card-arrow">View Docs →</span>
                    </div>
                </a>
            <?php endif; ?>

        </div>

        <div class="footer-bar">
            <div class="footer-note">
                Logged in as <?= html_escape($role); ?>.
            </div>

            <a href="<?= site_url('logout'); ?>" class="logout-btn">Log Out</a>
        </div>

    </div>
</div>

</body>
</html>