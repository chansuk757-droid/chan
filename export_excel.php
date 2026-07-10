<?php
// 1. ເຊື່ອມຕໍ່ຖານຂໍ້ມູນ
$servername = "localhost";
$username = "root";       
$password = "";           
$dbname = "report"; 

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8");

// 2. ຕັ້ງຄ່າດາວໂຫຼດ Excel
$filename = "cummins_employee_report_" . date('Y-m-d') . ".xls";
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=$filename");
header("Pragma: no-cache");
header("Expires: 0");

// 📊 Query ທີ 1: ຈັດອັນດັບພະນັກງານ + JOIN ເອົາ "ຊື່ພະນັກງານ" ມາສະແດງ
$sql_rank = "SELECT r.employee_id, 
                    e.name as employee_name, 
                    AVG(r.rating) as avg_rating, 
                    COUNT(r.id) as total_reviews 
             FROM service_reports r
             LEFT JOIN employees e ON r.employee_id = e.id 
             GROUP BY r.employee_id 
             ORDER BY avg_rating DESC";
$result_rank = $conn->query($sql_rank);

// 📋 Query ທີ 2: ດຶງປະຫວັດທັງໝົດ + JOIN ເອົາ "ຊື່ພະນັກງານ" ມາສະແດງ
$sql_raw = "SELECT r.id, 
                   e.name as employee_name, 
                   r.rating, 
                   r.category, 
                   r.comment, 
                   r.created_at 
            FROM service_reports r
            LEFT JOIN employees e ON r.employee_id = e.id 
            ORDER BY r.created_at DESC";
$result_raw = $conn->query($sql_raw);
?>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<!-- 🏆 ຕາຕະລາງທີ 1: ສະຫຼຸບອັນດັບພະນັກງານ (ສະແດງຊື່) -->
<h2>🏆 ສະຫຼຸບອັນດັບຄະແນນພະນັກງານ (Ranking Summary)</h2>
<table border="1" style="margin-bottom: 30px;">
    <thead>
        <tr style="background-color: #002060; color: white; font-weight: bold;">
            <th width="80">ອັນດັບ</th>
            <th width="200">ຊື່-ນາມສະກຸນ ພະນັກງານ</th>
            <th width="150">ຄະແນນດາວສະເລ່ຍ</th>
            <th width="150">ຈຳນວນຄັ້ງທີ່ຖືກປະເມີນ</th>
            <th width="250">ໝາຍເຫດ / ສະຖານະ</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if ($result_rank && $result_rank->num_rows > 0) {
            $rank = 1;
            $total_rows = $result_rank->num_rows;
            while($row = $result_rank->fetch_assoc()) {
                $avg = round($row['avg_rating'], 2);
                
                // ຖ້າບໍ່ມີຊື່ໃນລະບົບ ໃຫ້ສະແດງເປັນ ID ແທນ
                $emp_display = !empty($row['employee_name']) ? $row['employee_name'] : "ID: " . $row['employee_id'];
                
                $bg_color = "";
                $status_text = "ປານກາງ";
                
                if ($rank == 1) {
                    $bg_color = "background-color: #E2EFDA;"; 
                    $status_text = "🥇 ໄດ້ດາວຫຼາຍທີ່ສຸດ (ດີເລີດ)";
                } elseif ($rank == $total_rows && $total_rows > 1) {
                    $bg_color = "background-color: #FCE4D6;"; 
                    $status_text = "⚠️ ໄດ້ດາວໜ້ອຍທີ່ສຸດ (ຄວນປັບປຸງ)";
                }
                
                echo "<tr style='$bg_color'>";
                echo "<td style='text-align:center;'><b>" . $rank . "</b></td>";
                echo "<td>" . htmlspecialchars($emp_display) . "</td>"; // 🛠️ ສະແດງຊື່ພະນັກງານ
                echo "<td style='text-align:center;'><b>" . $avg . " / 5 ດາວ</b></td>";
                echo "<td style='text-align:center;'>" . $row['total_reviews'] . " ຄັ້ງ</td>";
                echo "<td>" . $status_text . "</td>";
                echo "</tr>";
                $rank++;
            }
        } else {
            echo "<tr><td colspan='5' style='text-align:center;'>ບໍ່ມີຂໍ້ມູນ</td></tr>";
        }
        ?>
    </tbody>
</table>

<br><hr><br>

<!-- 📋 ຕາຕະລາງທີ 2: ລາຍລະອຽດປະຫວັດການປະເມີນ (ສະແດງຊື່) -->
<h2>📋 ປະຫວັດການລົງຄະແນນທັງໝົດ (All Evaluation Logs)</h2>
<table border="1">
    <thead>
        <tr style="background-color: #1F4E78; color: white; font-weight: bold;">
            <th>ລຳດັບ</th>
            <th>ຊື່ພະນັກງານ</th>
            <th>ຄະແນນປະເມີນ</th>
            <th>ໝວດໝູ່ທີ່ປະເມີນ</th>
            <th>ຄຳຕิຊົມ / ຄຳເຫັນ</th>
            <th>ວັນທີ-ເວລາ</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if ($result_raw && $result_raw->num_rows > 0) {
            $num = 1;
            while($row = $result_raw->fetch_assoc()) {
                $emp_display = !empty($row['employee_name']) ? $row['employee_name'] : "ID: " . $row['employee_id'];
                
                echo "<tr>";
                echo "<td style='text-align:center;'>" . $num++ . "</td>";
                echo "<td>" . htmlspecialchars($emp_display) . "</td>"; // 🛠️ ສະແດງຊື່ພະນັກງານ
                echo "<td style='text-align:center;'>" . htmlspecialchars($row['rating']) . " ດາວ</td>";
                echo "<td>" . htmlspecialchars($row['category']) . "</td>";
                echo "<td>" . htmlspecialchars($row['comment']) . "</td>";
                echo "<td>" . htmlspecialchars($row['created_at']) . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='6' style='text-align:center;'>ບໍ່ມີຂໍ້ມູນ</td></tr>";
        }
        ?>
    </tbody>
</table>

<?php $conn->close(); ?>