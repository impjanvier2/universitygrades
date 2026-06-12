<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome - Catholic University of Rwanda Portal</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body, html {
            height: 100%;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow: hidden;
        }

        /* Full Screen Background Layout */
        .hero-section {
            height: 100vh;
            width: 100vw;
            background: linear-gradient(rgba(15, 23, 42, 0.85), rgba(30, 41, 59, 0.9)), 
                        url('https://images.unsplash.com/photo-1523050854058-8df90110c9f1?q=80&w=1920') no-repeat center center/cover;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            padding: 40px 20px;
            color: #ffffff;
            text-align: center;
        }

        /* --- UNIVERSITY OFFICIAL TOP HEADER --- */
        .uni-header {
            max-width: 800px;
            width: 100%;
            border-bottom: 2px double rgba(255, 255, 255, 0.3);
            padding-bottom: 20px;
            animation: fadeInDown 1s ease-out;
        }

        .uni-name {
            font-size: 2rem;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #ffffff;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
        }

        .uni-sub {
            font-size: 0.95rem;
            color: #cbd5e1;
            margin-top: 6px;
            font-weight: 500;
            letter-spacing: 0.5px;
        }

        /* --- CENTRAL WELCOME CARD --- */
        .welcome-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 50px 40px;
            border-radius: 20px;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
            animation: fadeInUp 1s ease-out;
        }

        .portal-title {
            font-size: 2.4rem;
            font-weight: 800;
            margin-bottom: 15px;
            color: #ffffff;
            letter-spacing: -0.5px;
        }

        .portal-desc {
            font-size: 1.05rem;
            color: #e2e8f0;
            line-height: 1.6;
            margin-bottom: 35px;
        }

        /* --- BUTTON MODERN ACTION --- */
        .enter-btn {
            display: inline-block;
            background-color: #2563eb;
            color: #ffffff;
            text-decoration: none;
            padding: 16px 45px;
            font-size: 1.15rem;
            font-weight: 700;
            border-radius: 30px;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.4);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .enter-btn:hover {
            background-color: #3b82f6;
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.6);
            transform: translateY(-3px);
        }

        .enter-btn:active {
            transform: translateY(-1px);
        }

        /* --- BOTTOM FOOTER --- */
        .footer-text {
            font-size: 0.85rem;
            color: #94a3b8;
            letter-spacing: 0.5px;
        }

        /* --- ANIMATIONS --- */
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Responsive Adjustments */
        @media (max-width: 600px) {
            .uni-name { font-size: 1.4rem; }
            .portal-title { font-size: 1.8rem; }
            .welcome-card { padding: 30px 20px; }
            .enter-btn { padding: 14px 30px; font-size: 1rem; }
        }
    </style>
</head>
<body>

<div class="hero-section">
    
    <div class="uni-header">
        <h1 class="uni-name">Catholic University of Rwanda</h1>
        <p class="uni-sub">P.o Box 49 Butare/Huye - RWANDA</p>
    </div>

    <div class="welcome-card">
        <h2 class="portal-title">Grading & Information Portal</h2>
        <p class="portal-desc">
            Welcome to the official academic framework. Access your modular course tracks, 
            secure evaluation charts, and automated GPA matrices.
        </p>
        <a href="login.php" class="enter-btn">Proceed to Sign In</a>
    </div>

    <div class="footer-text">
        &copy; <?php echo date("Y"); ?> Catholic University of Rwanda. All Rights Reserved.
    </div>

</div>

</body>
</html>