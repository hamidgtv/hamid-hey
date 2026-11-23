<?php
$u=current_user(); $title=$pageTitle??'سامانه یکپارچه دانشگاهی';
$flash=get_flash(); $currentPage=basename($_SERVER['PHP_SELF']??'');
$teacherMenu=[
 ['label'=>'نمای کلی','icon'=>'⌂','href'=>'teacher.php','pages'=>['teacher.php']],
 ['label'=>'ساخت آزمون','icon'=>'＋','href'=>'create_exam.php','pages'=>['create_exam.php','edit_questions.php','publish_exam.php']],
 ['label'=>'دانشجویان','icon'=>'♙','href'=>'students.php','pages'=>['students.php']],
 ['label'=>'درس‌ها و کلاس‌ها','icon'=>'▦','href'=>'manage_classes.php','pages'=>['manage_classes.php']],
 ['label'=>'تصحیح و ارزیابی','icon'=>'✓','href'=>'teacher.php#my-exams','pages'=>['grade.php']],
 ['label'=>'پیام‌ها','icon'=>'✉','href'=>'messages.php','pages'=>['messages.php']],
];
$studentMenu=[
 ['label'=>'داشبورد من','icon'=>'⌂','href'=>'student.php','pages'=>['student.php']],
 ['label'=>'آزمون‌های من','icon'=>'▤','href'=>'student.php#available-exams','pages'=>[]],
 ['label'=>'کارنامه تحصیلی','icon'=>'★','href'=>'results.php','pages'=>['results.php']],
 ['label'=>'درس‌ها و کلاس‌های من','icon'=>'▦','href'=>'choose_class.php','pages'=>['choose_class.php']],
 ['label'=>'ارتباط با استاد','icon'=>'✉','href'=>'messages.php','pages'=>['messages.php']],
];
$menuItems=$u?($u['role']==='teacher'?$teacherMenu:$studentMenu):[];
?>
<!doctype html>
<html lang="fa" dir="rtl">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<meta name="theme-color" content="#10131b"><title><?=e($title)?> | سامانه آموزشی</title>
<script>try{document.documentElement.dataset.theme=localStorage.getItem('khuzestan_exam_theme')||'light'}catch(e){}</script>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css?v=30">
</head><body class="<?=$u?'has-sidebar':'guest-layout'?>">
<?php if($u): ?>
<header class="topbar">
 <button class="menu-toggle" type="button" id="menuToggle" aria-label="باز کردن فهرست درس‌ها و بخش‌ها" aria-expanded="false" aria-controls="appDrawer"><span></span><span></span><span></span></button>
 <a class="brand" href="<?=$u['role']==='teacher'?'teacher.php':'student.php'?>"><span class="brand-icon">آ</span><span>سامانه <b>آموزشی</b><small>مدیریت آموزش و آزمون</small></span></a>
 <div class="topbar-center"><span class="topbar-breadcrumb">فضای کاری</span><span class="breadcrumb-divider">/</span><b><?=e($title)?></b></div>
 <div class="topbar-tools">
  <button class="theme-toggle" type="button" data-theme-toggle aria-label="تغییر تم"><span class="theme-toggle-icon">☾</span><span class="theme-toggle-label">حالت شب</span></button>
  <div class="top-user"><div class="avatar"><?=e(mb_substr($u['full_name'],0,1,'UTF-8'))?></div><div><b><?=e($u['full_name'])?></b><small><?=$u['role']==='teacher'?'عضو هیئت علمی':'دانشجو'?></small></div></div>
  <a class="logout" href="logout.php">خروج</a>
 </div>
</header>
<div class="drawer-backdrop" id="drawerBackdrop"></div>
<aside class="app-drawer" id="appDrawer" aria-hidden="true">
 <div class="drawer-head"><div class="drawer-brand"><span class="brand-icon">آ</span><div><b>سامانه آموزشی</b><small>مدیریت آموزش و آزمون</small></div></div><button class="drawer-close" id="drawerClose" type="button" aria-label="بستن فهرست">×</button></div>
 <div class="sidebar-campus"><span class="campus-emblem">✦</span><div><b>پرتال آموزشی</b><small>درگاه یکپارچه آموزشی</small></div><i></i></div>
 <div class="drawer-caption">فهرست درس‌ها و بخش‌های آموزشی</div>
 <nav class="drawer-nav"><?php foreach($menuItems as $item): $active=in_array($currentPage,$item['pages'],true); ?><a class="drawer-link <?=$active?'active':''?>" href="<?=e($item['href'])?>"><span class="drawer-link-icon"><?=e($item['icon'])?></span><span><?=e($item['label'])?></span><span class="drawer-chevron">‹</span></a><?php endforeach; ?></nav>
 <div class="sidebar-help"><span>✧</span><b>همراه مسیر یادگیری</b><small>تمام ابزارهای آموزشی در یک فضای یکپارچه</small></div>
 <div class="drawer-bottom"><div class="drawer-credit">سامانه یکپارچه آموزشی<br><b>طراحی و توسعه: حمید حیدرزاده</b></div><a class="drawer-logout" href="logout.php">↪ خروج از حساب کاربری</a></div>
</aside>
<?php endif; ?>
<main class="<?=$u?'app-main':'guest-main'?>">
<?php if(!$u): ?><button class="theme-toggle guest-theme-toggle" type="button" data-theme-toggle aria-label="تغییر تم"><span class="theme-toggle-icon">☾</span><span class="theme-toggle-label">حالت شب</span></button><?php endif; ?>
<?php if($flash): ?><div class="alert <?=e($flash[1])?>"><?=e($flash[0])?></div><?php endif; ?>
