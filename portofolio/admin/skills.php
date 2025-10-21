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
    $conn->query("DELETE FROM skills WHERE id = $id");
    $success = 'Skill berhasil dihapus!';
}

// Handle Add/Edit
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $skill_name = clean($_POST['skill_name']);
    $level = intval($_POST['level']);
    $category = clean($_POST['category']);
    
    if($id > 0) {
        $query = "UPDATE skills SET skill_name='$skill_name', level=$level, category='$category' WHERE id=$id";
        $success = 'Skill berhasil diupdate!';
    } else {
        $query = "INSERT INTO skills (skill_name, level, category) VALUES ('$skill_name', $level, '$category')";
        $success = 'Skill berhasil ditambahkan!';
    }
    $conn->query($query);
}

// Get all skills
$skills = $conn->query("SELECT * FROM skills ORDER BY category, skill_name");

// Get skill for editing
$edit_skill = null;
if(isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $edit_result = $conn->query("SELECT * FROM skills WHERE id = $edit_id");
    if($edit_result->num_rows > 0) {
        $edit_skill = $edit_result->fetch_assoc();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Skills</title>
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
        input[type="number"],
        input[type="range"],
        select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        input:focus, select:focus {
            outline: none;
            border-color: #667eea;
        }
        .range-container {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .range-container input[type="range"] {
            flex: 1;
        }
        .range-value {
            font-size: 18px;
            font-weight: bold;
            color: #667eea;
            min-width: 50px;
            text-align: center;
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
        .skill-bar {
            background: #e0e0e0;
            height: 20px;
            border-radius: 10px;
            overflow: hidden;
            width: 200px;
        }
        .skill-progress {
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 12px;
            font-weight: bold;
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
    <script>
        function updateRangeValue(val) {
            document.getElementById('level_value').textContent = val + '%';
        }
    </script>
</head>
<body>
    <div class="header">
        <h1>Kelola Skills</h1>
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
            <h2><?php echo $edit_skill ? 'Edit Skill' : 'Tambah Skill Baru'; ?></h2>
            <form method="POST">
                <?php if($edit_skill): ?>
                <input type="hidden" name="id" value="<?php echo $edit_skill['id']; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label>Nama Skill *</label>
                    <input type="text" name="skill_name" value="<?php echo $edit_skill ? htmlspecialchars($edit_skill['skill_name']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Kategori</label>
                    <input type="text" name="category" value="<?php echo $edit_skill ? htmlspecialchars($edit_skill['category']) : ''; ?>" placeholder="Contoh: Programming, Design, Database, dll">
                </div>
                
                <div class="form-group">
                    <label>Level Kemampuan (0-100%)</label>
                    <div class="range-container">
                        <input type="range" name="level" min="0" max="100" value="<?php echo $edit_skill ? $edit_skill['level'] : 50; ?>" oninput="updateRangeValue(this.value)" required>
                        <span class="range-value" id="level_value"><?php echo $edit_skill ? $edit_skill['level'] : 50; ?>%</span>
                    </div>
                </div>
                
                <button type="submit" class="btn">
                    <?php echo $edit_skill ? 'Update Skill' : 'Tambah Skill'; ?>
                </button>
                <?php if($edit_skill): ?>
                <a href="skills.php" class="btn btn-cancel" style="text-decoration: none; display: inline-block;">Batal</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="table-container">
            <h2>Daftar Skills</h2>
            <?php if($skills->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Nama Skill</th>
                        <th>Kategori</th>
                        <th>Level</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($skill = $skills->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($skill['skill_name']); ?></td>
                        <td><?php echo htmlspecialchars($skill['category']); ?></td>
                        <td>
                            <div class="skill-bar">
                                <div class="skill-progress" style="width: <?php echo $skill['level']; ?>%">
                                    <?php echo $skill['level']; ?>%
                                </div>
                            </div>
                        </td>
                        <td>
                            <a href="?edit=<?php echo $skill['id']; ?>" class="action-btn btn-edit">Edit</a>
                            <a href="?delete=<?php echo $skill['id']; ?>" class="action-btn btn-delete" onclick="return confirm('Yakin ingin menghapus skill ini?')">Hapus</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p style="text-align: center; color: #888; padding: 20px;">Belum ada skill.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>