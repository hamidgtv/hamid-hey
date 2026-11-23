document.querySelectorAll('[data-confirm]').forEach(el=>el.addEventListener('click',e=>{if(!confirm(el.dataset.confirm))e.preventDefault()}));
document.querySelectorAll('[data-timer]').forEach(el=>{let left=parseInt(el.dataset.timer,10);const out=el.querySelector('[data-time]');function tick(){let m=Math.floor(left/60),s=left%60;out.textContent=String(m).padStart(2,'0')+':'+String(s).padStart(2,'0');if(left<=0){const f=document.getElementById('exam-form');if(f){f.querySelector('[name=auto_submit]').value='1';f.submit()}return}left--;}tick();setInterval(tick,1000)});

// Universal animated navigation drawer
(()=>{
 const toggle=document.getElementById('menuToggle');
 const drawer=document.getElementById('appDrawer');
 const backdrop=document.getElementById('drawerBackdrop');
 const close=document.getElementById('drawerClose');
 if(!toggle||!drawer||!backdrop)return;
 let previousFocus=null;
 const open=()=>{
  if(document.body.classList.contains('drawer-open'))return;
  previousFocus=document.activeElement;
  document.body.classList.add('drawer-open');
  toggle.setAttribute('aria-expanded','true');
  drawer.setAttribute('aria-hidden','false');
  toggle.setAttribute('aria-label','بستن فهرست');
  if(close)close.focus();
 };
 const shut=()=>{
  if(!document.body.classList.contains('drawer-open'))return;
  document.body.classList.remove('drawer-open');
  toggle.setAttribute('aria-expanded','false');
  drawer.setAttribute('aria-hidden','true');
  toggle.setAttribute('aria-label','باز کردن فهرست درس‌ها و بخش‌ها');
  if(previousFocus&&typeof previousFocus.focus==='function')previousFocus.focus();
 };
 toggle.addEventListener('click',()=>document.body.classList.contains('drawer-open')?shut():open());
 backdrop.addEventListener('click',shut);
 if(close)close.addEventListener('click',shut);
 document.addEventListener('keydown',e=>{if(e.key==='Escape')shut();});
 drawer.querySelectorAll('a').forEach(a=>a.addEventListener('click',shut));
})();

/* Persistent light / dark appearance */
(()=>{
 const root=document.documentElement;
 const key='khuzestan_exam_theme';
 const buttons=[...document.querySelectorAll('[data-theme-toggle]')];
 const apply=theme=>{
  const dark=theme==='dark';
  root.dataset.theme=dark?'dark':'light';
  root.style.colorScheme=dark?'dark':'light';
  buttons.forEach(btn=>{
   const icon=btn.querySelector('.theme-toggle-icon');
   const label=btn.querySelector('.theme-toggle-label');
   if(icon)icon.textContent=dark?'☀':'☾';
   if(label)label.textContent=dark?'حالت روز':'حالت شب';
   btn.setAttribute('aria-pressed',dark?'true':'false');
   btn.setAttribute('title',dark?'فعال‌سازی تم روشن':'فعال‌سازی تم تیره');
  });
 };
 let saved='light';
 try{saved=localStorage.getItem(key)||'light';}catch(e){}
 apply(saved);
 buttons.forEach(btn=>btn.addEventListener('click',()=>{
  const next=root.dataset.theme==='dark'?'light':'dark';
  apply(next);
  try{localStorage.setItem(key,next);}catch(e){}
 }));
})();
