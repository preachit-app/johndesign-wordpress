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
  const summary=menu.querySelector('summary');
  if(summary){
    summary.textContent='Menu';
    summary.setAttribute('aria-label','Ouvrir le menu');
  }

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
 * Le header reste flottant en permanence, avec exactement la largeur,
 * l'espace supérieur et les arrondis de sa position naturelle.
 */
if(header){
  const natural=header.getBoundingClientRect();
  const computed=window.getComputedStyle(header);
  const naturalTop=Math.max(14,Math.round(natural.top));
  const naturalWidth=Math.max(280,Math.round(natural.width));
  const naturalRadius=computed.borderTopLeftRadius||'32px';

  header.style.setProperty('--jd-header-top',naturalTop+'px');
  header.style.setProperty('--jd-header-width',naturalWidth+'px');
  header.style.setProperty('--jd-header-radius',naturalRadius);

  const spacer=document.createElement('div');
  spacer.className='jd-header-spacer';
  spacer.style.height=Math.ceil(natural.height)+'px';
  header.parentNode.insertBefore(spacer,header);

  header.classList.add('is-fixed');
  document.documentElement.classList.add('jd-header-fixed');

  const resize=()=>{
    spacer.style.height=Math.ceil(header.offsetHeight)+'px';
  };
  window.addEventListener('resize',resize,{passive:true});
}
})();