<?php

require('function.php');

debug('☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆');
debug('☆　ねこちゃん詳細ページ ');
debug('☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆');
debugLogStart();

//debug('$_SESSION中身2:'.$_SESSION);
//GETパラメータからねこちゃんIDを取得して変数へ代入
$n_id = (!empty($_GET['n_id']))? $_GET['n_id'] : '';

//ねこちゃんデータ取得
$nekoData = getnekoOne($n_id);
//debug('$nekoData'.print_r($nekoData,true));

$b_day = 0000-00-00;

if(empty($nekoData)){
    error_log('エラー発生:指定ページに不正な値が入りました');
    header("Location:index.php");
}

//年齢算出
$birthdate = $nekoData['birth'];
                 $now = date("Ymd");
                 $birthday = str_replace("-", "", $birthdate);

//=====================================
//　メッセージ部分画面処理
//=====================================

$viewData = (!empty($n_id)) ? getMsgsAndBord($n_id) : '0';
//debug('取得したDBデータ$viewData:'.print_r($viewData,true));


if(empty($viewData)){
    error_log('エラー発生:指定ページに不正な値が入りました');
    header("Location:index.php");
}

$userData = getUser($viewData['owner']);
//debug('$userDataの中身:'.print_r($userData,true));

$getMessage = getMsg($viewData['id']);
//debug('$getMessage:'.print_r($getMessage,true));

//debug('取得したDBデータ:'.print_r($nekoData,true));

//POSTされていた場合
if(!empty($_POST)){
    debug('POST送信があります。');
    
    require('auth.php');
    
    $msg = (isset($_POST['msg'])) ? $_POST['msg'] : '';
    
    validMaxLen($msg,'msg',500);
    validRequired($msg,'msg');
    
    if(empty($err_msg)){
        debug('バリデーションOKです。');
        try{
            $dbh = dbConnect();
            
            $sql = 'INSERT INTO message (bord_id,send_date,user,msg,create_date) VALUES(:b_id,:send_date,:user,:msg,:date)';
            $data = array(':b_id'=>$n_id,':send_date'=>date('Y-m-d H:i:s'),':user'=>$_SESSION['user_id'],':msg'=>$msg,':date'=>date('Y-m-d H:i:s'));
            $stmt = queryPost($dbh,$sql,$data);
            
            if($stmt){
                $_POST = array();
                debug('$_SESSION中身1:'.print_r($_SESSION,true));
                debug('連絡掲示板へ遷移します。');
                header("Location:".$_SERVER['PHP_SELF'].'?n_id='.$n_id);
            }
        }catch(Exception $e){
            error_log('エラー発生:'.$e->getMessage());
            $err_msg['common'] = MSG07;
        }
    }
}


debug('画面表示処理終了　☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆');
?>

<?php
$siteTitle= 'ねこ図鑑 - 詳細ページ';
require('head.php');
?>
<body>
<style>
    .area-bord{
        max-height:500px;
        overflow-y:scroll;
        background:#fff;
        padding:10px 50px;
    }
    .area-send-msg{
        background:#fff;
        padding:10px 50px 50px 50px;
        overflow:hidden;
    }
    .area-send-msg textarea{
        width:80%;
        float:right;
        background:#eee;
        height:120px;
        padding:15px;
        margin-bottom: 30px;
    }
    .area-bord .msg-cnt{
        width:80%;
        overflow:hidden;
        margin-bottom:30px;
    }
    .area-bord .msg-cnt .avatar{
        overflow:hidden;
        float:left;
    }
    .area-bord .msg-cnt .avatar img{
        width:40px;
        height:40px;
        border-radius:20px;
        float:left;
    } 
    .zukan .avatar img{
        max-width:100px;
        max-height:100px;
        border-radius:5px;
    }
    .area-bord .msg-cnt .msg-inrTxt{
        width:80%;
        float:left;
        border-radius:5px;
        padding:10px;
        margin:0 0 0 25px;
        position:relative;
    }
    .area-bord .msg-cnt.msg-left .msg-inrTxt{
        background:#e8e8ff;
    } 
       .area-bord .msg-cnt.msg-left .msg-inrTxt > .triangle{
        position: absolute;
        left: -20px;
        width: 0;
        height: 0;
        border-top: 10px solid transparent;
        border-right: 15px solid #e8e8ff;
        border-left: 10px solid transparent;
        border-bottom: 10px solid transparent;
      }
      .area-bord .msg-cnt.msg-right{
        float: right;
      }
      .area-bord .msg-cnt.msg-right .msg-inrTxt{
        background: #fffcb6;
        margin: 0 25px 0 0;
      }
      .area-bord .msg-cnt.msg-right .msg-inrTxt > .triangle{
        position: absolute;
        right: -20px;
        width: 0;
        height: 0;
        border-top: 10px solid transparent;
        border-left: 15px solid #fffcb6;
        border-right: 10px solid transparent;
        border-bottom: 10px solid transparent;
      }
      .area-bord .msg-cnt.msg-right .msg-inrTxt{
        float: right;
      }
      .area-bord .msg-cnt.msg-right .avatar{
        float: right;
      }
    .msg-right .name{
        margin:0 0 0 5px;
    }
</style>
<div class="main">
<div class="title">
    
        <h1>詳細ページ</h1>
         </div>
        
       
         <div class="left" style="width:80%;margin:0 auto;">
               <div style="padding-top:30px;">
                 <a href="index.php<?php echo appendGetParam(array('n_id')); ?>" style="text-decoration:underline">&lt;&lt; TOPページへ戻る</a>
              </div>
              
               <div class="zukan-wrap">
                <div class="zukan" style="height:450px;">
                 <img src="<?php echo sanitize($nekoData['pic1']); ?>" class="z-left l-img" id="js-switch-img-main" style=width:600px;max-height:450px;>
                  <div class="z-right l-img" style="text-align:right;margin:20px 40px 0 0;">
                     <?php if(!empty($n_id)) echo countLike($n_id);  ?>スキ
                     <i class="fas fa-heart fa-lg icn-like js-click-like <?php if(isLike($_SESSION['user_id'],$nekoData['id'])){echo 'active';} ?>" data-catid="<?php echo sanitize($nekoData['id']); ?>"></i>
                 </div>
                 <div class="z-right" style="margin-right:20px;">
                 <?php if(!empty($nekoData['pic1']) && !empty($nekoData['pic2']) || !empty($nekoData['pic3']) || !empty($nekoData['pic4'])) echo '<img src="'.sanitize($nekoData['pic1']).'" class="z-left  r-img js-switch-img-sub">'; ?>
                 <?php if(!empty($nekoData['pic2'])) echo '<img  src="'.sanitize($nekoData['pic2']).'" class="z-left  r-img js-switch-img-sub">'; ?> 
                 <?php if(!empty($nekoData['pic3'])) echo '<img  src="'.sanitize($nekoData['pic3']).'" class="z-left  r-img js-switch-img-sub">'; ?>
                 <?php if(!empty($nekoData['pic4'])) echo '<img  src="'.sanitize($nekoData['pic4']).'" class="z-left  r-img js-switch-img-sub">'; ?>
                 </div>
                 </div> 
                 <h2 style="text-align:center;">OWNER &amp; CATINFO</h2>
                 <div class="zukan">
                 <div class="z-left l-img avatar" style="padding:20px;">
                  <span><img src="<?php echo showImg(sanitize($userData['pic'])); ?>"></span><br>
                 <span><?php  echo (!empty(sanitize($userData['username']))) ? '飼い主 : '.sanitize($userData['username']).'さん<br>' : '飼い主 : 名無しさん<br>' ;?></span>
                 <span>名前　: <?php echo sanitize($nekoData['catname']).'<br>'; ?></span>
                 <span>種類　: <?php if(!empty(sanitize($nekoData['category']))) echo sanitize($nekoData['category']).'<br>'; ?></span>
                 <span><?php if(!empty(sanitize($nekoData['gender']))) echo '性別　: '.sanitize($nekoData['gender']).'<br>'; ?></span>
                 <span><?php if(!empty(sanitize($nekoData['birth'])) && sanitize($nekoData['birth']) !== $b_day)  echo '年齢　: '.floor(($now-$birthday)/10000).'歳'.'<br>'; ?></span>
                 </div>
                 <div style="width:100%;">
                 <p><?php echo sanitize($nekoData['des']); ?></p>
                 </div>
                 </div>
                 <section id="main">
                 <h2 style="text-align:center;">掲示板</h2>
                 <div class="area-bord">
                    <?php
                     
                     if(!empty($viewData['msg'])){
                         foreach($viewData['msg'] as $key => $val){
                             //debug('$viewData 展開:'.print_r($val,true));
                        if($viewData['owner'] === $val['user']){
                        ?>
                        
                    
                       <div class="msg-right msg-cnt">
                          <div class="avatar">
                            
                              <img src="<?php echo showImg(sanitize($val['pic'])); ?>" class="avatar">
                        
                          </div>
                          <p class="msg-inrTxt">
                              <span class="triangle"></span>
                              <?php echo sanitize($val['msg']); ?>
                          </p>
                          <div style="font-size:.5em;text-align:right;float:right;"><?php echo sanitize($val['send_date']); ?>
                          <span class="name"><?php echo sanitize($val['username'].'さん'); ?></span></div>
                      </div>
                      <?php
                             }else{
                                  
                      ?>
                      <div class="msg-left msg-cnt">
                          <div class="avatar">
                          
                         <img src="<?php echo showImg(sanitize($val['pic'])); ?>" class="avatar">
                          
                     </div> 
                      <p class="msg-inrTxt">
                          <span class="triangle"></span>
                          <?php echo sanitize($val['msg']); ?>
                      </p>
                      <div style="font-size:.5em;float:left;"><span class="name"><?php echo (!empty(sanitize($val['username']))) ?  sanitize($val['username']).'さん' : '名無しさん'; ?></span>
                      <?php echo sanitize($val['send_date']); ?>
                      </div>
                       </div>
                       <?php
                             }
                         }
                     }elseif($viewData === 1){
                         ?>
                     <p style="font-size:.8em;text-align:center;line-height: 250px;">掲示板はありません</p>
                     <?php
                     }else{
                         ?>
                     <p style="font-size:.8em;text-align:center;line-height: 250px;">飼い主さんにメッセージを送ろう！</p>
                     <?php
                     }
                     ?>
                     
                 </div>
                 <div class="area-send-msg">
                     <form action="" method="post" style="overflow:hidden;">
                         <textarea name="msg" cols="30" rows="3"></textarea>
                         <input type="submit" value="メッセージを送る" class="btn" style="clear:both;">
                         
                     </form>
                 </div>
                 </section>
             </div>
             <div style="padding-top:30px;">
                 <a href="index.php<?php echo appendGetParam(array('n_id')); ?>" style="text-decoration:underline">&lt;&lt; TOPページへ戻る</a>
              </div>
         </div>
         
    
    </div>
   
</body>

<?php
         require('footer.php');
        ?>