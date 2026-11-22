/* Header, dropdown menus, language switch and mobile drawer, ported from the design draft (header.js). */
/* One shared header and disclosure controller for every route. */
export function initHeader(){'use strict';
const roots='.nav-about-item,.nr-nav-item,.pub-nav-item,.site-language';
const chevron=open=>`<svg class="header-chevron" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="${open?'m5 12 5-5 5 5':'m5 8 5 5 5-5'}"/></svg>`;
const paths={company:'<path d="M4 21V5l8-3 8 3v16M8 8h2m4 0h2M8 12h2m4 0h2M8 16h2m4 0h2M10 21v-3h4v3"/>',chart:'<rect x="8" y="2" width="8" height="6" rx="1"/><path d="M12 8v5M4 16v-3h16v3M12 13v3"/><rect x="1" y="16" width="6" height="5" rx="1"/><rect x="9" y="16" width="6" height="5" rx="1"/><rect x="17" y="16" width="6" height="5" rx="1"/>',people:'<circle cx="9" cy="7" r="3"/><path d="M3 21v-4a6 6 0 0 1 12 0v4M17 4a3 3 0 0 1 0 6m2 11v-4a5 5 0 0 0-2-4"/>',shield:'<path d="m12 3 8 3v5c0 5-3 8-8 10-5-2-8-5-8-10V6l8-3Z"/><path d="m8 12 3 3 5-6"/>',leaf:'<path d="M20 3C9 2 3 7 5 14c2 6 10 7 13 1 2-4 2-8 2-12ZM4 21 15 10"/>',growth:'<path d="M3 4v17h18M7 15l5-5 4 2 5-8m-6 0h6v6"/>'};
const icon=k=>`<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${paths[k]}</svg>`;
function setOpen(root,open){const button=root.querySelector(':scope > button');const panel=document.getElementById(button.getAttribute('aria-controls'));root.classList.toggle('is-open',open);button.setAttribute('aria-expanded',String(open));panel.inert=!open;panel.setAttribute('aria-hidden',String(!open));const arrow=button.querySelector('.header-chevron');if(arrow)arrow.outerHTML=chevron(open);}
function closeAll(except){document.querySelectorAll('#site-header '+roots.split(',').join(',#site-header ')).forEach(root=>{if(root!==except)setOpen(root,false);});}
function bindRoot(root){if(!root)return;const button=root.querySelector(':scope > button'),panel=document.getElementById(button.getAttribute('aria-controls'));if(!root.classList.contains('site-language'))button.innerHTML=chevron(false);setOpen(root,false);
button.onclick=ev=>{ev.stopPropagation();const open=button.getAttribute('aria-expanded')!=='true';closeAll(root);setOpen(root,open);};
root.onpointerenter=null;root.onpointerleave=null;root.onfocusin=null;
root.onfocusout=ev=>{if(!root.contains(ev.relatedTarget))setOpen(root,false);};
root.onkeydown=ev=>{const links=[...panel.querySelectorAll('a,button')];if(ev.key==='Escape'){ev.preventDefault();ev.stopPropagation();setOpen(root,false);button.focus();return;}if(ev.target===button&&(ev.key==='ArrowDown'||ev.key==='ArrowUp')){ev.preventDefault();closeAll(root);setOpen(root,true);links[ev.key==='ArrowDown'?0:links.length-1]?.focus();return;}if(panel.contains(ev.target)&&['ArrowDown','ArrowUp','Home','End'].includes(ev.key)){ev.preventDefault();const index=links.indexOf(ev.target);const next=ev.key==='Home'?0:ev.key==='End'?links.length-1:(index+(ev.key==='ArrowDown'?1:links.length-1))%links.length;links[next]?.focus();}};
panel.querySelectorAll('a').forEach(a=>a.onclick=()=>setOpen(root,false));
}
window.egatiBindUnifiedNav=selector=>{const root=document.querySelector(selector);if(!root)return;if(selector==='.nav-about-item'){
const types={'#about':'company','#about-structure':'chart','#about-board':'people','#about-management':'people','#about-governance':'shield','#about-csr':'leaf','#about-development':'growth'};
root.querySelectorAll('.nav-about-menu a').forEach(a=>{if(a.dataset.headerDecorated)return;const text=a.textContent;a.innerHTML=`<i class="header-link-icon">${icon(types['#'+new URL(a.href).pathname.split('/').slice(2).join('-')]||'company')}</i><span class="header-link-text"></span><span class="header-link-arrow" aria-hidden="true">→</span>`;a.querySelector('.header-link-text').textContent=text;a.dataset.headerDecorated='true';});
}bindRoot(root);};
window.egatiBindAboutNav=()=>window.egatiBindUnifiedNav('.nav-about-item');
window.egatiBindNewsNav=()=>window.egatiBindUnifiedNav('.nr-nav-item');
window.egatiBindPublicationNav=()=>window.egatiBindUnifiedNav('.pub-nav-item');
window.egatiBindLanguage=()=>{const root=document.querySelector('.site-language');if(!root)return;bindRoot(root);root.querySelectorAll('[data-language]').forEach(b=>b.onclick=()=>{setOpen(root,false);if(b.getAttribute('aria-pressed')!=='true')window.location.href=b.dataset.href;});};
const mobileNavQuery=window.matchMedia('(max-width:1100px)');
function mobileNavOpen(){return document.body.classList.contains('mobile-nav-open')}
function moveLanguage(compact){const header=document.getElementById('site-header'),nav=document.getElementById('nav'),language=document.querySelector('.site-language');if(!header||!nav||!language)return;if(compact&&language.parentElement!==nav)nav.append(language);if(!compact&&language.parentElement!==header)header.append(language);}
window.egatiPrepareResponsiveHeader=()=>moveLanguage(false);
function syncMobileNav(){const nav=document.getElementById('nav'),button=document.getElementById('responsive-menu-toggle');if(!nav||!button)return;const compact=mobileNavQuery.matches,open=mobileNavOpen();moveLanguage(compact);nav.inert=compact&&!open;nav.setAttribute('aria-hidden',String(compact&&!open));button.setAttribute('aria-expanded',String(compact&&open));button.setAttribute('aria-label',compact&&open?button.dataset.labelClose:button.dataset.labelOpen);if(!compact){document.body.classList.remove('mobile-nav-open');nav.inert=false;nav.removeAttribute('aria-hidden');}}
function closeMobileNav({focus=false}={}){const button=document.getElementById('responsive-menu-toggle');document.body.classList.remove('mobile-nav-open');syncMobileNav();if(focus&&mobileNavQuery.matches)button?.focus();}
function openMobileNav(){if(!mobileNavQuery.matches)return;closeAll();document.body.classList.add('mobile-nav-open');syncMobileNav();requestAnimationFrame(()=>document.querySelector('#nav>a,#nav>div>a')?.focus());}
window.egatiSetupResponsiveHeader=()=>{const button=document.getElementById('responsive-menu-toggle');if(!button)return;button.onclick=()=>mobileNavOpen()?closeMobileNav({focus:true}):openMobileNav();document.querySelectorAll('#nav>a,#nav>div>a').forEach(a=>a.addEventListener('click',()=>closeMobileNav()));syncMobileNav();};
document.addEventListener('click',ev=>{const root=ev.target.closest(roots);closeAll(root);});
document.addEventListener('click',ev=>{if(mobileNavOpen()&&!ev.target.closest('#site-header'))closeMobileNav();});
document.addEventListener('keydown',ev=>{if(ev.key==='Escape'&&mobileNavOpen()){ev.preventDefault();closeMobileNav({focus:true});}});
mobileNavQuery.addEventListener?.('change',()=>closeMobileNav());
const header=document.getElementById('site-header');function measure(){const height=Math.ceil(header.getBoundingClientRect().height);document.documentElement.style.setProperty('--site-header-height',height+'px');document.body.style.setProperty('--hero-header-space',height+'px');}
if('ResizeObserver' in window)new ResizeObserver(measure).observe(header);window.addEventListener('resize',measure);requestAnimationFrame(measure);
window.egatiBindAboutNav();window.egatiBindNewsNav();window.egatiBindPublicationNav();window.egatiBindLanguage();window.egatiSetupResponsiveHeader();markActiveNav();}
function markActiveNav(){const section=location.pathname.split('/')[2]||'';if(!section)return;document.querySelectorAll('#nav>a,.nav-about-link,.nr-nav-item>a,.pub-nav-item>a').forEach(a=>{const s=new URL(a.href).pathname.split('/')[2]||'';const hit=s===section||(section==='news'&&s==='news');a.classList.toggle('active',hit);});}
