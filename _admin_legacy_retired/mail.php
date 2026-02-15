// Send email using PHPMailer
require_once 'PHPMailer/PHPMailer.php';
require_once 'PHPMailer/SMTP.php';
require_once 'PHPMailer/Exception.php';

$mail = new PHPMailer();
$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;
$mail->Username = 'apsdreamhoms44@gmail.com';
$mail->Password = '128125@Aps';
$mail->SMTPSecure = 'tls';
$mail->Port = 587;

$mail->setFrom($email);
$mail->addAddress('techguruabhay@gmail.com');
$mail->Subject = 'Job Application Form Submission';
$mail->Body = "Name: $name\nPhone: $phone\nEmail: $email\nComments: $msg";
$mail->addAttachment($file_tmp, $file_name);
if (!$mail->send()) {
    echo "Error sending email: " . $mail->ErrorInfo;
} else {
    echo "Form submitted successfully! We will review your application and get back to you soon.";
}