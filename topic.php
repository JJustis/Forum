<?php
include 'config.php';
session_start();

$topic_id = $_GET['id'];

$sql = "SELECT t.title, t.created_at, u.username 
        FROM topics t 
        JOIN users u ON t.user_id = u.id 
        WHERE t.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $topic_id);
$stmt->execute();
$result = $stmt->get_result();
$topic = $result->fetch_assoc();

$sql = "SELECT p.content, p.created_at, u.username, u.avatar, u.signature 
        FROM posts p 
        JOIN users u ON p.user_id = u.id 
        WHERE p.topic_id = ?
        ORDER BY p.created_at ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $topic_id);
$stmt->execute();
$result = $stmt->get_result();

ob_start();
?>

<h1 class="mb-4"><i class="fas fa-comments"></i> <?php echo $topic['title']; ?></h1>
<p class="text-muted">
    Created by <?php echo $topic['username']; ?> on <?php echo date('M j, Y', strtotime($topic['created_at'])); ?>
</p>

<?php while ($row = $result->fetch_assoc()): ?>
    <div class="card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-2 text-center">
                    <img src="<?php echo $row['avatar'] ? $row['avatar'] : 'https://via.placeholder.com/100'; ?>" alt="Avatar" class="img-fluid rounded-circle mb-2" style="max-width: 100px;">
                    <p><?php echo $row['username']; ?></p>
                </div>
                <div class="col-md-10">
                    <p class="card-text"><?php echo nl2br(htmlspecialchars($row['content'])); ?></p>
                    <?php if (!empty($row['signature'])): ?>
                        <hr>
                        <p class="card-text"><small><?php echo nl2br(htmlspecialchars($row['signature'])); ?></small></p>
                    <?php endif; ?>
                    <p class="card-text"><small class="text-muted">
                        Posted on <?php echo date('M j, Y g:i A', strtotime($row['created_at'])); ?>
                    </small></p>
                </div>
            </div>
        </div>
    </div>
<?php endwhile; ?>

<?php if (isset($_SESSION['user_id'])): ?>
    <h2 class="mt-4 mb-3"><i class="fas fa-reply"></i> Reply</h2>
    <form method="post" action="reply.php">
        <input type="hidden" name="topic_id" value="<?php echo $topic_id; ?>">
        <div class="mb-3">
            <textarea class="form-control" name="content" rows="3" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Post Reply</button>
    </form>
<?php else: ?>
    <div class="alert alert-info mt-4" role="alert">
        <i class="fas fa-info-circle"></i> Please <a href="login.php">login</a> to reply
    </div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include 'layout.php';
?>