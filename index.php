<?php
include 'config.php';
session_start();

// Get online users (active in the last 15 minutes)
$online_users_sql = "SELECT username, avatar FROM users WHERE last_activity > DATE_SUB(NOW(), INTERVAL 15 MINUTE)";
$online_users_result = $conn->query($online_users_sql);
$online_users = $online_users_result->fetch_all(MYSQLI_ASSOC);

$sql = "SELECT * FROM categories";
$result = $conn->query($sql);

ob_start();
?>

<div class="row">
    <div class="col-md-9">
        <h1 class="mb-4"><i class="fas fa-list"></i> Categories</h1>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="card mb-3">
                <div class="card-body">
                    <h2 class="card-title"><a href="category.php?id=<?php echo $row['id']; ?>"><?php echo $row['name']; ?></a></h2>
                    <p class="card-text"><?php echo $row['description']; ?></p>
                    <?php
                    $topic_sql = "SELECT t.id, t.title, u.username, t.created_at 
                                  FROM topics t 
                                  JOIN users u ON t.user_id = u.id 
                                  WHERE t.category_id = " . $row['id'] . "
                                  ORDER BY t.created_at DESC
                                  LIMIT 5";
                    $topic_result = $conn->query($topic_sql);
                    ?>
                    <ul class="list-group list-group-flush">
                        <?php while ($topic = $topic_result->fetch_assoc()): ?>
                            <li class="list-group-item">
                                <a href="topic.php?id=<?php echo $topic['id']; ?>"><?php echo $topic['title']; ?></a>
                                <small class="text-muted">
                                    by <a href="user_profile.php?id=<?php echo $topic['id']; ?>"><?php echo $topic['username']; ?></a> on <?php echo date('M j, Y', strtotime($topic['created_at'])); ?>
                                </small>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                </div>
            </div>
        <?php endwhile; ?>

        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="create_topic.php" class="btn btn-primary"><i class="fas fa-plus"></i> Create New Topic</a>
        <?php else: ?>
            <div class="alert alert-info" role="alert">
                <i class="fas fa-info-circle"></i> Please <a href="login.php">login</a> or <a href="register.php">register</a> to create a topic
            </div>
        <?php endif; ?>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-users"></i> Online Users</h3>
            </div>
            <div class="card-body">
                <ul class="list-unstyled">
                    <?php foreach ($online_users as $user): ?>
                        <li>
                            <a href="user_profile.php?id=<?php echo $topic['id']; ?>">
                                <img src="<?php echo $user['avatar'] ? $user['avatar'] : 'https://via.placeholder.com/30'; ?>" alt="Avatar" class="img-fluid rounded-circle mr-2" style="width: 30px;">
                                <?php echo $user['username']; ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <?php if (isset($_SESSION['user_id']) && is_admin($_SESSION['user_id'])): ?>
            <div class="card mt-4">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-cogs"></i> Admin Tools</h3>
                </div>
                <div class="card-body">
                    <a href="admin_categories.php" class="btn btn-primary btn-block mb-2"><i class="fas fa-folder-open"></i> Manage Categories</a>
                    <a href="admin_users.php" class="btn btn-primary btn-block mb-2"><i class="fas fa-users"></i> Manage Users</a>
                    <a href="admin_seo.php" class="btn btn-primary btn-block mb-2"><i class="fas fa-chart-line"></i> Site-wide SEO Settings</a>
					<a href="admin_topics.php" class="btn btn-primary btn-block mb-2"><i class="fas fa-comments"></i> Manage Topics</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

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