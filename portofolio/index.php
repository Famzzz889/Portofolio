<?php
require_once 'config.php';

// Get profile data
$profile_query = "SELECT * FROM profile LIMIT 1";
$profile_result = $conn->query($profile_query);
$profile = $profile_result->fetch_assoc();

// Get certificates
$cert_query = "SELECT * FROM certificates ORDER BY created_at DESC";
$certificates = $conn->query($cert_query);

// Get experiences
$exp_query = "SELECT * FROM experiences ORDER BY start_date DESC";
$experiences = $conn->query($exp_query);

// Get skills
$skill_query = "SELECT * FROM skills ORDER BY category, skill_name";
$skills = $conn->query($skill_query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($profile['name'] ?? 'Portfolio'); ?> - Professional Portfolio</title>
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="<?php echo htmlspecialchars(substr($profile['about'] ?? '', 0, 160)); ?>">
    <meta name="keywords" content="portfolio, professional, <?php echo htmlspecialchars($profile['name'] ?? ''); ?>">
    <meta name="author" content="<?php echo htmlspecialchars($profile['name'] ?? ''); ?>">
    
    <!-- Open Graph -->
    <meta property="og:title" content="<?php echo htmlspecialchars($profile['name'] ?? ''); ?> - Portfolio">
    <meta property="og:description" content="<?php echo htmlspecialchars(substr($profile['about'] ?? '', 0, 160)); ?>">
    <meta property="og:type" content="website">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');
        
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --text-color: #333;
            --bg-color: #ffffff;
            --card-bg: #ffffff;
            --shadow: 0 5px 20px rgba(0,0,0,0.08);
            --transition: all 0.3s ease;
        }
        
        [data-theme="dark"] {
            --text-color: #e4e4e4;
            --bg-color: #0f0f1e;
            --card-bg: #1a1a2e;
            --shadow: 0 5px 20px rgba(0,0,0,0.3);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            color: var(--text-color);
            background: var(--bg-color);
            overflow-x: hidden;
            transition: var(--transition);
        }
        
        /* Loading Screen */
        .loading-screen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--primary-gradient);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            transition: opacity 0.5s, visibility 0.5s;
        }
        
        .loading-screen.hidden {
            opacity: 0;
            visibility: hidden;
        }
        
        .loader {
            width: 60px;
            height: 60px;
            border: 5px solid rgba(255,255,255,0.3);
            border-top: 5px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        .loading-text {
            color: white;
            margin-top: 20px;
            font-size: 1.2rem;
            font-weight: 600;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Scroll Progress Bar */
        .scroll-progress {
            position: fixed;
            top: 0;
            left: 0;
            height: 4px;
            background: var(--primary-gradient);
            z-index: 9999;
            transition: width 0.1s;
        }
        
        /* Custom Cursor */
        .cursor {
            width: 20px;
            height: 20px;
            border: 2px solid var(--primary-color);
            border-radius: 50%;
            position: fixed;
            pointer-events: none;
            z-index: 9999;
            transition: all 0.1s;
            display: none;
        }
        
        .cursor-follower {
            width: 8px;
            height: 8px;
            background: var(--primary-color);
            border-radius: 50%;
            position: fixed;
            pointer-events: none;
            z-index: 9999;
            transition: all 0.3s;
            display: none;
        }
        
        @media (min-width: 1024px) {
            .cursor, .cursor-follower {
                display: block;
            }
        }
        
        /* Navbar */
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 1rem 0;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            box-shadow: var(--shadow);
            transition: var(--transition);
        }
        
        [data-theme="dark"] .navbar {
            background: rgba(26, 26, 46, 0.95);
        }
        
        .navbar.scrolled {
            padding: 0.5rem 0;
            box-shadow: 0 2px 30px rgba(0,0,0,0.15);
        }
        
        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .nav-links {
            display: flex;
            gap: 30px;
            align-items: center;
        }
        
        .nav-links a {
            color: var(--text-color);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
            position: relative;
        }
        
        .nav-links a:after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--primary-gradient);
            transition: width 0.3s;
        }
        
        .nav-links a:hover:after,
        .nav-links a.active:after {
            width: 100%;
        }
        
        /* Dark Mode Toggle */
        .theme-toggle {
            background: var(--card-bg);
            border: 2px solid var(--primary-color);
            width: 50px;
            height: 26px;
            border-radius: 20px;
            position: relative;
            cursor: pointer;
            transition: var(--transition);
        }
        
        .theme-toggle:before {
            content: '';
            position: absolute;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: var(--primary-gradient);
            top: 2px;
            left: 2px;
            transition: var(--transition);
        }
        
        [data-theme="dark"] .theme-toggle:before {
            left: 26px;
        }
        
        /* Mobile Menu */
        .mobile-menu-btn {
            display: none;
            flex-direction: column;
            gap: 5px;
            cursor: pointer;
            padding: 5px;
        }
        
        .mobile-menu-btn span {
            width: 25px;
            height: 3px;
            background: var(--text-color);
            transition: var(--transition);
            border-radius: 3px;
        }
        
        .mobile-menu-btn.active span:nth-child(1) {
            transform: rotate(45deg) translate(8px, 8px);
        }
        
        .mobile-menu-btn.active span:nth-child(2) {
            opacity: 0;
        }
        
        .mobile-menu-btn.active span:nth-child(3) {
            transform: rotate(-45deg) translate(7px, -7px);
        }
        
        /* Hero with Particles */
        .hero {
            background: var(--primary-gradient);
            padding: 150px 20px 100px;
            margin-top: 60px;
            position: relative;
            overflow: hidden;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        
        #particles-js {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
        }
        
        .hero-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            gap: 60px;
            position: relative;
            z-index: 1;
        }
        
        .hero-text {
            flex: 1;
            color: white;
        }
        
        .hero-text h1 {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 15px;
            animation: fadeInUp 0.8s ease;
        }
        
        .typing-container {
            font-size: 1.8rem;
            margin-bottom: 20px;
            opacity: 0.95;
            min-height: 45px;
        }
        
        .typing-text {
            border-right: 3px solid white;
            animation: blink 0.7s infinite;
        }
        
        @keyframes blink {
            0%, 100% { border-color: transparent; }
            50% { border-color: white; }
        }
        
        .hero-text p {
            font-size: 1.1rem;
            margin-bottom: 30px;
            opacity: 0.9;
            line-height: 1.8;
            animation: fadeInUp 0.8s ease 0.4s;
            animation-fill-mode: backwards;
        }
        
        .hero-buttons {
            display: flex;
            gap: 15px;
            animation: fadeInUp 0.8s ease 0.6s;
            animation-fill-mode: backwards;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 14px 30px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            border: none;
        }
        
        .btn-primary {
            background: white;
            color: var(--primary-color);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
        }
        
        .btn-secondary {
            background: rgba(255,255,255,0.2);
            color: white;
            border: 2px solid white;
            backdrop-filter: blur(10px);
        }
        
        .btn-secondary:hover {
            background: white;
            color: var(--primary-color);
        }
        
        /* Profile Photo */
        .hero-photo {
            flex: 0 0 350px;
            animation: fadeInRight 0.8s ease;
        }
        
        .photo-container {
            position: relative;
            width: 350px;
            height: 350px;
        }
        
        .photo-frame {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            overflow: hidden;
            border: 8px solid rgba(255,255,255,0.3);
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            position: relative;
            z-index: 2;
        }
        
        .photo-frame img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .photo-bg {
            position: absolute;
            width: 120%;
            height: 120%;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            top: -10%;
            left: -10%;
            z-index: 1;
            animation: pulse 3s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes fadeInRight {
            from {
                opacity: 0;
                transform: translateX(50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        /* Container */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 80px 20px;
        }
        
        .section-title {
            font-size: 2.8rem;
            margin-bottom: 15px;
            text-align: center;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 700;
        }
        
        .section-subtitle {
            text-align: center;
            color: #666;
            font-size: 1.1rem;
            margin-bottom: 50px;
        }
        
        [data-theme="dark"] .section-subtitle {
            color: #999;
        }
        
        /* Stats Section */
        .stats-section {
            background: var(--primary-gradient);
            padding: 60px 20px;
            margin-top: -50px;
            position: relative;
            z-index: 10;
        }
        
        .stats-grid {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
        }
        
        .stat-item {
            text-align: center;
            color: white;
            padding: 20px;
            background: rgba(255,255,255,0.1);
            border-radius: 15px;
            backdrop-filter: blur(10px);
            transition: var(--transition);
        }
        
        .stat-item:hover {
            transform: translateY(-10px);
            background: rgba(255,255,255,0.2);
        }
        
        .stat-number {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 1rem;
            opacity: 0.9;
        }
        
        /* About Section */
        .about-section {
            background: var(--bg-color);
        }
        
        .about-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
        }
        
        .about-text h3 {
            color: var(--primary-color);
            font-size: 1.5rem;
            margin-bottom: 20px;
        }
        
        .about-text p {
            font-size: 1.05rem;
            margin-bottom: 20px;
            line-height: 1.8;
            color: var(--text-color);
        }
        
        .contact-info {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-top: 30px;
        }
        
        .contact-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            background: var(--card-bg);
            border-radius: 10px;
            box-shadow: var(--shadow);
            transition: var(--transition);
        }
        
        .contact-item:hover {
            transform: translateX(10px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.2);
        }
        
        .contact-item i {
            width: 40px;
            height: 40px;
            background: var(--primary-gradient);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
        }
        
        /* Certificates with Modal */
        .certificates-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 30px;
        }
        
        .cert-card {
            background: var(--card-bg);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: var(--transition);
            cursor: pointer;
        }
        
        .cert-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(102, 126, 234, 0.3);
        }
        
        .cert-image-container {
            width: 100%;
            height: 220px;
            overflow: hidden;
            position: relative;
        }
        
        .cert-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }
        
        .cert-card:hover .cert-image {
            transform: scale(1.1);
        }
        
        .cert-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(102, 126, 234, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: var(--transition);
        }
        
        .cert-card:hover .cert-overlay {
            opacity: 1;
        }
        
        .cert-overlay i {
            font-size: 3rem;
            color: white;
        }
        
        .cert-info {
            padding: 25px;
        }
        
        .cert-info h3 {
            color: var(--primary-color);
            margin-bottom: 12px;
            font-size: 1.2rem;
        }
        
        .cert-info p {
            color: var(--text-color);
            font-size: 0.95rem;
            margin-bottom: 8px;
        }
        
        .cert-badge {
            display: inline-block;
            background: var(--primary-gradient);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            margin-top: 10px;
        }
        
        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 10000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.9);
            animation: fadeIn 0.3s;
        }
        
        .modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            max-width: 90%;
            max-height: 90%;
            position: relative;
            animation: zoomIn 0.3s;
        }
        
        .modal-content img {
            width: 100%;
            height: auto;
            border-radius: 10px;
        }
        
        .modal-close {
            position: absolute;
            top: -40px;
            right: 0;
            color: white;
            font-size: 40px;
            cursor: pointer;
            transition: var(--transition);
        }
        
        .modal-close:hover {
            color: var(--primary-color);
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes zoomIn {
            from { transform: scale(0.5); }
            to { transform: scale(1); }
        }
        
        /* Timeline Experience */
        .timeline {
            position: relative;
            padding: 20px 0;
        }
        
        .timeline:before {
            content: '';
            position: absolute;
            left: 50%;
            top: 0;
            bottom: 0;
            width: 3px;
            background: var(--primary-gradient);
            transform: translateX(-50%);
        }
        
        .timeline-item {
            margin-bottom: 50px;
            position: relative;
        }
        
        .timeline-item:nth-child(odd) .experience-item {
            margin-left: 0;
            margin-right: auto;
            width: calc(50% - 40px);
        }
        
        .timeline-item:nth-child(even) .experience-item {
            margin-left: auto;
            margin-right: 0;
            width: calc(50% - 40px);
        }
        
        .timeline-dot {
            position: absolute;
            left: 50%;
            top: 30px;
            width: 20px;
            height: 20px;
            background: var(--primary-gradient);
            border: 4px solid var(--bg-color);
            border-radius: 50%;
            transform: translateX(-50%);
            z-index: 2;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.2);
        }
        
        .experience-item {
            background: var(--card-bg);
            padding: 35px;
            border-radius: 15px;
            box-shadow: var(--shadow);
            transition: var(--transition);
            position: relative;
        }
        
        .experience-item:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.2);
        }
        
        .experience-item:before {
            content: '';
            position: absolute;
            top: 35px;
            width: 0;
            height: 0;
            border: 10px solid transparent;
        }
        
        .timeline-item:nth-child(odd) .experience-item:before {
            right: -20px;
            border-left-color: var(--card-bg);
        }
        
        .timeline-item:nth-child(even) .experience-item:before {
            left: -20px;
            border-right-color: var(--card-bg);
        }
        
        .exp-header {
            margin-bottom: 15px;
        }
        
        .experience-item h3 {
            color: var(--primary-color);
            margin-bottom: 8px;
            font-size: 1.4rem;
        }
        
        .experience-item .company {
            font-weight: 600;
            color: var(--text-color);
            font-size: 1.1rem;
            margin-bottom: 8px;
        }
        
        .experience-item .date {
            color: #888;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(102, 126, 234, 0.1);
            padding: 5px 15px;
            border-radius: 20px;
            display: inline-flex;
        }
        
        .experience-item p {
            color: var(--text-color);
            line-height: 1.8;
            margin-top: 15px;
        }
        
        /* Skills */
        .skills-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
        }
        
        .skill-item {
            background: var(--card-bg);
            padding: 25px;
            border-radius: 15px;
            box-shadow: var(--shadow);
            transition: var(--transition);
        }
        
        .skill-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.2);
        }
        
        .skill-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .skill-name {
            font-weight: 600;
            color: var(--text-color);
            font-size: 1.1rem;
        }
        
        .skill-percentage {
            font-weight: 700;
            font-size: 1.2rem;
        }
        
        .skill-bar {
            background: rgba(0,0,0,0.1);
            height: 12px;
            border-radius: 10px;
            overflow: hidden;
            position: relative;
        }
        
        [data-theme="dark"] .skill-bar {
            background: rgba(255,255,255,0.1);
        }
        
        .skill-progress {
            height: 100%;
            border-radius: 10px;
            transition: width 1.5s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }
        
        .skill-progress[data-progress^="0"],
        .skill-progress[data-progress^="1"],
        .skill-progress[data-progress^="2"],
        .skill-progress[data-progress^="3"],
        .skill-progress[data-progress^="4"] {
            background: linear-gradient(90deg, #e74c3c 0%, #c0392b 100%);
        }
        
        .skill-progress[data-progress^="5"],
        .skill-progress[data-progress="60"],
        .skill-progress[data-progress="61"],
        .skill-progress[data-progress="62"],
        .skill-progress[data-progress="63"],
        .skill-progress[data-progress="64"],
        .skill-progress[data-progress="65"],
        .skill-progress[data-progress="66"],
        .skill-progress[data-progress="67"],
        .skill-progress[data-progress="68"],
        .skill-progress[data-progress="69"] {
            background: linear-gradient(90deg, #f39c12 0%, #e67e22 100%);
        }
        
        .skill-progress[data-progress^="7"],
        .skill-progress[data-progress^="8"],
        .skill-progress[data-progress^="9"],
        .skill-progress[data-progress="100"] {
            background: linear-gradient(90deg, #27ae60 0%, #229954 100%);
        }
        
        .skill-progress:after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            bottom: 0;
            right: 0;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            animation: shimmer 2s infinite;
        }
        
        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }
        
        .skill-category {
            display: inline-block;
            background: rgba(102, 126, 234, 0.1);
            color: var(--primary-color);
            padding: 3px 12px;
            border-radius: 15px;
            font-size: 0.85rem;
            margin-top: 10px;
            font-weight: 500;
        }
        
        /* Contact Form */
        .contact-form {
            background: var(--card-bg);
            padding: 40px;
            border-radius: 15px;
            box-shadow: var(--shadow);
            max-width: 600px;
            margin: 50px auto 0;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-color);
        }
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px 20px;
            border: 2px solid rgba(102, 126, 234, 0.2);
            border-radius: 10px;
            background: var(--bg-color);
            color: var(--text-color);
            font-family: 'Poppins', sans-serif;
            transition: var(--transition);
        }
        
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .form-group textarea {
            min-height: 120px;
            resize: vertical;
        }
        
        .btn-submit {
            width: 100%;
            background: var(--primary-gradient);
            color: white;
            padding: 15px;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
        }
        
        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }
        
        /* Footer */
        footer {
            background: #1a1a2e;
            color: white;
            padding: 50px 20px 30px;
        }
        
        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            text-align: center;
        }
        
        .footer-content h3 {
            margin-bottom: 20px;
            font-size: 1.8rem;
        }
        
        .social-links {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin: 30px 0;
        }
        
        .social-links a {
            width: 50px;
            height: 50px;
            background: rgba(255,255,255,0.1);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: var(--transition);
            font-size: 1.3rem;
            cursor: pointer;
        }
        
        .social-links a:hover {
            background: var(--primary-gradient);
            transform: translateY(-5px);
        }
        
        .social-links a.disabled {
            opacity: 0.3;
            cursor: not-allowed;
        }
        
        .social-links a.disabled:hover {
            background: rgba(255,255,255,0.1);
            transform: none;
        }
        
        .footer-bottom {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid rgba(255,255,255,0.1);
            opacity: 0.7;
        }
        
        /* Back to Top */
        .back-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            background: var(--primary-gradient);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            cursor: pointer;
            opacity: 0;
            visibility: hidden;
            transition: var(--transition);
            z-index: 999;
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        
        .back-to-top.visible {
            opacity: 1;
            visibility: visible;
        }
        
        .back-to-top:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.6);
        }
        
        /* Admin Link */
        .admin-link {
            position: fixed;
            bottom: 90px;
            right: 30px;
            background: var(--primary-gradient);
            color: white;
            padding: 15px 25px;
            border-radius: 50px;
            text-decoration: none;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
            transition: var(--transition);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
            z-index: 999;
        }
        
        .admin-link:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(102, 126, 234, 0.6);
        }
        
        /* Responsive */
        @media (max-width: 968px) {
            .hero-content {
                flex-direction: column-reverse;
                text-align: center;
            }
            
            .hero-text h1 {
                font-size: 2.5rem;
            }
            
            .typing-container {
                font-size: 1.4rem;
            }
            
            .hero-buttons {
                justify-content: center;
            }
            
            .photo-container {
                width: 250px;
                height: 250px;
            }
            
            .hero-photo {
                flex: 0 0 250px;
            }
            
            .about-content {
                grid-template-columns: 1fr;
            }
            
            .nav-links {
                position: fixed;
                top: 60px;
                left: -100%;
                width: 100%;
                height: calc(100vh - 60px);
                background: var(--card-bg);
                flex-direction: column;
                padding: 50px 20px;
                transition: var(--transition);
                box-shadow: var(--shadow);
            }
            
            .nav-links.active {
                left: 0;
            }
            
            .mobile-menu-btn {
                display: flex;
            }
            
            .timeline:before {
                left: 20px;
            }
            
            .timeline-item:nth-child(odd) .experience-item,
            .timeline-item:nth-child(even) .experience-item {
                width: calc(100% - 60px);
                margin-left: 60px;
                margin-right: 0;
            }
            
            .timeline-dot {
                left: 20px;
            }
            
            .timeline-item:nth-child(odd) .experience-item:before,
            .timeline-item:nth-child(even) .experience-item:before {
                left: -20px;
                border-right-color: var(--card-bg);
                border-left-color: transparent;
            }
            
            .admin-link {
                padding: 12px 20px;
                font-size: 0.9rem;
            }
        }
        
        /* Loading Animation */
        .fade-in {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.6s ease;
        }
        
        .fade-in.visible {
            opacity: 1;
            transform: translateY(0);
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            color: #888;
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.3;
        }
        
        .empty-state p {
            font-size: 1.1rem;
        }
    </style>
</head>
<body>
    <!-- Loading Screen -->
    <div class="loading-screen" id="loadingScreen">
        <div class="loader"></div>
        <div class="loading-text">Loading Portfolio...</div>
    </div>
    
    <!-- Scroll Progress Bar -->
    <div class="scroll-progress" id="scrollProgress"></div>
    
    <!-- Custom Cursor -->
    <div class="cursor" id="cursor"></div>
    <div class="cursor-follower" id="cursorFollower"></div>

    <nav class="navbar" id="navbar">
        <div class="nav-container">
            <div class="logo">
                <?php echo htmlspecialchars($profile['name'] ?? 'Portfolio'); ?>
            </div>
            <div class="nav-links" id="navLinks">
                <a href="#home"><i class="fas fa-home"></i> Home</a>
                <a href="#about"><i class="fas fa-user"></i> About</a>
                <a href="#certificates"><i class="fas fa-certificate"></i> Sertifikat</a>
                <a href="#experience"><i class="fas fa-briefcase"></i> Pengalaman</a>
                <a href="#skills"><i class="fas fa-code"></i> Skills</a>
                <a href="#contact"><i class="fas fa-envelope"></i> Kontak</a>
                <div class="theme-toggle" id="themeToggle" title="Toggle Dark Mode"></div>
            </div>
            <div class="mobile-menu-btn" id="mobileMenuBtn">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </nav>

    <section id="home" class="hero">
        <div id="particles-js"></div>
        <div class="hero-content">
            <div class="hero-text">
                <h1><?php echo htmlspecialchars($profile['name'] ?? 'Your Name'); ?></h1>
                <div class="typing-container">
                    <span class="typing-text" id="typingText"></span>
                </div>
                <p><?php echo htmlspecialchars(substr($profile['about'] ?? 'Your description here', 0, 150)); ?>...</p>
                <div class="hero-buttons">
                    <a href="#about" class="btn btn-primary">
                        <i class="fas fa-user"></i> Tentang Saya
                    </a>
                    <a href="#certificates" class="btn btn-secondary">
                        <i class="fas fa-certificate"></i> Lihat Portfolio
                    </a>
                    <a href="#contact" class="btn btn-secondary">
                        <i class="fas fa-envelope"></i> Hubungi Saya
                    </a>
                </div>
            </div>
            <div class="hero-photo">
                <div class="photo-container">
                    <div class="photo-bg"></div>
                    <div class="photo-frame">
                        <?php if($profile && !empty($profile['photo'])): ?>
                            <img src="<?php echo UPLOAD_DIR . htmlspecialchars($profile['photo']); ?>" alt="<?php echo htmlspecialchars($profile['name']); ?>" loading="lazy">
                        <?php else: ?>
                            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($profile['name'] ?? 'User'); ?>&size=350&background=667eea&color=fff&bold=true&font-size=0.4" alt="Profile" loading="lazy">
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="stats-section">
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-number" data-target="<?php echo $certificates->num_rows; ?>">0</div>
                <div class="stat-label">Sertifikat</div>
            </div>
            <div class="stat-item">
                <div class="stat-number" data-target="<?php echo $experiences->num_rows; ?>">0</div>
                <div class="stat-label">Pengalaman</div>
            </div>
            <div class="stat-item">
                <div class="stat-number" data-target="<?php echo $skills->num_rows; ?>">0</div>
                <div class="stat-label">Skills</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">100%</div>
                <div class="stat-label">Dedikasi</div>
            </div>
        </div>
    </section>

    <section id="about" class="about-section">
        <div class="container">
            <h2 class="section-title">Tentang Saya</h2>
            <p class="section-subtitle">Kenalan lebih dekat dengan saya</p>
            <div class="about-content fade-in">
                <div class="about-text">
                    <h3>Halo! Saya <?php echo htmlspecialchars($profile['name'] ?? 'Your Name'); ?></h3>
                    <p><?php echo nl2br(htmlspecialchars($profile['about'] ?? 'Your description here')); ?></p>
                    <p>Saya passionate dalam mengembangkan solusi yang inovatif dan memberikan dampak positif. Dengan pengalaman dan dedikasi tinggi, saya siap memberikan kontribusi terbaik untuk tim Anda.</p>
                </div>
                <div class="contact-info">
                    <div class="contact-item">
                        <i class="fas fa-envelope"></i>
                        <div>
                            <strong>Email</strong><br>
                            <?php echo htmlspecialchars($profile['email'] ?? 'email@example.com'); ?>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-phone"></i>
                        <div>
                            <strong>Telepon</strong><br>
                            <?php echo htmlspecialchars($profile['phone'] ?? '08123456789'); ?>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <div>
                            <strong>Alamat</strong><br>
                            <?php echo htmlspecialchars($profile['address'] ?? 'Your Address'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="certificates">
        <div class="container">
            <h2 class="section-title">Sertifikat & Penghargaan</h2>
            <p class="section-subtitle">Pencapaian dan sertifikasi profesional</p>
            <div class="certificates-grid">
                <?php 
                $certificates->data_seek(0);
                while($cert = $certificates->fetch_assoc()): 
                ?>
                <div class="cert-card fade-in" onclick="openModal('<?php echo UPLOAD_DIR . htmlspecialchars($cert['photo']); ?>')">
                    <div class="cert-image-container">
                        <img src="<?php echo UPLOAD_DIR . htmlspecialchars($cert['photo']); ?>" alt="<?php echo htmlspecialchars($cert['title']); ?>" class="cert-image" loading="lazy" onerror="this.src='https://via.placeholder.com/400x300/667eea/ffffff?text=Certificate'">
                        <div class="cert-overlay">
                            <i class="fas fa-search-plus"></i>
                        </div>
                    </div>
                    <div class="cert-info">
                        <h3><?php echo htmlspecialchars($cert['title']); ?></h3>
                        <p><i class="fas fa-building"></i> <strong>Penerbit:</strong> <?php echo htmlspecialchars($cert['issued_by']); ?></p>
                        <p><i class="fas fa-calendar"></i> <strong>Tanggal:</strong> <?php echo date('d F Y', strtotime($cert['issue_date'])); ?></p>
                        <span class="cert-badge"><i class="fas fa-check-circle"></i> Verified</span>
                    </div>
                </div>
                <?php endwhile; ?>
                
                <?php if($certificates->num_rows == 0): ?>
                <div class="empty-state" style="grid-column: 1/-1;">
                    <i class="fas fa-certificate"></i>
                    <p>Belum ada sertifikat yang ditambahkan</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section id="experience" style="background: var(--bg-color);">
        <div class="container">
            <h2 class="section-title">Pengalaman Kerja</h2>
            <p class="section-subtitle">Perjalanan karir profesional saya</p>
            <?php 
            $experiences->data_seek(0);
            if($experiences->num_rows > 0):
            ?>
            <div class="timeline">
                <?php while($exp = $experiences->fetch_assoc()): ?>
                <div class="timeline-item fade-in">
                    <div class="timeline-dot"></div>
                    <div class="experience-item">
                        <div class="exp-header">
                            <div>
                                <h3><?php echo htmlspecialchars($exp['position']); ?></h3>
                                <div class="company"><i class="fas fa-building"></i> <?php echo htmlspecialchars($exp['company']); ?></div>
                                <div class="date">
                                    <i class="fas fa-calendar-alt"></i>
                                    <?php 
                                    echo date('M Y', strtotime($exp['start_date']));
                                    echo ' - ';
                                    echo $exp['end_date'] ? date('M Y', strtotime($exp['end_date'])) : 'Sekarang';
                                    ?>
                                </div>
                            </div>
                        </div>
                        <p><?php echo nl2br(htmlspecialchars($exp['description'])); ?></p>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-briefcase"></i>
                <p>Belum ada pengalaman kerja yang ditambahkan</p>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <section id="skills" style="background: var(--bg-color);">
        <div class="container">
            <h2 class="section-title">Keahlian & Kompetensi</h2>
            <p class="section-subtitle">Teknologi dan tools yang saya kuasai</p>
            <div class="skills-grid">
                <?php 
                $skills->data_seek(0);
                while($skill = $skills->fetch_assoc()): 
                    $level_value = 0;
                    if(isset($skill['skill_level'])) {
                        $level_value = $skill['skill_level'];
                    } elseif(isset($skill['proficiency'])) {
                        $level_value = $skill['proficiency'];
                    } elseif(isset($skill['level'])) {
                        $level_value = $skill['level'];
                    } elseif(isset($skill['percentage'])) {
                        $level_value = $skill['percentage'];
                    }
                ?>
                <div class="skill-item fade-in">
                    <div class="skill-header">
                        <span class="skill-name"><?php echo htmlspecialchars($skill['skill_name']); ?></span>
                        <span class="skill-percentage" style="color: <?php 
                            if($level_value < 50) echo '#e74c3c';
                            elseif($level_value < 70) echo '#f39c12';
                            else echo '#27ae60';
                        ?>;"><?php echo $level_value; ?>%</span>
                    </div>
                    <div class="skill-bar">
                        <div class="skill-progress" data-progress="<?php echo $level_value; ?>" style="width: 0%"></div>
                    </div>
                    <span class="skill-category"><i class="fas fa-tag"></i> <?php echo htmlspecialchars($skill['category']); ?></span>
                </div>
                <?php endwhile; ?>
                
                <?php if($skills->num_rows == 0): ?>
                <div class="empty-state" style="grid-column: 1/-1;">
                    <i class="fas fa-code"></i>
                    <p>Belum ada skill yang ditambahkan</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section id="contact" style="background: var(--bg-color);">
        <div class="container">
            <h2 class="section-title">Hubungi Saya</h2>
            <p class="section-subtitle">Saya terbuka untuk peluang kolaborasi dan proyek baru</p>
            <form class="contact-form fade-in" id="contactForm">
                <div class="form-group">
                    <label for="name"><i class="fas fa-user"></i> Nama Lengkap</label>
                    <input type="text" id="name" name="name" required placeholder="Masukkan nama Anda">
                </div>
                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> Email</label>
                    <input type="email" id="email" name="email" required placeholder="email@example.com">
                </div>
                <div class="form-group">
                    <label for="subject"><i class="fas fa-tag"></i> Subjek</label>
                    <input type="text" id="subject" name="subject" required placeholder="Subjek pesan">
                </div>
                <div class="form-group">
                    <label for="message"><i class="fas fa-comment"></i> Pesan</label>
                    <textarea id="message" name="message" required placeholder="Tulis pesan Anda di sini..."></textarea>
                </div>
                <button type="submit" class="btn-submit">
                    <i class="fas fa-paper-plane"></i> Kirim Pesan
                </button>
            </form>
        </div>
    </section>

    <footer>
        <div class="footer-content">
            <h3>Mari Terhubung</h3>
            <p>Saya terbuka untuk peluang kolaborasi dan proyek baru</p>
            <div class="social-links">
                <a href="<?php echo !empty($profile['linkedin']) ? htmlspecialchars($profile['linkedin']) : 'https://www.linkedin.com/in/famadha-nugraha-setyajati-42aaa6287/'; ?>" 
                   target="_blank" 
                   title="LinkedIn"
                   class="<?php echo empty($profile['linkedin']) ? 'enable' : ''; ?>">
                    <i class="fab fa-linkedin-in"></i>
                </a>
                
                <a href="<?php echo !empty($profile['github']) ? htmlspecialchars($profile['github']) : '#'; ?>" 
                   target="_blank" 
                   title="GitHub"
                   class="<?php echo empty($profile['github']) ? 'disabled' : ''; ?>">
                    <i class="fab fa-github"></i>
                </a>
                
                <a href="<?php echo !empty($profile['instagram']) ? htmlspecialchars($profile['instagram']) : 'https://www.instagram.com/famadha_ns/'; ?>" 
                   target="_blank" 
                   title="Instagram"
                   class="<?php echo empty($profile['instagram']) ? 'enable' : ''; ?>">
                    <i class="fab fa-instagram"></i>
                </a>
                
                <a href="<?php echo !empty($profile['twitter']) ? htmlspecialchars($profile['twitter']) : '#'; ?>" 
                   target="_blank" 
                   title="Twitter"
                   class="<?php echo empty($profile['twitter']) ? 'disabled' : ''; ?>">
                    <i class="fab fa-twitter"></i>
                </a>
                
                <a href="<?php echo !empty($profile['facebook']) ? htmlspecialchars($profile['facebook']) : '#'; ?>" 
                   target="_blank" 
                   title="Facebook"
                   class="<?php echo empty($profile['facebook']) ? 'disabled' : ''; ?>">
                    <i class="fab fa-facebook-f"></i>
                </a>
                
                <a href="<?php echo !empty($profile['email']) ? 'mailto:'.htmlspecialchars($profile['email']) : 'mailto:famadha.nugraha@gmail.com'; ?>" 
                   title="Email"
                   class="<?php echo empty($profile['email']) ? 'enable' : ''; ?>">
                    <i class="fas fa-envelope"></i>
                </a>
                
                <a href="<?php echo !empty($profile['whatsapp']) ? 'https://wa.me/'.preg_replace('/[^0-9]/', '', $profile['whatsapp']) : 'https://wa.me/6289608577916'; ?>" 
                   target="_blank" 
                   title="WhatsApp"
                   class="<?php echo empty($profile['whatsapp']) ? 'enable' : ''; ?>">
                    <i class="fab fa-whatsapp"></i>
                </a>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($profile['name'] ?? 'Portfolio'); ?>. All rights reserved.</p>
                <p>Dibuat dengan <i class="fas fa-heart" style="color: #e74c3c;"></i> menggunakan PHP & MySQL</p>
            </div>
        </div>
    </footer>

    <!-- Modal for Certificate -->
    <div class="modal" id="certModal" onclick="closeModal()">
        <div class="modal-content" onclick="event.stopPropagation()">
            <span class="modal-close" onclick="closeModal()">&times;</span>
            <img id="modalImage" src="" alt="Certificate">
        </div>
    </div>

    <!-- Back to Top -->
    <div class="back-to-top" id="backToTop">
        <i class="fas fa-arrow-up"></i>
    </div>

    

    <!-- Particles.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/particles.js/2.0.0/particles.min.js"></script>
    
    <script>
        // Loading Screen
        window.addEventListener('load', function() {
            setTimeout(() => {
                document.getElementById('loadingScreen').classList.add('hidden');
            }, 1000);
        });
        
        // Particles.js Configuration
        if(document.getElementById('particles-js')) {
            particlesJS('particles-js', {
                particles: {
                    number: { value: 80, density: { enable: true, value_area: 800 } },
                    color: { value: '#ffffff' },
                    shape: { type: 'circle' },
                    opacity: { value: 0.5, random: false },
                    size: { value: 3, random: true },
                    line_linked: {
                        enable: true,
                        distance: 150,
                        color: '#ffffff',
                        opacity: 0.4,
                        width: 1
                    },
                    move: {
                        enable: true,
                        speed: 2,
                        direction: 'none',
                        random: false,
                        straight: false,
                        out_mode: 'out',
                        bounce: false
                    }
                },
                interactivity: {
                    detect_on: 'canvas',
                    events: {
                        onhover: { enable: true, mode: 'repulse' },
                        onclick: { enable: true, mode: 'push' },
                        resize: true
                    }
                },
                retina_detect: true
            });
        }
        
        // Typing Animation
        const typingText = document.getElementById('typingText');
        const texts = [
            '<?php echo htmlspecialchars($profile['title'] ?? 'Professional'); ?>',
            'Creative Problem Solver',
            'Team Player',
            'Fast Learner'
        ];
        let textIndex = 0;
        let charIndex = 0;
        let isDeleting = false;
        
        function type() {
            const currentText = texts[textIndex];
            
            if (!isDeleting && charIndex < currentText.length) {
                typingText.textContent += currentText.charAt(charIndex);
                charIndex++;
                setTimeout(type, 100);
            } else if (isDeleting && charIndex > 0) {
                typingText.textContent = currentText.substring(0, charIndex - 1);
                charIndex--;
                setTimeout(type, 50);
            } else {
                isDeleting = !isDeleting;
                if (!isDeleting) {
                    textIndex = (textIndex + 1) % texts.length;
                }
                setTimeout(type, 1000);
            }
        }
        
        type();
        
        // Dark Mode Toggle
        const themeToggle = document.getElementById('themeToggle');
        const currentTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', currentTheme);
        
        themeToggle.addEventListener('click', function() {
            const theme = document.documentElement.getAttribute('data-theme');
            const newTheme = theme === 'light' ? 'dark' : 'light';
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
        });
        
        // Mobile Menu
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const navLinks = document.getElementById('navLinks');
        
        mobileMenuBtn.addEventListener('click', function() {
            this.classList.toggle('active');
            navLinks.classList.toggle('active');
        });
        
        // Close mobile menu when clicking a link
        document.querySelectorAll('.nav-links a').forEach(link => {
            link.addEventListener('click', function() {
                mobileMenuBtn.classList.remove('active');
                navLinks.classList.remove('active');
            });
        });
        
        // Custom Cursor
        const cursor = document.getElementById('cursor');
        const cursorFollower = document.getElementById('cursorFollower');
        
        document.addEventListener('mousemove', (e) => {
            cursor.style.left = e.clientX + 'px';
            cursor.style.top = e.clientY + 'px';
            
            setTimeout(() => {
                cursorFollower.style.left = e.clientX + 'px';
                cursorFollower.style.top = e.clientY + 'px';
            }, 100);
        });
        
        // Cursor hover effect
        document.querySelectorAll('a, button, .cert-card, .skill-item').forEach(el => {
            el.addEventListener('mouseenter', () => {
                cursor.style.transform = 'scale(1.5)';
                cursorFollower.style.transform = 'scale(1.5)';
            });
            el.addEventListener('mouseleave', () => {
                cursor.style.transform = 'scale(1)';
                cursorFollower.style.transform = 'scale(1)';
            });
        });
        
        // Scroll Progress Bar
        window.addEventListener('scroll', function() {
            const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
            const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
            const scrolled = (winScroll / height) * 100;
            document.getElementById('scrollProgress').style.width = scrolled + '%';
        });
        
        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Intersection Observer for fade-in animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.fade-in').forEach(element => {
            observer.observe(element);
        });

        // Animate skill bars when in viewport
        const skillObserver = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const progressBar = entry.target.querySelector('.skill-progress');
                    if (progressBar && !progressBar.style.width.includes('%')) {
                        const progress = progressBar.getAttribute('data-progress');
                        setTimeout(() => {
                            progressBar.style.width = progress + '%';
                        }, 100);
                    }
                    skillObserver.unobserve(entry.target);
                }
            });
        }, observerOptions);

        document.querySelectorAll('.skill-item').forEach(element => {
            skillObserver.observe(element);
        });

        // Counter animation for stats
        function animateCounter(element) {
            const target = parseInt(element.getAttribute('data-target'));
            if (!target) {
                return;
            }
            
            let current = 0;
            const increment = target / 50;
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    element.textContent = target + '+';
                    clearInterval(timer);
                } else {
                    element.textContent = Math.floor(current) + '+';
                }
            }, 30);
        }

        // Animate stats when visible
        const statsObserver = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const statNumbers = entry.target.querySelectorAll('.stat-number[data-target]');
                    statNumbers.forEach(stat => {
                        animateCounter(stat);
                    });
                    statsObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });

        const statsSection = document.querySelector('.stats-section');
        if (statsSection) {
            statsObserver.observe(statsSection);
        }

        // Add active state to navigation
        window.addEventListener('scroll', function() {
            let current = '';
            const sections = document.querySelectorAll('section[id]');
            
            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                const sectionHeight = section.clientHeight;
                if (pageYOffset >= sectionTop - 100) {
                    current = section.getAttribute('id');
                }
            });

            document.querySelectorAll('.nav-links a').forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href').substring(1) === current) {
                    link.classList.add('active');
                }
            });
        });

        // Modal Functions
        function openModal(imageSrc) {
            const modal = document.getElementById('certModal');
            const modalImg = document.getElementById('modalImage');
            modal.classList.add('active');
            modalImg.src = imageSrc;
            document.body.style.overflow = 'hidden';
        }
        
        function closeModal() {
            const modal = document.getElementById('certModal');
            modal.classList.remove('active');
            document.body.style.overflow = 'auto';
        }
        
        // Close modal with ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
            }
        });
        
        // Back to Top Button
        const backToTop = document.getElementById('backToTop');
        
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                backToTop.classList.add('visible');
            } else {
                backToTop.classList.remove('visible');
            }
        });
        
        backToTop.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
        
        // Contact Form Handler
        const contactForm = document.getElementById('contactForm');
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get form data
            const formData = new FormData(contactForm);
            const name = formData.get('name');
            const email = formData.get('email');
            const subject = formData.get('subject');
            const message = formData.get('message');
            
            // Create WhatsApp message
            const whatsappNumber = '<?php echo preg_replace("/[^0-9]/", "", $profile["whatsapp"] ?? "6289608577916"); ?>';
            const whatsappMessage = `Halo, saya ${name}%0A%0AEmail: ${email}%0ASubjek: ${subject}%0A%0APesan:%0A${message}`;
            
            // Open WhatsApp
            window.open(`https://wa.me/${whatsappNumber}?text=${whatsappMessage}`, '_blank');
            
            // Reset form
            contactForm.reset();
            
            // Show success message
            alert('Terima kasih! Pesan Anda akan dikirim via WhatsApp.');
        });
        
        // Lazy loading images
        const lazyImages = document.querySelectorAll('img[loading="lazy"]');
        
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.src;
                        img.classList.add('loaded');
                        observer.unobserve(img);
                    }
                });
            });
            
            lazyImages.forEach(img => imageObserver.observe(img));
        }
        
        // Add parallax effect to hero background
        window.addEventListener('scroll', function() {
            const hero = document.querySelector('.hero');
            const scrolled = window.pageYOffset;
            if (hero && scrolled < hero.offsetHeight) {
                hero.style.transform = 'translateY(' + scrolled * 0.5 + 'px)';
            }
        });
        
        // Animate elements on scroll
        const animateOnScroll = () => {
            const elements = document.querySelectorAll('.fade-in');
            elements.forEach(element => {
                const elementTop = element.getBoundingClientRect().top;
                const elementVisible = 150;
                if (elementTop < window.innerHeight - elementVisible) {
                    element.classList.add('visible');
                }
            });
        };
        
        window.addEventListener('scroll', animateOnScroll);
        animateOnScroll(); // Initial check
        
        // Add hover effect to certificate cards
        document.querySelectorAll('.cert-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.zIndex = '10';
            });
            card.addEventListener('mouseleave', function() {
                this.style.zIndex = '1';
            });
        });
        
        // Add animation to stats on scroll
        let statsAnimated = false;
        window.addEventListener('scroll', function() {
            const statsSection = document.querySelector('.stats-section');
            if (statsSection && !statsAnimated) {
                const rect = statsSection.getBoundingClientRect();
                if (rect.top < window.innerHeight && rect.bottom >= 0) {
                    statsAnimated = true;
                }
            }
        });
        
        // Prevent right-click on images (optional - for protection)
        document.querySelectorAll('img').forEach(img => {
            img.addEventListener('contextmenu', function(e) {
                // e.preventDefault(); // Uncomment to disable right-click
            });
        });
        
        // Add smooth transition to page
        document.addEventListener('DOMContentLoaded', function() {
            document.body.style.opacity = '0';
            setTimeout(() => {
                document.body.style.transition = 'opacity 0.5s';
                document.body.style.opacity = '1';
            }, 100);
        });
        
        // Add tooltip to social links
        document.querySelectorAll('.social-links a').forEach(link => {
            link.addEventListener('mouseenter', function() {
                const title = this.getAttribute('title');
                if (title) {
                    const tooltip = document.createElement('div');
                    tooltip.textContent = title;
                    tooltip.style.cssText = `
                        position: absolute;
                        bottom: 100%;
                        left: 50%;
                        transform: translateX(-50%);
                        background: var(--primary-gradient);
                        color: white;
                        padding: 5px 10px;
                        border-radius: 5px;
                        font-size: 0.8rem;
                        white-space: nowrap;
                        margin-bottom: 10px;
                        pointer-events: none;
                    `;
                    this.style.position = 'relative';
                    this.appendChild(tooltip);
                }
            });
            link.addEventListener('mouseleave', function() {
                const tooltip = this.querySelector('div');
                if (tooltip) {
                    tooltip.remove();
                }
            });
        });
        
        // Performance optimization - Debounce scroll events
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }
        
        // Apply debounce to scroll-heavy functions
        const optimizedScroll = debounce(() => {
            // Your scroll-heavy functions here
        }, 10);
        
        window.addEventListener('scroll', optimizedScroll);
        
        // Add entrance animation
        window.addEventListener('load', function() {
            document.querySelectorAll('.hero-text > *').forEach((el, index) => {
                el.style.animationDelay = (index * 0.2) + 's';
            });
        });
        
        // Console Easter Egg
        console.log('%c👋 Halo Developer!', 'color: #667eea; font-size: 24px; font-weight: bold;');
        console.log('%cTertarik dengan portfolio ini? Mari berkolaborasi!', 'color: #764ba2; font-size: 14px;');
        console.log('%c<?php echo htmlspecialchars($profile["email"] ?? "email@example.com"); ?>', 'color: #27ae60; font-size: 12px;');
        
        // Add animation class to elements
        const addAnimationClass = (entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        };
        
        const elementsObserver = new IntersectionObserver(addAnimationClass, {
            threshold: 0.1,
            rootMargin: '0px'
        });
        
        // Track all animated elements
        document.querySelectorAll('.cert-card, .skill-item, .experience-item').forEach(el => {
            elementsObserver.observe(el);
        });
        
        // Keyboard navigation
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Home') {
                e.preventDefault();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } else if (e.key === 'End') {
                e.preventDefault();
                window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
            }
        });
        
        // Service Worker for PWA (optional)
        if ('serviceWorker' in navigator) {
            // navigator.serviceWorker.register('/sw.js'); // Uncomment if you have a service worker
        }
        
        // Analytics tracking (placeholder)
        function trackEvent(category, action, label) {
            // Add your analytics code here
            console.log('Event:', category, action, label);
        }
        
        // Track button clicks
        document.querySelectorAll('.btn').forEach(btn => {
            btn.addEventListener('click', function() {
                trackEvent('Button', 'Click', this.textContent.trim());
            });
        });
        
        // Track section views
        const sectionObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    trackEvent('Section', 'View', entry.target.id);
                }
            });
        }, { threshold: 0.5 });
        
        document.querySelectorAll('section[id]').forEach(section => {
            sectionObserver.observe(section);
        });
        
        // Print-friendly styles
        window.addEventListener('beforeprint', function() {
            document.body.classList.add('printing');
        });
        
        window.addEventListener('afterprint', function() {
            document.body.classList.remove('printing');
        });
        
        // Accessibility improvements
        document.querySelectorAll('a, button').forEach(el => {
            if (!el.getAttribute('aria-label') && !el.textContent.trim()) {
                const icon = el.querySelector('i');
                if (icon) {
                    const classList = Array.from(icon.classList);
                    const iconName = classList.find(c => c.startsWith('fa-'));
                    if (iconName) {
                        el.setAttribute('aria-label', iconName.replace('fa-', ''));
                    }
                }
            }
        });
        
        // Focus trap for modal
        const focusableElements = 'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])';
        
        document.getElementById('certModal').addEventListener('keydown', function(e) {
            if (e.key === 'Tab') {
                const modal = this;
                const focusable = modal.querySelectorAll(focusableElements);
                const firstFocusable = focusable[0];
                const lastFocusable = focusable[focusable.length - 1];
                
                if (e.shiftKey) {
                    if (document.activeElement === firstFocusable) {
                        lastFocusable.focus();
                        e.preventDefault();
                    }
                } else {
                    if (document.activeElement === lastFocusable) {
                        firstFocusable.focus();
                        e.preventDefault();
                    }
                }
            }
        });
        
        // Initialize everything
        console.log('%c✨ Portfolio Loaded Successfully!', 'color: #27ae60; font-size: 16px; font-weight: bold;');
        console.log('%c🚀 All features are ready!', 'color: #667eea; font-size: 14px;');
    </script>
</body>
</html>