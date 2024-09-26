<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
    
function mailgonder_ee($kimden,$kime,$kime2,$konu,$mesaj) {
    require_once 'class.phpmailer.php';
    require_once 'smtp.php';
    require_once 'exception.php';
    
    $mail = new PHPMailer();   
    $mail->IsSMTP();
    $mail->From     = $kimden; //Gönderen kısmında yer alacak e-mail adresi  
    $mail->Sender   = $kimden;  
    $mail->FromName = "STV";  
    $mail->Host     = "smtp.hoster.kz"; //SMTP server adresi  
    $mail->SMTPAuth = true;
    $mail->Username = "test@agro312.com"; //SMTP kullanıcı adı  
    $mail->Password = "Agro3122024"; //SMTP şifre  
	$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 
    $mail->Port = "587";
    $mail->CharSet = "utf-8";
    $mail->WordWrap = 50;  
    $mail->IsHTML(true); //Mailin HTML formatında hazırlanacağını bildiriyoruz.  
    $mail->Subject  = $konu; // Konu  
    //Mailimizin gövdesi: (HTML ile)  
    $body = $mesaj;
    // HTML okuyamayan mail okuyucularda görünecek düz metin:  
    $textBody = strip_tags($mesaj);
    $mail->Body = $body;  
    $mail->AltBody = $textBody;  
    $mail->AddAddress($kime);
    $mail->AddAddress($kime2);
    // Mail gönderilecek adresleri ekliyoruz.  
    //$mail->AddAddress("falan@filan.com");  //Başka mail ekleyecekseniz.
    return ($mail->Send())?true:false;      
    $mail->ClearAddresses();  
    $mail->ClearAttachments();
}

?>




<?php
$hmesaji="";
if(isset($_POST["submit"])){
    $namesurname = (isset($_POST["ad"]))?$_POST["ad"]:"";
    $phonenumber = (isset($_POST["phone"]))?$_POST["phone"]:"";
    $email = (isset($_POST["email"]))?$_POST["email"]:"";
    $qeyd = (isset($_POST["qeyd"]))?$_POST["qeyd"]:"";
    
    if($namesurname == "" || $phonenumber == "" )
        $hmesaji = "<p style='text-align: center; color: #e80c0c;'>Xəta! Bütün sahələr düzgün doldurulmalıdır!</p>";
    else
    {
        $mesajk = "<b>$namesurname</b> sizə veb saytınızdan mesaj göndərdi:<hr />";
        $mesajk .= "<b>Telefon:</b> $phonenumber <hr />";
        $email .= "<b>Email:</b> $email <hr />";
        $mesajk .= "<b>Qeyd:</b> $qeyd <hr />";
        $mesajk .= "<span style='font-size:10px;color:#bbbbbb;'>Bu mesaj ". date('H:i:s d.m.Y') ." tarixində göndərildi.</span>";

        if(mailgonder_ee("test@agro312.com","ceyhun.rzayeev@gmai.com", "ceyhun.rzayeev@gmai.com", "STV test sorgu",$mesajk))
        {
            $hmesaji =  "<p style='text-align: center;color: #14b137;'>
            Müraciət müvəffəqiyyətlə göndərildi.<br> Ən qısa zamanda səninlə əlaqə saxlayacağıq. <br> Təşəkkür edirik !</p>";
        }
        else
        {
            $hmesaji =  "Göndərilmədi.";
        }
    }
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<body>
<form method="post" id="sendmail" name="sendmail" action="">
    <?php echo "<p style='margin-top:15px'>$hmesaji</p>"; ?>
    <div class="row">
        <div class="col-12 col-lg-4 col-md-6">
            <div class="form-feedback-wrapper">
                    <input name="ad" type="text" class="form-feedback-wrapper-input" required>
                    <label for="" class="form-feedback-wrapper-label">Ваше имя</label>
            </div>
        </div>
        <div class="col-12 col-lg-4 col-md-6">
            <div class="form-feedback-wrapper">
                <input name="phone" type="text" class="form-feedback-wrapper-input" required>
                <label for="" class="form-feedback-wrapper-label">Ваш телефон</label>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="form-feedback-wrapper">
                <input name="email" type="text" class="form-feedback-wrapper-input" required>
                <label for="" class="form-feedback-wrapper-label">Email</label>
            </div>
        </div>
        <div class="col-12">
            <div class="form-feedback-wrapper">
                <textarea name="qeyd" class="form-feedback-wrapper-textarea" required></textarea>
                <label for="" class="form-feedback-wrapper-label">Ваше сообщение</label>
            </div>
        </div>
        <div class="col-12">
            <div class="btn-feedback-wrapper">
               <button type="submit" name="submit">
                    <i class="animation"></i>Отправить<i class="animation"></i>
                </button>
            </div>
        </div>
    </div>
</form>

</body>

</html>