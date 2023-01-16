<?php

$spamFilter = "http|www|advert|custom|promot|market|traffic|click here";

if (!empty($_POST) &&
    $_SERVER["REQUEST_METHOD"] === 'POST'&&
    isset($_POST["name"]) &&
    isset($_POST["from"]) &&
    isset($_POST["message"]) &&
    isset($_POST["consent"]) &&
    isset($_POST["referer"]) &&
	isset($_POST["status"])) {

	// Simple antispam honeypot
	if(!empty($_POST["website"])) die();

	if ($_POST["status"] == "send") {

		$to = mb_encode_mimeheader("GuitarFirst")." <admin@test.com>";
		$subject = mb_encode_mimeheader("[GuitarFirst] Message");

		$message = $_POST["message"] . "\r\n-- \r\n" . $_POST["name"];

		$mailHeaders = mb_encode_mimeheader("From: Admin")."<admin@test.com>\r\n";
		$mailHeaders .= mb_encode_mimeheader("Reply-To: " . $_POST["name"])." <".$_POST["from"].">\r\n";
		$mailHeaders .= mb_encode_mimeheader("X-Referer-Url: ".$_POST["referer"])."\r\n";
		$mailHeaders .= mb_encode_mimeheader("X-Mailer: PHP/".phpversion())."\r\n";
		$mailHeaders .= "Mime-Version: 1.0\r\n";
		$mailHeaders .= "Content-Type: text/plain; charset=utf-8\r\n";

		// Spam filter
		if ($spamFilter!="none" && preg_match("/$spamFilter/i", $message)) {
			$subject = mb_encode_mimeheader("[SPAM] [GuitarFirst] Message");
			$mailHeaders .= "X-Spam-Flag: YES\r\n";
			$mailHeaders .= "X-Spam-Status: Yes, score=1\r\n";
		}

		$status = mail($to, $subject, $message, $mailHeaders) ? true : false;

		if ($status) {
			echo "Message envoyé !";
			exit(0);
		} else {
			header('HTTP/1.1 400 Internal Server Booboo');
			echo "Erreur. Veuillez réessayer plus tard.";
			exit(1);
		}
	} else {
		header('HTTP/1.1 400 Internal Server Booboo');
		echo "Erreur. Veuillez réessayer plus tard.";
		exit(1);
	}

} else {
	header('HTTP/1.1 0 Internal Server Booboo');
	echo "Le formulaire n'est pas rempli.";
	exit(1);
}


?>
