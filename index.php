<?php
session_start();
include "connect_db.php";

// Library PHPMailer
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';
require 'PHPMailer-master/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Delete expired OTP
$db->prepare("DELETE FROM otp_mail WHERE expired < " . time())->execute();
unset($_SESSION['email']);

?>

<title>OTP Email</title>
<?php $css_ver = filemtime("style.css"); ?>
<link rel="stylesheet" href="style.css?v=<?php echo $css_ver; ?>">

<div class="title-box">
    <div class="title-logo"></div>
    <h2>OTP Email</h2>
</div>

<div class="container">
<h3>1. Kirim OTP</h3>

<form method="POST">
    <input type="email" name="email" placeholder="Masukan Alamat Email" required>
    <button name="kirim">Kirim OTP</button>
</form>

</div>

<?php
if (isset($_POST['kirim'])) {

    // Bersihkan input email
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    // Generate OTP
    $otp      = rand(100000, 999999);
    $otp_hash = password_hash($otp, PASSWORD_DEFAULT);
    $expired  = time() + 120;

    // Simpan OTP baru
    $query = $db->prepare("INSERT INTO otp_mail (email, otp_hash, expired) VALUES (?, ?, ?)");
    $query->bind_param("ssi", $email, $otp_hash, $expired);
    $query->execute();

    $_SESSION['email'] = $email;

    // Mulai kirim email OTP
    $mail = new PHPMailer(true);

    try {
        // SMTP Setup
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'bisangoding.id@gmail.com'; //email pengirim    
        $mail->Password   = '****'; // sesaikan password aplikasinya
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        // Email pengirim & Subjek
        $mail->setFrom('bisangoding.id@gmail.com', 'Verifikasi OTP');
        $mail->addAddress($email);

        // Isi email
        $mail->isHTML(true);
        $mail->Subject = 'Kode OTP Verifikasi Anda';
        $mail->Body = "
            <p>Kode OTP Anda:</p>
            <h2 style='font-size:22px;'> $otp </h2>
            <p>Berlaku selama <b>2 menit</b>.<br>
            Jangan berikan kode ini kepada siapa pun.</p>";

        $mail->send();

        echo "<div class='success'>OTP telah dikirim ke email: <b> $email </b><br>
              <a href='verifikasi.php' class='btn-otp'>Verifikasi OTP</a>
              </div>";

    } catch (Exception $e) {
        echo "<div class='error'>Gagal mengirim email: {$mail->ErrorInfo}</div>";
    }
}
?>
