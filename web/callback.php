<?php
// Phpmailerの読み込み
require_once ( './PHPMailer-master/PHPMailerAutoload.php' );
 


$accessToken = getenv('LINE_CHANNEL_ACCESS_TOKEN');
//ユーザーからのメッセージ取得
$json_string = file_get_contents('php://input');
$jsonObj = json_decode($json_string);
$type = $jsonObj->{"events"}[0]->{"message"}->{"type"};
//メッセージ取得
$text = $jsonObj->{"events"}[0]->{"message"}->{"text"};
//ReplyToken取得
$replyToken = $jsonObj->{"events"}[0]->{"replyToken"};
//messageId取得
$messageId = $jsonObj->{"events"}[0]->{"message"}->{"id"};

//画像ファイルのバイナリ取得
$ch = curl_init("https://api.line.me/v2/bot/message/".$messageId."/content");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
 'Content-Type: application/json; charser=UTF-8',
 'Authorization: Bearer ' . $accessToken
 ));
$result = curl_exec($ch);
curl_close($ch);


/* SMTP接続 */
//define('MAIL_HOST','example.sakura.ne.jp:587');  // さくらのメールの場合
define('MAIL_HOST','smtp.gmail.com:465'); // Gmailの場合
define('MAIL_USERNAME','kobaken.5884.guitar@gmail.com');
define('MAIL_PASSWORD','Kobakenk5884');
define('MAIL_FROM','kobaken.5884.guitar@gmail.com');
define('MAIL_CHARSET','iso-2022-jp');
define('MAIL_ENCODING','7bit');
define('MAIL_PHP_LANGUAGE','japanese');
define('MAIL_PHP_INTERNAL_ENCODING','UTF-8');
define('MAIL_FROM_NAME','シフト自動送信');

mb_language(MAIL_PHP_LANGUAGE);
mb_internal_encoding(MAIL_PHP_INTERNAL_ENCODING);
$mail = new PHPMailer();
$mail->CharSet = MAIL_CHARSET;
$mail->Encoding = MAIL_ENCODING;

//SMTP接続
$mail->IsSMTP();
$mail->SMTPAuth = TRUE;
$mail->SMTPSecure = 'ssl';  // Gmailの場合はこれが必要！
$mail->Host = MAIL_HOST;  //メールサーバー
$mail->Username = MAIL_USERNAME; //アカウント名
$mail->Password = TMMAIL_PASSWORD; //アカウントのパスワード
$mail->From = MAIL_FROM; //差出人(From)をセット
$mail->FromName = mb_encode_mimeheader(MAIL_FROM_NAME); //差出人の名前

$mail->ClearAddresses();  // 宛先アドレスを前に指定した場合はクリア
$mail->AddAddress(‘kobaken.5884.guitar@gmail.com’); //宛先アドレス1。


$mail->Subject = mb_encode_mimeheader('シフト変更通知');  //メールサブジェクトの指定
// 本文を指定
$mail->Body  = mb_convert_encoding('シフトが変更されました。', 'JIS', TMMAIL_PHP_INTERNAL_ENCODING);
//送信
$mail->Send();


// //画像ファイルの作成  
// $fp = fopen('./img/test.jpg', 'wb');

// if ($fp){
//     if (flock($fp, LOCK_EX)){
//         if (fwrite($fp,  $result ) === FALSE){
//             print('ファイル書き込みに失敗しました<br>');
//         }else{
//             print($data.'をファイルに書き込みました<br>');
//         }

//         flock($fp, LOCK_UN);
//     }else{
//         print('ファイルロックに失敗しました<br>');
//     }
// }

// fclose($fp);

//そのまま画像をオウム返しで送信  
 $response_format_text = [
 "type" => "text",
 "text" => "画像"
 // "originalContentUrl" => "【画像ファイルのパス】/img/test.jpg",
 // "previewImageUrl" => "【画像ファイルのパス】/img/test.jpg"
 ];

$post_data = [
"replyToken" => $replyToken,
"messages" => [$response_format_text]
];
 
$ch = curl_init("https://api.line.me/v2/bot/message/reply");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
 'Content-Type: application/json; charser=UTF-8',
 'Authorization: Bearer ' . $accessToken
 ));
$result = curl_exec($ch);
curl_close($ch);


// //メッセージ以外のときは何も返さず終了
// if($type != "image"){
// 	exit;
// }
// //返信データ作成
// if ($text == 'はい') {
//   $response_format_text = [
//     "type" => "template",
//     "altText" => "こちらの〇〇はいかがですか？",
//     "template" => [
//       "type" => "buttons",
//       "thumbnailImageUrl" => "https://" . $_SERVER['SERVER_NAME'] . "/img1.jpg",
//       "title" => "○○レストラン",
//       "text" => "お探しのレストランはこれですね",
//       "actions" => [
//           [
//             "type" => "postback",
//             "label" => "予約する",
//             "data" => "action=buy&itemid=123"
//           ],
//           [
//             "type" => "postback",
//             "label" => "電話する",
//             "data" => "action=pcall&itemid=123"
//           ],
//           [
//             "type" => "uri",
//             "label" => "詳しく見る",
//             "uri" => "https://" . $_SERVER['SERVER_NAME'] . "/"
//           ],
//           [
//             "type" => "message",
//             "label" => "違うやつ",
//             "text" => "違うやつお願い"
//           ]
//       ]
//     ]
//   ];
// } else if ($text == 'いいえ') {
//   exit;
// } else if ($text == '違うやつお願い') {
//   $response_format_text = [
//     "type" => "template",
//     "altText" => "候補を３つご案内しています。",
//     "template" => [
//       "type" => "carousel",
//       "columns" => [
//           [
//             "thumbnailImageUrl" => "https://" . $_SERVER['SERVER_NAME'] . "/img2-1.jpg",
//             "title" => "●●レストラン",
//             "text" => "こちらにしますか？",
//             "actions" => [
//               [
//                   "type" => "postback",
//                   "label" => "予約する",
//                   "data" => "action=rsv&itemid=111"
//               ],
//               [
//                   "type" => "postback",
//                   "label" => "電話する",
//                   "data" => "action=pcall&itemid=111"
//               ],
//               [
//                   "type" => "uri",
//                   "label" => "詳しく見る（ブラウザ起動）",
//                   "uri" => "https://" . $_SERVER['SERVER_NAME'] . "/"
//               ]
//             ]
//           ],
//           [
//             "thumbnailImageUrl" => "https://" . $_SERVER['SERVER_NAME'] . "/img2-2.jpg",
//             "title" => "▲▲レストラン",
//             "text" => "それともこちら？（２つ目）",
//             "actions" => [
//               [
//                   "type" => "postback",
//                   "label" => "予約する",
//                   "data" => "action=rsv&itemid=222"
//               ],
//               [
//                   "type" => "postback",
//                   "label" => "電話する",
//                   "data" => "action=pcall&itemid=222"
//               ],
//               [
//                   "type" => "uri",
//                   "label" => "詳しく見る（ブラウザ起動）",
//                   "uri" => "https://" . $_SERVER['SERVER_NAME'] . "/"
//               ]
//             ]
//           ],
//           [
//             "thumbnailImageUrl" => "https://" . $_SERVER['SERVER_NAME'] . "/img2-3.jpg",
//             "title" => "■■レストラン",
//             "text" => "はたまたこちら？（３つ目）",
//             "actions" => [
//               [
//                   "type" => "postback",
//                   "label" => "予約する",
//                   "data" => "action=rsv&itemid=333"
//               ],
//               [
//                   "type" => "postback",
//                   "label" => "電話する",
//                   "data" => "action=pcall&itemid=333"
//               ],
//               [
//                   "type" => "uri",
//                   "label" => "詳しく見る（ブラウザ起動）",
//                   "uri" => "https://" . $_SERVER['SERVER_NAME'] . "/"
//               ]
//             ]
//           ]
//       ]
//     ]
//   ];
// } else {
//   $response_format_text = [
//     "type" => "template",
//     "altText" => "こんにちは 何かご用ですか？（はい／いいえ）",
//     "template" => [
//         "type" => "confirm",
//         "text" => "こんにちは 何かご用ですか？",
//         "actions" => [
//             [
//               "type" => "message",
//               "label" => "はい",
//               "text" => "はい"
//             ],
//             [
//               "type" => "message",
//               "label" => "いいえ",
//               "text" => "いいえ"
//             ]
//         ]
//     ]
//   ];
//   //imageならバイナリ取得
//    $ch = curl_init("https://api.line.me/v2/bot/message/".$messageId."/content");
//  'Authorization: Bearer ' . $accessToken
//  'Content-Type: application/json; charser=UTF-8',
//  ));
// $result = curl_exec($ch);
// curl_close($ch);


// $response_format_text = [
//  "type" => "text",
//  "text" => "画像"
//  ];

// }
// $post_data = [
// 	"replyToken" => $replyToken,
// 	"messages" => [$response_format_text]
// 	];
// $ch = curl_init("https://api.line.me/v2/bot/message/reply");
// curl_setopt($ch, CURLOPT_POST, true);
// curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
// curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
// curl_setopt($ch, CURLOPT_HTTPHEADER, array(
//     'Content-Type: application/json; charser=UTF-8',
//     'Authorization: Bearer ' . $accessToken
//     ));
// $result = curl_exec($ch);
// curl_close($ch);