<?php
session_start();

?>
<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>كِفَاء - الصفحة الرئيسية</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
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
    position: relative;
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

.search-icon {
    width: 1.5625em; /* 25px */
    height: 1.5625em; /* 25px */
    cursor: pointer;
}

.hero {
    position: relative;
    height: 60vh;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    color: white;
    overflow: hidden;
}

.hero video {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    z-index: -1;
}

.hero h1 {
    font-size: 2.25em; /* 36px */
    margin-bottom: 0.625em; /* 10px */
}

.hero p {
    font-size: 1.25em; /* 20px */
    margin-bottom: 1.25em; /* 20px */
}

.cta-btn {
    background-color: #028484;
    color: white;
    padding: 0.625em 1.25em; /* 10px 20px */
    text-decoration: none;
    border-radius: 0.3125em; /* 5px */
}

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
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="profile.php"><img src="user-icon.png" style="width:30px;height:30px;" alt="الملف الشخصي"></a>
                <?php else: ?>
                    <a href="login.html"><img src="user-icon.png" style="width:30px;height:30px;" alt="الملف الشخصي"></a>
                <?php endif; ?>
                <img src="search-icon.png" id="searchIcon" style="width:25px;height:25px;" alt="بحث">
            </li>
            <li>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="profile.php?action=logout" class="login-btn">تسجيل الخروج</a>
                <?php else: ?>
                    <a href="login.html" class="login-btn">تسجيل الدخول/إنشاء حساب</a>
                <?php endif; ?>
            </li>
        </ul>
    </nav>
</header>

    <div id="searchBarContainer" class="search-box">
        <input type="text" id="searchInput" placeholder="ابحث عن المنتجات أو الخدمات...">
        <button onclick="performSearch()">🔍</button>
    </div>

    <section class="hero">
        <video autoplay muted loop>
            <source src="background-video.mp4" type="video/mp4">
        </video>
        <div class="container"> 
            <h1>مرحبًا بك في منصة كِفَاء</h1>
            <p>ابدأ بتبادل المنتجات والخدمات بسهولة وبدون مقابل مالي!</p>
            <a href="trading.php" class="cta-btn">ابدأ الآن</a>
        </div>
    </section>
    
    <footer>
        <div class="footer-sections">
            <div class="footer-section">
                <h3>روابط سريعة</h3>
                <p><a href='index.html'>الرئيسية</a> | <a href='about.html'>من نحن</a> | <a href='trading.php'>التبادل</a></p>
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
        <div class="copyright">&copy; 2025 كِفَاء. جميع الحقوق محفوظة.</div>
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
