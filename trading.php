<?php
session_start();

$servername = "localhost";
$username = "root";
$db_password = "root";
$dbname = "it320";

// Create database connection
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
// Query all posts from the database
$sql = "SELECT * FROM posts";
$result = $conn->query($sql);
$posts = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $posts[] = $row;
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>التبادل - كِفَاء</title>
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

.trading-header {
    text-align: center;
    padding: 3.75em 1.25em; /* 60px = 3.75em, 20px = 1.25em */
    background-color: #e0f7f7;
}

.search-bar {
    text-align: center;
    margin: 1.25em 0; /* 20px */
}

.search-bar input {
    width: 50%;
    padding: 0.625em; /* 10px */
    border: 0.0625em solid #ccc; /* 1px */
    border-radius: 0.3125em; /* 5px */
    font-size: 1em; /* 16px */
}

.filters {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 0.625em; /* 10px */
    margin: 1.25em 0; /* 20px */
}

.filters button {
    background-color: #028484;
    color: white;
    border: none;
    padding: 0.625em 0.9375em; /* 10px 15px */
    border-radius: 0.3125em; /* 5px */
    cursor: pointer;
    font-size: 1em; /* 16px */
}

.filters button:hover {
    background-color: #026666;
}

.trading-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.25em; /* 20px */
    padding: 1.25em; /* 20px */
    text-align: center;
}

.trade-item {
    background: white;
    padding: 0.9375em; /* 15px */
    border-radius: 0.5em; /* 8px */
    box-shadow: 0 0.25em 0.375em rgba(0, 0, 0, 0.1); /* 4px 6px */
    transition: transform 0.3s ease-in-out;
}

.trade-item:hover {
    transform: scale(1.05);
}

.trade-item img {
    width: 100%;
    height: 11.25em; /* 180px */
    border-radius: 0.5em; /* 8px */
    object-fit: cover;
}

.trade-item .view-btn {
    display: inline-block;
    margin-top: 0.625em; /* 10px */
    padding: 0.5em 0.9375em; /* 8px 15px */
    background-color: #035352;
    color: white;
    text-decoration: none;
    border-radius: 0.3125em; /* 5px */
    font-weight: bold;
}

.trade-item .view-btn:hover {
    background-color: #028484;
}

#noResults {
    text-align: center;
    display: none;
    color: gray;
    font-size: 1.125em; /* 18px */
    margin-top: 1.25em; /* 20px */
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

/* Responsive */
@media (max-width: 900px) {
    .trading-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 600px) {
    .trading-grid {
        grid-template-columns: repeat(1, 1fr);
    }
}

/* Search Box */
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
    <section class="trading-header">
        <h1>ابدأ بالتبادل الآن</h1>
        <p>استكشف العروض وابحث عن التبادلات المثالية لك!</p>
    </section>
    <div class="search-bar">
        <input type="text" id="search" placeholder="ابحث عن المنتجات أو الخدمات..." onkeyup="searchItems()">
    </div>
    <div class="filters">
        <button onclick="filterItems('all')">الكل</button>
        <button onclick="filterItems('electronics')">الإلكترونيات</button>
        <button onclick="filterItems('clothing')">الملابس</button>
        <button onclick="filterItems('home')">الأثاث</button>
        <button onclick="filterItems('books')">الكتب</button>
        <button onclick="filterItems('services')">الخدمات</button>
        <button onclick="filterItems('health')">الصحة</button>
    </div>
    <div id="noResults">لا توجد نتائج مطابقة</div>
    <div class="trading-grid" id="tradingGrid">
        <?php if (!empty($posts)): ?>
            <?php foreach ($posts as $post): ?>
                <div class="trade-item" data-category="<?php echo htmlspecialchars($post['Category']); ?>">
                    <img src="<?php echo htmlspecialchars($post['ImageURL']); ?>" alt="<?php echo htmlspecialchars($post['PostTitle']); ?>">
                    <h3><?php echo htmlspecialchars($post['PostTitle']); ?></h3>
                    <p><?php echo htmlspecialchars($post['Description']); ?></p>
                    <a href="post.php?postId=<?php echo $post['PostID']; ?>" class="view-btn">عرض التفاصيل</a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p id="noResults" style="display:block;">لا توجد نتائج مطابقة</p>
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
        function searchItems() {
            let input = document.getElementById("search").value.toLowerCase();
            let items = document.querySelectorAll(".trade-item");
            let found = false;
            items.forEach(item => {
                let text = item.innerText.toLowerCase();
                if (text.includes(input)) {
                    item.style.display = "block";
                    found = true;
                } else {
                    item.style.display = "none";
                }
            });
            document.getElementById("noResults").style.display = found ? "none" : "block";
        }
        function filterItems(category) {
            let items = document.querySelectorAll('.trade-item');
            let found = false;
            items.forEach(item => {
                let match = category === 'all' || item.dataset.category === category;
                item.style.display = match ? "block" : "none";
                if (match) found = true;
            });
            document.getElementById("noResults").style.display = found ? "none" : "block";
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
