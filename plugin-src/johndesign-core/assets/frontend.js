(()=>{
'use strict';

const links=[
  ['Site internet','/creation-site-internet/'],
  ['Identité visuelle','/identite-visuelle/'],
  ['Print & signalétique','/print-signaletique/'],
  ['Réalisations','/realisations/'],
  ['À propos','/a-propos/']
];

const menu=document.querySelector('.jd-preview-nav');
const header=document.querySelector('.jd-header');

if(menu){
  let nav=menu.querySelector('nav');
  if(!nav){
    nav=document.createElement('nav');
    menu.appendChild(nav);
  }

  nav.classList.add('jd-desktop-nav');
  nav.innerHTML='';

  links.forEach(([label,href])=>{
    const a=document.createElement('a');
    a.href=href;
    a.textContent=label;
    a.className='jd-nav-link';
    nav.appendChild(a);
  });

  const cta=document.createElement('a');
  cta.href='/contact/';
  cta.textContent='Contact';
  cta.className='jd-nav-cta';
  nav.appendChild(cta);

  const desktop=window.matchMedia('(min-width:1024px)');
  const syncMenu=()=>{
    if(desktop.matches){
      menu.open=true;
    }else if(!menu.matches(':focus-within')){
      menu.open=false;
    }
  };
  syncMenu();
  if(typeof desktop.addEventListener==='function') desktop.addEventListener('change',syncMenu);
  else if(typeof desktop.addListener==='function') desktop.addListener(syncMenu);
}

/**
 * Fixed-on-scroll header.
 * We use a real fixed state instead of CSS sticky because some WordPress/theme
 * wrappers can create containing/overflow contexts that prevent sticky from
 * reaching the viewport top.
 */
if(header){
  const spacer=document.createElement('div');
  spacer.className='jd-header-spacer';
  header.parentNode.insertBefore(spacer,header);

  let trigger=0;
  const measure=()=>{
    const wasFixed=header.classList.contains('is-fixed');
    if(wasFixed) header.classList.remove('is-fixed');
    spacer.style.height='0px';
    trigger=header.getBoundingClientRect().top+window.scrollY;
    if(wasFixed) update();
  };

  const update=()=>{
    const fixed=window.scrollY>=trigger;
    header.classList.toggle('is-fixed',fixed);
    document.documentElement.classList.toggle('jd-header-fixed',fixed);
    spacer.style.height=fixed?(header.offsetHeight+24)+'px':'0px';
  };

  measure();
  update();
  window.addEventListener('scroll',update,{passive:true});
  window.addEventListener('resize',()=>{measure();update();});
}
})();
