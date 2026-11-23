<?php
require_once 'config.php';
try {
 $pdo=db();
 $makeCode=function() use ($pdo): string {
  do {
   $first9=(string)random_int(100000000,999999999); $sum=0;
   for($i=0;$i<9;$i++) $sum+=(int)$first9[$i]*(10-$i);
   $r=$sum%11; $code=$first9.(string)($r<2?$r:11-$r);
   $q=$pdo->prepare('SELECT id FROM users WHERE national_code=?');$q->execute([$code]);
  } while($q->fetch());
  return $code;
 };
 $code=$makeCode();
 $st=$pdo->prepare("INSERT INTO users(national_code,full_name,role,city,is_active) VALUES(?,?,'teacher',?,1)");
 $st->execute([$code,'مدیر سامانه آموزشی','اهواز']);
 $teacher=(int)$pdo->lastInsertId();
 $pdo->prepare('INSERT INTO classes(name,city,teacher_id) VALUES(?,?,?)')->execute(['کلاس نمونه اهواز','اهواز',$teacher]);
 echo '<div dir="rtl" style="font-family:tahoma;line-height:2"><h2>حساب اولیه استاد ساخته شد</h2><p>کد ملی ورود استاد: <strong style="font-size:24px">'.htmlspecialchars($code).'</strong></p><p>این کد به‌صورت تصادفی برای نصب شما تولید شده است. آن را در جای امن نگه دارید و فایل setup_demo.php را همین حالا حذف کنید.</p><p>نام حساب: مدیر سامانه آموزشی</p><a href="index.php">رفتن به صفحه ورود</a></div>';
} catch(Throwable $e) { http_response_code(500); echo 'خطا در ایجاد حساب اولیه: '.htmlspecialchars($e->getMessage()); }
