<?php  
include 'db.php';

// Tambah tugas
if (isset($_POST['add'])) {
    $task = $_POST['task'];
    $deadline = $_POST['deadline'];
    $conn->query("INSERT INTO todos (task, deadline) VALUES ('$task', '$deadline')");
    header("Location: index.php");
}

// Update tugas
if (isset($_POST['update'])) {
    $id = intval($_POST['id']);
    $task = $conn->real_escape_string($_POST['task']);
    $deadline = $conn->real_escape_string($_POST['deadline']);

    if (!empty($task) && !empty($deadline)) {
        $stmt = $conn->prepare("UPDATE todos SET task=?, deadline=? WHERE id=?");
        $stmt->bind_param("ssi", $task, $deadline, $id);
        $stmt->execute();
        $stmt->close();
        header("Location: index.php");
        exit;
    }
}

// Hapus tugas
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM todos WHERE id=$id");
    header("Location: index.php");
}

// Tandai selesai / belum selesai
if (isset($_GET['status']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $status = $_GET['status'] == 'completed' ? 'completed' : 'pending';
    $conn->query("UPDATE todos SET status='$status' WHERE id=$id");
    header("Location: index.php");
    exit;
}

// Pencarian tugas
$searchQuery = '';
if (isset($_GET['search'])) {
    $search = $_GET['search'];
    $searchQuery = "WHERE task LIKE '%$search%' OR deadline LIKE '%$search%'";
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 5;
$offset = ($page - 1) * $limit;
$totalTasks = $conn->query("SELECT COUNT(*) as count FROM todos $searchQuery")->fetch_assoc()['count'];
$totalPages = ceil($totalTasks / $limit);

$result = $conn->query("SELECT * FROM todos $searchQuery ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <link rel="shorcut icon" type="x-icon" href="../todolist_db/asset/img/todo.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To-Do List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../todolist_db/asset/css/style.css">
</head>
<body>

<!-- Navigasi -->
<nav class="navbar navbar-expand-lg bg-light">
    <div class="container-fluid">
        <a class="navbar-brand" href="#"><b>To-Do List</b></a>
        <div class="collapse navbar-collapse justify-content-end">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="login.php">
                        <i class="bi bi-person-circle"></i> Login
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <h2 class="text-center">To-Do List</h2>

    <!-- Form Tambah dan Pencarian -->
    <div class="d-flex align-items-center mb-3">
        <button class="btn btn-success me-2" onclick="openAddTaskModal()">+ Tambah Tugas</button>
        <form method="GET" class="d-flex flex-grow-1">
            <input type="text" name="search" class="form-control me-2" placeholder="Cari tugas atau jangka waktu..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
            <button type="submit" class="btn btn-outline-success">
                <i class="bi bi-search"></i>
            </button>
        </form>
    </div>

    <!-- Tabel Tugas -->
    <table class="table table-striped mt-4">
        <thead>
            <tr>
                <th>No</th>
                <th>Tugas</th>
                <th>Jangka Waktu</th>
                <th>Keterangan</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result->num_rows > 0) {
                $no = $offset + 1;
                while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= htmlspecialchars($row['task']) ?></td>
                        <td><?= htmlspecialchars($row['deadline']) ?></td>
                        <td>
                            <input type="checkbox" id="taskStatus<?= $row['id'] ?>" 
                                <?= $row['status'] == 'completed' ? 'checked' : '' ?>
                                onchange="updateTaskStatus(<?= $row['id'] ?>, this.checked)">
                            <label for="taskStatus<?= $row['id'] ?>"><?= $row['status'] == 'completed' ? 'Selesai' : 'Belum Selesai' ?></label>
                        </td>

                        <td>
                            <button class="btn btn-sm btn-warning" onclick="editTask(<?= $row['id'] ?>, '<?= $row['task'] ?>', '<?= $row['deadline'] ?>')" data-bs-toggle="modal" data-bs-target="#editModal">
                                <i class="bi bi-pencil"></i> Edit
                            </button>
                            <a href="?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus tugas ini?')">
                                <i class="bi bi-trash"></i> Hapus
                            </a>
                        </td>
                    </tr>
                <?php }
            } else { ?>
                <tr>
                    <td colspan="5" class="text-center">Tidak ada data ditemukan</td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <!-- Pagination -->
    
        <ul class="pagination justify-content-center">
            <?php if ($page > 1): ?>
                <li class="page-item"><a class="page-link" href="?page=<?= $page - 1 ?>"><i class="bi bi-arrow-left"></i></a></li>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= ($i == $page) ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a></li>
            <?php endfor; ?>
            <?php if ($page < $totalPages): ?>
                <li class="page-item"><a class="page-link" href="?page=<?= $page + 1 ?>"><i class="bi bi-arrow-right"></i></a></li>
            <?php endif; ?>
        </ul>
    
</div>

<!-- Modal Tambah Tugas -->
<div class="modal fade" id="addTaskModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Tugas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="text" name="task" class="form-control" placeholder="Nama Tugas" required>
                    <input type="date" name="deadline" class="form-control mt-2" required>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="add" class="btn btn-primary">Tambah</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Tugas -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Tugas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id" id="editId">
                    <input type="text" name="task" id="editTask" class="form-control" required>
                    <input type="date" name="deadline" id="editDeadline" class="form-control mt-2" required>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="update" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../todolist_db/asset/javaScript/script.js">

</script>
</body>
</html>