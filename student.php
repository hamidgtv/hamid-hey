<?php
require_once 'config.php';
$u = require_role('student');
$st = db()->prepare("SELECT e.*,c.name class_title,a.status attempt_status FROM exams e LEFT JOIN classes c ON c.id=e.class_id LEFT JOIN attempts a ON a.exam_id=e.id AND a.student_id=? WHERE e.status='published' AND e.ends_at>=NOW() AND (e.class_id IS NULL OR e.class_id IN (SELECT class_id FROM user_classes WHERE user_id=?)) ORDER BY e.starts_at ASC");
$st->execute([$u['id'], $u['id']]);
$exams = $st->fetchAll();
$now = time();
$upcomingCount = 0; $activeCount = 0; $completedVisible = 0;
foreach ($exams as $exam) {
    if (strtotime($exam['starts_at']) > $now) $upcomingCount++;
    else $activeCount++;
    if (in_array($exam['attempt_status'], ['submitted','graded'], true)) $completedVisible++;
}
$st = db()->prepare("SELECT COUNT(*) n FROM attempts WHERE student_id=? AND status IN ('submitted','graded')");
$st->execute([$u['id']]); $done = (int)$st->fetch()['n'];
$st = db()->prepare("SELECT AVG(score) avg_score FROM attempts WHERE student_id=? AND status='graded'");
$st->execute([$u['id']]); $avg = $st->fetch()['avg_score'];
$pageTitle = 'فضای آموزشی دانشجو';
include 'partials/header.php';
?>
<section class="dash-welcome student-welcome">
  <div class="welcome-copy">
    <div class="welcome-kicker"><span class="live-dot"></span> فضای اختصاصی یادگیری شما</div>
    <h1>سلام، <?=e($u['full_name'])?> <span class="welcome-wave">✦</span></h1>
    <p>اینجا همه‌چیز برای یک تجربه آموزشی منظم آماده است؛ درس‌ها، آزمون‌ها و مسیر پیشرفتت را یک‌جا دنبال کن.</p>
    <div class="welcome-meta"><span>⌖ <?=e($u['city'] ?: 'شهر ثبت‌نشده')?></span><span>▦ <?=e(date('Y/m/d'))?></span><span>کد دانشجویی: <?=e($u['id'])?></span></div>
    <div class="welcome-actions"><a class="btn welcome-primary" href="choose_class.php">مشاهده درس‌ها و کلاس‌ها <b>←</b></a><a class="welcome-text-link" href="results.php">رفتن به کارنامه <span>↗</span></a></div>
  </div>
  <div class="welcome-visual" aria-hidden="true"><div class="visual-ring ring-a"></div><div class="visual-ring ring-b"></div><div class="visual-core"><span>آ</span><small>یادگیری • پیشرفت</small></div><div class="visual-float float-top">✦ <b>یادگیری هوشمند</b></div><div class="visual-float float-bottom">◎ <b>مسیر پیشرفت</b></div></div>
  <div class="welcome-index">۰۱ <i></i> فضای دانشجو</div>
</section>

<section class="dashboard-metrics student-metrics">
  <article class="metric-card metric-main"><div class="metric-top"><span>آزمون‌های در دسترس</span><i>▤</i></div><strong><?=count($exams)?></strong><small>آزمون منتشرشده برای شما</small><div class="metric-track"><i style="width:<?=count($exams) ? min(100, max(12, count($exams)*12)) : 0?>%"></i></div></article>
  <article class="metric-card"><div class="metric-top"><span>آزمون‌های آینده</span><i>◷</i></div><strong><?=$upcomingCount?></strong><small>در انتظار زمان شروع</small></article>
  <article class="metric-card"><div class="metric-top"><span>آزمون‌های انجام‌شده</span><i>✓</i></div><strong><?=$done?></strong><small>پاسخ‌های ثبت‌شده</small></article>
  <article class="metric-card"><div class="metric-top"><span>میانگین نمرات</span><i>↗</i></div><strong><?= $avg !== null ? number_format((float)$avg, 1) : '—' ?></strong><small>بر اساس نمرات تصحیح‌شده</small></article>
</section>

<section class="dashboard-section student-course-section" id="available-exams">
  <div class="dashboard-heading"><div><span class="heading-eyebrow">فضای یادگیری شما</span><h2>درس‌ها و آزمون‌های من</h2><p>آزمون‌های منتشرشده از همین‌جا قابل مشاهده‌اند؛ حتی اگر هنوز زمان شروعشان نرسیده باشد.</p></div><a class="heading-action" href="choose_class.php">مدیریت درس‌ها <b>←</b></a></div>
  <div class="student-toolbar"><div class="student-tabs" role="group" aria-label="فیلتر آزمون‌ها"><button class="student-tab active" type="button" data-exam-filter="all">همه آزمون‌ها <b><?=count($exams)?></b></button><button class="student-tab" type="button" data-exam-filter="upcoming">به‌زودی <b><?=$upcomingCount?></b></button><button class="student-tab" type="button" data-exam-filter="active">در حال برگزاری <b><?=$activeCount?></b></button><button class="student-tab" type="button" data-exam-filter="done">شرکت‌کرده <b><?=$completedVisible?></b></button></div><span class="student-list-note"><i></i> فهرست به‌روز آزمون‌ها</span></div>
  <?php if ($exams): ?>
  <div class="student-exam-grid student-exam-grid-premium">
    <?php foreach ($exams as $x):
      $startTs = strtotime($x['starts_at']); $endTs = strtotime($x['ends_at']);
      $isUpcoming = $startTs > $now; $alreadyDone = in_array($x['attempt_status'], ['submitted','graded'], true);
      $window = $alreadyDone ? 'done' : ($isUpcoming ? 'upcoming' : 'active');
      $state = $alreadyDone ? 'شرکت کرده‌اید' : ($isUpcoming ? 'زمان‌بندی‌شده' : 'آماده شروع');
    ?>
    <article class="card student-exam-card student-exam-premium" data-exam-window="<?=$window?>">
      <div class="exam-premium-head"><div class="exam-course-icon">▤</div><span class="exam-state-pill <?=$window?>"><i></i><?=$state?></span></div>
      <div class="exam-premium-body"><span class="exam-course-name"><?=e($x['class_title'] ?? 'درس عمومی')?></span><h3><?=e($x['title'])?></h3><p><?=e($x['description'] ?: 'برای مشاهده جزئیات و شرکت در این آزمون، زمان‌بندی را بررسی کنید.')?></p></div>
      <div class="exam-facts"><div><small>مدت آزمون</small><b><?=e($x['duration_minutes'])?> <small>دقیقه</small></b></div><div><small>نمره کل</small><b><?=e($x['total_score'])?></b></div><div><small>تاریخ برگزاری</small><b><?=e(date('Y/m/d', $startTs))?></b></div></div>
      <div class="exam-premium-footer">
        <?php if ($alreadyDone): ?><div class="exam-time-info"><small>وضعیت شما</small><b>پاسخ ثبت شده است</b></div><a class="btn exam-action-btn muted-action" href="results.php">مشاهده نتیجه <span>←</span></a>
        <?php elseif ($isUpcoming): ?><div class="exam-time-info"><small>زمان شروع</small><b><?=e(date('H:i', $startTs))?></b><em><?=e(date('Y/m/d', $startTs))?></em></div><button class="btn exam-action-btn disabled-action" type="button" disabled>هنوز شروع نشده</button>
        <?php else: ?><div class="exam-time-info"><small>مهلت شرکت تا</small><b><?=e(date('H:i', $endTs))?></b><em>پایان آزمون امروز</em></div><a class="btn exam-action-btn start-action" href="take_exam.php?id=<?=$x['id']?>">ورود به آزمون <span>←</span></a><?php endif; ?>
      </div>
    </article>
    <?php endforeach; ?>
  </div>
  <?php else: ?>
  <div class="empty-dashboard"><div class="empty-illustration">▤</div><h3>هنوز آزمونی برای شما منتشر نشده</h3><p>به محض انتشار آزمون توسط استاد، جزئیات و زمان‌بندی آن در همین بخش نمایش داده می‌شود.</p><a class="btn welcome-primary" href="choose_class.php">بررسی درس‌ها و کلاس‌ها <b>←</b></a></div>
  <?php endif; ?>
</section>
<section class="student-bottom-grid">
  <a class="student-shortcut" href="results.php"><span class="shortcut-icon">▥</span><span><b>کارنامه تحصیلی</b><small>بررسی نمرات و نتایج آزمون‌ها</small></span><i>←</i></a>
  <a class="student-shortcut" href="messages.php"><span class="shortcut-icon">✉</span><span><b>ارتباط با استاد</b><small>پیام‌ها و پیگیری آموزشی</small></span><i>←</i></a>
  <a class="student-shortcut" href="choose_class.php"><span class="shortcut-icon">▦</span><span><b>درس‌ها و کلاس‌ها</b><small>مشاهده و انتخاب کلاس‌های آموزشی</small></span><i>←</i></a>
</section>
<?php include 'partials/footer.php'; ?>
<script>
(()=>{const tabs=[...document.querySelectorAll('[data-exam-filter]')],cards=[...document.querySelectorAll('[data-exam-window]')];tabs.forEach(btn=>btn.addEventListener('click',()=>{tabs.forEach(t=>t.classList.toggle('active',t===btn));cards.forEach(card=>{card.hidden=btn.dataset.examFilter!=='all'&&card.dataset.examWindow!==btn.dataset.examFilter;});}));})();
</script>
