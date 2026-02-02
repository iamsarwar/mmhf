<?php

function generatePIN($digits = 4){
	$i = 0; //counter
	$pin = ""; //our default pin is blank.
	while($i < $digits){
		//generate a random number between 0 and 9.
		$pin .= mt_rand(0, 9);
		$i++;
	}
	return $pin;
}


function drb_nonce_lifetime() {
    return 240; // 4 minutes
}
add_filter( 'nonce_life', 'drb_nonce_lifetime' );
$nonce = wp_create_nonce( '21Dhruboorion&@mmhf' );
remove_filter( 'nonce_life', 'drb_nonce_lifetime' );

//wp-json prefix change to api
add_filter( 'rest_url_prefix', function() {
    return 'api';
});


wp_enqueue_script( 'ajax_custom_script',  get_stylesheet_directory_uri() . '/js/drb_custom_ajax.js', array('jquery') );
wp_localize_script('ajax_custom_script', 'ajax_object', ['ajax_url' => get_bloginfo('url').'/wp-admin/admin-ajax.php', 'ajax_nonce' => $nonce]);


function smsmmhfOrion($msisdn, $messageBody, $csmsId)
{
    $params = [
        "api_token" => "ldxtxzxi-cv7x5xfv-7bk2o6oo-txiawn2p-45vnvrjp",
        "sid" => "ORIONJGFPMASKINGAPI",
        "msisdn" => $msisdn,
        "sms" => $messageBody,
        "csms_id" => $csmsId
    ];
    $url = "https://smsplus.sslwireless.com/api/v3/send-sms";
    $params = json_encode($params);

    echo callApi($url, $params);
}


function callApi($url, $params)
{
    $ch = curl_init(); // Initialize cURL
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($params),
        'accept:application/json'
    ));

    $response = curl_exec($ch);

    curl_close($ch);

    return $response;
}


// get mobile pin
add_action( 'wp_ajax_nopriv_get_mobile_pin', 'get_mobile_pin' );
add_action( 'wp_ajax_get_mobile_pin', 'get_mobile_pin' );

function get_mobile_pin()
{
    // check the nonce, if it fails the function will break
    add_filter( 'nonce_life', 'drb_nonce_lifetime' );
    check_ajax_referer( '21Dhruboorion&@mmhf', 'security' );
    remove_filter( 'nonce_life', 'drb_nonce_lifetime' );

    //echo $_POST['mobile_no'];
	if(!empty($_POST['mobile_no'])){
		$mobileno=trim($_POST['mobile_no']);
		$mobileno=ltrim($mobileno,'+');
		$mobileno=ltrim($mobileno,'88');
		if(strlen($mobileno)!= 11 && $mobileno != '')
		    echo 'error';
		else {
    		global $wpdb;
    		// this adds the prefix which is set by the user upon instillation of wordpress
    		//$table_name = $wpdb->prefix . "outlets";
    		//$retrieve_data = $wpdb->get_row( $wpdb->prepare( "SELECT $table_name.* FROM $table_name WHERE $table_name.location = '".str_replace('Kfc ', '', $_POST['branch'])."'" ));
    		//$_SESSION['outlet'] = $retrieve_data->id;

			//$_POST['mobile_no']='+88'.$mobileno;
			$_POST['mobile_no'] = $mobileno;

			//generate PIN code
			$pin_code = generatePIN(6);

            $message = "Your Orion Toll Bridge One-Time PIN Code is ".$pin_code." It will expire in 20 minutes.";
            //$url ="http://103.177.125.106:7788/sendtext?apikey=9517ddd56c9fe27b&secretkey=99757acb&callerID=ORION_JGFP&toUser=".$_POST['mobile_no']."&messageContent=".urlencode($message);
            //$ch=curl_init($url);
            //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  
            //curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  
            //curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.3) Gecko/20070309 Firefox/2.0.0.3");  
            //$response = curl_exec( $ch );
            //curl_close($ch);        

		// $msisdn = "01777758394";
		// $messageBody = "SMS FROM SSL";
		$csmsId = uniqid(); // csms id must be unique

		smsmmhfOrion($_POST['mobile_no'], $message, $csmsId);

			$otp_table_name = $wpdb->prefix . "otp";
			// this will get the data from your table
			$wpdb->delete( $otp_table_name, array( 'mobile_no' => trim($_POST['mobile_no']) ) );
			$insert_data = $wpdb->insert(
										$otp_table_name,
										array(
											'mobile_no' => trim($_POST['mobile_no']),
											'pin_code' => $pin_code,
											'date_inserted' => date('Y-m-d h:i:s')
										),
										array(
											'%s',
											'%d',
											'%s'
										)
									);

		}
	}
	else
	    echo 'error';
}



// match mobile pin
add_action( 'wp_ajax_nopriv_match_mobile_pin', 'match_mobile_pin' );
add_action( 'wp_ajax_match_mobile_pin', 'match_mobile_pin' );

function match_mobile_pin()
{
    session_start();
	if(!empty($_POST['pin_code'])){
		global $wpdb;
		// this adds the prefix which is set by the user upon instillation of wordpress
		$otp_table_name = $wpdb->prefix . "otp";
		$otp_retrieve_data = $wpdb->get_row( "SELECT $otp_table_name.* FROM $otp_table_name WHERE $otp_table_name.mobile_no = '".$_POST['user_mobile']."' AND $otp_table_name.pin_code = ".trim($_POST['pin_code'])." ORDER BY $otp_table_name.id DESC LIMIT 0,1" );
		//echo "SELECT $otp_table_name.* FROM $otp_table_name WHERE $otp_table_name.mobile_no = '".$_POST['user_mobile']."' AND $otp_table_name.pin_code = ".trim($_POST['pin_code'])." ORDER BY $otp_table_name.id DESC LIMIT 0,1";
		//var_dump($otp_retrieve_data);
		if(!empty($otp_retrieve_data->id)) {
            echo 'success';
            $_SESSION['OTP'] = 'Oyes';
        }
		else
		    echo 'error';
	}
	else
	    echo 'error';
}



function generateRechargeInvoiceID(){
    if(!empty(get_current_user_id())) {
        global $wpdb;
        // this adds the prefix which is set by the user upon instillation of wordpress
        $recharge_histories_table_name = $wpdb->prefix . "users_recharge_histories";
        $recharge_histories_retrieve_data = $wpdb->get_row("SELECT MAX(id) AS last_id FROM $recharge_histories_table_name");
        if(!empty($recharge_histories_retrieve_data->last_id)){
            $invoice_id = get_current_user_id().date('Ym').($recharge_histories_retrieve_data->last_id+1);
            return $invoice_id;
        }
    }
    else
        return 0;
}

function generateProcessFeeInvoiceID($user_id){
    global $wpdb;
    // this adds the prefix which is set by the user upon instillation of wordpress
    $process_fees_table_name = $wpdb->prefix . "user_process_fees";
    $process_fees_retrieve_data = $wpdb->get_row("SELECT MAX(id) AS last_id FROM $process_fees_table_name");
    if(!empty($process_fees_retrieve_data->last_id)){
        $invoice_id = $user_id.date('Ym').($process_fees_retrieve_data->last_id+1);
    }
    else
        $invoice_id = $user_id.date('Ym').'1';

    return $invoice_id;
}

function encrypt_decrypt($action, $string,$secret_key) {
    $output = false;
    $encrypt_method = "AES-256-CBC";
    $secret_iv = 'randomString#12231'; // change this to one more secure
    $key = hash('sha256', $secret_key);

    // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
    $iv = substr(hash('sha256', $secret_iv), 0, 16);
    if ( $action == 'encrypt' ) {
        $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
        $output = base64_encode($output);
    } else if( $action == 'decrypt' ) {
        $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
    }
    return $output;
}

// bKash

add_action( 'wp_ajax_nopriv_bkash_token', 'bkash_token' );
add_action( 'wp_ajax_bkash_token', 'bkash_token' );
/*function bkash_token(){
	session_start();
	$post_token=array(
		   'app_key'=>'1pumcdhoonkuk2erg8od3g55uv',
		   'app_secret'=>'1rq10mcag05a06vf1sha0vfi8oih1utc01egc3mo22q15rn5o4bq'
	);
        
	//$url=curl_init('https://checkout.pay.bka.sh/v1.2.0-beta/checkout/token/grant');
        $url = "https://checkout.pay.bka.sh/v1.2.0-beta/checkout/token/grant";
	$posttoken=json_encode($post_token);
	$header=array(
	        'Content-Type:application/json',
			'password:O@1rIoN5fRL8K',
			'username:ORIONMMHF');
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_FAILONERROR, true);
	curl_setopt($ch,CURLOPT_HTTPHEADER, $header);
	curl_setopt($ch,CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch,CURLOPT_POSTFIELDS, $posttoken);
	curl_setopt($ch,CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
	$resultdata = curl_exec($ch);
        // echo curl_error($ch);
	if(!$resultdata)
	{
		echo curl_error($ch);
	}
	else
	{
		//echo $resultdata;
		$obj = json_decode($resultdata);
	        // echo $obj->{'id_token'};
		$id_token = encrypt_decrypt('encrypt',$obj->{'id_token'},'maskPassOrionToll');
		$_SESSION['bkash_tokens'] = $id_token;
		$_SESSION['bkash_token_time'] = date('Y-m-d H:i:s');
                $_SESSION['bkash_refresh_token'] = $obj->refresh_token;
	}
	curl_close($ch);
}*/

function bkash_token(){
	session_start();
    if(!empty($_SESSION['bkash_tokens'])){
        $timeFirst  = strtotime($_SESSION['bkash_token_time']);
        $timeSecond = strtotime(date('Y-m-d H:i:s'));
        $differenceInSeconds = $timeSecond - $timeFirst;
        if($differenceInSeconds >= 3600){
            $post_token=array(
                'app_key'=>'1pumcdhoonkuk2erg8od3g55uv',
                'app_secret'=>'1rq10mcag05a06vf1sha0vfi8oih1utc01egc3mo22q15rn5o4bq'
            );
            $url=curl_init('https://checkout.pay.bka.sh/v1.2.0-beta/checkout/token/grant');

            $posttoken=json_encode($post_token);
            $header=array(
                    'Content-Type:application/json',
                    'password:O@1rIoN5fRL8K',
                    'username:ORIONMMHF');
            curl_setopt($url,CURLOPT_HTTPHEADER, $header);
            curl_setopt($url,CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($url,CURLOPT_RETURNTRANSFER, true);
            curl_setopt($url,CURLOPT_POSTFIELDS, $posttoken);
            curl_setopt($url,CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($url, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
            $resultdata = curl_exec($url);
            if(!$resultdata)
            {
                echo curl_error($url);
            }
            else
            {
                //echo $resultdata;
                $obj = json_decode($resultdata);
                // echo $obj->{'id_token'};
                $id_token = encrypt_decrypt('encrypt',$obj->{'id_token'},'maskPassOrionToll');
                $_SESSION['bkash_tokens'] = $id_token;
                $_SESSION['bkash_token_time'] = date('Y-m-d H:i:s');
                $_SESSION['bkash_refresh_token'] = $obj->refresh_token;
            }
            curl_close($url);
        }
        else{
            $post_token=array(
                'app_key'=>'1pumcdhoonkuk2erg8od3g55uv',
                'app_secret'=>'1rq10mcag05a06vf1sha0vfi8oih1utc01egc3mo22q15rn5o4bq',
                'refresh_token' => $_SESSION['bkash_refresh_token']
            );
            $url=curl_init('https://checkout.pay.bka.sh/v1.2.0-beta/checkout/token/refresh');

            $posttoken=json_encode($post_token);
            $header=array(
                    'Content-Type:application/json',
                    'password:O@1rIoN5fRL8K',
                    'username:ORIONMMHF');
            curl_setopt($url,CURLOPT_HTTPHEADER, $header);
            curl_setopt($url,CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($url,CURLOPT_RETURNTRANSFER, true);
            curl_setopt($url,CURLOPT_POSTFIELDS, $posttoken);
            curl_setopt($url,CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($url, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
            $resultdata = curl_exec($url);
            if(!$resultdata)
            {
                echo curl_error($url);
            }
            else
            {
                //echo $resultdata;
                $obj = json_decode($resultdata);
                // echo $obj->{'id_token'};
                $id_token = encrypt_decrypt('encrypt',$obj->{'id_token'},'maskPassOrionToll');
                $_SESSION['bkash_tokens'] = $id_token;
                $_SESSION['bkash_token_time'] = date('Y-m-d H:i:s');
                $_SESSION['bkash_refresh_token'] = $obj->refresh_token;
            }
            curl_close($url);
        }
    }
    else{
        $post_token=array(
            'app_key'=>'1pumcdhoonkuk2erg8od3g55uv',
            'app_secret'=>'1rq10mcag05a06vf1sha0vfi8oih1utc01egc3mo22q15rn5o4bq'
        );
        $url=curl_init('https://checkout.pay.bka.sh/v1.2.0-beta/checkout/token/grant');

        $posttoken=json_encode($post_token);
        $header=array(
                'Content-Type:application/json',
                'password:O@1rIoN5fRL8K',
                'username:ORIONMMHF');
        curl_setopt($url,CURLOPT_HTTPHEADER, $header);
        curl_setopt($url,CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($url,CURLOPT_RETURNTRANSFER, true);
        curl_setopt($url,CURLOPT_POSTFIELDS, $posttoken);
        curl_setopt($url,CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($url, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        $resultdata = curl_exec($url);
        if(!$resultdata)
        {
            echo curl_error($url);
        }
        else
        {
            //echo $resultdata;
            $obj = json_decode($resultdata);
            // echo $obj->{'id_token'};
            $id_token = encrypt_decrypt('encrypt',$obj->{'id_token'},'maskPassOrionToll');
            $_SESSION['bkash_tokens'] = $id_token;
            $_SESSION['bkash_token_time'] = date('Y-m-d H:i:s');
            $_SESSION['bkash_refresh_token'] = $obj->refresh_token;
        }
        curl_close($url);
    }
}


add_action( 'wp_ajax_nopriv_bkash_createpayment', 'bkash_createpayment' );
add_action( 'wp_ajax_bkash_createpayment', 'bkash_createpayment' );
function bkash_createpayment()
{
    session_start();
    //echo $_SESSION['bkash_tokens'];
    $amount = !empty($_POST['amount']) ? $_POST['amount'] : '';
    $invoice = $_POST['invoice'];//must be unique
    $isInvoiceUnique = true;
    if (!empty($_SESSION['process_fee']) && $_SESSION['process_fee'] == 1) {
        //$amount = 100;
        if ($amount != '100')
            return json_encode(array('msg' => 'Amount missmatch'));

        global $wpdb;
        $process_fees_table_name = $wpdb->prefix . "user_process_fees";
        $process_fees_retrieve_data = $wpdb->get_row("SELECT id FROM $process_fees_table_name WHERE invoice_id='" . $invoice . "'");
        if (!empty($process_fees_retrieve_data->id))
            $isInvoiceUnique = false;

        if ($_SESSION['signon_invoie_id'] != $invoice && empty(get_current_user_id()))
            $isInvoiceUnique = false;
    }

    $intent = "sale";

    if ($_SESSION['OTP'] == 'Oyes' || !empty(get_current_user_id())) {
        if ($isInvoiceUnique) {
            $createpaybody = array(
                'amount' => $amount,
                'currency' => 'BDT',
                'intent' => $intent,
                'merchantInvoiceNumber' => $invoice
            );
            $url = curl_init('https://checkout.pay.bka.sh/v1.2.0-beta/checkout/payment/create');

            if (!empty($_SESSION['bkash_tokens'])) {
                $id_token = encrypt_decrypt('decrypt', $_SESSION['bkash_tokens'], 'maskPassOrionToll');
                $createpaybodyx = json_encode($createpaybody);
                $header = array(
                    'Content-Type:application/json',
                    'authorization:' . $id_token,
                    'x-app-key:1pumcdhoonkuk2erg8od3g55uv');
                curl_setopt($url, CURLOPT_HTTPHEADER, $header);
                curl_setopt($url, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($url, CURLOPT_POSTFIELDS, $createpaybodyx);
                curl_setopt($url, CURLOPT_FOLLOWLOCATION, 1);
                curl_setopt($url, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
                $resultdata = curl_exec($url);
                curl_close($url);
                echo $resultdata;
                $_SESSION['OTP'] = '';
            }
        } else {
            echo json_encode(array('msg' => 'Invoice already used'));
        }
    }
    else
        echo json_encode(array('msg' => 'You are out of the site'));
}

add_action( 'wp_ajax_nopriv_bkash_executepayment', 'bkash_executepayment' );
add_action( 'wp_ajax_bkash_executepayment', 'bkash_executepayment' );
function bkash_executepayment(){
    session_start();
    $paymentID = $_POST['paymentID'];

    $url=curl_init('https://checkout.pay.bka.sh/v1.2.0-beta/checkout/payment/execute/'.$paymentID);
    $id_token = encrypt_decrypt('decrypt',$_SESSION['bkash_tokens'],'maskPassOrionToll');
    $header=array(
        'Content-Type:application/json',
        'authorization:'.$id_token,
        'x-app-key:1pumcdhoonkuk2erg8od3g55uv');
    curl_setopt($url,CURLOPT_HTTPHEADER, $header);
    curl_setopt($url,CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($url,CURLOPT_RETURNTRANSFER, true);
    curl_setopt($url,CURLOPT_FOLLOWLOCATION, 1);
    $resultdatax=curl_exec($url);
    curl_close($url);
    echo $resultdatax;
}

add_action( 'wp_ajax_nopriv_bkash_querypayment', 'bkash_querypayment' );
add_action( 'wp_ajax_bkash_querypayment', 'bkash_querypayment' );
function bkash_querypayment(){
    session_start();
    $paymentID = $_POST['paymentID'];

    $url=curl_init('https://checkout.pay.bka.sh/v1.2.0-beta/checkout/payment/query/'.$paymentID);
    $id_token = encrypt_decrypt('decrypt',$_SESSION['bkash_tokens'],'maskPassOrionToll');
    $header=array(
        'Content-Type:application/json',
        'authorization:'.$id_token,
        'x-app-key:1pumcdhoonkuk2erg8od3g55uv');
    curl_setopt($url,CURLOPT_HTTPHEADER, $header);
    curl_setopt($url,CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($url,CURLOPT_RETURNTRANSFER, true);
    curl_setopt($url,CURLOPT_FOLLOWLOCATION, 1);
    $resultdatax=curl_exec($url);
    curl_close($url);
    echo $resultdatax;
}

add_action( 'wp_ajax_nopriv_bkash_paymentdetails', 'bkash_paymentdetails' );
add_action( 'wp_ajax_bkash_paymentdetails', 'bkash_paymentdetails' );
function bkash_paymentdetails(){
    session_start();
    $trxID = $_POST['trxID'];

    $url=curl_init('https://checkout.pay.bka.sh/v1.2.0-beta/checkout/payment/search/'.$trxID);
    $id_token = encrypt_decrypt('decrypt',$_SESSION['bkash_tokens'],'maskPassOrionToll');
    $header=array(
        'Content-Type:application/json',
        'authorization:'.$id_token,
        'x-app-key:1pumcdhoonkuk2erg8od3g55uv');
    curl_setopt($url,CURLOPT_HTTPHEADER, $header);
    curl_setopt($url,CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($url,CURLOPT_RETURNTRANSFER, true);
    curl_setopt($url,CURLOPT_FOLLOWLOCATION, 1);
    $resultdatax=curl_exec($url);
    curl_close($url);
    echo $resultdatax;
}

add_action( 'wp_ajax_nopriv_get_updated_trip', 'get_updated_trip' );
add_action( 'wp_ajax_get_updated_trip', 'get_updated_trip' );
function get_updated_trip(){
    global $wpdb;
    date_default_timezone_set("Asia/Dhaka");

    $user_id = $_POST['user_id'];
    $gea_subscription_id = $_POST['gea_subscription_id'];
    $info_table_name = $wpdb->prefix . 'users_info';
    $tariff_table_name = $wpdb->prefix . 'tariff_rates';
    $info_result = $wpdb->get_row("SELECT $info_table_name.gea_customer_id, $info_table_name.vehicle_type, $tariff_table_name.toll_rates FROM $info_table_name LEFT JOIN $tariff_table_name ON $tariff_table_name.vehicle_type = $info_table_name.vehicle_type WHERE $info_table_name.user_id = $user_id AND $info_table_name.gea_subscription_id = '".$gea_subscription_id."'");
    $gea_customer_id = $info_result->gea_customer_id;
    //echo $gea_customer_id;
    //exit;
    //$url = "http://182.163.122.66/trips.php?sid=".$gea_subscription_id."&cid=".$gea_customer_id;
    $url = "http://182.163.122.66/get_latest_trip.php?sid=".$gea_subscription_id."&cid=".$gea_customer_id;

    $curl = curl_init();

    $timeout = 5;
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeout);
    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    if($response > 0)
        $total_trip = $response / $info_result->toll_rates;
    else
        $total_trip = $response;

    $trip_table_name = $wpdb->prefix . "users_trips";
    $current_datetime = date('Y-m-d H:i:s');
   // $trip_update_data = $wpdb->update($trip_table_name, array('total_trip' => $response, 'updated_by' => 1, 'date_updated' => $current_datetime), array('user_id' => $user_id, 'gea_subscription_id' => $gea_subscription_id));
    $trip_update_data = $wpdb->query("UPDATE $trip_table_name SET total_trip = ".$total_trip.", date_updated = '".$current_datetime."' WHERE user_id = ".$user_id." AND gea_subscription_id = ".$gea_subscription_id);
    
    echo $current_datetime.'|'.$total_trip;
}

// Change deault login page text
add_filter( 'gettext', 'change_registration_usename_label', 10, 3 );
function change_registration_usename_label( $translated, $text, $domain ) {
    if( is_account_page() && ! is_wc_endpoint_url() ) {
        if( $text === 'Username' ) {
            $translated = __( 'Account number', $domain );
        } elseif( $text === 'Username or email address' ) {
            $translated = __( 'Mobile No.', $domain );
        }
    }

    return $translated;
}

//add class in woocommerce login form
function action_woocommerce_login_form(  ) {
    // make action magic happen here...
    echo '
        <script>
            jQuery(document).ready(function () {
                jQuery( "#main" ).removeClass( "all_colors" ).addClass( "all_colors myAccClass" );
            });
        </script>
    ';
};

add_action( 'woocommerce_login_form', 'action_woocommerce_login_form', 10, 0 );


// Remove lost password url from login page
function remove_lostpassword_text ( $text ) {
    if ($text == 'Lost your password?'){
        $text = '';
    }
    return $text;
}
add_filter( 'gettext', 'remove_lostpassword_text' );

add_action( 'woocommerce_after_customer_login_form', 'custom_login_text' );
function custom_login_text() {
    if( ! is_user_logged_in() ){
        //Your link
        $link = home_url( '/registration' );
        $plink = home_url( '/forgot_password' );

        // The displayed (output)
        echo '<p>'. __("Not a user? <a href='$link'>register now<a/>", "woocommerce"). __(" | <a href='$plink'>Forgot password?<a/>", "woocommerce").'</p>';
    }
}



function wc_custom_user_redirect( $redirect, $user ) {
    // Get the first of all the roles assigned to the user
    $role = $user->roles[0];

    $dashboard = admin_url();
    $myaccount = get_permalink( wc_get_page_id( 'myaccount' ) );

    /*if( $role == 'administrator' ) {
        //Redirect administrators to the dashboard
        $redirect = $dashboard;
    } elseif ( $role == 'shop-manager' ) {
        //Redirect shop managers to the dashboard
        $redirect = $dashboard;
    } elseif ( $role == 'editor' ) {
        //Redirect editors to the dashboard
        $redirect = $dashboard;
    } else*/if ( $role == 'reloads' ) {
        //Redirect authors to the dashboard
        $redirect = home_url('reloadpoints');
    } elseif ( $role == 'accounts' ) {
        //Redirect authors to the dashboard
        $redirect = home_url('transactions');
    } /*elseif ( $role == 'customer' || $role == 'subscriber' ) {
        //Redirect customers and subscribers to the "My Account" page
        $redirect = $myaccount;
    } else {
        //Redirect any other role to the previous visited page or, if not available, to the home
        $redirect = wp_get_referer() ? wp_get_referer() : home_url();
    }*/

    return $redirect;
}
add_filter( 'woocommerce_login_redirect', 'wc_custom_user_redirect', 10, 2 );


// user dashboard add content
add_action( 'woocommerce_account_dashboard', 'user_dashboard_addition' );
function user_dashboard_addition() {
    global $wpdb;
    $user = wp_get_current_user();
    $roles = ( array )$user->roles;
    if($roles[0] != 'accounts') {
        // this adds the prefix which is set by the user upon instillation of wordpress
        $info_table_name = $wpdb->prefix . "users_info";
        $rate_table_name = $wpdb->prefix . "tariff_rates";
        $info_retrieve_data = $wpdb->get_results( "SELECT DISTINCT $info_table_name.id, $info_table_name.user_id, $info_table_name.gea_subscription_id, $info_table_name.gea_customer_id, $info_table_name.vehicle_type, $rate_table_name.toll_rates FROM $info_table_name LEFT JOIN $rate_table_name ON $rate_table_name.vehicle_type = $info_table_name.vehicle_type WHERE $info_table_name.user_id = ".get_current_user_id()." AND $info_table_name.installed = 1 ORDER BY gea_subscription_id" );
        $grand_total_trip = 0;
        if(!empty($info_retrieve_data)){
            $is_corporate = '';
            //$trip_table_name = $wpdb->prefix . "users_trips";
            //$trip_retrieve_data = $wpdb->get_row("SELECT SUM($trip_table_name.total_trip) AS total_trip FROM $trip_table_name WHERE $trip_table_name.user_id = " . get_current_user_id());

            //if (!empty($trip_retrieve_data->total_trip)) {
                //$total_trip = $trip_retrieve_data->total_trip;
                //$grand_total_trip = $grand_total_trip + $total_trip;
            //}

	   $gea_subs_id = 0;
	   foreach($info_retrieve_data as $thisRdata){
		if($gea_subs_id != trim($thisRdata->gea_subscription_id)){
			$trip_table_name = $wpdb->prefix . "users_trips";
		        $trip_retrieve_data = $wpdb->get_row("SELECT $trip_table_name.total_trip FROM $trip_table_name WHERE $trip_table_name.gea_subscription_id = '".trim($thisRdata->gea_subscription_id."'"));
			// echo "SELECT $trip_table_name.total_trip FROM $trip_table_name WHERE $trip_table_name.gea_subscription_id = '".trim($thisRdata->gea_subscription_id."'");
			// var_dump($trip_retrieve_data);
			if (!empty($trip_retrieve_data->total_trip)) {
		                $total_trip = $trip_retrieve_data->total_trip;
               			$grand_total_trip = $grand_total_trip + $total_trip;
            		}
		}

		$gea_subs_id = trim($thisRdata->gea_subscription_id);
           }

            echo "<p> <img style='float:left;width:20%;' src='" . get_bloginfo('url') . "/wp-content/uploads/2020/01/trips.png'> <h2>Your total trip " . $grand_total_trip . ".</h2></p>";
            ?>
            <link rel="stylesheet" href="https://cdn.datatables.net/1.10.20/css/jquery.dataTables.min.css">
            <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.3/css/responsive.dataTables.min.css">
            <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
            <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
            <script src="https://code.jquery.com/jquery-3.3.1.js"></script>
            <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
            <script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js"></script>
            <script src="https://cdn.datatables.net/responsive/2.2.3/js/dataTables.responsive.min.js"></script>
            <script type="text/javascript">
                $.extend( $.fn.dataTable.defaults, {
                    responsive: true
                } );

                $(document).ready(function() {
                    $( "#vehicle_table" ).DataTable();
                } );

                function get_uspdated_trip(id, user_id, gea_subscription_id, gea_customer_id){
                    //alert(id+'||'+gea_subscription_id+'||'+gea_customer_id);
					$.post("<?php echo admin_url('admin-ajax.php');?>", { action: 'get_updated_trip', user_id: user_id, gea_subscription_id: gea_subscription_id, gea_customer_id: gea_customer_id }, function(output) {
						//location.reload();
                        //alert(output);
                        output = output.substring(0, output.length - 1);
                        var result = output.split('|');
                        //alert(result[0]);
                        //alert($("#last_updated_"+id).html());
                        $('#last_updated_'+id).html(result[0]);
                        $('#trip_info_'+id).html(result[1]);
					});
                }
            </script>

            <?php
            echo '<table width="100%" class="display" id="vehicle_table">
                <thead>
                    <tr>
                        <th>Subscription ID</th>
                        <th>Type</th>
                        <th>Reg. No`s</th>
                        <th>Total trip</th>
                        <th>Last Updated</th>
                    </tr>
                 </thead>
                 <tbody>
                ';
		$gea_subscription_id = '';
            foreach ($info_retrieve_data as $thisData){
                //$installed = ($thisData->installed == 1)?'Yes':'Processing';
                $total_trip = 0;
                //$info_id = $thisData->id;
                $reg_retrieve_data = $wpdb->get_row( "SELECT GROUP_CONCAT(vehicle_reg_no SEPARATOR ', ') AS vehicle_reg_no FROM $info_table_name WHERE gea_subscription_id = '".$thisData->gea_subscription_id."' GROUP BY 'all'" );

                /*if(!empty($thisData->parent_id))
                    $info_id = $thisData->parent_info_id;*/

                $trip_table_name = $wpdb->prefix . "users_trips";
                $trip_retrieve_data = $wpdb->get_row( "SELECT $trip_table_name.* FROM $trip_table_name WHERE $trip_table_name.user_id = ".get_current_user_id()." AND $trip_table_name.gea_subscription_id = ".$thisData->gea_subscription_id);
                if(!empty($trip_retrieve_data->id)){
                    $total_trip = $trip_retrieve_data->total_trip;
		}

		if($gea_subscription_id != trim($thisData->gea_subscription_id)){
                echo '
                <tr>
                    <td>'.$thisData->gea_subscription_id.'</td>
                    <td>'.$thisData->vehicle_type.'</td>
                    <td>'.$reg_retrieve_data->vehicle_reg_no.'</td>
                    <td id="trip_info_'.$thisData->id.'">'.$total_trip.'</td>
                    <td><span id="last_updated_'.$thisData->id.'">'.$trip_retrieve_data->date_updated.'</span> <a href="#" onclick="get_uspdated_trip(\''.$thisData->id.'\',\''.$thisData->user_id.'\',\''.$thisData->gea_subscription_id.'\',\''.$thisData->gea_customer_id.'\');return false;" class="avia-button avia-color-theme-color">Get Update</a></td>
                </tr>
            ';
		}

		$gea_subscription_id = trim($thisData->gea_subscription_id);
            }

            echo '
                </tbody>
                <tfoot>
                    <tr>
                        <th>Subscription ID</th>
                        <th>Type</th>
                        <th>Reg. No`s</th>
                        <th>Total trip</th>
                        <th>Last Updated</th>
                    </tr>
                 </tfoot>
            </table>';
        }
    }
    else {
        $redirect = home_url('transactions');
        echo '<a href="'.$redirect.'"><h3>You must go to the transaction page.</h3></a>';
    }
    echo '
            <script>
                jQuery(document).ready(function () {
                    jQuery( "#main" ).removeClass( "all_colors" ).addClass( "all_colors myAccClass" );
                });
            </script>
        ';

}




function my_custom_endpoints() {
	add_rewrite_endpoint( 'add-trip', EP_ROOT | EP_PAGES );
	add_rewrite_endpoint( 'add-vehicle', EP_ROOT | EP_PAGES );
    add_rewrite_endpoint( 'recharge-history', EP_ROOT | EP_PAGES );
    add_rewrite_endpoint( 'deduction-history', EP_ROOT | EP_PAGES );
    add_rewrite_endpoint( 'processing-history', EP_ROOT | EP_PAGES );
}
add_action( 'init', 'my_custom_endpoints' );

function my_custom_query_vars( $vars ) {
    $vars[] = 'add-trip';
    $vars[] = 'add-vehicle';
    $vars[] = 'recharge-history';
    $vars[] = 'deduction-history';
    $vars[] = 'processing-history';
    return $vars;
}
add_filter( 'query_vars', 'my_custom_query_vars', 0 );

function my_custom_flush_rewrite_rules() {
    flush_rewrite_rules();
}
add_action( 'wp_loaded', 'my_custom_flush_rewrite_rules' );

function my_custom_my_account_menu_items( $items ) {
    $user = wp_get_current_user();
    $roles = ( array )$user->roles;
    $roles_array = array('accounts', 'reloads');
    //print_r($roles[0]);
    if(!empty($roles_array) && !in_array($roles[0], $roles_array)) {
        $items = array(
            'dashboard' => __('Dashboard', 'woocommerce'),
            'edit-account' => __('Account Details', 'woocommerce'),
            'add-trip' => 'Add Trip',
            'add-vehicle' => 'Add Vehicle',
            'recharge-history' => 'Recharge History',
            'deduction-history' => 'Deduction History',
            'processing-history' => 'Processing History',
            'customer-logout' => __('Logout', 'woocommerce'),
        );
    }
    else{
        $items = array(
            'dashboard' => __('Dashboard', 'woocommerce'),
            'edit-account' => __('Account Details', 'woocommerce'),
            'customer-logout' => __('Logout', 'woocommerce'),
        );

    }

    return $items;
}
add_filter( 'woocommerce_account_menu_items', 'my_custom_my_account_menu_items' );


require_once( get_template_directory().'/custom_functions/add_trip.php');
require_once( get_template_directory().'/custom_functions/add_vehicle.php');
require_once( get_template_directory().'/custom_functions/recharge_history.php');
require_once( get_template_directory().'/custom_functions/deduction_history.php');
require_once( get_template_directory().'/custom_functions/processing_history.php');


add_action( 'wp_ajax_nopriv_rfid_sticker', 'rfid_sticker' );
add_action( 'wp_ajax_rfid_sticker', 'rfid_sticker' );
function rfid_sticker(){
    global $wpdb;
    if(!empty($_POST['info_id']) && !empty($_POST['gea_product_type_id']) && !empty($_POST['gea_product_id']) && !empty($_POST['gea_customer_id']) && !empty($_POST['gea_subscription_id']) && !empty($_POST['rfid_sticker_no']) && !empty($_POST['install_date'])) {

        $message = "Your Orion Toll Bridge car installation date is ".$_POST['install_date'].". Please bring your car on that day.";
        //$url = 'https://api.mobireach.com.bd/SendTextMessage?Username=orion_pharma&Password=Orion@357159&From=ORION&To='.$_POST['mobile_no'].'&Message='.urlencode($message);
	//$url = 'https://api.boom-cast.com/boomcast/WebFramework/boomCastWebService/OTPMessage.php?masking=ORION&userName=orion&password=cddffa4f9f8e75167355f48d13dced2f&MsgType=TEXT&receiver='.$_POST['mobile_no'].'&message='.urlencode($message);
        //send SMS
/*$url ="http://103.177.125.106:7788/sendtext?apikey=9517ddd56c9fe27b&secretkey=99757acb&callerID=ORION_JGFP&toUser=".$_POST['mobile_no']."&messageContent=".urlencode($message);

    $ch=curl_init($url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.3) Gecko/20070309 Firefox/2.0.0.3");  
    $response = curl_exec( $ch );
    $err = curl_error($curl);
    curl_close($ch);*/

        $table_name = $wpdb->prefix . "users_info";
        $update_data = $wpdb->update($table_name, array('id'=>$_POST['info_id'], 'gea_product_type_id'=>$_POST['gea_product_type_id'], 'gea_product_id'=>$_POST['gea_product_id'], 'gea_customer_id'=>$_POST['gea_customer_id'], 'gea_subscription_id'=>$_POST['gea_subscription_id'], 'rfid_sticker_no'=>$_POST['rfid_sticker_no'], 'install_date'=>$_POST['install_date'], 'updated_by'=>get_current_user_id(), 'date_updated'=>date('Y-m-d H:i:s')), array('id'=>$_POST['info_id']));
    }
}



add_action( 'wp_ajax_nopriv_rfid_user_update', 'rfid_user_update' );
add_action( 'wp_ajax_rfid_user_update', 'rfid_user_update' );
function rfid_user_update(){
    global $wpdb;
    if(!empty($_POST['info_id']) && !empty($_POST['vehicle_type']) && !empty($_POST['vehicle_reg_no']) && !empty($_POST['gea_product_type_id']) && !empty($_POST['gea_product_id']) && !empty($_POST['gea_customer_id']) && !empty($_POST['gea_subscription_id']) && !empty($_POST['rfid_sticker_no']) && !empty($_POST['display_name']) && !empty($_POST['user_login']) && !empty($_POST['total_trip'])) {
        $table_name = $wpdb->prefix . "users_info";
        $info_result = $wpdb->get_row("SELECT UI.user_id FROM $table_name AS UI WHERE UI.ID = ".$_POST['info_id']);
        $update_data = $wpdb->update($table_name, array('id'=>$_POST['info_id'], 'vehicle_type'=>$_POST['vehicle_type'], 'vehicle_reg_no'=>$_POST['vehicle_reg_no'], 'gea_product_type_id'=>$_POST['gea_product_type_id'], 'gea_product_id'=>$_POST['gea_product_id'], 'gea_customer_id'=>$_POST['gea_customer_id'], 'gea_subscription_id'=>$_POST['gea_subscription_id'], 'rfid_sticker_no'=>$_POST['rfid_sticker_no'], 'updated_by'=>get_current_user_id(), 'date_updated'=>date('Y-m-d H:i:s')), array('id'=>$_POST['info_id']));
        $trip_table_name = $wpdb->prefix . "users_trips";
        $trip_retrieve_data = $wpdb->get_row("SELECT $trip_table_name.* FROM $trip_table_name WHERE $trip_table_name.user_id = " . $info_result->user_id . " AND $trip_table_name.gea_subscription_id=" . $_POST['gea_subscription_id']);
        if (!empty($trip_retrieve_data)) {
            $update_data = $wpdb->update($trip_table_name, array('user_id'=>$info_result->user_id, 'gea_subscription_id'=>$_POST['gea_subscription_id'], 'total_trip'=>$_POST['total_trip'], 'updated_by'=>get_current_user_id(), 'date_updated'=>date('Y-m-d H:i:s')), array('user_id'=>$info_result->user_id, 'gea_subscription_id'=>$_POST['gea_subscription_id']));
        } else {
            $trip_insert_data = $wpdb->insert(
                $trip_table_name,
                array(
                    'user_id' => $info_result->user_id,
                    'gea_subscription_id' => $_POST['gea_subscription_id'],
                    'total_trip' => $_POST['total_trip'],
                    'created_by' => get_current_user_id(),
                    'date_inserted' => date('Y-m-d H:i:s')
                ),
                array(
                    '%d',
                    '%d',
                    '%d',
                    '%d',
                    '%s'
                )
            );
        }
        $user_table_name = $wpdb->prefix . "users";
        $update_data = $wpdb->update($user_table_name, array('ID'=>$info_result->user_id, 'user_login'=>$_POST['user_login'], 'user_nicename'=>$_POST['user_login'], 'display_name'=>$_POST['display_name']), array('ID'=>$info_result->user_id));
        if(!empty($_POST['user_pass']))
            wp_update_user(array('ID' => $info_result->user_id, 'user_pass' => $_POST['user_pass']));

    }
}



add_action( 'wp_ajax_nopriv_rfid_installation', 'rfid_installation' );
add_action( 'wp_ajax_rfid_installation', 'rfid_installation' );
function rfid_installation(){
    global $wpdb;
    if(!empty($_POST['info_id']) && !empty($_POST['vehicle_reg_no'])) {
        $table_name = $wpdb->prefix . "users_info";
        $update_data = $wpdb->update($table_name, array('id'=>$_POST['info_id'], 'vehicle_reg_no'=>$_POST['vehicle_reg_no'], 'installed'=>1, 'installed_by'=>get_current_user_id(), 'installed_at'=>date('Y-m-d H:i:s')), array('id'=>$_POST['info_id']));
        if(!empty($_POST['parent'])){
            $parent = explode('_', $_POST['parent']);
            $parent_id = $parent[0];
            $parent_info_id = $parent[1];
            $parent_update_data = $wpdb->update($table_name, array('id'=>$_POST['info_id'], 'parent_id'=>$parent_id, 'parent_info_id'=>$parent_info_id), array('id'=>$_POST['info_id']));
        }
    }
}


// user roles toevoegen
add_role(
    'rfid',
    __( 'RFID', 'testdomain' ),
    array(
        'read'              => true,  // true allows this capability
        'edit_posts'        => true,
        'delete_posts'      => false, // Use false to explicitly deny
        'manage_categories' => true
    )
);


add_role(
    'accounts',
    __( 'Accounts', 'testdomain' ),
    array(
        'read'         => true,  // true allows this capability
        'edit_posts'   => false,
        'delete_posts' => false, // Use false to explicitly deny
    )
);


add_role(
    'reloads',
    __( 'Reloads', 'testdomain' ),
    array(
        'read'         => true,  // true allows this capability
        'edit_posts'   => false,
        'delete_posts' => false, // Use false to explicitly deny
    )
);



/* Change login logo and link
----------------------------------------------------------------------------------------*/
add_filter('login_headerurl','ag_login_link');
function ag_login_link() {
    return home_url();
}

add_action( 'login_enqueue_scripts', 'ag_login_logo' );
function ag_login_logo() { ?>
    <style type="text/css">
        #login {
            width: 364px;
        }

        #login h1 {
            background: transparent;
            padding: 20px;
        }

        #login h1 a {
            background: url(https://mmhf.com.bd/wp-content/uploads/2024/11/202405logo.png) no-repeat center center;
            background-size: 276px 60px;
            height: 60px;
            margin: 0 auto;
            width: 276px;
        }

        .login form .input, .login input[type="text"] {
            font-size: 22px;
            font-weight: 100;
            margin: 2px 6px 16px 0;
            padding: 5px 10px;
        }

        input[type="checkbox"], input[type="color"], input[type="date"], input[type="datetime-local"], input[type="datetime"], input[type="email"], input[type="month"], input[type="number"], input[type="password"], input[type="radio"], input[type="search"], input[type="tel"], input[type="text"], input[type="time"], input[type="url"], input[type="week"], select, textarea {
            border: 1px solid #ddd;
        }

        input[type="checkbox"]:focus, input[type="color"]:focus, input[type="date"]:focus, input[type="datetime-local"]:focus, input[type="datetime"]:focus, input[type="email"]:focus, input[type="month"]:focus, input[type="number"]:focus, input[type="password"]:focus, input[type="radio"]:focus, input[type="search"]:focus, input[type="tel"]:focus, input[type="text"]:focus, input[type="time"]:focus, input[type="url"]:focus, input[type="week"]:focus, select:focus, textarea:focus {
            border: 1px solid #ddd;
            box-shadow: 0 0 2px rgba(0, 0, 0, .5);
        }

        .wp-core-ui .button-group.button-large .button, .wp-core-ui .button.button-large {
            background: #229bee;
            border: 0;
            border-radius: 0;
            box-shadow: none;
            font-weight: 700;
            height: 30px;
            line-height: 28px;
            padding: 1px 12px 2px;
            text-shadow: none;
            text-transform: uppercase;
        }

        .login #backtoblog a:hover, .login #nav a:hover, .login h1 a:hover {
            color: #229bee;
        }

        .login .message {
            border-left-color: #333;
        }

        p#nav{display:none;}
    </style>
<?php }




add_action( 'rest_api_init', 'mmhf_register_route' );
function mmhf_register_route() {
    register_rest_route( 'mmhf-route', 'trip-phrase', array(
            'methods' => 'GET',
            'callback' => 'trip_deduction_phrase',
        )
    );
}
function trip_deduction_phrase() {
    global $wpdb;
    if(!empty($_REQUEST['gea_customer_id']) && !empty($_REQUEST['gea_subscription_id']) && !empty($_REQUEST['vehicle_reg_no'])) {
        $deduct_table_name = $wpdb->prefix . "user_trip_deductions";
        $deduct_retrieve_data = $wpdb->get_row("SELECT $deduct_table_name.id FROM $deduct_table_name WHERE $deduct_table_name.gea_passage_time = '".$_REQUEST['passage_time']."'");
        //echo "SELECT $deduct_table_name.id FROM $deduct_table_name WHERE $deduct_table_name.gea_passage_time = '".$_REQUEST['gea_passage_time']."'";
        //var_dump($deduct_retrieve_data);
        if(empty($deduct_retrieve_data->id)){
            //echo 'ASI';
            $info_table_name = $wpdb->prefix . "users_info";
            $info_retrieve_data = $wpdb->get_row("SELECT $info_table_name.* FROM $info_table_name WHERE $info_table_name.gea_customer_id = '".$_REQUEST['gea_customer_id']."' AND $info_table_name.gea_subscription_id = '".$_REQUEST['gea_subscription_id']."' AND $info_table_name.vehicle_reg_no = '".$_REQUEST['vehicle_reg_no']."'");
            $user_id = $info_retrieve_data->user_id;
            $user_info_id = $info_retrieve_data->id;
            if(!empty($info_retrieve_data->parent_id)){
                $user_id = $info_retrieve_data->parent_id;
                $user_info_id = $info_retrieve_data->parent_info_id;
            }
            $trip_table_name = $wpdb->prefix . "users_trips";
            $trip_retrieve_data = $wpdb->get_row("SELECT $trip_table_name.* FROM $trip_table_name WHERE $trip_table_name.gea_subscription_id=" . $_REQUEST['gea_subscription_id'] ." AND $trip_table_name.user_id=".$user_id);
            if (!empty($trip_retrieve_data->total_trip)) {
                $total_trip = $trip_retrieve_data->total_trip - 1;
                $trip_update_data = $wpdb->update($trip_table_name, array('id' => $trip_retrieve_data->id, 'total_trip' => $total_trip, 'date_updated' => date('Y-m-d H:i:s')), array('id' => $trip_retrieve_data->id));
                $deduct_insert_data = $wpdb->insert(
                    $deduct_table_name,
                    array(
                        'user_id' => $user_id,
                        'user_info_id' => $user_info_id,
                        'trips' => 1,
                        'gea_passage_time' => $_REQUEST['passage_time'],
                        'date_inserted' => date('Y-m-d H:i:s')
                    ),
                    array(
                        '%d',
                        '%d',
                        '%d',
                        '%s',
                        '%s'
                    )
                );
                return true;
            }
        }
    }


    return false;
}

add_action( 'wp_ajax_nopriv_get_vehicle', 'get_vehicle' );
add_action( 'wp_ajax_get_vehicle', 'get_vehicle' );
function get_vehicle(){
    global $wpdb;

    if(!empty($_POST['user_id'])) {
        $table_name = $wpdb->prefix . "users_info";
        $tariff_table_name = $wpdb->prefix . "tariff_rates";
        $info_retrieve_data = $wpdb->get_results( "SELECT $table_name.*, $tariff_table_name.toll_rates  FROM $table_name LEFT JOIN $tariff_table_name ON $tariff_table_name.vehicle_type = $table_name.vehicle_type WHERE $table_name.user_id =".$_POST['user_id'] );

        if(!empty($info_retrieve_data)){
            echo '
                <p><label for="vehicle_type">Select Vehicle <span class="required" title="required" style="color:red;">*</span></label></p>
                <p>
                    <select id="info_id" name="info_id" onchange="get_cost();">
                        <option value="">Select</option>
            ';
                        foreach($info_retrieve_data as $thisResult){
                            $info_id = $thisResult->id;
                            if(!empty($thisResult->parent_id))
                                $info_id = $thisResult->parent_info_id;

                            echo "<option value='".$info_id."_".$thisResult->toll_rates."'>".$thisResult->vehicle_reg_no."</option>";
                        }
            echo '
                    </select>
                </p>
             ';
        }
    }

    wp_die();
}


add_action( 'wp_ajax_nopriv_user_check', 'user_check' );
add_action( 'wp_ajax_user_check', 'user_check' );
function user_check()
{
    global $wpdb;
    $username = $wpdb->escape($_POST['mobile_no']);
    $table_name = $wpdb->prefix . "users";
    //echo "SELECT $table_name.ID FROM $table_name WHERE $table_name.user_login = '".$username."'";
    $user_retrieve_data = $wpdb->get_row( "SELECT $table_name.ID FROM $table_name WHERE $table_name.user_login = '".$username."'" );
    //var_dump($user_retrieve_data->ID);
    if(!empty($user_retrieve_data->ID))
        echo $user_retrieve_data->ID;
    else
        echo 0;

    wp_die();
}

add_action( 'wp_ajax_nopriv_setuser', 'setuser' );
add_action( 'wp_ajax_setuser', 'setuser' );
function setuser()
{
    session_start();
    $_SESSION['passUserID'] = $_POST['t'];
}

add_action( 'wp_ajax_nopriv_get_subscription_ids', 'get_subscription_ids' );
add_action( 'wp_ajax_get_subscription_ids', 'get_subscription_ids' );
function get_subscription_ids(){
    global $wpdb;
    $customer_id = explode('|', $_POST['gea_customer_id']);
    $gea_customer_id = $customer_id[1];
    $table_name = $wpdb->prefix . 'users_info';
    $results = $wpdb->get_results("SELECT DISTINCT gea_subscription_id FROM $table_name WHERE gea_customer_id = '".$gea_customer_id."'");
    echo '<select name="gea_subscription_id" class="select is_empty" id="gea_subscription_id">';
    foreach($results as $thisResult){
        echo '<option value="'.$thisResult->gea_subscription_id.'">'.$thisResult->gea_subscription_id.'</option>';
    }
    echo "</select>";
}

add_action( 'wp_ajax_nopriv_set_user_trip', 'set_user_trip' );
add_action( 'wp_ajax_set_user_trip', 'set_user_trip' );
function set_user_trip(){
    global $wpdb;
    $customer_id = explode('|', $_POST['gea_customer_id']);
    $user_id = $customer_id[0];
    $gea_customer_id = $customer_id[1];
    $gea_subscription_id = $_POST['gea_subscription_id'];
    $total_trip = $_POST['total_trip'];
    $table_name = $wpdb->prefix . 'users_trips';
    $trip_insert_data = $wpdb->insert(
        $table_name,
        array(
            'user_id' => $user_id,
            'gea_subscription_id' => $gea_subscription_id,
            'total_trip' => $total_trip,
            'isactive' => 1,
            'created_by' => get_current_user_id(),
            'date_inserted' => date('Y-m-d H:i:s')
        ),
        array(
            '%d',
            '%s',
            '%d',
            '%d',
            '%d',
            '%s'
        )
    );
}


add_action( 'wp_ajax_nopriv_missing_user_insert', 'missing_user_insert' );
add_action( 'wp_ajax_missing_user_insert', 'missing_user_insert' );
function missing_user_insert(){
    global $wpdb, $PasswordHash, $current_user, $user_id;
    $password = $wpdb->escape($_POST['user_pass']);
    $first_name = $wpdb->escape($_POST['display_name']);
    $v_account_type = $wpdb->escape($_POST['account_type']);
    $username = $wpdb->escape($_POST['user_login']);
    $useremail = $wpdb->escape($_POST['user_email']);
    $vehicle_reg_no = $wpdb->escape(trim($_POST['vehicle_reg_no']));
    $vehicle_type = $wpdb->escape($_POST['vehicle_type']);
    //exit;
    $nid = $wpdb->escape($_POST['nid']);
    $address = $wpdb->escape($_POST['address']);
    $auth_person_name = $wpdb->escape($_POST['auth_person_name']);
    $amount = 100;

    $process_fees_table_name = $wpdb->prefix . "user_process_fees";
    $process_fees_retrieve_data = $wpdb->get_row("SELECT MAX(id) AS last_id FROM $process_fees_table_name");
    if(!empty($process_fees_retrieve_data->last_id)){
        $last_invoice_id = $user_id.date('Ym').($process_fees_retrieve_data->last_id+1).date('his');
    }
    else
        $last_invoice_id = $user_id.date('Ym').'1'.date('his');


    $pgw_transaction_id = $wpdb->escape($_POST['pgw_transaction_id']);

    if(username_exists($username) ) {
        echo 'Mobile no already exist.';
    } else {

        $user_id = wp_insert_user( array ('first_name' => apply_filters('pre_user_first_name', $first_name), 'user_pass' => apply_filters('pre_user_user_pass', $password), 'user_login' => apply_filters('pre_user_user_login', $username), 'user_email' => apply_filters('pre_user_user_email', $useremail), 'role' => 'subscriber' ) );
        if( is_wp_error($user_id) ) {
            $error= 'Error on user creation.';
        } else {
            do_action('user_register', $user_id);
            $user_table_name = $wpdb->prefix . "users";
            $user_update_data = $wpdb->update($user_table_name, array('id'=>$user_id, 'v_account_type'=>$v_account_type, 'auth_person_name'=>$auth_person_name, 'user_address'=>$address), array('id'=>$user_id));

            $users_info_table_name = $wpdb->prefix . "users_info";
            $users_info_insert_data = $wpdb->insert(
                $users_info_table_name,
                array(
                    'user_id' => $user_id,
                    'nid' => $nid,
                    'vehicle_type' => $vehicle_type,
                    'vehicle_reg_no' => $vehicle_reg_no,
                    'date_inserted' => date('Y-m-d H:i:s')
                ),
                array(
                    '%d',
                    '%s',
                    '%s',
                    '%s',
                    '%s'
                )
            );
            $user_info_id = $wpdb->insert_id;
            $process_fees_table_name = $wpdb->prefix . "user_process_fees";
            $process_fees_insert_data = $wpdb->insert(
                $process_fees_table_name,
                array(
                    'user_id' => $user_id,
                    'user_info_id' => $user_info_id,
                    'invoice_id' => $last_invoice_id,
                    'process_amount' => $amount,
                    'pgw_name' => 'bkash',
                    'pgw_transaction_id' => $pgw_transaction_id
                ),
                array(
                    '%d',
                    '%d',
                    '%s',
                    '%d',
                    '%s',
                    '%s'
                )
            );

            echo 'Success';
        }

    }
}

function CityProcessRequest($curl_post_data,$service_url,$proxy,$proxyauth)
{
    $uploads = wp_upload_dir();
    $upload_path = $uploads['basedir'];
    $certfile       = $upload_path.'/mmhfcitydhrubo/mmhf.crt';
    $keyfile        = $upload_path.'/mmhfcitydhrubo/mmhf.key';
    $ch = curl_init();
    curl_setopt( $ch, CURLOPT_URL, $service_url );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
    curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt( $ch, CURLOPT_SSLCERT, $certfile );
    curl_setopt( $ch, CURLOPT_SSLKEY, $keyfile );
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $curl_post_data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    $output = curl_exec($ch);
    if (curl_error($ch)) {
        echo $error_msg = curl_error($ch);
    }
    $cblcz = json_decode($output, true );
    return $cblcz;
}

add_action( 'wp_ajax_nopriv_register_city', 'register_city' );
add_action( 'wp_ajax_register_city', 'register_city' );
function register_city(){
    session_start();
    global $wpdb;
    $myObj = new StdClass();
    $myObj->f_name = $_POST['f_name'];
    $myObj->mobile_no = $_POST['mobile_no'];
    $myObj->user_email = $_POST['user_email'];
    $myObj->password1 = $_POST['password1'];
    $myObj->account_type = $_POST['account_type'];
    $myObj->vehicle_reg_no = $_POST['vehicle_reg_no'];
    $myObj->nid = $_POST['nid'];
    $myObj->address = $_POST['address'];
    $myObj->vehicle_type = $_POST['vehicle_type'];
    $myObj->auth_person_name = $_POST['auth_person_name'];

    $user_data = array("f_name"=>$_POST['f_name'], "mobile_no"=>$_POST['mobile_no'], "user_email"=>$_POST['user_email'], "password1"=>$_POST['password1'], "account_type"=>$_POST['account_type'], "vehicle_reg_no"=>$_POST['vehicle_reg_no'], "nid"=>$_POST['nid'], "address"=>$_POST['address'], "vehicle_type"=>$_POST['vehicle_type'], "auth_person_name"=>$_POST['auth_person_name']);

    $_SESSION['mmhfreg_data'] = json_encode($myObj);
    setcookie('mmhfreg_data', json_encode($myObj), time() + (86400 * 30), "/");

    $proxy = "";
    $proxyauth = "";
    $postDatatoken = '{
        "password": "123456Aa",
        "userName": "mmhf"
    }';
    $serviceUrltoken= 'https://ecomm-webservice.thecitybank.com:7788/transaction/token';
    $cblcz = CityProcessRequest($postDatatoken,$serviceUrltoken,$proxy,$proxyauth);
    $transactionId = $cblcz['transactionId'];
    $serviceUrlEcomm = 'https://ecomm-webservice.thecitybank.com:7788/transaction/createorder';

    $postdataEcomm = '{
        "merchantId": "9107027096",
        "amount": "'.(100*100).'",
        "currency": "050",
        "description": "Reference_Number ",
        "approveUrl": "'.site_url().'/cityreturn",
        "cancelUrl": "'.site_url().'/cityreturn",
        "declineUrl": "'.site_url().'/cityreturn",
        "userName": "mmhf",
        "passWord": "123456Aa",
        "secureToken": "'.$transactionId.'"
    }';


    $cblEcomm = CityProcessRequest($postdataEcomm,$serviceUrlEcomm,$proxy,$proxyauth);

    $URL = $cblEcomm['items']['url'];
    $orderId = $cblEcomm['items']['orderId'];
    $sessionId = $cblEcomm['items']['sessionId'];
    $redirectUrl = $URL."?ORDERID=".$orderId."&SESSIONID=".$sessionId;

    $bank_process_data = array("order_id"=>$orderId, "session_id"=>$sessionId);

    $bank_tokens_table_name = $wpdb->prefix . "bank_tokens";
    $bank_tokens_insert_data = $wpdb->insert(
        $bank_tokens_table_name,
        array(
            'bank_name' => 'city',
            'bank_token' => $sessionId,
            'bank_process_data' => json_encode($bank_process_data),
            'user_data' => json_encode($user_data),
            'process_status' => 'pending',
            'payment_for' => 'new user registration',
            'created_by' => '0',
            'created_at' => date('Y-m-d H:i:s')
        ),
        array(
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%d',
            '%s'
        )
    );

    echo $redirectUrl;
}

add_action( 'wp_ajax_nopriv_register_nagad', 'register_nagad' );
add_action( 'wp_ajax_register_nagad', 'register_nagad' );
function register_nagad(){
    session_start();
    global $wpdb;
    $myObj = new StdClass();
    $myObj->f_name = $_POST['f_name'];
    $myObj->mobile_no = $_POST['mobile_no'];
    $myObj->user_email = $_POST['user_email'];
    $myObj->password1 = $_POST['password1'];
    $myObj->account_type = $_POST['account_type'];
    $myObj->vehicle_reg_no = $_POST['vehicle_reg_no'];
    $myObj->nid = $_POST['nid'];
    $myObj->address = $_POST['address'];
    $myObj->vehicle_type = $_POST['vehicle_type'];
    $myObj->auth_person_name = $_POST['auth_person_name'];

    $user_data = array("f_name"=>$_POST['f_name'], "mobile_no"=>$_POST['mobile_no'], "user_email"=>$_POST['user_email'], "password1"=>$_POST['password1'], "account_type"=>$_POST['account_type'], "vehicle_reg_no"=>$_POST['vehicle_reg_no'], "nid"=>$_POST['nid'], "address"=>$_POST['address'], "vehicle_type"=>$_POST['vehicle_type'], "auth_person_name"=>$_POST['auth_person_name']);

    $_SESSION['mmhfreg_data'] = json_encode($myObj);
    setcookie('mmhfreg_data', json_encode($myObj), time() + (86400 * 30), "/");
    $data = base64_encode('100;null;reg');
    echo 'https://mmhf.com.bd/nagadexecute/?data='.$data;
}

add_action( 'wp_ajax_nopriv_addtrip_upay', 'addtrip_upay' );
add_action( 'wp_ajax_addtrip_upay', 'addtrip_upay' );
function addtrip_upay(){
    session_start();
    $upay_initial_url = 'https://pg.upaysystem.com/payment/merchant-auth/';
    $upay_initial_data  = array(
        "merchant_id" => "1120101070010830",
        "merchant_key" => "tFhyiQv73FVyrmSLr9TiLhoUiZm78yqF"
	);
    $upay_initial_datapayload = json_encode($upay_initial_data);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $upay_initial_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $upay_initial_datapayload);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

    // Execute the POST request
    $upay_initial_result = curl_exec($ch);
    $upay_initial_result = json_decode($upay_initial_result);
    curl_close($ch);

    //echo '<pre>';
    //print_r($upay_initial_result->data->token);
    //echo '</pre>';
    $_SESSION['upay_token'] = $upay_initial_result->data->token;
    setcookie('upay_token', $upay_initial_result->data->token, time() + (86400 * 30), "/");

    $header = array(
        'Content-Type:application/json',
        'Authorization:UPAY ' . $upay_initial_result->data->token
    );

    $invoice_id = $_POST['invoice_id'];
    $post_data = array(
        "date" => date('Y-m-d'),
        "txn_id" => $invoice_id,
        "invoice_id" => $invoice_id,
        "amount" => $_POST['amount'],
        "merchant_id" => "1120101070010830",
        "merchant_name" => "ORION INFRUSTRUCTURE LIMITED",
        "merchant_code" => "4784",
        "merchant_country_code" => "BD",
        "merchant_city" => "Dhaka",
        "merchant_category_code" => "4784",
        "merchant_mobile" => $_POST['user_login'], 
        "transaction_currency_code" => "BDT",
        "redirect_url" => "https://mmhf.com.bd/upayreturn/"
    );

    $post_data = json_encode($post_data);
    //exit;

    $init_ch = curl_init();
    curl_setopt($init_ch, CURLOPT_URL, "https://pg.upaysystem.com/payment/merchant-payment-init/");
    curl_setopt($init_ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($init_ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($init_ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($init_ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($init_ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($init_ch, CURLOPT_HTTPHEADER, $header);
    
    $upay_init_payment_result = curl_exec($init_ch);
    $upay_init_payment_result = json_decode($upay_init_payment_result);
    curl_close($init_ch);

    //echo '<pre>';
    //print_r($upay_init_payment_result->data);
    //echo '</pre>';
    //exit;

    $sessionId = $upay_init_payment_result->data->session_id;
    $orderId = $upay_init_payment_result->data->trx_id;
    $bank_process_data = array("trx_id"=>$orderId, "session_id"=>$sessionId);
    $user_data = array("trip_no"=>$_POST['trip_no'], "extra_trip"=>$_POST['extra_trip'], "user_id"=>$_POST['user_id'], "gea_subscription_id"=>$_POST['gea_subscription_id'], "invoice_id"=>$invoice_id, "vehicle_type"=>$_POST['vehicle_type'], "amount"=>$_POST['amount']);

    global $wpdb;
    $bank_tokens_table_name = $wpdb->prefix . "bank_tokens";
    $bank_tokens_insert_data = $wpdb->insert(
        $bank_tokens_table_name,
        array(
            'bank_name' => 'upay',
            'bank_token' => $upay_initial_result->data->token,
            'bank_process_data' => json_encode($bank_process_data),
            'user_data' => json_encode($user_data),
            'process_status' => 'pending',
            'payment_for' => 'add trip',
            'created_by' => get_current_user_id(),
            'created_at' => date('Y-m-d H:i:s')
        ),
        array(
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%d',
            '%s'
        )
    );
    
    echo $upay_init_payment_result->data->gateway_url;
    exit;
}


add_action( 'wp_ajax_nopriv_addtrip_city', 'addtrip_city' );
add_action( 'wp_ajax_addtrip_city', 'addtrip_city' );
function addtrip_city(){
    session_start();

    // check the nonce, if it fails the function will break
    add_filter( 'nonce_life', 'drb_nonce_lifetime' );
    check_ajax_referer( '21Dhruboorion&@mmhf', 'security' );
    remove_filter( 'nonce_life', 'drb_nonce_lifetime' );

    global $wpdb;
    $myObj = new StdClass();
    $myObj->trip_no = $_POST['trip_no'];
    $myObj->extra_trip = $_POST['extra_trip'];
    $myObj->user_id = $_POST['user_id'];
    $myObj->gea_subscription_id = $_POST['gea_subscription_id'];
    $myObj->invoice_id = $_POST['invoice_id'];
    $myObj->vehicle_type = $_POST['vehicle_type'];
    $myObj->amount = $_POST['amount'];

    $user_data = array("trip_no"=>$_POST['trip_no'], "extra_trip"=>$_POST['extra_trip'], "user_id"=>$_POST['user_id'], "gea_subscription_id"=>$_POST['gea_subscription_id'], "invoice_id"=>$_POST['invoice_id'], "vehicle_type"=>$_POST['vehicle_type'], "amount"=>$_POST['amount']);

    $_SESSION['mmhftrip_data'] = json_encode($myObj);
    setcookie('mmhftrip_data', json_encode($myObj), time() + (86400 * 30), "/");

    $proxy = "";
    $proxyauth = "";
    $postDatatoken = '{
        "password": "123456Aa",
        "userName": "mmhf"
    }';
    $serviceUrltoken= 'https://ecomm-webservice.thecitybank.com:7788/transaction/token';
    $cblcz = CityProcessRequest($postDatatoken,$serviceUrltoken,$proxy,$proxyauth);
    $transactionId = $cblcz['transactionId'];
    $serviceUrlEcomm = 'https://ecomm-webservice.thecitybank.com:7788/transaction/createorder';

    $postdataEcomm = '{
        "merchantId": "9107027096",
        "amount": "'.($_POST['amount']*100).'",
        "currency": "050",
        "description": "Reference_Number ",
        "approveUrl": "'.site_url().'/citytripreturn",
        "cancelUrl": "'.site_url().'/citytripreturn",
        "declineUrl": "'.site_url().'/citytripreturn",
        "userName": "mmhf",
        "passWord": "123456Aa",
        "secureToken": "'.$transactionId.'"
    }';


    $cblEcomm = CityProcessRequest($postdataEcomm,$serviceUrlEcomm,$proxy,$proxyauth);

    $URL = $cblEcomm['items']['url'];
    $orderId = $cblEcomm['items']['orderId'];
    $sessionId = $cblEcomm['items']['sessionId'];
    $redirectUrl = $URL."?ORDERID=".$orderId."&SESSIONID=".$sessionId;

    $bank_process_data = array("order_id"=>$orderId, "session_id"=>$sessionId);

    $bank_tokens_table_name = $wpdb->prefix . "bank_tokens";
    $bank_tokens_insert_data = $wpdb->insert(
        $bank_tokens_table_name,
        array(
            'bank_name' => 'city',
            'bank_token' => $sessionId,
            'bank_process_data' => json_encode($bank_process_data),
            'user_data' => json_encode($user_data),
            'process_status' => 'pending',
            'payment_for' => 'add trip',
            'created_by' => get_current_user_id(),
            'created_at' => date('Y-m-d H:i:s')
        ),
        array(
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%d',
            '%s'
        )
    );

    echo $redirectUrl;
}

add_action( 'wp_ajax_nopriv_addtrip_nagad', 'addtrip_nagad' );
add_action( 'wp_ajax_addtrip_nagad', 'addtrip_nagad' );
function addtrip_nagad(){
    session_start();

    // check the nonce, if it fails the function will break
    add_filter( 'nonce_life', 'drb_nonce_lifetime' );
    check_ajax_referer( '21Dhruboorion&@mmhf', 'security' );
    remove_filter( 'nonce_life', 'drb_nonce_lifetime' );

    global $wpdb;
    $myObj = new StdClass();
    $myObj->trip_no = $_POST['trip_no'];
    $myObj->extra_trip = $_POST['extra_trip'];
    $myObj->user_id = $_POST['user_id'];
    $myObj->gea_subscription_id = $_POST['gea_subscription_id'];
    $myObj->invoice_id = $_POST['invoice_id'];
    $myObj->vehicle_type = $_POST['vehicle_type'];
    $myObj->amount = $_POST['amount'];

    $user_data = array("trip_no"=>$_POST['trip_no'], "extra_trip"=>$_POST['extra_trip'], "user_id"=>$_POST['user_id'], "gea_subscription_id"=>$_POST['gea_subscription_id'], "invoice_id"=>$_POST['invoice_id'], "vehicle_type"=>$_POST['vehicle_type'], "amount"=>$_POST['amount']);

    $_SESSION['mmhftrip_data'] = json_encode($myObj);
    setcookie('mmhftrip_data', json_encode($myObj), time() + (86400 * 30), "/");
    $data = base64_encode($_POST['amount'].';'.$_POST['invoice_id'].';trip');
    echo 'https://mmhf.com.bd/nagadexecute/?data='.$data;
}

add_action( 'wp_ajax_nopriv_addvehicle_city', 'addvehicle_city' );
add_action( 'wp_ajax_addvehicle_city', 'addvehicle_city' );
function addvehicle_city(){
    session_start();
    global $wpdb;
    $myObj = new StdClass();
    $myObj->vehicle_reg_no = $_POST['vehicle_reg_no'];
    $myObj->user_id = $_POST['user_id'];
    $myObj->invoice_id = $_POST['invoice_id'];
    $myObj->vehicle_type = $_POST['vehicle_type'];
    $myObj->amount = $_POST['amount'];

    $user_data = array("vehicle_reg_no"=>$_POST['vehicle_reg_no'], "user_id"=>$_POST['user_id'], "invoice_id"=>$_POST['invoice_id'], "vehicle_type"=>$_POST['vehicle_type'], "amount"=>$_POST['amount']);

    $_SESSION['mmhfvehicle_data'] = json_encode($myObj);
    setcookie('mmhfvehicle_data', json_encode($myObj), time() + (86400 * 30), "/");

    $proxy = "";
    $proxyauth = "";
    $postDatatoken = '{
        "password": "123456Aa",
        "userName": "mmhf"
    }';
    $serviceUrltoken= 'https://ecomm-webservice.thecitybank.com:7788/transaction/token';
    $cblcz = CityProcessRequest($postDatatoken,$serviceUrltoken,$proxy,$proxyauth);
    $transactionId = $cblcz['transactionId'];
    $serviceUrlEcomm = 'https://ecomm-webservice.thecitybank.com:7788/transaction/createorder';

    $postdataEcomm = '{
        "merchantId": "9107027096",
        "amount": "'.($_POST['amount']*100).'",
        "currency": "050",
        "description": "Reference_Number ",
        "approveUrl": "'.site_url().'/cityvehiclereturn",
        "cancelUrl": "'.site_url().'/cityvehiclereturn",
        "declineUrl": "'.site_url().'/cityvehiclereturn",
        "userName": "mmhf",
        "passWord": "123456Aa",
        "secureToken": "'.$transactionId.'"
    }';


    $cblEcomm = CityProcessRequest($postdataEcomm,$serviceUrlEcomm,$proxy,$proxyauth);

    $URL = $cblEcomm['items']['url'];
    $orderId = $cblEcomm['items']['orderId'];
    $sessionId = $cblEcomm['items']['sessionId'];
    $redirectUrl = $URL."?ORDERID=".$orderId."&SESSIONID=".$sessionId;

    $bank_process_data = array("order_id"=>$orderId, "session_id"=>$sessionId);

    $bank_tokens_table_name = $wpdb->prefix . "bank_tokens";
    $bank_tokens_insert_data = $wpdb->insert(
        $bank_tokens_table_name,
        array(
            'bank_name' => 'city',
            'bank_token' => $sessionId,
            'bank_process_data' => json_encode($bank_process_data),
            'user_data' => json_encode($user_data),
            'process_status' => 'pending',
            'payment_for' => 'add vehicle',
            'created_by' => get_current_user_id(),
            'created_at' => date('Y-m-d H:i:s')
        ),
        array(
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%d',
            '%s'
        )
    );

    echo $redirectUrl;
}

add_action( 'wp_ajax_nopriv_addvehicle_nagad', 'addvehicle_nagad' );
add_action( 'wp_ajax_addvehicle_nagad', 'addvehicle_nagad' );
function addvehicle_nagad(){
    session_start();
    global $wpdb;
    $myObj = new StdClass();
    $myObj->vehicle_reg_no = $_POST['vehicle_reg_no'];
    $myObj->user_id = $_POST['user_id'];
    $myObj->invoice_id = $_POST['invoice_id'];
    $myObj->vehicle_type = $_POST['vehicle_type'];
    $myObj->amount = $_POST['amount'];

    $user_data = array("vehicle_reg_no"=>$_POST['vehicle_reg_no'], "user_id"=>$_POST['user_id'], "invoice_id"=>$_POST['invoice_id'], "vehicle_type"=>$_POST['vehicle_type'], "amount"=>$_POST['amount']);

    $_SESSION['mmhfvehicle_data'] = json_encode($myObj);
    setcookie('mmhfvehicle_data', json_encode($myObj), time() + (86400 * 30), "/");
    $data = base64_encode($_POST['amount'].';'.$_POST['invoice_id'].';vehicle');
    echo 'https://mmhf.com.bd/nagadexecute/?data='.$data;
}


add_action( 'wp_ajax_nopriv_addtrip_dbbl', 'addtrip_dbbl' );
add_action( 'wp_ajax_addtrip_dbbl', 'addtrip_dbbl' );
function addtrip_dbbl(){
    session_start();
    global $wpdb;
    $myObj = new StdClass();
    $myObj->trip_no = $_POST['trip_no'];
    $myObj->user_id = $_POST['user_id'];
    $myObj->gea_subscription_id = $_POST['gea_subscription_id'];
    $myObj->invoice_id = $_POST['invoice_id'];
    $myObj->vehicle_type = $_POST['vehicle_type'];
    $myObj->amount = $_POST['amount'];

    $RedirectUrl = "";

    $userid = '000599992510000';
    $pwd    = '$2a$10$fW4dQ945hQDWZBv45XFcj.DqucgjrVHdZ3TTWNSzaLAqvvAllCi7C'; //-- UAT
    $checksum = 'ORINfrad251';  // -- UAT
    //$dbblurl = "https://ecomtest.dutchbanglabank.com"; // --- Test Server ---//
    //$dbblurl = "https://ecom.dutchbanglabank.com";  // --- Live Server 1 ---//
    $dbblurl = "https://ecom1.dutchbanglabank.com"; // --- Live Server 2 ---//
    $ecomrsUrl = $dbblurl.'/ecomrs/rsvr/ecomtxn/getransid';

    $clientIp = '192.144.80.231'; //$_SERVER['REMOTE_ADDR'];
    $cardType = $_POST['card_type'];
    $billamount = $_POST['amount'];
    $fee = 0;
    $txnAmount = $_POST['amount'];
    $invoiceNo = $_POST["invoice_id"];	

    $billamount = $billamount * 100;
    $fee = $fee * 100;
    $totalamount = $txnAmount * 100;

    $dbbl_data  = array(
        "userid" => $userid,
        "passwd" => $pwd,
        "amount" => $totalamount,
        "cardtype" => $cardType,
        "txnrefnum" => $invoiceNo,
        "clientip" => $clientIp
	);
    $payload = json_encode($dbbl_data);
    //var_dump($payload);
    //echo $ecomrsUrl;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $ecomrsUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

    // Execute the POST request
    $result = curl_exec($ch);
    //var_dump($result);

    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		
    if($httpcode != '200'){
        $result = '{"RSPCODE":"0","RSPMSG":"API Request Failed" }';
    }

    $dbblinit = json_decode($result, false);

    $_SESSION['DBBL_TRANSACTION_ID'] = $dbblinit->TRANSACTION_ID;
    setcookie('DBBL_TRANSACTION_ID', $dbblinit->TRANSACTION_ID, time() + (86400 * 30), "/");

    $dbbl_card = ($_POST['card_type']==6)?'rocket':'nexus';
    $user_data = array("trip_no"=>$_POST['trip_no'], "extra_trip"=>$_POST['extra_trip'], "user_id"=>$_POST['user_id'], "gea_subscription_id"=>$_POST['gea_subscription_id'], "invoice_id"=>$_POST['invoice_id'], "vehicle_type"=>$_POST['vehicle_type'], "amount"=>$_POST['amount']);

    $_SESSION['mmhfreg_data'] = json_encode($myObj);
    setcookie('mmhfreg_data', json_encode($myObj), time() + (86400 * 30), "/");
        
    if($dbblinit->RSPCODE == '1' ){

        $bank_process_data = array("order_id"=>$dbblinit->TRANSACTION_ID, "dbbl_card"=>$dbbl_card);

        $bank_tokens_table_name = $wpdb->prefix . "bank_tokens";
        $bank_tokens_insert_data = $wpdb->insert(
            $bank_tokens_table_name,
            array(
                'bank_name' => 'dbbl '.$dbbl_card,
                'bank_token' => $dbblinit->TRANSACTION_ID,
                'bank_process_data' => json_encode($bank_process_data),
                'user_data' => json_encode($user_data),
                'process_status' => 'pending',
                'payment_for' => 'add trip',
                'created_by' => get_current_user_id(),
                'created_at' => date('Y-m-d H:i:s')
            ),
            array(
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%d',
                '%s'
            )
        );

        echo $RedirectUrl = $dbblinit->REDIRECT_URL; 
    }
    else{
        echo $RspMessage =  $dbblinit->RSPMSG;
    }
}

add_action( 'woocommerce_login_form_start','bbloomer_add_login_text' );
function bbloomer_add_login_text() {
    if(!empty($_REQUEST['msg']))
        echo '<h3 class="bb-login-subtitle" style="color:green;">'.$_REQUEST['msg'].'</h3>';
}


add_action( 'rest_api_init', 'create_pgwcron_endpoint' );
 
function create_pgwcron_endpoint(){
    register_rest_route(
        'wp/v2',
        '/pgwcron',
        array(
            'methods' => 'GET',
            'callback' => 'get_pgw_response',
        )
    );
}
 
function get_pgw_response() {
    // your code
    global $wpdb;
    date_default_timezone_set("Asia/Dhaka");
        
    $bank_tokens_table_name = $wpdb->prefix . "bank_tokens";
    $bank_tokens_retrieve_data = $wpdb->get_results("SELECT * FROM $bank_tokens_table_name WHERE bank_name = 'nagad' AND process_status = 'pending' AND payment_for = 'add trip' /*AND created_by = '".get_current_user_id()."' */AND DATE_FORMAT(created_at, '%Y-%m-%d') = '".date('Y-m-d')."'");
    //echo "SELECT * FROM $bank_tokens_table_name WHERE bank_name = 'nagad' AND process_status = 'pending' AND payment_for = 'add trip' AND DATE_FORMAT(created_at, '%Y-%m-%d') = '".date('Y-m-d')."'";
    if(!empty($bank_tokens_retrieve_data)){
	//echo 'ASI';
        foreach($bank_tokens_retrieve_data as $thiBankData){
            echo $paymentRefId = $thiBankData->bank_token;
            $url = "https://api.mynagad.com/api/dfs/verify/payment/".$payment_ref_id;
            $ch = curl_init();
            $timeout = 10;
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/0 (Windows; U; Windows NT 0; zh-CN; rv:3)");
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $file_contents = curl_exec($ch);
            echo curl_error($ch);
            curl_close($ch);
            $json = $file_contents;
            $arr = json_decode($json, true);
            if (!empty($arr)) {
    
                $trans_id = !empty($arr['orderId'])?$arr['orderId']:'';
                //$paymentRefId = !empty($arr['paymentRefId'])?$arr['paymentRefId']:'';
                $pgw_transaction_id = $trans_id.'||'.$paymentRefId;
                $amount = !empty($arr['amount'])?$arr['amount']:'';
                if(!empty($bank_tokens_retrieve_data->user_data)){
                    $string = substr($bank_tokens_retrieve_data->user_data, 1, -1);
                    $bank_desc = $paymentRefId;
                    $trip_data = json_decode(stripslashes($string));
                    $trip_no = $wpdb->escape($trip_data->trip_no);
                    $user_id = $wpdb->escape($trip_data->user_id);
                    $gea_subscription_id = $wpdb->escape($trip_data->gea_subscription_id);
                    $invoice_id = $wpdb->escape($trip_data->invoice_id);
                    $vehicle_type = $wpdb->escape($trip_data->vehicle_type);
                    $tellamount = $wpdb->escape($trip_data->amount);
                }
    
                //echo $bank_tokens_retrieve_data->payment_for;
                //exit;
                if (isset($arr['status']) && !empty($arr['status'])) {
                    if ($arr['status'] == 'Success' && !empty($user_id)) {
                        $recharge_table_name = $wpdb->prefix . "users_recharge_histories";
                        $recharge_retrieve_data = $wpdb->get_row("SELECT $recharge_table_name.id FROM $recharge_table_name WHERE $recharge_table_name.pgw_transaction_id = '$pgw_transaction_id'");
                        //echo 'ASI';
                        echo "SELECT $recharge_table_name.id FROM $recharge_table_name WHERE $recharge_table_name.pgw_transaction_id = '$pgw_transaction_id'";
                        if (empty($recharge_retrieve_data->id)) {
                            $info_table_name = $wpdb->prefix . "users_info";
                            $info_retrieve_data = $wpdb->get_row("SELECT $info_table_name.* FROM $info_table_name WHERE $info_table_name.gea_subscription_id = $gea_subscription_id");
                            $tariff_table_name = $wpdb->prefix . "tariff_rates";
                            $tariff_retrieve_data = $wpdb->get_row("SELECT $tariff_table_name.* FROM $tariff_table_name WHERE $tariff_table_name.vehicle_type = '$vehicle_type'");
                            $actual_trip_no = floor($total_cost / $tariff_retrieve_data->toll_rates);
                            $total_cost = $tariff_retrieve_data->toll_rates * $trip_no;
                            $recharge_insert_data = $wpdb->insert(
                                $recharge_table_name,
                                array(
                                    'user_id' => $user_id,
                                    'gea_subscription_id' => $gea_subscription_id,
                                    'invoice_id' => $invoice_id,
                                    'pgw_name' => 'nagad',
                                    'pgw_transaction_id' => $pgw_transaction_id,
                                    'pgw_payment_id' => $trans_id,
                                    'pgw_amount' => $tellamount,
                                    'trips' => $trip_no,
                                    'created_by' => $user_id,
                                    //'date_inserted' => date('Y-m-d H:i:s')
                                ),
                                array(
                                    '%d',
                                    '%s',
                                    '%s',
                                    '%s',
                                    '%s',
                                    '%d',
                                    '%d',
                                    '%d',
                                    '%d'
                                )
                            );
                            $trip_table_name = $wpdb->prefix . "users_trips";
                            $trip_retrieve_data = $wpdb->get_row("SELECT $trip_table_name.* FROM $trip_table_name WHERE $trip_table_name.user_id = " . $user_id . " AND $trip_table_name.gea_subscription_id=" . $gea_subscription_id);
                            $total_trip = $actual_trip_no;
                            if (!empty($trip_retrieve_data)) {
                                $total_trip = $trip_no + $trip_retrieve_data->total_trip;
                                $trip_update_data = $wpdb->update($trip_table_name, array('id' => $trip_retrieve_data->id, 'total_trip' => $total_trip, 'updated_by' => get_current_user_id(), 'date_updated' => date('Y-m-d H:i:s')), array('id' => $trip_retrieve_data->id));
                            } else {
                                $trip_insert_data = $wpdb->insert(
                                    $trip_table_name,
                                    array(
                                        'user_id' => $user_id,
                                        'gea_subscription_id' => $gea_subscription_id,
                                        'total_trip' => $total_trip,
                                        'created_by' => $user_id,
                                        'date_inserted' => date('Y-m-d H:i:s')
                                    ),
                                    array(
                                        '%d',
                                        '%d',
                                        '%d',
                                        '%d',
                                        '%s'
                                    )
                                );
                            }
    
                            //send reload request to GEA
                            //$url ="http://182.160.116.91/subsrevise.php";
                            //$url = "http://192.168.174.251/subsrevise.php";
                            $url ="http://182.163.122.66/subsrevise.php";
                            //$info_retrieve_data->gea_customer_id.'<br>';
                            $data = array
                            (
                                "ID_PRODUCT_TYPE" => $info_retrieve_data->gea_product_type_id,
                                "ID_PRODUCT" => $info_retrieve_data->gea_product_id,
                                "AMOUNT" => $tellamount,
                                "VAT_AMOUNT" => $tariff_retrieve_data->vat_rate,
                                "ID_CUSTOMER" => $info_retrieve_data->gea_customer_id,
                                "ID_SUBSCRIPTION" => $info_retrieve_data->gea_subscription_id
                            );
    
                            $ch = curl_init($url);
                            curl_setopt($ch, CURLOPT_POST, true);
                            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                            curl_setopt($ch, CURLOPT_HEADER, 0);
                            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                            curl_setopt($ch, CURLOPT_COOKIEJAR, "my_cookies.txt");
                            curl_setopt($ch, CURLOPT_COOKIEFILE, "my_cookies.txt");
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                            curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.3) Gecko/20070309 Firefox/2.0.0.3");
                            $response = curl_exec($ch);
                            curl_close($ch);
    
    
                            $bank_tokens_table_name = $wpdb->prefix . "bank_tokens";
                            $bank_tokens_update_data = $wpdb->update($bank_tokens_table_name, array('process_status'=>'success', 'bank_data'=>$bank_desc,'modified_by' => $user_id, 'modified_at' => date('Y-m-d H:i:s')), array('bank_token'=>$paymentRefId));
                        }
                    }
                    else {
                        //$bank_tokens_table_name = $wpdb->prefix . "bank_tokens";
                        //$bank_tokens_update_data = $wpdb->update($bank_tokens_table_name, array('process_status'=>'failed', 'modified_at' => date('Y-m-d H:i:s')), array('bank_token'=>$paymentRefId));
                    }
                }
                else {
                    //$bank_tokens_table_name = $wpdb->prefix . "bank_tokens";
                    //$bank_tokens_update_data = $wpdb->update($bank_tokens_table_name, array('process_status'=>'failed', 'modified_at' => date('Y-m-d H:i:s')), array('bank_token'=>$paymentRefId));
                }
            }
        }
    }
    
}


// bKash check bill API
add_action( 'rest_api_init', 'bkash_check_bill' );
function bkash_check_bill() {
    register_rest_route( 'bkash', 'checkbill', array(
            'methods' => 'POST',
            'callback' => 'check_bkash_trip_amount',
        )
    );
}
function check_bkash_trip_amount(WP_REST_Request $request_data) {
    global $wpdb;

    $data = $request_data->get_params();

    if(!empty($data["username"]) && !empty($data["password"]) && !empty($data["vehicle_reg_no"]) && !empty($data["mobile_no"]) && (!empty($data["package"]) || !empty($data["trip"]))) {
        $user_table_name = $wpdb->prefix . "users";
        //$logged_user = wp_authenticate( $data["user"], $data["password"] );
        /* WHERE $info_table_name.user_id = ".$logged_user->ID." AND $info_table_name.installed = 1 AND $info_table_name.gea_subscription_id = '".$data["rfid"]."'" );*/
        $bkash_user = 'bkashorionlive';
        $bkash_pass = 'Eh<?2sJqJ806';

        if($bkash_user == $data["username"] && $bkash_pass == $data["password"]){
            $info_table_name = $wpdb->prefix . "users_info";
            $rate_table_name = $wpdb->prefix . "tariff_rates";
            $trip_table_name = $wpdb->prefix . "users_trips";
            $info_retrieve_data = $wpdb->get_row( "SELECT DISTINCT GROUP_CONCAT($info_table_name.vehicle_reg_no) AS vehicle_reg_no, $info_table_name.user_id, $user_table_name.display_name, $info_table_name.gea_subscription_id, $rate_table_name.toll_rates, $trip_table_name.total_trip 
                                            FROM $info_table_name 
                                            LEFT JOIN $user_table_name ON $user_table_name.ID = $info_table_name.user_id
                                            LEFT JOIN $rate_table_name ON $rate_table_name.vehicle_type = $info_table_name.vehicle_type
                                            LEFT JOIN $trip_table_name ON $trip_table_name.user_id = $info_table_name.user_id AND $trip_table_name.gea_subscription_id = $info_table_name.gea_subscription_id
                                            WHERE $info_table_name.installed = 1 AND $user_table_name.user_login = '".$data["mobile_no"]."'
                                             AND SUBSTRING_INDEX($info_table_name.vehicle_reg_no, '-', -1) = '".$data["vehicle_reg_no"]."'" );
            if(!empty($info_retrieve_data->toll_rates)){
                $packages = array('Package_1' => 23, 'Package_2' => 46, 'Package_3' => 69, 'Package_4' => 92, 'Package_5' => 115, 'Package_6' => 138, 'Package_7' => 161, 'Package_8' => 184, 'Package_9' => 207, 'Package_10' => 230, 'Package_11' => 345, 'Package_12' => 460);
                $package_data = array('23' => 2, '46' => 4, '69' => 6, '92' => 8, '115' => 10, '138' => 12, '161' => 14, '184' => 16, '207' => 18, '230' => 20, '345' => 30, '460' => 40);
                if(!empty($data["package"]))
                    $actual_trip_no = $packages[$data["package"]];
                else
                    $actual_trip_no = $data["trip"];

                $packages = '';

                $amount = $info_retrieve_data->toll_rates * $actual_trip_no;
                if(!empty($package_data[$actual_trip_no])){
                    $extra_trip = $package_data[$actual_trip_no];
                    $actual_trip_no = $actual_trip_no + $extra_trip;
                    $packages = 'Package_'.($extra_trip / 2);
                }

                $data = ['code' => '200', 'message' => 'Success', 'name' => $info_retrieve_data->display_name, 'vehicle_reg_no' => $info_retrieve_data->vehicle_reg_no, 'package' => $packages, 'available_trip' => (integer) $info_retrieve_data->total_trip, 'requested_trip' => (integer) $actual_trip_no, 'subscription_id' => $info_retrieve_data->gea_subscription_id, 'amount' => (integer) $amount];
            }
            else
                $data = ['code' => '404', 'message' => 'Data not found'];
        }
        else
            $data = ['code' => '403', 'message' => 'Authentication failed'];
    }
    else{
        $data = ['code' => '406', 'message' => 'Mandatory field missing'];
    }


    echo json_encode($data);
	exit;
}


// bKash bill payment API
add_action( 'rest_api_init', 'bkash_bill_payment' );
function bkash_bill_payment() {
    register_rest_route( 'bkash', 'billpayment', array(
            'methods' => 'POST',
            'callback' => 'insert_bkash_trip_amount',
        )
    );
}
function insert_bkash_trip_amount(WP_REST_Request $request_data) {
    global $wpdb;

    $data = $request_data->get_params();

    if(!empty($data["username"]) && !empty($data["password"]) && !empty($data["subscription_id"]) && !empty($data["amount"]) && !empty($data["trxid"])) {
        $user_table_name = $wpdb->prefix . "users";
        // $logged_user = wp_authenticate( $data["user"], $data["password"] );

        $bkash_user = 'bkashorionlive';
        $bkash_pass = 'Eh<?2sJqJ806';

        if($bkash_user == $data["username"] && $bkash_pass == $data["password"]){
            $info_table_name = $wpdb->prefix . "users_info";
            $rate_table_name = $wpdb->prefix . "tariff_rates";
            $trip_table_name = $wpdb->prefix . "users_trips";
            $info_retrieve_data = $wpdb->get_row( "SELECT DISTINCT GROUP_CONCAT($info_table_name.vehicle_reg_no) AS vehicle_reg_no, $info_table_name.user_id, 
                                                $info_table_name.gea_product_id, $info_table_name.gea_product_type_id, $info_table_name.gea_customer_id,
                                                $user_table_name.display_name, $info_table_name.vehicle_type, $rate_table_name.toll_rates,
                                                $rate_table_name.vat_rate, $trip_table_name.total_trip 
                                            FROM $info_table_name 
                                            LEFT JOIN $user_table_name ON $user_table_name.ID = $info_table_name.user_id
                                            LEFT JOIN $rate_table_name ON $rate_table_name.vehicle_type = $info_table_name.vehicle_type
                                            LEFT JOIN $trip_table_name ON $trip_table_name.user_id = $info_table_name.user_id AND $trip_table_name.gea_subscription_id = $info_table_name.gea_subscription_id
                                            WHERE $info_table_name.installed = 1 AND $info_table_name.gea_subscription_id = '".$data["subscription_id"]."'" );
            if(!empty($info_retrieve_data->toll_rates)){
                if($info_retrieve_data->toll_rates <= $data['amount']){
                    $user_id = $wpdb->escape($info_retrieve_data->user_id);
                    $extra_trip = 0;
                    $vehicle_type = $wpdb->escape($info_retrieve_data->vehicle_type);
                    $gea_subscription_id = $wpdb->escape($data["subscription_id"]);

                    $recharge_histories_table_name = $wpdb->prefix . "users_recharge_histories";
                    $recharge_histories_retrieve_data = $wpdb->get_row("SELECT MAX(id) AS last_id FROM $recharge_histories_table_name");
                    $invoice_id = $user_id.date('Ym').($recharge_histories_retrieve_data->last_id+1);

                    $pgw_transaction_id = $wpdb->escape($data['trxid']);
                    $pgw_payment_id = $wpdb->escape($data['trxid']);
                    $total_cost = $wpdb->escape($data['amount']);
                    $trip_no = floor($total_cost / $info_retrieve_data->toll_rates);

                    $recharge_table_name = $wpdb->prefix . "users_recharge_histories";
                    $recharge_retrieve_data = $wpdb->get_row( "SELECT $recharge_table_name.id FROM $recharge_table_name WHERE $recharge_table_name.pgw_payment_id = '$pgw_payment_id'" );

                    if(empty($recharge_retrieve_data->id)) {
                        $actual_trip_no = $trip_no;
                        $packages = '';
                        $package_data = array('23' => 2, '46' => 4, '69' => 6, '92' => 8, '115' => 10, '138' => 12, '161' => 14, '184' => 16, '207' => 18, '230' => 20, '345' => 30, '460' => 40);
                        //if(!empty($extra_trip)){
                            if(!empty($package_data[$actual_trip_no])){
                                $extra_trip = $package_data[$actual_trip_no];
                                $actual_trip_no = $actual_trip_no + $extra_trip;
                                $packages = 'package_'.($extra_trip / 2);
                            }
                        //}
                        date_default_timezone_set("Asia/Dhaka");
                        $date_inserted = date('Y-m-d H:i:s');
                        $recharge_insert_data = $wpdb->insert(
                            $recharge_table_name,
                            array(
                                'user_id' => $user_id,
                                'gea_subscription_id' => $gea_subscription_id,
                                'invoice_id' => $invoice_id,
                                'pgw_name' => 'bkash',
                                'pgw_transaction_id' => $pgw_transaction_id,
                                'pgw_payment_id' => $pgw_payment_id,
                                'pgw_amount' => $total_cost,
                                'trips' => $actual_trip_no,
                                'packages' => $packages,
                                'platform' => 'bkash_app',
                                'created_by' => $user_id,
                                'date_inserted' => $date_inserted
                            ),
                            array(
                                '%d',
                                '%s',
                                '%s',
                                '%s',
                                '%s',
                                '%s',
                                '%d',
                                '%d',
                                '%s',
                                '%s',
                                '%d',
                                '%s'
                            )
                        );
                        $trip_table_name = $wpdb->prefix . "users_trips";
                        $trip_retrieve_data = $wpdb->get_row("SELECT $trip_table_name.* FROM $trip_table_name WHERE $trip_table_name.user_id = " . $user_id . " AND $trip_table_name.gea_subscription_id=" . $gea_subscription_id);
                        $total_trip = $actual_trip_no;
                        if (!empty($trip_retrieve_data)) {
                            $total_trip = $actual_trip_no + $trip_retrieve_data->total_trip;
                            $trip_update_data = $wpdb->update($trip_table_name, array('id' => $trip_retrieve_data->id, 'total_trip' => $total_trip, 'updated_by' => get_current_user_id(), 'date_updated' => date('Y-m-d H:i:s')), array('id' => $trip_retrieve_data->id));
                        } else {
                            $trip_insert_data = $wpdb->insert(
                                $trip_table_name,
                                array(
                                    'user_id' => $user_id,
                                    'gea_subscription_id' => $gea_subscription_id,
                                    'total_trip' => $total_trip,
                                    'created_by' => $user_id,
                                    'date_inserted' => date('Y-m-d H:i:s')
                                ),
                                array(
                                    '%d',
                                    '%d',
                                    '%d',
                                    '%d',
                                    '%s'
                                )
                            );
                        }

                        $gea_url ="http://182.163.122.66/subsrevise.php";
                        //$info_retrieve_data->gea_customer_id.'<br>';
                        if(!empty($extra_trip) && !empty($info_retrieve_data->toll_rates)){
                            //if(!empty($package_data[$actual_trip_no])){
                                // $extra_trip = $package_data[$actual_trip_no];
                                $total_cost = $total_cost + ($extra_trip * $info_retrieve_data->toll_rates);
                            //}
                        }

                        $gea_data = array
                        (
                            "ID_PRODUCT_TYPE" => $info_retrieve_data->gea_product_type_id,
                            "ID_PRODUCT" => $info_retrieve_data->gea_product_id,
                            "AMOUNT" => $total_cost,
                            "VAT_AMOUNT" => $info_retrieve_data->vat_rate,
                            "ID_CUSTOMER" => $info_retrieve_data->gea_customer_id,
                            "ID_SUBSCRIPTION" => $gea_subscription_id
                        );

                        // echo json_encode($gea_data);

                        $ch = curl_init($gea_url);
                        curl_setopt($ch, CURLOPT_POST, true);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $gea_data);
                        curl_setopt($ch, CURLOPT_HEADER, 0);
                        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                        curl_setopt($ch, CURLOPT_COOKIEJAR, "my_cookies.txt");
                        curl_setopt($ch, CURLOPT_COOKIEFILE, "my_cookies.txt");
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.3) Gecko/20070309 Firefox/2.0.0.3");
                        $response = curl_exec($ch);
                        curl_close($ch);
        
                        $data = ['code' => '200', 'message' => 'Success', 'name' => $info_retrieve_data->display_name, 'vehicle_reg_no' => $info_retrieve_data->vehicle_reg_no, 'available_trip' => (integer) $info_retrieve_data->total_trip, 'purchased_trip' => (integer) $actual_trip_no, 'subscription_id' => $gea_subscription_id, 'amount' => (integer) $total_cost, 'trxid' => $pgw_payment_id, 'paytime' => strtotime($date_inserted)];
                    }
                    else
                        $data = ['code' => '436', 'message' => 'Already paid'];
                }
                else
                    $data = ['code' => '438', 'message' => 'Minimum amount not paid'];
            }
            else
                $data = ['code' => '404', 'message' => 'Data not found'];
        }
        else
            $data = ['code' => '403', 'message' => 'Authentication failed'];
    }
    else{
        $data = ['code' => '406', 'message' => 'Mandatory field missing'];
    }


    echo json_encode($data);
	exit;
}

// bKash Transaction Search API
add_action( 'rest_api_init', 'bkash_transaction_search' );
function bkash_transaction_search() {
    register_rest_route( 'bkash', 'transactionsearchquery', array(
            'methods' => 'POST',
            'callback' => 'bkash_transaction_search_query',
        )
    );
}
function bkash_transaction_search_query(WP_REST_Request $request_data) {
    global $wpdb;

    $data = $request_data->get_params();

    if(!empty($data["username"]) && !empty($data["password"]) && !empty($data["trxid"])) {
        $user_table_name = $wpdb->prefix . "users";
        //$logged_user = wp_authenticate( $data["user"], $data["password"] );

        $bkash_user = 'bkashorionlive';
        $bkash_pass = 'Eh<?2sJqJ806';

        if($bkash_user == $data["username"] && $bkash_pass == $data["password"]){
            $info_table_name = $wpdb->prefix . "users_info";
            $rate_table_name = $wpdb->prefix . "tariff_rates";
            $trip_table_name = $wpdb->prefix . "users_trips";
            $pgw_payment_id = $wpdb->escape($data['trxid']);

            $recharge_table_name = $wpdb->prefix . "users_recharge_histories";
            $recharge_retrieve_data = $wpdb->get_row( "SELECT $recharge_table_name.id, $recharge_table_name.user_id, $recharge_table_name.gea_subscription_id, $recharge_table_name.pgw_amount, $recharge_table_name.trips, $recharge_table_name.date_inserted  FROM $recharge_table_name WHERE $recharge_table_name.pgw_payment_id = '$pgw_payment_id'" );

            if(!empty($recharge_retrieve_data->id)) {
                $info_retrieve_data = $wpdb->get_row( "SELECT DISTINCT GROUP_CONCAT($info_table_name.vehicle_reg_no) AS vehicle_reg_no, $info_table_name.user_id, $user_table_name.display_name, $rate_table_name.toll_rates, $trip_table_name.total_trip 
                                            FROM $info_table_name 
                                            LEFT JOIN $user_table_name ON $user_table_name.ID = $info_table_name.user_id
                                            LEFT JOIN $rate_table_name ON $rate_table_name.vehicle_type = $info_table_name.vehicle_type
                                            LEFT JOIN $trip_table_name ON $trip_table_name.user_id = $info_table_name.user_id AND $trip_table_name.gea_subscription_id = $info_table_name.gea_subscription_id
                                            WHERE $info_table_name.installed = 1 AND $info_table_name.gea_subscription_id = '".$recharge_retrieve_data->gea_subscription_id."'" );
                if(!empty($info_retrieve_data->toll_rates)){
                    $available_trips = $info_retrieve_data->total_trip - $recharge_retrieve_data->trips;
                    $data = ['code' => '200', 'message' => 'Success', 'name' => $info_retrieve_data->display_name, 'vehicle_reg_no' => $info_retrieve_data->vehicle_reg_no, 'available_trip' => (integer) $available_trips, 'purchased_trip' => (integer) $recharge_retrieve_data->trips, 'subscription_id' => $recharge_retrieve_data->gea_subscription_id, 'amount' => (integer) $recharge_retrieve_data->pgw_amount, 'trxid' => $data["trxid"], 'paytime' => strtotime($recharge_retrieve_data->date_inserted)];
                }
                else
                    $data = ['code' => '404', 'message' => 'Data not found'];
            }
            else
                $data = ['code' => '404', 'message' => 'Data not found'];
        }
        else
            $data = ['code' => '403', 'message' => 'Authentication failed'];
    }
    else{
        $data = ['code' => '406', 'message' => 'Mandatory field missing'];
    }


    echo json_encode($data);
	exit;
}


// Nagad check bill API
add_action( 'rest_api_init', 'nagad_check_bill' );
function nagad_check_bill() {
    register_rest_route( 'nagad', 'checkbill', array(
            'methods' => 'POST',
            'callback' => 'check_nagad_trip_amount',
        )
    );
}
function check_nagad_trip_amount(WP_REST_Request $request_data) {
    global $wpdb;

    $data = $request_data->get_params();

    if(!empty($data["username"]) && !empty($data["password"]) && !empty($data["vehicle_reg_no"]) && !empty($data["mobile_no"]) && (!empty($data["package"]) || !empty($data["trip"]))) {
        $user_table_name = $wpdb->prefix . "users";
        //$logged_user = wp_authenticate( $data["user"], $data["password"] );
        /* WHERE $info_table_name.user_id = ".$logged_user->ID." AND $info_table_name.installed = 1 AND $info_table_name.gea_subscription_id = '".$data["rfid"]."'" );*/
        $nagad_user = 'nagadorionlive';
        $nagad_pass = 'DG)?2sJqJ905';

        if($nagad_user == $data["username"] && $nagad_pass == $data["password"]){
            $info_table_name = $wpdb->prefix . "users_info";
            $rate_table_name = $wpdb->prefix . "tariff_rates";
            $trip_table_name = $wpdb->prefix . "users_trips";
            $info_retrieve_data = $wpdb->get_row( "SELECT DISTINCT GROUP_CONCAT($info_table_name.vehicle_reg_no) AS vehicle_reg_no, $info_table_name.user_id, $user_table_name.display_name, $info_table_name.gea_subscription_id, $rate_table_name.toll_rates, $trip_table_name.total_trip 
                                            FROM $info_table_name 
                                            LEFT JOIN $user_table_name ON $user_table_name.ID = $info_table_name.user_id
                                            LEFT JOIN $rate_table_name ON $rate_table_name.vehicle_type = $info_table_name.vehicle_type
                                            LEFT JOIN $trip_table_name ON $trip_table_name.user_id = $info_table_name.user_id AND $trip_table_name.gea_subscription_id = $info_table_name.gea_subscription_id
                                            WHERE $info_table_name.installed = 1 AND $user_table_name.user_login = '".$data["mobile_no"]."'
                                             AND SUBSTRING_INDEX($info_table_name.vehicle_reg_no, '-', -1) = '".$data["vehicle_reg_no"]."'" );
            if(!empty($info_retrieve_data->toll_rates)){
                $packages = array('Package_1' => 23, 'Package_2' => 46, 'Package_3' => 69, 'Package_4' => 92, 'Package_5' => 115, 'Package_6' => 138, 'Package_7' => 161, 'Package_8' => 184, 'Package_9' => 207, 'Package_10' => 230, 'Package_11' => 345, 'Package_12' => 460);
                $package_data = array('23' => 2, '46' => 4, '69' => 6, '92' => 8, '115' => 10, '138' => 12, '161' => 14, '184' => 16, '207' => 18, '230' => 20, '345' => 30, '460' => 40);
                if(!empty($data["package"]))
                    $actual_trip_no = $packages[$data["package"]];
                else
                    $actual_trip_no = $data["trip"];

                $packages = '';

                $amount = $info_retrieve_data->toll_rates * $actual_trip_no;
                if(!empty($package_data[$actual_trip_no])){
                    $extra_trip = $package_data[$actual_trip_no];
                    $actual_trip_no = $actual_trip_no + $extra_trip;
                    $packages = 'Package_'.($extra_trip / 2);
                }

                $data = ['code' => '200', 'message' => 'Success', 'name' => $info_retrieve_data->display_name, 'vehicle_reg_no' => $info_retrieve_data->vehicle_reg_no, 'package' => $packages, 'available_trip' => (integer) $info_retrieve_data->total_trip, 'requested_trip' => (integer) $actual_trip_no, 'subscription_id' => $info_retrieve_data->gea_subscription_id, 'amount' => (integer) $amount];
            }
            else
                $data = ['code' => '404', 'message' => 'Data not found'];
        }
        else
            $data = ['code' => '403', 'message' => 'Authentication failed'];
    }
    else{
        $data = ['code' => '406', 'message' => 'Mandatory field missing'];
    }


    echo json_encode($data);
	exit;
}


// Nagad bill payment API
add_action( 'rest_api_init', 'nagad_bill_payment' );
function nagad_bill_payment() {
    register_rest_route( 'nagad', 'billpayment', array(
            'methods' => 'POST',
            'callback' => 'insert_nagad_trip_amount',
        )
    );
}
function insert_nagad_trip_amount(WP_REST_Request $request_data) {
    global $wpdb;

    $data = $request_data->get_params();

    if(!empty($data["username"]) && !empty($data["password"]) && !empty($data["subscription_id"]) && !empty($data["amount"]) && !empty($data["trxid"])) {
        $user_table_name = $wpdb->prefix . "users";
        // $logged_user = wp_authenticate( $data["user"], $data["password"] );

        $nagad_user = 'nagadorionlive';
        $nagad_pass = 'DG)?2sJqJ905';

        if($nagad_user == $data["username"] && $nagad_pass == $data["password"]){
            $info_table_name = $wpdb->prefix . "users_info";
            $rate_table_name = $wpdb->prefix . "tariff_rates";
            $trip_table_name = $wpdb->prefix . "users_trips";
            $info_retrieve_data = $wpdb->get_row( "SELECT DISTINCT GROUP_CONCAT($info_table_name.vehicle_reg_no) AS vehicle_reg_no, $info_table_name.user_id, 
                                                $info_table_name.gea_product_id, $info_table_name.gea_product_type_id, $info_table_name.gea_customer_id,
                                                $user_table_name.display_name, $info_table_name.vehicle_type, $rate_table_name.toll_rates,
                                                $rate_table_name.vat_rate, $trip_table_name.total_trip 
                                            FROM $info_table_name 
                                            LEFT JOIN $user_table_name ON $user_table_name.ID = $info_table_name.user_id
                                            LEFT JOIN $rate_table_name ON $rate_table_name.vehicle_type = $info_table_name.vehicle_type
                                            LEFT JOIN $trip_table_name ON $trip_table_name.user_id = $info_table_name.user_id AND $trip_table_name.gea_subscription_id = $info_table_name.gea_subscription_id
                                            WHERE $info_table_name.installed = 1 AND $info_table_name.gea_subscription_id = '".$data["subscription_id"]."'" );
            if(!empty($info_retrieve_data->toll_rates)){
                if($info_retrieve_data->toll_rates <= $data['amount']){
                    $user_id = $wpdb->escape($info_retrieve_data->user_id);
                    $extra_trip = 0;
                    $vehicle_type = $wpdb->escape($info_retrieve_data->vehicle_type);
                    $gea_subscription_id = $wpdb->escape($data["subscription_id"]);

                    $recharge_histories_table_name = $wpdb->prefix . "users_recharge_histories";
                    $recharge_histories_retrieve_data = $wpdb->get_row("SELECT MAX(id) AS last_id FROM $recharge_histories_table_name");
                    $invoice_id = $user_id.date('Ym').($recharge_histories_retrieve_data->last_id+1);

                    $pgw_transaction_id = $wpdb->escape($data['trxid']);
                    $pgw_payment_id = $wpdb->escape($data['trxid']);
                    $total_cost = $wpdb->escape($data['amount']);
                    $trip_no = floor($total_cost / $info_retrieve_data->toll_rates);

                    $recharge_table_name = $wpdb->prefix . "users_recharge_histories";
                    $recharge_retrieve_data = $wpdb->get_row( "SELECT $recharge_table_name.id FROM $recharge_table_name WHERE $recharge_table_name.pgw_payment_id = '$pgw_payment_id'" );

                    if(empty($recharge_retrieve_data->id)) {
                        $actual_trip_no = $trip_no;
                        $packages = '';
                        $package_data = array('23' => 2, '46' => 4, '69' => 6, '92' => 8, '115' => 10, '138' => 12, '161' => 14, '184' => 16, '207' => 18, '230' => 20, '345' => 30, '460' => 40);
                        //if(!empty($extra_trip)){
                            if(!empty($package_data[$actual_trip_no])){
                                $extra_trip = $package_data[$actual_trip_no];
                                $actual_trip_no = $actual_trip_no + $extra_trip;
                                $packages = 'package_'.($extra_trip / 2);
                            }
                        //}
                        date_default_timezone_set("Asia/Dhaka");
                        $date_inserted = date('Y-m-d H:i:s');
                        $recharge_insert_data = $wpdb->insert(
                            $recharge_table_name,
                            array(
                                'user_id' => $user_id,
                                'gea_subscription_id' => $gea_subscription_id,
                                'invoice_id' => $invoice_id,
                                'pgw_name' => 'nagad',
                                'pgw_transaction_id' => $pgw_transaction_id,
                                'pgw_payment_id' => $pgw_payment_id,
                                'pgw_amount' => $total_cost,
                                'trips' => $actual_trip_no,
                                'packages' => $packages,
                                'platform' => 'nagad_app',
                                'created_by' => $user_id,
                                'date_inserted' => $date_inserted
                            ),
                            array(
                                '%d',
                                '%s',
                                '%s',
                                '%s',
                                '%s',
                                '%s',
                                '%d',
                                '%d',
                                '%s',
                                '%s',
                                '%d',
                                '%s'
                            )
                        );
                        $trip_table_name = $wpdb->prefix . "users_trips";
                        $trip_retrieve_data = $wpdb->get_row("SELECT $trip_table_name.* FROM $trip_table_name WHERE $trip_table_name.user_id = " . $user_id . " AND $trip_table_name.gea_subscription_id=" . $gea_subscription_id);
                        $total_trip = $actual_trip_no;
                        if (!empty($trip_retrieve_data)) {
                            $total_trip = $actual_trip_no + $trip_retrieve_data->total_trip;
                            $trip_update_data = $wpdb->update($trip_table_name, array('id' => $trip_retrieve_data->id, 'total_trip' => $total_trip, 'updated_by' => get_current_user_id(), 'date_updated' => date('Y-m-d H:i:s')), array('id' => $trip_retrieve_data->id));
                        } else {
                            $trip_insert_data = $wpdb->insert(
                                $trip_table_name,
                                array(
                                    'user_id' => $user_id,
                                    'gea_subscription_id' => $gea_subscription_id,
                                    'total_trip' => $total_trip,
                                    'created_by' => $user_id,
                                    'date_inserted' => date('Y-m-d H:i:s')
                                ),
                                array(
                                    '%d',
                                    '%d',
                                    '%d',
                                    '%d',
                                    '%s'
                                )
                            );
                        }

                        $gea_url ="http://182.163.122.66/subsrevise.php";
                        //$info_retrieve_data->gea_customer_id.'<br>';
                        if(!empty($extra_trip) && !empty($info_retrieve_data->toll_rates)){
                            //if(!empty($package_data[$actual_trip_no])){
                                // $extra_trip = $package_data[$actual_trip_no];
                                $total_cost = $total_cost + ($extra_trip * $info_retrieve_data->toll_rates);
                            //}
                        }

                        $gea_data = array
                        (
                            "ID_PRODUCT_TYPE" => $info_retrieve_data->gea_product_type_id,
                            "ID_PRODUCT" => $info_retrieve_data->gea_product_id,
                            "AMOUNT" => $total_cost,
                            "VAT_AMOUNT" => $info_retrieve_data->vat_rate,
                            "ID_CUSTOMER" => $info_retrieve_data->gea_customer_id,
                            "ID_SUBSCRIPTION" => $gea_subscription_id
                        );

                        // echo json_encode($gea_data);

                        $ch = curl_init($gea_url);
                        curl_setopt($ch, CURLOPT_POST, true);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $gea_data);
                        curl_setopt($ch, CURLOPT_HEADER, 0);
                        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                        curl_setopt($ch, CURLOPT_COOKIEJAR, "my_cookies.txt");
                        curl_setopt($ch, CURLOPT_COOKIEFILE, "my_cookies.txt");
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.3) Gecko/20070309 Firefox/2.0.0.3");
                        $response = curl_exec($ch);
                        curl_close($ch);
        
                        $data = ['code' => '200', 'message' => 'Success', 'name' => $info_retrieve_data->display_name, 'vehicle_reg_no' => $info_retrieve_data->vehicle_reg_no, 'available_trip' => (integer) $info_retrieve_data->total_trip, 'purchased_trip' => (integer) $actual_trip_no, 'subscription_id' => $gea_subscription_id, 'amount' => (integer) $total_cost, 'trxid' => $pgw_payment_id, 'paytime' => strtotime($date_inserted)];
                    }
                    else
                        $data = ['code' => '436', 'message' => 'Already paid'];
                }
                else
                    $data = ['code' => '438', 'message' => 'Minimum amount not paid'];
            }
            else
                $data = ['code' => '404', 'message' => 'Data not found'];
        }
        else
            $data = ['code' => '403', 'message' => 'Authentication failed'];
    }
    else{
        $data = ['code' => '406', 'message' => 'Mandatory field missing'];
    }


    echo json_encode($data);
	exit;
}

// Nagad Transaction Search API
add_action( 'rest_api_init', 'nagad_transaction_search' );
function nagad_transaction_search() {
    register_rest_route( 'nagad', 'transactionsearchquery', array(
            'methods' => 'POST',
            'callback' => 'nagad_transaction_search_query',
        )
    );
}
function nagad_transaction_search_query(WP_REST_Request $request_data) {
    global $wpdb;

    $data = $request_data->get_params();

    if(!empty($data["username"]) && !empty($data["password"]) && !empty($data["trxid"])) {
        $user_table_name = $wpdb->prefix . "users";
        //$logged_user = wp_authenticate( $data["user"], $data["password"] );

        $nagad_user = 'nagadorionlive';
        $nagad_pass = 'DG)?2sJqJ905';

        if($nagad_user == $data["username"] && $nagad_pass == $data["password"]){
            $info_table_name = $wpdb->prefix . "users_info";
            $rate_table_name = $wpdb->prefix . "tariff_rates";
            $trip_table_name = $wpdb->prefix . "users_trips";
            $pgw_payment_id = $wpdb->escape($data['trxid']);

            $recharge_table_name = $wpdb->prefix . "users_recharge_histories";
            $recharge_retrieve_data = $wpdb->get_row( "SELECT $recharge_table_name.id, $recharge_table_name.user_id, $recharge_table_name.gea_subscription_id, $recharge_table_name.pgw_amount, $recharge_table_name.trips, $recharge_table_name.date_inserted  FROM $recharge_table_name WHERE $recharge_table_name.pgw_payment_id = '$pgw_payment_id'" );

            if(!empty($recharge_retrieve_data->id)) {
                $info_retrieve_data = $wpdb->get_row( "SELECT DISTINCT GROUP_CONCAT($info_table_name.vehicle_reg_no) AS vehicle_reg_no, $info_table_name.user_id, $user_table_name.display_name, $rate_table_name.toll_rates, $trip_table_name.total_trip 
                                            FROM $info_table_name 
                                            LEFT JOIN $user_table_name ON $user_table_name.ID = $info_table_name.user_id
                                            LEFT JOIN $rate_table_name ON $rate_table_name.vehicle_type = $info_table_name.vehicle_type
                                            LEFT JOIN $trip_table_name ON $trip_table_name.user_id = $info_table_name.user_id AND $trip_table_name.gea_subscription_id = $info_table_name.gea_subscription_id
                                            WHERE $info_table_name.installed = 1 AND $info_table_name.gea_subscription_id = '".$recharge_retrieve_data->gea_subscription_id."'" );
                if(!empty($info_retrieve_data->toll_rates)){
                    $available_trips = $info_retrieve_data->total_trip - $recharge_retrieve_data->trips;
                    $data = ['code' => '200', 'message' => 'Success', 'name' => $info_retrieve_data->display_name, 'vehicle_reg_no' => $info_retrieve_data->vehicle_reg_no, 'available_trip' => (integer) $available_trips, 'purchased_trip' => (integer) $recharge_retrieve_data->trips, 'subscription_id' => $recharge_retrieve_data->gea_subscription_id, 'amount' => (integer) $recharge_retrieve_data->pgw_amount, 'trxid' => $data["trxid"], 'paytime' => strtotime($recharge_retrieve_data->date_inserted)];
                }
                else
                    $data = ['code' => '404', 'message' => 'Data not found'];
            }
            else
                $data = ['code' => '404', 'message' => 'Data not found'];
        }
        else
            $data = ['code' => '403', 'message' => 'Authentication failed'];
    }
    else{
        $data = ['code' => '406', 'message' => 'Mandatory field missing'];
    }


    echo json_encode($data);
	exit;
}


// van user info API
add_action( 'rest_api_init', 'van_user_infos' );
function van_user_infos() {
    register_rest_route( 'van', 'userinfo', array(
            'methods' => 'POST',
            'callback' => 'get_van_user_infos',
        )
    );
}
function get_van_user_infos(WP_REST_Request $request_data) {
    global $wpdb;
    ini_set('max_execution_time', -1);
    $post_data = $request_data->get_params();

    if(!empty($post_data["username"]) && !empty($post_data["password"])) {
        $user_table_name = $wpdb->prefix . "users";
        $van_user = 'vanorionlive';
        $van_pass = 'kc%n#c]0c>oe';

        if($van_user == $post_data["username"] && $van_pass == $post_data["password"]){
            $info_table_name = $wpdb->prefix . "users_info";
            $rate_table_name = $wpdb->prefix . "tariff_rates";
            $trip_table_name = $wpdb->prefix . "users_trips";
            $query = "SELECT $user_table_name.user_login, $user_table_name.display_name, $info_table_name.nid, $info_table_name.gea_product_type_id, 
                                            $info_table_name.gea_product_id, $info_table_name.gea_subscription_id, $info_table_name.gea_customer_id, 
                                            $info_table_name.vehicle_reg_no, $info_table_name.rfid_sticker_no, $info_table_name.vehicle_type, IFNULL($trip_table_name.total_trip, 0) AS total_trip,
                                            IFNULL($trip_table_name.date_updated, $info_table_name.date_inserted) AS date_updated
                        FROM $info_table_name 
                        LEFT JOIN $user_table_name ON $user_table_name.ID = $info_table_name.user_id
                        LEFT JOIN $trip_table_name ON $trip_table_name.user_id = $info_table_name.user_id AND $trip_table_name.gea_subscription_id = $info_table_name.gea_subscription_id
                        WHERE $info_table_name.installed = 1";
            if(!empty($post_data["mobile_no"]))
                $query.= " AND $user_table_name.user_login = '".$post_data["mobile_no"]."'";

            if(!empty($post_data["trip_modification_date"]))
                $query.= " AND DATE_FORMAT($trip_table_name.date_updated, '%Y-%m-%d') >= '".date('Y-m-d', strtotime($post_data["trip_modification_date"]))."'";

            $info_retrieve_data = $wpdb->get_results( $query );
            if(!empty($info_retrieve_data)){
                $data = ['code' => '200', 'message' => 'Success'];
                foreach($info_retrieve_data as $thisData){
                    $data['data'][] = ['name' => $thisData->display_name, 'mobile_no' => $thisData->user_login, 'nid' => $thisData->nid, 'customer_id' => $thisData->gea_customer_id, 'subscription_id' => $thisData->gea_subscription_id, 'vehicle_type' => $thisData->vehicle_type, 'vehicle_reg_no' => $thisData->vehicle_reg_no, 'rfid' => $thisData->rfid_sticker_no, 'available_trip' => $thisData->total_trip, 'trip_modification_date' => $thisData->date_updated, 'card_type' => 3, 'card_status' => 1];
                }
            }
            else
                $data = ['code' => '404', 'message' => 'Data not found'];
        }
        else
            $data = ['code' => '403', 'message' => 'Authentication failed'];
    }
    else{
        $data = ['code' => '406', 'message' => 'Mandatory field missing'];
    }


    echo json_encode($data);
	exit;
}


// van trip deduction API
add_action( 'rest_api_init', 'van_trip_deduction' );
function van_trip_deduction() {
    register_rest_route( 'van', 'tripdeduction', array(
            'methods' => 'POST',
            'callback' => 'insert_van_trip_deduction',
        )
    );
}

function insert_van_trip_deduction(WP_REST_Request $request_data)
{
    global $wpdb;

    $post_data = $request_data->get_params();
    $van_tarns_id = $wpdb->escape($post_data["transaction_ref_id"]);

    $trans_exists = $wpdb->get_results("SELECT COUNT(*) as num_rows FROM " . $wpdb->prefix . "api_logs  WHERE  transaction_id = '" . $van_tarns_id . "' AND status_code != '200' "); 

    if( $trans_exists[0]->num_rows > 10 ){
        $status_code = '404';
        $status_messsage = 'Something went wrong!';
        $return_string = array();
        $data = ['code' => $status_code, 'message' => $status_messsage];
        echo json_encode($data);
        exit;
    }

    // start write the log
    date_default_timezone_set("Asia/Dhaka");
    $log = [
        'time' => date('Y-m-d H:i:s'),
        'method' => $request_data->get_method(),
        'route' => $request_data->get_route(),
        'params' => $request_data->get_params(),
    ];
    //$log_file_name = WP_CONTENT_DIR . '/uploads/van_log/' . date('Y_m_d')  . '/' . $post_data["subscription_id"] . '.txt';
    $log_path = WP_CONTENT_DIR . '/uploads/van_log/' . date('Y_m_d')  . '/';
    $log_file_name =  $log_path . $post_data["subscription_id"] . '.txt';
    if (!file_exists($log_path)) {
        @mkdir($log_path, 0777, true);
    }
    if (!file_exists($log_file_name)) {
        fopen($log_file_name, "w");
    }
    file_put_contents($log_file_name, print_r($log, true), FILE_APPEND);
    // end write the log
    // $van_tarns_id = $wpdb->escape($post_data["transaction_ref_id"]);
    // $van_tarns_id = $wpdb->escape($post_data["transaction_ref_id"]);

    // $trans_exists = $wpdb->get_results("SELECT COUNT(*) as num_rows FROM " . $wpdb->prefix . "api_logs  WHERE  transaction_id = '" . $van_tarns_id . "' AND status_code != '200' "); 

    //if( $trans_exists[0]->num_rows > 10 ){
    //    $status_code = '404';
    //    $status_messsage = 'Something went wrong!';
    //    $return_string = array();
    //    $data = ['code' => $status_code, 'message' => $status_messsage];
    //    echo json_encode($data);
    //    exit;
    //}

    //$transaction_ref_id = "021915202601161806370727";
    $transaction_ref_id = $van_tarns_id;
    $transaction_datetime = substr($transaction_ref_id, 6);
    $date_parts = date_parse_from_format("YmdHisu", $transaction_datetime);
    $transaction_timestamp = mktime(
        $date_parts['hour'],
        $date_parts['minute'],
        $date_parts['second'],
        $date_parts['month'],
        $date_parts['day'],
        $date_parts['year']
    );
    $difference_in_seconds = time() - $transaction_timestamp;
    //$difference_in_minutes = ($difference_in_seconds / 60) % 60;
    $difference_in_hours = round($difference_in_seconds / (60 * 60));
    
    //skip if 48 hours old transaction
    $allow_trans_within_hours = 48;

    if (!empty($post_data["username"]) && !empty($post_data["password"]) && !empty($post_data["subscription_id"]) && !empty($post_data["no_of_deducted_trip"]) && !empty($post_data["transaction_ref_id"])) {
        $user_table_name = $wpdb->prefix . "users";

        $van_user = 'vanorionlive';
        $van_pass = 'kc%n#c]0c>oe';

        if ($van_user == $post_data["username"] && $van_pass == $post_data["password"]) {
            $api_log_table_name = $wpdb->prefix . "api_logs";
            $api_log_retrieve_data = $wpdb->get_row("SELECT id FROM $api_log_table_name WHERE transaction_id = '" . $van_tarns_id . "' AND status_code='200' LIMIT 1");
            if (empty($api_log_retrieve_data->id)) {
                if( $difference_in_hours < $allow_trans_within_hours){
                    $info_table_name = $wpdb->prefix . "users_info";
                    $rate_table_name = $wpdb->prefix . "tariff_rates";
                    $trip_table_name = $wpdb->prefix . "users_trips";
                    $info_retrieve_data = $wpdb->get_row("SELECT DISTINCT GROUP_CONCAT($info_table_name.vehicle_reg_no) AS vehicle_reg_no, $info_table_name.user_id, 
                                                    $user_table_name.display_name, $user_table_name.user_login, $info_table_name.vehicle_type,
                                                    $info_table_name.gea_product_id, $info_table_name.gea_product_type_id, $info_table_name.gea_customer_id,
                                                    $user_table_name.display_name, $info_table_name.vehicle_type, $rate_table_name.toll_rates,
                                                    $rate_table_name.vat_rate, $trip_table_name.total_trip 
                                                FROM $info_table_name 
                                                LEFT JOIN $user_table_name ON $user_table_name.ID = $info_table_name.user_id
                                                LEFT JOIN $rate_table_name ON $rate_table_name.vehicle_type = $info_table_name.vehicle_type
                                                LEFT JOIN $trip_table_name ON $trip_table_name.user_id = $info_table_name.user_id AND $trip_table_name.gea_subscription_id = $info_table_name.gea_subscription_id
                                                WHERE $info_table_name.installed = 1 AND $info_table_name.gea_subscription_id = '" . $post_data["subscription_id"] . "'");
                    if (!empty($info_retrieve_data->toll_rates)) {
                        $user_id = $wpdb->escape($info_retrieve_data->user_id);
                        $vehicle_type = $wpdb->escape($info_retrieve_data->vehicle_type);
                        $gea_subscription_id = $wpdb->escape($post_data["subscription_id"]);
                        // $total_cost = $info_retrieve_data->toll_rates * $post_data["no_of_deducted_trip"];
                        $trip_no = $wpdb->escape($post_data["no_of_deducted_trip"]);

                        $trip_table_name = $wpdb->prefix . "users_trips";
                        $trip_retrieve_data = $wpdb->get_row("SELECT $trip_table_name.* FROM $trip_table_name WHERE $trip_table_name.user_id = " . $user_id . " AND $trip_table_name.gea_subscription_id=" . $gea_subscription_id);

                        if (!empty($trip_retrieve_data->total_trip)) {
                            $total_trip = $trip_retrieve_data->total_trip - $trip_no;
                            date_default_timezone_set("Asia/Dhaka");
                            // $total_trip = $trip_no;
                            $trip_update_data = $wpdb->update($trip_table_name, array('id' => $trip_retrieve_data->id, 'total_trip' => $total_trip, 'updated_by' => get_current_user_id(), 'date_updated' => date('Y-m-d H:i:s')), array('id' => $trip_retrieve_data->id));

                            $total_cost = $info_retrieve_data->toll_rates * $post_data["no_of_deducted_trip"];

                            $url = "http://182.163.122.66/subsrevise.php";

                            $gea_data = array
                            (
                                "ID_PRODUCT_TYPE" => $info_retrieve_data->gea_product_type_id,
                                "ID_PRODUCT" => $info_retrieve_data->gea_product_id,
                                "AMOUNT" => $total_cost * (-1),
                                "VAT_AMOUNT" => $info_retrieve_data->vat_rate,
                                "ID_CUSTOMER" => $info_retrieve_data->gea_customer_id,
                                "ID_SUBSCRIPTION" => $post_data["subscription_id"]
                            );

                            $ch = curl_init($url);
                            curl_setopt($ch, CURLOPT_POST, true);
                            curl_setopt($ch, CURLOPT_POSTFIELDS, $gea_data);
                            curl_setopt($ch, CURLOPT_HEADER, 0);
                            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                            curl_setopt($ch, CURLOPT_COOKIEJAR, "my_cookies.txt");
                            curl_setopt($ch, CURLOPT_COOKIEFILE, "my_cookies.txt");
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                            curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.3) Gecko/20070309 Firefox/2.0.0.3");
                            $response = curl_exec($ch);
                            curl_close($ch);

                            // SMS
                            $csmsId = uniqid(); // csms id must be unique
                            $sms_message = 'Dear Customer, Vehicle No. ' . $post_data["vehicle_reg_no"] . ' has passed through    lane ' . $post_data["lane_no"] . ' at ' . date('H:i:s', $transaction_timestamp) . ' on ' . date('d.m.Y') . '. Current trip:' . $total_trip . ' Thank you for using JGFP.';
                            // $mobile_no = '01713336322';
                            $mobile_no = !empty($info_retrieve_data->user_login) ? $info_retrieve_data->user_login : '01713336322';
                            smsmmhfOrion($mobile_no, $sms_message, $csmsId);

                            $status_code = '200';
                            $status_messsage = 'Success';
                            $return_string = array
                            (
                                "name" => $info_retrieve_data->display_name,
                                "vehicle_reg_no" => $info_retrieve_data->vehicle_reg_no,
                                "available_trip" => $total_trip,
                                "subscription_id" => $post_data["subscription_id"]
                            );

                            $data = ['code' => '200', 'message' => 'Success', 'name' => $info_retrieve_data->display_name, 'vehicle_reg_no' => $info_retrieve_data->vehicle_reg_no, 'available_trip' => (integer) $total_trip, 'subscription_id' => $post_data["subscription_id"]];
                        } else {
                            $status_code = '404';
                            $status_messsage = 'Data not found';
                            $return_string = array();
                            $data = ['code' => '404', 'message' => 'Data not found'];
                        }
                    } else {
                        $status_code = '404';
                        $status_messsage = 'Data not found';
                        $return_string = array();
                        $data = ['code' => '404', 'message' => 'Data not found'];
                    }
                }
                else{
                    $status_code = '405';
                    $status_messsage = 'Transaction older than '.$allow_trans_within_hours.' hours';
                    $return_string = array();
                    $data = ['code' => $status_code, 'message' => $status_messsage];
                }
            } else {
                $status_code = '405';
                $status_messsage = 'Duplicate transaction reference id';
                $return_string = array();
                $data = ['code' => '405', 'message' => 'Duplicate transaction reference id'];
            }
        } else {
            $status_code = '403';
            $status_messsage = 'Authentication failed';
            $return_string = array();
            $data = ['code' => '403', 'message' => 'Authentication failed'];
        }
    } else {
        $status_code = '406';
        $status_messsage = 'Mandatory field missing';
        $return_string = array();
        $data = ['code' => '406', 'message' => 'Mandatory field missing'];
    }


    // keep log in db
    date_default_timezone_set("Asia/Dhaka");
    $date_inserted = date('Y-m-d H:i:s');
    $api_log_table_name = $wpdb->prefix . "api_logs";
    $api_log_insert_data = $wpdb->insert(
        $api_log_table_name,
        array(
            'api_for' => 'vaan',
            'method' => $request_data->get_method(),
            'route' => $request_data->get_route(),
            'params' => json_encode($request_data->get_params()),
            'transaction_id' => $van_tarns_id,
            'status_code' => $status_code,
            'status_messsage' => $status_messsage,
            'return_string' => json_encode($return_string),
            'created_at' => $date_inserted
        ),
        array(
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s'
        )
    );

    // start write the log
    $log = [
        'status_code' => $status_code,
        'status_messsage' => $status_messsage,
        'return_string' => json_encode($return_string),
    ];
    file_put_contents($log_file_name, print_r($log, true), FILE_APPEND);
    // end write the log

    echo json_encode($data);
    exit;
}

function insert_van_trip_deduction_backup(WP_REST_Request $request_data) {
    global $wpdb;

    $post_data = $request_data->get_params();

    // start write the log
    date_default_timezone_set("Asia/Dhaka");
    $log = [
        'time'   => date('Y-m-d H:i:s'),
        'method' => $request_data->get_method(),
        'route'  => $request_data->get_route(),
        'params' => $request_data->get_params(),
    ];
    $log_file_name = WP_CONTENT_DIR . '/uploads/van_log/'.$post_data["subscription_id"].'.txt';
    if(!file_exists($log_file_name)){
        fopen($log_file_name, "w");
    }
    file_put_contents($log_file_name, print_r($log, true), FILE_APPEND);
    // end write the log
    $van_tarns_id = $wpdb->escape($post_data["transaction_ref_id"]);

    if(!empty($post_data["username"]) && !empty($post_data["password"]) && !empty($post_data["subscription_id"]) && !empty($post_data["no_of_deducted_trip"]) && !empty($post_data["transaction_ref_id"])) {
        $user_table_name = $wpdb->prefix . "users";

        $van_user = 'vanorionlive';
        $van_pass = 'kc%n#c]0c>oe';

        if($van_user == $post_data["username"] && $van_pass == $post_data["password"]){
            $api_log_table_name = $wpdb->prefix . "api_logs";
            $api_log_retrieve_data = $wpdb->get_row( "SELECT id FROM $api_log_table_name WHERE transaction_id = '".$van_tarns_id."' LIMIT 1");
            if(empty($api_log_retrieve_data->id)){
	        $info_table_name = $wpdb->prefix . "users_info";
        	$rate_table_name = $wpdb->prefix . "tariff_rates";
            	$trip_table_name = $wpdb->prefix . "users_trips";
            	$info_retrieve_data = $wpdb->get_row( "SELECT DISTINCT GROUP_CONCAT($info_table_name.vehicle_reg_no) AS vehicle_reg_no, $info_table_name.user_id, 
                                                $user_table_name.display_name, $user_table_name.user_login, $info_table_name.vehicle_type,
                                                $info_table_name.gea_product_id, $info_table_name.gea_product_type_id, $info_table_name.gea_customer_id,
                                                $user_table_name.display_name, $info_table_name.vehicle_type, $rate_table_name.toll_rates,
                                                $rate_table_name.vat_rate, $trip_table_name.total_trip 
                                            FROM $info_table_name 
                                            LEFT JOIN $user_table_name ON $user_table_name.ID = $info_table_name.user_id
                                            LEFT JOIN $rate_table_name ON $rate_table_name.vehicle_type = $info_table_name.vehicle_type
                                            LEFT JOIN $trip_table_name ON $trip_table_name.user_id = $info_table_name.user_id AND $trip_table_name.gea_subscription_id = $info_table_name.gea_subscription_id
                                            WHERE $info_table_name.installed = 1 AND $info_table_name.gea_subscription_id = '".$post_data["subscription_id"]."'" );
            	if(!empty($info_retrieve_data->toll_rates)){
                	$user_id = $wpdb->escape($info_retrieve_data->user_id);
                	$vehicle_type = $wpdb->escape($info_retrieve_data->vehicle_type);
                	$gea_subscription_id = $wpdb->escape($post_data["subscription_id"]);
                	// $total_cost = $info_retrieve_data->toll_rates * $post_data["no_of_deducted_trip"];
                	$trip_no = $wpdb->escape($post_data["no_of_deducted_trip"]);

                	$trip_table_name = $wpdb->prefix . "users_trips";
                	$trip_retrieve_data = $wpdb->get_row("SELECT $trip_table_name.* FROM $trip_table_name WHERE $trip_table_name.user_id = " . $user_id . " AND $trip_table_name.gea_subscription_id=" . $gea_subscription_id);

                	if (!empty($trip_retrieve_data->total_trip)) {
                    		$total_trip = $trip_retrieve_data->total_trip - $trip_no;
                    		date_default_timezone_set("Asia/Dhaka");
                    		// $total_trip = $trip_no;
                    		$trip_update_data = $wpdb->update($trip_table_name, array('id' => $trip_retrieve_data->id, 'total_trip' => $total_trip, 'date_updated' => date('Y-m-d H:i:s')), array('id' => $trip_retrieve_data->id));
                    
                    		$total_cost = $info_retrieve_data->toll_rates * $post_data["no_of_deducted_trip"];
                    
                    		$url ="http://182.163.122.66/subsrevise.php";

                    		$gea_data = array
                    			(
                        			"ID_PRODUCT_TYPE" => $info_retrieve_data->gea_product_type_id,
                        			"ID_PRODUCT" => $info_retrieve_data->gea_product_id,
                        			"AMOUNT" => $total_cost * (-1),
                        			"VAT_AMOUNT" => $info_retrieve_data->vat_rate,
                        			"ID_CUSTOMER" => $info_retrieve_data->gea_customer_id,
                        			"ID_SUBSCRIPTION" => $post_data["subscription_id"]
                    			);
        
                    		$ch = curl_init($url);
                    		curl_setopt($ch, CURLOPT_POST, true);
                    		curl_setopt($ch, CURLOPT_POSTFIELDS, $gea_data);
                    		curl_setopt($ch, CURLOPT_HEADER, 0);
                    		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                    		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    		curl_setopt($ch, CURLOPT_COOKIEJAR, "my_cookies.txt");
                    		curl_setopt($ch, CURLOPT_COOKIEFILE, "my_cookies.txt");
                    		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.3) Gecko/20070309 Firefox/2.0.0.3");
                    		$response = curl_exec($ch);
                    		curl_close($ch);

                    		// SMS
                    		$csmsId = uniqid(); // csms id must be unique
                    		$sms_message = 'Dear Customer, Vehicle No. '.$post_data["vehicle_reg_no"].' has passed through    lane '.$post_data["lane_no"].' at '. date('H:i:s') . ' on '. date('d.m.Y') .'. Current trip:'.$total_trip .' Thank you for using JGFP.';
                    		// $mobile_no = '01713336322';
                    		$mobile_no = !empty($info_retrieve_data->user_login)?$info_retrieve_data->user_login:'01713336322';
                    		smsmmhfOrion($mobile_no, $sms_message, $csmsId);
                        
				            $status_code = '200';
                        	$status_messsage = 'Success';
                        	$return_string = array
                                        (
                                            "name" => $info_retrieve_data->display_name,
                                            "vehicle_reg_no" => $info_retrieve_data->vehicle_reg_no,
                                            "available_trip" => $total_trip,
                                            "subscription_id" => $post_data["subscription_id"]
                                        );

                    		$data = ['code' => '200', 'message' => 'Success', 'name' => $info_retrieve_data->display_name, 'vehicle_reg_no' => $info_retrieve_data->vehicle_reg_no, 'available_trip' => (integer) $total_trip, 'subscription_id' => $post_data["subscription_id"]];
                	}
               		else{
                	        $status_code = '404';
        	                $status_messsage = 'Data not found';
	                        $return_string = array();
                    		$data = ['code' => '404', 'message' => 'Data not found'];
        		}    
		}
            	else{
                    $status_code = '404';
                    $status_messsage = 'Data not found';
                    $return_string = array();
	            $data = ['code' => '404', 'message' => 'Data not found'];
		}
		}
		else{
	                $status_code = '202';
	                $status_messsage = 'Duplicate transaction reference id';
	                $return_string = array();
	                $data = ['code' => '404', 'message' => 'Duplicate transaction reference id'];
		}
        }
        else{
            $status_code = '403';
            $status_messsage = 'Authentication failed';
            $return_string = array();
            $data = ['code' => '403', 'message' => 'Authentication failed'];
	}
    }
    else{
        $status_code = '406';
        $status_messsage = 'Mandatory field missing';
        $return_string = array();
        $data = ['code' => '406', 'message' => 'Mandatory field missing'];
    }


    // keep log in db
    date_default_timezone_set("Asia/Dhaka");
    $date_inserted = date('Y-m-d H:i:s');
    $api_log_table_name = $wpdb->prefix . "api_logs";
    $api_log_insert_data = $wpdb->insert(
        $api_log_table_name,
        array(
            'api_for' => 'vaan',
            'method' => $request_data->get_method(),
            'route' => $request_data->get_route(),
            'params' => json_encode($request_data->get_params()),
            'transaction_id' => $van_tarns_id,
            'status_code' => $status_code,
            'status_messsage' => $status_messsage,
            'return_string' => json_encode($return_string),
            'created_at' => $date_inserted
        ),
        array(
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s'
        )
    );


    echo json_encode($data);
	exit;
}
