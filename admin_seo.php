<?php
include 'config.php';
session_start();

if (!isset($_SESSION['user_id']) || !is_admin($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $site_title = $_POST["site_title"];
    $site_description = $_POST["site_description"];
    $site_keywords = $_POST["site_keywords"];

    // Correct SQL query for name-value pairs
    $sql = "REPLACE INTO settings (name, value) VALUES
            ('site_title', ?),
            ('site_description', ?),
            ('site_keywords', ?)";
    
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }
    
    // Bind the parameters
    $stmt->bind_param("sss", $site_title, $site_description, $site_keywords);

    if ($stmt->execute()) {
        $success_message = "SEO settings updated successfully.";
    } else {
        $error_message = "Error updating SEO settings: " . $stmt->error;
    }

    $stmt->close();
}


$sql = "SELECT name, value FROM settings WHERE name IN ('site_title', 'site_description', 'site_keywords')";
$result = $conn->query($sql);

if ($result === false) {
    die('Query failed: ' . $conn->error);
}

$settings = $result->fetch_all(MYSQLI_ASSOC);

$site_title = "";
$site_description = "";
$site_keywords = "";

foreach ($settings as $setting) {
    if ($setting['name'] == 'site_title') {
        $site_title = $setting['value'];
    } elseif ($setting['name'] == 'site_description') {
        $site_description = $setting['value'];
    } elseif ($setting['name'] == 'site_keywords') {
        $site_keywords = $setting['value'];
    }
}


ob_start();
?>

<h1 class="mb-4"><i class="fas fa-chart-line"></i> Site-wide SEO Settings</h1>

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

<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
    <div class="form-group">
        <label for="site_title">Site Title</label>
        <input type="text" class="form-control" id="site_title" name="site_title" value="<?php echo $site_title; ?>" required>
    </div>
    <div class="form-group">
        <label for="site_description">Site Description</label>
        <textarea class="form-control" id="site_description" name="site_description" rows="3" required><?php echo $site_description; ?></textarea>
    </div>
    <div class="form-group">
        <label for="site_keywords">Site Keywords (comma-separated)</label>
        <input type="text" class="form-control" id="site_keywords" name="site_keywords" value="<?php echo $site_keywords; ?>" required>
    </div>
    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Settings</button>
</form>

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