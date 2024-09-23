<?php
include 'config.php';
session_start();

if (!isset($_SESSION['user_id']) || !is_admin($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST["action"];
    $user_id = $_POST["user_id"];

    if ($action == "delete") {
        $sql = "DELETE FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            $success_message = "User deleted successfully.";
        } else {
            $error_message = "Error deleting user: " . $conn->error;
        }
    } elseif ($action == "promote") {
        $sql = "UPDATE users SET is_admin = 1 WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            $success_message = "User promoted to admin successfully.";
        } else {
            $error_message = "Error promoting user: " . $conn->error;
        }
    } elseif ($action == "demote") {
        $sql = "UPDATE users SET is_admin = 0 WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            $success_message = "User demoted from admin successfully.";
        } else {
            $error_message = "Error demoting user: " . $conn->error;
        }
    }
}

$sql = "SELECT id, username, email, is_admin, created_at FROM users";
$result = $conn->query($sql);
$users = $result->fetch_all(MYSQLI_ASSOC);

ob_start();
?>

<h1 class="mb-4"><i class="fas fa-users"></i> Manage Users</h1>

<?php if (isset($success_message)): ?>
    <div class="alert alert-success" role="alert">
        <?php echo $success_message; ?>
    </div>
<?php endif; ?>

<?php if (isset($error_message)): ?>
    <div class="alert alert-danger" role="alert">
        <?php echo $error_message; ?>
    </div>
<?php endif; ?>

<table class="table table-striped">
    <thead>
        <tr>
            <th>Username</th>
            <th>Email</th>
            <th>Admin</th>
            <th>Registered</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><a href="user_profile.php?id=<?php echo $user['id']; ?>"><?php echo $user['username']; ?></a></td>
                <td><?php echo $user['email']; ?></td>
                <td><?php echo $user['is_admin'] ? 'Yes' : 'No'; ?></td>
                <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                <td>
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" class="form-inline">
                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                        <button type="submit" name="action" value="delete" class="btn btn-danger btn-sm mr-2" onclick="return confirm('Are you sure you want to delete this user?');">
                            <i class="fas fa-trash"></i>
                        </button>
                        <?php if ($user['is_admin']): ?>
                            <button type="submit" name="action" value="demote" class="btn btn-warning btn-sm mr-2">
                                <i class="fas fa-user-slash"></i> Demote
                            </button>
                        <?php else: ?>
                            <button type="submit" name="action" value="promote" class="btn btn-success btn-sm mr-2">
                                <i class="fas fa-user-shield"></i> Promote
                            </button>
                        <?php endif; ?>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php
$content = ob_get_clean();
include 'layout.php';

function is_admin($user_id) {
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