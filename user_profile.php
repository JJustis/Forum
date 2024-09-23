<?php
include 'config.php';
session_start();

$user_id = $_GET['id'];

// Fetch user data
$sql = "SELECT username, avatar, signature, created_at FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Fetch user's recent posts
$post_sql = "SELECT p.content, p.created_at, t.id as topic_id, t.title as topic_title
             FROM posts p
             JOIN topics t ON p.topic_id = t.id
             WHERE p.user_id = ?
             ORDER BY p.created_at DESC
             LIMIT 5";
$post_stmt = $conn->prepare($post_sql);
$post_stmt->bind_param("i", $user_id);
$post_stmt->execute();
$recent_posts = $post_stmt->get_result();
$post_stmt->close();

ob_start();
?>

<div class="row">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <img src="<?php echo $user['avatar'] ? $user['avatar'] : 'https://via.placeholder.com/150'; ?>" alt="Avatar" class="img-fluid rounded-circle mb-3" style="max-width: 150px;">
                <h4><?php echo $user['username']; ?></h4>
                <p>Member since: <?php echo date('M j, Y', strtotime($user['created_at'])); ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-9">
        <h1 class="mb-4"><i class="fas fa-user"></i> User Profile</h1>
        
        <?php if (!empty($user['signature'])): ?>
            <h3>Signature</h3>
            <div class="card mb-4">
                <div class="card-body">
                    <?php echo nl2br(htmlspecialchars($user['signature'])); ?>
                </div>
            </div>
        <?php endif; ?>

        <h3>Recent Posts</h3>
        <?php while ($post = $recent_posts->fetch_assoc()): ?>
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title"><a href="topic.php?id=<?php echo $post['topic_id']; ?>"><?php echo $post['topic_title']; ?></a></h5>
                    <p class="card-text"><?php echo substr(strip_tags($post['content']), 0, 200) . '...'; ?></p>
                    <p class="card-text"><small class="text-muted">Posted on <?php echo date('M j, Y g:i A', strtotime($post['created_at'])); ?></small></p>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
include 'layout.php';
?>