<?php
session_start();
require "./includes/library.php";
require "./includes/util.php";

// login verification
if (!(isset($_SESSION['username']) && $_SESSION['username'] != '')) {
    header("Location: Login");
    exit();
}

// random ID errors
if(!isset($_GET['id']) || !(!is_int($_GET['id'] && strtolower($_GET['id']) != "random"))) {
    header('Location: Error');
}

/* Connect to DB */
$pdo = connectDB();

// Current List ID
$curID = $_GET['id'];

// for im feeling lucky search, if id passed in is random select a random value from the list
if(strtolower($curID) == "random") {
    $query = "SELECT id FROM bucket_lists ORDER BY RAND() LIMIT 1";
    $statement = $pdo->prepare($query);
    $statement->execute();
    $curID = $statement->fetch()['id'];
}

// get title, username, and description from bucket_lists and store for later use
$query = "SELECT title, username, description FROM `bucket_lists` INNER JOIN bucket_users ON bucket_lists.fk_userid = bucket_users.id WHERE bucket_lists.id = ?";
$statement = $pdo->prepare($query);
$statement->execute([$curID]);
$list = $statement->fetch();
$username = $list['username'];
$title = $list['title'];
$description = $list['description'];

// get id, title, photo, & desc from entries
$query = "SELECT id, title, photo, description FROM `bucket_entries` WHERE fk_listid = ?";
$statement = $pdo->prepare($query);
$statement->execute([$curID]);
$results = $statement->fetchAll();

if(isPrivate($curID) && !isOwner($curID)) {
    header("Location: Login.php");
    exit();
}
?>

<!--html starts-->
<?php include "./includes/header.php"; ?>
    <div class="main-box">
        <!--Display main list information-->
        <h1><?php echo $username ?>'s Bucket List</h1>
        <h2><?php echo $title ?></h2>
        <p><?php echo $description ?></p>
        <div class="bucket-list-nav">
            <!--only display manage if their the owner of the list-->
            <?php if(isOwner($curID)):?>
                <a href="<?php echo "ManageList?id=".$_GET['id']?>" class="right"><i class="fas fa-tasks"></i> Manage</a>
            <?php endif; ?>
        </div>
        <!--List out each item-->
        <?php foreach ($results as $result): ?>
            <div class ="item">
                <div class="bucket-content">
                    <h3><?= $result['title'] ?></h3>
                    <p><?= $result['description'] ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php include "./includes/footer.php"; ?>