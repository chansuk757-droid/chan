<?php
// 🌟 ຮັບຄ່າຈາກຟອມດ້ວຍ POST (ກວດເບິ່ງວ່າໄດ້ຂຽນແຖວເຫຼົ່ານີ້ແລ້ວຫຼືຍັງ)
$employee_id = $_POST['employee_id'] ?? '';
$rating      = $_POST['rating'] ?? '';
$category    = $_POST['category'] ?? '';
$comment     = $_POST['comment'] ?? '';
// 1. ເຊື່ອມຕໍ່ຖານຂໍ້ມູນ
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "report"; // ປ່ຽນໃຫ້ກົງກັບຊື່ DB ຂອງທ່ານ

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) { 
    die("Connection failed: " . $conn->connect_error); 
}
$conn->set_charset("utf8");
// 3. ຄຳສັ່ງ SQL ບັນທຶກລົງຕາຕະລາງ service_reports
    $sql = "INSERT INTO service_reports (employee_id, rating, category, comment) 
            VALUES ('$employee_id', '$rating', '$category', '$comment')";

    if ($conn->query($sql) === TRUE) {
        // 🌟 ດຶງລະຫັດ emp_code ຂອງພະນັກງານຄົນນັ້ນອອກມາ ເພື່ອເອົາໄປຕໍ່ທ້າຍ URL
        $sql_emp = "SELECT emp_code FROM employees WHERE id = '$employee_id'";
        $res_emp = $conn->query($sql_emp);
        $emp_data = $res_emp->fetch_assoc();
        $redirect_code = $emp_data['emp_code'];

        // 🌟 ສົ່ງກັບໄປໜ້າ index.php ໂດຍລະບຸ IP ແລະ ລະຫັດພະນັກງານແບບຟິກຕາຍຕົວເລີຍ (ແອັບ Google ຈະຍອມໃຫ້ຜ່ານ)
        echo "<script>
                alert('✨ ຂອບໃຈຫຼາຍໆ ທີ່ໃຫ້ຄຳປະເມີນການບໍລິການຂອງພວກເຮົາ!');
                window.location.href = 'index.php?emp=" . $redirect_code . "';
              </script>";
        exit();
    } else {
        echo "❌ ບໍ່ສາມາດບັນທຶກຂໍ້ມູນໄດ້: " . $conn->error;
    }

// 2. ຮັບຂໍ້ມູນຈາກຟອມໜ້າບ້ານ
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $employee_id = $_POST['employee_id'];
    $rating = $_POST['rating'];
    
    // ປ້ອງກັນການແຮກ ຫຼື ການໃສ່ອັກສອນພິເສດ (SQL Injection)
    $comment = $conn->real_escape_string($_POST['comment']);

    // ດຶງຄ່າຈາກ Checkbox (ຂໍ້ຄວາມແທັກທີ່ລູກຄ້າກົດເລືອກ) ມາລວມກັນເປັນຂໍ້ຄວາມດຽວ
    // ຕົວຢ່າງ: "ບໍລິການວ່ອງໄວ, ມາລະຍາດດີຍິ້ມແຍ້ມ"
    $category_box = isset($_POST['category']) ? $_POST['category'] : array();
    $category = $conn->real_escape_string(implode(", ", $category_box));

    // 3. ຄຳສັ່ງ SQL ບັນທຶກລົງຕາຕະລາງ service_reports (ບັນທຶກ 1 ແຖວ ເຂົ້າທຸກຄໍລຳພ້ອມກັນ)
    $sql = "INSERT INTO service_reports (employee_id, rating, category, comment) 
            VALUES ('$employee_id', '$rating', '$category', '$comment')";

    // 4. ກວດສອບຜົນການບັນທຶກ
    if ($conn->query($sql) === TRUE) {
        // ເມື່ອສຳເລັດ ໃຫ້ສະແດງ Alert ແລ້ວເດັ້ງກັບໄປໜ້າເຄົາເຕີຄົນເກົ່າທັນທີ
        echo "<script>
                alert('✨ ຂອບໃຈຫຼາຍໆ ທີ່ໃຫ້ຄຳປະເມີນການບໍລິການຂອງພວກເຮົາ!');
                window.location.href = document.referrer;
              </script>";
    } else {
        // ຖ້າ Error ໃຫ້ສະແດງຂໍ້ຄວາມເຕືອນ
        echo "❌ ບໍ່ສາມາດບັນທຶກຂໍ້ມູນໄດ້: " . $conn->error;
    }
}

$conn->close();
?>