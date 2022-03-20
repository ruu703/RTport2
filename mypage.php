<?php

require('function.php');

debug('☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆');
debug('☆　マイページ ');
debug('☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆');
debugLogStart();

require('auth.php');

$u_id = $_SESSION['user_id'];

$nekoData = getMyNeko($u_id);
//DBからねこちゃんデータを取得

//debug('取得したねこデータ:'.print_r($nekoData,true));

$userInfo = getUser($u_id);

//自分のねこちゃんにもらった掲示板データ
$bordData = getMyMsgsAndBord($u_id);

$mylike = getmylike($_SESSION['user_id']);
debug('$mylike中身:'.print_r($mylike,true));

debug('画面表示処理終了　☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆');
?>

<?php
$siteTitle= 'ねこ図鑑 - マイページ';
require('head.php');
?>
<body>
<div class="title">
          <p id="js-show-msg" style="display:none;" class="msg-slide">
      <?php echo getSessionFlash('msg_success'); ?>
    </p>
        <h1>マイページ</h1>
         </div>
        <div class="form-wrap">
         <div class="form">
          
            <p style="margin:0;text-align:right;">ようこそ
        <?php echo (!empty(sanitize($userInfo['username'])))? $userInfo['username'] : '名無し'; ?>
        さん</p>
           <section class="panel-list">
            <h2>🌟 登録済みねこちゃん一覧</h2> 
            <?php
             if(!empty($nekoData)):
             foreach($nekoData as $key => $val):
             ?>
             <a href="neko.php<?php echo (!empty(appendGetParam())) ? appendGetParam().'&n_id='.$val['id'] : '?n_id='.$val['id']; ?>" class="panel">
                 <div>
                     <img src="<?php echo showImg(sanitize($val['pic1'])); ?>" alt="<?php echo sanitize($val['catname']); ?>">
                 </div>
                 <div>
                     <p><?php echo (sanitize($val['catname'])); ?></p>
                 </div>
                 <div>
              <p>
             <?php
                  if(!empty(countLike($val['id']))) echo '<i class="fas fa-heart fa-lg myp"></i>';
                  if(!empty($val['id'])) echo countLike($val['id']);  ?></p>
             </div>
             </a>
             
             <?php
             endforeach;
             endif;
             ?>
             </section>
             <section class="panel-list">
             <h2>🌟 スキなねこちゃん一覧</h2> 
             <?php
             if(!empty($mylike)):
             foreach($mylike as $key => $val):
             ?>
             <a href="nekoDetail.php<?php echo (!empty(appendGetParam())) ? appendGetParam().'&n_id='.$val['id'] : '?n_id='.$val['id']; ?>" class="panel">
                 <div>
                     <img src="<?php echo showImg(sanitize($val['pic1'])); ?>" alt="<?php echo sanitize($val['catname']); ?>">
                 </div>
                 <div>
                     <p><?php echo (sanitize($val['catname'])); ?></p>
                 </div>
             </a>
             
             <?php
             endforeach;
             endif;
             ?>
             </section>
             <section class="panel-list">
             <h2>🌟 マイねこちゃんに貰ったコメント</h2>
              <table class="table">
                  <thead>
                      <tr>
                          <th>最新送信日時</th>
                          <th>最新送信者名</th>
                          <th>メッセージ</th>
                      </tr>
                  </thead>
                  <tbody>
                      <?php
                      if(!empty($bordData)){
                          foreach($bordData as $key => $val){
                              
                              if(!empty($val['msg'])){
                                  $msg = array_shift($val['msg']);
                                 // debug('$bordData内$val'.print_r($val,true));
                        ?>
                             <tr>
                                 <td><?php echo sanitize(date('Y.m.d H:i:s',strtotime($msg['send_date']))); ?></td>
                                 <td><?php echo (!empty($msg['username']))?sanitize($msg['username']) : '名無しさん'; ?></td>
                                 <td><a href="nekoDetail.php?n_id=<?php echo sanitize($val['cat_id']); ?>"><?php echo mb_substr(sanitize($msg['msg']),0,18); ?>...</a></td><!--先頭から１８文字目までを取得-->
                             </tr>
                             <?php
                              }
                          }
                      }
                      ?>
                  </tbody>
              </table>
               </section>
         </div>
          
          <?php require('side.php'); ?>
    </div>
   
</body>

<?php
         require('footer.php');
        ?>
