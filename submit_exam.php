<?php
require_once 'config.php';$u=require_role('student');csrf_check();$aid=(int)($_POST['attempt_id']??0);
$st=db()->prepare("SELECT a.*,e.duration_minutes FROM attempts a JOIN exams e ON e.id=a.exam_id WHERE a.id=? AND a.student_id=? AND a.status='in_progress'");$st->execute([$aid,$u['id']]);$attempt=$st->fetch();if(!$attempt)redirect('results.php');
$st=db()->prepare("SELECT q.* FROM questions q WHERE q.exam_id=?");$st->execute([$attempt['exam_id']]);$qs=$st->fetchAll();$total=0;$pending=false;
db()->beginTransaction();
try{
 foreach($qs as $q){$raw=$_POST['answers'][$q['id']]??'';$answer=is_string($raw)?trim($raw):'';$score=0;$graded=1;
  if($q['type']==='mcq'){$score=($answer===$q['correct_option'])?(float)$q['score']:0;}
  else{$graded=0;$pending=true;}
  $st=db()->prepare("INSERT INTO answers(attempt_id,question_id,answer_text,score,is_graded) VALUES(?,?,?,?,?) ON DUPLICATE KEY UPDATE answer_text=VALUES(answer_text),score=VALUES(score),is_graded=VALUES(is_graded)");
  $st->execute([$aid,$q['id'],$answer,$score,$graded]);$total+=$score;
 }
 $st=db()->prepare("UPDATE attempts SET status='submitted',submitted_at=NOW(),score=?,needs_grading=? WHERE id=?");$st->execute([$total,$pending?1:0,$aid]);db()->commit();
 flash($pending?'آزمون ثبت شد. نمره نهایی پس از تصحیح سؤالات تشریحی اعلام می‌شود.':'آزمون با موفقیت ثبت شد.');redirect('results.php');
}catch(Throwable $e){db()->rollBack();throw $e;}
