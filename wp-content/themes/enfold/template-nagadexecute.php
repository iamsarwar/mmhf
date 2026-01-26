<?php
/* 
Template Name: NagadExecute
*/

the_post();
get_header();

session_start();

date_default_timezone_set('Asia/Dhaka');

?>
                                    <div id="loading">
                                        <center><img src="<?php echo get_bloginfo('url');?>/wp-content/uploads/2019/12/charging.gif"></center>
                                    </div>

<?php
function generateRandomString($length = 40)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}


function EncryptDataWithPublicKey($data)
{
    $pgPublicKey = "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAiCWvxDZZesS1g1lQfilVt8l3X5aMbXg5WOCYdG7q5C+Qevw0upm3tyYiKIwzXbqexnPNTHwRU7Ul7t8jP6nNVS/jLm35WFy6G9qRyXqMc1dHlwjpYwRNovLc12iTn1C5lCqIfiT+B/O/py1eIwNXgqQf39GDMJ3SesonowWioMJNXm3o80wscLMwjeezYGsyHcrnyYI2LnwfIMTSVN4T92Yy77SmE8xPydcdkgUaFxhK16qCGXMV3mF/VFx67LpZm8Sw3v135hxYX8wG1tCBKlL4psJF4+9vSy4W+8R5ieeqhrvRH+2MKLiKbDnewzKonFLbn2aKNrJefXYY7klaawIDAQAB";
    $public_key = "-----BEGIN PUBLIC KEY-----\n" . $pgPublicKey . "\n-----END PUBLIC KEY-----";
    $key_resource = openssl_get_publickey($public_key);
    openssl_public_encrypt($data, $cryptText, $key_resource);
    return base64_encode($cryptText);
}


function SignatureGenerate($data)
{
    $merchantPrivateKey = "MIIEvwIBADANBgkqhkiG9w0BAQEFAASCBKkwggSlAgEAAoIBAQCSGAi+4YB5/LTwfkNQ1ffkAlJFnQQ4CteGY6hs0j0RtATpmLkbr19NbEAcVO5UwsNA5Q9oWfVEgkf6Pjb1nDAktp8pnSa/qhZfJfHYCHoqjKcC18Z4ZajeJWy8/p8/KjCUfJXriU1g+nmRAqfC+Yi7aHhm/lCnP92QYGbs4xiSHM4VBnYBOS5ldZOzohWDWxXPcSvpgFRaYalxkbfZ7hBWh2Clu2r5jjMCMi9VfNpugXXgFzUJZLvUG75WEj3NGrp62WIJ/EKviVl2NyXaXMWK7+WD4EpNaa4uWcBJDo/iTSAxg1fu/5yZPCCKNc5tGaeP7tx61voy8caJHIKyBjiVAgMBAAECggEBAJFA5DEk9CBVaXFTmIpes0E4LaSZIQC3huJPY74OqjlXyyqWdjVYgGDyKCwDJZOQsaFsHY2bI4kiH35nLS22RQe7qzQs08Hs0qF3kKVCiHSVs9fXwbUjHmsAusSORLcAs5xu4oB020J4xzWudi4c/B2ZGV0NrhMcJKbPsoYEpQFfr2DCcOKu3m02pastQUfDitC+juPH6d+BwEkY4b+L3D1+Ns63gNS7ePr5He68LNb5AuvDgxOhAuPJm+nSus5wp7VeO5zJtfI8krL9jfk1jbV5WM8otMMbG9pUuStfH21Vg1XGAjYewAdDjTf/Da92jSEeqN50V+GgcJlxNJwBggECgYEA2In+CahBljH05+8CYmvBl0ukhVzuA++I1E3XZeDPaVlDXzo2QOfxRJLRoa/5f3p+sOZL+zCD38EbGEiPtcZqeJRSy3hLQtMd36PSPBlNLaOHa4Rl9GXQN7rwpEi4mTwiX9GtY91JgmNNeOUhktPZCSxWi6n+0u8tgBcWqMNCThECgYEArLegGXogizxgodQzp/I1wXHQ3RpbwqMv9VAiX/U7VVP45s+BtXIWUwSpuDQqxUUD5LupiYbEK2q9AwKb4J+lc+Hp32mo4TNwxKi/fdPHjy+TasigQa4VHRq+TaEh10uo4PRm0NnbGmg06luD6fLP7EZRhtsaAaaNCxj5QWF+TkUCgYEAoguCRI/ZpSB1eivuyOCC20oMMJE+vUtARkCKdP8ruFbIiDbz6taoQvzsS+d4+uEcdh2htrSzu3qzYBTNFucJPnshCotXJwb+UCI0bi0xCHpcGSXXdnHKxCntc8PIAURzJOmwrA8pt53AmDxAR7SpsqevjI0G2auLjVO57UlSEUECgYB+pnP+2qAsmYyeflWWrLSgck5fI6nv0uwi7a0XQmNM0bOtxLHvlIYsQDoX+iD24QHW44mRcEI/OBj7sRkOoOKZVCrECd2traPegYNRyE8IfzGRVya0ouuWmPq9QA/pqPBgbLdMJMRW290ZkRvtHIE9V98GjXnHhhLc9WYpfE07JQKBgQCrKDAfJ83W7cTXZDZUGpT6bkI4Hn7s2pWXNq7+6iwqGiAw1yXgvMeA9O8u0pvYrKaobM6iRrPerrIXCH16mZOJZAZWqrb0tJBxZ1wQ4tnlIrOD3pFSeM6Zze8m3TaqEGw1VtxFpGKe2W2wUar0BaVPG5xE4hSQryoiFg5z1NHvUQ==";
    $private_key = "-----BEGIN RSA PRIVATE KEY-----\n" . $merchantPrivateKey . "\n-----END RSA PRIVATE KEY-----";
    openssl_sign($data, $signature, $private_key, OPENSSL_ALGO_SHA256);
    return base64_encode($signature);
}

function EHttpPostMethod($PostURL, $PostData)
{
    $url = curl_init($PostURL);
    $postToken = json_encode($PostData);
    $header = array(
        'Content-Type:application/json',
        'X-KM-Api-Version:v-0.2.0',
        'X-KM-IP-V4:' . get_client_ip(),
        'X-KM-Client-Type:PC_WEB'
    );

    curl_setopt($url, CURLOPT_HTTPHEADER, $header);
    curl_setopt($url, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($url, CURLOPT_POSTFIELDS, $postToken);
    curl_setopt($url, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($url, CURLOPT_SSL_VERIFYPEER, false); 

    // curl_setopt($url, CURLOPT_HEADER, 1); 
    $resultData = curl_exec($url);
    $ResultArray = json_decode($resultData, true);
    $header_size = curl_getinfo($url, CURLINFO_HEADER_SIZE);

    curl_close($url);

    $fp = fopen('/var/www/html/cron_logs/nagad_events.log', 'w');
    fwrite($fp, date('Y-m-d H:i:s').' : '.$postToken);
    fclose($fp);

        // $headers = substr($resultData, 0, $header_size);

        // $body = substr($resultData, $header_size);

        // print_r($body);

        // print_r($headers);

    return $ResultArray;
}


function get_client_ip()
{
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if (isset($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if (isset($_SERVER['HTTP_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if (isset($_SERVER['HTTP_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if (isset($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';

    return $ipaddress;
}



function DecryptDataWithPrivateKey($cryptText)
{
    $merchantPrivateKey = "MIIEvwIBADANBgkqhkiG9w0BAQEFAASCBKkwggSlAgEAAoIBAQCSGAi+4YB5/LTwfkNQ1ffkAlJFnQQ4CteGY6hs0j0RtATpmLkbr19NbEAcVO5UwsNA5Q9oWfVEgkf6Pjb1nDAktp8pnSa/qhZfJfHYCHoqjKcC18Z4ZajeJWy8/p8/KjCUfJXriU1g+nmRAqfC+Yi7aHhm/lCnP92QYGbs4xiSHM4VBnYBOS5ldZOzohWDWxXPcSvpgFRaYalxkbfZ7hBWh2Clu2r5jjMCMi9VfNpugXXgFzUJZLvUG75WEj3NGrp62WIJ/EKviVl2NyXaXMWK7+WD4EpNaa4uWcBJDo/iTSAxg1fu/5yZPCCKNc5tGaeP7tx61voy8caJHIKyBjiVAgMBAAECggEBAJFA5DEk9CBVaXFTmIpes0E4LaSZIQC3huJPY74OqjlXyyqWdjVYgGDyKCwDJZOQsaFsHY2bI4kiH35nLS22RQe7qzQs08Hs0qF3kKVCiHSVs9fXwbUjHmsAusSORLcAs5xu4oB020J4xzWudi4c/B2ZGV0NrhMcJKbPsoYEpQFfr2DCcOKu3m02pastQUfDitC+juPH6d+BwEkY4b+L3D1+Ns63gNS7ePr5He68LNb5AuvDgxOhAuPJm+nSus5wp7VeO5zJtfI8krL9jfk1jbV5WM8otMMbG9pUuStfH21Vg1XGAjYewAdDjTf/Da92jSEeqN50V+GgcJlxNJwBggECgYEA2In+CahBljH05+8CYmvBl0ukhVzuA++I1E3XZeDPaVlDXzo2QOfxRJLRoa/5f3p+sOZL+zCD38EbGEiPtcZqeJRSy3hLQtMd36PSPBlNLaOHa4Rl9GXQN7rwpEi4mTwiX9GtY91JgmNNeOUhktPZCSxWi6n+0u8tgBcWqMNCThECgYEArLegGXogizxgodQzp/I1wXHQ3RpbwqMv9VAiX/U7VVP45s+BtXIWUwSpuDQqxUUD5LupiYbEK2q9AwKb4J+lc+Hp32mo4TNwxKi/fdPHjy+TasigQa4VHRq+TaEh10uo4PRm0NnbGmg06luD6fLP7EZRhtsaAaaNCxj5QWF+TkUCgYEAoguCRI/ZpSB1eivuyOCC20oMMJE+vUtARkCKdP8ruFbIiDbz6taoQvzsS+d4+uEcdh2htrSzu3qzYBTNFucJPnshCotXJwb+UCI0bi0xCHpcGSXXdnHKxCntc8PIAURzJOmwrA8pt53AmDxAR7SpsqevjI0G2auLjVO57UlSEUECgYB+pnP+2qAsmYyeflWWrLSgck5fI6nv0uwi7a0XQmNM0bOtxLHvlIYsQDoX+iD24QHW44mRcEI/OBj7sRkOoOKZVCrECd2traPegYNRyE8IfzGRVya0ouuWmPq9QA/pqPBgbLdMJMRW290ZkRvtHIE9V98GjXnHhhLc9WYpfE07JQKBgQCrKDAfJ83W7cTXZDZUGpT6bkI4Hn7s2pWXNq7+6iwqGiAw1yXgvMeA9O8u0pvYrKaobM6iRrPerrIXCH16mZOJZAZWqrb0tJBxZ1wQ4tnlIrOD3pFSeM6Zze8m3TaqEGw1VtxFpGKe2W2wUar0BaVPG5xE4hSQryoiFg5z1NHvUQ==";
    $private_key = "-----BEGIN RSA PRIVATE KEY-----\n" . $merchantPrivateKey . "\n-----END RSA PRIVATE KEY-----";
    openssl_private_decrypt(base64_decode($cryptText), $plain_text, $private_key);
    return $plain_text;
}


if(isset($_SERVER['HTTP_REFERER']) && stristr($_SERVER['HTTP_REFERER'], 'nagad'))
    echo "<script>window.open('https://mmhf.com.bd/my-account/add-trip/', '_self')</script>"; 




if(!empty($_REQUEST['data'])){
    $data = base64_decode($_REQUEST['data']);
    $data = explode(';', $data);
    $amnt = $data[0];
//echo $data[2];    
//exit;
    if(!empty($amnt) && $amnt > 0){
        $MerchantID = "688871122997372";
        $DateTime = Date('YmdHis');
        $amount = $amnt;
	$rdn = str_pad(rand(0,999), 5, "0", STR_PAD_LEFT);
        if(empty($data[1]) || $data[1] == 'null')
            $OrderId = strtotime("now").rand(1000, 10000);
        else
            $OrderId = $data[1].$rdn;


        if(strlen($OrderId) > 20)
            $OrderId = substr($OrderId, 0, 19);
        
        
//echo $OrderId;
        $random = generateRandomString();

        $PostURL = "https://api.mynagad.com/api/dfs/check-out/initialize/" . $MerchantID . "/" . $OrderId;

        $_SESSION['nagadOrderId'] = $OrderId;

        $merchantCallbackURL = "https://mmhf.com.bd/nagadgateway";

        $SensitiveData = array(
            'merchantId' => $MerchantID,
            'datetime' => $DateTime,
            'orderId' => $OrderId,
            'challenge' => $random
        );

        $PostData = array(
            'accountNumber' => '01887112299', //Replace with Merchant Number (not mandatory)
            'dateTime' => $DateTime,
            'sensitiveData' => EncryptDataWithPublicKey(json_encode($SensitiveData)),
            'signature' => SignatureGenerate(json_encode($SensitiveData))
        );
        $Result_Data = EHttpPostMethod($PostURL, $PostData);
//var_dump($Result_Data);
//exit;
        if (isset($Result_Data['sensitiveData']) && isset($Result_Data['signature'])) {
            if ($Result_Data['sensitiveData'] != "" && $Result_Data['signature'] != "") {

                $PlainResponse = json_decode(DecryptDataWithPrivateKey($Result_Data['sensitiveData']), true);


                if (isset($PlainResponse['paymentReferenceId']) && isset($PlainResponse['challenge'])) {


                    $paymentReferenceId = $PlainResponse['paymentReferenceId'];


                    $randomServer = $PlainResponse['challenge'];

                    $SensitiveDataOrder = array(
                        'merchantId' => $MerchantID,
                        'orderId' => $OrderId,
                        'currencyCode' => '050',
                        'amount' => $amount,
                        'challenge' => $randomServer
                    );

                    
                    $logo = "https://mmhf.com.bd/wp-content/uploads/2020/01/logo-1-1.png";
                    
                    $merchantAdditionalInfo = '{"serviceName":"Brand Name", "serviceLogoURL": "'.$logo.'", "additionalFieldNameEN": "Type", "additionalFieldNameBN": "টাইপ","additionalFieldValue": "Payment"}';

                    $PostDataOrder = array(
                        'sensitiveData' => EncryptDataWithPublicKey(json_encode($SensitiveDataOrder)),
                        'signature' => SignatureGenerate(json_encode($SensitiveDataOrder)),
                        'merchantCallbackURL' => $merchantCallbackURL,
                        'additionalMerchantInfo' => json_decode($merchantAdditionalInfo)
                    );

                            
                    $OrderSubmitUrl = "https://api.mynagad.com/api/dfs/check-out/complete/" . $paymentReferenceId;
                    $Result_Data_Order = EHttpPostMethod($OrderSubmitUrl, $PostDataOrder);
                
                    if ($Result_Data_Order['status'] == "Success") {

                        $bank_process_data = array("order_id"=>$OrderId, "paymentRefId"=>$paymentReferenceId);

                        if($data[2] == 'reg'){
                            $payment_for = 'new user registration';
                            $user_data = $_SESSION['mmhfreg_data'];
                        }
                        else if($data[2] == 'vehicle'){
                            $payment_for = 'add vehicle';
                            $user_data = $_SESSION['mmhfvehicle_data'];
                        }
                        else{
                            $payment_for = 'add trip';
                            $user_data = $_SESSION['mmhftrip_data'];
                        }


                        $bank_tokens_table_name = $wpdb->prefix . "bank_tokens";
                        $bank_tokens_insert_data = $wpdb->insert(
                            $bank_tokens_table_name,
                            array(
                                'bank_name' => 'nagad',
                                'bank_token' => $paymentReferenceId,
                                'bank_process_data' => json_encode($bank_process_data),
                                'user_data' => json_encode($user_data),
                                'process_status' => 'pending',
                                'payment_for' => $payment_for,
                                'created_by' => !empty(get_current_user_id())?get_current_user_id():0,
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

                        $url = json_encode($Result_Data_Order['callBackUrl']);   
                        echo "<script>window.open($url, '_self')</script>";  
                                
                    }
                    else {
                        echo json_encode($Result_Data_Order);
                        
                    }
                } else {
                    echo json_encode($PlainResponse);             
                }
            }
        }
    }
}
?>
    <script src="//code.jquery.com/jquery-1.8.3.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            $("#main").removeClass("all_colors").addClass("all_colors myAccClass");
        });
    </script>

<?php get_footer() ?>


