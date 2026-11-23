<?php
require_once 'config.php';$u=require_role('teacher');$id=(int)($_GET['id']??0);
$st=db()->prepare("SELECT * FROM exams WHERE id=? AND teacher_id=?");$st->execute([$id,$u['id']]);$exam=$st->fetch();if(!$exam)redirect('teacher.php');csrf_check();
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 $type=$_POST['type']??'mcq';$text=trim($_POST['question_text']??'');$score=(float)($_POST['score']??1);
 if($text && $score>0 && in_array($type,['mcq','essay'],true)){
  $opts=$type==='mcq'?array_map('trim',[$_POST['a']??'',$_POST['b']??'',$_POST['c']??'',$_POST['d']??'']):[];
  $correct=$type==='mcq'?($_POST['correct']??'A'):null;
  if($type==='essay'||count(array_filter($opts))===4){
   $st=db()->prepare("INSERT INTO questions(exam_id,type,question_text,option_a,option_b,option_c,option_d,correct_option,score,sort_order) VALUES(?,?,?,?,?,?,?,?,?,(SELECT COALESCE(MAX(x.sort_order),0)+1 FROM (SELECT sort_order FROM questions WHERE exam_id=?) x))");
   $st->execute([$id,$type,$text,$opts[0]??null,$opts[1]??null,$opts[2]??null,$opts[3]??null,$correct,$score,$id]);
   $st=db()->prepare("UPDATE exams SET total_score=(SELECT COALESCE(SUM(score),0) FROM questions WHERE exam_id=?) WHERE id=?");$st->execute([$id,$id]);
   redirect('edit_questions.php?id='.$id);
  }else $error='هر چهار گزینه را کامل وارد کنید.';
 }else $error='متن سؤال و بارم معتبر وارد کنید.';
}
$st=db()->prepare("SELECT * FROM questions WHERE exam_id=? ORDER BY sort_order");$st->execute([$id]);$qs=$st->fetchAll();
$pageTitle='طراحی سؤالات';include 'partials/header.php';
?>
<div class="page-head"><div><span class="section-label">مرحله دوم · طراحی سؤال‌ها</span><h1>طراحی سؤالات</h1><p><?=e($exam['title'])?> · بارم فعلی: <?=e($exam['total_score'])?></p></div><a class="btn secondary" href="teacher.php">بازگشت</a></div>
<div class="card form-card"><h3>افزودن سؤال</h3><?php if($error): ?><div class="alert danger"><?=e($error)?></div><?php endif; ?><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><div class="form-grid">
<div class="field"><label>نوع سؤال</label><select name="type" id="qtype"><option value="mcq">چهارگزینه‌ای</option><option value="essay">تشریحی</option></select></div><div class="field"><label>بارم سؤال</label><input type="number" name="score" min=".25" step=".25" value="1" required></div>
<div class="field full"><label>متن سؤال</label><textarea name="question_text" rows="3" required></textarea></div>
<div id="options" class="field full"><label>گزینه‌ها و پاسخ صحیح</label><div class="form-grid"><?php foreach(['a','b','c','d'] as $i=>$v): ?><div class="field"><label>گزینه <?=['الف','ب','ج','د'][$i]?></label><input name="<?=$v?>" placeholder="متن گزینه"></div><?php endforeach; ?><div class="field"><label>پاسخ صحیح</label><select name="correct"><option value="A">الف</option><option value="B">ب</option><option value="C">ج</option><option value="D">د</option></select></div></div></div>
</div><button class="btn primary" style="margin-top:18px">＋ افزودن سؤال</button></form></div>
<div class="card form-card" style="margin-top:18px"><div class="toolbar"><h3>سؤالات ثبت‌شده (<?=count($qs)?>)</h3><span class="badge"><?=e($exam['total_score'])?> نمره</span></div><?php foreach($qs as $i=>$q): ?><div class="question-box"><b><?=($i+1)?>. <?=e($q['question_text'])?></b><span class="badge"><?=$q['type']==='mcq'?'چهارگزینه‌ای':'تشریحی'?></span><p class="muted-small">بارم: <?=e($q['score'])?> <?php if($q['type']==='mcq'): ?> · پاسخ صحیح: <?=e($q['correct_option'])?><?php endif; ?></p></div><?php endforeach; ?><form method="post" action="publish_exam.php"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="id" value="<?=$id?>"><button class="btn primary" <?=count($qs)===0?'disabled':''?>>انتشار آزمون و نمایش به دانشجویان</button></form></div>
<script>document.getElementById('qtype').addEventListener('change',e=>document.getElementById('options').style.display=e.target.value==='essay'?'none':'block');</script>
<?php include 'partials/footer.php'; ?>
