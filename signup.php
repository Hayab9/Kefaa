<?php
$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "it320";

// الاتصال بقاعدة البيانات
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $age = intval($_POST['age']);
    $gender = $_POST['gender'];
    $city = $_POST['city'];
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // التأكد إذا البريد أو الجوال مستخدم مسبقًا
    $check = $conn->prepare("SELECT * FROM users WHERE Email = ? OR PhoneNumber = ?");
    $check->bind_param("ss", $email, $phone);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        echo "البريد الإلكتروني أو رقم الجوال مستخدم مسبقًا.";
    } else {
        $insert = $conn->prepare("INSERT INTO users (Email, Name, Age, PhoneNumber, Password, City, Gender)
                                  VALUES (?, ?, ?, ?, ?, ?, ?)");
        $insert->bind_param("ssissss", $email, $name, $age, $phone, $password, $city, $gender);
        if ($insert->execute()) {
            echo "✅ تم إنشاء الحساب بنجاح! يمكنك الآن تسجيل الدخول.";
             header("Location: login.html?registered=1");
    exit();
        } else {
            echo "❌ حدث خطأ أثناء إنشاء الحساب.";
        }
        $insert->close();
    }

    $check->close();
}
$conn->close();
?>
