<?php
require_once 'config.php';
$u = require_role('teacher');
csrf_check();

// Only students enrolled in classes owned by the signed-in teacher are manageable.
$ownedClassSql = "EXISTS (SELECT 1 FROM user_classes ux JOIN classes cx ON cx.id=ux.class_id WHERE ux.user_id=s.id AND cx.teacher_id=?)";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'toggle_status') {
    $studentId = (int)($_POST['student_id'] ?? 0);
    $st = db()->prepare("SELECT s.id,s.is_active FROM users s WHERE s.id=? AND s.role='student' AND $ownedClassSql LIMIT 1");
    $st->execute([$studentId, $u['id']]);
    $student = $st->fetch();
    if ($student) {
        $newStatus = (int)$student['is_active'] ? 0 : 1;
        $up = db()->prepare('UPDATE users SET is_active=? WHERE id=?');
        $up->execute([$newStatus, $studentId]);
        flash($newStatus ? 'دسترسی دانشجو فعال شد.' : 'دسترسی دانشجو غیرفعال شد.', $newStatus ? 'success' : 'warning');
    } else {
        flash('دانشجو موردنظر در کلاس‌های شما ثبت نشده است.', 'danger');
    }
    redirect('students.php');
}

$classesSt = db()->prepare('SELECT id,name,city FROM classes WHERE teacher_id=? ORDER BY city,name');
$classesSt->execute([$u['id']]);
$classes = $classesSt->fetchAll();

$viewId = (int)($_GET['view'] ?? 0);
$detail = null;
$detailAttempts = [];
if ($viewId) {
    $st = db()->prepare("SELECT s.id,s.full_name,s.national_code,s.city,s.class_name,s.is_active,s.created_at, GROUP_CONCAT(DISTINCT CONCAT(c.city,' — ',c.name) ORDER BY c.city,c.name SEPARATOR ' | ') class_list FROM users s JOIN user_classes uc ON uc.user_id=s.id JOIN classes c ON c.id=uc.class_id AND c.teacher_id=? WHERE s.id=? AND s.role='student' GROUP BY s.id LIMIT 1");
    $st->execute([$u['id'], $viewId]);
    $detail = $st->fetch();
    if ($detail) {
        $st = db()->prepare("SELECT e.title,e.total_score,a.score,a.status,a.started_at,a.submitted_at FROM attempts a JOIN exams e ON e.id=a.exam_id WHERE a.student_id=? AND e.teacher_id=? ORDER BY a.started_at DESC LIMIT 50");
        $st->execute([$viewId, $u['id']]);
        $detailAttempts = $st->fetchAll();
    }
}

$q = trim($_GET['q'] ?? '');
$city = trim($_GET['city'] ?? '');
$classId = (int)($_GET['class_id'] ?? 0);
$status = $_GET['status'] ?? 'all';
$where = ["s.role='student'", $ownedClassSql];
$params = [$u['id']];
if ($q !== '') {
    $where[] = '(s.full_name LIKE ? OR s.national_code LIKE ?)';
    $params[] = '%'.$q.'%'; $params[] = '%'.$q.'%';
}
if ($city !== '') { $where[] = 's.city=?'; $params[] = $city; }
if ($classId > 0) {
    $where[] = 'EXISTS (SELECT 1 FROM user_classes uf WHERE uf.user_id=s.id AND uf.class_id=? AND EXISTS(SELECT 1 FROM classes cf WHERE cf.id=uf.class_id AND cf.teacher_id=?))';
    $params[] = $classId; $params[] = $u['id'];
}
if ($status === 'active') $where[] = 's.is_active=1';
elseif ($status === 'inactive') $where[] = 's.is_active=0';
$sqlWhere = implode(' AND ', $where);
$sql = "SELECT s.id,s.full_name,s.national_code,s.city,s.class_name,s.is_active,s.created_at, GROUP_CONCAT(DISTINCT c.name ORDER BY c.name SEPARATOR '، ') class_titles, COUNT(DISTINCT CASE WHEN e.id IS NOT NULL THEN a.id END) attempts_count, MAX(CASE WHEN e.id IS NOT NULL THEN a.submitted_at END) last_attempt FROM users s JOIN user_classes uc ON uc.user_id=s.id JOIN classes c ON c.id=uc.class_id AND c.teacher_id=? LEFT JOIN attempts a ON a.student_id=s.id LEFT JOIN exams e ON e.id=a.exam_id AND e.teacher_id=? WHERE $sqlWhere GROUP BY s.id ORDER BY s.created_at DESC";
// The first two JOIN placeholders precede WHERE placeholders.
$runParams = [$u['id'], $u['id'], ...$params];
$st = db()->prepare($sql); $st->execute($runParams); $students = $st->fetchAll();

$st = db()->prepare("SELECT COUNT(DISTINCT s.id) total, COUNT(DISTINCT CASE WHEN s.is_active=1 THEN s.id END) active, COUNT(DISTINCT CASE WHEN s.is_active=0 THEN s.id END) inactive FROM users s JOIN user_classes uc ON uc.user_id=s.id JOIN classes c ON c.id=uc.class_id AND c.teacher_id=? WHERE s.role='student'");
$st->execute([$u['id']]); $stats = $st->fetch();
$cities = [];
$st = db()->prepare("SELECT DISTINCT s.city FROM users s JOIN user_classes uc ON uc.user_id=s.id JOIN classes c ON c.id=uc.class_id AND c.teacher_id=? WHERE s.role='student' AND s.city IS NOT NULL AND s.city<>'' ORDER BY s.city");
$st->execute([$u['id']]); $cities = $st->fetchAll(PDO::FETCH_COLUMN);

$pageTitle = 'مدیریت دانشجویان';
include 'partials/header.php';
?>
<div class="page-head students-page-head">
  <div><span class="section-label">STUDENT AFFAIRS</span><h1>مدیریت دانشجویان</h1><p>دانشجویان کلاس‌های خود را جست‌وجو کنید، وضعیت دسترسی را مدیریت کنید و سوابق آزمون را ببینید.</p></div>
  <a class="btn secondary" href="teacher.php">بازگشت به داشبورد</a>
</div>
<div class="grid student-management-stats">
  <div class="card stat"><div><span class="muted-small">کل دانشجویان کلاس‌های من</span><strong><?= (int)$stats['total'] ?></strong></div><div class="stat-icon">♙</div></div>
  <div class="card stat"><div><span class="muted-small">حساب‌های فعال</span><strong><?= (int)$stats['active'] ?></strong></div><div class="stat-icon green-icon">✓</div></div>
  <div class="card stat"><div><span class="muted-small">حساب‌های غیرفعال</span><strong><?= (int)$stats['inactive'] ?></strong></div><div class="stat-icon orange-icon">⏸</div></div>
  <div class="card stat"><div><span class="muted-small">کلاس‌های تعریف‌شده</span><strong><?= count($classes) ?></strong></div><div class="stat-icon">▦</div></div>
</div>
<?php if ($detail): ?>
<section class="card student-detail-card">
  <div class="section-heading"><div><span class="section-label">STUDENT PROFILE</span><h2>پرونده آموزشی دانشجو</h2></div><a class="btn secondary" href="students.php">بازگشت به فهرست</a></div>
  <div class="student-profile-grid">
    <div><small>نام و نام خانوادگی</small><b><?=e($detail['full_name'])?></b></div>
    <div><small>کد ملی</small><b class="latin-number"><?=e($detail['national_code'])?></b></div>
    <div><small>شهر</small><b><?=e($detail['city'] ?: '—')?></b></div>
    <div><small>کلاس‌ها</small><b><?=e($detail['class_list'] ?: '—')?></b></div>
    <div><small>تاریخ عضویت</small><b><?=e(date('Y/m/d', strtotime($detail['created_at'])))?></b></div>
    <div><small>وضعیت حساب</small><b><span class="badge <?=$detail['is_active']?'green':'orange'?>"><?=$detail['is_active']?'فعال':'غیرفعال'?></span></b></div>
  </div>
  <form method="post" class="inline-status-form"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="toggle_status"><input type="hidden" name="student_id" value="<?=$detail['id']?>"><button class="btn <?=$detail['is_active']?'danger-btn':'primary'?>" data-confirm="<?= $detail['is_active'] ? 'دسترسی این دانشجو غیرفعال شود؟' : 'دسترسی این دانشجو فعال شود؟' ?>"><?=$detail['is_active']?'غیرفعال‌کردن حساب':'فعال‌کردن حساب'?></button></form>
  <h3 class="student-history-title">سوابق آزمون‌های شما</h3>
  <div class="table-wrap"><table class="table"><thead><tr><th>آزمون</th><th>تاریخ</th><th>وضعیت</th><th>نمره</th><th>نمره کل</th></tr></thead><tbody>
  <?php foreach($detailAttempts as $a): ?><tr><td><?=e($a['title'])?></td><td><?=e($a['submitted_at'] ?: $a['started_at'])?></td><td><?= $a['status']==='graded'?'تصحیح‌شده':($a['status']==='submitted'?'در انتظار تصحیح':'در حال انجام') ?></td><td><?=$a['status']==='graded'?number_format((float)$a['score'],2):'—'?></td><td><?=e($a['total_score'])?></td></tr><?php endforeach; ?>
  <?php if(!$detailAttempts): ?><tr><td colspan="5" class="empty">هنوز در آزمون‌های شما شرکت نکرده است.</td></tr><?php endif; ?>
  </tbody></table></div>
</section>
<?php elseif($viewId): ?><div class="alert warning">پرونده پیدا نشد یا این دانشجو عضو کلاس‌های شما نیست.</div><?php endif; ?>
<section class="card student-filter-card">
  <div class="section-heading"><div><span class="section-label">DIRECTORY</span><h2>فهرست دانشجویان</h2><p>فقط دانشجویان عضو کلاس‌هایی نمایش داده می‌شوند که شما ساخته‌اید.</p></div><span class="badge green"><?=count($students)?> نتیجه</span></div>
  <form method="get" class="student-filters">
    <div class="field"><label>جست‌وجوی نام یا کد ملی</label><input name="q" value="<?=e($q)?>" placeholder="نام یا ۱۰ رقم کد ملی"></div>
    <div class="field"><label>شهر</label><select name="city"><option value="">همه شهرها</option><?php foreach($cities as $c): ?><option value="<?=e($c)?>" <?=$city===$c?'selected':''?>><?=e($c)?></option><?php endforeach; ?></select></div>
    <div class="field"><label>کلاس</label><select name="class_id"><option value="0">همه کلاس‌ها</option><?php foreach($classes as $c): ?><option value="<?=$c['id']?>" <?=$classId===(int)$c['id']?'selected':''?>><?=e($c['city'].' — '.$c['name'])?></option><?php endforeach; ?></select></div>
    <div class="field"><label>وضعیت حساب</label><select name="status"><option value="all">همه</option><option value="active" <?=$status==='active'?'selected':''?>>فعال</option><option value="inactive" <?=$status==='inactive'?'selected':''?>>غیرفعال</option></select></div>
    <div class="student-filter-actions"><button class="btn primary">اعمال فیلتر</button><a class="btn secondary" href="students.php">پاک‌کردن</a></div>
  </form>
  <div class="table-wrap"><table class="table student-table"><thead><tr><th>دانشجو</th><th>کد ملی</th><th>شهر و کلاس</th><th>تعداد شرکت</th><th>آخرین آزمون</th><th>وضعیت</th><th>عملیات</th></tr></thead><tbody>
  <?php foreach($students as $s): ?><tr><td><b><?=e($s['full_name'])?></b></td><td class="latin-number"><?=e($s['national_code'])?></td><td><?=e(($s['city']?:'—').' / '.($s['class_titles']?:$s['class_name']?:'—'))?></td><td><?= (int)$s['attempts_count'] ?></td><td><?=e($s['last_attempt']?:'—')?></td><td><span class="badge <?=$s['is_active']?'green':'orange'?>"><?=$s['is_active']?'فعال':'غیرفعال'?></span></td><td><div class="student-row-actions"><a class="btn secondary small-btn" href="students.php?view=<?=$s['id']?>">پرونده</a><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="toggle_status"><input type="hidden" name="student_id" value="<?=$s['id']?>"><button class="btn <?=$s['is_active']?'danger-btn':'primary'?> small-btn" data-confirm="<?= $s['is_active'] ? 'دسترسی این دانشجو غیرفعال شود؟' : 'دسترسی این دانشجو فعال شود؟' ?>"><?=$s['is_active']?'غیرفعال':'فعال‌سازی'?></button></form></div></td></tr><?php endforeach; ?>
  <?php if(!$students): ?><tr><td colspan="7" class="empty">دانشجوی با این فیلترها پیدا نشد. ابتدا از بخش مدیریت کلاس‌ها کلاس بسازید و از دانشجویان بخواهید آن کلاس را انتخاب کنند.</td></tr><?php endif; ?>
  </tbody></table></div>
</section>
<?php include 'partials/footer.php'; ?>
