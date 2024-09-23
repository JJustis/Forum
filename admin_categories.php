<?php
include 'config.php';
session_start();

// Check if user is admin (you need to implement this logic)
if (!isset($_SESSION['user_id']) || !is_admin($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Handle category creation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'create') {
        $name = $_POST['name'];
        $description = $_POST['description'];
        
        $sql = "INSERT INTO categories (name, description) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $name, $description);
        $stmt->execute();
        $stmt->close();
    } elseif ($_POST['action'] == 'delete' && isset($_POST['category_id'])) {
        $category_id = $_POST['category_id'];
        
        $sql = "DELETE FROM categories WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $category_id);
        $stmt->execute();
        $stmt->close();
    }
}

// Fetch all categories
$sql = "SELECT * FROM categories";
$result = $conn->query($sql);

ob_start();
?>

<h1 class="mb-4"><i class="fas fa-cogs"></i> Manage Categories</h1>

<h2>Create New Category</h2>
<form method="post" class="mb-4">
    <input type="hidden" name="action" value="create">
    <div class="mb-3">
        <label for="name" class="form-label">Category Name</label>
        <input type="text" class="form-control" id="name" name="name" required>
    </div>
    <div class="mb-3">
        <label for="description" class="form-label">Description</label>
        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
    </div>
    <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Create Category</button>
</form>

<h2>Existing Categories</h2>
<table class="table table-striped">
    <thead>
        <tr>
            <th>Name</th>
            <th>Description</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['name']; ?></td>
                <td><?php echo $row['description']; ?></td>
                <td>
                    <form method="post" onsubmit="return confirm('Are you sure you want to delete this category?');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="category_id" value="<?php echo $row['id']; ?>">
                        <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i> Delete</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<?php
$content = ob_get_clean();
include 'layout.php';

// Helper function to check if a user is an admin
function is_admin($user_id) {
    // Implement your admin check logic here
    // For example, you could have an 'is_admin' column in the users table
    global $conn;
    $sql = "SELECT is_admin FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    return $user['is_admin'] == 1;
}
?>