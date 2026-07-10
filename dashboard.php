<?php
session_start();
// ຖ້າບໍ່ທັນໄດ້ Login ໃຫ້ເດັ້ງກັບໄປໜ້າ login.php ທັນທີ
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// 🚪 ລະບົບອອກຈາກລະບົບ (Logout)
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit;
}

// --- ໂຄ້ດເຊື່ອມຕໍ່ຖານຂໍ້ມູນ ແລະ Query ຕ່າງໆຂອງທ່ານ... ---
$servername = "localhost";

// --- ໂຄ້ດເຊື່ອມຕໍ່ຖານຂໍ້ມູນຂອງທ່ານທີ່ມີຢູ່ແລ້ວຢູ່ດ້ານລຸ່ມ ---
$servername = "localhost";

// 1. ເຊື່ອມຕໍ່ຖານຂໍ້ມູນ
$servername = "localhost";
$username = "root";       
$password = "";           
$dbname = "report"; 

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }
$conn->set_charset("utf8");

// ❌ ລະບົບລົບພະນັກງານ
if (isset($_GET['delete_emp_id'])) {
    $del_id = (int)$_GET['delete_emp_id'];
    $conn->query("DELETE FROM service_reports WHERE employee_id = $del_id");
    if ($conn->query("DELETE FROM employees WHERE id = $del_id")) {
        echo "<script>alert('🗑️ ລົບພະນັກງານ ແລະ ປະຫວັດການປະ微ນສຳເລັດແລ້ວ!'); window.location='dashboard.php';</script>";
        exit();
    }
}

// 📝 [ຟີເຈີໃໝ່] ລະບົບອັບເດດ/ແກ້ໄຂຂໍ້ມູນພະນັກງານ
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'update_emp') {
    $emp_id = (int)$_POST['emp_id'];
    $update_code = $conn->real_escape_string(trim($_POST['update_code']));
    $update_name = $conn->real_escape_string(trim($_POST['update_name']));
    $update_position = $conn->real_escape_string(trim($_POST['update_position'])); // ຮັບຄ່າຕຳແໜ່ງໃໝ່

    if (!empty($update_code) && !empty($update_name)) {
        $check_duplicate = $conn->query("SELECT id FROM employees WHERE emp_code = '$update_code' AND id != $emp_id");
        if ($check_duplicate->num_rows > 0) {
            echo "<script>alert('❌ ລະຫັດພະນັກງານນີ້ມີຢູ່ໃນລະບົບແລ້ວ!'); window.location='dashboard.php';</script>";
            exit();
        }

        // ເພີ່ມ position = '$update_position' ເຂົ້າໃນ SQL
        $update_sql = "UPDATE employees SET emp_code = '$update_code', name = '$update_name', position = '$update_position' WHERE id = $emp_id";
        if ($conn->query($update_sql)) {
            echo "<script>alert('💾 ອັບເດດຂໍ້ມູນພະນັກງານສຳເລັດແລ້ວ!'); window.location='dashboard.php';</script>";
            exit();
        }
    }
}

// ➕ 2. ລະບົບຮັບຄ່າຈາກຟອມເພື່ອ "ສ້າງພະນັກງານໃໝ່ ແລະ QR Code"
$generated_qr_url = "";
$new_emp_code = "";
$new_emp_name = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'create_qr') {
    $new_emp_code = $conn->real_escape_string(trim($_POST['new_emp_id'])); 
    $new_emp_name = $conn->real_escape_string(trim($_POST['new_emp_name'])); 
    
    if(!empty($new_emp_code)) {
        $check_stmt = $conn->query("SELECT id FROM employees WHERE emp_code = '$new_emp_code'");
        
        if($check_stmt->num_rows == 0) {
            $insert_sql = "INSERT INTO employees (emp_code, name, position, department) VALUES ('$new_emp_code', '$new_emp_name', 'ພະນັກງານ', 'General')";
            $conn->query($insert_sql);
        }
        
        $domain = "hanky-oppressor-flagstick.ngrok-free.dev"; 
        $target_url = "https://" . $domain . "/LCD/index.php?emp=" . urlencode($new_emp_code);
        $generated_qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($target_url);
    }
}

// 🔍 4. ລະບົບຄົ້ນຫາ/ກັ່ນຕອງ (Filters) ຕາຕະລາງປະຫວັດ
$search_cate = isset($_GET['filter_category']) ? $_GET['filter_category'] : '';

// 📊 5. ດຶງຂໍ້ມູນມາວິເຄາະເຮັດກ່ອງ KPI Cards
$total_query = $conn->query("SELECT COUNT(id) as total, AVG(rating) as avg_rating FROM service_reports");
$kpi = $total_query->fetch_assoc();
$total_evaluations = $kpi['total'];
$average_rating = round($kpi['avg_rating'], 2);

// 📈 6. ດຶງຂໍ້ມູນຄະແນນສະເລ່ຍຂອງພະນັກງານ
$chart_names = []; $chart_ratings = [];
$chart_query = $conn->query("SELECT e.name as employee_name, r.employee_id, AVG(r.rating) as avg_rating FROM service_reports r LEFT JOIN employees e ON r.employee_id = e.id GROUP BY r.employee_id ORDER BY avg_rating DESC");
while($c_row = $chart_query->fetch_assoc()) {
    $chart_names[] = !empty($c_row['employee_name']) ? $c_row['employee_name'] : "ID: " . $c_row['employee_id'];
    $chart_ratings[] = round($c_row['avg_rating'], 2);
}

// 📋 7. ດຶງລາຍການປະຫວັດຕາຕະລາງດ້ານລຸ່ມ
$where_clause = "";
if(!empty($search_cate)) { $where_clause = "WHERE r.category LIKE '%" . $conn->real_escape_string($search_cate) . "%'"; }
$sql = "SELECT r.id, e.name as employee_name, r.employee_id, r.rating, r.category, r.comment, r.created_at FROM service_reports r LEFT JOIN employees e ON r.employee_id = e.id $where_clause ORDER BY r.created_at DESC";
$result = $conn->query($sql);

// 👥 8. ດຶງລາຍຊື່ພະນັກງານທັງໝົດອອກມາສະແດງ (ເພື່ອເອົາໄວ້ກົດລົບ ແລະ ແກ້ໄຂ)
$employees_result = $conn->query("SELECT * FROM employees ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <!-- 🔄 ສັ່ງໃຫ້ໜ້າເວັບຣີເຟຣຊ໌ເອງອັດຕະໂນມັດທຸກໆ 10 ວິນາທີ -->
    <meta http-equiv="refresh" content="60"> 
    <title>Cummins Evaluation Dashboard</title>
    
    <!-- 📄 ເຊື່ອມຕໍ່ CSS, FontAwesome ແລະ Chart.js -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        body { font-family: 'Arial', sans-serif; }
    </style>
    <!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fa-solid fa-chart-pie text-primary me-2"></i> ລະບົບຈັດການ ແລະ ລາຍງານຜົນ (Dashboard)</h2>
    <div>
        <a href="export_excel.php" class="btn btn-success me-2"><i class="fa-solid fa-file-excel me-1"></i> ສົ່ງອອກເປັນ Excel</a>
        <!-- ✨ ປຸ່ມອອກຈາກລະບົບໃໝ່ -->
        <a href="dashboard.php?logout=true" class="btn btn-danger" onclick="return confirm('ແນ່ໃຈບໍ່ວ່າຈະອອກຈາກລະບົບ?')"><i class="fa-solid fa-right-from-bracket me-1"></i> ອອກຈາກລະບົບ</a>
    </div>
</div>
</head>
    <style>
        body { font-family: 'Arial', sans-serif; }
    </style>
</head>
<body class="bg-light">

<div class="container-fluid py-4" style="max-width: 1300px;">

    <!-- 📊 KPI Cards -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card bg-white shadow-sm border-0 text-center py-3">
                <h3 class="text-primary font-weight-bold mb-1"><?php echo $total_evaluations; ?> ຄັ້ງ</h3>
                <p class="text-muted mb-0">ຈຳນວນການປະເມີນທັງໝົດ</p>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card bg-white shadow-sm border-0 text-center py-3">
                <h3 class="text-warning font-weight-bold mb-1">★ <?php echo $average_rating; ?> / 5.00</h3>
                <p class="text-muted mb-0">ຄະແນນສະເລ່ຍພາບລວມ</p>
            </div>
        </div>
    </div>

    <!-- ➕ ສ່ວນສ້າງ QR Code ພະນັກງານໃໝ່ -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title mb-3 text-primary"><i class="fa-solid fa-qrcode me-1"></i> ສ້າງ QR Code ພະນັກງານໃໝ່</h5>
                    <form method="POST" action="dashboard.php">
                        <input type="hidden" name="action" value="create_qr">
                        <div class="mb-3">
                            <label class="form-label fw-bold">ລະຫັດພະນັກງານ (Employee ID/Code):</label>
                            <input type="text" name="new_emp_id" class="form-control" placeholder="ຕົວຢ່າງ: EMP006" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">ຊື່-ນາມສະກຸນ ພະນັກງານ:</label>
                            <input type="text" name="new_emp_name" class="form-control" placeholder="ຕົວຢ່າງ: ທ້າວ ແຈັກມາ ປະທານ" required>
                        </div>
                        <button type="submit" class="btn btn-success w-100"><i class="fa-solid fa-wand-magic-sparkles me-1"></i> ບັນທຶກ ແລະ ຜະລິດ QR Code</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- 🖼️ ບ່ອນສະແດງຜົນ QR Code ທີ່ສ້າງແລ້ວ -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100 d-flex align-items-center justify-content-center text-center p-3">
                <?php if(!empty($generated_qr_url)): ?>
                    <div>
                        <h6 class="text-success mb-2">ສ້າງ QR Code สำເລັດແລ້ວ!</h6>
                        <p class="small text-muted mb-2">ພະນັກງານ: <?php echo htmlspecialchars($new_emp_name); ?> (Code: <?php echo htmlspecialchars($new_emp_code); ?>)</p>
                        <img src="<?php echo $generated_qr_url; ?>" alt="QR Code" class="img-thumbnail mb-3" style="width: 160px;">
                        <div>
                            <a href="<?php echo $generated_qr_url; ?>" download="QR_<?php echo $new_emp_code; ?>.png" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="fa-solid fa-download me-1"></i> ເປີດ/ດາວໂຫຼດຮູບ QR
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="text-muted">
                        <i class="fa-solid fa-qrcode fa-3x mb-2" style="opacity: 0.3;"></i>
                        <p class="mb-0">ป້ອນຂໍ້ມູນດ້ານຊ້າຍເພື່ອສ້າງ QR Code</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- 👥 ລາຍຊື່ພະນັກງານທັງໝົດໃນລະບົບ (ເພີ່ມປຸ່ມແກ້ໄຂ 📝) -->
    <div class="card border-0 shadow-sm mb-5">
        <div class="card-header bg-white py-3">
            <h5 class="card-title mb-0 text-dark"><i class="fa-solid fa-users text-primary me-1"></i> ລາຍຊື່ພະນັກງານທັງໝົດໃນລະບົບ</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive" style="max-height: 280px;">
                <table class="table table-hover align-middle mb-0">
                <thead class="table-secondary">
                    <tr>
                    <th class="text-center" width="80">ລຳດັບ</th> <!-- ປ່ຽນຈາກ ID ເປັນ ລຳດັບ -->
                    <th>ລະຫັດ (emp_code)</th>
                    <th>ຊື່-ນາມສະກຸນ</th>
                    <th>ຕຳແໜ່ງ</th>
                    <th class="text-center" width="200">ຈັດການ</th>
                    </tr>
                </thead>
                    <tbody>
    <?php
    if ($employees_result && $employees_result->num_rows > 0) {
        $emp_number = 1; // ✨ ສ້າງຕົວແປນັບລຳດັບເລີ່ມຈາກ 1
        
        while($emp_row = $employees_result->fetch_assoc()) {
            echo "<tr>";
            // ✨ ສະແດງເລກລຳດັບແທນ ID ຂອງຖານຂໍ້ມູນ
            echo "<td class='text-center text-muted'>{$emp_number}</td>";
            
            echo "<td><span class='badge bg-light text-dark border'>{$emp_row['emp_code']}</span></td>";
            echo "<td><b>{$emp_row['name']}</b></td>";
            echo "<td><small class='text-muted'>{$emp_row['position']}</small></td>";
            
            // ປຸ່ມຈັດການ (ຍັງໃຊ້ ID ຂອງຖານຂໍ້ມູນເພື່ອອ້າງອີງຄືເກົ່າ ເພື່ອຄວາມຖືກຕ້ອງເວລາແກ້ໄຂ/ລຶບ)
            echo "<td class='text-center'>
                    <button type='button' class='btn btn-sm btn-warning me-1 fw-bold' 
                            onclick='openEditModal({$emp_row['id']}, \"{$emp_row['emp_code']}\", \"{$emp_row['name']}\", \"{$emp_row['position']}\")'>
                        <i class='fa-solid fa-pen-to-square'></i> ແກ້ໄຂ
                    </button>
                    <a href='dashboard.php?delete_emp_id={$emp_row['id']}' class='btn btn-sm btn-danger fw-bold' onclick='return confirm(\"⚠️ ຄຳເຕືອນ: ຖ້າທ່ານລຶບພະນັກງານຄົນນີ້ ປະຫວັດການປະເມີນທັງໝົດຈະຖືກລຶບອອກນຳ! ທ່ານແນ່ໃຈບໍ່?\")'>
                        <i class='fa-solid fa-trash-can'></i> ລຶບ
                    </a>
                  </td>";
            echo "</tr>";
            
            $emp_number++; // ✨ ບວກເລກລຳດັບຂຶ້ນເທື່ອລະ 1
        }
    } else {
        echo "<tr><td colspan='5' class='text-center py-3 text-muted'>❌ ບໍ່ມີລາຍຊື່ພະນັກງານ</td></tr>";
    }
    ?>
</tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 📊 ກາຟຈັດອັນດັບ ແລະ ກັ່ນຕອງ -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title mb-3"><i class="fa-solid fa-chart-bar text-info me-1"></i> ຈັດອັນດັບຄະແນນສະເລ່ຍພະນັກງານ</h5>
                    <div style="height: 220px;"><canvas id="starChart"></canvas></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title mb-3"><i class="fa-solid fa-filter text-secondary me-1"></i> ກັ່ນຕອງຂໍ້ມູນຕາຕະລາງ</h5>
                    <form method="GET" action="dashboard.php">
                        <div class="mb-3">
                            <label class="form-label text-muted">ພິມຄຳຄົ້ນຫາ (ໝວດໝູ່):</label>
                            <input type="text" name="filter_category" class="form-control" list="cate_list" placeholder="-- ພິມເພື່ອຄົ້ນຫາ... --" value="<?php echo htmlspecialchars($search_cate); ?>">
                            <datalist id="cate_list">
                                <option value="ບໍລິການວ່ອງໄວ">
                                <option value="ມາລະຍາດດີຍі້ມແຍ້ມ">
                                <option value="ໃຫ້ຄຳແນະນຳຊັດເຈນ">
                                <option value="ຄວນປັບປຸງຕື່ມ">
                            </datalist>
                        </div>
                        <button type="submit" class="btn btn-primary w-100"><i class="fa-solid fa-magnifying-glass me-1"></i> ຄົ້ນຫາ</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- 📋 ຕາຕະລາງປະຫວັດການປະເມີນ -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-dark text-white py-3">
            <h5 class="card-title mb-0"><i class="fa-solid fa-list-check me-1"></i> ປະຫວັດລາຍການປະເມີນຜົນທັງໝົດ</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th class="text-center" width="80">ລຳດັບ</th>
                            <th>ชື່ພະນັກງານ</th>
                            <th class="text-center" width="120">ຄະແນນ</th>
                            <th>ສິ່ງທີ່ປະທັບໃຈ/ຄວນປັບປຸງ</th>
                            <th>ຄຳຕິຊົມເພີ່ມເຕີມ</th>
                            <th width="200">ວັນທີ-ເວລາ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result && $result->num_rows > 0) {
                            $num = 1;
                            while($row = $result->fetch_assoc()) {
                                $emp_name = !empty($row['employee_name']) ? $row['employee_name'] : "ID: " . $row['employee_id'];
                                echo "<tr>";
                                echo "<td class='text-center text-muted'>{$num}</td>";
                                echo "<td><b>{$emp_name}</b> <small class='text-muted'>(ID: {$row['employee_id']})</small></td>";
                                echo "<td class='text-center'><span class='badge bg-warning text-dark'>★ {$row['rating']}</span></td>";
                                echo "<td><span class='badge bg-light text-dark border'>{$row['category']}</span></td>";
                                echo "<td>" . (!empty($row['comment']) ? htmlspecialchars($row['comment']) : '<span class="text-muted">-</span>') . "</td>";
                                echo "<td class='text-muted'>{$row['created_at']}</td>";
                                echo "</tr>";
                                $num++;
                            }
                        } else {
                            echo "<tr><td colspan='6' class='text-center py-4 text-muted'>❌ ບໍ່ມີຂໍ້ມູນການປະເມີນ</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- 📦 [ກ່ອງປັອບອັບ] Bootstrap Modal ສຳລັບແກ້ໄຂຂໍ້ມູນພະນັກງານ -->
<div class="modal fade" id="editEmpModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title fw-bold"><i class="fa-solid fa-pen-to-square"></i> ແກ້ໄຂຂໍ້ມູນພະນັກງານ</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" action="dashboard.php">
        <div class="modal-body">
            <input type="hidden" name="action" value="update_emp">
            <input type="hidden" name="emp_id" id="edit_emp_id">

            <div class="mb-3">
                <label class="form-label fw-bold">ລະຫັດພະນັກງານ (emp_code):</label>
                <input type="text" name="update_code" id="edit_emp_code" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">ຊື່ - ນາມສະກຸນ:</label>
                <input type="text" name="update_name" id="edit_emp_name" class="form-control" required>
            </div>
            <!-- ➕ ເພີ່ມຊ່ອງແກ້ໄຂຕຳແໜ່ງ -->
            <div class="mb-3">
                <label class="form-label fw-bold">ຕຳແໜ່ງ:</label>
                <input type="text" name="update_position" id="edit_emp_position" class="form-control" required>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ຍົກເລີກ</button>
            <button type="submit" class="btn btn-success"><i class="fa-solid fa-floppy-disk"></i> ບັນທຶກການແກ້ໄຂ</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ປິດແທັກ PHP ກ່ອນ (ເພື່ອປ້ອງກັນບໍ່ໃຫ້ໂຄ້ດຫຼຸດອອກມາສະແດງເທິງໜ້າຈໍ) -->
<?php ?> 

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
<script>
// ✨ ລະບົບກວດສອບຂໍ້ມູນໃໝ່ເບື້ອງຫຼັງ (ອັບເດດສະເພາະຕອນມີການປ້ອນຂໍ້ມູນເຂົ້າມາ)
let initialCount = null;

function checkNewData() {
    // ສ້າງ XMLHttp ເພື່ອໄປດຶງຈຳນວນລາຍການປະເມີນທັງໝົດມາເບິ່ງ
    fetch('dashboard.php')
    .then(response => response.text())
    .then(html => {
        // ສ້າງ parser ເພື່ອດຶງເອົາຕົວເລກຈຳນວນຄັ້ງການປະເມີນ (ເຊັ່ນ: 47 ຄັ້ງ) ມາທຽບ
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        
        // ຊອກຫາກ່ອງທີ່ສະແດງຈຳນວນຄັ້ງ (ປ່ຽນ selector ໃຫ້ຕົງກັບ class ທີ່ສະແດງຕົວເລກຂອງທ່ານ)
        const currentCountElement = doc.querySelector('.card h1, .card .display-4') || doc.body; 
        const currentText = currentCountElement.innerText;
        
        // ຈັບເອົາຕົວເລກທຳອິດທີ່ເຫັນໃນກ່ອງ KPI (ຈຳນວນຄັ້ງການປະເມີນ)
        const match = currentText.match(/\d+/);
        if (match) {
            const currentCount = parseInt(match[0]);
            
            if (initialCount === null) {
                initialCount = currentCount; // ເກັບຄ່າເລີ່ມຕົ້ນໄວ້ກ່ອນ
            } else if (currentCount !== initialCount) {
                // 🚀 ຖ້າມີລູກຄ້າປ້ອນຂໍ້ມູນໃໝ່ (ຕົວເລກປ່ຽນ) ໃຫ້ຣີເຟຣຊ໌ໜ້າຈໍທັນທີ!
                window.location.reload();
            }
        }
    });
}

// ໃຫ້ແອບກວດສອບຂໍ້ມູນໃໝ່ທຸກໆ 5 ວິນາທີ (ບໍ່ເຮັດໃຫ້ໜ້າຈໍກະຕຸກ ຫຼື ຂໍ້ມູນທີ່ Admin ພິມຢູ່ຫາຍ)
setInterval(checkNewData, 5000);
</script>
// ຟັງຊັນເປີດປັອບອັບແກ້ໄຂ
function openEditModal(id, code, name, position) {
    document.getElementById('edit_emp_id').value = id;
    document.getElementById('edit_emp_code').value = code;
    document.getElementById('edit_emp_name').value = name;
    document.getElementById('edit_emp_position').value = position;
    
    var myModal = new bootstrap.Modal(document.getElementById('editEmpModal'));
    myModal.show();
}

// 📊 ໂຄ້ດກາຟ Chart.js (ແກ້ໄຂໃຫ້ສະແດງພາສາລາວຊັດເຈນ ບໍ່ເປັນ Unicode)
const ctx = document.getElementById('starChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($chart_names, JSON_UNESCAPED_UNICODE); ?>, // ✨ ເພີ່ມ JSON_UNESCAPED_UNICODE ບ່ອນນີ້
        datasets: [{
            label: 'ຄະແນນດາວສະເລ່ຍ',
            data: <?php echo json_encode($chart_ratings); ?>,
            backgroundColor: '#1F4E78',
            borderRadius: 5
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: { y: { beginAtZero: true, max: 5 } }
    }
});
</script>
</body>
</html>
<?php $conn->close(); ?>