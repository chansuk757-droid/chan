<?php
// 1. ເເຊື່ອມຕໍ່ຖານຂໍ້ມູນ
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "report"; 

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }
$conn->set_charset("utf8");

// 2. ຄຳນວນສະຖິຕິລວມ (ຈຳນວນຜູ້ປະເມີນທັງໝົດ ແລະ ຄະແນນສະເລ່ຍທັງໝົດ)
$sql_stats = "SELECT COUNT(id) as total_reviews, AVG(rating) as avg_rating FROM service_reports";
$res_stats = $conn->query($sql_stats);
$stats = $res_stats->fetch_assoc();

// 3. ດຶງຂໍ້ມູນຄະແນນສະເລ່ຍແຍກຕາມແຕ່ລະພະນັກງານ
$sql_emp_report = "SELECT e.emp_code, e.name, e.position, AVG(r.rating) as emp_avg, COUNT(r.id) as emp_reviews 
                   FROM service_reports r
                   JOIN employees e ON r.employee_id = e.id
                   GROUP BY r.employee_id
                   ORDER BY emp_avg DESC";
$result_emp_report = $conn->query($sql_emp_report);

// 4. ດຶງລາຍການການປະເມີນຫຼ້າສຸດທັງໝົດ
$sql_latest = "SELECT r.rating, r.category, r.comment, r.created_at, e.name as emp_name, e.emp_code
               FROM service_reports r
               JOIN employees e ON r.employee_id = e.id
               ORDER BY r.created_at DESC";
$result_latest = $conn->query($sql_latest);
?>

<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - ລະບົບສະຫຼຸບຜົນການປະເມີນ</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Lao:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <style>
        :root {
            --primary: #2563eb;
            --success: #10b981;
            --warning: #f59e0b;
            --bg: #f8fafc;
            --card: #ffffff;
            --text: #1e293b;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Noto Sans Lao', sans-serif; }
        body { background-color: var(--bg); color: var(--text); padding: 30px 20px; }
        
        .dashboard-container { max-width: 1100px; margin: 0 auto; }
        
        .dash-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .dash-header h1 { font-size: 26px; font-weight: 700; color: #0f172a; }
        
        /* ບັດສະຫຼຸບຕົວເລກ (Cards) */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 35px; }
        .stat-card { background: var(--card); padding: 25px; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 20px; }
        .icon-box { width: 50px; height: 50px; border-radius: 12px; display: flex; justify-content: center; align-items: center; color: white; }
        .stat-info h3 { font-size: 24px; font-weight: 700; }
        .stat-info p { font-size: 14px; color: #64748b; }
        
        /* ຕາຕະລາງ ແລະ ລາຍການ */
        .section-box { background: var(--card); padding: 25px; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); margin-bottom: 35px; }
        .section-box h2 { font-size: 18px; margin-bottom: 20px; color: #0f172a; display: flex; align-items: center; gap: 8px; }
        
        table { width: 100%; border-collapse: collapse; text-align: left; font-size: 14px; }
        th { background: #f1f5f9; padding: 14px; font-weight: 600; color: #475569; }
        td { padding: 14px; border-bottom: 1px solid #f1f5f9; }
        tr:hover { background: #f8fafc; }
        
        .badge-rating { background: #fef3c7; color: #d97706; padding: 4px 10px; border-radius: 20px; font-weight: 600; font-size: 13px; }
        
        /* ລາຍການຄວາມຄິດເຫັນຫຼ້າສຸດ */
        .review-list { display: flex; flex-direction: column; gap: 15px; max-height: 400px; overflow-y: auto; padding-right: 5px; }
        .review-item { background: #f8fafc; padding: 15px; border-radius: 12px; border: 1px solid #e2e8f0; }
        .review-meta { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 13px; color: #64748b; }
        .review-text { font-size: 14px; font-weight: 500; color: #334155; }
        .review-tags { font-size: 12px; color: var(--primary); background: #eff6ff; display: inline-block; padding: 2px 8px; border-radius: 6px; margin-top: 8px; font-weight: 600; }
    </style>
</head>
<body>

<div class="dashboard-container">
    
    <div class="dash-header">
        <h1>📊 ລະບົບຕິດຕາມ ແລະ ສະຫຼຸບຜົນການປະເມີນ</h1>
        <p style="color:#64748b; font-size:14px;">ອັບເດດຂໍ້ມູນຫຼ້າສຸດແບບ Real-time</p>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="icon-box" style="background: var(--primary);"><span class="material-icons">people</span></div>
            <div class="stat-info">
                <h3><?php echo $stats['total_reviews']; ?> ເທື່ອ</h3>
                <p>ຈຳນວນຜູ້ປະເມີນທັງໝົດ</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="icon-box" style="background: var(--warning);"><span class="material-icons">star</span></div>
            <div class="stat-info">
                <h3><?php echo number_format($stats['avg_rating'], 1); ?> / 5.0</h3>
                <p>ຄະແນນສະເລ່ຍທັງອົງກອນ</p>
            </div>
        </div>
    </div>

    <div class="section-box">
        <h2><span class="material-icons" style="color:var(--primary);">analytics</span> ຈັດອັນດັບຄະແນນປະເມີນຂອງພະນັກງານ</h2>
        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>ລະຫັດ</th>
                        <th>ຊື່ພະນັກງານ</th>
                        <th>ຕຳແໜ່ງ</th>
                        <th>ຈຳນວນຜູ້ປະເມີນ</th>
                        <th>ຄະແນນສະເລ່ຍ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result_emp_report->num_rows > 0) {
                        while($row = $result_emp_report->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td><strong>".$row['emp_code']."</strong></td>";
                            echo "<td>".$row['name']."</td>";
                            echo "<td>".$row['position']."</td>";
                            echo "<td>".$row['emp_reviews']." ເທື່ອ</td>";
                            echo "<td><span class='badge-rating'>⭐ ".number_format($row['emp_avg'], 1)."</span></td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5' style='text-align:center; color:#64748b;'>ບໍ່ທັນມີຂໍ້ມູນການປະເມີນ</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="section-box">
        <h2><span class="material-icons" style="color:var(--success);">comment</span> ຄວາມຄິດເຫັນ ແລະ ຟີດແບັກຫຼ້າສຸດ</h2>
        <div class="review-list">
            <?php
            if ($result_latest->num_rows > 0) {
                while($row = $result_latest->fetch_assoc()) {
                    echo "<div class='review-item'>";
                    echo "  <div class='review-meta'>";
                    echo "      <span>ປະເມີນໃຫ້: <strong>".$row['emp_name']." (".$row['emp_code'].")</strong></span>";
                    echo "      <span>ຄະແນນ: <strong style='color:var(--warning);'>★ ".$row['rating']."</strong> | ".date('d/m/Y H:i', strtotime($row['created_at']))."</span>";
                    echo "  </div>";
                    echo "  <div class='review-text'>".(!empty($row['comment']) ? htmlspecialchars($row['comment']) : "<span style='color:#94a3b8; font-style:italic;'>- ບໍ່ມີຄຳຄິດເຫັນເພີ່ມເຕີມ -</span>")."</div>";
                    if(!empty($row['category'])) {
                        echo "  <div class='review-tags'>🏷️ ".$row['category']."</div>";
                    }
                    echo "</div>";
                }
            } else {
                echo "<p style='text-align:center; color:#64748b;'>ບໍ່ທັນມີຂໍ້ຄວາມໃດໆ</p>";
            }
            ?>
        </div>
    </div>

</div>

</body>
</html>