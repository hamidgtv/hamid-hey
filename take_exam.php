<?php
require_once 'config.php';$u=require_role('student');$id=(int)($_GET['id']??0);
$st=db()->prepare("SELECT e.* FROM exams e WHERE e.id=? AND e.status='published' AND e.starts_at<=NOW() AND e.ends_at>=NOW() AND (e.class_id IS NULL OR e.class_id IN(SELECT class_id FROM user_classes WHERE user_id=?))");$st->execute([$id,$u['id']]);$exam=$st->fetch();if(!$exam) {flash('این آزمون در دسترس نیست.','warning');redirect('student.php');}
$st=db()->prepare("SELECT * FROM attempts WHERE exam_id=? AND student_id=?");$st->execute([$id,$u['id']]);$attempt=$st->fetch();
if($attempt){if($attempt['status']!=='in_progress'){flash('این آزمون قبلاً ثبت شده است.','warning');redirect('results.php');} $started=strtotime($attempt['started_at']);}
else{$st=db()->prepare("INSERT INTO attempts(exam_id,student_id,started_at,status) VALUES(?,?,NOW(),'in_progress')");$st->execute([$id,$u['id']]);$attemptId=(int)db()->lastInsertId();$st=db()->prepare("SELECT * FROM attempts WHERE id=?");$st->execute([$attemptId]);$attempt=$st->fetch();$started=time();}
$started=strtotime($attempt['started_at']);$remaining=max(0,$exam['duration_minutes']*60-(time()-$started));
$st=db()->prepare("SELECT * FROM questions WHERE exam_id=? ORDER BY sort_order");$st->execute([$id]);$qs=$st->fetchAll();
$st=db()->prepare("SELECT question_id,answer_text FROM answers WHERE attempt_id=?");$st->execute([$attempt['id']]);$saved=[];foreach($st as $a)$saved[$a['question_id']]=$a['answer_text'];
$pageTitle='شرکت در آزمون';include 'partials/header.php';
?>
<div class="page-head"><div><span class="section-label">آزمون آنلاین</span><h1><?=e($exam['title'])?></h1><p><?=e($exam['description'])?></p></div></div>
<form id="exam-form" method="post" action="submit_exam.php"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="attempt_id" value="<?=$attempt['id']?>"><input type="hidden" name="auto_submit" value="0">
<div class="exam-layout"><div><?php foreach($qs as $i=>$q): ?><div class="card question-box"><h4>سؤال <?=($i+1)?> <span class="badge"><?=e($q['score'])?> نمره</span></h4><p><?=nl2br(e($q['question_text']))?></p><?php if($q['type']==='mcq'): foreach(['A'=>'option_a','B'=>'option_b','C'=>'option_c','D'=>'option_d'] as $letter=>$field): ?><label class="option"><input type="radio" name="answers[<?=$q['id']?>]" value="<?=$letter?>" <?=($saved[$q['id']]??'')===$letter?'checked':''?>> <?=['A'=>'الف','B'=>'ب','C'=>'ج','D'=>'د'][$letter]?>) <?=e($q[$field])?></label><?php endforeach; else: ?><textarea name="answers[<?=$q['id']?>]" rows="5" placeholder="پاسخ تشریحی خود را بنویسید..."><?=e($saved[$q['id']]??'')?></textarea><?php endif; ?></div><?php endforeach; ?>
<button class="btn primary" data-confirm="پس از ثبت نهایی امکان ویرایش پاسخ‌ها وجود ندارد. مطمئن هستید؟">ثبت نهایی آزمون</button></div>
<aside class="timer" data-timer="<?=$remaining?>"><small>زمان باقی‌مانده</small><strong data-time>--:--</strong><p>پاسخ‌ها با ثبت نهایی ارسال می‌شوند.</p><div class="question-nav"><?php foreach($qs as $i=>$q): ?><span><?=($i+1)?></span><?php endforeach; ?></div></aside></div></form>
<?php include 'partials/footer.php'; ?>
