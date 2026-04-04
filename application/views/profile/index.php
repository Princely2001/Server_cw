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
            --bg-1: #0f172a;
            --bg-2: #111827;
            --card: rgba(255,255,255,0.08);
            --card-border: rgba(255,255,255,0.16);
            --text: #e5e7eb;
            --muted: #94a3b8;
            --primary: #6366f1;
            --primary-2: #8b5cf6;
            --success: #22c55e;
            --danger: #ef4444;
            --warning: #f59e0b;
            --shadow: 0 20px 50px rgba(0,0,0,0.35);
            --radius: 20px;
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
            background:
                radial-gradient(circle at top left, rgba(99,102,241,0.28), transparent 30%),
                radial-gradient(circle at top right, rgba(139,92,246,0.22), transparent 28%),
                linear-gradient(135deg, var(--bg-1), var(--bg-2));
            min-height: 100vh;
            overflow-x: hidden;
        }

        body::before,
        body::after {
            content: "";
            position: fixed;
            width: 380px;
            height: 380px;
            border-radius: 50%;
            filter: blur(90px);
            z-index: 0;
            opacity: 0.28;
            animation: floatGlow 10s ease-in-out infinite alternate;
            pointer-events: none;
        }

        body::before {
            background: #6366f1;
            top: -80px;
            left: -100px;
        }

        body::after {
            background: #8b5cf6;
            right: -100px;
            bottom: -100px;
            animation-delay: 2s;
        }

        @keyframes floatGlow {
            from { transform: translateY(0px) translateX(0px) scale(1); }
            to   { transform: translateY(30px) translateX(10px) scale(1.08); }
        }

        .page {
            position: relative;
            z-index: 1;
            max-width: 1280px;
            margin: 0 auto;
            padding: 32px 20px 60px;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            margin-bottom: 28px;
            flex-wrap: wrap;
        }

        .title-wrap h1 {
            margin: 0;
            font-size: clamp(2rem, 4vw, 3rem);
            font-weight: 800;
            letter-spacing: -0.03em;
            background: linear-gradient(90deg, #ffffff, #c4b5fd 65%, #93c5fd);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .title-wrap p {
            margin: 8px 0 0;
            color: var(--muted);
            font-size: 0.98rem;
        }

        .nav-link {
            text-decoration: none;
            color: white;
            background: linear-gradient(135deg, var(--primary), var(--primary-2));
            padding: 12px 18px;
            border-radius: 14px;
            font-weight: 600;
            box-shadow: 0 10px 25px rgba(99,102,241,0.35);
            transition: 0.25s ease;
        }

        .nav-link:hover {
            transform: translateY(-2px) scale(1.02);
            box-shadow: 0 16px 32px rgba(99,102,241,0.45);
        }

        .alert {
            padding: 14px 16px;
            border-radius: 14px;
            margin-bottom: 18px;
            font-weight: 500;
            backdrop-filter: blur(14px);
            border: 1px solid rgba(255,255,255,0.12);
            animation: fadeSlideIn 0.45s ease;
        }

        .alert-success {
            background: rgba(34, 197, 94, 0.15);
            color: #bbf7d0;
            border-color: rgba(34, 197, 94, 0.28);
        }

        .alert-danger {
            background: rgba(239, 68, 68, 0.14);
            color: #fecaca;
            border-color: rgba(239, 68, 68, 0.25);
        }

        @keyframes fadeSlideIn {
            from {
                opacity: 0;
                transform: translateY(12px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            gap: 24px;
            margin-bottom: 24px;
        }

        .full-width {
            grid-column: 1 / -1;
        }

        .section {
            background: var(--card);
            border: 1px solid var(--card-border);
            border-radius: var(--radius);
            padding: 24px;
            backdrop-filter: blur(16px);
            box-shadow: var(--shadow);
            transition: transform 0.35s ease, box-shadow 0.35s ease, border-color 0.35s ease;
            animation: fadeSlideIn 0.5s ease;
        }

        .section:hover {
            transform: translateY(-4px);
            border-color: rgba(255,255,255,0.22);
            box-shadow: 0 24px 55px rgba(0,0,0,0.42);
        }

        .section h2 {
            margin: 0 0 16px;
            font-size: 1.35rem;
            font-weight: 700;
            letter-spacing: -0.02em;
        }

        .section-sub {
            margin: -6px 0 18px;
            color: var(--muted);
            font-size: 0.95rem;
        }

        .progress-card {
            overflow: hidden;
            position: relative;
        }

        .progress-card::before {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(99,102,241,0.12), rgba(139,92,246,0.1));
            pointer-events: none;
        }

        .progress-container {
            position: relative;
            width: 100%;
            height: 24px;
            background: rgba(255,255,255,0.08);
            border-radius: 999px;
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.08);
            margin: 16px 0 14px;
        }

        .progress-bar {
            height: 100%;
            width: 0;
            border-radius: 999px;
            background: linear-gradient(90deg, var(--success), #4ade80, #22c55e);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.88rem;
            font-weight: 700;
            box-shadow: inset 0 0 10px rgba(255,255,255,0.15);
            animation: fillBar 1.4s ease forwards;
            position: relative;
            overflow: hidden;
        }

        .progress-bar::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(120deg, transparent 20%, rgba(255,255,255,0.28) 50%, transparent 80%);
            animation: shine 2.5s linear infinite;
        }

        @keyframes fillBar {
            from { width: 0; }
            to { width: <?= html_escape($completion_percentage ?? 0) ?>%; }
        }

        @keyframes shine {
            from { transform: translateX(-100%); }
            to   { transform: translateX(100%); }
        }

        .profile-header {
            display: flex;
            align-items: center;
            gap: 18px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .profile-avatar {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid rgba(255,255,255,0.18);
            box-shadow: 0 12px 30px rgba(0,0,0,0.35);
        }

        .avatar-placeholder {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            font-size: 2rem;
            font-weight: 800;
            color: white;
            background: linear-gradient(135deg, var(--primary), var(--primary-2));
            box-shadow: 0 12px 30px rgba(99,102,241,0.32);
        }

        .profile-meta h3 {
            margin: 0 0 8px;
            font-size: 1.3rem;
        }

        .profile-meta p {
            margin: 0;
            color: var(--muted);
        }

        form {
            margin-top: 8px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }

        .form-grid .full {
            grid-column: 1 / -1;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-size: 0.92rem;
            font-weight: 600;
            color: #dbeafe;
        }

        input[type="text"],
        input[type="url"],
        input[type="date"],
        input[type="file"],
        textarea {
            width: 100%;
            border: 1px solid rgba(255,255,255,0.10);
            background: rgba(255,255,255,0.06);
            color: white;
            border-radius: 14px;
            padding: 14px 14px;
            outline: none;
            transition: 0.25s ease;
            font-size: 0.96rem;
        }

        input::placeholder,
        textarea::placeholder {
            color: #94a3b8;
        }

        input:focus,
        textarea:focus {
            border-color: rgba(99,102,241,0.7);
            background: rgba(255,255,255,0.09);
            box-shadow: 0 0 0 4px rgba(99,102,241,0.18);
            transform: translateY(-1px);
        }

        textarea {
            min-height: 120px;
            resize: vertical;
        }

        .btn {
            appearance: none;
            border: none;
            padding: 12px 18px;
            border-radius: 14px;
            font-weight: 700;
            font-size: 0.95rem;
            cursor: pointer;
            transition: 0.25s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-primary {
            color: white;
            background: linear-gradient(135deg, var(--primary), var(--primary-2));
            box-shadow: 0 12px 24px rgba(99,102,241,0.35);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 18px 32px rgba(99,102,241,0.44);
        }

        .btn-success {
            color: white;
            background: linear-gradient(135deg, #10b981, #22c55e);
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 26px rgba(34,197,94,0.28);
        }

        .btn-danger {
            color: white;
            background: linear-gradient(135deg, #ef4444, #dc2626);
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 26px rgba(239,68,68,0.28);
        }

        .stack {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .item-list {
            position: relative;
            padding: 18px;
            border-radius: 18px;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.08);
            transition: 0.3s ease;
            overflow: hidden;
        }

        .item-list::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background: linear-gradient(180deg, var(--primary), var(--primary-2));
            border-radius: 10px;
        }

        .item-list:hover {
            transform: translateY(-4px) scale(1.01);
            background: rgba(255,255,255,0.075);
            border-color: rgba(255,255,255,0.14);
        }

        .item-list strong {
            color: #fff;
        }

        .item-meta {
            color: var(--muted);
            margin-top: 8px;
            font-size: 0.94rem;
            line-height: 1.6;
        }

        .verify-link {
            display: inline-block;
            margin-top: 10px;
            color: #a5b4fc;
            text-decoration: none;
            font-weight: 600;
        }

        .verify-link:hover {
            color: white;
        }

        .action-links {
            margin-top: 14px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .empty-state {
            padding: 18px;
            border-radius: 16px;
            background: rgba(255,255,255,0.04);
            color: var(--muted);
            border: 1px dashed rgba(255,255,255,0.14);
        }

        .mini-title {
            margin: 24px 0 16px;
            font-size: 1.02rem;
            color: #c7d2fe;
            font-weight: 700;
        }

        .section-grid {
            display: grid;
            gap: 24px;
        }

        @media (max-width: 980px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .page {
                padding: 22px 14px 50px;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .section {
                padding: 18px;
            }

            .profile-header {
                align-items: flex-start;
            }

            .title-wrap h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>

<div class="page">
    <div class="topbar">
        <div class="title-wrap">
            <h1><?= html_escape($title) ?></h1>
            <p>Manage your academic and professional profile in one elegant place.</p>
        </div>
        <a class="nav-link" href="<?= site_url('auth/dashboard') ?>">← Back to Dashboard</a>
    </div>

    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success"><?= html_escape($this->session->flashdata('success')); ?></div>
    <?php endif; ?>

    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger"><?= $this->session->flashdata('error'); ?></div>
    <?php endif; ?>

    <div class="dashboard-grid">
        <div class="section progress-card">
            <h2>Profile Completion Status</h2>
            <p class="section-sub">Track how complete your profile is and fill the missing sections.</p>

            <div class="progress-container">
                <div class="progress-bar">
                    <?= html_escape($completion_percentage ?? 0) ?>%
                </div>
            </div>

            <?php if (($completion_percentage ?? 0) < 100): ?>
                <p class="section-sub">
                    Complete your profile to 100% by adding a bio, profile image, LinkedIn URL, and at least one entry in education, employment, and professional sections.
                </p>
            <?php else: ?>
                <p style="color:#86efac;font-weight:700;margin:0;">Your profile is 100% complete. Excellent work.</p>
            <?php endif; ?>
        </div>

        <div class="section">
            <div class="profile-header">
                <?php if (isset($profile) && !empty($profile->profile_image)): ?>
                    <img class="profile-avatar" src="<?= base_url('uploads/profile_images/' . html_escape($profile->profile_image)) ?>" alt="Profile Picture">
                <?php else: ?>
                    <div class="avatar-placeholder">
                        <?= strtoupper(substr($title ?? 'P', 0, 1)) ?>
                    </div>
                <?php endif; ?>

                <div class="profile-meta">
                    <h3>Your Profile</h3>
                    <p>Keep your personal, academic, and work details updated with a polished professional presence.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="section full-width">
        <h2>Basic Information</h2>
        <p class="section-sub">Update your profile image, biography, and LinkedIn profile.</p>

        <?= form_open_multipart('profile/update_basic') ?>
            <div class="form-grid">
                <div class="full">
                    <label>Upload New Profile Image (JPG, PNG, GIF - Max 2MB)</label>
                    <input type="file" name="profile_image" accept="image/jpeg, image/png, image/gif">
                </div>

                <div class="full">
                    <label>Biography</label>
                    <textarea name="bio" rows="4" placeholder="Write a short professional bio..."><?= isset($profile) ? html_escape($profile->bio) : '' ?></textarea>
                </div>

                <div class="full">
                    <label>LinkedIn Profile URL</label>
                    <input
                        type="url"
                        name="linkedin_url"
                        value="<?= isset($profile) ? html_escape($profile->linkedin_url) : '' ?>"
                        placeholder="https://linkedin.com/in/username"
                        pattern="https?://(www\.)?linkedin\.com/.*"
                        title="Please enter a valid LinkedIn URL (e.g., https://linkedin.com/in/yourname)">
                </div>

                <div>
                    <button type="submit" class="btn btn-primary">Save Basic Info</button>
                </div>
            </div>
        <?= form_close() ?>
    </div>

    <div class="section-grid">

        <div class="section">
            <h2>Education (Degrees)</h2>
            <p class="section-sub">Manage your completed degrees and verification links.</p>

            <div class="stack">
                <?php if(!empty($degrees)): ?>
                    <?php foreach($degrees as $deg): ?>
                        <div class="item-list">
                            <strong><?= html_escape($deg->degree_name) ?></strong>
                            <div class="item-meta">
                                Completed: <?= html_escape($deg->completion_date) ?>
                            </div>
                            <a class="verify-link" href="<?= html_escape($deg->university_url) ?>" target="_blank" rel="noopener noreferrer">Verify Degree</a>
                            <div class="action-links">
                                <a href="<?= site_url('profile/edit_degree/'.$deg->id) ?>" class="btn btn-success">Edit</a>
                                <a href="<?= site_url('profile/delete_degree/'.$deg->id) ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this degree?');">Delete</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">No degrees added yet.</div>
                <?php endif; ?>
            </div>

            <div class="mini-title">Add New Degree</div>
            <?= form_open('profile/add_degree') ?>
                <div class="form-grid">
                    <div>
                        <label>Degree Name</label>
                        <input type="text" name="degree_name" required>
                    </div>
                    <div>
                        <label>University/Course URL</label>
                        <input type="url" name="university_url" required>
                    </div>
                    <div>
                        <label>Completion Date</label>
                        <input type="date" name="completion_date" required>
                    </div>
                    <div>
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-primary">Add Degree</button>
                    </div>
                </div>
            <?= form_close() ?>
        </div>

        <div class="section">
            <h2>Professional Certifications</h2>
            <p class="section-sub">Add industry-recognized certifications and proof links.</p>

            <div class="stack">
                <?php if(!empty($certifications)): ?>
                    <?php foreach($certifications as $cert): ?>
                        <div class="item-list">
                            <strong><?= html_escape($cert->certification_name) ?></strong>
                            <div class="item-meta">
                                Completed: <?= html_escape($cert->completion_date) ?>
                            </div>
                            <a class="verify-link" href="<?= html_escape($cert->course_url) ?>" target="_blank" rel="noopener noreferrer">Verify Certification</a>
                            <div class="action-links">
                                <a href="<?= site_url('profile/edit_certification/'.$cert->id) ?>" class="btn btn-success">Edit</a>
                                <a href="<?= site_url('profile/delete_certification/'.$cert->id) ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this certification?');">Delete</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">No certifications added yet.</div>
                <?php endif; ?>
            </div>

            <div class="mini-title">Add Certification</div>
            <?= form_open('profile/add_certification') ?>
                <div class="form-grid">
                    <div>
                        <label>Certification Name</label>
                        <input type="text" name="certification_name" required>
                    </div>
                    <div>
                        <label>Course URL</label>
                        <input type="url" name="course_url" required>
                    </div>
                    <div>
                        <label>Completion Date</label>
                        <input type="date" name="completion_date" required>
                    </div>
                    <div>
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-primary">Add Certification</button>
                    </div>
                </div>
            <?= form_close() ?>
        </div>

        <div class="section">
            <h2>Professional Licences</h2>
            <p class="section-sub">Store awarded licences and their verification links.</p>

            <div class="stack">
                <?php if(!empty($licences)): ?>
                    <?php foreach($licences as $licence): ?>
                        <div class="item-list">
                            <strong><?= html_escape($licence->licence_name) ?></strong>
                            <div class="item-meta">
                                Awarded: <?= html_escape($licence->completion_date) ?>
                            </div>
                            <a class="verify-link" href="<?= html_escape($licence->awarding_body_url) ?>" target="_blank" rel="noopener noreferrer">Verify Awarding Body</a>
                            <div class="action-links">
                                <a href="<?= site_url('profile/edit_licence/'.$licence->id) ?>" class="btn btn-success">Edit</a>
                                <a href="<?= site_url('profile/delete_licence/'.$licence->id) ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this licence?');">Delete</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">No licences added yet.</div>
                <?php endif; ?>
            </div>

            <div class="mini-title">Add Licence</div>
            <?= form_open('profile/add_licence') ?>
                <div class="form-grid">
                    <div>
                        <label>Licence Name</label>
                        <input type="text" name="licence_name" required>
                    </div>
                    <div>
                        <label>Awarding Body URL</label>
                        <input type="url" name="awarding_body_url" required>
                    </div>
                    <div>
                        <label>Award Date</label>
                        <input type="date" name="completion_date" required>
                    </div>
                    <div>
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-primary">Add Licence</button>
                    </div>
                </div>
            <?= form_close() ?>
        </div>

        <div class="section">
            <h2>Short Professional Courses</h2>
            <p class="section-sub">Add short courses and direct verification sources.</p>

            <div class="stack">
                <?php if(!empty($courses)): ?>
                    <?php foreach($courses as $course): ?>
                        <div class="item-list">
                            <strong><?= html_escape($course->course_name) ?></strong>
                            <div class="item-meta">
                                Completed: <?= html_escape($course->completion_date) ?>
                            </div>
                            <a class="verify-link" href="<?= html_escape($course->course_url) ?>" target="_blank" rel="noopener noreferrer">Verify Course</a>
                            <div class="action-links">
                                <a href="<?= site_url('profile/edit_course/'.$course->id) ?>" class="btn btn-success">Edit</a>
                                <a href="<?= site_url('profile/delete_course/'.$course->id) ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this course?');">Delete</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">No short courses added yet.</div>
                <?php endif; ?>
            </div>

            <div class="mini-title">Add Short Course</div>
            <?= form_open('profile/add_course') ?>
                <div class="form-grid">
                    <div>
                        <label>Course Name</label>
                        <input type="text" name="course_name" required>
                    </div>
                    <div>
                        <label>Course URL</label>
                        <input type="url" name="course_url" required>
                    </div>
                    <div>
                        <label>Completion Date</label>
                        <input type="date" name="completion_date" required>
                    </div>
                    <div>
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-primary">Add Course</button>
                    </div>
                </div>
            <?= form_close() ?>
        </div>

        <div class="section">
            <h2>Employment History</h2>
            <p class="section-sub">Showcase your work experience with a more polished timeline card style.</p>

            <div class="stack">
                <?php if(!empty($employments)): ?>
                    <?php foreach($employments as $emp): ?>
                        <div class="item-list">
                            <strong><?= html_escape($emp->role) ?></strong> at <strong><?= html_escape($emp->company_name) ?></strong>
                            <div class="item-meta">
                                From: <?= html_escape($emp->start_date) ?><br>
                                To: <?= $emp->end_date ? html_escape($emp->end_date) : 'Present' ?>
                            </div>
                            <div class="action-links">
                                <a href="<?= site_url('profile/edit_employment/'.$emp->id) ?>" class="btn btn-success">Edit</a>
                                <a href="<?= site_url('profile/delete_employment/'.$emp->id) ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this employment record?');">Delete</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">No employment history added yet.</div>
                <?php endif; ?>
            </div>

            <div class="mini-title">Add Employment</div>
            <?= form_open('profile/add_employment') ?>
                <div class="form-grid">
                    <div>
                        <label>Company Name</label>
                        <input type="text" name="company_name" required>
                    </div>
                    <div>
                        <label>Job Role</label>
                        <input type="text" name="role" required>
                    </div>
                    <div>
                        <label>Start Date</label>
                        <input type="date" name="start_date" required>
                    </div>
                    <div>
                        <label>End Date (Leave blank if currently working here)</label>
                        <input type="date" name="end_date">
                    </div>
                    <div>
                        <button type="submit" class="btn btn-primary">Add Employment</button>
                    </div>
                </div>
            <?= form_close() ?>
        </div>

    </div>
</div>

</body>
</html>