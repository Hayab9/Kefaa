<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "it320";

// الاتصال بقاعدة البيانات
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

// حذف منشور (إذا تم طلب ذلك)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete_post_id"])) {
    $delete_post_id = intval($_POST["delete_post_id"]);
    $stmt_del = $conn->prepare("DELETE FROM posts WHERE PostID = ? AND UserID = ?");
    $stmt_del->bind_param("ii", $delete_post_id, $user_id);
    $stmt_del->execute();
    $stmt_del->close();
    // يمكنك إضافة رسالة نجاح أو إعادة تحميل الصفحة هنا
}

// تسجيل الخروج
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_unset();
    session_destroy();
    header("Location: login.html");
    exit();
}

// جلب بيانات المستخدم
$stmt = $conn->prepare("SELECT * FROM users WHERE UserID = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// جلب المنشورات الخاصة بالمستخدم
$posts = [];
$stmt = $conn->prepare("SELECT * FROM posts WHERE UserID = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $posts[] = $row;
}
$stmt->close();

// جلب الطلبات المستلمة (طلبات التبادل الواردة)
$received = [];
$stmt = $conn->prepare("SELECT r.*, u.Name AS TraderName FROM offerrequests r JOIN users u ON r.TraderID = u.UserID WHERE r.OwnerID = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $received[] = $row;
}
$stmt->close();

// جلب الطلبات المرسلة (طلبات التبادل التي أرسلتها)
$sent = [];
$stmt = $conn->prepare("SELECT r.*, u.Name AS OwnerName, u.Email AS OwnerEmail, u.PhoneNumber AS OwnerPhone FROM offerrequests r JOIN users u ON r.OwnerID = u.UserID WHERE r.TraderID = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $sent[] = $row;
}
$stmt->close();

$conn->close();
?>
<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>الملف الشخصي - كِفَاء</title>
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

/* ====== HEADER ====== */
header {
    background-color: #035352;
    padding: 0.9375em 3.125em; /* 15px 50px */
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

/* ====== MAIN CONTAINER ====== */
.profile-container {
    max-width: 56.25em; /* 900px */
    margin: 2.5em auto; /* 40px */
    padding: 1.25em; /* 20px */
}

h1 {
    text-align: center;
    margin-bottom: 1.25em; /* 20px */
}

/* ====== CARD DESIGN ====== */
.card {
    background: white;
    padding: 1.25em; /* 20px */
    border-radius: 0.625em; /* 10px */
    box-shadow: 0 0.25em 0.375em rgba(0, 0, 0, 0.1); /* 4px 6px */
    margin-bottom: 1.875em; /* 30px */
}

/* ====== USER DETAILS ====== */
.profile-details p {
    font-size: 1em; /* 16px */
}

.edit-btn {
    display: inline-block;
    margin-top: 0.625em; /* 10px */
    padding: 0.5em 0.9375em; /* 8px 15px */
    background-color: #035352;
    color: white;
    text-decoration: none;
    border-radius: 0.3125em; /* 5px */
    cursor: pointer;
}

.edit-btn:hover {
    background-color: #028484;
}

/* ====== REQUESTS + POSTS ====== */
.post-item, .request-item {
    background: #fff;
    padding: 0.9375em; /* 15px */
    border-radius: 0.5em; /* 8px */
    box-shadow: 0 0.25em 0.375em rgba(0, 0, 0, 0.1); /* 4px 6px */
    margin-bottom: 0.9375em; /* 15px */
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.post-item button, .request-item button {
    padding: 0.3125em 0.625em; /* 5px 10px */
    border: none;
    cursor: pointer;
    border-radius: 0.3125em; /* 5px */
    font-size: 0.875em; /* 14px */
}

.edit-post {
    padding: 0 0.3125em; /* 0px 5px */
    background-color: #035352;
    color: white;
    border-radius: 0.3125em; /* 5px */
    text-align: center;
    font-size: 0.8125em; /* 13px */
    min-width: 1.875em; /* 30px */
}

.edit-post:hover {
    background-color: #028484;
}

.delete-post {
    background-color: #cc0000;
    color: white;
}

.add-post-btn {
    display: block;
    text-align: center;
    padding: 0.625em; /* 10px */
    background-color: #035352;
    color: white;
    border-radius: 0.3125em; /* 5px */
    text-decoration: none;
    font-size: 1em; /* 16px */
    margin-top: 1.25em; /* 20px */
}

.add-post-btn:hover {
    background-color: #028484;
}

/* ====== FOOTER ====== */
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

/* ====== SEARCH BOX ====== */
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
    <!-- HEADER -->
    <header>
        <img src="logo.png" alt="كِفَاء">
        <nav>
            <ul>
                <li><a href="index.php">الرئيسية</a></li>
                <li><a href="about.php">من نحن</a></li>
                <li><a href="trading.php">التبادل</a></li>
                <li class="icon-group">
                    <a href="profile.php"><img src="user-icon.png" style="width: 30px; height: 30px;" alt="الملف الشخصي"></a>
                    <img src="search-icon.png" id="searchIcon" class="search-icon" style="width: 25px; height: 25px;" alt="بحث">
                </li>
                <li><a href="profile.php?action=logout" class="login-btn">تسجيل الخروج</a></li>
            </ul>
        </nav>
    </header>

    <!-- SEARCH BOX -->
    <div id="searchBarContainer" class="search-box">
        <input type="text" id="searchInput" placeholder="ابحث عن المنتجات أو الخدمات...">
        <button onclick="performSearch()">🔍</button>
    </div>

    <!-- PROFILE CONTAINER -->
    <div class="profile-container">
        <h1>الملف الشخصي للمستخدم: <?php echo htmlspecialchars($user['Name']); ?></h1>
        
        <!-- معلومات المستخدم -->
        <div class="card profile-details">
            <h3>معلومات المستخدم</h3>
            <p><strong>الاسم:</strong> <?php echo htmlspecialchars($user['Name']); ?></p>
            <p><strong>الجنس:</strong> <?php echo htmlspecialchars($user['Gender']); ?></p>
            <p><strong>البريد الإلكتروني:</strong> <?php echo htmlspecialchars($user['Email']); ?></p>
            <p><strong>رقم الهاتف:</strong> <?php echo htmlspecialchars($user['PhoneNumber']); ?></p>
            <p><strong>المدينة:</strong> <?php echo htmlspecialchars($user['City']); ?></p>
            <a href="edit-profile.php" class="edit-btn">تعديل المعلومات</a>
        </div>

        <!-- الطلبات المستلمة -->
        <div class="card">
            <h3>طلبات التبادل المستلمة</h3>
            <?php if(count($received) > 0): ?>
                <?php foreach($received as $r): ?>
                    <div class="request-item">
                        <span>طلب: <?php echo htmlspecialchars($r['OfferDetails']); ?> من <?php echo htmlspecialchars($r['TraderName']); ?></span>
                        <a href="request-details.php?requestId=<?php echo $r['RequestID']; ?>" style="background-color: #035352; color: white; padding: 8px 12px; border: none; border-radius: 5px; font-size: 14px; text-decoration:none;">عرض الطلب</a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>لا توجد طلبات مستلمة.</p>
            <?php endif; ?>
        </div>

        <!-- الطلبات المرسلة -->
        <div class="card">
            <h3>الطلبات التي قمت بإرسالها</h3>
            <?php if(count($sent) > 0): ?>
                <?php foreach($sent as $s): ?>
                    <div class="request-item" style="flex-direction: column; align-items: flex-start;">
                        <span>طلب: <?php echo htmlspecialchars($s['OfferDetails']); ?> إلى <?php echo htmlspecialchars($s['OwnerName']); ?></span>
                        <?php if($s['RequestStatus'] === 'تم القبول'): ?>
                            <div style="margin-top: 5px; padding: 10px; background-color: #f9f9f9; border-radius: 5px;">
                                <p><strong>البريد:</strong> <?php echo htmlspecialchars($s['OwnerEmail']); ?></p>
                                <p><strong>الهاتف:</strong> <?php echo htmlspecialchars($s['OwnerPhone']); ?></p>
                            </div>
                        <?php endif; ?>
                        <span style="padding: 5px 10px; border-radius: 5px; background-color: <?php echo ($s['RequestStatus'] === 'انتظار') ? '#ff9800' : (($s['RequestStatus'] === 'تم القبول') ? '#028484' : '#cc0000'); ?>; color: white; font-size:14px;">
                            <?php echo htmlspecialchars($s['RequestStatus']); ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>لا توجد طلبات مرسلة.</p>
            <?php endif; ?>
        </div>

        <!-- المنشورات -->
        <div class="card">
            <h3>المنتجات التي قمت بنشرها</h3>
            <?php if(count($posts) > 0): ?>
                <?php foreach($posts as $p): ?>
                    <div class="post-item">
                        <span><?php echo htmlspecialchars($p['PostTitle']); ?></span>
                        <div>
                            <a href="edit-post.php?postId=<?php echo $p['PostID']; ?>" class="edit-post">تعديل</a>
                            <form action="" method="POST" style="display:inline;">
                                <input type="hidden" name="delete_post_id" value="<?php echo $p['PostID']; ?>">
                                <button type="submit" class="delete-post">حذف</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>لم تقم بنشر أي منتجات بعد.</p>
            <?php endif; ?>
            <a href="add-post.php" class="add-post-btn">➕ أضف إعلان جديد</a>
        </div>
    </div>

    <!-- FOOTER -->
   
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
