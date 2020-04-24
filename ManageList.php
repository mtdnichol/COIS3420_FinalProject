<?php
session_start();
require "./includes/library.php";
require "./includes/util.php";

if (!(isset($_SESSION['username']) && $_SESSION['username'] != '')) {
    header("Location: Login.php");
    exit();
}

if(!isset($_GET['id']) || !is_int($_GET['id'])) {
    // TODO error
}

/* Connect to DB */
$pdo = connectDB();

// current list id
$curID = $_GET['id'];

$query = "SELECT * FROM `bucket_lists` WHERE fk_userid = ?";
$statement = $pdo->prepare($query);
$statement->execute([$_SESSION['userID']]);
$userLists = $statement->fetchAll();

$query = "SELECT title, username, description FROM `bucket_lists` INNER JOIN bucket_users ON bucket_lists.fk_userid = bucket_users.id WHERE bucket_lists.id = ?";
$statement = $pdo->prepare($query);
$statement->execute([$curID]);
$list = $statement->fetch();
$username = $list['username'];
$title = $list['title'];
$description = $list['description'];

$query = "SELECT id, title, photo, description FROM `bucket_entries` WHERE fk_listid = ?";
$statement = $pdo->prepare($query);
$statement->execute([$curID]);
$results = $statement->fetchAll();

if (isset($_POST['exit'])) {
    header("Location: DisplayList");
    exit();
}

if(!isOwner($curID)) {
    header("Location: Login");
    exit();
}
?>

<!--html file starts-->
<?php include "./includes/header.php"; ?>
    <link href="https://transloadit.edgly.net/releases/uppy/v1.13.2/uppy.min.css" rel="stylesheet">
    <div class="main-box">
        <h1><?php echo ucfirst($username) ?>'s Bucket List</h1>
        <div class="titleHeader">
            <h2><?php echo $title ?></h2>
        </div>
        <div class="titleEdit hidden">
            <input type="text">
            <button id="titleSubmit">Submit</button>
        </div>

        <div class="bucketDesc">
            <p id="bucketDescription"><?php echo $description ?></p>
        </div>
        <div class="bucketEdit hidden">
            <input type="text">
            <button id="descSubmit">Submit</button>
        </div>


        <div class="bucketListNav" style="">
            <div class="leftButtons">
                <div class="button-horizontal">
                    <button id="addItem" data-open-modal="addItemModal" name="addItem" data-tippy-content="Add Item"><i class="fas fa-plus"></i></button>
                    <div id="addItemModal" class="modal">
                        <div class="modal-content">
                            <span class="close-btn">&times;</span>
                            <p>this is the text inside the modal</p>
                        </div>
                    </div>
                    <form action="<?= $_SERVER['PHP_SELF'] ?>" method="POST">
                        <button id="editList" name="editList" onclick="titleSwap(); return false;" data-tippy-content="Edit List Title"><i class="fas fa-edit"></i></button>
                    </form>
                    <form action="./Login" method="POST">
                        <input type="hidden" name="listID" value="<?php echo $_GET['id'] ?>">
                        <button id="deleteList" name="deleteList" data-tippy-content="Delete List" onclick="return confirmation()"><i class="fas fa-trash-alt"></i></button>
                    </form>
                </div>
            </div>
            <div class="rightButtons">
                <div class="button-horizontal">
                    <button class="<?php echo isPrivate($_GET['id']) ? "" : "hidden"?>" id="privatize" name="privatize" onclick="return privacySwap('<?php echo $_GET['id'] ?>');" data-tippy-content="Make Public"><i class="fa fa-lock"></i></button>
                    <button class="<?php echo isPrivate($_GET['id']) ? "hidden" : ""?>" id="privatize-lock" name="privatize-lock" onclick="return privacySwap('<?php echo $_GET['id'] ?>');" data-tippy-content="Make Private"><i class="fa fa-unlock"></i></button>
                    <form id="exit-form" action="<?php echo "DisplayList?id=".$_GET['id']?>" method="POST">
                        <button id="exit" name="exit"><i class="fas fa-sign-out-alt"></i> Exit</button>
                    </form>
                </div>

            </div>
        </div>

        <?php foreach ($results as $result): ?>
            <input id="dbid" type="hidden" name="value">

            <div class="item" data-item-id="<?php echo $result['id'] ?>">
                <div class="item-buttons">
                    <button id="markItem" onclick="resetUppy()" class="markItem" data-open-modal="markItemModal" name="markItem" data-tippy-content="Mark Completed"><i class="fas fa-check"></i></button>
                    <div id="markItemModal" class="modal">
                        <div class="modal-content">
                            <span class="close-btn">&times;</span>
                            <label> Completed Date: <input id="completedDate" type="date"></label>
                            <button id="openUppy">Upload Image</button>
                            <button onclick="return markItemComplete('<?php echo $result['id'] ?>');">Mark Completed</button>
                            <p id="uploadErrors"></p>
                        </div>
                    </div>
                    <button id="editItem" class="editItem" name="editItem" data-tippy-content="Edit Item"><i class="fas fa-edit"></i></button>
                    <button id="deleteItem" class="deleteItem" name="deleteItem" data-tippy-content="Delete Item" onclick="return deleteItem('<?php echo $result['id'] ?>');"><i class="fas fa-trash-alt"></i></button>
                </div>
                <img src="<?= $result['photo'] ?>" alt="TestImage">
                <div class="bucket-content" id="<?= $result['id'] ?>">
                    <h3><?= $result['title'] ?></h3>
                    <p><?= $result['description'] ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <script src="https://transloadit.edgly.net/releases/uppy/v1.13.2/uppy.min.js"></script>
    <script defer src="./scripts/ManageList.js"></script>
<?php include "./includes/footer.php"; ?>