<?php
require_once 'config.php';$u=require_role('teacher');csrf_check();$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 $name=trim($_POST['name']??'');$city=trim($_POST['city']??'');
 if($name!==''&&$city!==''){$st=db()->prepare("INSERT INTO classes(name,city,teacher_id) VALUES(?,?,?)");$st->execute([$name,$city,$u['id']]);flash('کلاس جدید ایجاد شد.');redirect('manage_classes.php');}
 $error='نام کلاس و شهر را وارد کنید.';
}
$st=db()->prepare("SELECT * FROM classes WHERE teacher_id=? ORDER BY city,name");$st->execute([$u['id']]);$classes=$st->fetchAll();
$pageTitle='مدیریت کلاس‌ها';include 'partials/header.php';
?>
<div class="page-head"><div><span class="section-label">CLASS MANAGEMENT</span><h1>مدیریت کلاس‌ها</h1><p>کلاس‌های آموزشی خود را تعریف کنید تا دانشجویان بتوانند آن‌ها را انتخاب کنند.</p></div><a class="btn secondary" href="teacher.php">بازگشت</a></div>
<div class="card form-card"><h3>ایجاد کلاس جدید</h3><?php if($error): ?><div class="alert danger"><?=e($error)?></div><?php endif; ?><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><div class="form-grid"><div class="field"><label>نام کلاس</label><input name="name" required placeholder="مثلاً پایه دوازدهم تجربی"></div><div class="field"><label>شهر</label><input name="city" required placeholder="مثلاً تهران"></div></div><button class="btn primary" style="margin-top:16px">＋ ایجاد کلاس</button></form></div>
<div class="card form-card" style="margin-top:18px"><h3>کلاس‌های تعریف‌شده</h3><?php foreach($classes as $c): ?><div class="message"><?=e($c['name'])?><small><?=e($c['city'])?></small></div><?php endforeach; ?><?php if(!$classes): ?><p class="empty">کلاسی ایجاد نشده است.</p><?php endif; ?></div>
<?php include 'partials/footer.php'; ?>
