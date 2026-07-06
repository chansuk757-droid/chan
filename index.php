<?php
// 1. ກວດສອບວ່າ ມີການສົ່ງຄ່າ emp ມາຜ່ານ URL ຫຼືບໍ່
if (!isset($_GET['emp']) || empty(trim($_GET['emp']))) {
    // ຖ້າບໍ່ມີຄ່າ emp ໃຫ້ສະແດງຂໍ້ຄວາມເຕືອນ ແລ້ວຢຸດການເຮັດວຽກທັນທີ
    echo "
    <div style='text-align: center; margin-top: 50px; font-family: Arial, sans-serif;'>
        <h2 style='color: red;'>❌ ລິ້ງບໍ່ຖືກຕ້ອງ ຫຼື ບໍ່ພົບລະຫັດພະນັກງານ</h2>
        <p style='color: #555555; font-size: 18px;'>ກະລຸນາສະແກນ QR Code ໃໝ່ຈາກບັດປະຈຳໂຕ ຫຼື ປ້າຍປະຈຳຈຸດ ເພື່ອເຮັດການປະເມີນຜົນ.</p>
        <br>
        <small style='color: #999;'>[ ຕຶກ Cummins - ລະບົບຮັກສາຄວາມປອດໄພ ]</small>
    </div>
    ";
    exit(); // ຢຸດການໂຫຼດໜ້າຟອມທີ່ເຫຼືອທັງໝົດ
}
// ເຊື່ອມຕໍ່ຖານຂໍ້ມູນ
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "report"; 

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }
$conn->set_charset("utf8");

// ຮັບຄ່າລະຫັດພະນັກງານຈາກ URL
$emp_code = isset($_GET['emp']) ? $conn->real_escape_string($_GET['emp']) : '';

// ຄົ້ນຫາຂໍ້ມູນພະນັກງານປະຈຳເຄົາເຕີ
$sql = "SELECT id, emp_code, name, position, department FROM employees WHERE emp_code = '$emp_code'";
$result = $conn->query($sql);
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
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Lao:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <style>
       :root {
    /* 🌟 ປ່ຽນເປັນ Theme ສີແດງ LTC ແລະ ຟ້າເຂັ້ມ/ເທົາ */
    --primary: #d32f2f;       /* ສີແດງຫຼັກ (LTC Red) */
    --primary-hover: #b71c1c; /* ສີແດງເຂັ້ມເວລາເອົາມືຖືໄປກົດ */
    --bg: #1e293b;            /* ສີຟ້າເຂັ້ມອົມເທົາ (Slate 800) ສຳລັບພື້ນຫຼັງໃຫຍ່ */
    --card: #ffffff;          /* ສີຂາວສຳລັບກ່ອງຟອມ */
    --text: #0f172a;          /* ສີຕົວໜັງສືຫຼັກ */
    --star-active: #f59e0b;
    --star-dim: rgba(56, 67, 82, 0.4); /* 🌟 ປ່ຽນເປັນສີເທົາເຂັ້ມໂປ່ງແສງ ເພື່ອໃຫ້ຕັດກັບພື້ນຫຼັງ */
}
body { 
    background-color: var(--bg); /* ກັບມາໃຊ້ສີຟ້າເຂັ້ມອົມເທົາ ຕາມຮູບດ້ານນອກຂອງທ່ານ */
    color: var(--text); 
    padding: 25px 15px; 
    display: flex; 
    justify-content: center; 
    align-items: center; 
    min-height: 100vh; 
}

.kiosk-container { 
    /* 🌟 ປັບມາໃຊ້ background-size: 100% 100% ເພື່ອໃຫ້ຮູບຕຶກຫຍໍ້ເຂົ້າເຫັນຄົບ ແລະ ເຕັມຂອບພໍດີ */
    background: linear-gradient(rgba(255, 255, 255, 0.70), rgba(255, 255, 255, 0.70)), 
                url('cummins-office.webp') no-repeat center center;
    background-size: 100% 100%; 
    
    max-width: 550px; 
    width: 100%; 
    border-radius: 24px; 
    box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); 
    padding: 25px; /* 🌟 ຫຼຸດ padding ລົງໜ້ອຍໜຶ່ງເພື່ອໃຫ້ມີພື້ນທີ່ໃນມືຖືຫຼາຍຂຶ້ນ */
    border-top: 10px solid var(--primary); 
    text-align: center; 
    position: relative;
    overflow: hidden;
}
.employee-card {
    background: rgba(255, 255, 255, 0.6) !important; /* ປ່ຽນໃຫ້ໂປ່ງແສງເຫັນຮູບຕຶກທາງຫຼັງບາງໆ */
    backdrop-filter: blur(5px); /* ເຮັດໃຫ້ພື້ນຫຼັງມົວແບບກະຈົກ */
    border: 1px solid rgba(255, 255, 255, 0.5);
}

/* ປັບປຸງປຸ່ມສົ່ງໃຫ້ເປັນສີແດງ ແລະ ເວລາກົດໃຫ້ປ່ຽນສີ */
.btn-submit { 
    width: 100%; 
    background: var(--primary); 
    color: white; 
    border: none; 
    padding: 12px; 
    font-size: 18px; 
    font-weight: 700; 
    border-radius: 16px; 
    cursor: pointer; 
    display: inline-flex; 
    align-items: center; 
    justify-content: center; 
    gap: 12px; 
    transition: background 0.2s; 
}
.btn-submit:hover { 
    background: var(--primary-hover); 
}
textarea {
    <textarea name="comment" placeholder="ຂຽນຄຳຄິດເຫັນ..."></textarea>
    width: 100%; /* ກຳນົດໃຫ້ກວ້າງ 100% ຂອງກ່ອງຫຼັກ */
    max-width: 100%; /* ປ້ອງກັນບໍ່ໃຫ້ມັນກວ້າງເກີນ */
    box-sizing: border-box; /* ໃຫ້ຄວາມກວ້າງລວມ padding ແລະ border ນຳ (ອັນນີ້ສຳຄັນ!) */
    height: 80px; /* ຫຼື ກຳນົດຄວາມສູງຕາມໃຈຊອບ */
    padding: 15px; /* ໄລຍະຫ່າງທາງໃນ */
    border: 1px solid #ccc; /* ຫຼື ໃສ່ສີຕາມຕ້ອງການ #ccc ສີເທົາຈາງ */
    border-radius: 8px; /* ຄວາມມົນຂອງຂອບ */
    display: block; /* ເຮັດໃຫ້ມັນເປັນ block */
    margin-left: auto; /* ຈັດໃຫ້ຢູ່ເຄິ່ງກາງ */
    margin-right: auto; /* ຈັດໃຫ້ຢູ່ເຄິ່ງກາງ */
    resize: vertical; /* ອະນຸຍາດໃຫ້ຍືດໄດ້ແຕ່ທາງຕັ້ງ */
}

/* ປັບສີຟອນຕ໌ ແລະ ໄອຄອນໃນໂປຣໄຟລ໌ພະນັກງານໃຫ້ເຂົ້າກັບ Theme */
.avatar-box { 
    background: var(--bg); /* ໃຊ້ສີຟ້າເຂັ້ມເທົາ */
    color: white; 
    width: 55px; 
    height: 55px; 
    border-radius: 50%; 
    display: flex; 
    justify-content: center; 
    align-items: center; 
}     
        /* ຂໍ້ມູນພະນັກງານຫຍໍ້ */
        .emp-profile { background: #f1f5f9; padding: 20px; border-radius: 16px; margin-bottom: 30px; display: flex; align-items: center; gap: 18px; text-align: left; }
        .avatar-box { background: var(--primary); color: white; width: 55px; height: 55px; border-radius: 50%; display: flex; justify-content: center; align-items: center; }
        .emp-details h2 { font-size: 18px; font-weight: 700; color: #1e293b; }
        .emp-details p { font-size: 13px; color: #64748b; margin-top: 3px; }
        
        .section-title { font-size: 16px; font-weight: 700; margin-bottom: 15px; color: #1e293b; letter-spacing: 0.5px; }
        
        /* ລະບົບໃຫ້ດາວໃຫຍ່ໆ ກົດງ່າຍ */
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
        
        textarea { width: 100%; padding: 14px; border: 2px solid #e2e8f0; border-radius: 14px; min-height: 95px; font-size: 14px; outline: none; resize: none; margin-bottom: 25px; background: #fafafa; transition: 0.2s; }
        textarea:focus { border-color: var(--primary); background: #fff; }
        
        .btn-submit { width: 100%; background: var(--primary); color: white; border: none; padding: 18px; font-size: 18px; font-weight: 700; border-radius: 16px; cursor: pointer; display: flex; justify-content: center; align-items: center; gap: 10px; transition: background 0.2s; }
        .btn-submit:hover { background: #1d4ed8; }
        
        .back-link { display: inline-block; margin-top: 20px; font-size: 13px; color: #94a3b8; text-decoration: none; }
        .back-link:hover { color: #64748b; }
    </style>
</head>
<body>

<div class="kiosk-container">
    <div class="emp-profile">
        <div class="avatar-box">
            <span class="material-icons" style="font-size: 32px;">person</span>
        </div>
        <div class="emp-details">
            <h2><?php echo $employee['name']; ?></h2>
            <p>ລະຫັດ: <strong><?php echo $employee['emp_code']; ?></strong> | ຕຳແໜ່ງ: <?php echo $employee['position']; ?></p>
            <p>ພະແນກ: <?php echo $employee['department']; ?></p>
        </div>
    </div>

    <!-- ຕົວຢ່າງ: ເພີ່ມ id ແລະ onsubmit ໃສ່ແທັກ form ເດີມຂອງທ່ານ -->
<form id="evaluationForm" action="save.php" method="POST" onsubmit="return disableButton()"></form>
    <form action="save_evaluation.php" method="POST">
        <input type="hidden" name="employee_id" value="<?php echo $employee['id']; ?>">

        <div class="section-title">ກະລຸນາໃຫ້ຄະແນນຄວາມພຶງພໍໃຈ</div>
        
        <div class="rating-box">
            <input type="radio" id="star5" name="rating" value="5" required><label for="star5" class="material-icons">star</label>
            <input type="radio" id="star4" name="rating" value="4"><label for="star4" class="material-icons">star</label>
            <input type="radio" id="star3" name="rating" value="3"><label for="star3" class="material-icons">star</label>
            <input type="radio" id="star2" name="rating" value="2"><label for="star2" class="material-icons">star</label>
            <input type="radio" id="star1" name="rating" value="1"><label for="star1" class="material-icons">star</label>
        </div>

        <div class="section-title">ສິ່ງທີ່ທ່ານປະທັບໃຈ ຫຼື ຄວນປັບປຸງ</div>
        
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

        <textarea name="comment" placeholder="ຂຽນຄຳຄິດເຫັນ ຫຼື ຂໍ້ສະເໜີແນະນຳເພີ່ມເຕີມ... (ຖ້າມີ)"></textarea>
<button type="submit" class="btn-submit">
    <!-- ໂລໂກ້ LTC ພື້ນຫຼັງຂາວວົງມົນ ເຫັນແຈ້ງ 100% -->
    <div style="background: white; width: 35px; height: 35px; border-radius: 50%; display: flex; justify-content: center; align-items: center; padding: 4px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <img src="LTC logo sign.png" alt="LTC Logo" style="width: 100%; height: 100%; object-fit: contain;"> 
    </div>
    <span>ສົ່ງຄຳປະເມີນຜົນ</span>
</button>

</div>

</body>
<script>
function disableButton() {
    var btn = document.getElementById("submitBtn");
    // ສັ່ງໃຫ້ປຸ່ມກົດບໍ່ໄດ້ອີກ
    btn.disabled = true;
    // ປ່ຽນຂໍ້ຄວາມຢູ່ປຸ່ມເພື່ອໃຫ້ລູກຄ້າຮູ້ວ່າລະບົບກຳລັງເຮັດວຽກ
    btn.innerText = "ກຳລັງສົ່ງຂໍ້ມູນ... ກະລຸນາລໍຖ້າ";
    btn.style.backgroundColor = "#cccccc"; // ປ່ຽນສີປຸ່ມໃຫ້ເປັນສີເທົາ
    return true;
}
</script>
</html>