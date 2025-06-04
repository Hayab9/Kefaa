<?php
session_start();

// التأكد من تسجيل الدخول (يفترض أن صاحب الطلب هو المستخدم المسجل)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$servername = "localhost";
$username = "root";
$db_password = "root";
$dbname = "it320";

// إنشاء الاتصال بقاعدة البيانات
$conn = new mysqli($servername, $username, $db_password, $dbname);
if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
}
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_unset();
    session_destroy();
    header("Location: login.html");
    exit();
}
// التأكد من وجود معرف الطلب في الرابط
if (!isset($_GET['requestId'])) {
    die("لم يتم تحديد معرف الطلب.");
}
$requestId = intval($_GET['requestId']);

// معالجة الطلب عند الضغط على "قبول" أو "رفض"
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $action = $_POST['action'];
    // نفذ العملية فقط إذا كان المستخدم هو صاحب الطلب (Owner)
    $ownerId = $_SESSION['user_id'];
    
    if ($action === "accept") {
        // تحديث حالة الطلب إلى "تم القبول"
        $stmt = $conn->prepare("UPDATE offerrequests SET RequestStatus = 'تم القبول' WHERE RequestID = ? AND OwnerID = ?");
        $stmt->bind_param("ii", $requestId, $ownerId);
        $stmt->execute();
        $stmt->close();
    } elseif ($action === "reject") {
        // تحديث حالة الطلب إلى "مرفوض"
        $stmt = $conn->prepare("UPDATE offerrequests SET RequestStatus = 'مرفوض' WHERE RequestID = ? AND OwnerID = ?");
        $stmt->bind_param("ii", $requestId, $ownerId);
        $stmt->execute();
        $stmt->close();
    }
}

// جلب تفاصيل الطلب مع معلومات الطرفين
$stmt = $conn->prepare("SELECT r.*, 
    t.Name AS TraderName, t.Email AS TraderEmail, t.PhoneNumber AS TraderPhone,
    o.Name AS OwnerName, o.Email AS OwnerEmail, o.PhoneNumber AS OwnerPhone, o.City AS OwnerCity
FROM offerrequests r
JOIN users t ON r.TraderID = t.UserID
JOIN users o ON r.OwnerID = o.UserID
WHERE r.RequestID = ?");
$stmt->bind_param("i", $requestId);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    die("الطلب غير موجود.");
}
$request = $result->fetch_assoc();
$stmt->close();

$conn->close();
?>
<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>تفاصيل الطلب - كِفَاء</title>
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

/* ====== REQUEST DETAILS CONTAINER ====== */
.request-details-container {
    max-width: 37.5em; /* 600px */
    margin: 2.5em auto; /* 40px */
    background: white;
    padding: 1.25em; /* 20px */
    border-radius: 0.625em; /* 10px */
    box-shadow: 0 0.25em 0.375em rgba(0,0,0,0.1); /* 4px 6px */
    text-align: center;
}

h1 {
    margin-bottom: 1.25em; /* 20px */
}

/* ====== INFO BOX ====== */
.info-box {
    background-color: #e0f7f7;
    border-radius: 0.5em; /* 8px */
    padding: 0.9375em; /* 15px */
    margin-bottom: 1.25em; /* 20px */
}

/* ====== BUTTONS ====== */
.buttons {
    display: flex;
    justify-content: center;
    gap: 1.25em; /* 20px */
}

.btn-accept {
    background-color: #028484;
    color: white;
    padding: 0.625em 1.25em; /* 10px 20px */
    border: none;
    border-radius: 0.3125em; /* 5px */
    font-size: 1em; /* 16px */
    cursor: pointer;
}

.btn-accept:hover {
    background-color: #035352;
}

.btn-reject {
    background-color: #cc0000;
    color: white;
    padding: 0.625em 1.25em; /* 10px 20px */
    border: none;
    border-radius: 0.3125em; /* 5px */
    font-size: 1em; /* 16px */
    cursor: pointer;
}

.btn-reject:hover {
    background-color: #a30000;
}

/* ====== STATUS MESSAGE ====== */
.status-message {
    margin-top: 1.25em; /* 20px */
    font-size: 1.125em; /* 18px */
    font-weight: bold;
    color: #333;
}

.accepted {
    color: #028484;
}

.rejected {
    color: #cc0000;
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

.copyright {
    margin-top: 1.25em; /* 20px */
    font-size: 0.875em; /* 14px */
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
    
    <div class="request-details-container">
        <h1>تفاصيل الطلب</h1>
        <div class="info-box" id="infoBox">
            <!-- عرض معلومات الطلب -->
            <p><strong>اسم مقدم الطلب:</strong> <?php echo htmlspecialchars($request['TraderName']); ?></p>
            <p><strong>تفاصيل العرض:</strong> <?php echo htmlspecialchars($request['OfferDetails']); ?></p>
            <!-- إظهار معلومات إضافية في حالة القبول -->
            <?php if ($request['RequestStatus'] === "تم القبول"): ?>
                <p id="emailField"><strong>إيميل مقدم الطلب:</strong> <?php echo htmlspecialchars($request['TraderEmail']); ?></p>
                <p id="phoneField"><strong>رقم مقدم الطلب:</strong> <?php echo htmlspecialchars($request['TraderPhone']); ?></p>
                <p><strong>معلومات صاحب الغرض:</strong> <?php echo htmlspecialchars($request['OwnerName']); ?> - <?php echo htmlspecialchars($request['OwnerCity']); ?></p>
            <?php endif; ?>
        </div>
        
        <?php if ($request['RequestStatus'] === "انتظار"): ?>
        <!-- إذا كانت الحالة "انتظار" يسمح للمالك بالتصرف -->
        <div class="buttons" id="buttonsContainer">
            <!-- أزرار قبول ورفض -->
            <form method="POST" style="display:inline;">
                <input type="hidden" name="action" value="accept">
                <button type="submit" class="btn-accept">قبول</button>
            </form>
            <form method="POST" style="display:inline;">
                <input type="hidden" name="action" value="reject">
                <button type="submit" class="btn-reject">رفض</button>
            </form>
        </div>
        <?php else: ?>
        <div class="status-message <?php echo ($request['RequestStatus'] === "تم القبول") ? "accepted" : "rejected"; ?>">
            <?php echo htmlspecialchars($request['RequestStatus']); ?>
        </div>
        <?php endif; ?>
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
