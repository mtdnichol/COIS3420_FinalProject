<?php
session_start();
require "./includes/library.php";

// Ensure user is logged in
if (!(isset($_SESSION['username']) && $_SESSION['username'] != '')) {
    header("Location: Login");
    exit();
}

/* Connect to DB */
$pdo = connectDB();

// Handle pagination (this never made it into the UI, ran out of time)
$page = 1;
if(isset($_GET['page']) && is_int($_GET['page']) && (int)$_GET['page'] > 0) {
    $page = (int)$_GET['page'];
}
$per_page = 10;
if(isset($_GET['per_page']) && is_int($_GET['per_page']) && (int)$_GET['per_page'] > 0) {
    $per_page = (int)$_GET['per_page'];
}

// Calculate limit based on pagination values
$min_limit = ($page - 1) * $per_page;
$max_limit = $page * $per_page;

// Search for the title provided in the url
$title = $_GET['title'] ?? "";

// Inner join users table here to display which user made which list
$query = "SELECT bucket_lists.*, bucket_users.username FROM bucket_lists INNER JOIN bucket_users ON bucket_lists.fk_userid = bucket_users.id WHERE private = 0 AND title LIKE ? LIMIT ?,?";
$statement = $pdo->prepare($query);
$statement->execute(['%'.$title.'%', $min_limit, $max_limit]);
$searchLists = $statement->fetchAll();

// If title is empty, search for all lists
$searchLine = empty($title) ? "Find all lists" : "Find lists associated with ".$title;
?>

<?php include "./includes/header.php"; ?>
<div class="main-box">
    <h1>Search Results</h1>
    <h3><?php echo $searchLine ?></h3>
    <?php foreach($searchLists as $key=>$value): ?>
    <div class="list-container">
        <div class="list-info" id="<?php echo $value['id'] ?>">
            <p class="list-title"><?php echo $value['title']." By ".$value['username']; ?></p>
            <p class="list-description"><?php echo $value['description']; ?></p>
            <p class="list-date"><?php echo $value['created']; ?></p>
        </div>
        <div class="list-properties">
            <div class="list-status">
                <?php
                    if($value['private'] == 0) {
                        echo '<p class="list-public public">Public <i class="fas fa-lock-open"></i></p>';
                    } else {
                        echo '<p class="list-public private">Private <i class="fas fa-lock-closed"></i></p>';
                    }
                ?>
            </div>
            <div class="list-links">
                <a class="list-copy" href="#" id="<?php echo $value['id'] ?>">Copy Link <i class="fas fa-clone"></i></a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<script>
    // Upon clicking a list, go to its url
    window.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll(".list-info").forEach(node => {
            node.addEventListener('click', (event) => {
                document.location.href = "DisplayList?id=" + node.id;
            });
        });

        // Upon clicking the copy button, copy its URL
        let dir = window.location.href.substring(0, window.location.href.lastIndexOf('/'));
        document.querySelectorAll(".list-copy").forEach(node => {
            node.addEventListener("click", (event) => {
                copyToClipboard(dir + "/DisplayList?id=" + event.target.id);
            });
        });
    });

    // TAKEN FROM STACK OVERFLOW
    // Copies a string to the clipboard
    function copyToClipboard(text) {
        if (window.clipboardData && window.clipboardData.setData) {
            // Internet Explorer-specific code path to prevent textarea being shown while dialog is visible.
            return clipboardData.setData("Text", text);

        }
        else if (document.queryCommandSupported && document.queryCommandSupported("copy")) {
            var textarea = document.createElement("textarea");
            textarea.textContent = text;
            textarea.style.position = "fixed";  // Prevent scrolling to bottom of page in Microsoft Edge.
            document.body.appendChild(textarea);
            textarea.select();
            try {
                return document.execCommand("copy");  // Security exception may be thrown by some browsers.
            }
            catch (ex) {
                console.warn("Copy to clipboard failed.", ex);
                return false;
            }
            finally {
                document.body.removeChild(textarea);
            }
        }
    }
</script>
<?php include "./includes/footer.php"; ?>

