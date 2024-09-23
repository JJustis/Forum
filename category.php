<?php
include 'config.php';
session_start();

$category_id = $_GET['id'];

// Fetch category details
$cat_sql = "SELECT name, description FROM categories WHERE id = ?";
$cat_stmt = $conn->prepare($cat_sql);
$cat_stmt->bind_param("i", $category_id);
$cat_stmt->execute();
$category = $cat_stmt->get_result()->fetch_assoc();
$cat_stmt->close();

// Fetch topics in this category
$topic_sql = "SELECT t.id, t.title, t.created_at, u.username, 
              (SELECT COUNT(*) FROM posts WHERE topic_id = t.id) as post_count
              FROM topics t 
              JOIN users u ON t.user_id = u.id 
              WHERE t.category_id = ?
              ORDER BY t.created_at DESC";
$topic_stmt = $conn->prepare($topic_sql);
$topic_stmt->bind_param("i", $category_id);
$topic_stmt->execute();
$topics = $topic_stmt->get_result();
$topic_stmt->close();

ob_start();
?>

<h1 class="mb-4"><i class="fas fa-folder-open"></i> <?php echo $category['name']; ?></h1>
<p class="mb-4"><?php echo $category['description']; ?></p>

<table class="table table-striped">
    <thead>
        <tr>
            <th>Topic</th>
            <th>Author</th>
            <th>Posts</th>
            <th>Created</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($topic = $topics->fetch_assoc()): ?>
            <tr>
                <td><a href="topic.php?id=<?php echo $topic['id']; ?>"><?php echo $topic['title']; ?></a></td>
                <td><?php echo $topic['username']; ?></td>
                <td><?php echo $topic['post_count']; ?></td>
                <td><?php echo date('M j, Y', strtotime($topic['created_at'])); ?></td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<?php if (isset($_SESSION['user_id'])): ?>
    <a href="create_topic.php?category_id=<?php echo $category_id; ?>" class="btn btn-primary"><i class="fas fa-plus"></i> Create New Topic</a>
<?php else: ?>
    <div class="alert alert-info" role="alert">
        <i class="fas fa-info-circle"></i> Please <a href="login.php">login</a> or <a href="register.php">register</a> to create a topic
    </div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include 'layout.php';
?>