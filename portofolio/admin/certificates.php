<?php
require_once '../config.php';

if(!isLoggedIn()) {
    redirect('login.php');
}

$success = '';
$error = '';

// Handle Delete
if(isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $query = "SELECT photo FROM certificates WHERE id = $id";
    $result = $conn->query($query);
    if($result->num_rows > 0) {
        $cert = $result->fetch_assoc();
        $file_path = '../' . UPLOAD_DIR . $cert['photo'];
        if(file_exists($file_path)) {
            unlink($file_path);
        }
        $conn->query("DELETE FROM certificates WHERE id = $id");
        $success = 'Sertifikat berhasil dihapus!';
    }
}

// Handle Add/Edit
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $title = clean($_POST['title']);
    $issued_by = clean($_POST['issued_by']);
    $issue_date = clean($_POST['issue_date']);
    
    // Handle file upload
    if(isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['photo']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if(in_array($ext, $allowed)) {
            $new_filename = uniqid() . '.' . $ext;
            $upload_path = '../' . UPLOAD_DIR . $new_filename;
            
            if(!is_dir('../' . UPLOAD_DIR)) {
                mkdir('../' . UPLOAD_DIR, 0777, true);
            }
            
            if(move_uploaded_file($_FILES['photo']['tmp_name'], $upload_path)) {
                if($id > 0) {
                    // Delete old photo
                    $old_query = "SELECT photo FROM certificates WHERE id = $id";
                    $old_result = $conn->query($old_query);
                    if($old_result->num_rows > 0) {
                        $old_cert = $old_result->fetch_assoc();
                        $old_file = '../' . UPLOAD_DIR . $old_cert['photo'];
                        if(file_exists($old_file)) {
                            unlink($old_file);
                        }
                    }
                    
                    $query = "UPDATE certificates SET title='$title', issued_by='$issued_by', issue_date='$issue_date', photo='$new_filename' WHERE id=$id";
                    $success = 'Sertifikat berhasil diupdate!';
                } else {
                    $query = "INSERT INTO certificates (title, issued_by, issue_date, photo) VALUES ('$title', '$issued_by', '$issue_date', '$new_filename')";
                    $success = 'Sertifikat berhasil ditambahkan!';
                }
                $conn->query($query);
            } else {
                $error = 'Gagal mengupload file!';
            }
        } else {
            $error = 'Format file tidak didukung!';
        }
    } elseif($id > 0) {
        // Update without changing photo
        $query = "UPDATE certificates SET title='$title', issued_by='$issued_by', issue_date='$issue_date' WHERE id=$id";
        $conn->query($query);
        $success = 'Sertifikat berhasil diupdate!';
    } else {
        $error = 'Silakan upload foto sertifikat!';
    }
}

// Get all certificates
$certificates = $conn->query("SELECT * FROM certificates ORDER BY created_at DESC");

// Get certificate for editing
$edit_cert = null;
if(isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $edit_result = $conn->query("SELECT * FROM certificates WHERE id = $edit_id");
    if($edit_result->num_rows > 0) {
        $edit_cert = $edit_result->fetch_assoc();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Sertifikat</title>
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
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .form-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: 500;
        }
        input[type="text"],
        input[type="date"],
        input[type="file"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        input:focus {
            outline: none;
            border-color: #667eea;
        }
        .btn {
            padding: 12px 25px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: transform 0.3s;
        }
        .btn:hover {
            transform: translateY(-2px);
        }
        .btn-cancel {
            background: #6c757d;
            margin-left: 10px;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .table-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            background: #667eea;
            color: white;
            padding: 15px;
            text-align: left;
        }
        td {
            padding: 15px;
            border-bottom: 1px solid #ddd;
        }
        tr:hover {
            background: #f8f9fa;
        }
        .cert-thumb {
            width: 80px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
        }
        .action-btn {
            padding: 8px 15px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 12px;
            margin-right: 5px;
            display: inline-block;
        }
        .btn-edit {
            background: #ffc107;
            color: #000;
        }
        .btn-delete {
            background: #dc3545;
            color: white;
        }
        .btn-delete:hover {
            background: #c82333;
        }
        h2 {
            color: #667eea;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Kelola Sertifikat</h1>
        <a href="dashboard.php">← Kembali ke Dashboard</a>
    </div>

    <div class="container">
        <?php if($success): ?>
        <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if($error): ?>
        <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="form-container">
            <h2><?php echo $edit_cert ? 'Edit Sertifikat' : 'Tambah Sertifikat Baru'; ?></h2>
            <form method="POST" enctype="multipart/form-data">
                <?php if($edit_cert): ?>
                <input type="hidden" name="id" value="<?php echo $edit_cert['id']; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label>Judul Sertifikat *</label>
                    <input type="text" name="title" value="<?php echo $edit_cert ? htmlspecialchars($edit_cert['title']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Diterbitkan Oleh *</label>
                    <input type="text" name="issued_by" value="<?php echo $edit_cert ? htmlspecialchars($edit_cert['issued_by']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Tanggal Terbit *</label>
                    <input type="date" name="issue_date" value="<?php echo $edit_cert ? $edit_cert['issue_date'] : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Foto Sertifikat <?php echo $edit_cert ? '' : '*'; ?></label>
                    <input type="file" name="photo" accept="image/*" <?php echo $edit_cert ? '' : 'required'; ?>>
                    <?php if($edit_cert): ?>
                    <small style="color: #888;">Kosongkan jika tidak ingin mengubah foto</small><br>
                    <img src="../<?php echo UPLOAD_DIR . $edit_cert['photo']; ?>" style="width: 200px; margin-top: 10px; border-radius: 5px;">
                    <?php endif; ?>
                </div>
                
                <button type="submit" class="btn">
                    <?php echo $edit_cert ? 'Update Sertifikat' : 'Tambah Sertifikat'; ?>
                </button>
                <?php if($edit_cert): ?>
                <a href="certificates.php" class="btn btn-cancel" style="text-decoration: none; display: inline-block;">Batal</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="table-container">
            <h2>Daftar Sertifikat</h2>
            <?php if($certificates->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Foto</th>
                        <th>Judul</th>
                        <th>Diterbitkan Oleh</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($cert = $certificates->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <img src="../<?php echo UPLOAD_DIR . $cert['photo']; ?>" class="cert-thumb" alt="<?php echo htmlspecialchars($cert['title']); ?>">
                        </td>
                        <td><?php echo htmlspecialchars($cert['title']); ?></td>
                        <td><?php echo htmlspecialchars($cert['issued_by']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($cert['issue_date'])); ?></td>
                        <td>
                            <a href="?edit=<?php echo $cert['id']; ?>" class="action-btn btn-edit">Edit</a>
                            <a href="?delete=<?php echo $cert['id']; ?>" class="action-btn btn-delete" onclick="return confirm('Yakin ingin menghapus sertifikat ini?')">Hapus</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p style="text-align: center; color: #888; padding: 20px;">Belum ada sertifikat.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>