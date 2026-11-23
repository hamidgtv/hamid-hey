<?php
require_once 'config.php';
$u = require_role('teacher');
$queries = [
 'examCount'=>["SELECT COUNT(*) n FROM exams WHERE teacher_id=?",[$u['id']]],
 'publishedCount'=>["SELECT COUNT(*) n FROM exams WHERE teacher_id=? AND status='published'",[$u['id']]],
 'draftCount'=>["SELECT COUNT(*) n FROM exams WHERE teacher_id=? AND status='draft'",[$u['id']]],
 'upcomingCount'=>["SELECT COUNT(*) n FROM exams WHERE teacher_id=? AND status='published' AND starts_at>NOW() AND ends_at>=NOW()",[$u['id']]],
 'activeCount'=>["SELECT COUNT(*) n FROM exams WHERE teacher_id=? AND status='published' AND starts_at<=NOW() AND ends_at>=NOW()",[$u['id']]],
 'attemptCount'=>["SELECT COUNT(*) n FROM attempts a JOIN exams e ON e.id=a.exam_id WHERE e.teacher_id=?",[$u['id']]],
 'studentCount'=>["SELECT COUNT(DISTINCT uc.user_id) n FROM user_classes uc JOIN classes c ON c.id=uc.class_id JOIN users su ON su.id=uc.user_id WHERE c.teacher_id=? AND su.role='student'",[$u['id']]],
];
foreach($queries as $key=>$query){$st=db()->prepare($query[0]);$st->execute($query[1]);$$key=(int)$st->fetch()['n'];}
$st=db()->prepare("SELECT e.*,c.name class_title,(SELECT COUNT(*) FROM questions q WHERE q.exam_id=e.id) qcount,(SELECT COUNT(*) FROM attempts a WHERE a.exam_id=e.id) acount FROM exams e LEFT JOIN classes c ON c.id=e.class_id WHERE e.teacher_id=? ORDER BY e.created_at DESC");
$st->execute([$u['id']]);$exams=$st->fetchAll();$now=time();
$pageTitle='مرکز مدیریت استاد';include 'partials/header.php';
?>
<section class="dash-welcome teacher-welcome">
  <div class="welcome-copy"><div class="welcome-kicker"><span class="live-dot"></span> مرکز مدیریت آموزشی استاد</div><h1>درود استاد <?=e($u['full_name'])?> <span class="welcome-wave">✦</span></h1><p>از طراحی آزمون تا مدیریت کلاس‌ها و ارزیابی پاسخ‌ها؛ همه ابزارهای آموزشی در یک فضای حرفه‌ای و یکپارچه.</p>
    <div class="welcome-meta"><span>⌖ <?=e($u['city'] ?: 'شهر ثبت‌نشده')?></span><span>▦ <?=e(date('Y/m/d'))?></span><span>پنل اختصاصی مدرس</span></div>
    <div class="welcome-actions"><a class="btn welcome-primary" href="create_exam.php">＋ ساخت آزمون جدید <b>←</b></a><a class="welcome-text-link" href="#my-exams">مدیریت آزمون‌ها <span>↓</span></a></div>
  </div>
  <div class="welcome-visual teacher-visual" aria-hidden="true"><div class="visual-ring ring-a"></div><div class="visual-ring ring-b"></div><div class="visual-core"><span>آ</span><small>مرکز مدیریت استاد</small></div><div class="visual-float float-top">✦ <b>مدیریت هوشمند</b></div><div class="visual-float float-bottom">◈ <b>ارزیابی دقیق</b></div></div><div class="welcome-index">۰۲ <i></i> فضای استاد</div>
</section>

<section class="dashboard-metrics teacher-metrics">
 <article class="metric-card metric-main"><div class="metric-top"><span>دانشجویان کلاس‌های من</span><i>♙</i></div><strong><?=$studentCount?></strong><small>دانشجوی عضو کلاس‌ها</small><div class="metric-track"><i style="width:<?=min(100,max(8,$studentCount*3))?>%"></i></div></article>
 <article class="metric-card"><div class="metric-top"><span>کل آزمون‌ها</span><i>▤</i></div><strong><?=$examCount?></strong><small>تمام آزمون‌های ساخته‌شده</small></article>
 <article class="metric-card"><div class="metric-top"><span>در حال برگزاری</span><i>◉</i></div><strong><?=$activeCount?></strong><small>آزمون‌های فعال</small></article>
 <article class="metric-card"><div class="metric-top"><span>آزمون‌های آینده</span><i>◷</i></div><strong><?=$upcomingCount?></strong><small>منتشرشده و زمان‌بندی‌شده</small></article>
 <article class="metric-card"><div class="metric-top"><span>دفعات شرکت</span><i>✓</i></div><strong><?=$attemptCount?></strong><small>مجموع پاسخ‌های ثبت‌شده</small></article>
</section>

<section class="dashboard-section teacher-tools-section"><div class="dashboard-heading"><div><span class="heading-eyebrow">دسترسی سریع به ابزارها</span><h2>ابزارهای مدیریت آموزشی</h2><p>دسترسی سریع به مهم‌ترین بخش‌های پنل استاد</p></div><span class="dashboard-status"><i></i> فضای کاری شما فعال است</span></div>
 <div class="teacher-tool-grid">
  <a class="teacher-tool-card tool-create" href="create_exam.php"><span class="tool-card-icon">＋</span><span class="tool-card-copy"><b>ساخت آزمون جدید</b><small>تعریف سؤال، زمان و تنظیمات آزمون</small></span><i>↗</i></a>
  <a class="teacher-tool-card" href="manage_classes.php"><span class="tool-card-icon">▦</span><span class="tool-card-copy"><b>درس‌ها و کلاس‌ها</b><small>ساخت و سامان‌دهی کلاس‌های آموزشی</small></span><i>↗</i></a>
  <a class="teacher-tool-card" href="students.php"><span class="tool-card-icon">♙</span><span class="tool-card-copy"><b>مدیریت دانشجویان</b><small>مشاهده اعضا و پیگیری وضعیت آموزشی</small></span><i>↗</i></a>
  <a class="teacher-tool-card" href="messages.php"><span class="tool-card-icon">✉</span><span class="tool-card-copy"><b>صندوق پیام‌ها</b><small>ارتباط و پاسخ‌گویی آموزشی</small></span><i>↗</i></a>
 </div>
</section>

<section class="dashboard-section teacher-exams-section" id="my-exams"><div class="dashboard-heading"><div><span class="heading-eyebrow">مرکز مدیریت آزمون‌ها</span><h2>مدیریت آزمون‌ها</h2><p>آزمون‌ها، وضعیت انتشار و آمار شرکت‌کنندگان را مدیریت کنید.</p></div><a class="heading-action" href="create_exam.php">＋ ایجاد آزمون <b>←</b></a></div>
 <div class="teacher-exam-toolbar"><div class="teacher-filter-tabs"><button class="teacher-filter active" type="button" data-filter="all">همه <b><?=$examCount?></b></button><button class="teacher-filter" type="button" data-filter="published">منتشرشده <b><?=$publishedCount?></b></button><button class="teacher-filter" type="button" data-filter="draft">پیش‌نویس <b><?=$draftCount?></b></button></div><span class="teacher-list-caption">آخرین فعالیت‌ها و آزمون‌های شما</span></div>
 <div class="teacher-exam-list teacher-exam-list-premium">
 <?php foreach($exams as $x):$startTs=strtotime($x['starts_at']);$endTs=strtotime($x['ends_at']);if($x['status']==='draft'){$stateText='پیش‌نویس';$stateClass='draft';}elseif($startTs>$now&&$endTs>=$now){$stateText='زمان‌بندی‌شده';$stateClass='upcoming';}elseif($startTs<=$now&&$endTs>=$now){$stateText='در حال برگزاری';$stateClass='active';}else{$stateText='پایان‌یافته';$stateClass='ended';}?>
 <article class="teacher-exam-row" data-status="<?=e($x['status'])?>"><div class="teacher-exam-leading"><div class="teacher-exam-symbol">▤</div><div class="teacher-exam-title"><div class="teacher-exam-title-line"><h3><?=e($x['title'])?></h3><span class="teacher-state <?=$stateClass?>"><i></i><?=$stateText?></span></div><p><?=e($x['class_title']??'همه کلاس‌ها')?> <span>•</span> ایجادشده در <?=e(date('Y/m/d',strtotime($x['created_at'])))?></p></div></div>
  <div class="teacher-exam-numbers"><div><b><?=$x['qcount']?></b><small>سؤال</small></div><div><b><?=$x['acount']?></b><small>شرکت‌کننده</small></div><div><b><?=e($x['duration_minutes'])?></b><small>دقیقه</small></div></div>
  <div class="teacher-exam-dates"><span>شروع <b><?=e(date('Y/m/d H:i',$startTs))?></b></span><span>پایان <b><?=e(date('H:i',$endTs))?></b></span></div>
  <div class="teacher-exam-actions"><a class="teacher-action-secondary" href="edit_questions.php?id=<?=$x['id']?>">ویرایش سؤال‌ها <span>↗</span></a><a class="teacher-action-primary" href="grade.php?exam_id=<?=$x['id']?>">پاسخ‌ها و نمرات <span>←</span></a></div>
  <?php if($x['status']==='draft'):?><div class="teacher-draft-hint">✦ پس از تکمیل سؤال‌ها، آزمون را منتشر کنید تا در فهرست دانشجویان نمایش داده شود.</div><?php endif;?></article>
 <?php endforeach;?>
 <?php if(!$exams):?><div class="empty-dashboard"><div class="empty-illustration">▤</div><h3>هنوز آزمونی ایجاد نکرده‌اید</h3><p>اولین آزمون خود را بسازید و پس از افزودن سؤال‌ها آن را منتشر کنید.</p><a class="btn welcome-primary" href="create_exam.php">ساخت اولین آزمون <b>←</b></a></div><?php endif;?>
 </div>
</section>
<?php include 'partials/footer.php';?>
<script>document.querySelectorAll('[data-filter]').forEach(btn=>btn.addEventListener('click',()=>{document.querySelectorAll('[data-filter]').forEach(x=>x.classList.toggle('active',x===btn));document.querySelectorAll('.teacher-exam-row').forEach(card=>card.hidden=btn.dataset.filter!=='all'&&card.dataset.status!==btn.dataset.filter);}));</script>
