<?php
require_once '../config.php';

if(!isLoggedIn()) {
    redirect('login.php');
}

// Get statistics
$cert_count = $conn->query("SELECT COUNT(*) as count FROM certificates")->fetch_assoc()['count'];
$exp_count = $conn->query("SELECT COUNT(*) as count FROM experiences")->fetch_assoc()['count'];
$skill_count = $conn->query("SELECT COUNT(*) as count FROM skills")->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .header h1 {
            font-size: 24px;
        }
        .header a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
            padding: 8px 15px;
            background: rgba(255,255,255,0.2);
            border-radius: 5px;
            transition: background 0.3s;
        }
        .header a:hover {
            background: rgba(255,255,255,0.3);
        }
        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        .stat-card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-number {
            font-size: 48px;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 10px;
        }
        .stat-label {
            color: #666;
            font-size: 18px;
        }
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }
        .menu-card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            text-align: center;
            text-decoration: none;
            color: #333;
            transition: all 0.3s;
        }
        .menu-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.3);
        }
        .menu-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }
        .menu-card h3 {
            color: #667eea;
            margin-bottom: 10px;
        }
        .welcome {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .welcome h2 {
            color: #667eea;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Admin Dashboard</h1>
        <div>
            <a href="../index.php" target="_blank">Lihat Website</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <div class="container">
        <div class="welcome">
            <h2>Selamat Datang, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>!</h2>
            <p>Kelola konten portfolio Anda dari dashboard ini.</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $cert_count; ?></div>
                <div class="stat-label">Sertifikat</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $exp_count; ?></div>
                <div class="stat-label">Pengalaman Kerja</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $skill_count; ?></div>
                <div class="stat-label">Skills</div>
            </div>
        </div>

        <div class="menu-grid">
            <a href="profile.php" class="menu-card">
                <div class="menu-icon">👤</div>
                <h3>Edit Profil</h3>
                <p>Ubah informasi profil Anda</p>
            </a>
            
            <a href="certificates.php" class="menu-card">
                <div class="menu-icon">📜</div>
                <h3>Kelola Sertifikat</h3>
                <p>Tambah, edit, atau hapus sertifikat</p>
            </a>
            
            <a href="experiences.php" class="menu-card">
                <div class="menu-icon">💼</div>
                <h3>Kelola Pengalaman</h3>
                <p>Tambah, edit, atau hapus pengalaman kerja</p>
            </a>
            
            <a href="skills.php" class="menu-card">
                <div class="menu-icon">⚡</div>
                <h3>Kelola Skills</h3>
                <p>Tambah, edit, atau hapus skills</p>
            </a>
        </div>
    </div>
</body>
</html>