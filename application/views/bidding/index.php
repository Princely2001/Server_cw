<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= html_escape($title) ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg-1: #0b1220;
            --bg-2: #111827;
            --glass: rgba(255,255,255,0.08);
            --border: rgba(255,255,255,0.14);
            --text: #f8fafc;
            --muted: #94a3b8;
            --primary: #6366f1;
            --primary-2: #8b5cf6;
            --success: #22c55e;
            --danger: #ef4444;
            --shadow: 0 24px 60px rgba(0,0,0,0.35);
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at top left, rgba(99,102,241,0.22), transparent 30%),
                radial-gradient(circle at bottom right, rgba(139,92,246,0.18), transparent 30%),
                linear-gradient(135deg, var(--bg-1), var(--bg-2));
        }

        .page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px 16px;
        }

        .container {
            width: 100%;
            max-width: 760px;
            background: var(--glass);
            border: 1px solid var(--border);
            backdrop-filter: blur(18px);
            border-radius: 28px;
            padding: 28px;
            box-shadow: var(--shadow);
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
            margin: 0;
            font-size: clamp(1.9rem, 4vw, 2.7rem);
            font-weight: 800;
        }

        .back-link {
            text-decoration: none;
            color: white;
            background: linear-gradient(135deg, var(--primary), var(--primary-2));
            padding: 12px 16px;
            border-radius: 14px;
            font-weight: 700;
        }

        .hero, .limit-info, .bid-panel, .status-box {
            border-radius: 22px;
            padding: 22px;
            margin-bottom: 20px;
        }

        .hero {
            background: linear-gradient(135deg, rgba(99,102,241,0.16), rgba(139,92,246,0.10));
            border: 1px solid rgba(255,255,255,0.10);
        }

        .hero-label {
            display: inline-block;
            padding: 8px 12px;
            border-radius: 999px;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.10);
            color: #cbd5e1;
            font-size: 0.82rem;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 12px;
        }

        .target-date {
            display: block;
            margin-top: 10px;
            font-size: 1.25rem;
            font-weight: 800;
            color: #fff;
        }

        .alert {
            padding: 14px 16px;
            border-radius: 16px;
            margin-bottom: 16px;
            border: 1px solid transparent;
        }

        .alert-success {
            background: rgba(34,197,94,0.14);
            color: #bbf7d0;
            border-color: rgba(34,197,94,0.25);
        }

        .alert-danger {
            background: rgba(239,68,68,0.14);
            color: #fecaca;
            border-color: rgba(239,68,68,0.25);
        }

        .limit-info {
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.08);
            text-align: center;
            line-height: 1.8;
        }

        .limit-value {
            font-size: 1.3rem;
            font-weight: 800;
            color: white;
        }

        .winning {
            background: rgba(34,197,94,0.14);
            color: #bbf7d0;
            border: 1px solid rgba(34,197,94,0.28);
        }

        .losing {
            background: rgba(239,68,68,0.14);
            color: #fecaca;
            border: 1px solid rgba(239,68,68,0.25);
        }

        .neutral {
            background: rgba(148,163,184,0.14);
            color: #e2e8f0;
            border: 1px solid rgba(148,163,184,0.25);
        }

        .resolved-won {
            background: rgba(34,197,94,0.18);
            color: #dcfce7;
            border: 1px solid rgba(34,197,94,0.28);
        }

        .resolved-lost {
            background: rgba(239,68,68,0.18);
            color: #fee2e2;
            border: 1px solid rgba(239,68,68,0.28);
        }

        .status-box small {
            display: block;
            margin-top: 8px;
            font-size: 0.88rem;
        }

        .bid-panel {
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.08);
        }

        label {
            display: block;
            margin-bottom: 10px;
            font-weight: 700;
        }

        input[type="number"] {
            width: 100%;
            padding: 15px 16px;
            border-radius: 16px;
            border: 1px solid rgba(255,255,255,0.10);
            background: rgba(255,255,255,0.06);
            color: white;
            font-size: 1rem;
            margin-bottom: 14px;
        }

        .help-text {
            margin: -2px 0 16px;
            font-size: 0.88rem;
            color: var(--muted);
            line-height: 1.6;
        }

        .btn {
            width: 100%;
            border: none;
            border-radius: 16px;
            padding: 14px 18px;
            font-size: 1rem;
            font-weight: 800;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-primary {
            color: white;
            background: linear-gradient(135deg, var(--primary), var(--primary-2));
        }

        .remaining-good {
            color: #86efac;
            font-weight: 700;
        }

        .remaining-bad {
            color: #fca5a5;
            font-weight: 700;
        }

        .featured-link {
            margin-top: 14px;
            display: inline-block;
            color: #c7d2fe;
            text-decoration: underline;
        }

        @media (max-width: 640px) {
            .container { padding: 20px; }
            .back-link { width: 100%; text-align: center; }
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="container">
            <div class="topbar">
                <h1 class="page-title">Sponsorship Bidding</h1>
                <a href="<?= site_url('auth/dashboard') ?>" class="back-link">← Back to Dashboard</a>
            </div>

            <div class="hero">
                <span class="hero-label">Blind Bidding</span>
                <p>
                    Bid to be featured as the <strong>Alumni of the Day</strong> for:
                    <span class="target-date"><?= date('l, jS F Y', strtotime($target_date)) ?></span>
                </p>
                <a class="featured-link" href="<?= site_url('bidding/featured_today') ?>">View today's featured alumnus</a>
            </div>

            <?php if ($this->session->flashdata('success')): ?>
                <div class="alert alert-success"><?= html_escape($this->session->flashdata('success')) ?></div>
            <?php endif; ?>

            <?php if ($this->session->flashdata('error')): ?>
                <div class="alert alert-danger"><?= html_escape($this->session->flashdata('error')) ?></div>
            <?php endif; ?>

            <div class="limit-info">
                You have won the featured slot
                <span class="limit-value"><?= (int)$monthly_wins ?>/<?= (int)$max_allowed_wins ?></span>
                times this month.<br>
                <span class="<?= $remaining_slots > 0 ? 'remaining-good' : 'remaining-bad' ?>">
                    <?= (int)$remaining_slots ?> slot(s) remaining
                </span>
            </div>

            <?php
                $status_class = 'neutral';

                if ($current_status === 'Winning!') {
                    $status_class = 'winning';
                } elseif ($current_status === 'Losing') {
                    $status_class = 'losing';
                } elseif ($current_status === 'Won') {
                    $status_class = 'resolved-won';
                } elseif ($current_status === 'Lost') {
                    $status_class = 'resolved-lost';
                }
            ?>

            <div class="status-box <?= $status_class ?>">
                Current Status: <strong><?= html_escape($current_status) ?></strong>
                <?php if ($my_bid): ?>
                    <small>Your current bid: £<?= number_format((float)$my_bid->bid_amount, 2) ?></small>
                <?php else: ?>
                    <small>You have not placed a bid for this round yet.</small>
                <?php endif; ?>
            </div>

            <?php if ($limit_reached): ?>
                <div class="alert alert-danger" style="text-align:center;">
                    <strong>Monthly Limit Reached!</strong><br>
                    You cannot place any more bids until next month.
                </div>
            <?php elseif ($my_bid && in_array($my_bid->status, ['won', 'lost'], true)): ?>
                <div class="alert alert-success" style="text-align:center;">
                    This bidding round has already been resolved. You cannot modify this bid anymore.
                </div>
            <?php else: ?>

                <div class="bid-panel">
                    <?= form_open('bidding/submit_bid') ?>
                        <label for="bid_amount">
                            <?= $my_bid ? 'Increase Your Bid Amount (£)' : 'Place Your Bid Amount (£)' ?>
                        </label>

                        <input
                            type="number"
                            name="bid_amount"
                            id="bid_amount"
                            step="0.01"
                            min="<?= $my_bid ? number_format(((float)$my_bid->bid_amount + 0.01), 2, '.', '') : '1.00' ?>"
                            placeholder="e.g. 50.00"
                            required
                        >

                        <?php if ($my_bid): ?>
                            <p class="help-text">
                                You may only increase your bid. Reducing or cancelling bids is disabled to preserve blind bidding fairness.
                            </p>
                        <?php else: ?>
                            <p class="help-text">
                                The system will not reveal the current highest bid. It will only show whether your bid is currently winning or losing.
                            </p>
                        <?php endif; ?>

                        <button type="submit" class="btn btn-primary">
                            <?= $my_bid ? 'Update Bid' : 'Place Bid' ?>
                        </button>
                    <?= form_close() ?>
                </div>

            <?php endif; ?>
        </div>
    </div>
</body>
</html>