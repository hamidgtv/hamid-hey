<?php
require_once 'config.php';$u=require_role('teacher');csrf_check();$id=(int)($_POST['id']??0);
$st=db()->prepare("SELECT COUNT(*) FROM questions q JOIN exams e ON e.id=q.exam_id WHERE e.id=? AND e.teacher_id=?");$st->execute([$id,$u['id']]);
if((int)$st->fetchColumn()>0){$st=db()->prepare("UPDATE exams SET status='published' WHERE id=? AND teacher_id=?");$st->execute([$id,$u['id']]);flash('آزمون با موفقیت منتشر شد.');}else flash('برای انتشار، حداقل یک سؤال اضافه کنید.','warning');
redirect('teacher.php');
