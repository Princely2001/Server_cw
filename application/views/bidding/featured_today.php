<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= html_escape($title) ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg-1: #07111f;
            --bg-2: #0f172a;
            --bg-3: #111827;
            --glass: rgba(255, 255, 255, 0.08);
            --border: rgba(255, 255, 255, 0.16);
            --text: #f8fafc;
            --muted: #cbd5e1;
            --soft: #94a3b8;
            --primary: #6366f1;
            --primary-2: #8b5cf6;
            --cyan: #06b6d4;
            --shadow: 0 20px 60px rgba(0, 0, 0, 0.35);
            --radius-xl: 30px;
        }

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            color: var(--text);
            min-height: 100vh;
            background:
                radial-gradient(circle at 10% 20%, rgba(99,102,241,0.22), transparent 25%),
                radial-gradient(circle at 90% 10%, rgba(6,182,212,0.18), transparent 24%),
                radial-gradient(circle at 80% 90%, rgba(139,92,246,0.18), transparent 26%),
                linear-gradient(135deg, var(--bg-1), var(--bg-2), var(--bg-3));
            overflow-x: hidden;
            position: relative;
        }

        body::before,
        body::after {
            content: "";
            position: fixed;
            width: 420px;
            height: 420px;
            border-radius: 50%;
            filter: blur(80px);
            z-index: 0;
            opacity: 0.25;
            pointer-events: none;
            animation: floatGlow 10s ease-in-out infinite;
        }

        body::before {
            top: -120px;
            left: -100px;
            background: #6366f1;
        }

        body::after {
            bottom: -140px;
            right: -100px;
            background: #06b6d4;
            animation-delay: 2s;
        }

        @keyframes floatGlow {
            0%, 100% { transform: translateY(0) translateX(0) scale(1); }
            50% { transform: translateY(20px) translateX(10px) scale(1.08); }
        }

        .page {
            position: relative;
            z-index: 1;
            padding: 36px 16px 80px;
        }

        .container {
            max-width: 1180px;
            margin: 0 auto;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
            margin-bottom: 24px;
            animation: fadeUp 0.7s ease both;
        }

        .top-link {
            text-decoration: none;
            color: white;
            font-weight: 700;
            padding: 12px 18px;
            border-radius: 14px;
            background: linear-gradient(135deg, var(--primary), var(--primary-2));
            box-shadow: 0 10px 30px rgba(99,102,241,0.35);
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }

        .top-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 18px 40px rgba(99,102,241,0.42);
        }

        .page-title {
            margin: 0;
            font-size: clamp(2rem, 4vw, 3.4rem);
            font-weight: 900;
            letter-spacing: -0.03em;
            line-height: 1.05;
        }

        .page-subtitle {
            margin: 10px 0 0;
            color: var(--muted);
            font-size: 1rem;
            max-width: 720px;
            line-height: 1.7;
        }

        .hero-card {
            position: relative;
            overflow: hidden;
            border-radius: var(--radius-xl);
            border: 1px solid var(--border);
            background: linear-gradient(135deg, rgba(99,102,241,0.14), rgba(139,92,246,0.10), rgba(255,255,255,0.06));
            backdrop-filter: blur(18px);
            box-shadow: var(--shadow);
            padding: 30px;
            animation: fadeUp 0.9s ease both;
        }

        .hero-card::before {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(120deg, transparent 0%, rgba(255,255,255,0.06) 40%, transparent 75%);
            transform: translateX(-100%);
            animation: shine 5s linear infinite;
            pointer-events: none;
        }

        @keyframes shine {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(120%); }
        }

        .hero-grid {
            display: grid;
            grid-template-columns: 220px 1fr;
            gap: 28px;
            align-items: center;
        }

        .profile-wrap {
            position: relative;
            width: 220px;
            height: 220px;
            margin: 0 auto;
        }

        .profile-wrap::before {
            content: "";
            position: absolute;
            inset: -10px;
            border-radius: 28px;
            background: linear-gradient(135deg, var(--primary), var(--cyan), var(--primary-2));
            opacity: 0.85;
            filter: blur(14px);
            z-index: 0;
            animation: pulseGlow 3s ease-in-out infinite;
        }

        @keyframes pulseGlow {
            0%, 100% { transform: scale(1); opacity: 0.72; }
            50% { transform: scale(1.03); opacity: 0.95; }
        }

        .profile-img,
        .profile-placeholder {
            position: relative;
            z-index: 1;
            width: 220px;
            height: 220px;
            object-fit: cover;
            border-radius: 28px;
            border: 2px solid rgba(255,255,255,0.16);
            background: rgba(255,255,255,0.08);
            box-shadow: 0 18px 40px rgba(0,0,0,0.28);
        }

        .profile-placeholder {
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--muted);
            font-weight: 700;
            font-size: 1rem;
        }

        .hero-content {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .badge-row {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            border-radius: 999px;
            font-size: 0.86rem;
            font-weight: 800;
            border: 1px solid rgba(255,255,255,0.10);
            background: rgba(255,255,255,0.08);
            color: #e2e8f0;
        }

        .badge.primary {
            background: linear-gradient(135deg, rgba(99,102,241,0.24), rgba(139,92,246,0.24));
            color: white;
        }

        .winner-name {
            margin: 0;
            font-size: clamp(2rem, 4vw, 3rem);
            font-weight: 900;
            line-height: 1.05;
            letter-spacing: -0.03em;
        }

        .winner-meta {
            display: grid;
            gap: 10px;
            margin-top: 2px;
        }

        .meta-pill {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            width: fit-content;
            max-width: 100%;
            padding: 12px 16px;
            border-radius: 16px;
            background: rgba(255,255,255,0.07);
            border: 1px solid rgba(255,255,255,0.08);
            color: var(--muted);
            font-size: 0.95rem;
            word-break: break-word;
        }

        .action-row {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            margin-top: 10px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 14px 18px;
            border-radius: 16px;
            text-decoration: none;
            font-weight: 800;
            transition: transform 0.25s ease, box-shadow 0.25s ease, background 0.25s ease;
        }

        .btn-primary {
            color: white;
            background: linear-gradient(135deg, var(--primary), var(--primary-2));
            box-shadow: 0 14px 30px rgba(99,102,241,0.28);
        }

        .btn-secondary {
            color: #e2e8f0;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.10);
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .content-grid {
            display: grid;
            gap: 22px;
            margin-top: 26px;
        }

        .section-card {
            background: var(--glass);
            border: 1px solid var(--border);
            border-radius: 26px;
            backdrop-filter: blur(16px);
            box-shadow: var(--shadow);
            padding: 24px;
            animation: fadeUp 0.8s ease both;
        }

        .section-card:nth-child(2) { animation-delay: 0.08s; }
        .section-card:nth-child(3) { animation-delay: 0.14s; }
        .section-card:nth-child(4) { animation-delay: 0.2s; }
        .section-card:nth-child(5) { animation-delay: 0.26s; }
        .section-card:nth-child(6) { animation-delay: 0.32s; }

        .section-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 14px;
            flex-wrap: wrap;
            margin-bottom: 18px;
        }

        .section-title {
            margin: 0;
            font-size: 1.35rem;
            font-weight: 800;
            letter-spacing: -0.02em;
        }

        .section-count {
            padding: 8px 12px;
            border-radius: 999px;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.08);
            color: var(--soft);
            font-size: 0.82rem;
            font-weight: 800;
        }

        .bio-box {
            font-size: 1rem;
            line-height: 1.85;
            color: #e2e8f0;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: 18px;
            padding: 18px;
        }

        .card-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 16px;
        }

        .item {
            position: relative;
            overflow: hidden;
            background: linear-gradient(180deg, rgba(255,255,255,0.07), rgba(255,255,255,0.04));
            border: 1px solid rgba(255,255,255,0.09);
            border-radius: 20px;
            padding: 18px;
            transition: transform 0.28s ease, box-shadow 0.28s ease, border-color 0.28s ease;
        }

        .item::before {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(99,102,241,0.10), transparent 45%, rgba(6,182,212,0.08));
            opacity: 0;
            transition: opacity 0.28s ease;
            pointer-events: none;
        }

        .item:hover {
            transform: translateY(-6px);
            box-shadow: 0 18px 35px rgba(0,0,0,0.20);
            border-color: rgba(255,255,255,0.18);
        }

        .item:hover::before {
            opacity: 1;
        }

        .item strong {
            display: block;
            margin-bottom: 10px;
            color: white;
            font-size: 1.04rem;
            font-weight: 800;
            line-height: 1.4;
        }

        .item div {
            color: var(--muted);
            line-height: 1.65;
            font-size: 0.95rem;
            margin-bottom: 6px;
            word-break: break-word;
        }

        .item a {
            color: #93c5fd;
            text-decoration: none;
            font-weight: 600;
        }

        .item a:hover {
            text-decoration: underline;
        }

        .empty {
            margin: 0;
            padding: 18px;
            border-radius: 18px;
            background: rgba(255,255,255,0.05);
            border: 1px dashed rgba(255,255,255,0.12);
            color: var(--soft);
            font-style: italic;
        }

        .empty-state {
            text-align: center;
            padding: 60px 24px;
            border-radius: 28px;
            border: 1px solid var(--border);
            background: var(--glass);
            backdrop-filter: blur(16px);
            box-shadow: var(--shadow);
            animation: fadeUp 0.8s ease both;
        }

        .empty-state h2 {
            margin: 0 0 12px;
            font-size: 2rem;
            font-weight: 800;
        }

        .empty-state p {
            margin: 0;
            color: var(--muted);
            line-height: 1.8;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(28px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 900px) {
            .hero-grid {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .hero-content {
                align-items: center;
            }

            .winner-meta {
                justify-items: center;
            }

            .action-row {
                justify-content: center;
            }

            .badge-row {
                justify-content: center;
            }
        }

        @media (max-width: 640px) {
            .page {
                padding: 24px 12px 56px;
            }

            .hero-card,
            .section-card {
                padding: 20px;
            }

            .profile-wrap,
            .profile-img,
            .profile-placeholder {
                width: 170px;
                height: 170px;
            }

            .page-title {
                font-size: 2rem;
            }

            .winner-name {
                font-size: 2rem;
            }

            .card-list {
                grid-template-columns: 1fr;
            }

            .meta-pill {
                width: 100%;
                justify-content: center;
            }

            .btn {
                width: 100%;
            }

            .action-row {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="container">

            <div class="topbar">
                <div>
                    <h1 class="page-title">Alumni of the Day</h1>
                    <p class="page-subtitle">
                        Discover today’s featured alumnus, explore their achievements, and showcase an inspiring professional journey built through degrees, certifications, licences, courses, and employment history.
                    </p>
                </div>

                <a class="top-link" href="<?= site_url('bidding') ?>">← Back to Bidding</a>
            </div>

            <?php if (!$featured): ?>
                <div class="empty-state">
                    <h2>No featured alumnus yet</h2>
                    <p>
                        A winner has not been selected for <strong><?= date('l, jS F Y', strtotime($today)) ?></strong> yet.
                        Please check again after the bidding round is resolved.
                    </p>
                </div>
            <?php else: ?>
                <?php $profile = $featured['profile']; ?>
                <?php $profile_img_url = !empty($profile->profile_image) ? base_url('uploads/profile_images/' . rawurlencode($profile->profile_image)) : ''; ?>

                <div class="hero-card">
                    <div class="hero-grid">
                        <div class="profile-wrap">
                            <?php if (!empty($profile->profile_image)): ?>
                                <img
                                    src="<?= html_escape($profile_img_url) ?>"
                                    alt="Profile Image"
                                    class="profile-img"
                                    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                                >
                                <div class="profile-placeholder" style="display:none;">No Image</div>
                            <?php else: ?>
                                <div class="profile-placeholder">No Image</div>
                            <?php endif; ?>
                        </div>

                        <div class="hero-content">
                            <div class="badge-row">
                                <div class="badge primary">Featured Winner</div>
                                <div class="badge"><?= date('l, jS F Y', strtotime($today)) ?></div>
                            </div>

                            <h2 class="winner-name"><?= html_escape($profile->first_name . ' ' . $profile->last_name) ?></h2>

                            <div class="winner-meta">
                                <div class="meta-pill">
                                    University Email: <?= html_escape($profile->university_email) ?>
                                </div>
                                <div class="meta-pill">
                                    Featured for: <?= date('l, jS F Y', strtotime($profile->featured_for_date)) ?>
                                </div>
                            </div>

                            <div class="action-row">
                                <?php if (!empty($profile->linkedin_url)): ?>
                                    <a
                                        class="btn btn-primary"
                                        href="<?= html_escape($profile->linkedin_url) ?>"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                    >
                                        View LinkedIn Profile
                                    </a>
                                <?php endif; ?>

                                <a class="btn btn-secondary" href="<?= site_url('featured-today') ?>">
                                    Refresh Profile
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="content-grid">
                    <div class="section-card">
                        <div class="section-head">
                            <h3 class="section-title">Biography</h3>
                            <span class="section-count">Profile Overview</span>
                        </div>

                        <div class="bio-box">
                            <?= !empty($profile->bio) ? nl2br(html_escape($profile->bio)) : '<span class="empty">No biography added.</span>' ?>
                        </div>
                    </div>

                    <div class="section-card">
                        <div class="section-head">
                            <h3 class="section-title">Degrees</h3>
                            <span class="section-count"><?= !empty($featured['degrees']) ? count($featured['degrees']) : 0 ?> item(s)</span>
                        </div>

                        <?php if (!empty($featured['degrees'])): ?>
                            <div class="card-list">
                                <?php foreach ($featured['degrees'] as $degree): ?>
                                    <div class="item">
                                        <strong><?= html_escape($degree->degree_name) ?></strong>
                                        <div>Completion Date: <?= html_escape($degree->completion_date) ?></div>
                                        <div>
                                            Official URL:
                                            <a href="<?= html_escape($degree->university_url) ?>" target="_blank" rel="noopener noreferrer">
                                                <?= html_escape($degree->university_url) ?>
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="empty">No degrees available.</p>
                        <?php endif; ?>
                    </div>

                    <div class="section-card">
                        <div class="section-head">
                            <h3 class="section-title">Certifications</h3>
                            <span class="section-count"><?= !empty($featured['certifications']) ? count($featured['certifications']) : 0 ?> item(s)</span>
                        </div>

                        <?php if (!empty($featured['certifications'])): ?>
                            <div class="card-list">
                                <?php foreach ($featured['certifications'] as $certification): ?>
                                    <div class="item">
                                        <strong><?= html_escape($certification->certification_name) ?></strong>
                                        <div>Completion Date: <?= html_escape($certification->completion_date) ?></div>
                                        <div>
                                            Course URL:
                                            <a href="<?= html_escape($certification->course_url) ?>" target="_blank" rel="noopener noreferrer">
                                                <?= html_escape($certification->course_url) ?>
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="empty">No certifications available.</p>
                        <?php endif; ?>
                    </div>

                    <div class="section-card">
                        <div class="section-head">
                            <h3 class="section-title">Licences</h3>
                            <span class="section-count"><?= !empty($featured['licences']) ? count($featured['licences']) : 0 ?> item(s)</span>
                        </div>

                        <?php if (!empty($featured['licences'])): ?>
                            <div class="card-list">
                                <?php foreach ($featured['licences'] as $licence): ?>
                                    <div class="item">
                                        <strong><?= html_escape($licence->licence_name) ?></strong>
                                        <div>Completion Date: <?= html_escape($licence->completion_date) ?></div>
                                        <div>
                                            Awarding Body URL:
                                            <a href="<?= html_escape($licence->awarding_body_url) ?>" target="_blank" rel="noopener noreferrer">
                                                <?= html_escape($licence->awarding_body_url) ?>
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="empty">No licences available.</p>
                        <?php endif; ?>
                    </div>

                    <div class="section-card">
                        <div class="section-head">
                            <h3 class="section-title">Short Professional Courses</h3>
                            <span class="section-count"><?= !empty($featured['courses']) ? count($featured['courses']) : 0 ?> item(s)</span>
                        </div>

                        <?php if (!empty($featured['courses'])): ?>
                            <div class="card-list">
                                <?php foreach ($featured['courses'] as $course): ?>
                                    <div class="item">
                                        <strong><?= html_escape($course->course_name) ?></strong>
                                        <div>Completion Date: <?= html_escape($course->completion_date) ?></div>
                                        <div>
                                            Course URL:
                                            <a href="<?= html_escape($course->course_url) ?>" target="_blank" rel="noopener noreferrer">
                                                <?= html_escape($course->course_url) ?>
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="empty">No short courses available.</p>
                        <?php endif; ?>
                    </div>

                    <div class="section-card">
                        <div class="section-head">
                            <h3 class="section-title">Employment History</h3>
                            <span class="section-count"><?= !empty($featured['employment']) ? count($featured['employment']) : 0 ?> item(s)</span>
                        </div>

                        <?php if (!empty($featured['employment'])): ?>
                            <div class="card-list">
                                <?php foreach ($featured['employment'] as $job): ?>
                                    <div class="item">
                                        <strong><?= html_escape($job->role) ?></strong>
                                        <div>Company: <?= html_escape($job->company_name) ?></div>
                                        <div>
                                            Period:
                                            <?= html_escape($job->start_date) ?>
                                            -
                                            <?= !empty($job->end_date) ? html_escape($job->end_date) : 'Present' ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="empty">No employment history available.</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>