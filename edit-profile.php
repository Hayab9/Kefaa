<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$servername = "localhost";
$username = "root";
$db_password = "root";
$dbname = "it320";

$conn = new mysqli($servername, $username, $db_password, $dbname);
if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];
// تسجيل الخروج
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_unset();
    session_destroy();
    header("Location: login.html");
    exit();
}

// جلب بيانات المستخدم الحالي
$stmt = $conn->prepare("SELECT * FROM users WHERE UserID = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// معالجة النموذج عند الإرسال
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name   = trim($_POST['name']);
    $email  = trim($_POST['email']);
    $phone  = trim($_POST['phone']);
    $city   = trim($_POST['city']);
    $age    = intval($_POST['age']);
    $gender = trim($_POST['gender']);

    if ($name == "" || $email == "" || $phone == "" || $city == "" || $age < 18 || $gender == "") {
        $error = "يرجى ملء جميع الحقول المطلوبة بشكل صحيح.";
    } else {
        // تعديل سلسلة النوع لتكون "ssssisi" (كل الحقول نصية باستثناء العمر وUserID)
        $stmt_upd = $conn->prepare("UPDATE users SET Name = ?, Email = ?, PhoneNumber = ?, City = ?, Age = ?, Gender = ? WHERE UserID = ?");
        $stmt_upd->bind_param("ssssisi", $name, $email, $phone, $city, $age, $gender, $user_id);
        if ($stmt_upd->execute()) {
            header("Location: profile.php");
            exit();
        } else {
            $error = "حدث خطأ أثناء تحديث البيانات.";
        }
        $stmt_upd->close();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>تعديل الملف الشخصي - كِفَاء</title>
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

/* ====== EDIT PROFILE CONTAINER ====== */
.edit-profile-container {
    max-width: 37.5em; /* 600px */
    margin: 2.5em auto; /* 40px */
    background: white;
    padding: 1.25em; /* 20px */
    border-radius: 0.625em; /* 10px */
    box-shadow: 0 0.25em 0.375em rgba(0, 0, 0, 0.1); /* 4px 6px */
}

.edit-profile-container h2 {
    text-align: center;
}

/* ====== FORM STYLES ====== */
.form-group {
    margin-bottom: 0.9375em; /* 15px */
}

.form-group label {
    display: block;
    font-weight: bold;
    margin-bottom: 0.3125em; /* 5px */
}

.form-group input, 
.form-group select {
    width: 100%;
    padding: 0.625em; /* 10px */
    border: 0.0625em solid #ccc; /* 1px */
    border-radius: 0.3125em; /* 5px */
    font-size: 1em; /* 16px */
    height: 2.5em; /* 40px */
    box-sizing: border-box;
}

/* ====== SAVE BUTTON ====== */
.save-btn {
    width: 100%;
    padding: 0.625em; /* 10px */
    background-color: #035352;
    color: white;
    border: none;
    border-radius: 0.3125em; /* 5px */
    font-size: 1em; /* 16px */
    cursor: pointer;
}

.save-btn:hover {
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
                    <a href="profile.php"><img src="user-icon.png" style="width:30px; height:30px;" alt="الملف الشخصي"></a>
                    <img src="search-icon.png" id="searchIcon" style="width:25px; height:25px;" alt="بحث">
                </li>
                <li><a href="profile.php?action=logout" class="login-btn">تسجيل الخروج</a></li>
            </ul>
        </nav>
    </header>
    <div id="searchBarContainer" class="search-box">
        <input type="text" id="searchInput" placeholder="ابحث عن المنتجات أو الخدمات...">
        <button onclick="performSearch()">🔍</button>
    </div>
    <div class="edit-profile-container">
        <h2>تعديل الملف الشخصي</h2>
        <?php if (isset($error)) { echo "<p style='color:red;'>$error</p>"; } ?>
        <form method="POST" id="editProfileForm">
            <div class="form-group">
                <label for="name">الاسم:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['Name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">البريد الإلكتروني:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['Email']); ?>" required>
            </div>
            <div class="form-group">
                <label for="phone">رقم الهاتف:</label>
                <input type="text" id="phone" name="phone" pattern="\d{10}" value="<?php echo htmlspecialchars($user['PhoneNumber']); ?>" required placeholder="أدخل 10 أرقام">
            </div>
            <div class="form-group">
                <label for="city">المدينة:</label>
                <select id="city" name="city" required>
                    <option value="الرياض" <?php if($user['City'] == "الرياض") echo "selected"; ?>>الرياض</option>
                    <option value="جدة" <?php if($user['City'] == "جدة") echo "selected"; ?>>جدة</option>
                    <option value="الدمام" <?php if($user['City'] == "الدمام") echo "selected"; ?>>الدمام</option>
                    <option value="مكة" <?php if($user['City'] == "مكة") echo "selected"; ?>>مكة</option>
                </select>
            </div>
            <div class="form-group">
                <label for="age">العمر:</label>
                <input type="number" id="age" name="age" value="<?php echo htmlspecialchars($user['Age']); ?>" min="18" required>
            </div>
            <div class="form-group">
                <label for="gender">الجنس:</label>
                <select id="gender" name="gender" required>
                    <option value="Male" <?php if($user['Gender'] == "Male") echo "selected"; ?>>ذكر</option>
                    <option value="Female" <?php if($user['Gender'] == "Female") echo "selected"; ?>>أنثى</option>
                </select>
            </div>
            <button type="submit" class="submit-btn">حفظ التعديلات</button>
        </form>
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
