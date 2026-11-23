<?php
require_once 'config.php';$u=require_role('student');
$st=db()->prepare("SELECT a.*,e.title,e.total_score FROM attempts a JOIN exams e ON e.id=a.exam_id WHERE a.student_id=? ORDER BY a.started_at DESC");$st->execute([$u['id']]);$rows=$st->fetchAll();
$pageTitle='کارنامه و نتایج';include 'partials/header.php';
?>
<div class="page-head"><div><span class="section-label">MY ACADEMIC RECORD</span><h1>کارنامه و نتایج آزمون</h1><p>سوابق آزمون‌ها و نمرات ثبت‌شده شما</p></div><a class="btn secondary" href="student.php">بازگشت به پنل</a></div>
<div class="table-wrap"><table class="table"><thead><tr><th>عنوان آزمون</th><th>تاریخ شرکت</th><th>وضعیت</th><th>نمره کسب‌شده</th><th>نمره کل</th></tr></thead><tbody><?php foreach($rows as $r): ?><tr><td><b><?=e($r['title'])?></b></td><td><?=e($r['submitted_at']??$r['started_at'])?></td><td><span class="badge <?=$r['status']==='graded'?'green':'orange'?>"><?=$r['status']==='graded'?'تصحیح‌شده':($r['status']==='submitted'?'در انتظار تصحیح':'در حال انجام')?></span></td><td><?=$r['status']==='graded'?number_format((float)$r['score'],2):'—'?></td><td><?=e($r['total_score'])?></td></tr><?php endforeach; ?><?php if(!$rows): ?><tr><td colspan="5" class="empty">هنوز در آزمونی شرکت نکرده‌اید.</td></tr><?php endif; ?></tbody></table></div>
<?php include 'partials/footer.php'; ?>
