<?php
include 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle avatar upload
if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $filename = $_FILES['avatar']['name'];
    $filetype = pathinfo($filename, PATHINFO_EXTENSION);
    if (in_array(strtolower($filetype), $allowed)) {
        $avatar_path = 'uploads/avatars/' . $user_id . '.' . $filetype;
        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $avatar_path)) {
            $sql = "UPDATE users SET avatar = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            if ($stmt === false) {
                die('Prepare failed (Avatar update): ' . $conn->error);  // Debugging line
            }
            $stmt->bind_param("si", $avatar_path, $user_id);
            $stmt->execute();
            $stmt->close();
        }
    }
}

// Handle signature update
if (isset($_POST['signature'])) {
    $signature = $_POST['signature'];
    $sql = "UPDATE users SET signature = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die('Prepare failed (Signature update): ' . $conn->error);  // Debugging line
    }
    $stmt->bind_param("si", $signature, $user_id);
    $stmt->execute();
    $stmt->close();
}

$success_message = "Profile updated successfully!";



// Fetch user data
$sql = "SELECT username, email, avatar, signature FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

ob_start();
?>

<div class="row">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <img src="<?php echo $user['avatar'] ? $user['avatar'] : 'https://via.placeholder.com/150'; ?>" alt="Avatar" class="img-fluid rounded-circle mb-3" style="max-width: 150px;">
                <h4><?php echo $user['username']; ?></h4>
            </div>
        </div>
    </div>
    <div class="col-md-9">
        <h1 class="mb-4"><i class="fas fa-user-cog"></i> User Control Panel</h1>
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="avatar" class="form-label">Avatar</label>
                <input type="file" class="form-control" id="avatar" name="avatar" accept="image/*">
            </div>
            <div class="mb-3">
                <label for="signature" class="form-label">Signature</label>
                <textarea class="form-control" id="signature" name="signature" rows="3"><?php echo $user['signature']; ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Profile</button>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
include 'layout.php';
?>