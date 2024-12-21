<?php
include './config.php';
include './functions.php';
include './buttons.php';
$MySQLi = new mysqli('localhost',$DB['username'],$DB['password'],$DB['dbname']);
$MySQLi->query("SET NAMES 'utf8'");
$MySQLi->set_charset('utf8mb4');
if ($MySQLi->connect_error) die;
$update = json_decode(file_get_contents('php://input'));
if(isset($update->message)) {
$message = $update->message;
$msg = $message->text;
$tc = $message->chat->type;
$chat_id = $message->chat->id;
$from_id = $message->from->id;
$message_id = $message->message_id;
$first_name = $message->from->first_name;
$last_name = $message->from->last_name;
$username = $message->from->username?:'-';
$IsHeJoined = joinCheck($from_id);
$UserDataBase = mysqli_fetch_assoc(mysqli_query($MySQLi, "SELECT * FROM `user` WHERE `id` = '{$from_id}' LIMIT 1"));
}
if(isset($update->callback_query)) {
$callback_query = $update->callback_query;
$data = $callback_query->data;
$tc = $callback_query->message->chat->type;
$chatid = $callback_query->message->chat->id;
$fromid = $callback_query->from->id;
$messageid = $callback_query->message->message_id;
$inline_message_id = $callback_query->inline_message_id;
$firstname = $callback_query->from->first_name;
$lastname = $callback_query->from->last_name;
$cusername = $callback_query->from->username?:'-';
$membercall = $callback_query->id;
$IsHeJoined = joinCheck($fromid);
$UserDataBase = mysqli_fetch_assoc(mysqli_query($MySQLi, "SELECT * FROM `user` WHERE `id` = '{$fromid}' LIMIT 1"));
}
if(isset($update->inline_query)){
$inline_query = $update->inline_query->query;
$inline_query_id = $update->inline_query->id;
$inline_first_name = $update->inline_query->from->first_name;
$inline_from_id = $update->inline_query->from->id;
$IsHeJoined = true;
}
if($UserDataBase['step'] == 'banned'){
if(isset($update->message)) {
LampStack('sendMessage',[
'chat_id'=>$from_id,
'text'=> 'کاربرگرامی, حساب شما در ربات توسط مدیریت مسدود شده است.',
'parse_mode'=>"HTML",
]);
}
if(isset($update->callback_query)) {
LampStack('answercallbackquery', [
'callback_query_id' => $update->callback_query->id,
'text' => 'کاربرگرامی, حساب شما در ربات توسط مدیریت مسدود شده است.',
'show_alert' => true
]);
}
ToDie($MySQLi);
}
if($tc == 'private' and strpos($msg,'/start ') !== false and explode(' ',$msg)[3] == null){
$InViTerID = str_replace('/start ', '', $msg);
$InviterName = mysqli_fetch_assoc(mysqli_query($MySQLi, "SELECT * FROM `user` WHERE `id` = '{$InViTerID}' LIMIT 1"))['name'];
if($InViTerID == $from_id){
LampStack('sendMessage',[
'chat_id'=>$from_id,
'text'=> 'خطایی رخ داد ❗️',
'parse_mode'=>"HTML",
]);
ToDie($MySQLi);
}
$UserDataBase = mysqli_fetch_assoc(mysqli_query($MySQLi, "SELECT * FROM `user` WHERE `id` = '{$from_id}' LIMIT 1"));
if(!$UserDataBase){
$MySQLi->query("INSERT INTO `user` (`id`,`step`,`sex`,`name`,`age`,`coin`,`inviter`,`invite_reward`,`randomuser`,`bool`,`pID`) VALUES ('{$from_id}',null,null,'{$first_name}',null,'{$first_coin_count}','{$InViTerID}',1,null,null,null)");
}else{
LampStack('sendMessage',[
'chat_id'=>$from_id,
'text'=> "خطایی رخ داد ❗️
لطفا ربات را /start نمایید.",
'parse_mode'=>"HTML",
]);
ToDie($MySQLi);
}
$MenTionUser = "[$first_name](tg://user?id=$from_id)";
LampStack('sendMessage',[
'chat_id'=>$InViTerID,
'text'=> "کاربر $MenTionUser با لینک دعوت شما به ربات پیوست, پس از عضویت در کانال های ما $refral_coin سکه به شما اضافه خواهد شد ❗️",
'parse_mode'=>"markdown",
]);
if($IsHeJoined == false){
$ListOFChannels = '';
foreach($LockChannelsUserName as $value){
$ListOFChannels .= '🆔 '.$value."\n";
}
LampStack('sendMessage',[
'chat_id'=>$from_id,
'text'=> "❌کاربر گرامی برای کار با ربات باید عضو کانال های زیر باشید :

$ListOFChannels

بعد از عضو شدن روی دکمه زیر بزنید :",
'parse_mode'=>"HTML",
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=> 'عضو شدم ✅','callback_data'=>'BackToMainMenu']],
]
])
]);
ToDie($MySQLi);
}
LampStack('sendMessage',[
'chat_id'=>$from_id,
'text'=> "سلام
به ربات جرعت و حقیقت خوش اومدی !
برای کار با ربات روی دکمه های شیشه ای زیر بزنید :",
'parse_mode'=>"HTML",
'reply_to_message_id'=>$message_id,
'reply_markup'=>$main_menu
]);
ToDie($MySQLi);
}
if(isset($update->message) and !$UserDataBase) {
$MySQLi->query("INSERT INTO `user` (`id`,`step`,`sex`,`name`,`age`,`coin`,`inviter`,`invite_reward`,`randomuser`,`bool`,`pID`) VALUES ('{$from_id}',null,null,'{$first_name}',null,'{$first_coin_count}',null,0,null,null,null)");
}
if($IsHeJoined == false){
if(isset($update->message)) {
$ListOFChannels = '';
foreach($LockChannelsUserName as $value){
$ListOFChannels .= '🆔 '.$value."\n";
}
LampStack('sendMessage',[
'chat_id'=>$from_id,
'text'=> "❌کاربر گرامی برای کار با ربات باید عضو کانال های زیر باشید :

$ListOFChannels

بعد از عضو شدن روی دکمه زیر بزنید :",
'parse_mode'=>"HTML",
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=> 'عضو شدم ✅','callback_data'=>'BackToMainMenu']],
]
])
]);
}
if(isset($update->callback_query)) {
LampStack('answercallbackquery', [
'callback_query_id' => $update->callback_query->id,
'text' => 'کاربرگرامی, هنوز در کانال های ما عضو نشده اید !',
'show_alert' => true
]);
}
ToDie($MySQLi);
}
// Accept Questions  
if(explode('-',$data)[0] == 'AcceptQ' or explode('-',$data)[0] == 'RejectQ'){
if(explode('-',$data)[0] == 'AcceptQ'){
$DataType = explode('-',$data)[1];
$QuestionText = str_replace([PHP_EOL,"\n"],['',''],explode('متن سوال :',$update->callback_query->message->text)[1]);
$RandID = rand(11111111,99999999);
$MySQLi->query("INSERT INTO `questions` (`id`,`type`,`question`) VALUES ('{$RandID}','{$DataType}','{$QuestionText}')");
$QuTypeFa = str_replace(['h_1_b','h_1_g','h_18_b','h_18_g','j_1_b','j_1_g','j_18_b','j_18_g'],['حقیقت عادی پسر','حقیقت عادی دختر','حقیقت +18 پسر','حقیقت +18 دختر','جرأت عادی پسر','جرأت عادی دختر','جرأت +18 پسر','جرأت +18 دختر'],$DataType);
LampStack('sendMessage',[
'chat_id'=>$ToHaveQuestions,
'text'=> "شناسه سوال در دیتابیس : $RandID
متن سوال :
$QuestionText
نوع سوال : $QuTypeFa",
'parse_mode'=>"HTML",
]);
LampStack('answercallbackquery', [
'callback_query_id' => $update->callback_query->id,
'text' => 'سوال مورد نظر تایید شد.',
'show_alert' => false
]);
LampStack('DeleteMessage',[
'chat_id' => $chatid,
'message_id' =>$messageid,
]);
}
if(explode('-',$data)[0] == 'RejectQ'){
LampStack('answercallbackquery', [
'callback_query_id' => $update->callback_query->id,
'text' => 'سوال مورد نظر رد شد.',
'show_alert' => false
]);
LampStack('DeleteMessage',[
'chat_id' => $chatid,
'message_id' =>$messageid,
]);
}
ToDie($MySQLi);
}

if($tc == 'private'){
if($IsHeJoined == true and $UserDataBase['inviter'] !== null and $UserDataBase['invite_reward'] == 1){
$InViTerID = $UserDataBase['inviter'];
$InviterCoins = mysqli_fetch_assoc(mysqli_query($MySQLi, "SELECT * FROM `user` WHERE `id` = '{$InViTerID}' LIMIT 1"))['coin'];
$NewCoin = $InviterCoins + $refral_coin;
$MyID = $from_id?:$fromid;
$MySQLi->query("UPDATE `user` SET `coin` = '{$NewCoin}' WHERE `id` = '{$InViTerID}' LIMIT 1");
$MySQLi->query("UPDATE `user` SET `invite_reward` = 0 WHERE `id` = '{$MyID}' LIMIT 1");
LampStack('sendMessage',[
'chat_id'=>$InViTerID,
'text'=> "تبریک, زیرمجموعه شما در کانال های ربات عضو شد و شما $refral_coin سکه دریافت کردید 🔥",
'parse_mode'=>"markdown",
]);
}
if($first_name and $first_name !== $UserDataBase['name']){
$MySQLi->query("UPDATE `user` SET `name` = '{$first_name}' WHERE `id` = '{$from_id}' LIMIT 1");
}
if($msg === '/start'){
$MySQLi->query("UPDATE `user` SET `step` = null WHERE `id` = '{$from_id}' LIMIT 1");
if(mysqli_fetch_assoc(mysqli_query($MySQLi, "SELECT * FROM `privates` WHERE `id` = '{$from_id}' LIMIT 1"))){
if(mysqli_fetch_assoc(mysqli_query($MySQLi, "SELECT * FROM `privates` WHERE `id` = '{$from_id}' LIMIT 1"))['coin'] == 'yes'){
$NewCoin = $UserDataBase['coin'] + 2;
$MySQLi->query("UPDATE `user` SET `coin` = '{$NewCoin}' WHERE `id` = '{$from_id}' LIMIT 1");
}
$MySQLi->query("DELETE FROM `privates` WHERE `id` = '{$from_id}'");
}
if($UserDataBase['randomuser'] !== null){
LampStack('sendMessage',[
'chat_id'=>$from_id,
'text'=> 'بازی فعلی رو قطع کنم؟',
'parse_mode'=>"HTML",
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=>'خیر ❌','callback_data'=>'NopeContenue'],['text'=>'بله ✅','callback_data'=>'BackToMainMenu']],
]
])
]);
ToDie($MySQLi);
}
LampStack('sendMessage',[
'chat_id'=>$from_id,
'text'=> "سلام
به ربات جرعت و حقیقت خوش اومدی !
برای کار با ربات روی دکمه های شیشه ای زیر بزنید :",
'parse_mode'=>"HTML",
'reply_to_message_id'=>$message_id,
'reply_markup'=>$main_menu
]);
ToDie($MySQLi);
}
if($data == 'NopeContenue'){
LampStack('answercallbackquery', [
'callback_query_id' => $update->callback_query->id,
'text' => 'رواله ...',
'show_alert' => true
]);
LampStack('DeleteMessage',[
'chat_id' => $fromid,
'message_id' =>$messageid,
]);
ToDie($MySQLi);
}
if($data == 'BackToMainMenu'){
if(mysqli_fetch_assoc(mysqli_query($MySQLi, "SELECT * FROM `privates` WHERE `id` = '{$fromid}' LIMIT 1"))){
if(mysqli_fetch_assoc(mysqli_query($MySQLi, "SELECT * FROM `privates` WHERE `id` = '{$fromid}' LIMIT 1"))['coin'] == 'yes'){
$NewCoin = $UserDataBase['coin'] + 2;
$MySQLi->query("UPDATE `user` SET `coin` = '{$NewCoin}' WHERE `id` = '{$fromid}' LIMIT 1");
}
$MySQLi->query("DELETE FROM `privates` WHERE `id` = '{$fromid}'");
}
if($UserDataBase['randomuser'] !== null){
$GetAUserForHim = $UserDataBase['randomuser'];
$twoPlayerName = mysqli_fetch_assoc(mysqli_query($MySQLi, "SELECT * FROM `user` WHERE `id` = '{$GetAUserForHim}' LIMIT 1"))['name'];
LampStack('sendMessage',[
'chat_id'=>$GetAUserForHim,
'text'=> 'بازی توسط حریف کنسل شد.',
'parse_mode'=>"HTML",
'reply_markup'=>json_encode(['KeyboardRemove'=>[
],'remove_keyboard'=>true
])
]);
LampStack('sendMessage',[
'chat_id'=>$GetAUserForHim,
'text'=> "سلام
به ربات جرعت و حقیقت خوش اومدی !
برای کار با ربات روی دکمه های شیشه ای زیر بزنید :",
'parse_mode'=>"HTML",
'reply_markup'=>$main_menu
]);
LampStack('sendMessage',[
'chat_id'=>$fromid,
'text'=> 'بازی توسط شما کنسل شد.',
'parse_mode'=>"HTML",
'reply_markup'=>json_encode(['KeyboardRemove'=>[
],'remove_keyboard'=>true
])
]);
$MySQLi->query("UPDATE `user` SET `step` = null WHERE `id` = '{$GetAUserForHim}' LIMIT 1");
$MySQLi->query("UPDATE `user` SET `randomuser` = null WHERE `id` = '{$GetAUserForHim}' LIMIT 1");
$MySQLi->query("UPDATE `user` SET `step` = null WHERE `id` = '{$fromid}' LIMIT 1");
$MySQLi->query("UPDATE `user` SET `randomuser` = null WHERE `id` = '{$fromid}' LIMIT 1");
}
LampStack('DeleteMessage',[
'chat_id' => $fromid,
'message_id' =>$messageid,
]);
LampStack('sendMessage',[
'chat_id'=>$fromid,
'text'=> "سلام
به ربات جرعت و حقیقت خوش اومدی !
برای کار با ربات روی دکمه های شیشه ای زیر بزنید :",
'parse_mode'=>"HTML",
'reply_markup'=>$main_menu
]);
ToDie($MySQLi);
}
if($data == 'BuyCoins'){
LampStack('editmessagetext',[
'chat_id'=>$fromid,
'message_id'=>$messageid,
'text'=> 'برای خرید سکه از دکمه های زیر استفاده کنید :',
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=> '20 سکه 5000 تومان', 'url' => $webAddress.'/pay.php?user='.$fromid.'&p=20']],
[['text'=> '50 سکه 10000 تومان', 'url' => $webAddress.'/pay.php?user='.$fromid.'&p=50']],
[['text'=> '120 سکه 20000 تومان', 'url' => $webAddress.'/pay.php?user='.$fromid.'&p=120']],
[['text'=> '500 سکه 50000 تومان 🔥', 'url' => $webAddress.'/pay.php?user='.$fromid.'&p=500']],
[['text'=> 'برگشت','callback_data'=>'BackToMainMenu']],
]
])
]);
ToDie($MySQLi);
}
if($data == 'GetCoins'){
$CountOfCons = $UserDataBase['coin'];
$MyLink = 'https://t.me/'.BOT_USERNAME.'?start='.$fromid;
LampStack('DeleteMessage',[
'chat_id' => $fromid,
'message_id' =>$messageid,
]);
if(getUserProfilePhotos($fromid)->photos[0][0]->file_id !== null){
LampStack('sendphoto', [
'chat_id' => $fromid,
'photo' => getUserProfilePhotos($fromid)->photos[0][0]->file_id,
'caption' => "سلام رفیق!! « $firstname » دعوتت کرده که به ربات جرات و حقیقت بپیوندی 💐
    
    • روی لینک زیر کلیک کن 😍👐🏻

$MyLink",
'parse_mode' => "html",
]);
}else{
LampStack('sendphoto', [
'chat_id' => $fromid,
'photo' => new CURLFile('default.png'),
'caption' => "سلام رفیق!! « $firstname » دعوتت کرده که به ربات جرات و حقیقت بپیوندی 💐
    
• روی لینک زیر کلیک کن 😍👐🏻

$MyLink",
'parse_mode' => "html",
]);
}
LampStack('sendMessage',[
'chat_id'=>$fromid,
'text'=> "برای کسب سکه رایگان لینک بالا را به دوستان خود بدهید تا با لینک اختصاصی شما عضو ربات شوند.
پس از عضویت آنها در کانال های ما شما سکه دریافت خواهید کرد.",
'parse_mode'=>"HTML",
]);
LampStack('sendMessage',[
'chat_id'=>$fromid,
'text'=> "سلام
به ربات جرعت و حقیقت خوش اومدی !
برای کار با ربات روی دکمه های شیشه ای زیر بزنید :",
'parse_mode'=>"HTML",
'reply_markup'=>$main_menu
]);
ToDie($MySQLi);
}
if($data == 'MyAccountInfo'){
if($UserDataBase['age'] == null){
$MySQLi->query("UPDATE `user` SET `step` = 'SetUserSex' WHERE `id` = '{$fromid}' LIMIT 1");
LampStack('editmessagetext',[
'chat_id'=>$fromid,
'message_id'=>$messageid,
'text'=> '-لطفا جنسیت خود را انتخاب کنید👇🏻',
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=> 'پسرم 👱🏻','callback_data'=>'ImABoy'],['text'=> 'دخترم 👱🏻‍♀️','callback_data'=>'ImAGirl']],
[['text'=> 'برگشت','callback_data'=>'BackToMainMenu']],
]
])
]);
}else{
LampStack('DeleteMessage',[
'chat_id' => $fromid,
'message_id' =>$messageid,
]);
$CountOfCons = $UserDataBase['coin'];
$YourSex = str_replace(['boy','girl'],['پسر','دختر'],$UserDataBase['sex']);
$YourAge = $UserDataBase['age'];
$YourInviter = $UserDataBase['inviter']?:'شما توسط کسی به ربات دعوت نشده اید.';
if(is_numeric($YourInviter)){
$NoBatName = mysqli_fetch_assoc(mysqli_query($MySQLi, "SELECT * FROM `user` WHERE `id` = '{$YourInviter}' LIMIT 1"))["name"];
$YourInviter = '<a href="tg://user?id='.$YourInviter.'">'.str_replace(['<', '>', '&'], ['&lt;', '&gt;', '&amp;'],$NoBatName).'</a>';
}
$firstname = str_replace(['<', '>', '&'], ['&lt;', '&gt;', '&amp;'],$firstname);
if(getUserProfilePhotos($fromid)->photos[0][0]->file_id !== null){
LampStack('sendphoto', [
'chat_id' => $fromid,
'photo' => getUserProfilePhotos($fromid)->photos[0][0]->file_id,
'caption' => "
👤 نام : $firstname
🔅 جنسیت : $YourSex
🌀 سن : $YourAge
💸 تعداد سکه ها : $CountOfCons
",
'parse_mode' => "html",
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=> 'تغییر اطلاعات 📝','callback_data'=>'ChangeUserInfo']],
[['text'=> 'برگشت','callback_data'=>'BackToMainMenu']],
]
])
]);
}else{
LampStack('sendphoto', [
'chat_id' => $fromid,
'photo' => new CURLFile('default.png'),
'caption' => "
👤 نام : $firstname
🔅 جنسیت : $YourSex
🌀 سن : $YourAge
💸 تعداد سکه ها : $CountOfCons
",
'parse_mode' => "html",
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=> '📝 ویرایش مشخصات 📝','callback_data'=>'ChangeUserInfo']],
[['text'=> 'برگشت','callback_data'=>'BackToMainMenu']],
]
])
]);
}
}
ToDie($MySQLi);
}
if($data == 'ChangeUserInfo'){
$MySQLi->query("UPDATE `user` SET `step` = 'SetUserSex' WHERE `id` = '{$fromid}' LIMIT 1");
LampStack('DeleteMessage',[
'chat_id' => $fromid,
'message_id' =>$messageid,
]);
LampStack('sendMessage',[
'chat_id'=>$fromid,
'text'=> '-لطفا جنسیت خود را انتخاب کنید👇🏻',
'parse_mode'=>"HTML",
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=> 'پسرم 👱🏻','callback_data'=>'ImABoy'],['text'=> 'دخترم 👱🏻‍♀️','callback_data'=>'ImAGirl']],
[['text'=> 'برگشت','callback_data'=>'BackToMainMenu']],
]
])
]);
ToDie($MySQLi);
}
if($data == 'ImAGirl' or $data == 'ImABoy'){
if($UserDataBase['step'] == 'SetUserSex'){
$sex = str_replace(['ImABoy','ImAGirl'],['boy','girl'],$data);
$MySQLi->query("UPDATE `user` SET `step` = 'SetUserAge' WHERE `id` = '{$fromid}' LIMIT 1");
$MySQLi->query("UPDATE `user` SET `sex` = '{$sex}' WHERE `id` = '{$fromid}' LIMIT 1");
LampStack('editmessagetext',[
'chat_id'=>$fromid,
'message_id'=>$messageid,
'text'=> 'لطفا سن خود را انتخاب کنید👇🏻',
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=> '13', 'callback_data'=>'13'], ['text'=> '14','callback_data'=>'14'], ['text'=> '15','callback_data'=>'15'], ['text'=> '16','callback_data'=>'16'], ['text'=> '17','callback_data'=>'17']],
[['text'=> '18', 'callback_data'=>'18'], ['text'=> '19','callback_data'=>'19'], ['text'=> '20','callback_data'=>'20'], ['text'=> '21','callback_data'=>'21'], ['text'=> '22','callback_data'=>'22']],
[['text'=> '23', 'callback_data'=>'23'], ['text'=> '24','callback_data'=>'24'], ['text'=> '25','callback_data'=>'25'], ['text'=> '26','callback_data'=>'26'], ['text'=> '27','callback_data'=>'27']],
[['text'=> '28', 'callback_data'=>'28'], ['text'=> '29','callback_data'=>'29'], ['text'=> '30','callback_data'=>'30'], ['text'=> '31','callback_data'=>'31'], ['text'=> '32','callback_data'=>'32']],
[['text'=> '33', 'callback_data'=>'33'], ['text'=> '34','callback_data'=>'34'], ['text'=> '35','callback_data'=>'35'], ['text'=> '36','callback_data'=>'36'], ['text'=> '37','callback_data'=>'37']],
[['text'=> '38', 'callback_data'=>'38'], ['text'=> '39','callback_data'=>'39'], ['text'=> '40','callback_data'=>'40'], ['text'=> '41','callback_data'=>'41'], ['text'=> '42','callback_data'=>'42']],
[['text'=> 'برگشت ➡️','callback_data'=>'BackToMainMenu']],
]
])
]);
}
ToDie($MySQLi);
}
if(is_numeric($data) and $UserDataBase['step'] == 'SetUserAge'){
$MySQLi->query("UPDATE `user` SET `step` = null WHERE `id` = '{$fromid}' LIMIT 1");
$MySQLi->query("UPDATE `user` SET `age` = '{$data}' WHERE `id` = '{$fromid}' LIMIT 1");
LampStack('answercallbackquery', [
'callback_query_id' => $update->callback_query->id,
'text' => 'پروفایل شما تکمیل شد.',
'show_alert' => true
]);
LampStack('editmessagetext',[
'chat_id'=>$fromid,
'message_id'=>$messageid,
'text'=> "سلام
به ربات جرعت و حقیقت خوش اومدی !
برای کار با ربات روی دکمه های شیشه ای زیر بزنید :",
'reply_markup'=>$main_menu
]);
ToDie($MySQLi);
}
if($data == 'ContanctUS'){
$MySQLi->query("UPDATE `user` SET `step` = 'SendMessageAdmin' WHERE `id` = '{$fromid}' LIMIT 1");
LampStack('editmessagetext',[
'chat_id'=>$fromid,
'message_id'=>$messageid,
'text'=> 'به پشتیبانی ربات خوش آمدید 🌱
لطفا نظرات, انتقادات و پیشنهادات خود را ارسال کنید تا به دست مدیران ربات برسد :',
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=> 'برگشت','callback_data'=>'BackToMainMenu']],
]
])
]);
ToDie($MySQLi);
}
if($msg and $UserDataBase['step'] == 'SendMessageAdmin'){
$MySQLi->query("UPDATE `user` SET `step` = null WHERE `id` = '{$from_id}' LIMIT 1");
$YourSex = str_replace(['boy','girl'],['پسر','دختر'],$UserDataBase['sex']);
$YourAge = $UserDataBase['age'];
$MenTionUser = '<a href="tg://user?id='.$from_id.'">جهت مشاهده پروفایل کاربر کلیک کنید</a>';
$Text2Send = "
یک پیام پشتیبانی ارسال شد.

نام کاربر : $first_name
شناسه عددی کاربر : <pre>$from_id</pre>
جنسیت کاربر : <b>$YourSex</b>
سن کاربر : <b>$YourAge</b>
$MenTionUser

———————————

$msg
";
foreach($BotAdmins as $id){
LampStack('sendMessage',[
'chat_id'=> $id,
'text'=> $Text2Send,
'parse_mode'=>'HTML',
]);
}
LampStack('sendMessage',[
'chat_id'=> $from_id,
'text'=> 'بازخورد شما به مدیریت ربات ارسال شد.',
'parse_mode'=>'HTML',
'reply_to_message_id'=>$message_id,
]);
LampStack('sendMessage',[
'chat_id'=>$from_id,
'text'=> "سلام
به ربات جرعت و حقیقت خوش اومدی !
برای کار با ربات روی دکمه های شیشه ای زیر بزنید :",
'parse_mode'=>"HTML",
'reply_to_message_id'=>$message_id,
'reply_markup'=>$main_menu
]);
ToDie($MySQLi);
}
if($data == 'SubmitQuestions'){
$MySQLi->query("UPDATE `user` SET `step` = 'SendQuestion' WHERE `id` = '{$fromid}' LIMIT 1");
LampStack('editmessagetext',[
'chat_id'=>$fromid,
'message_id'=>$messageid,
'text'=> 'قصد دارید برای کدام یک از دسته های زیر سوال ارسال کنید؟',
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=> 'حقیقت عادی پسر','callback_data'=>'h_1_b'],['text'=> 'حقیقت عادی دختر','callback_data'=>'h_1_g']],
[['text'=> 'حقیقت +18 پسر','callback_data'=>'h_18_b'],['text'=> 'حقیقت +18 دختر','callback_data'=>'h_18_g']],
[['text'=> 'جرأت عادی پسر','callback_data'=>'j_1_b'],['text'=> 'جرأت عادی دختر','callback_data'=>'j_1_g']],
[['text'=> 'جرأت +18 پسر','callback_data'=>'j_18_b'],['text'=> 'جرأت +18 دختر','callback_data'=>'j_18_g']],
[['text'=> 'برگشت','callback_data'=>'BackToMainMenu']],
]
])
]);
ToDie($MySQLi);
}
if(strpos($data,'_') !== false and $UserDataBase['step'] == 'SendQuestion'){
$MySQLi->query("UPDATE `user` SET `step` = '{$data}' WHERE `id` = '{$fromid}' LIMIT 1");
LampStack('editmessagetext',[
'chat_id'=>$fromid,
'message_id'=>$messageid,
'text'=> 'لطفا سوال مورد نظرتو ارسال کن.',
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=> 'برگشت','callback_data'=>'BackToMainMenu']],
]
])
]);
ToDie($MySQLi);
}
if(explode('_',$UserDataBase['step'])[0] == 'h' or explode('_',$UserDataBase['step'])[0] == 'j'){
if(!$msg) ToDie($MySQLi);
$SorT = '';
if(explode('_',$UserDataBase['step'])[0] == 'h') $SorT .= 'حقیقت';
if(explode('_',$UserDataBase['step'])[0] == 'j') $SorT .= 'جرأت';
if(explode('_',$UserDataBase['step'])[1] == 1) $SorT .= ' عادی';
if(explode('_',$UserDataBase['step'])[1] == 18) $SorT .= ' +18';
if(explode('_',$UserDataBase['step'])[2] == 'b') $SorT .= ' پسر';
if(explode('_',$UserDataBase['step'])[2] == 'g') $SorT .= ' دختر';
LampStack('sendMessage',[
'chat_id'=> $ToAcceptQuestion,
'text'=> "یک سوال ارسال شد.
دسته بندی : $SorT
فرستنده : $from_id
متن سوال :
$msg",
'parse_mode'=>'HTML',
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=> '✅','callback_data'=>'AcceptQ-'.$UserDataBase['step']],['text'=> '❌','callback_data'=>'RejectQ-'.$UserDataBase['step']]],
]
])
]);
$MySQLi->query("UPDATE `user` SET `step` = null WHERE `id` = '{$from_id}' LIMIT 1");
LampStack('sendMessage',[
'chat_id'=> $from_id,
'text'=> "با تشکر از شما, سوال شما برای تایید به مدیریت ربات ارسال شد.",
'parse_mode'=>'HTML',
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=> 'برگشت','callback_data'=>'BackToMainMenu']],
]
])
]);
ToDie($MySQLi);
}
// Private Game  
if($data == 'PlayWithBoys' or $data == 'PlayWithGirls' or $data == 'PlayRandom'){
if($UserDataBase['age'] == null){
LampStack('answercallbackquery', [
'callback_query_id' => $update->callback_query->id,
'text' => 'برای استفاده از این بخش, لطفا ابتدا از بخش [ 👤 حساب من 👤 ] پروفایلتون رو کامل کنید !️',
'show_alert' => true
]);
ToDie($MySQLi);
}
if($data == 'PlayWithBoys' or $data == 'PlayWithGirls'){
if($UserDataBase['coin'] < 2){
LampStack('answercallbackquery', [
'callback_query_id' => $update->callback_query->id,
'text' => 'شما سکه کافی برای انجام این کار را ندارید ❗️',
'show_alert' => true
]);
ToDie($MySQLi);
}
}
LampStack('DeleteMessage',[
'chat_id' => $fromid,
'message_id' =>$messageid,
]);
if($data !== 'PlayRandom'){
$NewCoin = $UserDataBase['coin'] - 2;
$MySQLi->query("UPDATE `user` SET `coin` = '{$NewCoin}' WHERE `id` = '{$fromid}' LIMIT 1");
}
if($data == 'PlayWithBoys'){
$SexULoGy = 'boy';
$IsHaveCoin = 'yes';
}
if($data == 'PlayWithGirls'){
$SexULoGy = 'girl';
$IsHaveCoin = 'yes';
}
if($data == 'PlayRandom'){
$RandO = rand(0,2);
if($RandO == 0 or $RandO == 1){
$SexULoGy = 'boy';
}else{
$SexULoGy = 'girl';
}
$IsHaveCoin = 'no';
}
$YourSex = $UserDataBase['sex'];
if($YourSex == 'girl')
	$GetAUserForHim = mysqli_fetch_assoc(mysqli_query($MySQLi, "SELECT `id` FROM `privates` WHERE `id` = 1532939849 LIMIT 1"))['id'];
if(!$GetAUserForHim or $GetAUserForHim == null or empty($GetAUserForHim))
	$GetAUserForHim = mysqli_fetch_assoc(mysqli_query($MySQLi, "SELECT `id` FROM `privates` WHERE `you` = '{$SexULoGy}' AND `want` = '{$YourSex}' LIMIT 1"))['id'];
if($GetAUserForHim == null){
$YourSex = $UserDataBase['sex'];
$MySQLi->query("INSERT INTO `privates` (`id`,`you`,`want`,`coin`) VALUES ('{$fromid}','{$YourSex}','{$SexULoGy}','{$IsHaveCoin}')");
LampStack('sendMessage',[
'chat_id'=> $fromid,
'text'=> 'درحال جستجو ...',
'parse_mode'=>'HTML',
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=>'کنسل ❌','callback_data'=>'BackToMainMenu']],
]
])
]);
}else{
LampStack('DeleteMessage',[
'chat_id' => $fromid,
'message_id' =>$messageid,
]);
$MySQLi->query("UPDATE `user` SET `step` = 'InGame' WHERE `id` = '{$fromid}' LIMIT 1");
$MySQLi->query("UPDATE `user` SET `randomuser` = '{$GetAUserForHim}' WHERE `id` = '{$fromid}' LIMIT 1");
$MySQLi->query("UPDATE `user` SET `step` = 'InGame' WHERE `id` = '{$GetAUserForHim}' LIMIT 1");
$MySQLi->query("UPDATE `user` SET `randomuser` = '{$fromid}' WHERE `id` = '{$GetAUserForHim}' LIMIT 1");
$MySQLi->query("DELETE FROM `privates` WHERE `id` = '{$fromid}'");
$MySQLi->query("DELETE FROM `privates` WHERE `id` = '{$GetAUserForHim}'");
$OnePlayerName = $UserDataBase['name'];
$twoPlayerName = mysqli_fetch_assoc(mysqli_query($MySQLi, "SELECT * FROM `user` WHERE `id` = '{$GetAUserForHim}' LIMIT 1"))['name'];
LampStack('sendMessage',[
'chat_id'=> $fromid,
'text'=> "بازی با $twoPlayerName شروع شد.",
'parse_mode'=>'HTML',
'reply_markup' => json_encode([
'resize_keyboard' => true,
'keyboard' => [
[['text' => 'مشخصات حریف ⁉️']],
[['text' => 'لغو بازی ❌']],
]
])
]);
LampStack('sendMessage',[
'chat_id'=> $GetAUserForHim,
'text'=> "بازی با $OnePlayerName شروع شد.",
'parse_mode'=>'HTML',
'reply_markup' => json_encode([
'resize_keyboard' => true,
'keyboard' => [
[['text' => 'مشخصات حریف ⁉️']],
[['text' => 'لغو بازی ❌']],
]
])
]);
$MySQLi->query("UPDATE `user` SET `bool` = 0 WHERE `id` = '{$fromid}' LIMIT 1");
$MySQLi->query("UPDATE `user` SET `bool` = 1 WHERE `id` = '{$GetAUserForHim}' LIMIT 1");
LampStack('sendMessage',[
'chat_id'=> $fromid,
'text'=> "لطفا صبر کنید حریف نوع سوال رو مشخص کنه ⌛️",
'parse_mode'=>'HTML',
]);
LampStack('sendMessage',[
'chat_id'=> $GetAUserForHim,
'text'=> "یه سوال انتخاب کن :",
'parse_mode'=>'HTML',
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=> 'حقیقت عادی','callback_data'=>'Select_h_1'],['text'=> 'حقیقت +18','callback_data'=>'Select_h_18']],
[['text'=> 'جرأت عادی','callback_data'=>'Select_j_1'],['text'=> 'جرأت +18','callback_data'=>'Select_j_18']],
]
])
]);
}
ToDie($MySQLi);
}
if($UserDataBase['step'] == 'InGame' and explode('_',$data)[0] == 'Select' and $UserDataBase['bool'] == 1){
if(explode('_',$data)[1] == 'h' or explode('_',$data)[1] == 'j'){
$TypeOfQ = explode('_',$data)[1];
$TypeOfQ .= '_'.explode('_',$data)[2];
$OnePlayer = $UserDataBase['randomuser'];
$HisSexIS = str_replace(['boy','girl'],['b','g'],mysqli_fetch_assoc(mysqli_query($MySQLi, "SELECT * FROM `user` WHERE `id` = '{$fromid}' LIMIT 1"))['sex']);
$TypeOfQ .= '_'.$HisSexIS;
$GetAllQArray = mysqli_fetch_all(mysqli_query($MySQLi, "SELECT `question` FROM `questions` WHERE `type` = '{$TypeOfQ}'"));
$QuestionMessage = $GetAllQArray[array_rand($GetAllQArray)][0];
$QuTypeFa = str_replace(['h_1_b','h_1_g','h_18_b','h_18_g','j_1_b','j_1_g','j_18_b','j_18_g'],['حقیقت عادی پسر','حقیقت عادی دختر','حقیقت +18 پسر','حقیقت +18 دختر','جرأت عادی پسر','جرأت عادی دختر','جرأت +18 پسر','جرأت +18 دختر'],$TypeOfQ);
LampStack('editmessagetext',[
'chat_id'=>$fromid,
'message_id'=>$messageid,
'text'=> "$QuTypeFa

$QuestionMessage

چت با حریف باز شد (میتونی با حریفت صحبت کنی)",
]);
LampStack('sendMessage',[
'chat_id'=> $OnePlayer,
'text'=> "حریفت $QuTypeFa رو انتخاب کرد

$QuestionMessage

چت با حریف باز شد (میتونی با حریفت صحبت کنی)",
'parse_mode'=>'HTML',
]);
$MySQLi->query("UPDATE `user` SET `bool` = 2 WHERE `id` = '{$fromid}' LIMIT 1");
$MySQLi->query("UPDATE `user` SET `bool` = 3 WHERE `id` = '{$OnePlayer}' LIMIT 1");
ToDie($MySQLi);
}
}
if($UserDataBase['step'] == 'InGame' and $msg == 'مشخصات حریف ⁉️'){
$HisID = $UserDataBase['randomuser'];
$HisData = mysqli_fetch_assoc(mysqli_query($MySQLi, "SELECT * FROM `user` WHERE `id` = '{$HisID}' LIMIT 1"));
$HisNAme = $HisData['name'];
$CountOfCons = $HisData['coin'];
$YourSex = str_replace(['boy','girl'],['پسر','دختر'],$HisData['sex']);
$YourAge = $HisData['age'];
if(getUserProfilePhotos($HisID)->photos[0][0]->file_id !== null){
LampStack('sendphoto', [
'chat_id' => $from_id,
'photo' => getUserProfilePhotos($HisID)->photos[0][0]->file_id,
'caption' => "
👤 نام : $HisNAme
👀 جنسیت : $YourSex
✨ سن : $YourAge
",
'parse_mode' => "html",
]);
}else{
LampStack('sendphoto', [
'chat_id' => $from_id,
'photo' => new CURLFile('default.png'),
'caption' => "
👤 نام : $HisNAme
👀 جنسیت : $YourSex
✨ سن : $YourAge
",
'parse_mode' => "html",
]);
}
ToDie($MySQLi);
}
if($UserDataBase['step'] == 'InGame' and $msg == 'لغو بازی ❌'){
LampStack('sendMessage',[
'chat_id'=>$from_id,
'text'=> 'بازی فعلی رو قطع کنم؟',
'parse_mode'=>"HTML",
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=>'خیر ❌','callback_data'=>'NopeContenue'],['text'=>'بله ✅','callback_data'=>'BackToMainMenu']],
]
])
]);
ToDie($MySQLi);
}
if($UserDataBase['step'] == 'InGame' and $UserDataBase['bool'] == 2){
if(isset($message->document)){
$file_id = $message->document->file_id;
$SendType = 'document';
$ArrType = 'document';
}
elseif(isset($message->voice)){
$file_id = $message->voice->file_id;
$SendType = 'voice';
$ArrType = 'voice';
}
elseif(isset($message->video)){
$file_id = $message->video->file_id;
$SendType = 'video';
$ArrType = 'video';
}
elseif(isset($message->video_note)){
$file_id = $message->video_note->file_id;
$SendType = 'videonote';
$ArrType = 'video_note';
}
elseif(isset($message->audio)){
$file_id = $message->audio->file_id;
$SendType = 'audio';
$ArrType = 'audio';
}
elseif(isset($message->sticker)){
$file_id = $message->sticker->file_id;
$SendType = 'sticker';
$ArrType = 'sticker';
}
elseif(isset($message->photo)){
$photo = $message->photo;
$file_id = $photo[count($photo)-1]->file_id;
$SendType = 'photo';
$ArrType = 'photo';
}
elseif(isset($message->gif)){
$file_id = $message->gif->file_id;
$SendType = 'gif';
$ArrType = 'gif';
}
else{
$file_id = $msg;
$SendType = 'message';
$ArrType = 'text';
}
LampStack('send'.$SendType,[
'chat_id'=> $UserDataBase['randomuser'],
$ArrType => $file_id,
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=> 'جواب داد ✅','callback_data'=>'AcceptQuestnG']],
]
])
]);
ToDie($MySQLi);
}
if($UserDataBase['step'] == 'InGame' and $UserDataBase['bool'] == 3 and !$data){
if(isset($message->document)){
$file_id = $message->document->file_id;
$SendType = 'document';
$ArrType = 'document';
}
elseif(isset($message->voice)){
$file_id = $message->voice->file_id;
$SendType = 'voice';
$ArrType = 'voice';
}
elseif(isset($message->video)){
$file_id = $message->video->file_id;
$SendType = 'video';
$ArrType = 'video';
}
elseif(isset($message->audio)){
$file_id = $message->audio->file_id;
$SendType = 'audio';
$ArrType = 'audio';
}
elseif(isset($message->video_note)){
$file_id = $message->video_note->file_id;
$SendType = 'videonote';
$ArrType = 'video_note';
}
elseif(isset($message->sticker)){
$file_id = $message->sticker->file_id;
$SendType = 'sticker';
$ArrType = 'sticker';
}
elseif(isset($message->photo)){
$photo = $message->photo;
$file_id = $photo[count($photo)-1]->file_id;
$SendType = 'photo';
$ArrType = 'photo';
}
elseif(isset($message->gif)){
$file_id = $message->gif->file_id;
$SendType = 'gif';
$ArrType = 'gif';
}
else{
$file_id = $msg;
$SendType = 'message';
$ArrType = 'text';
}
LampStack('send'.$SendType,[
'chat_id'=> $UserDataBase['randomuser'],
$ArrType => $file_id,
'parse_mode'=>"HTML",
]);
ToDie($MySQLi);
}
if($UserDataBase['step'] == 'InGame' and $UserDataBase['bool'] == 3 and $data == 'AcceptQuestnG'){
$OnePlayer = $UserDataBase['randomuser'];
$MySQLi->query("UPDATE `user` SET `bool` = 1 WHERE `id` = '{$fromid}' LIMIT 1");
$MySQLi->query("UPDATE `user` SET `bool` = 0 WHERE `id` = '{$OnePlayer}' LIMIT 1");
LampStack('sendMessage',[
'chat_id'=>$OnePlayer,
'text'=> 'حریف جوابتون رو تایید کرد ✅',
'parse_mode'=>"HTML",
]);
LampStack('answercallbackquery', [
'callback_query_id' => $update->callback_query->id,
'text' => 'جواب حریف تایید شد ✅',
'show_alert' => true
]);
LampStack('sendMessage',[
'chat_id'=> $fromid,
'text'=> 'یه سوال انتخاب کن :',
'parse_mode'=>'HTML',
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=> 'حقیقت عادی','callback_data'=>'Select_h_1'],['text'=> 'حقیقت +18','callback_data'=>'Select_h_18']],
[['text'=> 'جرأت عادی','callback_data'=>'Select_j_1'],['text'=> 'جرأت +18','callback_data'=>'Select_j_18']],
]
])
]);
ToDie($MySQLi);
}





} // TC PRIVATE


// INLINE  

if(isset($update->inline_query) and $inline_query == null){
$GameID = RandomString();
$DateIs = time();
$MySQLi->query("INSERT INTO `gps` (`id`,`turn`,`date`,`creator`) VALUES ('{$GameID}','{$inline_from_id}','{$DateIs}','{$inline_from_id}')");
$randcode = RandomString();
$MySQLi->query("INSERT INTO `ugps` (`randcode`,`id`,`userid`,`change`,`name`) VALUES ('{$randcode}','{$GameID}','{$inline_from_id}',0,'{$inline_first_name}')");
$ListOFChannels = '';
foreach($LockChannelsUserName as $value){
$ListOFChannels .= '🆔 '.$value."\n";
}
LampStack('answerInlineQuery', [
'inline_query_id' => $inline_query_id,
'cache_time'=>0,
'results' => json_encode([
[
'id' => base64_encode(rand(5,99999)),
'type' => 'article',
'thumb_url'=>'https://s6.uupload.ir/files/unnamed_jue6.png',
'description' => 'با کلیک روی این دکمه یک پیام به گروه / پیوی مورد نظر ارسال میشه که میتونید بصورت دونفره یا گروهی با دوستاتون بازی کنید.',
'title' => 'برای شروع بازی کلیک کنید',
'input_message_content'=>['message_text'=> "سلام🙂
شما به چالش جرعت حقیقت دعوت شدید 😍✨

اگه پایه چالشی روی دکمه پایه ام بزن😎


فقط قبلش عضو کانال های زیر بشو😁

$ListOFChannels

اعضای چالش :
1 - $inline_first_name"],
'reply_markup'=>['inline_keyboard'=>[
[['text'=> 'پایه ام 😍➕','callback_data'=>'ImInGame_'.$GameID],['text'=> 'شروع بازی 💣','callback_data'=>'StartTheGame_'.$GameID]],
[['text'=>'ربات بازی جرعت حقیقت','url'=>'https://t.me/'.BOT_USERNAME]],
]]
]
])
]);
ToDie($MySQLi);
}
if(isset($update->inline_query) and $inline_query !== null){
if($inline_from_id != mysqli_fetch_assoc(mysqli_query($MySQLi, "SELECT * FROM `gps` WHERE `id` = '{$inline_query}' LIMIT 1"))["creator"]) ToDie($MySQLi);
$TurnID = mysqli_fetch_assoc(mysqli_query($MySQLi, "SELECT * FROM `gps` WHERE `id` = '{$inline_query}' LIMIT 1"))["turn"];
$TurnName = mysqli_fetch_assoc(mysqli_query($MySQLi, "SELECT * FROM `ugps` WHERE `userid` = '{$TurnID}' LIMIT 1"))["name"];
$MenTionUser = "[$TurnName](tg://user?id=$TurnID)";
LampStack('answerInlineQuery', [
'inline_query_id' => $inline_query_id,
'cache_time'=>0,
'results' => json_encode([
[
'id' => base64_encode(rand(5,99999)),
'type' => 'article',
'description' => "ارسال بازی با شناسه $inline_query به پایین صفحه چت.",
'title' => 'ارسال بازی به پایین',
'input_message_content'=>['message_text'=> "نوبت $MenTionUser هست 😄

جرأت یا حقیقت ؟",'parse_mode'=>'MarkDown'],
'reply_markup'=>['inline_keyboard'=>[
[['text'=> 'حقیقت','callback_data'=>'SelectTrue_'.$inline_query],['text'=> 'جرأت','callback_data'=>'SelectJorat_'.$inline_query]],
[['text'=> 'نفر بعدی ↪️','callback_data'=>'SkipIt_'.$inline_query],['text'=> 'اخراج کاربر 📛','callback_data'=>'KikIT_'.$inline_query]],
[['text'=> '📛 لغو بازی 📛','callback_data'=>'EndIt_'.$inline_query]],
]]
]
])
]);
ToDie($MySQLi);
}
// Paye  

if(explode('_',$data)[0] == 'ImInGame'){
$GameID = explode('_',$data)[1];
if(mysqli_fetch_assoc(mysqli_query($MySQLi, "SELECT * FROM `gps` WHERE `id` = '{$GameID}' LIMIT 1"))['date'] + 5 > time()){
LampStack('answercallbackquery', [
'callback_query_id' => $update->callback_query->id,
'text' => 'لطفا کمی صبر کنید !',
'show_alert' => true
]);
ToDie($MySQLi);
}
if(!mysqli_fetch_assoc(mysqli_query($MySQLi, "SELECT * FROM `gps` WHERE `id` = '{$GameID}' LIMIT 1"))){
LampStack('answercallbackquery', [
'callback_query_id' => $update->callback_query->id,
'text' => 'این بازی حذف شده است.',
'show_alert' => true
]);
ToDie($MySQLi);
}
$GetAllUsers = mysqli_fetch_all(mysqli_query($MySQLi, "SELECT `userid` FROM `ugps` WHERE `id` = '{$GameID}' limit 30"));
$PlayersCount = count($GetAllUsers);
foreach($GetAllUsers as $value){
if($value[0] == $fromid){
LampStack('answercallbackquery', [
'callback_query_id' => $update->callback_query->id,
'text' => 'شما از قبل در بازی حضور دارید !',
'show_alert' => true
]);
ToDie($MySQLi);
}
}
if($PlayersCount > 30){
LampStack('answercallbackquery', [
'callback_query_id' => $update->callback_query->id,
'text' => 'این بازی به حداکثر اعضای خود رسیده , لطفا بازی دیگری را شروع کنید.',
'show_alert' => true
]);
ToDie($MySQLi);
}
if($PlayersCount < 6){
$randcode = RandomString();
$MySQLi->query("INSERT INTO `ugps` (`randcode`,`id`,`userid`,`change`,`name`) VALUES ('{$randcode}','{$GameID}','{$fromid}',0,'{$firstname}')");
LampStack('answercallbackquery', [
'callback_query_id' => $update->callback_query->id,
'text' => 'رواله ...
صبر کن سازنده بازی رو شروع کنه 👀',
'show_alert' => false
]);
$ListOFNames = '';
$counter = 1;
$GameInfo = mysqli_fetch_all(mysqli_query($MySQLi, "SELECT `name` FROM `ugps` WHERE `id` = '{$GameID}'"));
foreach($GameInfo as $name){
$ListOFNames .= $counter.' - '.$name[0]."\n";
$counter++;
}
$ListOFChannels = '';
foreach($LockChannelsUserName as $value){
$ListOFChannels .= '🆔 '.$value."\n";
}
$message_text = "سلام🙂
شما به چالش جرعت حقیقت دعوت شدید 😍✨

اگه پایه چالشی روی دکمه پایه ام بزن😎


فقط قبلش عضو کانال های زیر بشو😁

$ListOFChannels

اعضای چالش :
".$ListOFNames;
LampStack('editMessageText',[
'inline_message_id' => $inline_message_id,
'text'=> $message_text,
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=> 'پایه ام 😍➕','callback_data'=>'ImInGame_'.$GameID],['text'=> 'شروع بازی 💣','callback_data'=>'StartTheGame_'.$GameID]],
[['text'=>'ربات بازی جرعت حقیقت','url'=>'https://t.me/'.BOT_USERNAME]],
]
])
]);
}else{
$COFP = $PlayersCount - 5;
$randcode = RandomString();
$MySQLi->query("INSERT INTO `ugps` (`randcode`,`id`,`userid`,`change`,`name`) VALUES ('{$randcode}','{$GameID}','{$fromid}',0,'{$firstname}')");
LampStack('answercallbackquery', [
'callback_query_id' => $update->callback_query->id,
'text' => 'اسم شما در لیست بازیکنان این بازی ثبت شد , لطفا منتظر شروع بازی بمانید 🤠',
'show_alert' => false
]);
$ListOFNames = '';
$counter = 1;
$GameInfo = mysqli_fetch_all(mysqli_query($MySQLi, "SELECT `name` FROM `ugps` WHERE `id` = '{$GameID}'"));
foreach($GameInfo as $name){
if($counter >= 6) break;
$ListOFNames .= $counter.' - '.$name[0]."\n";
$counter++;
}
$ListOFChannels = '';
foreach($LockChannelsUserName as $value){
$ListOFChannels .= '🆔 '.$value."\n";
}
$message_text = "سلام سلام 😃👐🏻
بیاید جرأت حقیقت بازی کنیم 🤤

🙋🏻 کی پایست بازی کنیم 🙋🏻‍♂️

اگه پایه اید بزنید رو دکمه زیر تا به بازی اضافتون کنم 🤫

فقط قبلش تو کانال های اسپانسر ما عضو بشید :)))

کانال های ما :

$ListOFChannels

اعضای چالش :
".$ListOFNames."\n"."و $COFP نفر دیگر.";
LampStack('editMessageText',[
'inline_message_id' => $inline_message_id,
'text'=> $message_text,
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=> 'پایه ام 😍➕','callback_data'=>'ImInGame_'.$GameID],['text'=> 'شروع بازی 💣','callback_data'=>'StartTheGame_'.$GameID]],
[['text'=>'ربات بازی جرعت حقیقت','url'=>'https://t.me/'.BOT_USERNAME]],
]
])
]);
}
ToDie($MySQLi);
}

// Start 
if(explode('_',$data)[0] == 'StartTheGame'){
$GameID = explode('_',$data)[1];
$GetAllUsers = mysqli_fetch_all(mysqli_query($MySQLi, "SELECT `userid` FROM `ugps` WHERE `id` = '{$GameID}'"));
$PlayersCount = 0;
foreach($GetAllUsers as $value){
$PlayersCount++;
}
if(!mysqli_fetch_assoc(mysqli_query($MySQLi, "SELECT * FROM `gps` WHERE `id` = '{$GameID}' LIMIT 1"))){
LampStack('answercallbackquery', [
'callback_query_id' => $update->callback_query->id,
'text' => 'این بازی حذف شده است.',
'show_alert' => true
]);
ToDie($MySQLi);
}
if($fromid != mysqli_fetch_assoc(mysqli_query($MySQLi, "SELECT * FROM `gps` WHERE `id` = '{$GameID}' LIMIT 1"))["creator"]){
LampStack('answercallbackquery', [
'callback_query_id' => $update->callback_query->id,
'text' => 'شما سازنده این بازی نیستید.',
'show_alert' => false
]);
ToDie($MySQLi);
}
if(count($GetAllUsers) < 2){
LampStack('answercallbackquery', [
'callback_query_id' => $update->callback_query->id,
'text' => 'برای شروع بازی حداقل باید دونفر داخل بازی باشن.',
'show_alert' => true
]);
ToDie($MySQLi);
}
$NoBatName = mysqli_fetch_assoc(mysqli_query($MySQLi, "SELECT * FROM `ugps` WHERE `userid` = '{$fromid}' LIMIT 1"))["name"];
$MenTionUser = "[$NoBatName](tg://user?id=$fromid)";
LampStack('editMessageText',[
'inline_message_id' => $inline_message_id,
'parse_mode'=>'MarkDown',
'text'=> "نوبت $MenTionUser هست 😄

جرأت یا حقیقت ؟",
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=> 'حقیقت','callback_data'=>'SelectTrue_'.$GameID],['text'=> 'جرأت','callback_data'=>'SelectJorat_'.$GameID]],
[['text'=> 'نفر بعدی ↪️','callback_data'=>'SkipIt_'.$GameID],['text'=> 'اخراج کاربر 📛','callback_data'=>'KikIT_'.$GameID]],
[['text'=> '📛 لغو بازی 📛','callback_data'=>'EndIt_'.$GameID]],
]
])
]);
ToDie($MySQLi);
}



// SelectQuestion   

if(explode('_',$data)[0] == 'SelectTrue' or explode('_',$data)[0] == 'SelectJorat'){
$GameID = str_replace(['SelectTrue_','SelectJorat_'],['',''],$data);
if(!mysqli_fetch_assoc(mysqli_query($MySQLi, "SELECT * FROM `gps` WHERE `id` = '{$GameID}' LIMIT 1"))){
LampStack('answercallbackquery', [
'callback_query_id' => $update->callback_query->id,
'text' => 'این بازی حذف شده است.',
'show_alert' => true
]);
ToDie($MySQLi);
}
if($fromid != mysqli_fetch_assoc(mysqli_query($MySQLi, "SELECT * FROM `gps` WHERE `id` = '{$GameID}' LIMIT 1"))["turn"]){
LampStack('answercallbackquery', [
'callback_query_id' => $update->callback_query->id,
'text' => 'نوبت شما نیست.',
'show_alert' => false
]);
ToDie($MySQLi);
}
if(explode('_',$data)[0] == 'SelectTrue')
$MainBTN = json_encode(['inline_keyboard'=>[
[['text'=> 'حقیقت عادی (پسر)','callback_data'=>'GQ-h_1_b-'.$GameID],['text'=> 'حقیقت +18 (پسر)','callback_data'=>'GQ-h_18_b-'.$GameID]],
[['text'=> 'حقیقت عادی (دختر)','callback_data'=>'GQ-h_1_g-'.$GameID],['text'=> 'حقیقت +18 (دختر)','callback_data'=>'GQ-h_18_g-'.$GameID]],
[['text'=> 'برگشت','callback_data'=>'GoBack_'.$GameID]],
[['text'=> 'نفر بعدی ↪️','callback_data'=>'SkipIt_'.$GameID],['text'=> 'اخراج کاربر 📛','callback_data'=>'KikIT_'.$GameID]],
[['text'=> '📛 لغو بازی 📛','callback_data'=>'EndIt_'.$GameID]],
]]);
if(explode('_',$data)[0] == 'SelectJorat')
$MainBTN = json_encode(['inline_keyboard'=>[
[['text'=> 'جرأت عادی (پسر)','callback_data'=>'GQ-j_1_b-'.$GameID],['text'=> 'جرأت +18 (پسر)','callback_data'=>'GQ-j_18_b-'.$GameID]],
[['text'=> 'جرأت عادی (دختر)','callback_data'=>'GQ-j_1_g-'.$GameID],['text'=> 'جرأت +18 (دختر)','callback_data'=>'GQ-j_18_g-'.$GameID]],
[['text'=> 'برگشت','callback_data'=>'GoBack_'.$GameID]],
[['text'=> 'نفر بعدی ↪️','callback_data'=>'SkipIt_'.$GameID],['text'=> 'اخراج کاربر 📛','callback_data'=>'KikIT_'.$GameID]],
[['text'=> '📛 لغو بازی 📛','callback_data'=>'EndIt_'.$GameID]],
]]);
$MenTionUser = "[$firstname](tg://user?id=$fromid)";
LampStack('editMessageText',[
'inline_message_id' => $inline_message_id,
'parse_mode'=>'MarkDown',
'text'=> "نوبت $MenTionUser
سوالتو انتخاب کن :",
'reply_markup'=>$MainBTN
]);
ToDie($MySQLi);
}

// GoBack   

if(explode('_',$data)[0] == 'GoBack'){
$GameID = explode('_',$data)[1];
if(!mysqli_fetch_assoc(mysqli_query($MySQLi, "SELECT * FROM `gps` WHERE `id` = '{$GameID}' LIMIT 1"))){
LampStack('answercallbackquery', [
'callback_query_id' => $update->callback_query->id,
'text' => 'این بازی حذف شده است.',
'show_alert' => true
]);
ToDie($MySQLi);
}
if($fromid != mysqli_fetch_assoc(mysqli_query($MySQLi, "SELECT * FROM `gps` WHERE `id` = '{$GameID}' LIMIT 1"))["turn"]){
LampStack('answercallbackquery', [
'callback_query_id' => $update->callback_query->id,
'text' => 'نوبت شما نیست.',
'show_alert' => false
]);
ToDie($MySQLi);
}
$MenTionUser = "[$firstname](tg://user?id=$fromid)";
LampStack('editMessageText',[
'inline_message_id' => $inline_message_id,
'parse_mode'=>'MarkDown',
'text'=> "نوبت $MenTionUser هست 😄

جرأت یا حقیقت ؟",
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=> 'حقیقت','callback_data'=>'SelectTrue_'.$GameID],['text'=> 'جرأت','callback_data'=>'SelectJorat_'.$GameID]],
[['text'=> 'نفر بعدی ↪️','callback_data'=>'SkipIt_'.$GameID],['text'=> 'اخراج کاربر 📛','callback_data'=>'KikIT_'.$GameID]],
[['text'=> '📛 لغو بازی 📛','callback_data'=>'EndIt_'.$GameID]],
]
])
]);
ToDie($MySQLi);
}
if(explode('_',$data)[0] == 'KikIT'){
$GameID = explode('_',$data)[1];
if(!mysqli_fetch_assoc(mysqli_query($MySQLi, "SELECT * FROM `gps` WHERE `id` = '{$GameID}' LIMIT 1"))){
LampStack('answercallbackquery', [
'callback_query_id' => $update->callback_query->id,
'text' => 'این بازی حذف شده است.',
'show_alert' => true
]);
ToDie($MySQLi);
}
if($fromid != mysqli_fetch_assoc(mysqli_query($MySQLi, "SELECT * FROM `gps` WHERE `id` = '{$GameID}' LIMIT 1"))["creator"]){
LampStack('answercallbackquery', [
'callback_query_id' => $update->callback_query->id,
'text' => 'این دستور فقط توسط سازنده بازی قابل اجراست.',
'show_alert' => false
]);
ToDie($MySQLi);
}
$HisIDIs =  mysqli_fetch_assoc(mysqli_query($MySQLi, "SELECT * FROM `gps` WHERE `id` = '{$GameID}' LIMIT 1"))["turn"];
$HisNameIs = mysqli_fetch_assoc(mysqli_query($MySQLi, "SELECT * FROM `ugps` WHERE `userid` = '{$HisIDIs}' LIMIT 1"))["name"];
if($fromid == $HisIDIs){
LampStack('answercallbackquery', [
'callback_query_id' => $update->callback_query->id,
'text' => 'خطایی رخ داد !',
'show_alert' => true
]);
ToDie($MySQLi);
}
$GetAllUsers = mysqli_fetch_all(mysqli_query($MySQLi, "SELECT `userid` FROM `ugps` WHERE `id` = '{$GameID}' limit 30"));
$PlayersCount = count($GetAllUsers)-1;
if($PlayersCount < 2){
LampStack('answercallbackquery', [
'callback_query_id' => $update->callback_query->id,
'text' => 'خطایی رخ داد !',
'show_alert' => true
]);
ToDie($MySQLi);
}
$GetAllUsers = mysqli_fetch_all(mysqli_query($MySQLi, "SELECT `userid` FROM `ugps` WHERE `id` = '{$GameID}' limit 30"));
$Counter = 0;
$CountOfUsers = count($GetAllUsers)-1;
foreach($GetAllUsers as $value){
if($value[0] == $HisIDIs) break;
$Counter++;
}
$Counter++;
if($Counter > $CountOfUsers) $Counter = 0;
$ItsHisTurn = $GetAllUsers[$Counter][0];
$MySQLi->query("UPDATE `gps` SET `turn` = '{$ItsHisTurn}' WHERE `id` = '{$GameID}' LIMIT 1");
$NoBatName = mysqli_fetch_assoc(mysqli_query($MySQLi, "SELECT * FROM `ugps` WHERE `userid` = '{$ItsHisTurn}' LIMIT 1"))["name"];
$MenTionUser = "[$NoBatName](tg://user?id=$ItsHisTurn)";
LampStack('editMessageText',[
'inline_message_id' => $inline_message_id,
'parse_mode'=>'MarkDown',
'text'=> "نوبت $MenTionUser هست 😄

جرأت یا حقیقت ؟",
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=> 'حقیقت','callback_data'=>'SelectTrue_'.$GameID],['text'=> 'جرأت','callback_data'=>'SelectJorat_'.$GameID]],
[['text'=> 'نفر بعدی ↪️','callback_data'=>'SkipIt_'.$GameID],['text'=> 'اخراج کاربر 📛','callback_data'=>'KikIT_'.$GameID]],
[['text'=> '📛 لغو بازی 📛','callback_data'=>'EndIt_'.$GameID]],
]
])
]);
$MySQLi->query("DELETE FROM `ugps` WHERE `userid` = '{$HisIDIs}'");
LampStack('answercallbackquery', [
'callback_query_id' => $update->callback_query->id,
'text' => "بازیکن $HisNameIs با موفقیت از بازی حذف شد",
'show_alert' => true
]);
ToDie($MySQLi);
}
if(explode('_',$data)[0] == 'EndIt'){
$GameID = explode('_',$data)[1];
if(!mysqli_fetch_assoc(mysqli_query($MySQLi, "SELECT * FROM `gps` WHERE `id` = '{$GameID}' LIMIT 1"))){
LampStack('answercallbackquery', [
'callback_query_id' => $update->callback_query->id,
'text' => 'این بازی حذف شده است.',
'show_alert' => true
]);
ToDie($MySQLi);
}
if($fromid != mysqli_fetch_assoc(mysqli_query($MySQLi, "SELECT * FROM `gps` WHERE `id` = '{$GameID}' LIMIT 1"))["creator"]){
LampStack('answercallbackquery', [
'callback_query_id' => $update->callback_query->id,
'text' => 'این دستور فقط توسط سازنده بازی قابل اجراست.',
'show_alert' => false
]);
ToDie($MySQLi);
}
$MySQLi->query("DELETE FROM `gps` WHERE `id` = '{$GameID}'");
LampStack('answercallbackquery', [
'callback_query_id' => $update->callback_query->id,
'text' => 'بازی با موفقیت خاتمه یافت.',
'show_alert' => true
]);
LampStack('editMessageText',[
'inline_message_id' => $inline_message_id,
'text'=> "بازی با موفقیت پایان یافت !",
]);
ToDie($MySQLi);
}

// Show Group Question

if(explode('-',$data)[0] == 'GQ'){
$GameID = explode('-',$data)[2];
$Qtype = explode('-',$data)[1];
if(!mysqli_fetch_assoc(mysqli_query($MySQLi, "SELECT * FROM `gps` WHERE `id` = '{$GameID}' LIMIT 1"))){
LampStack('answercallbackquery', [
'callback_query_id' => $update->callback_query->id,
'text' => 'این بازی حذف شده است.',
'show_alert' => true
]);
ToDie($MySQLi);
}
if($fromid != mysqli_fetch_assoc(mysqli_query($MySQLi, "SELECT * FROM `gps` WHERE `id` = '{$GameID}' LIMIT 1"))["turn"]){
LampStack('answercallbackquery', [
'callback_query_id' => $update->callback_query->id,
'text' => 'نوبت شما نیست.',
'show_alert' => false
]);
ToDie($MySQLi);
}
$QuTypeFa = str_replace(['h_1_b','h_1_g','h_18_b','h_18_g','j_1_b','j_1_g','j_18_b','j_18_g'],['حقیقت عادی پسر','حقیقت عادی دختر','حقیقت +18 پسر','حقیقت +18 دختر','جرأت عادی پسر','جرأت عادی دختر','جرأت +18 پسر','جرأت +18 دختر'],$Qtype);
$MainQu = mysqli_fetch_all(mysqli_query($MySQLi, "SELECT `question` FROM `questions` WHERE `type` = '{$Qtype}'"));
$MainQText = $MainQu[rand(0,count($MainQu)-1)][0];
$MenTionUser = "[$firstname](tg://user?id=$fromid)";
LampStack('editMessageText',[
'inline_message_id' => $inline_message_id,
'parse_mode'=>'MarkDown',
'text'=> "نوبت $MenTionUser
🎭 $QuTypeFa
--------------------

$MainQText

--------------------
بعد از اینکه به سوال بالا جواب دادی روی گزینه [ثبت پاسخ ✅] کلیک کن.",
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=> 'ثبت پاسخ ✅','callback_data'=>'ITellIT_'.$GameID],['text'=> 'تغییر سوال ♻️','callback_data'=>'ChangeQues_'.$GameID]],
[['text'=> 'نفر بعدی ↪️','callback_data'=>'SkipIt_'.$GameID],['text'=> 'اخراج کاربر 📛','callback_data'=>'KikIT_'.$GameID]],
[['text'=> '📛 لغو بازی 📛','callback_data'=>'EndIt_'.$GameID]],
]
])
]);
ToDie($MySQLi);
}
if(explode('_',$data)[0] == 'ITellIT'){
$GameID = explode('_',$data)[1];
if(!mysqli_fetch_assoc(mysqli_query($MySQLi, "SELECT * FROM `gps` WHERE `id` = '{$GameID}' LIMIT 1"))){
LampStack('answercallbackquery', [
'callback_query_id' => $update->callback_query->id,
'text' => 'این بازی حذف شده است.',
'show_alert' => true
]);
ToDie($MySQLi);
}
if($fromid != mysqli_fetch_assoc(mysqli_query($MySQLi, "SELECT * FROM `gps` WHERE `id` = '{$GameID}' LIMIT 1"))["turn"]){
LampStack('answercallbackquery', [
'callback_query_id' => $update->callback_query->id,
'text' => 'نوبت شما نیست.',
'show_alert' => false
]);
ToDie($MySQLi);
}
$ItsTurn = mysqli_fetch_assoc(mysqli_query($MySQLi, "SELECT * FROM `gps` WHERE `id` = '{$GameID}' LIMIT 1"))["turn"];
$GetAllUsers = mysqli_fetch_all(mysqli_query($MySQLi, "SELECT `userid` FROM `ugps` WHERE `id` = '{$GameID}' limit 30"));
$Counter = 0;
$CountOfUsers = count($GetAllUsers)-1;
foreach($GetAllUsers as $value){
if($value[0] == $ItsTurn) break;
$Counter++;
}
$Counter++;
if($Counter > $CountOfUsers) $Counter = 0;
$ItsHisTurn = $GetAllUsers[$Counter][0];
$MySQLi->query("UPDATE `gps` SET `turn` = '{$ItsHisTurn}' WHERE `id` = '{$GameID}' LIMIT 1");
$MySQLi->query("UPDATE `ugps` SET `change` = 0 WHERE `id` = '{$GameID}' and `userid` = '{$ItsHisTurn}' LIMIT 1");
$NoBatName = mysqli_fetch_assoc(mysqli_query($MySQLi, "SELECT * FROM `ugps` WHERE `userid` = '{$ItsHisTurn}' LIMIT 1"))["name"];
$MenTionUser = "[$NoBatName](tg://user?id=$ItsHisTurn)";
LampStack('editMessageText',[
'inline_message_id' => $inline_message_id,
'parse_mode'=>'MarkDown',
'text'=> "نوبت $MenTionUser هست 😄

جرأت یا حقیقت ؟",
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=> 'حقیقت','callback_data'=>'SelectTrue_'.$GameID],['text'=> 'جرأت','callback_data'=>'SelectJorat_'.$GameID]],
[['text'=> 'نفر بعدی ↪️','callback_data'=>'SkipIt_'.$GameID],['text'=> 'اخراج کاربر 📛','callback_data'=>'KikIT_'.$GameID]],
[['text'=> '📛 لغو بازی 📛','callback_data'=>'EndIt_'.$GameID]],
]
])
]);
ToDie($MySQLi);
}
if(explode('_',$data)[0] == 'SkipIt'){
$GameID = explode('_',$data)[1];
if(!mysqli_fetch_assoc(mysqli_query($MySQLi, "SELECT * FROM `gps` WHERE `id` = '{$GameID}' LIMIT 1"))){
LampStack('answercallbackquery', [
'callback_query_id' => $update->callback_query->id,
'text' => 'این بازی حذف شده است.',
'show_alert' => true
]);
ToDie($MySQLi);
}
if($fromid != mysqli_fetch_assoc(mysqli_query($MySQLi, "SELECT * FROM `gps` WHERE `id` = '{$GameID}' LIMIT 1"))["creator"]){
LampStack('answercallbackquery', [
'callback_query_id' => $update->callback_query->id,
'text' => 'این دستور فقط توسط سازنده بازی قابل اجراست.',
'show_alert' => false
]);
ToDie($MySQLi);
}
$ItsTurn = mysqli_fetch_assoc(mysqli_query($MySQLi, "SELECT * FROM `gps` WHERE `id` = '{$GameID}' LIMIT 1"))["turn"];
$GetAllUsers = mysqli_fetch_all(mysqli_query($MySQLi, "SELECT `userid` FROM `ugps` WHERE `id` = '{$GameID}' limit 30"));
$Counter = 0;
$CountOfUsers = count($GetAllUsers)-1;
foreach($GetAllUsers as $value){
if($value[0] == $ItsTurn) break;
$Counter++;
}
$Counter++;
if($Counter > $CountOfUsers) $Counter = 0;
$ItsHisTurn = $GetAllUsers[$Counter][0];
$MySQLi->query("UPDATE `ugps` SET `change` = 0 WHERE `id` = '{$GameID}' and `userid` = '{$ItsHisTurn}' LIMIT 1");
$MySQLi->query("UPDATE `gps` SET `turn` = '{$ItsHisTurn}' WHERE `id` = '{$GameID}' LIMIT 1");
$NoBatName = mysqli_fetch_assoc(mysqli_query($MySQLi, "SELECT * FROM `ugps` WHERE `userid` = '{$ItsHisTurn}' LIMIT 1"))["name"];
$MenTionUser = "[$NoBatName](tg://user?id=$ItsHisTurn)";
LampStack('editMessageText',[
'inline_message_id' => $inline_message_id,
'parse_mode'=>'MarkDown',
'text'=> "نوبت $MenTionUser هست 😄

جرأت یا حقیقت ؟",
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=> 'حقیقت','callback_data'=>'SelectTrue_'.$GameID],['text'=> 'جرأت','callback_data'=>'SelectJorat_'.$GameID]],
[['text'=> 'نفر بعدی ↪️','callback_data'=>'SkipIt_'.$GameID],['text'=> 'اخراج کاربر 📛','callback_data'=>'KikIT_'.$GameID]],
[['text'=> '📛 لغو بازی 📛','callback_data'=>'EndIt_'.$GameID]],
]
])
]);
LampStack('answercallbackquery', [
'callback_query_id' => $update->callback_query->id,
'text' => 'با موفقیت انجام شد.',
'show_alert' => true
]);
ToDie($MySQLi);
}
if(explode('_',$data)[0] == 'ChangeQues'){
$GameID = explode('_',$data)[1];
if(!mysqli_fetch_assoc(mysqli_query($MySQLi, "SELECT * FROM `gps` WHERE `id` = '{$GameID}' LIMIT 1"))){
LampStack('answercallbackquery', [
'callback_query_id' => $update->callback_query->id,
'text' => 'این بازی به دلایلی حذف شده است.
لطفا یک بازی جدید ایجاد کنید.',
'show_alert' => true
]);
ToDie($MySQLi);
}
if($fromid != mysqli_fetch_assoc(mysqli_query($MySQLi, "SELECT * FROM `gps` WHERE `id` = '{$GameID}' LIMIT 1"))["turn"]){
LampStack('answercallbackquery', [
'callback_query_id' => $update->callback_query->id,
'text' => 'نوبت شما نیست.',
'show_alert' => false
]);
ToDie($MySQLi);
}
$HisChanges = mysqli_fetch_assoc(mysqli_query($MySQLi, "SELECT * FROM `ugps` WHERE `id` = '{$GameID}' and `userid` = '{$fromid}' LIMIT 1"))["change"];
if($HisChanges == 2){
LampStack('answercallbackquery', [
'callback_query_id' => $update->callback_query->id,
'text' => 'شما دو بار سوال رو عوض کردی. در هر مرحله فقط دوبار میتونی سوال رو عوض کنی.',
'show_alert' => true
]);
ToDie($MySQLi);
}
$HisChanges++;
$MySQLi->query("UPDATE `ugps` SET `change` = '{$HisChanges}' WHERE `id` = '{$GameID}' and `userid` = '{$fromid}' LIMIT 1");
$JArray = ['j_1_b','j_1_g','j_18_b','j_18_g'];
$HArray = ['h_1_b','h_1_g','h_18_b','h_18_g'];
$GetRndForMType = rand(1,100);
if($GetRndForMType <= 70) $Qtype = $HArray[rand(0,3)];
if($GetRndForMType > 70) $Qtype = $JArray[rand(0,3)];
$MainQu = mysqli_fetch_all(mysqli_query($MySQLi, "SELECT `question` FROM `questions` WHERE `type` = '{$Qtype}'"));
$MainQText = $MainQu[rand(0,count($MainQu)-1)][0];
$MenTionUser = "[$firstname](tg://user?id=$fromid)";
$QuTypeFa = str_replace(['h_1_b','h_1_g','h_18_b','h_18_g','j_1_b','j_1_g','j_18_b','j_18_g'],['حقیقت عادی پسر','حقیقت عادی دختر','حقیقت +18 پسر','حقیقت +18 دختر','جرأت عادی پسر','جرأت عادی دختر','جرأت +18 پسر','جرأت +18 دختر'],$Qtype);
LampStack('editMessageText',[
'inline_message_id' => $inline_message_id,
'parse_mode'=>'MarkDown',
'text'=> "نوبت $MenTionUser
🎭 $QuTypeFa
--------------------

$MainQText

--------------------
بعد از اینکه به سوال بالا جواب دادی روی گزینه [ثبت پاسخ ✅] کلیک کن.",
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=> 'ثبت پاسخ ✅','callback_data'=>'ITellIT_'.$GameID],['text'=> 'تغییر سوال ♻️','callback_data'=>'ChangeQues_'.$GameID]],
[['text'=> 'نفر بعدی ↪️','callback_data'=>'SkipIt_'.$GameID],['text'=> 'اخراج کاربر 📛','callback_data'=>'KikIT_'.$GameID]],
[['text'=> '📛 لغو بازی 📛','callback_data'=>'EndIt_'.$GameID]],
]
])
]);
LampStack('answercallbackquery', [
'callback_query_id' => $update->callback_query->id,
'text' => 'حله , بهت یک سوال رندوم دادم.',
'show_alert' => true
]);
ToDie($MySQLi);
}

// Admin Panel  

if(in_array($from_id,$BotAdmins)){

if($msg == '/panel' or $msg == 'panel' or $msg == 'Panel' or $msg == 'پنل' or $msg == 'مدیریت' or $msg == 'ادمین'){
if(file_exists('.admin_step')) unlink('.admin_step');
LampStack('sendMessage',[
'chat_id'=> $from_id,
'text'=> 'به پنل مدیریت ربات خوش آمدید.',
'parse_mode'=>'HTML',
'reply_to_message_id'=>$message_id,
'reply_markup' => $admin_panel
]);
ToDie($MySQLi);
}
if($msg == 'آمار ربات'){
$MessageToEdit = LampStack('sendMessage',[
'chat_id'=>$from_id,
'text'=> '<b>...</b>',
'reply_to_message_id'=>$message_id,
'parse_mode'=>"HTML",
]);


$CountAllUsers = $MySQLi->query("SELECT `id` FROM `user`")->num_rows;
$CountBoyUsers = $MySQLi->query("SELECT `id` FROM `user` WHERE `sex` = 'boy'")->num_rows;
$CountGirlUsers = $MySQLi->query("SELECT `id` FROM `user` WHERE `sex` = 'girl'")->num_rows;
$CountbanUsers = $MySQLi->query("SELECT `id` FROM `user` WHERE `step` = 'banned'")->num_rows;
$CountQuestions = $MySQLi->query("SELECT `id` FROM `questions`")->num_rows;
$CountQu_h_1_b = $MySQLi->query("SELECT `id` FROM `questions` WHERE `type` = 'h_1_b'")->num_rows;
$CountQu_h_1_g = $MySQLi->query("SELECT `id` FROM `questions` WHERE `type` = 'h_1_g'")->num_rows;
$CountQu_h_18_b = $MySQLi->query("SELECT `id` FROM `questions` WHERE `type` = 'h_18_b'")->num_rows;
$CountQu_h_18_g = $MySQLi->query("SELECT `id` FROM `questions` WHERE `type` = 'h_18_g'")->num_rows;
$CountQu_j_1_b = $MySQLi->query("SELECT `id` FROM `questions` WHERE `type` = 'j_1_b'")->num_rows;
$CountQu_j_1_g = $MySQLi->query("SELECT `id` FROM `questions` WHERE `type` = 'j_1_g'")->num_rows;
$CountQu_j_18_b = $MySQLi->query("SELECT `id` FROM `questions` WHERE `type` = 'j_18_b'")->num_rows;
$CountQu_j_18_g = $MySQLi->query("SELECT `id` FROM `questions` WHERE `type` = 'j_18_g'")->num_rows;


LampStack('DeleteMessage',[
'chat_id' => $from_id,
'message_id' =>$MessageToEdit->result->message_id,
]);
LampStack('sendMessage',[
'chat_id'=> $from_id,
'text'=> "- کل کاربران ربات : $CountAllUsers
- تعداد کاربران پسر : $CountBoyUsers
- تعداد کاربران دختر : $CountGirlUsers
- تعداد کاربران مسدود : $CountbanUsers

تعداد کل سوالات : $CountQuestions

🔅 حقیقت عادی پسر <b>$CountQu_h_1_b</b>
🔅 حقیقت عادی دختر <b>$CountQu_h_1_g</b>
🔅 حقیقت +18 پسر <b>$CountQu_h_18_b</b>
🔅 حقیقت +18 دختر <b>$CountQu_h_18_g</b>
🔅 جرأت عادی پسر <b>$CountQu_j_1_b</b>
🔅 جرأت عادی دختر <b>$CountQu_j_1_g</b>
🔅 جرأت +18 پسر <b>$CountQu_j_18_b</b>
🔅 جرأت +18 دختر <b>$CountQu_j_18_g</b>",
'parse_mode'=>'HTML',
'reply_to_message_id'=>$message_id,
]);
ToDie($MySQLi);
}
if($msg == 'ارسال همگانی'){
file_put_contents('.admin_step','Send2All');
LampStack('sendMessage',[
'chat_id'=>$from_id,
'text'=> 'پیام خود را برای ارسال همگانی ارسال کنید :',
'parse_mode'=>"HTML",
'reply_to_message_id'=>$message_id,
'reply_markup' => json_encode([
'resize_keyboard' => true,
'keyboard' => [
[['text' => 'پنل']],
]
])
]);
ToDie($MySQLi);
}
if(file_get_contents('.admin_step') == 'Send2All' and $msg !== 'پنل'){
unlink('.admin_step');
@$MySQLi->query("DELETE FROM `cronjob` WHERE `type` = 'send2all' OR `type` = 'for2all'");
$MySQLi->query("INSERT INTO `cronjob` (`type`,`text`,`count`,`fromid`,`msgid`) VALUES ('send2all','{$msg}',0,'{$from_id}',null)");
LampStack('sendMessage',[
'chat_id'=>$from_id,
'text'=> 'ارسال همگانی آغاز شد , لطفا تا پایان عملیات پیام دیگری را ارسال یا فروارد همگانی نکنید.',
'parse_mode'=>"HTML",
'reply_to_message_id'=>$message_id,
'reply_markup' => $admin_panel
]);
ToDie($MySQLi);
}
if($msg == 'فروارد همگانی'){
file_put_contents('.admin_step','For2All');
LampStack('sendMessage',[
'chat_id'=>$from_id,
'text'=> 'پیام خود را برای فروارد همگانی فروارد کنید :',
'parse_mode'=>"HTML",
'reply_to_message_id'=>$message_id,
'reply_markup' => json_encode([
'resize_keyboard' => true,
'keyboard' => [
[['text' => 'پنل']],
]
])
]);
ToDie($MySQLi);
}
if(file_get_contents('.admin_step') == 'For2All' and $msg !== 'پنل'){
unlink('.admin_step');
@$MySQLi->query("DELETE FROM `cronjob` WHERE `type` = 'send2all' OR `type` = 'for2all'");
$MySQLi->query("INSERT INTO `cronjob` (`type`,`text`,`count`,`fromid`,`msgid`) VALUES ('for2all',null,0,'{$from_id}','{$message_id}')");
LampStack('sendMessage',[
'chat_id'=>$from_id,
'text'=> 'فروارد همگانی آغاز شد , لطفا تا پایان عملیات پیام دیگری را ارسال یا فروارد همگانی نکنید.',
'parse_mode'=>"HTML",
'reply_to_message_id'=>$message_id,
'reply_markup' => $admin_panel
]);
ToDie($MySQLi);
}
if($msg == 'تنظیم سکه'){
file_put_contents('.admin_step','ChangeUsersCoins');
LampStack('sendMessage',[
'chat_id'=>$from_id,
'text'=> 'برای افزودن / کسر سکه , لطفا یوزرآیدی (شناسه کاربری) شخص مورد نظر را ارسال کنید.',
'parse_mode'=>"HTML",
'reply_to_message_id'=>$message_id,
'reply_markup' => json_encode([
'resize_keyboard' => true,
'keyboard' => [
[['text' => 'پنل']],
]
])
]);
ToDie($MySQLi);
}
if(file_get_contents('.admin_step') == 'ChangeUsersCoins' and is_numeric($msg) and $msg !== 'پنل'){
unlink('.admin_step');
$HisDataBase = mysqli_fetch_assoc(mysqli_query($MySQLi, "SELECT * FROM `user` WHERE `id` = '{$msg}' LIMIT 1"));
if(!$HisDataBase){
LampStack('sendMessage',[
'chat_id'=>$from_id,
'text'=> 'کاربر مورد نظر در ربات وجود ندارد.',
'parse_mode'=>"HTML",
'reply_to_message_id'=>$message_id,
]);
LampStack('sendMessage',[
'chat_id'=> $from_id,
'text'=> '-به پنل مدیریت ربات خوش آمدید.',
'parse_mode'=>'HTML',
'reply_markup' => $admin_panel
]);
ToDie($MySQLi);
}
$HisNAme = $HisDataBase['name'];
$MenTionUser = "[$HisNAme](tg://user?id=$msg)";
$HisUsercoin = $HisDataBase['coin'];
file_put_contents('.admin_step','ChangeCoinsFrom_'.$msg);
LampStack('sendMessage',[
'chat_id'=>$from_id,
'text'=> "تعداد سکه های کاربر $MenTionUser درحال حاضر مقدار $HisUsercoin عدد است.

اگر قصد تغییر تعداد سکه های فرد را دارید یک عدد وارد کنید تا بعنوان تعداد سکه های جدید کاربر ثبت کنم , درغیر اینصورت از دکمه زیر استفاده کنید.",
'parse_mode'=>"markdown",
'reply_to_message_id'=>$message_id,
'reply_markup' => json_encode([
'resize_keyboard' => true,
'keyboard' => [
[['text' => 'پنل']],
]
])
]);
ToDie($MySQLi);
}
if(explode('_',file_get_contents('.admin_step'))[0] == 'ChangeCoinsFrom' and $msg !== 'پنل' and is_numeric($msg)){
$MainUserID = explode('_',file_get_contents('.admin_step'))[1];
$HisDataBase = mysqli_fetch_assoc(mysqli_query($MySQLi, "SELECT * FROM `user` WHERE `id` = '{$MainUserID}' LIMIT 1"));
$MySQLi->query("UPDATE `user` SET `coin` = '{$msg}' WHERE `id` = '{$MainUserID}' LIMIT 1");
$HisNAme = $HisDataBase['name'];
$MenTionUser = "[$HisNAme](tg://user?id=$MainUserID)";
LampStack('sendMessage',[
'chat_id'=>$from_id,
'text'=> "مقدار $msg سکه برای کاربر $MenTionUser ثبت شد.",
'parse_mode'=>"markdown",
'reply_to_message_id'=>$message_id,
]);
LampStack('sendMessage',[
'chat_id'=>$MainUserID,
'text'=> "تعداد سکه های شما در ربات توسط مدیریت به مقدار $msg تغییر کرد.",
'parse_mode'=>"markdown",
]);
LampStack('sendMessage',[
'chat_id'=> $from_id,
'text'=> '-به پنل مدیریت ربات خوش آمدید.',
'parse_mode'=>'HTML',
'reply_markup' => $admin_panel
]);
unlink('.admin_step');
ToDie($MySQLi);
}

if($msg == 'افزودن سوال'){
file_put_contents('.admin_step','AddQu1');
LampStack('sendMessage',[
'chat_id'=>$from_id,
'text'=> '-لطفا نوع سوالی که قصد دارید به دیتابیس اضافه کنید مشخص نمایید.',
'parse_mode'=>"HTML",
'reply_to_message_id'=>$message_id,
'reply_markup' => json_encode([
'resize_keyboard' => true,
'keyboard' => [
[['text'=> 'حقیقت عادی پسر'],['text'=> 'حقیقت عادی دختر']],
[['text'=> 'حقیقت +18 پسر'],['text'=> 'حقیقت +18 دختر']],
[['text'=> 'جرأت عادی پسر'],['text'=> 'جرأت عادی دختر']],
[['text'=> 'جرأت +18 پسر'],['text'=> 'جرأت +18 دختر']],
[['text' => 'پنل']],
]
])
]);
ToDie($MySQLi);
}
if(file_get_contents('.admin_step') == 'AddQu1' and $msg !== 'پنل'){
if(!in_array($msg,['حقیقت عادی پسر','حقیقت عادی دختر','حقیقت +18 پسر','حقیقت +18 دختر','جرأت عادی پسر','جرأت عادی دختر','جرأت +18 پسر','جرأت +18 دختر'])) ToDie($MySQLi);
$MainQType = str_replace(['حقیقت عادی پسر','حقیقت عادی دختر','حقیقت +18 پسر','حقیقت +18 دختر','جرأت عادی پسر','جرأت عادی دختر','جرأت +18 پسر','جرأت +18 دختر'],['h_1_b','h_1_g','h_18_b','h_18_g','j_1_b','j_1_g','j_18_b','j_18_g'],$msg);
file_put_contents('.admin_step','AddQu2-'.$MainQType);
LampStack('sendMessage',[
'chat_id'=>$from_id,
'text'=> "شما درحال افزودن سوال به $msg هستید , لطفا سوالات خود را یکی یکی ارسال کنید تا در دیتابیس ثبت شوند.
درصورتیکه قصد کنسل کردن پروسه را دارید فقط از دکمه زیر استفاده کنید.",
'parse_mode'=>"HTML",
'reply_to_message_id'=>$message_id,
'reply_markup' => json_encode([
'resize_keyboard' => true,
'keyboard' => [
[['text' => 'پنل']],
]
])
]);
ToDie($MySQLi);
}
if(explode('-',file_get_contents('.admin_step'))[0] == 'AddQu2' and $msg !== 'پنل'){
$MainQType = explode('-',file_get_contents('.admin_step'))[1];
$QuTypeFa = str_replace(['h_1_b','h_1_g','h_18_b','h_18_g','j_1_b','j_1_g','j_18_b','j_18_g'],['حقیقت عادی پسر','حقیقت عادی دختر','حقیقت +18 پسر','حقیقت +18 دختر','جرأت عادی پسر','جرأت عادی دختر','جرأت +18 پسر','جرأت +18 دختر'],$MainQType);
$RandID = rand(11111111,99999999);
$MySQLi->query("INSERT INTO `questions` (`id`,`type`,`question`) VALUES ('{$RandID}','{$MainQType}','{$msg}')");
LampStack('sendMessage',[
'chat_id'=>$ToHaveQuestions,
'text'=> "شناسه سوال در دیتابیس : $RandID
متن سوال :
$msg
نوع سوال : $QuTypeFa",
'parse_mode'=>"HTML",
]);
LampStack('sendMessage',[
'chat_id'=>$from_id,
'text'=> "شناسه سوال در دیتابیس : $RandID
متن سوال :
$msg
نوع سوال : $QuTypeFa

با موفقیت به دیتابیس ربات اضافه شد , درصورت نیاز سوال دیگری را ارسال کنید یا از دکمه زیر به منوی اصلی برگردید.",
'parse_mode'=>"HTML",
'reply_to_message_id'=>$message_id,
'reply_markup' => json_encode([
'resize_keyboard' => true,
'keyboard' => [
[['text' => 'پنل']],
]
])
]);
ToDie($MySQLi);
}
if($msg == 'حذف سوال'){
file_put_contents('.admin_step','DeleteQuestion');
LampStack('sendMessage',[
'chat_id'=>$from_id,
'text'=> 'برای حذف سوال لطفا شناسه آن را وارد کنید.',
'parse_mode'=>"HTML",
'reply_to_message_id'=>$message_id,
'reply_markup' => json_encode([
'resize_keyboard' => true,
'keyboard' => [
[['text' => 'پنل']],
]
])
]);
ToDie($MySQLi);
}
if(file_get_contents('.admin_step') == 'DeleteQuestion' and is_numeric($msg) and $msg !== 'پنل'){
unlink('.admin_step');
$QuestionDB = mysqli_fetch_assoc(mysqli_query($MySQLi, "SELECT * FROM `questions` WHERE `id` = '{$msg}' LIMIT 1"));
if(!$QuestionDB){
LampStack('sendMessage',[
'chat_id'=>$from_id,
'text'=> 'سوال مورد نظر در دیتابیس وجود ندارد.',
'parse_mode'=>"HTML",
'reply_to_message_id'=>$message_id,
]);
LampStack('sendMessage',[
'chat_id'=> $from_id,
'text'=> '-به پنل مدیریت ربات خوش آمدید.',
'parse_mode'=>'HTML',
'reply_markup' => $admin_panel
]);
ToDie($MySQLi);
}
$MySQLi->query("DELETE FROM `questions` WHERE `id` = '{$msg}'");
LampStack('sendMessage',[
'chat_id'=>$from_id,
'text'=> "سوال با شناسه $msg از دیتابیس ربات پاک شد.",
'parse_mode'=>"HTML",
'reply_to_message_id'=>$message_id,
]);
LampStack('sendMessage',[
'chat_id'=> $from_id,
'text'=> '-به پنل مدیریت ربات خوش آمدید.',
'parse_mode'=>'HTML',
'reply_markup' => $admin_panel
]);
ToDie($MySQLi);
}
if($msg == 'مشاهده سوال'){
file_put_contents('.admin_step','ShowAQuestion');
LampStack('sendMessage',[
'chat_id'=>$from_id,
'text'=> 'برای نمایش سوال لطفا شناسه آن را وارد کنید.',
'parse_mode'=>"HTML",
'reply_to_message_id'=>$message_id,
'reply_markup' => json_encode([
'resize_keyboard' => true,
'keyboard' => [
[['text' => 'پنل']],
]
])
]);
ToDie($MySQLi);
}
if(file_get_contents('.admin_step') == 'ShowAQuestion' and is_numeric($msg) and $msg !== 'پنل'){
unlink('.admin_step');
$QuestionDB = mysqli_fetch_assoc(mysqli_query($MySQLi, "SELECT * FROM `questions` WHERE `id` = '{$msg}' LIMIT 1"));
if(!$QuestionDB){
LampStack('sendMessage',[
'chat_id'=>$from_id,
'text'=> 'سوال مورد نظر در دیتابیس وجود ندارد.',
'parse_mode'=>"HTML",
'reply_to_message_id'=>$message_id,
]);
LampStack('sendMessage',[
'chat_id'=> $from_id,
'text'=> '-به پنل مدیریت ربات خوش آمدید.',
'parse_mode'=>'HTML',
'reply_markup' => $admin_panel
]);
ToDie($MySQLi);
}
$QText = $QuestionDB['question'];
$QuTypeFa = str_replace(['h_1_b','h_1_g','h_18_b','h_18_g','j_1_b','j_1_g','j_18_b','j_18_g'],['حقیقت عادی پسر','حقیقت عادی دختر','حقیقت +18 پسر','حقیقت +18 دختر','جرأت عادی پسر','جرأت عادی دختر','جرأت +18 پسر','جرأت +18 دختر'],$QuestionDB['type']);
LampStack('sendMessage',[
'chat_id'=>$from_id,
'text'=> "شناسه سوال در دیتابیس : $msg
متن سوال :
$QText
نوع سوال : $QuTypeFa",
'parse_mode' => "markdown",
'reply_to_message_id'=>$message_id,
]);
LampStack('sendMessage',[
'chat_id'=> $from_id,
'text'=> '-به پنل مدیریت ربات خوش آمدید.',
'parse_mode'=>'HTML',
'reply_markup' => $admin_panel
]);
ToDie($MySQLi);
}
if($msg == 'آزاد کردن کاربر'){
file_put_contents('.admin_step','FreeAuser');
LampStack('sendMessage',[
'chat_id'=>$from_id,
'text'=> 'برای آزاد کردن یک کاربر لطفا یوزرآیدی (شناسه عددی) شخص را ارسال کنید.',
'parse_mode'=>"HTML",
'reply_to_message_id'=>$message_id,
'reply_markup' => json_encode([
'resize_keyboard' => true,
'keyboard' => [
[['text' => 'پنل']],
]
])
]);
ToDie($MySQLi);
}
if(file_get_contents('.admin_step') == 'FreeAuser' and is_numeric($msg) and $msg !== 'پنل'){
unlink('.admin_step');
if(in_array($msg,$BotAdmins)){
LampStack('sendMessage',[
'chat_id'=>$from_id,
'text'=> 'کاربر مورد نظر در ربات ادمین میباشد!',
'parse_mode'=>"HTML",
'reply_to_message_id'=>$message_id,
]);
LampStack('sendMessage',[
'chat_id'=> $from_id,
'text'=> '-به پنل مدیریت ربات خوش آمدید.',
'parse_mode'=>'HTML',
'reply_markup' => $admin_panel
]);
ToDie($MySQLi);
}
$HisDataBase = mysqli_fetch_assoc(mysqli_query($MySQLi, "SELECT * FROM `user` WHERE `id` = '{$msg}' LIMIT 1"));
if(!$HisDataBase){
LampStack('sendMessage',[
'chat_id'=>$from_id,
'text'=> 'کاربر مورد نظر در ربات وجود ندارد.',
'parse_mode'=>"HTML",
'reply_to_message_id'=>$message_id,
]);
LampStack('sendMessage',[
'chat_id'=> $from_id,
'text'=> '-به پنل مدیریت ربات خوش آمدید.',
'parse_mode'=>'HTML',
'reply_markup' => $admin_panel
]);
ToDie($MySQLi);
}
$HisNAme = $HisDataBase['name'];
$MenTionUser = "[$HisNAme](tg://user?id=$msg)";
$MySQLi->query("UPDATE `user` SET `step` = null WHERE `id` = '{$msg}' LIMIT 1");
LampStack('sendMessage',[
'chat_id'=>$from_id,
'text'=> "کاربر $MenTionUser با موفقیت در ربات آزاد شد.",
'parse_mode' => "markdown",
'reply_to_message_id'=>$message_id,
]);
LampStack('sendMessage',[
'chat_id'=>$msg,
'text'=> 'حساب کاربری شما توسط مدیریت آزاد شد.',
'parse_mode'=>"HTML",
]);
LampStack('sendMessage',[
'chat_id'=> $from_id,
'text'=> '-به پنل مدیریت ربات خوش آمدید.',
'parse_mode'=>'HTML',
'reply_markup' => $admin_panel
]);
ToDie($MySQLi);
}
if($msg == 'مسدود کردن کاربر'){
file_put_contents('.admin_step','BanAuser');
LampStack('sendMessage',[
'chat_id'=>$from_id,
'text'=> 'برای بن کردن یک کاربر لطفا یوزرآیدی (شناسه عددی) شخص را ارسال کنید.',
'parse_mode'=>"HTML",
'reply_to_message_id'=>$message_id,
'reply_markup' => json_encode([
'resize_keyboard' => true,
'keyboard' => [
[['text' => 'پنل']],
]
])
]);
ToDie($MySQLi);
}
if(file_get_contents('.admin_step') == 'BanAuser' and is_numeric($msg) and $msg !== 'پنل'){
unlink('.admin_step');
if(in_array($msg,$BotAdmins)){
LampStack('sendMessage',[
'chat_id'=>$from_id,
'text'=> 'کاربر مورد نظر در ربات ادمین میباشد!',
'parse_mode'=>"HTML",
'reply_to_message_id'=>$message_id,
]);
LampStack('sendMessage',[
'chat_id'=> $from_id,
'text'=> '-به پنل مدیریت ربات خوش آمدید.',
'parse_mode'=>'HTML',
'reply_markup' => $admin_panel
]);
ToDie($MySQLi);
}
$HisDataBase = mysqli_fetch_assoc(mysqli_query($MySQLi, "SELECT * FROM `user` WHERE `id` = '{$msg}' LIMIT 1"));
if(!$HisDataBase){
LampStack('sendMessage',[
'chat_id'=>$from_id,
'text'=> 'کاربر مورد نظر در ربات وجود ندارد.',
'parse_mode'=>"HTML",
'reply_to_message_id'=>$message_id,
]);
LampStack('sendMessage',[
'chat_id'=> $from_id,
'text'=> '-به پنل مدیریت ربات خوش آمدید.',
'parse_mode'=>'HTML',
'reply_markup' => $admin_panel
]);
ToDie($MySQLi);
}
$HisNAme = $HisDataBase['name'];
$MenTionUser = "[$HisNAme](tg://user?id=$msg)";
$MySQLi->query("UPDATE `user` SET `step` = 'banned' WHERE `id` = '{$msg}' LIMIT 1");
LampStack('sendMessage',[
'chat_id'=>$from_id,
'text'=> "کاربر $MenTionUser با موفقیت در ربات بن شد.",
'parse_mode' => "markdown",
'reply_to_message_id'=>$message_id,
]);
LampStack('sendMessage',[
'chat_id'=>$msg,
'text'=> 'حساب کاربری شما توسط مدیریت مسدود شد.',
'parse_mode'=>"HTML",
]);
LampStack('sendMessage',[
'chat_id'=> $from_id,
'text'=> '-به پنل مدیریت ربات خوش آمدید.',
'parse_mode'=>'HTML',
'reply_markup' => $admin_panel
]);
ToDie($MySQLi);
}

if($msg == 'مشخصات کاربر'){
file_put_contents('.admin_step','ShowUserDetiles');
LampStack('sendMessage',[
'chat_id'=>$from_id,
'text'=> 'برای دریافت اطلاعات یک کاربر لطفا یوزرآیدی (شناسه عددی) کاربر را ارسال کنید.',
'parse_mode'=>"HTML",
'reply_to_message_id'=>$message_id,
'reply_markup' => json_encode([
'resize_keyboard' => true,
'keyboard' => [
[['text' => 'پنل']],
]
])
]);
ToDie($MySQLi);
}
if(file_get_contents('.admin_step') == 'ShowUserDetiles' and is_numeric($msg) and $msg !== 'پنل'){
unlink('.admin_step');
$HisDataBase = mysqli_fetch_assoc(mysqli_query($MySQLi, "SELECT * FROM `user` WHERE `id` = '{$msg}' LIMIT 1"));
if(!$HisDataBase){
LampStack('sendMessage',[
'chat_id'=>$from_id,
'text'=> 'کاربر مورد نظر در ربات وجود ندارد.',
'parse_mode'=>"HTML",
'reply_to_message_id'=>$message_id,
]);
LampStack('sendMessage',[
'chat_id'=> $from_id,
'text'=> '-به پنل مدیریت ربات خوش آمدید.',
'parse_mode'=>'HTML',
'reply_markup' => $admin_panel
]);
ToDie($MySQLi);
}
$HisNAme = str_replace(['<', '>', '&'], ['&lt;', '&gt;', '&amp;'], $HisDataBase['name']);
$MenTionUser = '<a href="tg://user?id='.$msg.'">'.$HisNAme.'</a>';
$HisUsersex = $HisDataBase['sex'];
$HisUserage = $HisDataBase['age'];
$HisUsercoin = $HisDataBase['coin'];
$HisInviter = $HisDataBase['inviter']?:'توسط کسی به ربات دعوت نشده است.';
if(is_numeric($HisInviter)){
$NoBatName = mysqli_fetch_assoc(mysqli_query($MySQLi, "SELECT * FROM `user` WHERE `id` = '{$HisInviter}' LIMIT 1"))["name"];
$NoBatName = str_replace(['<', '>', '&'], ['&lt;', '&gt;', '&amp;'], $NoBatName);
$HisInviter = '<a href="tg://user?id='.$HisInviter.'">'.$NoBatName.'</a>';
}
if(getUserProfilePhotos($msg)->photos[0][0]->file_id !== null){
LampStack('sendphoto', [
'chat_id' => $from_id,
'photo' => getUserProfilePhotos($msg)->photos[0][0]->file_id,
'caption' => "
▪️نام : $MenTionUser

🔺جنسیت : $HisUsersex

🔺سن : $HisUserage

🔺تعداد سکه ها : $HisUsercoin

🔺دعوت کننده : $HisInviter
",
'parse_mode' => "html",
]);
}else{
LampStack('sendphoto', [
'chat_id' => $from_id,
'photo' => new CURLFile('default.png'),
'caption' => "
▪️نام : $MenTionUser

🔺جنسیت : $HisUsersex

🔺سن : $HisUserage

🔺تعداد سکه ها : $HisUsercoin

🔺دعوت کننده : $HisInviter
",
'parse_mode' => "html",
]);
}
LampStack('sendMessage',[
'chat_id'=> $from_id,
'text'=> '-به پنل مدیریت ربات خوش آمدید.',
'parse_mode'=>'HTML',
'reply_markup' => $admin_panel
]);
ToDie($MySQLi);
}
if($msg == 'برگشت به منوی کاربری'){
if(file_exists('.admin_step')) unlink('.admin_step');
$MessageToEdit = LampStack('sendMessage',[
'chat_id'=>$from_id,
'text'=> 'درحال تعویض به حالت کاربر , درصورتیکه این پیام حذف نشد از دستور /start استفاده کنید.',
'parse_mode'=>"HTML",
'reply_markup'=>json_encode(['KeyboardRemove'=>[
],'remove_keyboard'=>true
])
]);
LampStack('DeleteMessage',[
'chat_id' => $from_id,
'message_id' =>$MessageToEdit->result->message_id,
]);
LampStack('sendMessage',[
'chat_id'=>$from_id,
'text'=> "سلام
به ربات جرعت و حقیقت خوش اومدی !
برای کار با ربات روی دکمه های شیشه ای زیر بزنید :",
'parse_mode'=>"HTML",
'reply_markup'=>$main_menu
]);
ToDie($MySQLi);
}




















ToDie($MySQLi);
} // Admin Access  



















ToDie($MySQLi);