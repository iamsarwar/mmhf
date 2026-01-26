<?php

function enfold_theme_enqueue_styles() {

    $parent_style = 'enfold-style';

    wp_enqueue_style( $parent_style, get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array( $parent_style ),
        wp_get_theme()->get('Version')
    );
}
add_action( 'wp_enqueue_scripts', 'enfold_theme_enqueue_styles' );





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


// get mobile pin
add_action( 'wp_ajax_nopriv_get_mobile_pin', 'get_mobile_pin' );
add_action( 'wp_ajax_get_mobile_pin', 'get_mobile_pin' );

function get_mobile_pin()
{
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
            $url = 'https://api.mobireach.com.bd/SendTextMessage?Username=orion_pharma&Password=Orion@54321&From=ORION&To='.$_POST['mobile_no'].'&Message='.urlencode($message);

            //send SMS
            $curl = curl_init();

            $timeout = 5;
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeout);
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);

			
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
function bkash_token(){
	session_start();
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
		$id_token = encrypt_decrypt('encrypt',$obj->{'id_token'},'maskPassOrionToll');
		$_SESSION['bkash_tokens'] = $id_token;
	}
	curl_close($url);
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
        $info_retrieve_data = $wpdb->get_results( "SELECT DISTINCT $info_table_name.gea_subscription_id, $info_table_name.vehicle_type, $rate_table_name.toll_rates FROM $info_table_name LEFT JOIN $rate_table_name ON $rate_table_name.vehicle_type = $info_table_name.vehicle_type WHERE $info_table_name.user_id = ".get_current_user_id()." AND $info_table_name.installed = 1" );
        $grand_total_trip = 0;
        if(!empty($info_retrieve_data)){
            $is_corporate = '';
            $trip_table_name = $wpdb->prefix . "users_trips";
            $trip_retrieve_data = $wpdb->get_row("SELECT SUM($trip_table_name.total_trip) AS total_trip FROM $trip_table_name WHERE $trip_table_name.user_id = " . get_current_user_id());
            if (!empty($trip_retrieve_data->total_trip)) {
                $total_trip = $trip_retrieve_data->total_trip;
                $grand_total_trip = $grand_total_trip + $total_trip;
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
            </script>

            <?php
            echo '<table width="100%" class="display" id="vehicle_table">
                <thead>
                    <tr>
                        <th>Subscription ID</th>
                        <th>Type</th>
                        <th>Reg. No`s</th>
                        <th>Total trip</th>
                    </tr>
                 </thead>
                 <tbody>
                ';
            foreach ($info_retrieve_data as $thisData){
                //$installed = ($thisData->installed == 1)?'Yes':'Processing';
                $total_trip = 0;
                //$info_id = $thisData->id;
                $reg_retrieve_data = $wpdb->get_row( "SELECT GROUP_CONCAT(vehicle_reg_no SEPARATOR ', ') AS vehicle_reg_no FROM $info_table_name WHERE gea_subscription_id = '".$thisData->gea_subscription_id."' GROUP BY 'all'" );

                /*if(!empty($thisData->parent_id))
                    $info_id = $thisData->parent_info_id;*/

                $trip_table_name = $wpdb->prefix . "users_trips";
                $trip_retrieve_data = $wpdb->get_row( "SELECT $trip_table_name.* FROM $trip_table_name WHERE $trip_table_name.user_id = ".get_current_user_id()." AND $trip_table_name.gea_subscription_id = ".$thisData->gea_subscription_id);
                if(!empty($trip_retrieve_data->id))
                    $total_trip = $trip_retrieve_data->total_trip;

                echo '
                <tr>
                    <td>'.$thisData->gea_subscription_id.'</td>
                    <td>'.$thisData->vehicle_type.'</td>
                    <td>'.$reg_retrieve_data->vehicle_reg_no.'</td>
                    <td>'.$total_trip.'</td>
                </tr>
            ';
            }

            echo '
                </tbody>
                <tfoot>
                    <tr>
                        <th>Subscription ID</th>
                        <th>Type</th>
                        <th>Reg. No`s</th>
                        <th>Total trip</th>
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
    if(!in_array($roles[0], $roles_array)) {
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
        $url = 'https://api.mobireach.com.bd/SendTextMessage?Username=orion_pharma&Password=Orion@54321&From=ORION&To='.$_POST['mobile_no'].'&Message='.urlencode($message);

        //send SMS
        $curl = curl_init();

        $timeout = 5;
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeout);
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

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
            background: url(http://mmhf.com.bd/wp-content/uploads/2020/01/logo-1-1.png) no-repeat center center;
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