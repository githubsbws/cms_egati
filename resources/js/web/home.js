/* Home page behaviours, ported from the design draft (app.js). Labels come from window.WEB_I18N (js.* strings). */
const T=(key)=>window.WEB_I18N?.[key]??key;
const presentationUrl=()=>document.querySelector('.presentation-caption a')?.href??'';
function initHeroVideo(){const v=document.getElementById('hero-video');if(!v)return;const reduced=window.matchMedia('(prefers-reduced-motion: reduce)').matches;window.egatiPauseHeroVideo=()=>v.pause();if(reduced){v.autoplay=false;v.pause()}else{v.muted=true;v.play().catch(()=>{})}}
function initPresentationArtwork(){const section=document.getElementById('presentation'),button=section?.querySelector('.presentation-motion-toggle');if(!button)return;button.onclick=()=>{const paused=section.classList.toggle('is-art-paused');button.setAttribute('aria-pressed',String(paused));button.innerHTML=`<span aria-hidden="true">${paused?'▶':'Ⅱ'}</span>${paused?T('play_background_motion'):T('pause_background_motion')}`;};}
function initStakeholderTooltips(){
 const links=[...document.querySelectorAll('.stakeholder-link')];
 function show(link){links.forEach(item=>item.classList.toggle('is-tooltip-open',item===link));}
 links.forEach(link=>{
 link.addEventListener('pointerenter',event=>{if(event.pointerType==='mouse'||event.pointerType==='pen')show(link)});
 link.addEventListener('pointerleave',()=>{if(!link.matches(':focus-visible'))link.classList.remove('is-tooltip-open')});
 link.addEventListener('focus',()=>show(link));
 link.addEventListener('blur',()=>{if(!link.matches(':hover'))link.classList.remove('is-tooltip-open')});
 link.addEventListener('keydown',event=>{if(event.key==='Escape'){links.forEach(item=>item.classList.remove('is-tooltip-open'));event.stopPropagation()}});
 });
}
function initPresentation(){initPresentationArtwork();const v=document.getElementById('company-presentation'),cover=document.querySelector('.presentation-cover'),status=document.getElementById('presentation-status'),player=document.getElementById('presentation-player');if(!v)return;const start=()=>{v.controls=true;v.removeAttribute('aria-hidden');if(!v.getAttribute('src'))v.src=presentationUrl();cover.hidden=true;status.hidden=false;status.textContent=T('loading_video_from_the_source');player.setAttribute('aria-busy','true');window.egatiPauseHeroVideo?.();v.play().catch(()=>{player.setAttribute('aria-busy','false');status.textContent=T('if_playback_does_not_start')})};document.querySelectorAll('[data-play-presentation]').forEach(b=>b.onclick=start);v.addEventListener('playing',()=>{player.setAttribute('aria-busy','false');status.hidden=true});v.addEventListener('error',()=>{player.setAttribute('aria-busy','false');status.hidden=false;status.textContent=T('the_source_video_could_not')})}
var aboutCleanup;
function initAboutStory(){
 const section=document.getElementById('about-story');if(!section)return;
 const stage=section.querySelector('.about-stage'),slides=[...section.querySelectorAll('.about-slide')],buttons=[...section.querySelectorAll('[data-about-go]')],pause=section.querySelector('.about-pause'),motion=matchMedia('(prefers-reduced-motion: reduce), (max-height: 570px), (max-width:360px) and (max-height:650px)');
 let active=-1,visible=false,userPaused=false,frame=0,disposed=false,staticMode;
 function playActive(){slides.forEach((slide,i)=>{const v=slide.querySelector('video');if(i===active&&visible&&!motion.matches&&!userPaused&&!document.hidden){if(!v.getAttribute('src')){v.src=v.dataset.src;v.load()}v.muted=true;v.play().catch(()=>{})}else v.pause()});}
 function activate(index){if(index===active)return;active=index;slides.forEach((slide,i)=>{slide.classList.toggle('is-active',i===index);slide.classList.toggle('is-before',i<index);slide.inert=!motion.matches&&i!==index;slide.setAttribute('aria-hidden',String(!motion.matches&&i!==index));});buttons.forEach((b,i)=>b.setAttribute('aria-pressed',String(i===index)));section.querySelector('.about-count b').textContent=`0${index+1}`;playActive();}
 function update(){frame=0;if(disposed)return;if(staticMode!==motion.matches){staticMode=motion.matches;active=-1;}const header=document.querySelector('header');stage.style.setProperty('--about-header',`${header.getBoundingClientRect().height}px`);if(motion.matches){slides.forEach(s=>{s.inert=false;s.removeAttribute('aria-hidden')});playActive();return;}const rect=section.getBoundingClientRect(),range=section.offsetHeight-stage.offsetHeight;const progress=Math.max(0,Math.min(1,-rect.top/Math.max(1,range)));activate(Math.min(2,Math.floor(progress*3)));section.querySelector('.about-progress span').style.transform=`scaleX(${progress})`;}
 function requestUpdate(){if(!frame)frame=requestAnimationFrame(update)}
 const observer=new IntersectionObserver(entries=>{visible=entries[0].isIntersecting;playActive()},{rootMargin:'100px 0px',threshold:0});observer.observe(stage);
 buttons.forEach(b=>b.onclick=()=>{const i=+b.dataset.aboutGo;const top=window.scrollY+section.getBoundingClientRect().top;window.scrollTo({top:top+(i===0?0:(i+.15)/3)*(section.offsetHeight-stage.offsetHeight),behavior:motion.matches?'instant':'smooth'});});
 pause.onclick=()=>{userPaused=!userPaused;pause.textContent=userPaused?'▶':'Ⅱ';pause.setAttribute('aria-pressed',String(userPaused));pause.setAttribute('aria-label',userPaused?T('play_background_video'):T('pause_background_video'));playActive()};
 window.addEventListener('scroll',requestUpdate,{passive:true});window.addEventListener('resize',requestUpdate);document.addEventListener('visibilitychange',playActive);motion.addEventListener('change',requestUpdate);update();
 aboutCleanup=()=>{disposed=true;cancelAnimationFrame(frame);observer.disconnect();window.removeEventListener('scroll',requestUpdate);window.removeEventListener('resize',requestUpdate);document.removeEventListener('visibilitychange',playActive);motion.removeEventListener('change',requestUpdate);slides.forEach(s=>s.querySelector('video').pause());aboutCleanup=null;};
}

function initQuickAccess(){
 if(!matchMedia('(hover: hover) and (pointer: fine)').matches||matchMedia('(prefers-reduced-motion: reduce)').matches)return;
 document.querySelectorAll('.quick-card').forEach(card=>{let rect;
 card.addEventListener('pointerenter',()=>{rect=card.getBoundingClientRect()});
 card.addEventListener('pointermove',event=>{if(event.pointerType!=='mouse'||!rect)return;const x=Math.max(0,Math.min(1,(event.clientX-rect.left)/rect.width)),y=Math.max(0,Math.min(1,(event.clientY-rect.top)/rect.height));card.style.setProperty('--tilt-x',`${(0.5-y)*6}deg`);card.style.setProperty('--tilt-y',`${(x-0.5)*6}deg`);card.style.setProperty('--glow-x',`${x*100}%`);card.style.setProperty('--glow-y',`${y*100}%`)});
 card.addEventListener('pointerleave',()=>{card.style.setProperty('--tilt-x','0deg');card.style.setProperty('--tilt-y','0deg');rect=null});
 });
}

var portfolioMotionStarted=false,portfolioMotionPaused=false,portfolioMotionCleanup=null;
function initPortfolioMotion(){
 const section=document.getElementById('footprint'),button=document.getElementById('portfolio-motion-toggle');if(!section||!button)return;
 const motion=window.matchMedia('(prefers-reduced-motion: reduce)');let observer=null;
 function sync(){const running=portfolioMotionStarted&&!portfolioMotionPaused&&!motion.matches;section.classList.toggle('is-motion-running',running);button.hidden=!portfolioMotionStarted||motion.matches;button.setAttribute('aria-pressed',String(!running));button.setAttribute('aria-label',T(running?'pause_background_animation':'play_background_animation'));button.innerHTML=`<span aria-hidden="true">${running?'Ⅱ':'▶'}</span><span>${T(running?'pause_animation':'play_animation')}</span>`;}
 function start(){portfolioMotionStarted=true;sync();observer?.disconnect();}
 sync();
 if(!portfolioMotionStarted){if('IntersectionObserver' in window){observer=new IntersectionObserver(entries=>{if(entries.some(entry=>entry.isIntersecting))start();},{threshold:.12});observer.observe(section);}else start();}
 section.addEventListener('pointerenter',start,{once:true});section.addEventListener('focusin',start,{once:true});motion.addEventListener('change',sync);
 button.onclick=()=>{portfolioMotionPaused=section.classList.contains('is-motion-running');start();};
 portfolioMotionCleanup=()=>{observer?.disconnect();section.removeEventListener('pointerenter',start);section.removeEventListener('focusin',start);motion.removeEventListener('change',sync);button.onclick=null;portfolioMotionCleanup=null;};
}
function initHomeNewsArtwork(){const section=document.getElementById('home-news'),button=section?.querySelector('.home-news-motion-toggle');if(!button)return;button.onclick=()=>{const paused=section.classList.toggle('is-news-art-paused');button.setAttribute('aria-pressed',String(paused));button.innerHTML=`<span aria-hidden="true">${paused?'▶':'Ⅱ'}</span>${paused?T('play_background_motion'):T('pause_background_motion')}`;};}
function updateHeroHeader(){document.body.classList.toggle('hero-scrolled',window.scrollY>35)}window.addEventListener('scroll',updateHeroHeader,{passive:true});updateHeroHeader();

var scrollRevealObserver=null;
function setupScrollAnimations(){
 if(scrollRevealObserver)scrollRevealObserver.disconnect();
 if(window.matchMedia('(prefers-reduced-motion: reduce)').matches||!('IntersectionObserver' in window))return;
 const targets=document.querySelectorAll('.quick-access-heading,.quick-card,.section-index,.section-heading,.footprint-card,.journal-header,.news-card,.knowledge-intro,.knowledge-visual,.document-card,.footer-bottom>div,.proc-source-card,.proc-category-heading,.proc-category-tile,.proc-search-panel,.proc-contact-band,.presentation-copy,.presentation-frame,.stakeholder-intro,.stakeholder-link,.home-careers-banner');
 scrollRevealObserver=new IntersectionObserver(entries=>{entries.forEach(entry=>{if(entry.isIntersecting){entry.target.classList.add('is-revealed');scrollRevealObserver.unobserve(entry.target)}})},{threshold:.1,rootMargin:'0px 0px -24px 0px'});
 targets.forEach(el=>{if(el.classList.contains('is-revealed'))return;el.classList.add('reveal-on-scroll');if(el.matches('.footprint-card,.news-card,.proc-category-tile,.stakeholder-card,.document-card')){const siblings=[...el.parentElement.children];el.style.setProperty('--reveal-delay',Math.min(siblings.indexOf(el)%3,2)*80+'ms')}scrollRevealObserver.observe(el)});
}

export function initHome(){
 if(document.getElementById('hero-video')){initHeroVideo();initHomeNewsArtwork();initPresentation();initAboutStory();initQuickAccess();initStakeholderTooltips();initPortfolioMotion();}
 requestAnimationFrame(setupScrollAnimations);
}
