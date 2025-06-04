<?php
session_start();

$servername = "localhost";
$username = "root";
$db_password = "root";  // هنا نغير اسم المتغير الخاص بكلمة مرور قاعدة البيانات
$dbname = "it320";

// الاتصال بقاعدة البيانات
$conn = new mysqli($servername, $username, $db_password, $dbname);
if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = trim($_POST['emailOrPhone']);
    $inputPassword = $_POST['password'];

    // التحقق هل الإدخال بريد أو جوال
    if (filter_var($input, FILTER_VALIDATE_EMAIL)) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE Email = ?");
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE PhoneNumber = ?");
    }

    $stmt->bind_param("s", $input);
    $stmt->execute();
    $result = $stmt->get_result();

    // جلب بيانات المستخدم
    if ($user = $result->fetch_assoc()) {
        if (password_verify($inputPassword, $user['Password'])) {
            // الكلمة المدخلة تطابق الهاش
            $_SESSION['user_id'] = $user['UserID'];
            $_SESSION['user_name'] = $user['Name'];
            header("Location: profile.php");
            exit();
        } else {
            // إذا فشل التحقق بواسطة الهاش، نفترض أن الكلمة في DB غير مشفرة
            if ($inputPassword == $user['Password']) {
                // الكلمة المدخلة تطابق النص العادي المخزن
                // تحديث السجل لتشفير كلمة المرور
                $newHash = password_hash($inputPassword, PASSWORD_DEFAULT);
                $updateStmt = $conn->prepare("UPDATE users SET Password = ? WHERE UserID = ?");
                $updateStmt->bind_param("si", $newHash, $user['UserID']);
                $updateStmt->execute();
                $updateStmt->close();

                $_SESSION['user_id'] = $user['UserID'];
                $_SESSION['user_name'] = $user['Name'];
                header("Location: profile.php");
                exit();
            } else {
                echo "❌ كلمة المرور غير صحيحة.";
            }
        }
    } else {
        echo "❌ لا يوجد مستخدم بهذه البيانات.";
    }

    $stmt->close();
}
$conn->close();
?>
