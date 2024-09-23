<?php
include 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $category_id = $_POST['category_id'];
    $title = $_POST['title'];
    $content = $_POST['content'];

    $sql = "INSERT INTO topics (category_id, user_id, title) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $category_id, $_SESSION['user_id'], $title);
    $stmt->execute();
    $topic_id = $stmt->insert_id;
    $stmt->close();

    $sql = "INSERT INTO posts (topic_id, user_id, content) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $topic_id, $_SESSION['user_id'], $content);
    $stmt->execute();
    $stmt->close();

    header("Location: topic.php?id=" . $topic_id);
    exit();
}

$sql = "SELECT id, name FROM categories";
$result = $conn->query($sql);

ob_start();
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <h1 class="mb-4"><i class="fas fa-plus"></i> Create New Topic</h1>
        <form method="post">
            <div class="mb-3">
                <label for="category_id" class="form-label">Category</label>
                <select class="form-select" id="category_id" name="category_id" required>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="title" class="form-label">Title</label>
                <input type="text" class="form-control" id="title" name="title" required>
            </div>
            <div class="mb-3">
                <label for="content" class="form-label">Content</label>
                <textarea class="form-control" id="content" name="content" rows="5" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Create Topic</button>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
include 'layout.php';
?>