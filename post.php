<?php
session_start();
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_unset();
    session_destroy();
    header("Location: login.html");
    exit();
}
// If this is an AJAX request for sending a trade request, handle it here
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["tradeAction"]) && $_POST["tradeAction"] === "sendTradeRequest") {
    header("Content-Type: application/json");
    
    // Check if user is logged in
    if (!isset($_SESSION["user_id"])) {
        echo json_encode(["status" => "error", "message" => "يجب تسجيل الدخول لإرسال طلب التبادل."]);
        exit();
    }
    
    // Validate input
    if (!isset($_POST["postId"]) || !isset($_POST["tradeDescription"])) {
        echo json_encode(["status" => "error", "message" => "الرجاء ملء جميع الحقول المطلوبة."]);
        exit();
    }
    
    $postId = intval($_POST["postId"]);
    $tradeDescription = trim($_POST["tradeDescription"]);
    if ($tradeDescription === "") {
        echo json_encode(["status" => "error", "message" => "الرجاء كتابة تفاصيل التبادل."]);
        exit();
    }
    
    // Database connection settings
    $servername = "localhost";
    $username = "root";
    $db_password = "root";
    $dbname = "it320";
    
    $conn = new mysqli($servername, $username, $db_password, $dbname);
    if ($conn->connect_error) {
        echo json_encode(["status" => "error", "message" => "فشل الاتصال بقاعدة البيانات."]);
        exit();
    }
    
    // Get the post to determine the owner's ID
    $stmt = $conn->prepare("SELECT UserID FROM posts WHERE PostID = ?");
    $stmt->bind_param("i", $postId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows == 0) {
        echo json_encode(["status" => "error", "message" => "الإعلان غير موجود."]);
        exit();
    }
    $post = $result->fetch_assoc();
    $stmt->close();
    
    $traderId = $_SESSION["user_id"];
    $ownerId = $post["UserID"];
    $requestStatus = "انتظار"; // default status for pending
    
    $stmt_req = $conn->prepare("INSERT INTO offerrequests (TraderID, OwnerID, PostID, OfferDetails, RequestStatus) VALUES (?, ?, ?, ?, ?)");
    $stmt_req->bind_param("iiiss", $traderId, $ownerId, $postId, $tradeDescription, $requestStatus);
    if ($stmt_req->execute()) {
        echo json_encode(["status" => "success", "message" => "تم إرسال طلب التبادل بنجاح."]);
    } else {
        echo json_encode(["status" => "error", "message" => "حدث خطأ أثناء إرسال طلب التبادل."]);
    }
    $stmt_req->close();
    $conn->close();
    exit();
}

// If not processing an AJAX request, continue to display the post details

// Database connection settings
$servername = "localhost";
$username = "root";
$db_password = "root";
$dbname = "it320";

$conn = new mysqli($servername, $username, $db_password, $dbname);
if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
}

// Check if postId is provided
if (!isset($_GET['postId'])) {
    die("لم يتم تحديد معرف الإعلان.");
}
$postId = intval($_GET['postId']);

// Fetch post details
$stmt = $conn->prepare("SELECT * FROM posts WHERE PostID = ?");
$stmt->bind_param("i", $postId);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    die("الإعلان غير موجود.");
}
$post = $result->fetch_assoc();
$stmt->close();

// Fetch the owner's details
$stmtOwner = $conn->prepare("SELECT * FROM users WHERE UserID = ?");
$stmtOwner->bind_param("i", $post['UserID']);
$stmtOwner->execute();
$owner = $stmtOwner->get_result()->fetch_assoc();
$stmtOwner->close();

$conn->close();
?>
<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تفاصيل المنتج - كِفَاء</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/mark.js/dist/mark.min.js"></script>

    <style>
	html {
    box-sizing: border-box;
}

*, *:before, *:after {
    box-sizing: inherit;
}

body {
    font-family: 'Cairo', sans-serif;
    margin: 0;
    padding: 0;
    background-color: #f4f4f4;
    color: #333;
    font-size: 100%; /* Ensures 1em = 16px */
}

header {
    background-color: #035352;
    padding: 0.9375em 3.125em; /* 15px = 0.9375em, 50px = 3.125em */
    display: flex;
    justify-content: space-between;
    align-items: center;
}

header img {
    height: 4.375em; /* 70px */
}

nav ul {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    align-items: center;
}

nav ul li {
    margin: 0 1.875em; /* 30px */
}

nav ul li a {
    color: white;
    text-decoration: none;
    font-size: 1.125em; /* 18px */
}

nav ul li a:hover,
nav ul li a:focus,
nav ul li a:active {
    color: #bfcfb2;
    text-decoration: none !important;
}

a {
    text-decoration: none !important;
    color: inherit;
}

a:hover, a:focus, a:active {
    text-decoration: none !important;
}

.post-container {
    display: flex;
    justify-content: center;
    align-items: center;
    margin: 2.5em auto; /* 40px */
    padding: 1.25em; /* 20px */
    max-width: 56.25em; /* 900px */
    background: white;
    border-radius: 0.625em; /* 10px */
    box-shadow: 0 0.25em 0.375em rgba(0, 0, 0, 0.1); /* 4px 6px */
}

.post-image {
    flex: 1;
    max-width: 50%;
    padding-right: 1.25em; /* 20px */
}

.post-image img {
    width: 100%;
    height: auto; /* Ensures the aspect ratio is maintained */
    display: block; /* Removes any extra space below the image */
    border-radius: 0.625em; /* 10px */
}

.post-details {
    flex: 1;
    padding-left: 1.25em; /* 20px */
}

.post-details h1 {
    font-size: 1.5em; /* 24px */
    color: #035352;
}

.post-details p {
    font-size: 1em; /* 16px */
    color: #555;
    line-height: 1.6;
}

.owner-info {
    margin-top: 1.25em; /* 20px */
    padding: 0.9375em; /* 15px */
    background-color: #e0f7f7;
    border-radius: 0.5em; /* 8px */
}

.trade-request {
    margin-top: 1.25em; /* 20px */
}

.trade-request button {
    margin-top: 0.625em; /* 10px */
    padding: 0.625em;
    width: 100%;
    background-color: #035352;
    color: white;
    border: none;
    border-radius: 0.3125em; /* 5px */
    font-size: 1em; /* 16px */
    cursor: pointer;
}

.trade-request button:hover {
    background-color: #028484;
}

.popup {
    display: none;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    padding: 1.25em; /* 20px */
    border-radius: 0.5em; /* 8px */
    box-shadow: 0 0.25em 0.375em rgba(0, 0, 0, 0.2); /* 4px 6px */
    z-index: 1000;
    width: 18.75em; /* 300px */
    text-align: center;
}

.popup textarea {
    width: 100%;
    height: 5em; /* 80px */
    padding: 0.5em; /* 8px */
    border: 0.0625em solid #ccc; /* 1px */
    border-radius: 0.3125em; /* 5px */
    font-size: 0.875em; /* 14px */
}

.popup-buttons {
    margin-top: 0.625em; /* 10px */
    display: flex;
    justify-content: space-between;
}

.popup-buttons button {
    padding: 0.5em 0.75em; /* 8px 12px */
    border: none;
    border-radius: 0.3125em; /* 5px */
    cursor: pointer;
    font-size: 0.875em; /* 14px */
}

.confirm-btn {
    background-color: #035352;
    color: white;
}

.cancel-btn {
    background-color: #ccc;
    color: black;
}

.overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 999;
}

footer {
    background-color: #035352;
    color: white;
    text-align: center;
    padding: 1.25em 3.125em; /* 20px 50px */
    display: flex;
    flex-direction: column;
    align-items: center;
}

.footer-sections {
    display: flex;
    justify-content: space-between;
    width: 100%;
}

.footer-section {
    max-width: 18.75em; /* 300px */
}

.footer-section h3 {
    margin-bottom: 0.625em; /* 10px */
}

.newsletter {
    display: flex;
    flex-direction: column;
}

.newsletter input {
    padding: 0.5em; /* 8px */
    margin-bottom: 0.625em; /* 10px */
    border: none;
    border-radius: 0.25em; /* 4px */
}

.newsletter button {
    padding: 0.5em; /* 8px */
    background-color: #028484;
    color: white;
    border: none;
    border-radius: 0.25em; /* 4px */
    cursor: pointer;
}

.copyright {
    margin-top: 1.25em; /* 20px */
    font-size: 0.875em; /* 14px */
}

/* 🔍 Search Box */
.search-box {
    display: none;
    position: absolute;
    top: 5em; /* 80px */
    left: 50%;
    transform: translateX(-50%);
    background: white;
    padding: 0.625em 0.9375em; /* 10px 15px */
    border-radius: 1.5625em; /* 25px */
    box-shadow: 0 0.25em 0.5em rgba(0, 0, 0, 0.2); /* 4px 8px */
    width: 20em; /* 320px */
    display: flex;
    align-items: center;
    gap: 0.625em; /* 10px */
    border: 0.0625em solid #ccc; /* 1px */
    transition: opacity 0.3s ease, transform 0.3s ease;
    opacity: 0;
    visibility: hidden;
    z-index: 1000;
}

.search-box input {
    flex: 1;
    padding: 0.75em; /* 12px */
    border: none;
    border-radius: 1.25em; /* 20px */
    font-size: 1em; /* 16px */
    outline: none;
    background: #f9f9f9;
}

.search-box button {
    background: #035352;
    color: white;
    border: none;
    padding: 0.625em; /* 10px */
    border-radius: 50%;
    cursor: pointer;
    font-size: 1em; /* 16px */
    display: flex;
    align-items: center;
    justify-content: center;
    width: 2.5em; /* 40px */
    height: 2.5em; /* 40px */
}

.search-box button:hover {
    background: #028484;
}

.first-match {
    background-color: yellow;
}
    </style>
</head>
<body>
    <header>
        <img src="logo.png" alt="كِفَاء">
        <nav>
            <ul>
                <li><a href="index.php">الرئيسية</a></li>
                <li><a href="about.php">من نحن</a></li>
                <li><a href="trading.php">التبادل</a></li>
                <li class="icon-group">
                    <a href="profile.php"><img src="user-icon.png" style="width:30px;height:30px;" alt="الملف الشخصي"></a>
                    <img src="search-icon.png" id="searchIcon" style="width:25px;height:25px;" alt="بحث">
                </li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="profile.php?action=logout" class="login-btn">تسجيل الخروج</a></li>
                <?php else: ?>
                    <li><a href="login.html" class="login-btn">تسجيل الدخول/إنشاء حساب</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
    
    <div id="searchBarContainer" class="search-box">
        <input type="text" id="searchInput" placeholder="ابحث عن المنتجات أو الخدمات...">
        <button onclick="performSearch()">🔍</button>
    </div>
    
    <div class="post-container">
        <div class="post-image">
            <img src="<?php echo htmlspecialchars($post['ImageURL']); ?>" alt="<?php echo htmlspecialchars($post['PostTitle']); ?>">
        </div>
        <div class="post-details">
            <h1><?php echo htmlspecialchars($post['PostTitle']); ?></h1>
            <p><?php echo nl2br(htmlspecialchars($post['Description'])); ?></p>
            <div class="owner-info">
                <h3>معلومات المالك</h3>
                <p><strong>الاسم:</strong> <?php echo htmlspecialchars($owner['Name']); ?></p>
                <p><strong>الموقع:</strong> <?php echo htmlspecialchars($owner['City']); ?></p>
            </div>
            <div class="trade-request">
                <button onclick="openPopup()">إرسال طلب التبادل</button>
            </div>
        </div>
    </div>
    
    <!-- Popup for Trade Request -->
    <div class="overlay" id="overlay"></div>
    <div class="popup" id="popup">
        <h3>أدخل وصف التبادل</h3>
        <textarea id="tradeDescription" placeholder="اكتب تفاصيل المنتج الذي ترغب في التبادل به..."></textarea>
        <div class="popup-buttons">
            <button class="confirm-btn" onclick="sendTradeRequest()">تأكيد</button>
            <button class="cancel-btn" onclick="closePopup()">إلغاء</button>
        </div>
    </div>
    
      <footer>
        <div class="footer-sections">
            <div class="footer-section">
                <h3>روابط سريعة</h3>
                <p><a href="index.php">الرئيسية</a> | <a href="about.php">من نحن</a> | <a href="trading.php">التبادل</a></p>
            </div>
            <div class="footer-section">
                <h3>انضم إلينا</h3>
                <p>اشترك في نشرتنا الإخبارية لمتابعة آخر العروض.</p>
                <div class="newsletter">
                    <input type="email" placeholder="بريدك الإلكتروني" id="newsletterEmail">
                    <button onclick="subscribeNews()">اشترك</button>
                </div>
            </div>
            <div class="footer-section">
                <h3>تواصل معنا</h3>
                <p>الرياض، المملكة العربية السعودية</p>
                <p>📞 010-020-0340</p>
                <p>📧 info@kafaa.com</p>
            </div>
        </div>
    </footer>
    
    <script>
        // Check if user is logged in (set by PHP)
        var isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
        
        function openPopup() {
            // If user not logged in, force login
            if (!isLoggedIn) {
                alert("يجب تسجيل الدخول لإرسال طلب التبادل.");
                window.location.href = "login.html";
                return;
            }
            document.getElementById("popup").style.display = "block";
            document.getElementById("overlay").style.display = "block";
        }
        
        function closePopup() {
            document.getElementById("popup").style.display = "none";
            document.getElementById("overlay").style.display = "none";
        }
        
        function sendTradeRequest() {
            let message = document.getElementById("tradeDescription").value;
            if (message.trim() === "") {
                alert("يرجى كتابة تفاصيل التبادل قبل الإرسال.");
                return;
            }
            // Send AJAX POST request to the same page (post.php)
            fetch("post.php?postId=<?php echo $postId; ?>", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: "tradeAction=sendTradeRequest&postId=<?php echo $postId; ?>&tradeDescription=" + encodeURIComponent(message)
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.status === "success") {
                    closePopup();
                    // Optionally, you can update the UI here without refreshing, or redirect to the profile page:
                    window.location.href = "profile.php";
                }
            })
            .catch(error => {
                console.error("Error:", error);
                alert("حدث خطأ أثناء إرسال طلب التبادل.");
            });
        }
        
        document.getElementById("searchIcon").addEventListener("click", function () {
            let searchBar = document.getElementById("searchBarContainer");
            searchBar.style.opacity = (searchBar.style.opacity === "0" || searchBar.style.opacity === "") ? "1" : "0";
            searchBar.style.visibility = (searchBar.style.visibility === "hidden" || searchBar.style.visibility === "") ? "visible" : "hidden";
        });
        
       function performSearch() {
    let query = document.getElementById("searchInput").value.trim();
    if (query === "") {
        alert("الرجاء إدخال كلمة للبحث.");
        return;
    }
    
    // نحدد السياق الذي سيتم البحث فيه، هنا نستخدم body لكن يمكنك تغييره إذا أردت
    let context = document.querySelector("body");
    let markInstance = new Mark(context);
    
    // إزالة التحديدات السابقة
    markInstance.unmark({
        done: function() {
            // تحديد النصوص المطابقة
            markInstance.mark(query, {
                "each": function(element) {
                    // إذا لم يتم تحديد أول نتيجة بعد، قم بتحديدها وتمرير المستخدم إليها
                    if (!document.querySelector(".first-match")) {
                        element.classList.add("first-match");
                        element.scrollIntoView({ behavior: "smooth", block: "center" });
                    }
                }
            });
        }
    });
}
        
        function subscribeNews() {
            let emailInput = document.getElementById("newsletterEmail");
            let emailValue = emailInput.value.trim();
            let emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test(emailValue)) {
                alert("الرجاء إدخال بريد إلكتروني صحيح!");
                return;
            }
            alert("تم الاشتراك بنجاح في النشرة الإخبارية!");
            emailInput.value = "";
        }
    </script>
</body>
</html>
