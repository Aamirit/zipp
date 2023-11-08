<?php 
session_start();
$data = [];
// $data['response'] = false;


$first_name = $_POST['first_name1'];
$last_name = $_POST['last_name1'];
$email = $_POST['email'];
$address = $_POST['address1'];
$address_second = $_POST['address_21'];
$city = $_POST['city1'];
// $state = $_POST['state'];
$state = "AZ";
$zip = $_POST['zip1'];
$ccnumber = $_POST['ccnum'];
$carddate = $_POST['cardDate'];
$cvv = $_POST['cvv'];
$country = "US";
// Extract month and year
if(!empty($carddate)){
    list($month, $year) = explode('/', $carddate);
$fullYear = date('Y', strtotime('20'.$year));
}



if(!isset($_POST['billingSameAsShipping'])){

    $billing_first_name = $_POST['billing_first_name1'];
    $billing_last_name = $_POST['billing_last_name1'];
    $billing_address1 = $_POST['billing_address1'];
    $billing_city = $_POST['billing_city1'];
    $billing_zip = $_POST['billing_zip1'];

    if (empty($billing_first_name)) {  
        $data['errors']['billing_first_name_error'] = "Error! You didn't enter the Billing First Name.";  
    } 
    if (empty($billing_last_name)) {  
        $data['errors']['billing_last_name_error'] = "Error! You didn't enter  the Billing last Name.";  
    } 
    if (empty($billing_address1)) {  
        $data['errors']['billing_address1_error'] = "Error! You didn't enter the Billing Address.";  
    } 
    if (empty($billing_city)) {  
        $data['errors']['billing_city_error'] = "Error! You didn't enter the Billing City.";  
    } 
    if (empty($billing_zip)) {  
        $data['errors']['billing_zip_error'] = "Error! You didn't enter the Billing Zip.";  
    } 
}

    // start validation
    if (empty($first_name)) {  
        $data['errors']['first_name_error'] = "Error! You didn't enter the First Name.";  
    }     
    if (empty($last_name)) {  
        $data['errors']['last_name_error'] = "Error! You didn't enter the Last Name.";  
    }     

    if (empty($email)) {  
        $data['errors']['email_error'] = "Error! You didn't enter the Email Address.";  
    }else {
    if(filter_var($email, FILTER_VALIDATE_EMAIL)) {
    // 
    }
    else{
        $data['errors']['email_error'] = "Error! Please enter the valid email.";  
    }
    }

    if (empty($address)) {  
        $data['errors']['address_error'] = "Error! You didn't enter the Address.";  
    }
    if (empty($city)) {  
        $data['errors']['city_error'] = "Error! You didn't enter the City.";  
    }
    if (empty($zip)) {  
        $data['errors']['zip_error'] = "Error! You didn't enter the Zip Code.";  
    }
    if (empty($ccnumber)) {  
        $data['errors']['ccnum_error'] = "Error! You didn't enter the Credit Card Number.";  
    }
    if (empty($carddate)) {  
        $data['errors']['carddate_error'] = "Error! You didn't enter the Expiry Date.";  
    }
    if (empty($cvv)) {  
        $data['errors']['cvv_error'] = "Error! You didn't enter the CVV.";  
    }



    if(empty($data['errors'])){
        //checkout proces start here
        $product = 252;
        $loginId = 'phpcrmapi';
        $password = 'Mdrlol1234@';
   
        $import_lead_data = array(
            'address1' =>  $address,
            'city' =>  $city,
            'country' =>  $country,
            'emailAddress' => $email,
            'ipAddress' => getUserIP(),  
            'firstName' =>  $first_name,
            'lastName' => $last_name,
            'loginId' => $loginId,
            'password' =>  $password,
            'campaignId' => 14,
            // 'phoneNumber' =>  $phone,
            'postalCode' => $zip ,
            'product1_id' => $product,
            'state' => $state,
         
        );
   
            $url = 'https://api.konnektive.com/leads/import/';
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POSTFIELDS => $import_lead_data,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
            ));

            $response = curl_exec($curl);
            curl_close($curl);
            $response = json_decode($response, true);
    
            if($response['result'] == 'SUCCESS'){
                
                $orderid = $response['message']['orderId'];

                $_SESSION['order_ID'] = $orderid;
                $url = 'https://api.konnektive.com/order/import/';
                $order_params = array(
                    'loginId' =>  $loginId ,
                    'password' =>  $password ,
                    'campaignId' => 14,
                    'orderId' =>  $orderid,
                    'product1_id' => $product,
                    'paySource' => 'CREDITCARD',
                    'cardNumber' => $ccnumber,
                    'cardMonth' => $month,
                    'cardYear' =>  $year,
                    'cardSecurityCode' => $cvv,
                    'shipFirstName' => isset($billing_first_name) ? $billing_first_name : $first_name,
                    'shipLastName' => isset($billing_last_name) ? $billing_last_name : $last_name,
                    'shipCity' => isset($billing_city) ? $billing_city : $city,
                    'shipState' => $state,
                    'shipPostalCode' =>  $zip,
                    'shipCountry' => $country, 
                    'shipAddress1' => isset($billing_address1) ? $billing_address1 : $address,
                    'shipAddress2' => $address,
                    'address1' =>  $address,
                    'address2' => $address,
                    'billShipSame' => 1,
                    'city' =>  $city,
                    'state' => $state,
                    'postalCode' => $zip,
                    'country' => $country,
                    'emailAddress' => $email,
                    'firstName' => $first_name,
                    'lastName' => $last_name,
                    'ipAddress' => getUserIP(),  
                );
                
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $order_params);
                $order_response_string = curl_exec($ch);
                curl_close($ch);
                $response = json_decode($order_response_string, true);
                    if($response['result'] == 'SUCCESS' ){
                            $data['response'] = true;
                            $data['success_message'] ="Order placed Successfully ";
                    } else{
                        $data['error_message'] = $response['message'];
                    }
            } else {
                $data['error_message'] = $response['message'];
            }
    }else{
        $data['errors'] = $data['errors'];
    }

    echo json_encode($data);
    exit; 
     


    function getUserIP()
    {
        $client  = @$_SERVER['HTTP_CF_CONNECTING_IP'];
        $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
        $remote  = $_SERVER['REMOTE_ADDR'];
        if(filter_var($client, FILTER_VALIDATE_IP))
        {
            $ip = $client;
        }
        elseif(filter_var($forward, FILTER_VALIDATE_IP))
        {
            $ip = $forward;
        }
        else
        {
            $ip = $remote;
        }
        return $ip;
    }
     

// checkout process end here









