<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? html_escape($title) : 'Login'; ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg-1: #0b1020;
            --bg-2: #121a2f;
            --glass: rgba(255, 255, 255, 0.08);
            --glass-strong: rgba(255, 255, 255, 0.12);
            --border: rgba(255, 255, 255, 0.14);
            --text: #f8fafc;
            --muted: #94a3b8;
            --primary: #6366f1;
            --primary-2: #8b5cf6;
            --danger: #ef4444;
            --success: #22c55e;
            --shadow: 0 24px 60px rgba(0,0,0,0.35);
            --radius: 24px;
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
                radial-gradient(circle at top left, rgba(99,102,241,0.24), transparent 30%),
                radial-gradient(circle at bottom right, rgba(139,92,246,0.20), transparent 30%),
                linear-gradient(135deg, var(--bg-1), var(--bg-2));
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            overflow: hidden;
        }

        body::before,
        body::after {
            content: "";
            position: fixed;
            width: 340px;
            height: 340px;
            border-radius: 50%;
            filter: blur(90px);
            opacity: 0.35;
            z-index: 0;
            pointer-events: none;
            animation: floatGlow 10s ease-in-out infinite alternate;
        }

        body::before {
            background: #6366f1;
            top: -90px;
            left: -90px;
        }

        body::after {
            background: #8b5cf6;
            right: -90px;
            bottom: -90px;
            animation-delay: 2s;
        }

        @keyframes floatGlow {
            from { transform: translateY(0) translateX(0) scale(1); }
            to { transform: translateY(24px) translateX(12px) scale(1.08); }
        }

        .login-shell {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 1050px;
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--border);
            border-radius: 30px;
            overflow: hidden;
            box-shadow: var(--shadow);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            animation: fadeUp 0.75s ease;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(18px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .left-panel {
            position: relative;
            padding: 48px;
            background:
                linear-gradient(135deg, rgba(99,102,241,0.30), rgba(139,92,246,0.18)),
                rgba(255,255,255,0.04);
            display: flex;
            flex-direction: column;
            justify-content: center;
            overflow: hidden;
        }

        .left-panel::after {
            content: "";
            position: absolute;
            top: -10%;
            right: -20%;
            width: 260px;
            height: 260px;
            background: radial-gradient(circle, rgba(255,255,255,0.16), transparent 65%);
            pointer-events: none;
        }

        .brand-badge {
            display: inline-block;
            width: fit-content;
            padding: 8px 14px;
            border-radius: 999px;
            background: rgba(255,255,255,0.10);
            border: 1px solid rgba(255,255,255,0.12);
            font-size: 0.82rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: #dbeafe;
            margin-bottom: 18px;
        }

        .left-panel h1 {
            margin: 0 0 16px;
            font-size: clamp(2.2rem, 4vw, 3.5rem);
            line-height: 1.05;
            font-weight: 800;
            letter-spacing: -0.04em;
        }

        .left-panel p {
            margin: 0;
            color: #dbeafe;
            font-size: 1rem;
            line-height: 1.8;
            max-width: 480px;
        }

        .feature-list {
            margin-top: 28px;
            display: grid;
            gap: 14px;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #e2e8f0;
            font-weight: 500;
        }

        .feature-icon {
            width: 36px;
            height: 36px;
            border-radius: 12px;
            display: grid;
            place-items: center;
            background: rgba(255,255,255,0.10);
            border: 1px solid rgba(255,255,255,0.10);
            font-size: 1rem;
        }

        .right-panel {
            padding: 48px;
            background: rgba(10, 14, 25, 0.45);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .form-card {
            width: 100%;
            max-width: 420px;
        }

        .form-card h2 {
            margin: 0 0 8px;
            font-size: 2rem;
            font-weight: 800;
            letter-spacing: -0.03em;
        }

        .form-subtitle {
            margin: 0 0 28px;
            color: var(--muted);
            line-height: 1.7;
        }

        .alert {
            padding: 14px 16px;
            border-radius: 16px;
            margin-bottom: 16px;
            border: 1px solid transparent;
            animation: fadeUp 0.4s ease;
            font-size: 0.95rem;
            line-height: 1.6;
        }

        .alert-danger {
            background: rgba(239,68,68,0.14);
            color: #fecaca;
            border-color: rgba(239,68,68,0.25);
        }

        .field {
            margin-bottom: 18px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-size: 0.95rem;
            font-weight: 600;
            color: #e2e8f0;
        }

        .input-wrap {
            position: relative;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 15px 16px;
            border-radius: 16px;
            border: 1px solid rgba(255,255,255,0.10);
            background: rgba(255,255,255,0.06);
            color: white;
            outline: none;
            font-size: 0.98rem;
            transition: 0.25s ease;
        }

        input[type="email"]::placeholder,
        input[type="password"]::placeholder {
            color: #94a3b8;
        }

        input[type="email"]:focus,
        input[type="password"]:focus {
            border-color: rgba(99,102,241,0.75);
            box-shadow: 0 0 0 4px rgba(99,102,241,0.16);
            background: rgba(255,255,255,0.08);
            transform: translateY(-1px);
        }

        .btn {
            width: 100%;
            border: none;
            border-radius: 16px;
            padding: 14px 18px;
            font-size: 1rem;
            font-weight: 800;
            cursor: pointer;
            transition: 0.25s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-primary {
            color: white;
            background: linear-gradient(135deg, var(--primary), var(--primary-2));
            box-shadow: 0 14px 28px rgba(99,102,241,0.28);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 18px 32px rgba(99,102,241,0.38);
        }

        .divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 24px 0 18px;
            color: var(--muted);
            font-size: 0.92rem;
        }

        .divider::before,
        .divider::after {
            content: "";
            flex: 1;
            height: 1px;
            background: rgba(255,255,255,0.10);
        }

        .secondary-actions {
            display: grid;
            gap: 12px;
        }

        .link-btn {
            width: 100%;
            text-decoration: none;
        }

        .btn-secondary {
            width: 100%;
            color: white;
            background: rgba(255,255,255,0.07);
            border: 1px solid rgba(255,255,255,0.10);
        }

        .btn-secondary:hover {
            transform: translateY(-2px);
            background: rgba(255,255,255,0.10);
            border-color: rgba(255,255,255,0.16);
        }

        .helper-text {
            margin-top: 18px;
            text-align: center;
            color: var(--muted);
            font-size: 0.92rem;
        }

        @media (max-width: 900px) {
            .login-shell {
                grid-template-columns: 1fr;
            }

            .left-panel,
            .right-panel {
                padding: 32px 24px;
            }
        }

        @media (max-width: 520px) {
            .left-panel h1 {
                font-size: 2rem;
            }

            .form-card h2 {
                font-size: 1.6rem;
            }

            .left-panel,
            .right-panel {
                padding: 24px 18px;
            }

            .login-shell {
                border-radius: 22px;
            }
        }
    </style>
</head>
<body>

    <div class="login-shell">
        <div class="left-panel">
            <span class="brand-badge">Alumni Portal</span>
            <h1>Welcome Back</h1>
            <p>
                Sign in to access your alumni dashboard, manage your profile, join bidding activities,
                and handle your account in a modern secure workspace.
            </p>

            <div class="feature-list">
                <div class="feature-item">
                    <div class="feature-icon">👤</div>
                    <span>Manage your profile and achievements</span>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">🏆</div>
                    <span>Participate in Alumni of the Day bidding</span>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">🔐</div>
                    <span>Secure access to your account tools</span>
                </div>
            </div>
        </div>

        <div class="right-panel">
            <div class="form-card">
                <h2>Login</h2>
                <p class="form-subtitle">Enter your university credentials to continue.</p>

                <?php if (validation_errors()): ?>
                    <div class="alert alert-danger">
                        <?= validation_errors(); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger">
                        <?= html_escape($error_message); ?>
                    </div>
                <?php endif; ?>

                <?= form_open('auth/login'); ?>
                    <div class="field">
                        <label for="email">University Email</label>
                        <div class="input-wrap">
                            <input
                                type="email"
                                name="email"
                                id="email"
                                placeholder="Enter your university email"
                                value="<?= set_value('email'); ?>"
                                required
                            >
                        </div>
                    </div>

                    <div class="field">
                        <label for="password">Password</label>
                        <div class="input-wrap">
                            <input
                                type="password"
                                name="password"
                                id="password"
                                placeholder="Enter your password"
                                required
                            >
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Login</button>
                <?= form_close(); ?>

                <div class="divider">Need help?</div>

                <div class="secondary-actions">
                    <a href="<?= site_url('auth/register'); ?>" class="link-btn">
                        <button type="button" class="btn btn-secondary">Register Here</button>
                    </a>

                    <a href="<?= site_url('auth/forgot_password'); ?>" class="link-btn">
                        <button type="button" class="btn btn-secondary">Forgot Password</button>
                    </a>
                </div>

                <div class="helper-text">
                    Use your official university email to sign in.
                </div>
            </div>
        </div>
    </div>

</body>
</html>