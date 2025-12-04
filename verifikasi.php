<?php
session_start();
include "connect_db.php";

// Ambil email dari session
$email = $_SESSION['email'] ?? null;

// Ambil OTP dari database untuk hitung sisa waktu
$query = $db->prepare("SELECT * FROM otp_mail WHERE email=? LIMIT 1");
$query->bind_param("s", $email);
$query->execute();
$data = $query->get_result()->fetch_assoc();

// Hitung sisa waktu OTP
$remaining = max(0, $data['expired'] - time());
$otpValid = false;

?>

<title>OTP Email</title>
<?php $css_ver = filemtime("style.css"); ?>
<link rel="stylesheet" href="style.css?v=<?php echo $css_ver; ?>">

<div class="title-box">
    <div class="title-logo"></div>
    <h2>OTP Email</h2>
</div>

<div class="container">
<h3>2. Verifikasi OTP</h3>

<form method="POST">
    <div class="otp-input-group">
        <input type="text" name="d1" maxlength="1" class="otp-box" required>
        <input type="text" name="d2" maxlength="1" class="otp-box" required>
        <input type="text" name="d3" maxlength="1" class="otp-box" required>
        <input type="text" name="d4" maxlength="1" class="otp-box" required>
        <input type="text" name="d5" maxlength="1" class="otp-box" required>
        <input type="text" name="d6" maxlength="1" class="otp-box" required>
    </div>
    <button type="submit" name="cekotp" id="btn">Verifikasi OTP</button>
    
</form>

<div id="cdWrapper" style="margin-top:10px;">
    Waktu tersisa: <strong> <span id="countdown"><?php echo $remaining ?></span> detik </strong>
</div>
</div>

<?php
// Proses verifikasi OTP
if (isset($_POST['cekotp'])) {

    // Input OTP dari form
    $otp = $_POST['d1'] . $_POST['d2'] . $_POST['d3'] . $_POST['d4'] . $_POST['d5'] . $_POST['d6'];

    $query = $db->prepare("SELECT * FROM otp_mail WHERE email=? LIMIT 1");
    $query->bind_param("s", $email);
    $query->execute();
    $data = $query->get_result()->fetch_assoc();

    // Cek OTP & validasi
    if (password_verify($otp, $data['otp_hash'])) {

        echo "
       <div class='alert success'>
         <span class='alert-icon'>✔</span>
         <div class='alert-content'>
            <strong>SUCCESS!</strong>
            <p>OTP email anda terverifikasi. Akun anda sudah aktif.</p>
         </div>
       </div>";
        
       // Hapus OTP setelah valid
        unset($_SESSION['email']);
        $otpValid = true;

    } else {
        echo "<div class='error'>OTP SALAH!</div>";
    }
}
?>


<script>
// Variabel dari PHP
let s = <?= $remaining ?>, 
    v = <?= json_encode($otpValid) ?>,
    w = document.getElementById('cdWrapper'),
    c = document.getElementById('countdown');

// Jika OTP sudah valid
if (v) {
    w.style.display = "none";
    document.getElementById('btn').disabled = true;
}

// Jika OTP belum valid → jalankan countdown
else {
    let t = setInterval(() => {
        c.textContent = s;

        // Jika waktu habis → jalankan expired
        if (s <= 0) {
            clearInterval(t);
            w.style.display = "none";
            document.body.insertAdjacentHTML(
                "beforeend",
                "<div class='error'>OTP kadaluarsa! Silakan kirim ulang.</div>"
            );
            setTimeout(() => location.href="index.php", 2000);
            return;
        }
        s--; // hitung mundur
    }, 1000);
}

// Auto-paste OTP 
document.querySelectorAll(".otp-box")[0].addEventListener("paste", function (e) {
    let paste = (e.clipboardData || window.clipboardData).getData('text');
    paste = paste.replace(/\D/g, '').trim();

    if (paste.length === 6) {
        const boxes = document.querySelectorAll(".otp-box");
        for (let i = 0; i < 6; i++) {
            boxes[i].value = paste[i] ?? "";
        }
    }
});
</script>



