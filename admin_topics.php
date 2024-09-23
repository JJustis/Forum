<?php
include 'config.php';

session_start();

if (!isset($_SESSION['user_id']) || !is_admin($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST["action"];
    $topic_id = $_POST["topic_id"];

    if ($action == "delete") {
        $sql = "DELETE FROM topics WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $topic_id);
        if ($stmt->execute()) {
            $success_message = "Topic deleted successfully.";
        } else {
            $error_message = "Error deleting topic: " . $conn->error;
        }
    } elseif ($action == "sticky") {
        $sql = "UPDATE topics SET is_sticky = 1 WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $topic_id);
        if ($stmt->execute()) {
            $success_message = "Topic marked as sticky.";
        } else {
            $error_message = "Error marking topic as sticky: " . $conn->error;
        }
    } elseif ($action == "unsticky") {
        $sql = "UPDATE topics SET is_sticky = 0 WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $topic_id);
        if ($stmt->execute()) {
            $success_message = "Topic unmarked as sticky.";
        } else {
            $error_message = "Error unmarking topic as sticky: " . $conn->error;
        }
    }
}

$sql = "SELECT t.id, t.title, t.is_sticky, c.name as category_name, u.username 
        FROM topics t
        JOIN categories c ON t.category_id = c.id
        JOIN users u ON t.user_id = u.id
        ORDER BY t.is_sticky DESC, t.created_at DESC";
$result = $conn->query($sql);

if ($result === false) {
    $error_message = "Error fetching topics: " . $conn->error;
} else {
    $topics = $result->fetch_all(MYSQLI_ASSOC);
}

ob_start();
?>

<h1 class="mb-4"><i class="fas fa-comments"></i> Manage Topics</h1>

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

<?php if (isset($topics)): ?>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Title</th>
                <th>Category</th>
                <th>Creator</th>
                <th>Sticky</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($topics as $topic): ?>
                <tr>
                    <td><a href="topic.php?id=<?php echo $topic['id']; ?>"><?php echo $topic['title']; ?></a></td>
                    <td><?php echo $topic['category_name']; ?></td>
                    <td><a href="user_profile.php?id=<?php echo $topic['user_id']; ?>"><?php echo $topic['username']; ?></a></td>
                    <td><?php echo $topic['is_sticky'] ? 'Yes' : 'No'; ?></td>
                    <td>
                        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" class="form-inline">
                            <input type="hidden" name="topic_id" value="<?php echo $topic['id']; ?>">
                            <button type="submit" name="action" value="delete" class="btn btn-danger btn-sm mr-2" onclick="return confirm('Are you sure you want to delete this topic?');">
                                <i class="fas fa-trash"></i>
                            </button>
                            <?php if ($topic['is_sticky']): ?>
                                <button type="submit" name="action" value="unsticky" class="btn btn-warning btn-sm">
                                    <i class="fas fa-thumbtack"></i> Unmark as Sticky
                                </button>
                            <?php else: ?>
                                <button type="submit" name="action" value="sticky" class="btn btn-primary btn-sm">
                                    <i class="fas fa-thumbtack"></i> Mark as Sticky
                                </button>
                            <?php endif; ?>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

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