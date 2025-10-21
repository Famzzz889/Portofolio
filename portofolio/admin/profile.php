<?php
require_once '../config.php';

if(!isLoggedIn()) {
    redirect('login.php');
}

$success = '';
$error = '';

// Get profile data
$profile_query = "SELECT * FROM profile LIMIT 1";
$profile_result = $conn->query($profile_query);
$profile = $profile_result->fetch_assoc();

// Handle Update
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = clean($_POST['name']);
    $title = clean($_POST['title']);
    $about = clean($_POST['about']);
    $phone = clean($_POST['phone']);
    $email = clean($_POST['email']);
    $address = clean($_POST['address']);
    
    $photo_name = $profile ? $profile['photo'] : '';
    
    // Handle photo upload
    if(isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['photo']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if(in_array($ext, $allowed)) {
            $new_filename = 'profile_' . uniqid() . '.' . $ext;
            $upload_path = '../' . UPLOAD_DIR . $new_filename;
            
            if(!is_dir('../' . UPLOAD_DIR)) {
                mkdir('../' . UPLOAD_DIR, 0777, true);
            }
            
            if(move_uploaded_file($_FILES['photo']['tmp_name'], $upload_path)) {
                // Delete old photo if exists
                if($profile && !empty($profile['photo'])) {
                    $old_file = '../' . UPLOAD_DIR . $profile['photo'];
                    if(file_exists($old_file)) {
                        unlink($old_file);
                    }
                }
                $photo_name = $new_filename;
            } else {
                $error = 'Gagal mengupload foto!';
            }
        } else {
            $error = 'Format foto tidak didukung! Gunakan JPG, PNG, atau GIF.';
        }
    }
    
    if(empty($error)) {
        if($profile) {
            $query = "UPDATE profile SET name='$name', title='$title', about='$about', phone='$phone', email='$email', address='$address', photo='$photo_name' WHERE id=" . $profile['id'];
        } else {
            $query = "INSERT INTO profile (name, title, about, phone, email, address, photo) VALUES ('$name', '$title', '$about', '$phone', '$email', '$address', '$photo_name')";
        }
        
        if($conn->query($query)) {
            $success = 'Profil berhasil diupdate!';
            // Refresh data
            $profile_result = $conn->query($profile_query);
            $profile = $profile_result->fetch_assoc();
        } else {
            $error = 'Gagal mengupdate profil!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profil</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            padding: 8px 15px;
            background: rgba(255,255,255,0.2);
            border-radius: 5px;
            transition: background 0.3s;
        }
        .header a:hover {
            background: rgba(255,255,255,0.3);
        }
        .container {
            max-width: 900px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .form-container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .photo-upload-section {
            text-align: center;
            margin-bottom: 40px;
            padding: 30px;
            background: #f8f9fa;
            border-radius: 10px;
        }
        .photo-preview {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            margin: 0 auto 20px;
            overflow: hidden;
            border: 5px solid #667eea;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }
        .photo-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .upload-btn-wrapper {
            position: relative;
            display: inline-block;
        }
        .upload-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 30px;
            border-radius: 50px;
            cursor: pointer;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: transform 0.3s;
        }
        .upload-btn:hover {
            transform: translateY(-2px);
        }
        .upload-btn-wrapper input[type=file] {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            cursor: pointer;
            width: 100%;
            height: 100%;
        }
        .form-group {
            margin-bottom: 25px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 600;
            font-size: 14px;
        }
        label i {
            margin-right: 5px;
            color: #667eea;
        }
        input[type="text"],
        input[type="email"],
        input[type="tel"],
        textarea {
            width: 100%;
            padding: 14px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            transition: all 0.3s;
        }
        textarea {
            min-height: 120px;
            resize: vertical;
        }
        input:focus, textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .btn {
            padding: 15px 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }
        .success {
            background: linear-gradient(135deg, #00b09b, #96c93d);
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideIn 0.5s ease;
        }
        .error {
            background: linear-gradient(135deg, #f093fb, #f5576c);
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideIn 0.5s ease;
        }
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        h2 {
            color: #667eea;
            margin-bottom: 10px;
            text-align: center;
            font-size: 2rem;
        }
        .subtitle {
            text-align: center;
            color: #888;
            margin-bottom: 30px;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .char-counter {
            text-align: right;
            font-size: 12px;
            color: #888;
            margin-top: 5px;
        }
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            .photo-preview {
                width: 150px;
                height: 150px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1><i class="fas fa-user-edit"></i> Edit Profil</h1>
        <a href="dashboard.php"><i class="fas fa-arrow-left"></i> Kembali ke Dashboard</a>
    </div>

    <div class="container">
        <div class="form-container">
            <h2>Informasi Profil</h2>
            <p class="subtitle">Lengkapi profil Anda untuk tampilan portfolio yang lebih profesional</p>
            
            <?php if($success): ?>
            <div class="success">
                <i class="fas fa-check-circle"></i>
                <?php echo $success; ?>
            </div>
            <?php endif; ?>
            
            <?php if($error): ?>
            <div class="error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error; ?>
            </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="photo-upload-section">
                    <div class="photo-preview">
                        <?php if($profile && !empty($profile['photo'])): ?>
                            <img src="../<?php echo UPLOAD_DIR . htmlspecialchars($profile['photo']); ?>" alt="Profile Photo" id="preview-image">
                        <?php else: ?>
                            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($profile['name'] ?? 'User'); ?>&size=200&background=667eea&color=fff&bold=true&font-size=0.4" alt="Profile" id="preview-image">
                        <?php endif; ?>
                    </div>
                    <div class="upload-btn-wrapper">
                        <label class="upload-btn">
                            <i class="fas fa-camera"></i> Upload Foto Profil
                            <input type="file" name="photo" accept="image/*" onchange="previewPhoto(event)">
                        </label>
                    </div>
                    <p style="margin-top: 10px; font-size: 12px; color: #888;">
                        <i class="fas fa-info-circle"></i> Format: JPG, PNG, GIF (Max 5MB)
                    </p>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-user"></i> Nama Lengkap *</label>
                    <input type="text" name="name" value="<?php echo $profile ? htmlspecialchars($profile['name']) : ''; ?>" placeholder="Contoh: John Doe" required>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-briefcase"></i> Profesi/Jabatan *</label>
                    <input type="text" name="title" value="<?php echo $profile ? htmlspecialchars($profile['title']) : ''; ?>" placeholder="Contoh: Full Stack Developer" required>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-align-left"></i> Tentang Diri *</label>
                    <textarea name="about" id="about" placeholder="Ceritakan tentang diri Anda, pengalaman, passion, dan apa yang membuat Anda unik..." required oninput="updateCharCount()"><?php echo $profile ? htmlspecialchars($profile['about']) : ''; ?></textarea>
                    <div class="char-counter">
                        <span id="char-count">0</span> karakter
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-envelope"></i> Email *</label>
                        <input type="email" name="email" value="<?php echo $profile ? htmlspecialchars($profile['email']) : ''; ?>" placeholder="email@example.com" required>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-phone"></i> Nomor Telepon</label>
                        <input type="tel" name="phone" value="<?php echo $profile ? htmlspecialchars($profile['phone']) : ''; ?>" placeholder="08123456789">
                    </div>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-map-marker-alt"></i> Alamat</label>
                    <textarea name="address" placeholder="Alamat lengkap Anda..." style="min-height: 80px;"><?php echo $profile ? htmlspecialchars($profile['address']) : ''; ?></textarea>
                </div>
                
                <button type="submit" class="btn">
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>
            </form>
        </div>
    </div>

    <script>
        // Preview photo before upload
        function previewPhoto(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('preview-image').src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        }

        // Character counter
        function updateCharCount() {
            const textarea = document.getElementById('about');
            const charCount = document.getElementById('char-count');
            charCount.textContent = textarea.value.length;
        }

        // Initialize character count
        window.addEventListener('load', updateCharCount);

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const name = document.querySelector('input[name="name"]').value.trim();
            const title = document.querySelector('input[name="title"]').value.trim();
            const about = document.querySelector('textarea[name="about"]').value.trim();
            const email = document.querySelector('input[name="email"]').value.trim();

            if (!name || !title || !about || !email) {
                e.preventDefault();
                alert('Mohon lengkapi semua field yang wajib diisi (*)');
            }
        });
    </script>
</body>
</html>