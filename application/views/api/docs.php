<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alumni API Documentation</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/swagger-ui/4.18.3/swagger-ui.css" />

    <style>
        :root {
            --bg: #0b1020;
            --bg-soft: #121a30;
            --card: rgba(255, 255, 255, 0.08);
            --card-border: rgba(255, 255, 255, 0.12);
            --text: #eef2ff;
            --muted: #a7b0c7;
            --primary: #7c3aed;
            --primary-2: #06b6d4;
            --success: #22c55e;
            --warning: #f59e0b;
            --danger: #ef4444;
            --shadow: 0 20px 50px rgba(0, 0, 0, 0.35);
            --radius: 22px;
        }

        * {
            box-sizing: border-box;
        }

        html, body {
            margin: 0;
            padding: 0;
            min-height: 100%;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background:
                radial-gradient(circle at 10% 20%, rgba(124, 58, 237, 0.25), transparent 25%),
                radial-gradient(circle at 90% 10%, rgba(6, 182, 212, 0.18), transparent 22%),
                radial-gradient(circle at 80% 80%, rgba(99, 102, 241, 0.15), transparent 25%),
                linear-gradient(135deg, #0a0f1f 0%, #11182d 45%, #0c1222 100%);
            color: var(--text);
            overflow-x: hidden;
        }

        body::before,
        body::after {
            content: "";
            position: fixed;
            inset: auto;
            width: 420px;
            height: 420px;
            border-radius: 50%;
            filter: blur(90px);
            z-index: 0;
            opacity: 0.45;
            animation: floatBlob 12s ease-in-out infinite;
            pointer-events: none;
        }

        body::before {
            top: -100px;
            left: -80px;
            background: rgba(124, 58, 237, 0.35);
        }

        body::after {
            bottom: -120px;
            right: -60px;
            background: rgba(6, 182, 212, 0.25);
            animation-delay: -4s;
        }

        @keyframes floatBlob {
            0%, 100% { transform: translateY(0) translateX(0) scale(1); }
            50% { transform: translateY(25px) translateX(15px) scale(1.08); }
        }

        .page-shell {
            position: relative;
            z-index: 1;
            padding: 28px;
        }

        .topbar {
            position: sticky;
            top: 18px;
            z-index: 20;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            padding: 18px 22px;
            margin: 0 auto 24px;
            max-width: 1400px;
            border: 1px solid var(--card-border);
            border-radius: 24px;
            background: rgba(10, 15, 31, 0.62);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            box-shadow: var(--shadow);
        }

        .brand-wrap {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .brand-icon {
            width: 52px;
            height: 52px;
            border-radius: 16px;
            background: linear-gradient(135deg, var(--primary), var(--primary-2));
            display: grid;
            place-items: center;
            box-shadow: 0 10px 30px rgba(124, 58, 237, 0.35);
            position: relative;
            overflow: hidden;
        }

        .brand-icon::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(120deg, transparent 20%, rgba(255,255,255,0.25) 50%, transparent 80%);
            transform: translateX(-120%);
            animation: shine 4s linear infinite;
        }

        @keyframes shine {
            100% { transform: translateX(120%); }
        }

        .brand-icon span {
            font-size: 1.2rem;
            font-weight: 800;
            color: white;
            z-index: 1;
        }

        .brand-copy h1 {
            margin: 0;
            font-size: 1.25rem;
            line-height: 1.2;
            letter-spacing: -0.02em;
        }

        .brand-copy p {
            margin: 4px 0 0;
            color: var(--muted);
            font-size: 0.92rem;
        }

        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            border-radius: 999px;
            border: 1px solid rgba(255,255,255,0.1);
            background: rgba(255,255,255,0.05);
            color: var(--text);
            font-size: 0.88rem;
            font-weight: 600;
        }

        .status-dot {
            width: 9px;
            height: 9px;
            border-radius: 50%;
            background: #22c55e;
            box-shadow: 0 0 0 6px rgba(34, 197, 94, 0.12);
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 11px 18px;
            border-radius: 14px;
            background: linear-gradient(135deg, var(--primary), #5b21b6);
            color: white;
            text-decoration: none;
            font-weight: 700;
            transition: transform 0.25s ease, box-shadow 0.25s ease, opacity 0.25s ease;
            box-shadow: 0 10px 24px rgba(124, 58, 237, 0.28);
        }

        .back-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 30px rgba(124, 58, 237, 0.4);
        }

        .hero {
            max-width: 1400px;
            margin: 0 auto 24px;
            padding: 34px;
            border-radius: 30px;
            border: 1px solid var(--card-border);
            background:
                linear-gradient(135deg, rgba(124,58,237,0.16), rgba(6,182,212,0.10)),
                rgba(255,255,255,0.04);
            box-shadow: var(--shadow);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: "";
            position: absolute;
            top: -30%;
            right: -5%;
            width: 260px;
            height: 260px;
            background: radial-gradient(circle, rgba(255,255,255,0.16), transparent 60%);
            animation: pulseGlow 5s ease-in-out infinite;
        }

        @keyframes pulseGlow {
            0%, 100% { transform: scale(1); opacity: 0.65; }
            50% { transform: scale(1.18); opacity: 0.9; }
        }

        .hero-grid {
            display: grid;
            grid-template-columns: 1.3fr 0.9fr;
            gap: 24px;
            align-items: center;
            position: relative;
            z-index: 1;
        }

        .hero h2 {
            margin: 0 0 12px;
            font-size: clamp(2rem, 4vw, 3rem);
            line-height: 1.05;
            letter-spacing: -0.04em;
        }

        .hero h2 .accent {
            background: linear-gradient(90deg, #c084fc, #67e8f9);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero p {
            margin: 0 0 20px;
            color: var(--muted);
            max-width: 720px;
            font-size: 1rem;
            line-height: 1.75;
        }

        .hero-stats {
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
        }

        .stat-card {
            min-width: 140px;
            padding: 16px 18px;
            border-radius: 18px;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.08);
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.05);
            animation: riseIn 0.7s ease both;
        }

        .stat-card:nth-child(2) { animation-delay: 0.1s; }
        .stat-card:nth-child(3) { animation-delay: 0.2s; }

        @keyframes riseIn {
            from {
                opacity: 0;
                transform: translateY(16px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .stat-label {
            display: block;
            font-size: 0.78rem;
            color: var(--muted);
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .stat-value {
            font-size: 1.1rem;
            font-weight: 800;
            color: white;
        }

        .hero-panel {
            padding: 22px;
            border-radius: 24px;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.1);
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.05);
        }

        .hero-panel h3 {
            margin: 0 0 14px;
            font-size: 1rem;
        }

        .hero-panel ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .hero-panel li {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 10px 0;
            color: var(--muted);
            border-bottom: 1px solid rgba(255,255,255,0.06);
        }

        .hero-panel li:last-child {
            border-bottom: 0;
        }

        .hero-panel li::before {
            content: "✦";
            color: #67e8f9;
            margin-top: 1px;
        }

        .swagger-shell {
            max-width: 1400px;
            margin: 0 auto;
            padding: 18px;
            border-radius: 30px;
            border: 1px solid var(--card-border);
            background: rgba(255,255,255,0.045);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            box-shadow: var(--shadow);
        }

        #swagger-ui {
            border-radius: 22px;
            overflow: hidden;
        }

        /* Swagger overrides */
        .swagger-ui {
            font-family: Inter, ui-sans-serif, system-ui, sans-serif;
            color: #e5e7eb;
        }

        .swagger-ui .topbar {
            display: none;
        }

        .swagger-ui .info {
            margin: 12px 0 28px;
        }

        .swagger-ui .info .title {
            color: #ffffff;
            font-size: 2rem;
            font-weight: 800;
            letter-spacing: -0.03em;
        }

        .swagger-ui .info p,
        .swagger-ui .info li,
        .swagger-ui .info table {
            color: #cbd5e1;
        }

        .swagger-ui .scheme-container {
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 20px;
            box-shadow: none;
            padding: 18px;
            margin: 0 0 22px;
        }

        .swagger-ui .opblock-tag {
            background: rgba(255,255,255,0.05);
            border-radius: 18px;
            border: 1px solid rgba(255,255,255,0.08);
            color: white;
            margin-bottom: 14px;
            transition: transform 0.2s ease, background 0.2s ease;
        }

        .swagger-ui .opblock-tag:hover {
            background: rgba(255,255,255,0.08);
            transform: translateY(-1px);
        }

        .swagger-ui .opblock.opblock-get {
            background: rgba(6, 182, 212, 0.08);
            border-color: rgba(6, 182, 212, 0.35);
            border-radius: 22px;
            overflow: hidden;
            box-shadow: none;
        }

        .swagger-ui .opblock .opblock-summary {
            border-color: rgba(255,255,255,0.06);
        }

        .swagger-ui .opblock .opblock-summary-method {
            border-radius: 999px;
            min-width: 74px;
            font-weight: 800;
            background: linear-gradient(135deg, #0891b2, #06b6d4);
        }

        .swagger-ui .opblock-summary-path,
        .swagger-ui .opblock-summary-description,
        .swagger-ui .response-col_status,
        .swagger-ui .tab li,
        .swagger-ui label,
        .swagger-ui .parameter__name,
        .swagger-ui .model-title,
        .swagger-ui .responses-inner h4,
        .swagger-ui .responses-inner h5 {
            color: #f8fafc !important;
        }

        .swagger-ui .markdown p,
        .swagger-ui .markdown li,
        .swagger-ui .response-col_description,
        .swagger-ui .parameter__type,
        .swagger-ui .parameter__deprecated,
        .swagger-ui .renderedMarkdown p,
        .swagger-ui .renderedMarkdown code {
            color: #cbd5e1 !important;
        }

        .swagger-ui .parameters-col_description input[type=text],
        .swagger-ui textarea,
        .swagger-ui input[type=text],
        .swagger-ui select {
            background: rgba(15, 23, 42, 0.9) !important;
            color: #f8fafc !important;
            border: 1px solid rgba(255,255,255,0.1) !important;
            border-radius: 14px !important;
            box-shadow: none !important;
        }

        .swagger-ui .btn {
            border-radius: 14px;
            font-weight: 700;
            transition: all 0.2s ease;
        }

        .swagger-ui .btn.execute {
            background: linear-gradient(135deg, var(--primary), #5b21b6);
            border-color: transparent;
            color: white;
            box-shadow: 0 10px 22px rgba(124, 58, 237, 0.25);
        }

        .swagger-ui .btn.execute:hover {
            transform: translateY(-1px);
            filter: brightness(1.07);
        }

        .swagger-ui .responses-table,
        .swagger-ui table tbody tr td,
        .swagger-ui table thead tr th {
            color: #e2e8f0 !important;
            background: transparent !important;
        }

        .swagger-ui section.models {
            margin-top: 24px;
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 22px;
            background: rgba(255,255,255,0.05);
            overflow: hidden;
        }

        .swagger-ui section.models h4,
        .swagger-ui section.models h5,
        .swagger-ui .model-box {
            color: #fff !important;
        }

        .swagger-ui .model-box {
            background: rgba(15, 23, 42, 0.45);
            border-radius: 16px;
        }

        .swagger-ui .highlight-code,
        .swagger-ui .microlight,
        .swagger-ui pre {
            background: #0f172a !important;
            color: #e2e8f0 !important;
            border-radius: 16px !important;
        }

        .swagger-ui .response-control-media-type__accept-message {
            color: #cbd5e1;
        }

        .floating-badge {
            position: fixed;
            right: 22px;
            bottom: 22px;
            z-index: 30;
            padding: 12px 16px;
            border-radius: 16px;
            border: 1px solid rgba(255,255,255,0.12);
            background: rgba(10, 15, 31, 0.72);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            color: #e5e7eb;
            font-size: 0.85rem;
            box-shadow: var(--shadow);
            animation: floatMini 4s ease-in-out infinite;
        }

        @keyframes floatMini {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-6px); }
        }

        @media (max-width: 980px) {
            .hero-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .page-shell {
                padding: 16px;
            }

            .topbar {
                flex-direction: column;
                align-items: stretch;
            }

            .topbar-actions {
                justify-content: space-between;
            }

            .brand-copy h1 {
                font-size: 1.05rem;
            }

            .hero {
                padding: 24px;
                border-radius: 24px;
            }

            .swagger-shell {
                padding: 12px;
                border-radius: 24px;
            }

            .floating-badge {
                position: static;
                margin: 18px auto 0;
                width: fit-content;
            }
        }
    </style>
</head>
<body>
    <div class="page-shell">
        <header class="topbar">
            <div class="brand-wrap">
                <div class="brand-icon">
                    <span>API</span>
                </div>
                <div class="brand-copy">
                    <h1>Developer API Portal</h1>
                    <p>Interactive documentation for Alumni Data services</p>
                </div>
            </div>

            <div class="topbar-actions">
                <div class="chip">
                    <span class="status-dot"></span>
                    Live Docs
                </div>
                <a class="back-btn" href="<?= site_url('auth/dashboard') ?>">← Back to Dashboard</a>
            </div>
        </header>

        <section class="hero">
            <div class="hero-grid">
                <div>
                    <h2>
                        Build with the <span class="accent">Alumni Data API</span>
                    </h2>
                    <p>
                        Explore secure endpoints, test requests instantly, and integrate the
                        Alumni of the Day experience into your augmented reality and client applications.
                    </p>

                    <div class="hero-stats">
                        <div class="stat-card">
                            <span class="stat-label">API Version</span>
                            <span class="stat-value">v1.0.0</span>
                        </div>
                        <div class="stat-card">
                            <span class="stat-label">Auth</span>
                            <span class="stat-value">Bearer Token</span>
                        </div>
                        <div class="stat-card">
                            <span class="stat-label">Format</span>
                            <span class="stat-value">JSON Response</span>
                        </div>
                    </div>
                </div>

                <div class="hero-panel">
                    <h3>What’s included</h3>
                    <ul>
                        <li>Interactive Swagger testing interface</li>
                        <li>Animated premium dashboard-style presentation</li>
                        <li>Modern dark glassmorphism layout</li>
                        <li>Cleaner developer-focused API experience</li>
                    </ul>
                </div>
            </div>
        </section>

        <section class="swagger-shell">
            <div id="swagger-ui"></div>
        </section>

        <div class="floating-badge">
            Alumni API • Modern Docs Experience
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/swagger-ui/4.18.3/swagger-ui-bundle.js"></script>
    <script>
        window.onload = function() {
            const spec = {
                "openapi": "3.0.0",
                "info": {
                    "title": "Alumni Data API",
                    "description": "API for Augmented Reality Clients to retrieve the current Alumni of the Day.\n\n## Database Schema & Entity Relationships\nThe system utilizes a 3NF relational database. Key entities include:\n* **Users**: Core authentication (1:1 with Profiles, 1:M with Bids, 1:M with API Keys).\n* **Alumni_Profiles**: Biographics and featured status.\n* **Alumni_Bids**: Tracks target dates and bid amounts.\n* **Alumni_Monthly_Limits**: Enforces the max 3 (or 4) wins per calendar month rule.\n* **Education/Employment**: Collections (Degrees, Certifications, Licences, Courses) linked to Users via 1:M relationships.\n* **API_Keys & API_Logs**: Secures endpoints and tracks developer usage (1:M).",
                    "version": "1.0.0"
                },
                "servers": [
                    { "url": "<?= base_url() ?>" }
                ],
                "components": {
                    "securitySchemes": {
                        "BearerAuth": {
                            "type": "http",
                            "scheme": "bearer",
                            "bearerFormat": "API Key",
                            "description": "Enter your generated Developer API Key here."
                        }
                    }
                },
                "security": [{ "BearerAuth": [] }],
                "paths": {
                    "/index.php/api/alumni_of_the_day": {
                        "get": {
                            "summary": "Get Today's Winning Alumni",
                            "description": "Returns the complete profile details (Bio, Degrees, Employment, and Image URL) of the alumni who won the bid for today.",
                            "responses": {
                                "200": {
                                    "description": "Successful retrieval of alumni data.",
                                    "content": {
                                        "application/json": {
                                            "example": {
                                                "status": "success",
                                                "data": {
                                                    "first_name": "Jane",
                                                    "last_name": "Doe",
                                                    "email": "jane.doe@my.westminster.ac.uk",
                                                    "bio": "Senior Software Engineer with 5 years experience.",
                                                    "linkedin_url": "https://linkedin.com/in/janedoe",
                                                    "profile_image_url": "http://localhost/uploads/profile_images/sample.jpg",
                                                    "education": [
                                                        {
                                                            "degree_name": "BSc Computer Science",
                                                            "university_url": "https://westminster.ac.uk",
                                                            "completion_date": "2024-06-01"
                                                        }
                                                    ],
                                                    "employment": [
                                                        {
                                                            "company_name": "Tech Corp",
                                                            "role": "Backend Developer",
                                                            "start_date": "2024-07-01",
                                                            "end_date": null
                                                        }
                                                    ]
                                                }
                                            }
                                        }
                                    }
                                },
                                "401": {
                                    "description": "Unauthorized - Missing or badly formatted Bearer Token.",
                                    "content": {
                                        "application/json": {
                                            "example": {
                                                "status": "error",
                                                "message": "Missing or invalid Authorization header. Use format: Bearer <your_key>"
                                            }
                                        }
                                    }
                                },
                                "403": {
                                    "description": "Forbidden - API Key is invalid or has been revoked.",
                                    "content": {
                                        "application/json": {
                                            "example": {
                                                "status": "error",
                                                "message": "Invalid or Revoked API Key."
                                            }
                                        }
                                    }
                                },
                                "429": {
                                    "description": "Too Many Requests - Rate limit exceeded.",
                                    "content": {
                                        "application/json": {
                                            "example": {
                                                "status": "error",
                                                "message": "Rate limit exceeded. Please try again later."
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            };

            const ui = SwaggerUIBundle({
                spec: spec,
                dom_id: '#swagger-ui',
                deepLinking: true,
                docExpansion: "list",
                defaultModelsExpandDepth: 1,
                defaultModelExpandDepth: 1,
                presets: [SwaggerUIBundle.presets.apis]
            });

            window.ui = ui;
        };
    </script>
</body>
</html>