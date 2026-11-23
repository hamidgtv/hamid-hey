<?php
require_once 'config.php';$u=require_role('student');csrf_check();
if($_SERVER['REQUEST_METHOD']==='POST'){
 $classId=(int)($_POST['class_id']??0);
 $st=db()->prepare("SELECT id,name,city FROM classes WHERE id=?");$st->execute([$classId]);$c=$st->fetch();
 if($c){
  db()->beginTransaction();
  db()->prepare("DELETE FROM user_classes WHERE user_id=?")->execute([$u['id']]);
  db()->prepare("INSERT INTO user_classes(user_id,class_id) VALUES(?,?)")->execute([$u['id'],$classId]);
  db()->prepare("UPDATE users SET city=?,class_name=? WHERE id=?")->execute([$c['city'],$c['name'],$u['id']]);
  db()->commit();
  $_SESSION['user']['city']=$c['city'];$_SESSION['user']['class_name']=$c['name'];
  flash('شهر و کلاس شما با موفقیت به‌روزرسانی شد.');redirect('student.php');
 }
 $error='کلاس انتخاب‌شده معتبر نیست.';
}
$classes=db()->query("SELECT id,name,city FROM classes ORDER BY city,name")->fetchAll();
$pageTitle='انتخاب شهر و کلاس';include 'partials/header.php';
?>
<div class="page-head"><div><span class="section-label">MY EDUCATION</span><h1>انتخاب شهر و کلاس</h1><p>برای مشاهده آزمون‌های مربوط به خود، کلاس آموزشی را انتخاب کن.</p></div><a class="btn secondary" href="student.php">بازگشت</a></div>
<div class="card form-card"><h3>مشخصات آموزشی</h3><?php if(!empty($error)): ?><div class="alert danger"><?=e($error)?></div><?php endif; ?><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><div class="field"><label>شهر و کلاس</label><select name="class_id" required><option value="">انتخاب کنید...</option><?php foreach($classes as $c): ?><option value="<?=$c['id']?>"><?=e($c['city'].' — '.$c['name'])?></option><?php endforeach; ?></select></div><button class="btn primary" style="margin-top:18px">ثبت انتخاب</button></form><?php if(!$classes): ?><p class="muted">هنوز کلاسی تعریف نشده است. از استاد بخواهید کلاس ایجاد کند.</p><?php endif; ?></div>
<?php include 'partials/footer.php'; ?>
