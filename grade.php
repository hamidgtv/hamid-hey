<?php
require_once 'config.php';$u=require_role('teacher');$examId=(int)($_GET['exam_id']??0);
$st=db()->prepare("SELECT * FROM exams WHERE id=? AND teacher_id=?");$st->execute([$examId,$u['id']]);$exam=$st->fetch();if(!$exam)redirect('teacher.php');csrf_check();
if($_SERVER['REQUEST_METHOD']==='POST'){
 $aid=(int)($_POST['attempt_id']??0);$st=db()->prepare("SELECT a.id FROM attempts a WHERE a.id=? AND a.exam_id=? AND a.status='submitted'");$st->execute([$aid,$examId]);
 if($st->fetch()){
  foreach(($_POST['scores']??[]) as $answerId=>$score){$score=max(0,(float)$score);$st=db()->prepare("SELECT an.id,q.score FROM answers an JOIN questions q ON q.id=an.question_id WHERE an.id=? AND an.attempt_id=? AND q.type='essay'");$st->execute([(int)$answerId,$aid]);if($a=$st->fetch()){$score=min($score,(float)$a['score']);$up=db()->prepare("UPDATE answers SET score=?,is_graded=1 WHERE id=?");$up->execute([$score,$a['id']]);}}
  $st=db()->prepare("SELECT SUM(score) s, SUM(CASE WHEN is_graded=0 THEN 1 ELSE 0 END) pending FROM answers WHERE attempt_id=?");$st->execute([$aid]);$sum=$st->fetch();
  if((int)$sum['pending']===0){$up=db()->prepare("UPDATE attempts SET score=?,needs_grading=0,status='graded' WHERE id=?");$up->execute([$sum['s']??0,$aid]);}
  else{$up=db()->prepare("UPDATE attempts SET score=?,needs_grading=1 WHERE id=?");$up->execute([$sum['s']??0,$aid]);}
  flash('نمرات پاسخ‌ها ذخیره شد.');
 }redirect('grade.php?exam_id='.$examId);
}
$st=db()->prepare("SELECT a.*,u.full_name,u.national_code FROM attempts a JOIN users u ON u.id=a.student_id WHERE a.exam_id=? AND a.status IN('submitted','graded') ORDER BY a.submitted_at DESC");$st->execute([$examId]);$attempts=$st->fetchAll();
$pageTitle='تصحیح آزمون';include 'partials/header.php';
?>
<div class="page-head"><div><span class="section-label">مرکز ارزیابی و تصحیح</span><h1>پاسخ‌ها و تصحیح آزمون</h1><p><?=e($exam['title'])?></p></div><a class="btn secondary" href="teacher.php">بازگشت</a></div>
<?php foreach($attempts as $a): $st=db()->prepare("SELECT an.*,q.question_text,q.type,q.score max_score FROM answers an JOIN questions q ON q.id=an.question_id WHERE an.attempt_id=? ORDER BY q.sort_order");$st->execute([$a['id']]);$answers=$st->fetchAll(); ?>
<div class="card" style="margin-bottom:18px"><div class="toolbar"><div><h3><?=e($a['full_name'])?></h3><span class="muted-small"><?=e($a['national_code'])?> · <?=e($a['submitted_at'])?></span></div><span class="badge <?=$a['status']==='graded'?'green':'orange'?>"><?=$a['status']==='graded'?'تصحیح کامل':'نیازمند تصحیح'?></span></div>
<form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="attempt_id" value="<?=$a['id']?>">
<?php foreach($answers as $an): ?><div class="question-box"><b><?=e($an['question_text'])?></b><p><?=nl2br(e($an['answer_text']))?:'بدون پاسخ'?></p><div class="toolbar"><span class="muted-small">بارم: <?=e($an['max_score'])?></span><?php if($an['type']==='essay'): ?><div class="field" style="max-width:180px"><label>نمره داده‌شده</label><input type="number" min="0" max="<?=e($an['max_score'])?>" step=".25" name="scores[<?=$an['id']?>]" value="<?=e($an['score'])?>"></div><?php else: ?><span class="badge green">نمره خودکار: <?=e($an['score'])?></span><?php endif; ?></div></div><?php endforeach; ?>
<button class="btn primary">ذخیره تصحیح و نمره</button></form></div>
<?php endforeach; if(!$attempts): ?><div class="card empty">هنوز دانشجوی این آزمون را ثبت نکرده است.</div><?php endif; ?>
<?php include 'partials/footer.php'; ?>
