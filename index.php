<?php
require_once 'config.php';
if (current_user()) redirect(current_user()['role']==='teacher'?'teacher.php':'student.php');
$error='';
$mode=$_GET['mode']??'login';
if (!in_array($mode,['login','register'],true)) $mode='login';
$selectedRole='student';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    csrf_check();
    $action=$_POST['action']??'login';
    $mode=$action==='register'?'register':'login';
    $selectedRole=($_POST['role']??'student')==='teacher'?'teacher':'student';
    $code=normalize_digits(trim($_POST['national_code']??''));
//اگه کد ملی کمتر از 10 رقم اجازه ورود یا ثبت نام نده//
    if (!valid_national_code($code)) {
        $error='کد ملی واردشده معتبر نیست؛ کد ملی باید ۱۰ رقم و دارای رقم کنترل صحیح باشد.';
    } elseif ($action==='register') {
        $name=trim($_POST['full_name']??'');
        $city=trim($_POST['city']??'');
        if (mb_strlen($name,'UTF-8')<3 || mb_strlen($name,'UTF-8')>150) $error='نام و نام خانوادگی را صحیح وارد کنید.';
        elseif ($city==='') $error='لطفاً شهر محل فعالیت یا تحصیل را وارد کنید.';
        //اگر قبلا ثبت نام کرده بود نزار ورود کنه//
        else {
            $st=db()->prepare('SELECT id FROM users WHERE national_code=?');
            $st->execute([$code]);
            if ($st->fetch()) $error='این کد ملی قبلاً در سامانه ثبت شده است.';
            else {
                $st=db()->prepare('INSERT INTO users(national_code,full_name,role,city,is_active) VALUES(?,?,?,?,1)');
                $st->execute([$code,$name,$selectedRole,$city]);
                $id=(int)db()->lastInsertId();
                $u=['id'=>$id,'national_code'=>$code,'full_name'=>$name,'role'=>$selectedRole,'city'=>$city,'class_name'=>null];
                session_regenerate_id(true);
                $_SESSION['user']=$u;
                flash($selectedRole==='teacher'?'حساب استادی شما با موفقیت ساخته شد. اکنون می‌توانید آزمون طراحی کنید.':'ثبت‌نام شما با موفقیت انجام شد. اطلاعات در پایگاه داده ذخیره شد.');
                redirect($selectedRole==='teacher'?'teacher.php':'student.php');
            }
        }
    } else {
        $st=db()->prepare('SELECT id,national_code,full_name,role,city,class_name FROM users WHERE national_code=? AND is_active=1');
        $st->execute([$code]);
        $u=$st->fetch();
        if ($u) {
            session_regenerate_id(true);
            $_SESSION['user']=$u;
            redirect($u['role']==='teacher'?'teacher.php':'student.php');
        }
        $error='حسابی با این کد ملی پیدا نشد؛ ابتدا ثبت‌نام کنید.';
    }
}
$pageTitle='سامانه یکپارچه آموزشی';
include 'partials/header.php';
$isRegister=$mode==='register';
?>
<div class="unified-auth-page">
 <div class="auth-showcase">
  <div class="showcase-top"><span class="showcase-logo">آ</span><span>سامانه <b>آموزشی</b></span><span class="showcase-live"><i></i> سامانه فعال</span></div>
  <div class="showcase-content">
   <span class="showcase-kicker">سامانه یکپارچه آموزش و ارزیابی</span>
   <h1>یادگیری هوشمند،<br><em>آزمونی مطمئن</em></h1>
   <p>یک فضای یکپارچه برای استادان و دانشجویان؛ از طراحی آزمون تا شرکت در امتحان و مشاهده کارنامه، همه در یک سامانه.</p>
   <div class="showcase-features">
    <div><span>✦</span><b>مدیریت هوشمند آزمون</b><small>ساخت، ویرایش و انتشار آسان</small></div>
    <div><span>◈</span><b>فضای اختصاصی هر کاربر</b><small>دسترسی بر اساس نقش حساب</small></div>
    <div><span>▤</span><b>کارنامه و گزارش دقیق</b><small>پیگیری نتایج و عملکرد</small></div>
   </div>
  </div>
  <div class="showcase-footer"><span>طراحی و توسعه <b>حمید حیدرزاده</b></span><span>محیط یکپارچه یادگیری و ارزیابی</span></div>
 </div>
 <main class="unified-auth-card">
  <div class="auth-card-brand"><span class="auth-card-logo">آ</span><div><b>سامانه آموزشی</b><small>خوش آمدید؛ مسیر آموزشی شما از اینجا آغاز می‌شود.</small></div></div>
  <div class="unified-auth-switch" role="tablist" aria-label="نوع دسترسی">
   <a href="index.php" class="<?=$mode==='login'?'active':''?>">ورود به سامانه</a>
   <a href="index.php?mode=register" class="<?=$isRegister?'active':''?>">ساخت حساب جدید</a>
  </div>
  <?php if($isRegister): ?>
   <div class="auth-heading"><span class="auth-step">مرحله ۱ از ۲</span><h2>حساب کاربری خود را بسازید</h2><p>نقش خود را مشخص کنید و اطلاعات را برای ساخت حساب وارد نمایید.</p></div>
  <?php else: ?>
   <div class="auth-heading"><span class="auth-step">ورود یکپارچه</span><h2>به سامانه خوش آمدید</h2><p>کد ملی خود را وارد کنید تا وارد پنل اختصاصی شوید.</p></div>
  <?php endif; ?>
  <?php if($error): ?><div class="alert danger"><?=e($error)?></div><?php endif; ?>
  <form method="post" class="unified-auth-form" id="authForm">
   <input type="hidden" name="csrf" value="<?=e(csrf_token())?>">
   <input type="hidden" name="action" value="<?=$isRegister?'register':'login'?>">
   <?php if($isRegister): ?>
    <input type="hidden" name="role" id="accountRole" value="<?=e($selectedRole)?>">
    <label class="form-label">برای کدام گروه حساب می‌سازید؟</label>
    <div class="role-choice-grid">
     <button type="button" class="role-choice <?=$selectedRole==='student'?'selected':''?>" data-role="student" aria-pressed="<?=$selectedRole==='student'?'true':'false'?>"><span class="role-choice-icon">♙</span><span><b>دانشجو هستم</b><small>شرکت در آزمون و دریافت کارنامه</small></span><i>✓</i></button>
     <button type="button" class="role-choice <?=$selectedRole==='teacher'?'selected':''?>" data-role="teacher" aria-pressed="<?=$selectedRole==='teacher'?'true':'false'?>"><span class="role-choice-icon">✦</span><span><b>استاد هستم</b><small>ساخت آزمون و مدیریت کلاس‌ها</small></span><i>✓</i></button>
    </div>
    <div class="form-two-col"><div><label class="form-label" for="fullName">نام و نام خانوادگی</label><input id="fullName" name="full_name" required maxlength="150" autocomplete="name" placeholder="نام و نام خانوادگی خود را وارد کنید" value="<?=e($_POST['full_name']??'')?>"></div><div><label class="form-label" for="city">شهر</label><input id="city" name="city" required maxlength="100" list="studyCities" placeholder="شهر محل تحصیل یا فعالیت" value="<?=e($_POST['city']??'')?>"><datalist id="studyCities"><option value="اهواز"><option value="آبادان"><option value="خرمشهر"><option value="دزفول"><option value="ماهشهر"><option value="بهبهان"><option value="گتوند"><option value="اندیمشک"><option value="شوشتر"><option value="شوش"><option value="مسجدسلیمان"><option value="رامهرمز"><option value="شادگان"><option value="باغ‌ملک"><option value="هویزه"><option value="سوسنگرد"></datalist></div></div>
   <?php endif; ?>
   <label class="form-label" for="nationalCode">کد ملی</label>
   <div class="national-input-wrap"><span>کد ملی</span><input id="nationalCode" name="national_code" inputmode="numeric" autocomplete="off" maxlength="10" minlength="10" required placeholder="۱۰ رقم کد ملی" value="<?=e($_POST['national_code']??'')?>"></div>
   <label class="remember-row"><input type="checkbox" id="rememberCode"> <span>ذخیره اطلاعات</span></label>
   <button class="unified-submit" type="submit"><span><?=$isRegister?'ثبت‌نام و ورود به پنل':'ورود به پنل کاربری'?></span><b>←</b></button>
  </form>
  <div class="unified-auth-hint"><span>ⓘ</span><p><?=$isRegister?'پس از ثبت‌نام، پنل متناسب با نقش انتخابی شما به‌صورت خودکار باز می‌شود.':'سامانه نقش حساب شما را از پایگاه داده تشخیص می‌دهد؛ نیازی به انتخاب جداگانه استاد یا دانشجو نیست.'?></p></div>
  <div class="unified-auth-bottom"><span>سامانه یکپارچه آموزشی</span><b>طراحی و توسعه: حمید حیدرزاده</b></div>
 </main>
</div>
<script>
(()=>{
 const input=document.getElementById('nationalCode');
 const remember=document.getElementById('rememberCode');
 const key='khuzestan_exam_national_code';
 if(input&&remember){try{const saved=localStorage.getItem(key);if(saved){input.value=saved;remember.checked=true;}}catch(e){}document.getElementById('authForm').addEventListener('submit',()=>{try{if(remember.checked)localStorage.setItem(key,input.value);else localStorage.removeItem(key);}catch(e){}});}
 const roleInput=document.getElementById('accountRole');
 document.querySelectorAll('.role-choice').forEach(button=>button.addEventListener('click',()=>{
  if(!roleInput)return;
  roleInput.value=button.dataset.role;
  document.querySelectorAll('.role-choice').forEach(item=>{const active=item===button;item.classList.toggle('selected',active);item.setAttribute('aria-pressed',active?'true':'false');});
 }));
})();
</script>
<?php include 'partials/footer.php'; ?>
