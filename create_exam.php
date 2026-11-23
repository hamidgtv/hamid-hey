<?php
require_once 'config.php';
$u = require_role('teacher');
csrf_check();

$defaultStart = date('Y-m-d\\TH:i');
$defaultEnd = date('Y-m-d\\TH:i', strtotime('today 23:59')); 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    $duration = max(1, min(300, (int)($_POST['duration'] ?? 30)));
    $start = trim($_POST['starts_at'] ?? '');
    $end = trim($_POST['ends_at'] ?? '');
    $class = ($_POST['class_id'] ?? '') !== '' ? (int)$_POST['class_id'] : null;

    $startDate = DateTime::createFromFormat('!Y-m-d\\TH:i', $start);
    $endDate = DateTime::createFromFormat('!Y-m-d\\TH:i', $end);
    $validDates = $startDate && $endDate
        && $startDate->format('Y-m-d\\TH:i') === $start
        && $endDate->format('Y-m-d\\TH:i') === $end
        && $startDate->format('Y-m-d') === $endDate->format('Y-m-d')
        && $endDate->format('Y-m-d') === $startDate->format('Y-m-d')
        && $endDate > $startDate;

    if ($title !== '' && $validDates) {
        $st = db()->prepare("INSERT INTO exams(teacher_id,class_id,title,description,duration_minutes,starts_at,ends_at,status,total_score) VALUES(?,?,?,?,?,?,?,'draft',0)");
        $st->execute([
            $u['id'], $class, $title, $desc, $duration,
            $startDate->format('Y-m-d H:i:s'),
            $endDate->format('Y-m-d H:i:s')
        ]);
        redirect('edit_questions.php?id=' . db()->lastInsertId());
    }

    $error = $title === ''
        ? 'لطفاً عنوان آزمون را وارد کنید.'
        : 'تاریخ‌ها را به‌درستی وارد کنید و تاریخ شروع و پایان باید یکسان باشد و ساعت پایان بعد از ساعت شروع قرار بگیرد.';
}

$classes = db()->query("SELECT id,name FROM classes ORDER BY name")->fetchAll();
$pageTitle = 'ساخت آزمون';
include 'partials/header.php';
?>
<div class="page-head">
    <div><span class="section-label">مرحله اول · ساخت آزمون</span><h1>ساخت آزمون جدید</h1><p>ابتدا مشخصات پایه را تعیین کن؛ در مرحله بعد سؤال‌ها را اضافه می‌کنی.</p></div>
    <a class="btn secondary" href="teacher.php">بازگشت</a>
</div>
<div class="card form-card">
    <h3>مشخصات آزمون</h3>
    <?php if (!empty($error)): ?><div class="alert danger"><?=e($error)?></div><?php endif; ?>
    <form method="post" id="jalaliExamForm" data-jalali-exam-form>
        <input type="hidden" name="csrf" value="<?=e(csrf_token())?>">
        <div class="form-grid">
            <div class="field full"><label>عنوان آزمون</label><input name="title" required value="<?=e($_POST['title'] ?? '')?>" placeholder="مثلاً آزمون میان‌ترم مبانی برنامه‌نویسی"></div>
            <div class="field full"><label>توضیحات</label><textarea name="description" rows="3" placeholder="توضیح کوتاه درباره آزمون..."><?=e($_POST['description'] ?? '')?></textarea></div>
            <div class="field"><label>کلاس</label><select name="class_id"><option value="">همه کلاس‌ها</option><?php foreach ($classes as $c): ?><option value="<?=$c['id']?>" <?=((string)($_POST['class_id'] ?? '') === (string)$c['id']) ? 'selected' : ''?>><?=e($c['name'])?></option><?php endforeach; ?></select></div>
            <div class="field"><label>مدت آزمون (دقیقه)</label><input type="number" min="1" max="300" name="duration" value="<?=e($_POST['duration'] ?? '30')?>" required></div>

            <div class="field full exam-date-panel">
                <label for="starts_jalali_date">تاریخ برگزاری آزمون (شمسی)</label>
                <input id="starts_jalali_date" type="text" inputmode="numeric" autocomplete="off" placeholder="۱۴۰۵/۰۸/۳۰" dir="ltr" class="jalali-date-input" required>
                <small class="date-hint">آزمون در همین تاریخ شروع و در همان روز پایان پیدا می‌کند.</small>
                <input type="hidden" name="starts_at" id="starts_at" value="<?=e($_POST['starts_at'] ?? $defaultStart)?>">
                <input type="hidden" name="ends_at" id="ends_at" value="<?=e($_POST['ends_at'] ?? $defaultEnd)?>">
                <div class="exam-time-grid">
                    <div class="field"><label for="starts_jalali_time">ساعت شروع</label><input type="time" id="starts_jalali_time" value="<?=e(substr($_POST['starts_at'] ?? $defaultStart, 11, 5))?>" required></div>
                    <div class="field"><label for="ends_jalali_time">ساعت پایان همان روز</label><input type="time" id="ends_jalali_time" value="<?=e(substr($_POST['ends_at'] ?? $defaultEnd, 11, 5))?>" required></div>
                </div>
            </div>
        </div>
        <div class="actions" style="margin-top:22px"><button class="btn primary" type="submit">ادامه و افزودن سؤال ←</button></div>
    </form>
</div>
<style>
.jalali-date-input{direction:ltr;text-align:center;font-variant-numeric:tabular-nums;letter-spacing:1px;font-weight:700}
.date-hint{font-size:10px;color:var(--muted);margin-top:-2px}
</style>
<script>
(()=>{
 const form=document.querySelector('[data-jalali-exam-form]');
 if(!form)return;
 const fmt=new Intl.DateTimeFormat('en-u-ca-persian-nu-latn',{year:'numeric',month:'2-digit',day:'2-digit',timeZone:'UTC'});
 const parts=date=>Object.fromEntries(fmt.formatToParts(date).filter(p=>p.type!=='literal').map(p=>[p.type,Number(p.value)]));
 const normalize=s=>String(s).replace(/[۰-۹]/g,d=>'۰۱۲۳۴۵۶۷۸۹'.indexOf(d)).replace(/[٠-٩]/g,d=>'٠١٢٣٤٥٦٧٨٩'.indexOf(d)).replace(/[.\-]/g,'/').trim();
 const toJalali=iso=>{const [y,m,d]=iso.slice(0,10).split('-').map(Number);const p=parts(new Date(Date.UTC(y,m-1,d,12)));return `${p.year}/${String(p.month).padStart(2,'0')}/${String(p.day).padStart(2,'0')}`;};
 const toGregorian=value=>{
   const match=normalize(value).match(/^(\d{4})\/(\d{1,2})\/(\d{1,2})$/);if(!match)return null;
   const [jy,jm,jd]=match.slice(1).map(Number);if(jy<1300||jy>1500||jm<1||jm>12||jd<1||jd>(jm<=6?31:jm<=11?30:30))return null;
   const start=Date.UTC(jy+621,2,1,12);
   for(let i=0;i<370;i++){const candidate=new Date(start+i*86400000),p=parts(candidate);if(p.year===jy&&p.month===jm&&p.day===jd){const y=candidate.getUTCFullYear(),m=String(candidate.getUTCMonth()+1).padStart(2,'0'),d=String(candidate.getUTCDate()).padStart(2,'0');return `${y}-${m}-${d}`;}}
   return null;
 };
 const dateInput=document.getElementById('starts_jalali_date');
 const startTime=document.getElementById('starts_jalali_time');
 const endTime=document.getElementById('ends_jalali_time');
 const startHidden=document.getElementById('starts_at');
 const endHidden=document.getElementById('ends_at');
 dateInput.value=toJalali(startHidden.value);
 dateInput.addEventListener('input',()=>{dateInput.value=normalize(dateInput.value).replace(/[^0-9/]/g,'');});
 form.addEventListener('submit',e=>{
   const g=toGregorian(dateInput.value),a=startTime.value,b=endTime.value;
   const validTime=t=>/^([01]\d|2[0-3]):[0-5]\d$/.test(t);
   if(!g||!validTime(a)||!validTime(b)){e.preventDefault();dateInput.setCustomValidity('تاریخ شمسی یا ساعت را به‌درستی وارد کنید.');dateInput.reportValidity();return;}
   dateInput.setCustomValidity('');
   if(b<=a){e.preventDefault();endTime.setCustomValidity('ساعت پایان باید بعد از ساعت شروع باشد.');endTime.reportValidity();return;}
   endTime.setCustomValidity('');startHidden.value=`${g}T${a}`;endHidden.value=`${g}T${b}`;
 });
 endTime.addEventListener('input',()=>endTime.setCustomValidity(''));
})();
</script>
<?php include 'partials/footer.php'; ?>
