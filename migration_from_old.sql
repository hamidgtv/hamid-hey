-- فقط برای دیتابیسی که نسخه قبلی EXAM PRO در آن نصب شده است.
-- قبل از اجرا از دیتابیس خود نسخه پشتیبان بگیرید.
USE exam_pro;
ALTER TABLE users ADD COLUMN national_code CHAR(10) NULL UNIQUE AFTER id;
ALTER TABLE users MODIFY username VARCHAR(80) NULL, MODIFY password_hash VARCHAR(255) NULL;
-- کاربران قبلی باید پس از این مهاجرت، کد ملی معتبر خود را از طریق مدیریت دیتابیس برایشان ثبت کنید.
