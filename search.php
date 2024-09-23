<?php
include 'config.php';
include 'includes/header.php';
?>

<div class="container my-5">
    <h1 class="mb-4"><i class="fas fa-search"></i> Search Files</h1>

    <div class="row mb-4">
        <div class="col-12 col-md-6 offset-md-3">
            <div class="input-group">
                <input type="text" class="form-control" placeholder="Search for files..." aria-label="Search" aria-describedby="button-search">
                <a href="search.php" class="btn btn-primary" type="button" id="button-search"><i class="fas fa-search"></i></a>
            </div>
        </div>
    </div>

    <div class="row">
        <?php
        $searchQuery = isset($_GET['q']) ? $_GET['q'] : '';
        $sql = "SELECT * FROM files WHERE name LIKE '%$searchQuery%'";
        $result = $conn->query($sql);
        while ($row = $result->fetch_assoc()) {
            include 'includes/file-card.php';
        }
        ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>