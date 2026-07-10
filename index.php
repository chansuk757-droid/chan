<?php
// 1. ກວດສອບຄ່າ emp ທີ່ສົ່ງມາຜ່ານ URL
if (!isset($_GET['emp']) || empty(trim($_GET['emp']))) {
    echo "
    <div style='text-align: center; margin-top: 50px; font-family: Arial, sans-serif;'>
        <h2 style='color: red;'>❌ ລິ້ງບໍ່ຖືກຕ້ອງ ຫຼື ບໍ່ພົບລະຫັດພະນັກງານ</h2>
        <p style='color: #555555; font-size: 18px;'>ກະລຸນາສະແກນ QR Code ໃໝ່ຈາກບັດປະຈຳໂຕ ຫຼື ປ້າຍປະຈຳຈຸດ ເພື່ອເຮັດການປະເມີນຜົນ.</p>
        <br>
        <small style='color: #999;'>[ ຕຶກ Cummins - ລະບົບຮັກສາຄວາມປອດໄພ ]</small>
    </div>
    ";
    exit(); 
}

// 2. ເຊື່ອມຕໍ່ຖານຂໍ້ມູນ
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "report"; 

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) { 
    die("Connection failed: " . $conn->connect_error); 
}
$conn->set_charset("utf8mb4"); // ແນະນຳ utf8mb4 ເພື່ອຮອງຮັບ Emoji ນຳ

$emp_code = trim($_GET['emp']);

// 3. ດຶງຂໍ້ມູນພະນັກງານແບບປອດໄພ (Prepared Statement)
$stmt = $conn->prepare("SELECT id, emp_code, name, position, department FROM employees WHERE emp_code = ?");
$stmt->bind_param("s", $emp_code);
$stmt->execute();
$result = $stmt->get_result();
$employee = $result->fetch_assoc();

if (!$employee) {
    die("<h3 style='text-align:center; margin-top:50px; font-family:Noto Sans Lao;'>❌ ບໍ່ພົບຂໍ້ມູນພະນັກງານໃນລະບົບ, <a href='index.php'>ກັບຄືນ</a></h3>");
}
?>

<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ລະບົບປະເມີນຜົນປະຈຳເຄົາເຕີ</title>
    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Lao:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <style>
        :root {
            --primary: #d32f2f;       /* ສີແດງຫຼັກ (LTC Red) */
            --primary-hover: #b71c1c; /* ສີແດງເຂັ້ມເວລາ Hover */
            --bg: #1e293b;            /* ສີຟ້າເຂັ້ມອົມເທົາ (Slate 800) */
            --card: #ffffff;          /* ສີຂາວສຳລັບກ່ອງຟອມ */
            --text: #0f172a;          /* ສີຕົວໜັງສືຫຼັກ */
            --star-active: #f59e0b;
            --star-dim: rgba(56, 67, 82, 0.4); 
        }

        body { 
            background-color: var(--bg); 
            color: var(--text); 
            padding: 25px 15px; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            min-height: 100vh; 
            font-family: 'Noto Sans Lao', sans-serif;
            margin: 0;
        }

        .kiosk-container { 
            background: linear-gradient(rgba(255, 255, 255, 0.75), rgba(255, 255, 255, 0.75)), 
                        url('cummins-office.webp') no-repeat center center;
            background-size: cover; 
            max-width: 550px; 
            width: 100%; 
            border-radius: 24px; 
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); 
            padding: 25px; 
            border-top: 10px solid var(--primary); 
            text-align: center; 
            position: relative;
            overflow: hidden;
            box-sizing: border-box;
        }

        /* ຂໍ້ມູນພະນັກງານຫຍໍ້ */
        .emp-profile { 
            background: rgba(241, 245, 249, 0.8); 
            padding: 20px; 
            border-radius: 16px; 
            margin-bottom: 30px; 
            display: flex; 
            align-items: center; 
            gap: 18px; 
            text-align: left; 
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.5);
        }

        .avatar-box { 
            background: var(--bg); 
            color: white; 
            width: 55px; 
            height: 55px; 
            border-radius: 50%; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
        } 

        .emp-details h2 { font-size: 18px; font-weight: 700; color: #1e293b; margin: 0; }
        .emp-details p { font-size: 13px; color: #64748b; margin: 3px 0 0 0; }
        
        .section-title { font-size: 16px; font-weight: 700; margin-bottom: 15px; color: #1e293b; letter-spacing: 0.5px; }
        
        /* ລະບົບໃຫ້ດາວ */
        .rating-box { display: flex; justify-content: center; direction: rtl; margin-bottom: 30px; }
        .rating-box input { display: none; }
        .rating-box label { font-size: 54px; color: var(--star-dim); cursor: pointer; transition: transform 0.1s, color 0.2s; padding: 0 4px; }
        .rating-box label:hover, .rating-box label:hover ~ label, .rating-box input:checked ~ label { color: var(--star-active); }
        .rating-box label:active { transform: scale(1.2); }
        
        /* ປຸ່ມແທັກຂໍ້ຄວາມ */
        .tags-container { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 25px; }
        .tag-item input { display: none; }
        .tag-item label { display: block; background: #ffffff; border: 2px solid #e2e8f0; padding: 14px 10px; text-align: center; border-radius: 14px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.2s; color: #475569; }
        .tag-item input:checked + label { background: #eff6ff; border-color: var(--primary); color: var(--primary); }
        
        /* ກ່ອງຄຳຄິດເຫັນ */
        textarea { 
            width: 100%; 
            max-width: 100%;
            box-sizing: border-box;
            padding: 14px; 
            border: 2px solid #e2e8f0; 
            border-radius: 14px; 
            min-height: 95px; 
            font-size: 14px; 
            outline: none; 
            resize: vertical; 
            margin-bottom: 25px; 
            background: #fafafa; 
            transition: 0.2s; 
            font-family: inherit;
        }
        textarea:focus { border-color: var(--primary); background: #fff; }
        
        /* ປຸ່ມສົ່ງ */
        .btn-submit { 
            width: 100%; 
            background: var(--primary); 
            color: white; 
            border: none; 
            padding: 15px; 
            font-size: 18px; 
            font-weight: 700; 
            border-radius: 16px; 
            cursor: pointer; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            gap: 12px; 
            transition: all 0.2s; 
        }
        .btn-submit:hover { background: var(--primary-hover); }
        
        .back-link { display: inline-block; margin-top: 20px; font-size: 13px; color: #94a3b8; text-decoration: none; }
        .back-link:hover { color: #64748b; }
    </style>
</head>
<body>

<div class="kiosk-container">
    <!-- ສະແດງຂໍ້ມູນພະນັກງານ -->
    <div class="emp-profile">
        <div class="avatar-box">
            <span class="material-icons" style="font-size: 32px;">person</span>
        </div>
        <div class="emp-details">
            <h2><?php echo htmlspecialchars($employee['name'], ENT_QUOTES, 'UTF-8'); ?></h2>
            <p>ລະຫັດ: <strong><?php echo htmlspecialchars($employee['emp_code'], ENT_QUOTES, 'UTF-8'); ?></strong> | ຕຳແໜ່ງ: <?php echo htmlspecialchars($employee['position'], ENT_QUOTES, 'UTF-8'); ?></p>
            <p>ພະແນກ: <?php echo htmlspecialchars($employee['department'], ENT_QUOTES, 'UTF-8'); ?></p>
        </div>
    </div>

    <!-- ຟອມສົ່ງຂໍ້ມູນປະເມີນ -->
    <form id="evaluationForm" action="save_evaluation.php" method="POST" onsubmit="return disableButton()">
        <input type="hidden" name="employee_id" value="<?php echo (int)$employee['id']; ?>">

        <div class="section-title">ກະລຸນາໃຫ້ຄະແນນຄວາມພຶງພໍໃຈ</div>
        
        <!-- ດາວໃຫ້ຄະແນນ -->
        <div class="rating-box">
            <input type="radio" id="star5" name="rating" value="5" required><label for="star5" class="material-icons">star</label>
            <input type="radio" id="star4" name="rating" value="4"><label for="star4" class="material-icons">star</label>
            <input type="radio" id="star3" name="rating" value="3"><label for="star3" class="material-icons">star</label>
            <input type="radio" id="star2" name="rating" value="2"><label for="star2" class="material-icons">star</label>
            <input type="radio" id="star1" name="rating" value="1"><label for="star1" class="material-icons">star</label>
        </div>

        <div class="section-title">ສິ່ງທີ່ທ່ານປະທັບໃຈ ຫຼື ຄວນປັບປຸງ</div>
        
        <!-- ແທັກຕົວເລືອກ -->
        <div class="tags-container">
            <div class="tag-item">
                <input type="checkbox" id="tag1" name="category[]" value="ບໍລິການວ່ອງໄວ">
                <label for="tag1">⚡ ບໍລິການວ່ອງໄວ</label>
            </div>
            <div class="tag-item">
                <input type="checkbox" id="tag2" name="category[]" value="ກິລິຍາມາລະຍາດດີ">
                <label for="tag2">😊 ມາລະຍາດດີຍິ້ມແຍ້ມ</label>
            </div>
            <div class="tag-item">
                <input type="checkbox" id="tag3" name="category[]" value="ໃຫ້ຂໍ້ມູນຊັດເຈນ">
                <label for="tag3">ℹ️ ໃຫ້ຄຳແນະນຳຊັດເຈນ</label>
            </div>
            <div class="tag-item">
                <input type="checkbox" id="tag4" name="category[]" value="ຄວນປັບປຸງຄວາມໄວ">
                <label for="tag4">🕒 ຄວນປັບປຸງຕື່ມ</label>
            </div>
        </div>

        <!-- ກ່ອງຄຳຄິດເຫັນ -->
        <textarea name="comment" placeholder="ຂຽນຄຳຄິດເຫັນ ຫຼື ຂໍ້ສະເໜີແນະນຳເພີ່ມເຕີມ... (ຖ້າມີ)"></textarea>

        <!-- ປຸ່ມສົ່ງຂໍ້ມູນ -->
        <button type="submit" id="submitBtn" class="btn-submit">
            <div style="background: white; width: 32px; height: 32px; border-radius: 50%; display: flex; justify-content: center; align-items: center; padding: 4px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); flex-shrink: 0;">
                <img src="LTC logo sign.png" alt="LTC Logo" style="width: 100%; height: 100%; object-fit: contain;"> 
            </div>
            <span>ສົ່ງຄຳປະເມີນຜົນ</span>
        </button>
    </form>

    <a href="index.php" class="back-link">ກັບຄືນໜ້າຫຼັກ</a>
</div>

<script>
function disableButton() {
    var btn = document.getElementById("submitBtn");
    btn.disabled = true;
    btn.innerText = "ກຳລັງສົ່ງຂໍ້ມູນ... ກະລຸນາລໍຖ້າ";
    btn.style.backgroundColor = "#94a3b8"; // ປ່ຽນເປັນສີເທົາອ່ອນໃຫ້ເບິ່ງ Soft ລົງ
    btn.style.cursor = "not-allowed";
    return true;
}
</script>

</body>
</html>