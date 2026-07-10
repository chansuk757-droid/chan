<?php
// 1. ເຊື່ອມຕໍ່ຖານຂໍ້ມູນ
$servername = "localhost";
$username = "root";       
$password = "";           
$dbname = "report"; 

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }
$conn->set_charset("utf8");

// 2. ກວດສອບວ່າມີການສົ່ງຂໍ້ມູນມາແບບ POST ຫຼືບໍ່
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $employee_id = (int)$_POST['employee_id'];
    $rating = (int)$_POST['rating'];
    $comment = isset($_POST['comment']) ? $conn->real_escape_string($_POST['comment']) : '';
    
    // 🛠️ ວິທີແກ້ໄຂຈຸດທີ 1: ລວມຄ່າຈາກ Checkbox (Array) ໃຫ້ກາຍເປັນຂໍ້ຄວາມ String
    $category_string = "";
    if (isset($_POST['category']) && is_array($_POST['category'])) {
        // ເອົາທຸກແທັກທີ່ຕິກມາຂັ້ນດ້ວຍເຄື່ອງໝາຍຈຸດ ເຊັ່ນ: "ບໍລິການວ່ອງໄວ, ກິລິຍາມາລະຍາດດີ"
        $category_string = $conn->real_escape_string(implode(", ", $_POST['category']));
    }

    // 3. ຄຳສັ່ງ SQL ບັນທຶກຂໍ້ມູນ (ໃຊ້ $category_string ທີ່ເຮົາແປງແລ້ວ)
    $sql = "INSERT INTO service_reports (employee_id, rating, category, comment, created_at) 
            VALUES ($employee_id, $rating, '$category_string', '$comment', NOW())";

    if ($conn->query($sql) === TRUE) {
        // ບອກໃຫ້ບຣາວເຊີແຈ້ງເຕືອນ ແລະ ເດັ້ງກັບໄປໜ້າຟອມ ຫຼື ໜ້າຂອບໃຈ
        echo "<script>
                alert('ຂອບໃຈສຳລັບການປະເມີນຜົນ!');
                window.location.href = 'index.php'; 
              </script>";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

$conn->close();
?>