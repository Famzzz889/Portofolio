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
    $conn->query("DELETE FROM experiences WHERE id = $id");
    $success = 'Pengalaman kerja berhasil dihapus!';
}

// Handle Add/Edit
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $company = clean($_POST['company']);
    $position = clean($_POST['position']);
    $start_date = clean($_POST['start_date']);
    $end_date = isset($_POST['end_date']) ? clean($_POST['end_date']) : null;
    $is_current = isset($_POST['is_current']) ? 1 : 0;
    $description = clean($_POST['description']);
    
    if($id > 0) {
        $query = "UPDATE experiences SET company='$company', position='$position', start_date='$start_date', end_date=" . ($end_date ? "'$end_date'" : "NULL") . ", is_current=$is_current, description='$description' WHERE id=$id";
        $success = 'Pengalaman kerja berhasil diupdate!';
    } else {
        $query = "INSERT INTO experiences (company, position, start_date, end_date, is_current, description) VALUES ('$company', '$position', '$start_date', " . ($end_date ? "'$end_date'" : "NULL") . ", $is_current, '$description')";
        $success = 'Pengalaman kerja berhasil ditambahkan!';
    }
    $conn->query($query);
}

// Get all experiences
$experiences = $conn->query("SELECT * FROM experiences ORDER BY start_date DESC");

// Get experience for editing
$edit_exp = null;
if(isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $edit_result = $conn->query("SELECT * FROM experiences WHERE id = $edit_id");
    if($edit_result->num_rows > 0) {
        $edit_exp = $edit_result->fetch_assoc();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pengalaman Kerja</title>
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
        textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            font-family: inherit;
        }
        textarea {
            min-height: 100px;
            resize: vertical;
        }
        input:focus, textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        .checkbox-group {
            display: flex;
            align-items: center;
        }
        .checkbox-group input[type="checkbox"] {
            width: auto;
            margin-right: 10px;
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
        .list-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        .exp-item {
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 15px;
            position: relative;
        }
        .exp-item h3 {
            color: #667eea;
            margin-bottom: 5px;
        }
        .exp-item .company {
            font-weight: bold;
            color: #555;
            margin-bottom: 5px;
        }
        .exp-item .date {
            color: #888;
            font-size: 14px;
            margin-bottom: 10px;
        }
        .exp-actions {
            position: absolute;
            top: 20px;
            right: 20px;
        }
        .action-btn {
            padding: 8px 15px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 12px;
            margin-left: 5px;
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
    <script>
        function toggleEndDate() {
            const checkbox = document.getElementById('is_current');
            const endDateInput = document.getElementById('end_date');
            endDateInput.disabled = checkbox.checked;
            if(checkbox.checked) {
                endDateInput.value = '';
            }
        }
    </script>
</head>
<body>
    <div class="header">
        <h1>Kelola Pengalaman Kerja</h1>
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
            <h2><?php echo $edit_exp ? 'Edit Pengalaman Kerja' : 'Tambah Pengalaman Kerja Baru'; ?></h2>
            <form method="POST">
                <?php if($edit_exp): ?>
                <input type="hidden" name="id" value="<?php echo $edit_exp['id']; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label>Nama Perusahaan *</label>
                    <input type="text" name="company" value="<?php echo $edit_exp ? htmlspecialchars($edit_exp['company']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Posisi/Jabatan *</label>
                    <input type="text" name="position" value="<?php echo $edit_exp ? htmlspecialchars($edit_exp['position']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Tanggal Mulai *</label>
                    <input type="date" name="start_date" value="<?php echo $edit_exp ? $edit_exp['start_date'] : ''; ?>" required>
                </div>
                
                <div class="form-group checkbox-group">
                    <input type="checkbox" name="is_current" id="is_current" value="1" <?php echo ($edit_exp && $edit_exp['is_current']) ? 'checked' : ''; ?> onchange="toggleEndDate()">
                    <label for="is_current" style="margin-bottom: 0;">Masih bekerja di sini</label>
                </div>
                
                <div class="form-group">
                    <label>Tanggal Selesai</label>
                    <input type="date" name="end_date" id="end_date" value="<?php echo ($edit_exp && !$edit_exp['is_current']) ? $edit_exp['end_date'] : ''; ?>" <?php echo ($edit_exp && $edit_exp['is_current']) ? 'disabled' : ''; ?>>
                </div>
                
                <div class="form-group">
                    <label>Deskripsi Pekerjaan</label>
                    <textarea name="description" placeholder="Jelaskan tugas dan tanggung jawab Anda..."><?php echo $edit_exp ? htmlspecialchars($edit_exp['description']) : ''; ?></textarea>
                </div>
                
                <button type="submit" class="btn">
                    <?php echo $edit_exp ? 'Update Pengalaman' : 'Tambah Pengalaman'; ?>
                </button>
                <?php if($edit_exp): ?>
                <a href="experiences.php" class="btn btn-cancel" style="text-decoration: none; display: inline-block;">Batal</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="list-container">
            <h2>Daftar Pengalaman Kerja</h2>
            <?php if($experiences->num_rows > 0): ?>
                <?php while($exp = $experiences->fetch_assoc()): ?>
                <div class="exp-item">
                    <div class="exp-actions">
                        <a href="?edit=<?php echo $exp['id']; ?>" class="action-btn btn-edit">Edit</a>
                        <a href="?delete=<?php echo $exp['id']; ?>" class="action-btn btn-delete" onclick="return confirm('Yakin ingin menghapus pengalaman ini?')">Hapus</a>
                    </div>
                    <h3><?php echo htmlspecialchars($exp['position']); ?></h3>
                    <div class="company"><?php echo htmlspecialchars($exp['company']); ?></div>
                    <div class="date">
                        <?php echo date('M Y', strtotime($exp['start_date'])); ?> - 
                        <?php echo $exp['is_current'] ? 'Sekarang' : date('M Y', strtotime($exp['end_date'])); ?>
                    </div>
                    <p><?php echo nl2br(htmlspecialchars($exp['description'])); ?></p>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
            <p style="text-align: center; color: #888; padding: 20px;">Belum ada pengalaman kerja.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>