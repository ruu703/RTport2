<?php

//ログを取る、ログファイル出力
ini_set('log_errors','off');
ini_set('error_log','php.log');

//デバッグ

$debug_flg = false;

function debug($str){
    global $debug_flg;
    
    if(!empty($debug_flg)){
        error_log('デバッグ:'.$str);
    }
    
}

//セッション準備・セッション有効期限を延ばす
session_save_path("/var/tmp/");
ini_set('session.gc_maxlifetime',60*60*24*30);
ini_set('session.cookie_lifetime',60*60*24*30);
session_start();
session_regenerate_id();

//画面表示処理開始ログ吐き出し関数
function debugLogStart(){
    debug('☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆画面処理開始');
    debug('セッションID:'.session_id());
    debug('セッション変数の中身:'.print_r($_SESSION,true));
    debug('現在日時タイムスタンプ:'.time());

}

//定数
define('MSG01','入力必須です');
define('MSG02', 'Emailの形式で入力してください');
define('MSG03','パスワード（再入力）が合っていません');
define('MSG04','半角英数字のみご利用いただけます');
define('MSG05','6文字以上で入力してください');
define('MSG06','500文字以内で入力してください');
define('MSG07','エラーが発生しました。しばらく経ってからやり直してください。');
define('MSG08', 'そのEmailは既に登録されています');
define('MSG09', 'メールアドレスまたはパスワードが違います');
define('MSG10', '電話番号の形式が違います');
define('MSG11', '郵便番号の形式が違います');
define('MSG12', '古いパスワードが違います');
define('MSG13', '古いパスワードと同じです');
define('MSG14', '文字で入力してください');
define('MSG15', '選択して下さい。');
define('MSG16', '有効期限が切れています');
define('MSG17', '半角数字のみご利用いただけます');
define('SUC01', 'パスワードを変更しました');
define('SUC02', 'プロフィールを変更しました');
define('SUC03', 'メールを送信しました');
define('SUC04', '登録しました');
define('SUC05', '投稿しました！');

//グローバル変数
$err_msg = array();

//バリデーション関数

//バリデーション（未入力チェック）
function validRequired($str,$key){
    if($str === ''){
        global $err_msg;
        $err_msg[$key] = MSG01;
    }
}

//Email形式チェック
function validEmail($str,$key){
    if(!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/",$str)){
        global $err_msg;
        $err_msg[$key] = MSG02;
    }
}
//Email重複チェック
function validEmailDup($email){
    global $err_msg;
    try{
        $dbh = dbConnect();
        $sql = 'SELECT count(*) FROM users WHERE email = :email AND delete_flg = 0';
        $data = array(':email' => $email);
        $stmt = queryPost($dbh,$sql,$data);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if(!empty(array_shift($result))){
            $err_msg['email'] = MSG08;
            debug('$resultの中身:'.print_r($result,true));
        }
        
    }catch(Exception $e){
        error_log('エラー発生:'.$e->getMessage());
        $err_msg['common'] = MSG07;
    }
}
function validHalf($str,$key){
    if(!preg_match("/^[a-zA-Z0-9]+$/",$str)){
        global $err_msg;
        $err_msg[$key] = MSG04;
    }
}
function validMaxLen($str,$key,$max = 30){
    if(mb_strlen($str)>$max){
        global $err_msg;
        $err_msg[$key] = MSG06;
    }
}
function validMinLen($str,$key,$min = 6){
    if(mb_strlen($str) < $min){
        global $err_msg;
        $err_msg[$key] = MSG05;
    }
}
function validMatch($str1,$str2,$key){
    if($str1 !== $str2){
        global $err_msg;
        $err_msg[$key] = MSG03;
    }
}
function validSelect($str,$key){
    if(!preg_match("/^[1-9]+$/",$str)){
        global $err_msg;
        $err_msg[$key] = MSG15;
    }
}
function validPass($str,$key){
    validHalf($str,$key);
    validMaxLen($str,$key);
    validMinLen($str,$key);
}
function validLength($str,$key,$len = 8){//mb_strlen 文字数を取得
    if(mb_strlen($str) !== $len){
        global $err_msg;
        $err_msg[$key] = $len.MSG14;
    }
}
function getErrMsg($key){
    global $err_msg;
    if(!empty($err_msg[$key])){
        return $err_msg[$key];
    }
}
//データベース
function dbConnect(){
    $dsn = 'mysql:dbname=;host=localhost;charset=utf8';
    $user = '';
    $password = '';
    $options = array(
         // SQL実行失敗時にはエラーコードのみ設定
    PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT,
    // デフォルトフェッチモードを連想配列形式に設定　
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    // バッファードクエリを使う(一度に結果セットをすべて取得し、サーバー負荷を軽減)
    // SELECTで得た結果に対してもrowCountメソッドを使えるようにする
    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
    );
    $dbh = new PDO($dsn,$user,$password,$options);
    return $dbh;
}
function queryPost($dbh,$sql,$data){
    $stmt = $dbh->prepare($sql); //準備 $sql = SQL文
    
    if(!$stmt->execute($data)){//実行 $data = array(); 判定と同時に実行される
        debug('クエリに失敗しました。');
        debug('失敗したSQL:'.print_r($stmt,true));
        $err_msg['common'] = MSG07;
        return 0;
    }else{
        debug('クエリ成功。');
    }
    return $stmt;
}
function getUser($u_id){
    debug('ユーザー情報を取得します。');
    try{
        $dbh = dbConnect();
        $sql = 'SELECT * FROM users WHERE id = :id AND delete_flg = 0';
        $data = array(':id' => $u_id);
        $stmt = queryPost($dbh,$sql,$data);
        
        if($stmt){
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }else{
            return false;
        }
    }catch(Exception $e){
        error_log('エラー発生:'.$e->getMessage());
    }
}
function getNeko($u_id,$n_id){
    debug('ねこちゃん情報を取得します。');
    debug('ユーザーID:'.$u_id);
    debug('ねこちゃんID:'.$n_id);
    
    try{
        $dbh = dbConnect();
        $sql = 'SELECT * FROM cats WHERE user_id = :u_id AND id = :n_id AND delete_flg = 0';
        $data = array(':u_id'=>$u_id,':n_id'=>$n_id);
        $stmt = queryPost($dbh,$sql,$data);
        
        if($stmt){
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }else{
            return false;
        }
    }catch(Exception $e){
        error_log('エラー発生:'.$e->getMessage());
    }
}
function getMyNeko($u_id){
    debug('自分のねこちゃん情報を取得します。');
    debug('ユーザーID:'.$u_id);
    try{
        $dbh = dbConnect();
        $sql = 'SELECT * FROM cats WHERE user_id = :u_id AND delete_flg = 0';
        $data  = array(':u_id' => $u_id);
        $stmt = queryPost($dbh,$sql,$data);
        
        if($stmt){
            return $stmt->fetchAll();
        }else{
            return false;
        }
    }catch(Exception $e){
        error_log('エラー発生:'.$e->getMessage());
    }

}
function getCategory(){
    debug('カテゴリー情報を取得します。');
    try{
        $dbh = dbConnect();
        $sql = 'SELECT * FROM category';
        $data = array();
        $stmt = queryPost($dbh,$sql,$data);
        
        if($stmt){
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }else{
            return false;
        }
    }catch(Exception $e){
        error_log('エラー発生:'.$e->getMessage());
    }
}
function getNekoList($currentMinNum = 1,$type,$sort,$span = 10){
    debug('ねこちゃん情報を取得します。');
    try{
        $dbh = dbConnect();
        $sql = 'SELECT id FROM cats';
        if(!empty($type)) $sql .= ' WHERE type_id = :type';
        $data = array(':type'=>$type);
        $stmt = queryPost($dbh,$sql,$data);
        $rst['total'] = $stmt->rowCount();
        $rst['total_page'] = ceil($rst['total']/$span);
        if(!$stmt){
            return false;
        }
        $sql = 'SELECT c.id AS catid,c.catname,c.gender,c.birth,c.type_id,c.pic1,c.pic2,c.pic3,c.pic4,c.des,c.user_id,c.create_date,a.id,a.name,c.delete_flg FROM cats AS c LEFT JOIN category AS a ON c.type_id = a.id WHERE c.delete_flg = 0' ;
        if(!empty($type)) $sql .= ' AND type_id = '.$type;
        if(!empty($sort)){
            switch($sort){
            case 1:
            $sql .= ' ORDER BY c.create_date DESC';
            break;
            case 2:
            $sql .= ' ORDER BY c.create_date ASC';
            break;        
        }
        }
        $sql .= ' LIMIT '.$span.' OFFSET '.$currentMinNum;
        $data = array();
        debug('SQL:'.$sql);
        $stmt = queryPost($dbh,$sql,$data);
        
        if($stmt){
            $rst['data'] = $stmt->fetchAll();
            return $rst;
        }else{
            return false;
        }
       }catch(Exception $e){
        error_log('エラー発生:'.$e->getMessage());
    }
    }
function getnekoOne($n_id){
    debug('ねこちゃん情報を取得します。');
    debug('ねこちゃんID:'.$n_id);
    try{
        $dbh = dbConnect();
        $sql = 'SELECT c.id,c.catname,c.gender,c.birth,c.type_id,c.pic1,c.pic2,c.pic3,c.pic4,c.des,c.user_id,c.create_date,c.update_date,a.name AS category FROM cats AS c LEFT JOIN category AS a ON c.type_id = a.id  WHERE c.id = :n_id AND c.delete_flg = 0 AND a.delete_flg = 0';
        $data = array(':n_id'=>$n_id);
        $stmt = queryPost($dbh,$sql,$data);
        
        if($stmt){
            return $stmt->fetch();
        }else{
            return false;
        }
    }catch(Exception $e){
        error_log('エラー発生:'.$e->getMessage());
    }
}
//メッセージ主の情報を取得
function getMsg($id){
    debug('相手の情報を取得します');
    try{
        $dbh = dbConnect();
        $sql = 'SELECT u.id, u.username, u.pic, m.id AS m_id, m.send_date,m.user,m.msg FROM users AS u LEFT JOIN message AS m ON  u.id = m.user WHERE m.bord_id = :id AND m.delete_flg = 0 AND u.delete_flg = 0';
        $data = array(':id'=>$id);
        $stmt = queryPost($dbh,$sql,$data);
        
        if($stmt){
            return $stmt->fetch();
        }else{
            return false;
        }
    }catch(Exception $e){
        error_log('エラー発生:'.$e->getMessage());
    }
}

function isLike($u_id,$n_id){
    debug('お気に入り情報があるか確認します。');
    debug('ユーザーID:'.$u_id);
    debug('ねこちゃんID:'.$n_id);
    try{
        $dbh = dbConnect();
        $sql = 'SELECT * FROM `like` WHERE cat_id = :n_id AND user_id = :u_id';
        $data = array(':u_id'=> $u_id,':n_id'=>$n_id);
        $stmt = queryPost($dbh,$sql,$data);
        
        if($stmt->rowCount()){//カウントされた場合=お気に入り登録済み
            debug('スキです');
            return true;
        }else{
            debug('スキではありません');
            return false;
            
        }
        }catch(Exception $e){
        error_log('エラー発生:'.$e->getMessage());
    }
    }
function countLike($n_id){
   // debug('スキの数を確認します。');
   // debug('ねこちゃんID:'.$n_id);
    
    try{
        $dbh = dbConnect();
        $sql = 'SELECT * FROM `like` WHERE cat_id = :n_id AND delete_flg = 0';
        $data = array(':n_id'=>$n_id);
        $stmt = queryPost($dbh,$sql,$data);
        $count = $stmt->rowCount();
        
        if($count){
            //debug('スキの数:'.$count);
            return $count;
        }else{
            return false;
        }
    }catch(Exception $e){
        error_log('エラー発生:'.$e->getMessage());
    }
}
function countType($id){
    //debug('猫種ごとの登録数を取得します。');
    try{
        $dbh = dbConnect();
        $sql = 'SELECT * FROM cats WHERE type_id = :id AND delete_flg = 0';
        $data = array(':id'=>$id);
        $stmt = queryPost($dbh,$sql,$data);
        
        if($stmt){
            return $stmt->rowCount();
        }else{
            return false;
        }
    }catch(Exception $e){
        error_log('エラー発生:'.$e->getMessage());
    }
}
//ログイン認証
function isLogin(){
    if(!empty($_SESSION['login_date'])){
        debug('ログイン済みユーザーです。');
        
        if(($_SESSION['login_date']+$_SESSION['login_limit'])<time()){
            debug('ログイン有効期限オーバーです。');
            
            session_destroy();
            return false;
        }else{
            debug('ログイン有効期限内です。');
            return true;
        }
    }else{
        debug('未ログインユーザーです。');
        return false;
    }
}

//その他
//サニタイズ
function sanitize($str){
    return htmlspecialchars($str,ENT_QUOTES);
}
//フォーム入力保持
function getFormData($str,$flg = false){
    if($flg){
        $method = $_GET;
    }else{
        $method = $_POST; 
    }
    global $dbFormData;
    //ユーザーデータがある場合
    if(!empty($dbFormData)){
        //フォームのエラーがある場合
        if(!empty($err_msg[$str])){
            //POSTにデータがある場合
            if(isset($method[$str])){//$_POST['str']
                return sanitize($method[$str]);
            }else{
                //ない場合（基本ありえない）はDBの情報を表示
                return sanitize($dbFormData[$str]);
            }
        }else{
            //POSTにデータがあり、DBの情報と違う場合
            if(isset($method[$str]) && $method[$str] !== $dbFormData[$str]){
                return sanitize($method[$str]);
            }else{
                return sanitize($dbFormData[$str]);
            }
        }
    }else{
        if(isset($method[$str])){
            return sanitize($method[$str]);
        }
    }
}
function appendGetParam($arr_del_key = array()){//$arr_del_key（引数） は　nekoDetailでn_idを渡している。引数がなければ空の配列
    if(!empty($_GET)){
        $str = '?';
        debug('appendGetParam内$_GET:'.print_r($_GET,true));
        foreach($_GET as $key => $val){
            if(!in_array($key,$arr_del_key,true)){//$key=>n_id
                debug('$appendGetParm内$arr_del_key:'.print_r($arr_del_key,true));
                $str.= $key.'='.$val.'&';
            }
        }
        $str = mb_substr($str,0,-1,"UTF-8");
        debug('$appendGetParm内$str:'.print_r($str,true));
        return $str;
    }
}
function  getMsgsAndBord($id){
    debug('msg情報を取得します。');
    debug('掲示板ID:'.$id);
    
    try{
        $dbh = dbConnect();
        $sql = 'SELECT * FROM bord WHERE id = :id';
        $data = array(':id'=>$id);
        $stmt = queryPost($dbh,$sql,$data);
        $rst = $stmt->fetch(PDO::FETCH_ASSOC);
        debug('掲示板テーブルから取得したdbデータ:'.print_r($rst,true));
        $delete_flg = $rst['delete_flg'];
        debug('掲示板テーブルのdelete_flg:'.print_r($delete_flg,true));
        
        if(!empty($rst)&&(int)$delete_flg === 0){
            debug('メッセージ取得');
            $sql = 'SELECT m.bord_id,m.send_date,m.user,m.msg,m.delete_flg,u.id,u.username,u.pic FROM message AS m LEFT JOIN users AS u ON m.user = u.id WHERE bord_id = :id AND m.delete_flg = 0 ORDER BY send_date ASC';
            $data = array(':id'=>$rst['id']);
            $stmt = queryPost($dbh,$sql,$data);
            $rst['msg'] = $stmt->fetchAll();
            
        }elseif((int)$delete_flg === 1){
            debug('退会済みです');
            return 1;
        }
        if($rst){
            return $rst;
        }else{
            return false;
        }
    }catch(Exception $e){
        error_log('エラー発生:'.$e->getMessage());
    }
}
function getMyMsgsAndBord($u_id){
    debug('自分のメッセージ情報を取得します。');
    try{
        $dbh = dbConnect();
        $sql = 'SELECT * FROM bord AS b WHERE b.owner = :id AND b.delete_flg =  0';
        $data = array(':id'=>$u_id);
        $stmt = queryPost($dbh,$sql,$data);
        $rst = $stmt->fetchAll();
        
        if(!empty($rst)){
            foreach($rst as $key => $val){
                $sql = 'SELECT * FROM message AS m LEFT JOIN users AS u ON m.user = u.id WHERE m.bord_id = :id AND NOT m.user = :u_id AND m.delete_flg = 0 ORDER BY m.send_date DESC';
                $data = array(':id'=>$val['id'],':u_id'=>$u_id);
                $stmt = queryPost($dbh,$sql,$data);
                $rst[$key]['msg'] = $stmt->fetchAll();
            }
        }
        if($stmt){
            return $rst;
        }else{
            return false;
        }
    }catch(Exception $e){
        error_log('エラー発生:'.$e->getMessage());
    }
}

function getmylike($id){
    debug('スキしたねこちゃん情報を取得します');
    
    try{
        $dbh = dbConnect();
        $sql = 'SELECT c.id,c.catname,c.pic1,l.cat_id,l.update_date FROM cats AS c INNER JOIN `like` AS l ON c.id = l.cat_id WHERE l.user_id = :id AND c.delete_flg = 0 AND l.delete_flg = 0 ORDER BY l.update_date DESC';
        $data = array(':id'=>$id);
        $stmt = queryPost($dbh,$sql,$data);
        
        if($stmt){
            return $stmt->fetchall();
        }else{
            return false;
        }
    }catch(Exception $e){
        error_log('エラー発生:'.$e->getMessage());
    }
        
}
//画像処理
function uploadImg($file,$key){
    debug('画像アップロード開始');
    debug('FILE情報:'.print_r($file,true));
    
    if(isset($file['error']) && is_int($file['error'])){
        try{
            switch($file['error']){
                case UPLOAD_ERR_OK://0
                    break;
                case UPLOAD_ERR_NO_FILE://4
                    throw new RuntimeException('ファイルが選択されていません');
                case UPLOAD_ERR_INI_SIZE://1
                case UPLOAD_ERR_FORM_SIZE://2
                    throw new RuntiomeException('ファイルサイズが大きすぎます');
                default:
                    throw new RuntimeException('その他のエラーが発生しました');
            }
            $type = @exif_imagetype($file['tmp_name']);//マイムタイプ取得（ex.[pic1][type]=>image/jpeg　@をつけることでエラーを無視して処理を止めないようにしている
            if(!in_array($type,[IMAGETYPE_GIF,IMAGETYPE_JPEG,IMAGETYPE_PNG],true)){//$typeで取得したマイムタイプと第二引数群が合致しない場合（それ以外の形式の場合）エラーを投げる
                throw new RuntimeException('画像形式未対応です');
            }
            $path = 'uploads/'.sha1_file($file['tmp_name']).image_type_to_extension($type);//$typeで所得した拡張子をつける
            if(!move_uploaded_file($file['tmp_name'],$path)){//ファイルアップロード$file['tmp_name']から$pathへ移動
                throw new RuntimeException('ファイル保持時にエラーが発生しました');
            }
            chmod($path,0644);
            debug('ファイルは正常にアップロードされました');
            debug('ファイルパス:'.$path);
            return $path;
        }catch(RuntimeException $e){
            debug($e->getMessage());
            global $err_msg;
            $err_msg[$key] = $e->getMessage();
        }
    }
}
//画像表示用関数
function showImg($path){
    if(empty($path)){
        return 'img/sample-img.png';
    }else{
        return $path;
    }
}
function getSessionFlash($key){
    if(!empty($_SESSION[$key])){
        $data = $_SESSION[$key];
        $_SESSION[$key] = '';
        return $data;
    }
}
//ページング
function pagination($currentPageNum,$totalPageNum,$link = '',$pageColNum = 5){
    if($currentPageNum == $totalPageNum && $totalPageNum > $pageColNum){
        $minPageNum = $currentPageNum -4;
        $maxPageNum = $currentPageNum;
    }elseif($currentPageNum == ($totalPageNum - 1) && $totalPageNum > $pageColNum){
        $minPageNum = $currentPageNum -3;
        $maxPageNum = $currentPageNum +1;
    }elseif($currentPageNum == 2 && $totalPageNum > $pageColNum){
        $minPageNum = $currentPageNum -1;
        $maxPageNum = $currentPageNum +3;
    }elseif($currentPageNum ==1 && $totalPageNum > $pageColNum){
        $minPageNum = $currentPageNum;
        $maxPageNum = 5;
    }elseif($totalPageNum < $pageColNum){
        $minPageNum = 1;
        $maxPageNum = $totalPageNum;
    }else{
        $minPageNum = $currentPageNum -2;
        $maxPageNum = $currentPageNum +2;
    }
    
    echo '<div class="pagination">';
    echo '<ul class="pagination-list">';
    if($currentPageNum !=1){
        echo '<li class="list-item"><a href="?p=1'.$link.'">&lt;</a></li>';
    }
    for($i = $minPageNum;$i <= $maxPageNum; $i++){
        echo'<li class="list-item ';
        if($currentPageNum == $i){ echo 'active'; }
        echo '"><a href="?p='.$i.$link.'">'.$i.'</a></i>';
    }
    if($currentPageNum != $maxPageNum && $maxPageNum > 1){
        echo '<li class="list-item"><a href="?p='.$maxPageNum.$link.'">&gt;</a></li>';
    }
    echo '</ul>';
    echo '</div>';
}
//メール送信
function sendMail($from,$to,$subject,$comment){
    if(!empty($to) && !empty($subject) && !empty($comment)){
        mb_language("Japanese");
        mb_internal_encoding("UTF-8");
        
        //mb_send_mail(送信先, 題名, 本文, ヘッダ);
        //ヘッダ
        //From: 送信元メールアドレス
        //Reply-to: 送信元メールアドレス
        //CC: CCで送信する送信アドレス
        //BCC: BCCで送信するアドレス
        $result = mb_send_mail($to,$subject,$comment,"From: ".$from);
        
        if($result){
            debug('メールを送信しました。');
        }else{
            debug('エラー発生！　メールの送信に失敗しました。');
        }
    }
}

//認証キー
function makeRandKey($length = 8){
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $str = '';
    for($i = 0;$i < $length;++$i){//8回処理が繰り返される
        $str .= $chars[mt_rand(0,61)];
    }
    return $str;
}
?>