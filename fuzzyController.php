<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Firebase\Firebase;
use DateTime;

class fuzzyController extends Controller
{
 public function fuzzy(Request $request)
{
   
    // $sensorUS = 25;
    // $deteksi = 115; 
    $sensorUS = $request->input('d');
    $deteksi = $request->input('d2');
    //untuk gelas rendah
    if ($sensorUS < 90){ 
        $jarak[0] = 1;
    }
    else if ($sensorUS > 90 && $sensorUS < 110){ 
        $jarak[0] = (110 - $sensorUS)/(110 - 90); 
    }
    else { 
        $jarak[0] = 0;
    }

    //untuk gelas sedang
    if ($sensorUS < 90){ 
        $jarak[1] = 0;
    }
    else if ($sensorUS > 90 && $sensorUS < 110){ 
        $jarak[1] = ($sensorUS -90)/(110-90);
    }
    else if ($sensorUS > 110 && $sensorUS < 125){ 
        $jarak[1] = (125-$sensorUS)/(125 -110);
    }
    else { 
        $jarak[1] = 0;
    }

    //untuk gelas tinggi
    if ($sensorUS < 110){ 
        $jarak[2] = 0;
    } else if ($sensorUS > 110 && $sensorUS < 125){ 
        $jarak[2] = ($sensorUS-110)/(125-110);
    }
    else {  
        $jarak[2] = 1;
    }

    //untuk air rendah
    if ($deteksi < 90){ 
        $ketinggian[0] = 1;
    }
    else if ($deteksi > 90 && $deteksi < 110){ 
        $ketinggian[0] = (110 - $deteksi)/(110 - 90); 
    }
    else { 
        $ketinggian[0] = 0;
    }

    //untuk air sedang
    if ($deteksi < 90){ 
        $ketinggian[1] = 0;
    }
    else if ($deteksi > 90 && $deteksi < 110){ 
        $ketinggian[1] = ($deteksi -90)/(110-90);
    }
    else if ($deteksi > 110 && $deteksi < 125){ 
        $ketinggian[1] = (125-$deteksi)/(125 -110);
    }
    else { 
        $ketinggian[1] = 0;
    }

    //untuk air tinggi
    if ($deteksi < 110){ 
        $ketinggian[2] = 0;
    } else if ($deteksi > 110 && $deteksi < 125){ 
        $ketinggian[2] = ($deteksi-110)/(125-110);
    }
    else  { 
        $ketinggian[2] = 1;
    }

  $i; $j;
 for ( $i=0; $i<=2; $i=$i+1)
 {
   for ( $j=0; $j<=2; $j=$j+1)
   {
     $debit = min($jarak[$i], $ketinggian[$j]);
     $rule [$i][$j] = $debit;
   } 
 } 

 $rule00 = $rule [0][0]; // (lambat,rendah = Lambat)
 $rule01 = $rule [0][1]; // (lambat,sedang = Lambat)
 $rule02 = $rule [0][2]; // (lambat,tinggi = Lambat)
 
 $rule10 = $rule [1][0]; // (sedang,rendah = lambat)
 $rule11 = $rule [1][1]; // (sedang,sedang = Sedang)
 $rule12 = $rule [1][2]; // (sedang,tinggi = Cepat)
 
 $rule20 = $rule [2][0]; // (cepat,rendah = Cepat)
 $rule21 = $rule [2][1]; // (cepat,sedang = Cepat)
 $rule22 = $rule [2][2]; 

$lambat = 3;
$sedang = 15;
$cepat = 20;
$pwm = ($rule00 * $lambat) + ($rule01 * $lambat)+ ($rule02 * $lambat)+ 
($rule10 * $lambat)+ ($rule11 * $sedang)+ ($rule12 * $cepat) + 
($rule20 * $cepat)+ ($rule21 * $cepat)+ ($rule22 * $cepat);

$defuz = 0;
 $i; $j;
  for ( $i=0; $i<2; $i=$i+1)
  {
    for ( $j=0; $j<2; $j=$j+1)
    {
      $defuz = $defuz + $rule[$i][$j];
    } 
  } 
 $tinggi = $pwm / $defuz;

 if ($tinggi > 16 && $tinggi < 20){
             $status = 'tinggi';
         }
         if ($tinggi > 11 && $tinggi < 15 ){
             $status = 'normal';
         }
         if ($tinggi < 10){
            $status = 'rendah';
            $url = "https://fcm.googleapis.com/fcm/send";
            $token = 'cjqwXj-_0Fg:APA91bFPqIfYHOpYQoRPmC4qKGr6gYoR40DchKiUVfyi0YsG5KdHW7lPW2cusUMF3YFHnueYJGG6es7X0z2bG8aFIqIVIAGGmxpESJeZwx_m0TZKXcMKkpwR_sZT4eBdOqfAq8-fYANU'; // token user copy
            $serverKey = 'AAAAS9-yKTY:APA91bHglv9F1_nI4ZsDWaXdbXIWA_q4ZA1TA0dNkEJH9KW07-r6sUUda5MfBUx8BUrlgX8eBQgb-DqQVxjt0cL4-l2dGKeI9PL2CY8pA0n0mJdc_aKLExn50N3p5f7HrwNRl8gCKEpK'; // ke setting firebase ada key cloud massagung copy
            $title = 'Notifikasi';
            $body = 'Send Notif';
            $notification = array('title' =>$title , 'body' => $body, 'sound' => 'default', 'badge' => '1');
            $arrayToSend = array('to' => $token, 'notification' => $notification,'priority'=>'high');
            $json = json_encode($arrayToSend);
            $headers = array();
            $headers[] = 'Content-Type: application/json';
            $headers[] = 'Authorization: key='. $serverKey;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
            curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
            //Send the request
            $response = curl_exec($ch);
            //Close request
            if ($response === FALSE) {
            die('FCM Send Error: ' . curl_error($ch));
            }
            curl_close($ch);
        }

    $timeNow = Carbon::now()->timestamp;
    $milis = $timeNow % 1000;
        $ts = intval($timeNow / 1000);
        $date = DateTime::createFromFormat('U', $ts);
        $str = $date->format(date('d-m-Y_H:i:s'));
    $idarduino = 'alat01';
$fb = Firebase::initialize("https://call-poseidon.firebaseio.com");
$nodePushContent = $fb->set('/monitoring/'.$idarduino.'/'.$timeNow, [
                        'kondisi' => $tinggi,
                        
                          
    ]);

}
//  public function fuzzy(Request $request)
// {
   
//     // $sensorUS = 25;
//     // $deteksi = 115; 
//     $sensorUS = $request->input('d');
//     $deteksi = $request->input('d2');
//    $timeNow = Carbon::now()->timestamp;
//     $milis = $timeNow % 1000;
//         $ts = intval($timeNow / 1000);
//         $date = DateTime::createFromFormat('U', $ts);
//         $str = $date->format(date('d-m-Y_H:i:s'));
//     $idarduino = 'alat01';
// $fb = Firebase::initialize("https://call-poseidon.firebaseio.com");
// $nodePushContent = $fb->set('/monitoring/'.$idarduino.'/'.$timeNow, [
//                         'sensorUS' => $sensorUS,
//                         'sensorUS2' => $deteksi,
//                         // 'tinggi' => $tinggi,   
//     ]);

// }l

 
 public function grafik() {
    $fb = Firebase::initialize("https://call-poseidon.firebaseio.com");
    $idarduino = 'alat01';
    $grafik = $fb->get('/monitoring/'.$idarduino);
    echo $grafik , array('grafik' => $grafik);
    // $grafik = $fb->get('/monitoring/alat01/1222002822/d2');


}
public function notif() {
      
      $url = "https://fcm.googleapis.com/fcm/send";
            $token = 'cjqwXj-_0Fg:APA91bFPqIfYHOpYQoRPmC4qKGr6gYoR40DchKiUVfyi0YsG5KdHW7lPW2cusUMF3YFHnueYJGG6es7X0z2bG8aFIqIVIAGGmxpESJeZwx_m0TZKXcMKkpwR_sZT4eBdOqfAq8-fYANU'; // token user copy
            $serverKey = 'AAAAS9-yKTY:APA91bHglv9F1_nI4ZsDWaXdbXIWA_q4ZA1TA0dNkEJH9KW07-r6sUUda5MfBUx8BUrlgX8eBQgb-DqQVxjt0cL4-l2dGKeI9PL2CY8pA0n0mJdc_aKLExn50N3p5f7HrwNRl8gCKEpK'; // ke setting firebase ada key cloud massagung copy
            $title = 'Notifikasi';
            $body = 'Send Notif';
            $notification = array('title' =>$title , 'body' => $body, 'sound' => 'default', 'badge' => '1');
            $arrayToSend = array('to' => $token, 'notification' => $notification,'priority'=>'high');
            $json = json_encode($arrayToSend);
            $headers = array();
            $headers[] = 'Content-Type: application/json';
            $headers[] = 'Authorization: key='. $serverKey;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
            curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
            //Send the request
            $response = curl_exec($ch);
            //Close request
            if ($response === FALSE) {
            die('FCM Send Error: ' . curl_error($ch));
            }
            curl_close($ch);
        }



}
