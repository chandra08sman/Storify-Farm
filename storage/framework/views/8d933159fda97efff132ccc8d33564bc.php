<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo e($title ?? 'Storify Farm — Smart Warehouse Management System'); ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,0..1" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
<script src="https://unpkg.com/@zxing/library@0.21.3"></script>
<script>
tailwind.config = {
  theme: {
    extend: {
      colors: {
        bg:"#f6f4ee", primary:"#2c4a3b", primary2:"#47624f",
        sage:"#dcdfd0", sage2:"#eeece2", cream:"#faf9f4",
        text:"#1c211d", muted:"#5c6259", line:"#d7d9cc",
        amber:"#d8a13c", danger:"#b3261e"
      },
      fontFamily:{ sans:["Hanken Grotesk","sans-serif"], mono:["JetBrains Mono","monospace"] }
    }
  }
}
</script>
<style>
*{box-sizing:border-box}
body{margin:0;background:rgb(246 244 238);color:rgb(28 33 29);font-family:"Hanken Grotesk",sans-serif}
body{background:rgb(var(--page-bg,246 244 238));color:rgb(var(--ink,28 33 29))}
.material-symbols-outlined{font-size:22px;vertical-align:middle}
.card{background:#fff;border:1px solid #d7d9cc;border-radius:16px;box-shadow:0 4px 20px rgba(71,98,79,.08);transition:transform .2s ease,box-shadow .2s ease}
.shape,.card,.loading-area-fixed{background-color:rgb(var(--surface,255 255 255));border-color:rgb(var(--border-c,215 217 204));color:rgb(var(--ink,28 33 29))}
.card:hover{transform:translateY(-2px);box-shadow:0 10px 28px rgba(71,98,79,.14)}
.input{width:100%;height:44px;padding:0 12px;border:1px solid #bcc9b8;border-radius:8px;background:#fff;outline:none;transition:.15s}
.input{background-color:rgb(var(--surface,255 255 255));border-color:rgb(var(--border-c,188 201 184));color:rgb(var(--ink,28 33 29))}
.input:focus{border:2px solid #2c4a3b;padding-left:11px}
textarea.input{height:auto;padding:10px}
.tag-corner{position:relative}
.tag-corner::before{content:"";position:absolute;top:14px;left:-1px;width:10px;height:10px;background:#f6f4ee;border-right:1px solid #d7d9cc;border-bottom:1px solid #d7d9cc;transform:rotate(45deg);border-radius:2px}
.tag-corner::before{background:rgb(var(--page-bg,246 244 238));border-color:rgb(var(--border-c,215 217 204))}
.eyebrow{font:600 12px/1 "JetBrains Mono";letter-spacing:.06em;text-transform:uppercase}
nav a.navlink{position:relative;color:#47624f;font-weight:500}
nav a.navlink::after{content:"";position:absolute;left:0;bottom:-4px;width:0;height:2px;background:#2c4a3b;transition:.2s}
nav a.navlink:hover::after{width:100%}
.grain-bg{background-image:radial-gradient(circle,#2c4a3b 1px,transparent 1.4px);background-size:16px 16px;opacity:.08}
.hero-batch{font:600 13px/1 "JetBrains Mono";color:#2c4a3b;opacity:.35;letter-spacing:.04em}

.ambient-bg{position:fixed;inset:0;overflow:hidden;pointer-events:none;z-index:-1}
.orb{position:absolute;border-radius:50%;filter:blur(70px);will-change:transform;animation:orbFloat ease-in-out infinite}
.orb1{width:38vw;max-width:420px;height:38vw;max-height:420px;background:#a9c2a0;opacity:.38;top:-10%;left:-8%;animation-duration:26s}
.orb2{width:30vw;max-width:340px;height:30vw;max-height:340px;background:#d8a13c;opacity:.26;bottom:-12%;right:-6%;animation-duration:21s;animation-delay:-6s}
.orb3{width:24vw;max-width:280px;height:24vw;max-height:280px;background:#47624f;opacity:.2;top:42%;left:60%;animation-duration:30s;animation-delay:-13s}
.orb4{width:18vw;max-width:220px;height:18vw;max-height:220px;background:#2c4a3b;opacity:.14;top:68%;left:18%;animation-duration:24s;animation-delay:-9s}
.orb-onDark{background:#ffffff;opacity:.05}
.orb-onDark.orb2{background:#d8a13c;opacity:.15}
.orb-onDark.orb4{background:#a9c2a0;opacity:.08}
.ambient-bg.subtle .orb{opacity:.12;filter:blur(90px)}
.ambient-bg.subtle .orb2{opacity:.09}
@keyframes orbFloat{
  0%,100%{transform:translate(0,0) scale(1)}
  33%{transform:translate(28px,-24px) scale(1.08)}
  66%{transform:translate(-20px,18px) scale(.93)}
}
.scan-sweep{position:absolute;left:-15%;right:-15%;height:26vh;background:linear-gradient(180deg,transparent,rgba(44,74,59,.05) 45%,rgba(216,161,60,.06) 55%,transparent);transform:rotate(-3deg);animation:scanMove 11s ease-in-out infinite}
.scan-sweep.on-dark{background:linear-gradient(180deg,transparent,rgba(255,255,255,.05) 45%,rgba(216,161,60,.09) 55%,transparent)}
@keyframes scanMove{
  0%,100%{transform:translateY(-15vh) rotate(-3deg)}
  50%{transform:translateY(85vh) rotate(-3deg)}
}
.grain-drift span{position:absolute;bottom:-24px;width:5px;height:9px;background:#d8a13c;border-radius:50% 50% 50% 0;opacity:0;animation:grainRise linear infinite}
.grain-drift.on-dark span{background:#ffffff}
@keyframes grainRise{
  0%{transform:translateY(0) rotate(0deg);opacity:0}
  8%{opacity:.5}
  88%{opacity:.3}
  100%{transform:translateY(-115vh) rotate(150deg);opacity:0}
}
.float-icon{position:absolute;opacity:.07;color:#2c4a3b;animation:iconFloat ease-in-out infinite}
.float-icon.on-dark{color:#fff;opacity:.05}
@keyframes iconFloat{
  0%,100%{transform:translateY(0) rotate(-6deg)}
  50%{transform:translateY(-22px) rotate(6deg)}
}
@media (prefers-reduced-motion: reduce){
  .orb,.grain-drift span,.scan-sweep,.float-icon{animation:none}
}

.mascot{position:fixed;right:10px;bottom:10px;z-index:60;cursor:pointer;display:flex;flex-direction:column;align-items:center;will-change:transform}
.mascot-float{display:flex;flex-direction:column;align-items:center;animation:mascotBob 3.2s ease-in-out infinite;will-change:transform;cursor:grab;touch-action:none}
.mascot-float:active{cursor:grabbing}
.mascot:hover .mascot-float{animation-play-state:paused}
.mascot svg{filter:drop-shadow(0 10px 20px rgba(44,74,59,.28));transition:transform .15s ease}
.mascot:active svg{transform:scale(.94)}
@keyframes mascotBob{0%,100%{transform:translateY(0) rotate(0deg)}50%{transform:translateY(-9px) rotate(-2deg)}}
.mascot-pulse{animation:mascotPulse 1.8s ease-in-out infinite}
@keyframes mascotPulse{0%,100%{opacity:.6}50%{opacity:1}}
.mascot-bubble{margin-top:8px;background:#fff;border:1px solid #d7d9cc;padding:5px 12px;border-radius:999px;font-size:12px;font-weight:700;color:#2c4a3b;box-shadow:0 6px 14px rgba(0,0,0,.1);opacity:0;transform:translateY(4px);transition:.2s;white-space:nowrap}
.mascot:hover .mascot-bubble{opacity:1;transform:translateY(0)}
@media (prefers-reduced-motion: reduce){ .mascot-float{animation:none} }

.mascot-greeting{order:-1;max-width:230px;margin-bottom:10px;background:#fff;border:1px solid #d7d9cc;border-radius:16px;padding:10px 14px;font-size:13px;line-height:1.45;color:#23392c;box-shadow:0 10px 26px rgba(44,74,59,.18);opacity:0;transform:translateY(8px) scale(.96);transition:opacity .35s ease,transform .35s ease;pointer-events:none}
.mascot-greeting.show{opacity:1;transform:translateY(0) scale(1)}
.mascot-caret{display:inline-block;width:2px;height:13px;background:#23392c;margin-left:2px;vertical-align:-2px;animation:caretBlink .8s step-end infinite}
.mascot-caret.done{display:none}
@keyframes caretBlink{0%,100%{opacity:1}50%{opacity:0}}
@media (prefers-reduced-motion: reduce){ .mascot-caret{animation:none} }

.mascot.mascot-mini{right:18px;bottom:18px}
.mascot.mascot-mini svg{width:52px;height:56px}
.mascot.mascot-mini .mascot-bubble,.mascot.mascot-mini .mascot-greeting{display:none!important}

.ai-chat-panel{--surface:255 255 255;--surface2:250 249 244;--surface-tint:241 239 228;--ink:35 57 44;--accent:44 74 59;--accent2:71 98 79;position:fixed;right:22px;bottom:112px;width:340px;max-width:calc(100vw - 32px);max-height:min(520px,70vh);background:rgb(var(--surface));border:1px solid rgb(215 217 204);border-radius:18px;box-shadow:0 20px 50px rgba(44,74,59,.25);z-index:60;flex-direction:column;overflow:hidden;transform-origin:bottom right;transform:scale(.9) translateY(10px);opacity:0;pointer-events:none;transition:.18s ease;display:none}
.ai-chat-panel.theme-dark{--surface:28 34 29;--surface2:35 42 36;--surface-tint:44 52 45;--ink:234 238 231}
.ai-chat-panel.accent-blue{--accent:29 78 216;--accent2:30 64 175}
.ai-chat-panel.accent-orange{--accent:194 65 12;--accent2:154 52 18}
.ai-chat-panel.accent-purple{--accent:124 58 237;--accent2:109 40 217}
.ai-chat-panel.open{display:flex;transform:scale(1) translateY(0);opacity:1;pointer-events:auto}
.ai-chat-head{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:12px 14px;background:rgb(var(--accent));color:#fff;flex-shrink:0}
.ai-icon-btn{width:28px;height:28px;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#fff}
.ai-icon-btn:hover{background:rgba(255,255,255,.15)}
.ai-messages{flex:1;min-height:0;overflow-y:auto;padding:14px;display:flex;flex-direction:column;gap:10px;background:rgb(var(--surface2));color:rgb(var(--ink))}
.ai-msg{max-width:85%;padding:9px 12px;border-radius:14px;font-size:13.5px;line-height:1.45;white-space:pre-wrap}
.ai-msg.bot{align-self:flex-start;background-color:rgb(var(--surface-tint,241 239 228))!important;color:rgb(var(--ink,35 57 44))!important;border:1px solid rgb(var(--accent,44 74 59) / .16);border-bottom-left-radius:4px}
.ai-msg.user{align-self:flex-end;background-color:rgb(var(--accent,44 74 59))!important;color:#fff!important;border:1px solid rgb(var(--accent,44 74 59));border-bottom-right-radius:4px}
.ai-msg.typing{display:flex;gap:4px;align-items:center;padding:12px}
.ai-msg.typing span{width:6px;height:6px;border-radius:50%;background:#23392c;opacity:.4;animation:aiTyping 1s infinite}
.ai-msg.typing span:nth-child(2){animation-delay:.15s}
.ai-msg.typing span:nth-child(3){animation-delay:.3s}
@keyframes aiTyping{0%,100%{opacity:.3;transform:translateY(0)}50%{opacity:1;transform:translateY(-3px)}}
.ai-chat-form{display:flex;flex-direction:column;gap:8px;padding:12px;border-top:1px solid rgb(var(--border-c,230 228 217));flex-shrink:0;background:rgb(var(--surface))}
.ai-chat-form .ai-chat-input-row{display:flex;gap:8px;width:100%}
.ai-chat-form .input{flex:1}
.ai-send-btn{width:40px;height:40px;border-radius:10px;background:rgb(var(--accent));color:#fff;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.ai-send-btn:hover{background:rgb(var(--accent2))}
@media (max-width:480px){
  .mascot{right:10px;bottom:10px}
  .mascot-float{transform:scale(.85)}
  .mascot-greeting{max-width:180px;font-size:12.5px}
  .ai-chat-panel{right:12px;left:12px;width:auto;bottom:100px}
}

.ai-chat-panel.mini .ai-chat-head{background:linear-gradient(135deg,rgb(var(--accent)),rgb(var(--accent2)))}
.ai-mini-expand{display:flex;align-items:center;gap:4px;font:600 11.5px/1 "JetBrains Mono";letter-spacing:.02em;color:#fff;background:rgba(255,255,255,.16);border:1px solid rgba(255,255,255,.28);padding:6px 10px;border-radius:999px;flex-shrink:0}
.ai-mini-expand:hover{background:rgba(255,255,255,.28)}
.ai-inline-icon{font-size:15px;vertical-align:-3px;color:#d8a13c}
.ai-msg.user .ai-inline-icon{color:#fff}

.nav-item{display:flex;align-items:center;gap:12px;padding:11px 12px;border-radius:8px;color:#5c6259;transition:.2s;text-decoration:none;position:relative}
.nav-item::before{content:"";position:absolute;left:-5px;top:50%;translate:0 -50%;width:3px;height:0;border-radius:3px;background:#2c4a3b;transition:height .2s ease}
.nav-item:hover{background:#eeece2;transform:translateX(3px)}
.nav-item.active{background:#dcdfd0;color:#47624f;font-weight:700}
.nav-item.active::before{height:60%}
.nav-item.active,.nav-item:hover{background-color:rgb(var(--surface-tint2,220 223 208))}
.page{display:none}
.page.active{display:block;animation:fadeIn .3s ease}
@keyframes fadeIn{from{opacity:0;transform:translateY(6px)}to{opacity:1;transform:translateY(0)}}
.table-wrap{overflow:auto;border:1px solid #e3e1d4;border-radius:10px}
table{width:100%;border-collapse:collapse;min-width:760px}
th,td{padding:12px;text-align:left;border-bottom:1px solid #e3e1d4}
th{font:500 13px "JetBrains Mono";background:#ece9dd;color:#5c6259}
tr:nth-child(even){background:#f8f7f2}
tbody tr{transition:.15s}
tbody tr:hover{background:#eeece2!important}
.badge{display:inline-flex;align-items:center;gap:4px;padding:5px 9px;border-radius:999px;font:500 12px "JetBrains Mono"}
.warehouse-layout{display:flex;flex-direction:column;gap:18px}
.loading-area-fixed{border:2px solid #d8a13c;background:#fffaf0;color:#6b4a1c;border-radius:12px;padding:18px;text-align:center;box-shadow:0 4px 14px rgba(107,74,28,.08)}
.storify-rack{cursor:grab;touch-action:none}
.storify-rack-readonly{cursor:pointer;touch-action:auto}
.storify-rack:active{cursor:grabbing}
.storify-rack.dragging{opacity:.45}
.toast{position:fixed;right:24px;bottom:24px;background:rgb(var(--accent,44 74 59));color:#fff;padding:13px 18px;border-radius:10px;box-shadow:0 8px 30px #0002;z-index:200;transform:translateY(120px);opacity:0;transition:.25s}
.toast{border:1px solid rgb(var(--accent2,71 98 79));}
.toast.show{transform:none;opacity:1}
.mobile-menu{display:none}
.drawer-overlay{display:none;position:fixed;inset:0;background:rgb(var(--accent,44 74 59) / .35);z-index:45}
.drawer-overlay.show{display:block}
@media(max-width:767px){
 #view-app aside{position:fixed;left:0;top:0;height:100vh;transform:translateX(-100%);transition:transform .25s ease;z-index:50;display:flex!important}
 #view-app aside.open{transform:translateX(0)}
 #view-app .main{margin-left:0!important}
 .mobile-menu{display:flex}
 .desktop-search{display:none!important}
 .content{padding:16px!important}
}
#view-app{
  --accent:44 74 59;      --accent2:71 98 79;     --accent-ink:44 74 59;
  --surface:255 255 255; --surface2:250 249 244; --surface-tint:238 236 226; --surface-tint2:220 223 208;
  --page-bg:246 244 238; --ink:28 33 29;         --ink-muted:92 98 89;      --border-c:215 217 204;
}
#view-app.accent-blue  {--accent:29 78 216;  --accent2:30 64 175;  --accent-ink:29 78 216;}
#view-app.accent-orange{--accent:194 65 12;  --accent2:154 52 18;  --accent-ink:194 65 12;}
#view-app.accent-purple{--accent:124 58 237; --accent2:109 40 217; --accent-ink:124 58 237;}
#view-app.accent-custom{--accent:var(--custom-accent,44 74 59); --accent2:var(--custom-accent2,71 98 79); --accent-ink:var(--custom-accent-ink,44 74 59);}

#view-app.theme-dark{
  --surface:28 34 29;    --surface2:35 42 36;    --surface-tint:35 42 36;   --surface-tint2:44 52 45;
  --page-bg:19 23 20;    --ink:234 238 231;      --ink-muted:150 158 146;  --border-c:48 55 49;
  --accent-ink:167 197 178;
}
#view-app.theme-dark.accent-blue  {--accent-ink:147 197 253;}
#view-app.theme-dark.accent-orange{--accent-ink:253 186 116;}
#view-app.theme-dark.accent-purple{--accent-ink:196 165 245;}

#view-app.theme-dark,#view-app.theme-dark main{background:rgb(var(--page-bg));color:rgb(var(--ink))}
#view-app.theme-dark .card,#view-app.theme-dark aside,#view-app.theme-dark table,#view-app.theme-dark .ai-box,#view-app.theme-dark #userMenu,#view-app.theme-dark #notifPanel{background:rgb(var(--surface));border-color:rgb(var(--border-c));color:rgb(var(--ink))}
#view-app.theme-dark header{background:rgb(var(--page-bg) / .92)!important;border-color:rgb(var(--border-c))!important}
#view-app.theme-dark th{background:rgb(var(--surface2));color:rgb(var(--ink))}
#view-app.theme-dark td,#view-app.theme-dark tr{border-color:rgb(var(--border-c))!important}
#view-app.theme-dark tr:nth-child(even){background:rgb(var(--surface) / .6)}
#view-app.theme-dark tbody tr:hover{background:rgb(var(--surface2))!important}
#view-app.theme-dark .input{background:rgb(var(--page-bg));border-color:rgb(var(--border-c));color:rgb(var(--ink))}
#view-app.theme-dark .nav-item{color:rgb(var(--ink-muted))}
#view-app.theme-dark .nav-item:hover{background:rgb(var(--surface2))}
#view-app.theme-dark .text-muted{color:rgb(var(--ink-muted))!important}
#view-app.theme-dark .border-line{border-color:rgb(var(--border-c))!important}
#view-app.theme-dark .text-text{color:rgb(var(--ink))!important}
#view-app.theme-dark .bg-sage2,#view-app.theme-dark .hover\:bg-sage2:hover{background:rgb(var(--surface-tint))!important}
#view-app.theme-dark .bg-sage,#view-app.theme-dark .hover\:bg-sage:hover{background:rgb(var(--surface-tint2))!important}
#view-app.theme-dark .text-danger{color:#ff8a8a!important}

#view-app.theme-dark .shape,#view-app.theme-dark .card,#view-app.theme-dark .loading-area-fixed{background-color:rgb(var(--surface))!important;color:rgb(var(--ink))}
#view-app.theme-dark .input{background-color:rgb(var(--page-bg))!important;color:rgb(var(--ink))}

#view-app.theme-dark .bg-amber\/20,#view-app.theme-dark .bg-amber\/30,#view-app.theme-dark .bg-amber\/40{background-color:rgb(216 161 60 / .85)!important}


.bg-primary{background-color:rgb(var(--accent,44 74 59))!important}
.hover\:bg-primary2:hover,.bg-primary2{background-color:rgb(var(--accent2,71 98 79))!important}
.bg-primary\/10{background-color:rgb(var(--accent,44 74 59) / .1)!important}
.bg-primary\/15{background-color:rgb(var(--accent,44 74 59) / .15)!important}
.bg-sage{background-color:rgb(var(--surface-tint2,220 223 208))!important}
.bg-sage2{background-color:rgb(var(--surface-tint,238 236 226))!important}
.bg-amber\/20{background-color:rgb(216 161 60 / .2)!important}
.bg-amber\/30{background-color:rgb(216 161 60 / .3)!important}
.bg-amber\/40{background-color:rgb(216 161 60 / .4)!important}
.input:focus{border-color:rgb(var(--accent,44 74 59))!important}


.text-primary,.text-primary2{color:rgb(var(--accent-ink,44 74 59))!important}
.border-primary,.hover\:border-primary:hover{border-color:rgb(var(--accent-ink,44 74 59))!important}
.nav-item.active{background:rgb(var(--accent-ink,44 74 59) / .15)!important;color:rgb(var(--accent-ink,44 74 59))!important}
.nav-item.active::before{background:rgb(var(--accent-ink,44 74 59))!important}

.accent-primary{accent-color:rgb(var(--accent,44 74 59))!important}
[data-roles].role-hidden{display:none!important}

</style>
</head>
<body>

<div id="toast" class="toast"></div>

<div id="mascot" class="mascot" onclick="mascotAction()" title="Tanya Storify Assistant">
  <div id="mascotGreeting" class="mascot-greeting" aria-live="polite">
    <span class="material-symbols-outlined text-[15px]" style="vertical-align:-2px;color:#d8a13c">waving_hand</span> <span id="mascotGreetingText"></span><span id="mascotGreetingCaret" class="mascot-caret" aria-hidden="true"></span>
  </div>
  <div class="mascot-float">
    <svg id="mascotSvg" viewBox="0 0 120 130" width="88" height="95" aria-hidden="true">
      <ellipse cx="60" cy="120" rx="28" ry="5" fill="#2c4a3b" opacity=".14"></ellipse>
      <g id="mascotBody">
        <path d="M60 8c9 5 9 20 0 26-9-6-9-21 0-26Z" fill="#a9c2a0"></path>
        <circle cx="60" cy="34" r="4" fill="#d8a13c" class="mascot-pulse"></circle>
        <ellipse cx="60" cy="76" rx="44" ry="40" fill="#3a6b4d"></ellipse>
        <circle class="eye-white" cx="42" cy="70" r="12" fill="#fff"></circle>
        <circle class="eye-white" cx="78" cy="70" r="12" fill="#fff"></circle>
        <circle class="pupil" data-bx="42" data-by="70" cx="42" cy="70" r="5.5" fill="#2c4a3b"></circle>
        <circle class="pupil" data-bx="78" data-by="70" cx="78" cy="70" r="5.5" fill="#2c4a3b"></circle>
        <path d="M46 92c6 6 22 6 28 0" stroke="#2c4a3b" stroke-width="3" fill="none" stroke-linecap="round"></path>
      </g>
    </svg>
    <span class="mascot-bubble flex items-center gap-1">Tanya AI<span class="material-symbols-outlined text-[14px]">waving_hand</span></span>
  </div>
</div>

<div id="aiChatPanel" class="ai-chat-panel">
  <div class="ai-chat-head">
    <div class="flex items-center gap-2"><svg viewBox="0 0 48 48" width="22" height="22" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M24 4c3 2 3 7 0 9-3-2-3-7 0-9Z"></path><circle cx="24" cy="8" r="1.3" fill="currentColor" stroke="none"></circle><ellipse cx="24" cy="27" rx="16" ry="14.5"></ellipse><circle cx="18" cy="26" r="2.6"></circle><circle cx="30" cy="26" r="2.6"></circle><path d="M18 34c3 3 9 3 12 0"></path></svg><span class="font-semibold">Storify Assistant</span></div>
    <div class="flex items-center gap-1">
      <button type="button" onclick="toggleAIChat()" class="ai-icon-btn" title="Tutup"><span class="material-symbols-outlined text-[18px]">close</span></button>
    </div>
  </div>
  <div id="aiMessages" class="ai-messages"></div>
  <form id="aiChatForm" class="ai-chat-form" onsubmit="return sendAIMessage(event)">
    <div class="ai-chat-input-row">
      <input id="aiChatInput" class="input" placeholder="Tulis pertanyaan tentang Storify Farm..." autocomplete="off">
      <button type="submit" class="ai-send-btn"><span class="material-symbols-outlined">send</span></button>
    </div>
  </form>
</div>

<div id="aiMiniPanel" class="ai-chat-panel mini">
  <div class="ai-chat-head">
    <div class="flex items-center gap-2"><svg viewBox="0 0 48 48" width="22" height="22" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M24 4c3 2 3 7 0 9-3-2-3-7 0-9Z"></path><circle cx="24" cy="8" r="1.3" fill="currentColor" stroke="none"></circle><ellipse cx="24" cy="27" rx="16" ry="14.5"></ellipse><circle cx="18" cy="26" r="2.6"></circle><circle cx="30" cy="26" r="2.6"></circle><path d="M18 34c3 3 9 3 12 0"></path></svg><span class="font-semibold">Storify AI</span></div>
    <div class="flex items-center gap-1.5">
      <button type="button" onclick="openFullAIPage()" class="ai-mini-expand" title="Buka halaman penuh Storify AI"><span class="material-symbols-outlined">open_in_full</span>Halaman penuh</button>
      <button type="button" onclick="toggleMiniAIPanel()" class="ai-icon-btn" title="Tutup"><span class="material-symbols-outlined text-[18px]">close</span></button>
    </div>
  </div>
  <div id="aiMiniMessages" class="ai-messages"></div>
  <form id="aiMiniForm" class="ai-chat-form" onsubmit="return sendAppAIMessage(event)">
    <div class="ai-chat-input-row">
      <input id="aiMiniInput" class="input" placeholder="Tulis pertanyaan apa saja..." autocomplete="off">
      <button type="submit" class="ai-send-btn"><span class="material-symbols-outlined">send</span></button>
    </div>
  </form>
</div>

<?php echo $__env->yieldContent('content'); ?>

<script src="<?php echo e(asset('js/app.js')); ?>?v=<?php echo e(filemtime(public_path('js/app.js'))); ?>-theme2"></script>
<?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH D:\Punya Azmi\Mechanical\Informatika\Storify Farm\versi 3\storify-farm tessssssssssssssssssss\resources\views/layouts/base.blade.php ENDPATH**/ ?>