/* News pages: share button, photo lightbox, reveal animation, auto-submit filters. Labels come from window.WEB_I18N. */
const T=(key)=>window.WEB_I18N?.[key]??key;
let gallery;
function initArticle(){const images=[...document.querySelectorAll('[data-nr-image] img')].map(i=>i.src);document.getElementById('nr-share').onclick=async()=>{const status=document.getElementById('nr-share-status');try{await navigator.clipboard.writeText(location.href);status.textContent=T('link_copied');}catch{status.textContent=T('copy_the_url_from_your');}};document.querySelectorAll('[data-nr-image]').forEach(button=>button.onclick=()=>{let index=Number(button.dataset.nrImage);gallery=document.createElement('dialog');gallery.className='nr-lightbox';gallery.setAttribute('aria-label',T('event_gallery'));gallery.innerHTML=`<button type="button" class="nr-lightbox-close" aria-label="${T('close_gallery')}">×</button><img alt=""><div><button type="button" data-gallery-prev aria-label="${T('previous_photo')}">←</button><span role="status"></span><button type="button" data-gallery-next aria-label="${T('next_photo')}">→</button></div>`;const update=()=>{gallery.querySelector('img').src=images[index];gallery.querySelector('img').alt=T('event_photo')+' '+(index+1);gallery.querySelector('[role=status]').textContent=(index+1)+' / '+images.length;};const move=d=>{index=(index+d+images.length)%images.length;update();};gallery.querySelector('.nr-lightbox-close').onclick=()=>gallery.close();gallery.querySelector('[data-gallery-prev]').onclick=()=>move(-1);gallery.querySelector('[data-gallery-next]').onclick=()=>move(1);gallery.onkeydown=e=>{if(e.key==='ArrowLeft')move(-1);if(e.key==='ArrowRight')move(1);};gallery.onclick=e=>{if(e.target===gallery)gallery.close();};gallery.onclose=()=>{gallery.remove();gallery=null;button.focus();};document.body.append(gallery);update();gallery.showModal();});}
function initVideoPage(){
 const v=document.getElementById('nr-video'),fallback=document.getElementById('nr-video-fallback'),sound=document.getElementById('nr-sound'),status=document.getElementById('nr-video-status');
 if(!v)return;
 v.muted=true;
 const tryPlay=()=>{const p=v.play();if(p)p.catch(()=>{if(v.isConnected){fallback.hidden=false;status.textContent=T('select_play_to_start_the');}});};
 v.addEventListener('playing',()=>{fallback.hidden=true;});
 v.addEventListener('error',()=>{fallback.hidden=false;status.innerHTML=T('the_video_could_not_load')+`<a href="${v.querySelector('source')?.src??''}" target="_blank" rel="noopener">${T('open_the_source_website')} ↗</a>`;});
 v.addEventListener('volumechange',()=>{sound.setAttribute('aria-pressed',String(!v.muted));sound.innerHTML=T(v.muted?'turn_sound_on':'mute_video')+' <span aria-hidden="true">♫</span>';status.textContent=T(v.muted?'video_sound_is_muted':'video_sound_is_on');});
 fallback.onclick=()=>{if(v.error)v.load();tryPlay();};
 sound.onclick=()=>{v.muted=!v.muted;if(v.paused)tryPlay();};
 tryPlay();
}
export function initNews(){
 if(document.getElementById('nr-share')||document.querySelector('[data-nr-image]'))initArticle();
 initVideoPage();
 document.querySelectorAll('[data-autosubmit]').forEach(el=>el.addEventListener('change',()=>el.form.requestSubmit()));
 if('IntersectionObserver' in window&&!matchMedia('(prefers-reduced-motion: reduce)').matches){const observer=new IntersectionObserver(entries=>entries.forEach(e=>{if(e.isIntersecting){e.target.classList.add('nr-in-view');observer.unobserve(e.target);}}),{threshold:.08});document.querySelectorAll('.nr-featured,.nr-section-title,.nr-video-cta,.nr-video-next').forEach(e=>{e.classList.add('nr-reveal');observer.observe(e);});}
}
