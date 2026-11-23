<?php
require_once 'config.php';
$u=require_login();
csrf_check();
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $body=trim($_POST['body']??'');
    if ($body!=='') {
        $to=$u['role']==='student'?(int)($_POST['teacher_id']??0):(int)($_POST['student_id']??0);
        $st=db()->prepare('SELECT id FROM users WHERE id=? AND role=? AND is_active=1');
        $st->execute([$to,$u['role']==='student'?'teacher':'student']);
        if ($st->fetch()) {
            $st=db()->prepare('INSERT INTO messages(sender_id,receiver_id,body) VALUES(?,?,?)');
            $st->execute([$u['id'],$to,$body]); flash('پیام ارسال شد.');
        } else flash('گیرنده معتبر نیست.','danger');
    }
    redirect('messages.php'.(isset($_POST['with'])?'?with='.(int)$_POST['with']:''));
}
$peopleRole=$u['role']==='student'?'teacher':'student';
$people=db()->prepare('SELECT id,full_name FROM users WHERE role=? AND is_active=1 ORDER BY full_name');
$people->execute([$peopleRole]); $people=$people->fetchAll();
$with=(int)($_GET['with']??($people[0]['id']??0));
$st=db()->prepare('SELECT * FROM users WHERE id=? AND role=?'); $st->execute([$with,$peopleRole]); $peer=$st->fetch();
$msgs=[];
if($peer){$st=db()->prepare('SELECT m.*,u.full_name FROM messages m JOIN users u ON u.id=m.sender_id WHERE (sender_id=? AND receiver_id=?) OR (sender_id=? AND receiver_id=?) ORDER BY m.created_at');$st->execute([$u['id'],$with,$with,$u['id']]);$msgs=$st->fetchAll();}
$pageTitle='ارتباط با استاد'; include 'partials/header.php';
?>
<div class="page-head"><div><span class="section-label">MESSAGES</span><h1>ارتباط با <?= $u['role']==='student'?'استاد':'دانشجویان' ?></h1><p>پیام‌های آموزشی خود را ارسال و دریافت کنید.</p></div></div>
<div class="grid" style="grid-template-columns:260px 1fr"><div class="card"><h3>مخاطبان</h3><?php foreach($people as $p): ?><a class="message <?=$with===$p['id']?'mine':''?>" style="display:block" href="messages.php?with=<?=$p['id']?>"><?=e($p['full_name'])?></a><?php endforeach; ?></div><div class="card"><?php if($peer): ?><h3><?=e($peer['full_name'])?></h3><div style="max-height:390px;overflow:auto"><?php foreach($msgs as $m): ?><div class="message <?=$m['sender_id']===$u['id']?'mine':''?>"><?=nl2br(e($m['body']))?><small><?=e($m['full_name'])?> · <?=e($m['created_at'])?></small></div><?php endforeach; ?><?php if(!$msgs): ?><p class="empty">هنوز پیامی ردوبدل نشده است.</p><?php endif; ?></div><form method="post" style="margin-top:15px"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="with" value="<?=$with?>"><?php if($u['role']==='student'): ?><input type="hidden" name="teacher_id" value="<?=$with?>"><?php else: ?><input type="hidden" name="student_id" value="<?=$with?>"><?php endif; ?><textarea name="body" rows="3" required placeholder="پیام خود را بنویسید..."></textarea><button class="btn primary" style="margin-top:9px">ارسال پیام</button></form><?php else: ?><div class="empty">مخاطبی برای نمایش وجود ندارد.</div><?php endif; ?></div></div>
<?php include 'partials/footer.php'; ?>
