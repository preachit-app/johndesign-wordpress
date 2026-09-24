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
const cleanPath=window.location.pathname.replace(/\/+$/,'')||'/';

const waButton=document.querySelector('.jd-whatsapp-float');
if(waButton && !waButton.querySelector('span')){
  const label=document.createElement('span');
  label.textContent='WhatsApp';
  waButton.appendChild(label);
}

const jdTruckFromHome=async()=>{
  if(cleanPath!=='/print-signaletique') return;
  const sections=[...document.querySelectorAll('.jd-section')];
  const target=sections.find(section=>{
    const t=(section.textContent||'').toLowerCase();
    return t.includes('ia fait des merveilles') && t.includes('stickers');
  });
  if(!target) return;

  try{
    const response=await fetch(window.location.origin+'/',{credentials:'same-origin',cache:'no-store'});
    if(!response.ok) return;
    const html=await response.text();
    const doc=new DOMParser().parseFromString(html,'text/html');

    let source=[...doc.querySelectorAll('img')].find(img=>{
      const alt=(img.getAttribute('alt')||'').toLowerCase();
      return alt.includes('eco clim');
    });

    if(!source){
      const holder=[...doc.querySelectorAll('figure,.jd-home-work-item,.jd-section')].find(el=>{
        return (el.textContent||'').toLowerCase().includes('eco clim');
      });
      if(holder) source=holder.querySelector('img');
    }

    const targetImg=target.querySelector('img');
    if(!source || !targetImg) return;

    const src=source.getAttribute('src');
    if(!src) return;
    targetImg.setAttribute('src',src);
    targetImg.removeAttribute('srcset');
    targetImg.removeAttribute('sizes');
    targetImg.setAttribute('alt','Habillage adhésif Eco Clim System sur véhicule utilitaire');
  }catch(e){}
};
jdTruckFromHome();

if(['/creation-site-internet','/identite-visuelle','/print-signaletique'].includes(cleanPath)){
  const ctaMap={
    '/creation-site-internet':'Parlons de votre site',
    '/identite-visuelle':'Parlons de votre identité',
    '/print-signaletique':'Parlons de votre projet'
  };
  document.querySelectorAll('.wp-block-button__link').forEach(a=>{
    if(a.textContent.trim()==='Parlons de votre projet') a.textContent=ctaMap[cleanPath]+' ↗';
  });
}

if(cleanPath==='/a-propos'){
  const sourcePhoto=document.querySelector('.jd-about .jd-jonathan-photo img');
  const introVisual=document.querySelector('.jd-intro-visual');
  if(sourcePhoto && introVisual){
    const figure=document.createElement('figure');
    figure.className='jd-about-intro-photo';
    const img=sourcePhoto.cloneNode(true);
    img.removeAttribute('loading');
    img.setAttribute('loading','eager');
    figure.appendChild(img);
    introVisual.replaceChildren(figure);
  }

  const oldFigure=document.querySelector('.jd-about .jd-jonathan-photo');
  if(oldFigure){
    const process=document.createElement('div');
    process.className='jd-about-process-card';
    process.innerHTML=
      '<div class="jd-process-step"><strong>01</strong><span>Écouter</span></div>'+
      '<div class="jd-process-step"><strong>02</strong><span>Cadrer</span></div>'+
      '<div class="jd-process-step"><strong>03</strong><span>Créer</span></div>';
    oldFigure.replaceWith(process);
  }
}

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

/* Galerie horizontale des sites réalisés. */
document.querySelectorAll('[data-jd-web-portfolio]').forEach(gallery=>{
  const track=gallery.querySelector('[data-jd-web-portfolio-track]');
  const prev=gallery.querySelector('[data-jd-web-portfolio-prev]');
  const next=gallery.querySelector('[data-jd-web-portfolio-next]');
  if(!track) return;

  const step=()=>{
    const card=track.querySelector('.jd-web-portfolio-card');
    if(!card) return Math.max(280,track.clientWidth*.8);
    const styles=getComputedStyle(track);
    const gap=parseFloat(styles.columnGap||styles.gap||'18')||18;
    return card.getBoundingClientRect().width+gap;
  };

  const sync=()=>{
    if(prev) prev.disabled=track.scrollLeft<=4;
    if(next) next.disabled=track.scrollLeft+track.clientWidth>=track.scrollWidth-4;
  };

  if(prev) prev.addEventListener('click',()=>track.scrollBy({left:-step(),behavior:'smooth'}));
  if(next) next.addEventListener('click',()=>track.scrollBy({left:step(),behavior:'smooth'}));
  track.addEventListener('scroll',sync,{passive:true});
  window.addEventListener('resize',sync,{passive:true});
  sync();
});

/* Le portfolio principal se comporte comme une galerie : toute la carte est cliquable. */
document.querySelectorAll('.jd-site-card').forEach(card=>{
  const link=card.querySelector('a.jd-site-preview-link[href],a.jd-site-open[href]');
  if(!link) return;
  card.classList.add('is-clickable');
  card.addEventListener('click',event=>{
    if(event.target.closest('a,button')) return;
    if(link.target==='_blank') window.open(link.href,'_blank','noopener');
    else window.location.href=link.href;
  });
});

})();