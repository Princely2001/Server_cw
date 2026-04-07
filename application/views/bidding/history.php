<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= html_escape($title); ?></title>
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
            background: linear-gradient(135deg, #0f172a, #1e293b, #334155);
            background-size: 300% 300%;
            animation: bgShift 12s ease infinite;
            padding: 40px 15px;
            color: #fff;
        }

        @keyframes bgShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .page-header {
            animation: fadeDown 0.8s ease;
        }

        @keyframes fadeDown {
            from {
                opacity: 0;
                transform: translateY(-25px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-radius: 22px;
            overflow: hidden;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.35);
            animation: fadeUp 0.9s ease;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(35px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .page-title {
            font-size: 2rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            color: #ffffff;
        }

        .subtitle {
            color: rgba(255,255,255,0.75);
            font-size: 0.95rem;
            margin-top: 4px;
        }

        .modern-btn {
            border: none;
            padding: 12px 22px;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-block;
            background: linear-gradient(135deg, #3b82f6, #6366f1);
            color: #fff;
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.35);
        }

        .modern-btn:hover {
            transform: translateY(-2px) scale(1.02);
            color: #fff;
            box-shadow: 0 14px 30px rgba(99, 102, 241, 0.45);
        }

        .table-wrap {
            padding: 0;
        }

        .modern-table {
            margin: 0;
            color: #fff;
        }

        .modern-table thead {
            background: linear-gradient(135deg, rgba(59,130,246,0.35), rgba(99,102,241,0.35));
        }

        .modern-table thead th {
            border: none;
            padding: 18px 16px;
            font-size: 0.95rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: #f8fafc;
        }

        .modern-table tbody tr {
            transition: all 0.3s ease;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }

        .modern-table tbody tr:hover {
            background: rgba(255, 255, 255, 0.08);
            transform: scale(1.005);
        }

        .modern-table tbody td {
            padding: 18px 14px;
            vertical-align: middle;
            border: none;
        }

        .feature-date {
            font-weight: 700;
            color: #93c5fd;
            font-size: 1rem;
        }

        .bid-amount {
            font-weight: 700;
            font-size: 1.05rem;
            color: #f8fafc;
        }

        .updated-time {
            color: rgba(255,255,255,0.7);
            font-size: 0.88rem;
        }

        .status-badge {
            display: inline-block;
            padding: 9px 16px;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            animation: pulseBadge 2.4s infinite;
            box-shadow: 0 4px 14px rgba(0,0,0,0.15);
        }

        @keyframes pulseBadge {
            0% { transform: scale(1); }
            50% { transform: scale(1.04); }
            100% { transform: scale(1); }
        }

        .status-won {
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: #fff;
        }

        .status-lost {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: #fff;
        }

        .status-pending {
            background: linear-gradient(135deg, #facc15, #eab308);
            color: #1f2937;
        }

        .status-cancelled {
            background: linear-gradient(135deg, #94a3b8, #64748b);
            color: #fff;
        }

        .empty-state {
            padding: 70px 30px;
            text-align: center;
            color: rgba(255,255,255,0.85);
        }

        .empty-state h4 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 12px;
            color: #fff;
        }

        .empty-state p {
            max-width: 500px;
            margin: 0 auto 20px;
            color: rgba(255,255,255,0.7);
        }

        .footer-note {
            text-align: center;
            margin-top: 18px;
            color: rgba(255,255,255,0.7);
            font-size: 0.9rem;
            animation: fadeUp 1.1s ease;
        }

        .header-box {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }

        .table-responsive {
            border-radius: 0 0 22px 22px;
            overflow: hidden;
        }

        @media (max-width: 768px) {
            .page-title {
                font-size: 1.6rem;
            }

            .modern-btn {
                width: 100%;
                text-align: center;
            }

            .header-box {
                flex-direction: column;
                align-items: stretch;
            }

            .modern-table thead th,
            .modern-table tbody td {
                font-size: 0.88rem;
                padding: 14px 10px;
            }

            .status-badge {
                padding: 7px 12px;
                font-size: 0.75rem;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-11 col-xl-10">

            <div class="header-box page-header">
                <div>
                    <h2 class="page-title"><?= html_escape($title); ?></h2>
                    <div class="subtitle">Track your bidding activity with a modern dashboard experience</div>
                </div>
                <a href="<?= site_url('bidding'); ?>" class="modern-btn">&larr; Back to Bidding</a>
            </div>

            <div class="glass-card">
                <div class="card-body p-0">
                    <?php if (empty($history)): ?>
                        <div class="empty-state">
                            <h4>No bids found</h4>
                            <p>You haven't placed any bids yet. Start bidding now and your history will appear here beautifully.</p>
                            <a href="<?= site_url('bidding'); ?>" class="modern-btn mt-2">Place a Bid</a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive table-wrap">
                            <table class="table modern-table text-center align-middle">
                                <thead>
                                    <tr>
                                        <th>Target Feature Date</th>
                                        <th>Bid Amount</th>
                                        <th>Status</th>
                                        <th>Last Updated</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($history as $bid): ?>

                                        <?php 
                                            $badge_class = 'status-pending';
                                            if ($bid->status === 'won') $badge_class = 'status-won';
                                            if ($bid->status === 'lost') $badge_class = 'status-lost';
                                            if ($bid->status === 'cancelled') $badge_class = 'status-cancelled';
                                        ?>

                                        <tr>
                                            <td class="feature-date">
                                                <?= date('F j, Y', strtotime($bid->target_date)); ?>
                                            </td>
                                            <td class="bid-amount">
                                                £<?= number_format($bid->bid_amount, 2); ?>
                                            </td>
                                            <td>
                                                <span class="status-badge <?= $badge_class; ?>">
                                                    <?= ucfirst(html_escape($bid->status)); ?>
                                                </span>
                                            </td>
                                            <td class="updated-time">
                                                <?= date('M j, Y h:i A', strtotime($bid->updated_at)); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="footer-note">
                Timezone: <?= html_escape($app_timezone); ?> |
                Current Server Time: <?= html_escape($current_time_lk); ?>
            </div>

        </div>
    </div>
</div>

</body>
</html>