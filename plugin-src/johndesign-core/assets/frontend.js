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

/* Accueil 2.16.18 — ordre commercial : intérêt → preuve → confiance → contact. */
if(document.body.classList.contains('home')){
  const roleOrder=['hero','services','work','why','proof','reviews','about','method','faq','contact'];
  const roleNodes=new Map();

  document.querySelectorAll('[data-jd-home-role]').forEach(node=>{
    const role=node.getAttribute('data-jd-home-role');
    if(role && !roleNodes.has(role)) roleNodes.set(role,node);
  });

  const first=[...roleNodes.values()][0];
  if(first){
    const parent=first.parentElement;
    const ordered=roleOrder.map(role=>roleNodes.get(role)).filter(Boolean);
    const sameParent=ordered.filter(node=>node.parentElement===parent);

    if(parent && sameParent.length>=6){
      const markerNode=document.createComment('jd-home-flow');
      const firstInParent=sameParent.find(node=>node.parentElement===parent);
      parent.insertBefore(markerNode,firstInParent);

      const fragment=document.createDocumentFragment();
      roleOrder.forEach(role=>{
        const node=roleNodes.get(role);
        if(node && node.parentElement===parent) fragment.appendChild(node);
      });

      parent.insertBefore(fragment,markerNode);
      markerNode.remove();
      document.body.classList.add('jd-home-flow-ready');
    }
  }
}


const waButton=document.querySelector('.jd-whatsapp-float');
if(waButton && !waButton.querySelector('span')){
  const label=document.createElement('span');
  label.textContent='WhatsApp';
  waButton.appendChild(label);
}

if(['/creation-site-internet','/identite-visuelle','/print-signaletique'].includes(cleanPath)){
  const ctaMap={
    '/creation-site-internet':'Parlons de votre site',
    '/identite-visuelle':'Parlons de votre identité',
    '/print-signaletique':'Parlons de votre projet'
  };
  document.querySelectorAll('.wp-block-button__link').forEach(a=>{
    if(a.textContent.trim().replace(/\s*↗[\uFE0E\uFE0F]?\s*/gu,' ').trim()==='Parlons de votre projet') a.textContent=ctaMap[cleanPath];
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

/* Galeries horizontales des pages services. */
document.querySelectorAll('[data-jd-horizontal-gallery]').forEach(gallery=>{
  const track=gallery.querySelector('[data-jd-gallery-track]');
  const prev=gallery.querySelector('[data-jd-gallery-prev]');
  const next=gallery.querySelector('[data-jd-gallery-next]');
  if(!track) return;

  const step=()=>{
    const card=track.firstElementChild;
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

/* Utilise la capture fournie pour Un Max de Vie partout où ce projet apparaît. */
const umdvShot=window.JD_CORE_ASSETS&&window.JD_CORE_ASSETS.umdv;
if(umdvShot){
  document.querySelectorAll('a[href*="unmaxdevie.com"]').forEach(link=>{
    const holder=link.closest('.jd-site-card,.jd-home-work-item')||link;
    const img=holder.querySelector('img');
    if(!img) return;
    img.src=umdvShot;
    img.removeAttribute('srcset');
    img.removeAttribute('sizes');
  });
}

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



// Unifier toutes les flèches de CTA : jamais d'emoji, une seule flèche SVG fine.
const jdThinArrowMarkup='<span class="jd-cta-arrow" aria-hidden="true"><svg viewBox="0 0 24 24" focusable="false"><path d="M7 17L17 7"></path><path d="M9 7H17V15"></path></svg></span>';
const jdDiagonalArrowRegex=/[↗⬈➚⤴][\uFE0E\uFE0F]?/gu;

const jdStripUnicodeArrow=(el)=>{
  const walker=document.createTreeWalker(el,NodeFilter.SHOW_TEXT);
  const nodes=[];
  while(walker.nextNode()) nodes.push(walker.currentNode);

  let changed=false;
  nodes.forEach(node=>{
    const before=node.nodeValue||'';
    const after=before
      .replace(/\s*[↗⬈➚⤴][\uFE0E\uFE0F]?\s*/gu,' ')
      .replace(/\s{2,}/g,' ');
    if(after!==before){
      node.nodeValue=after;
      changed=true;
    }
  });
  return changed;
};

const jdNormalizeCtaArrows=(root=document)=>{
  const elements=[];
  if(root instanceof Element && root.matches('a,button')) elements.push(root);
  if(root.querySelectorAll) elements.push(...root.querySelectorAll('a,button'));

  [...new Set(elements)].forEach(el=>{
    if(el.matches('[data-jd-print-prev],[data-jd-print-next],[data-jd-gallery-prev],[data-jd-gallery-next]')) return;

    jdDiagonalArrowRegex.lastIndex=0;
    const hadUnicode=jdDiagonalArrowRegex.test(el.textContent||'');
    jdDiagonalArrowRegex.lastIndex=0;

    const existing=[...el.querySelectorAll(':scope > .jd-cta-arrow')];
    if(!hadUnicode && !existing.length) return;

    jdStripUnicodeArrow(el);

    const arrows=[...el.querySelectorAll(':scope > .jd-cta-arrow')];
    arrows.slice(1).forEach(node=>node.remove());

    if(!arrows.length){
      el.insertAdjacentHTML('beforeend',jdThinArrowMarkup);
    }
  });
};

jdNormalizeCtaArrows();

/* Conversion 2.16.19 — CTA plus simple et rassurant. */
const jdMakeEstimateCta=(link)=>{
  if(!link || link.dataset.jdEstimateReady==='1') return;
  link.dataset.jdEstimateReady='1';
  link.textContent='Demander une estimation';
  link.insertAdjacentHTML('beforeend',jdThinArrowMarkup);

  const parent=link.parentElement;
  if(parent && !parent.querySelector(':scope > .jd-estimate-note')){
    const note=document.createElement('span');
    note.className='jd-estimate-note';
    note.textContent='Premier échange sans engagement.';
    parent.appendChild(note);
  }
};

if(cleanPath==='/'){
  document.querySelectorAll('[data-jd-home-role="contact"] a[href*="/contact"]').forEach(jdMakeEstimateCta);
}
if(['/creation-site-internet','/identite-visuelle','/print-signaletique'].includes(cleanPath)){
  document.querySelectorAll(
    '.wp-block-post-content .wp-block-button__link[href*="/contact"],'+
    '.jd-preview-page .wp-block-button__link[href*="/contact"],'+
    '.jd-print-proof-v2__cta[href*="/contact"]'
  ).forEach(link=>{
    if(link.classList.contains('jd-web-maintenance-link')) return;
    jdMakeEstimateCta(link);
  });
}

/* Accueil : 3 avis d'abord, puis affichage complet à la demande. */
const jdReviews=document.querySelector('[data-jd-home-role="reviews"], .home #avis');
if(jdReviews){
  const cards=[...jdReviews.querySelectorAll('.jd-review-card')];
  if(cards.length>3 && !jdReviews.querySelector('.jd-reviews-toggle')){
    cards.slice(3).forEach(card=>card.hidden=true);
    jdReviews.classList.add('jd-reviews-collapsed');

    const toggle=document.createElement('button');
    toggle.type='button';
    toggle.className='jd-reviews-toggle';
    toggle.setAttribute('aria-expanded','false');
    toggle.textContent='Voir tous les avis';

    toggle.addEventListener('click',()=>{
      const expanded=toggle.getAttribute('aria-expanded')==='true';
      cards.slice(3).forEach(card=>card.hidden=expanded);
      toggle.setAttribute('aria-expanded',expanded?'false':'true');
      toggle.textContent=expanded?'Voir tous les avis':'Réduire les avis';
      jdReviews.classList.toggle('jd-reviews-collapsed',expanded);
    });

    jdReviews.appendChild(toggle);
  }
}

let jdArrowNormalizeQueued=false;
const jdQueueArrowNormalize=(root=document)=>{
  if(jdArrowNormalizeQueued) return;
  jdArrowNormalizeQueued=true;
  requestAnimationFrame(()=>{
    jdArrowNormalizeQueued=false;
    jdNormalizeCtaArrows(root);
  });
};

const jdArrowObserver=new MutationObserver(mutations=>{
  for(const mutation of mutations){
    if(mutation.type==='characterData'){
      const parent=mutation.target.parentElement?.closest('a,button');
      if(parent){
        jdQueueArrowNormalize(parent);
        return;
      }
    }
    for(const node of mutation.addedNodes){
      if(node.nodeType!==1) continue;
      const el=node;
      if(el.matches?.('a,button') || el.querySelector?.('a,button')){
        jdQueueArrowNormalize(el);
        return;
      }
    }
  }
});
jdArrowObserver.observe(document.body,{subtree:true,childList:true,characterData:true});

/* Tracking conversion — GA4 / Google Ads.
 * Le site utilise MonsterInsights : sa fonction __gtagTracker est prioritaire.
 * Un lead est envoyé uniquement après un vrai envoi serveur confirmé par jd_lead.
 */
const JD_GA4_MEASUREMENT_ID='G-S5XE9SGYGR';

const jdEnsureGtagFallback=()=>{
  window.dataLayer=window.dataLayer||[];
  if(typeof window.gtag!=='function'){
    window.gtag=function(){window.dataLayer.push(arguments);};
  }

  const selector='script[data-jd-ga4-fallback="'+JD_GA4_MEASUREMENT_ID+'"]';
  if(!document.querySelector(selector)){
    const script=document.createElement('script');
    script.async=true;
    script.src='https://www.googletagmanager.com/gtag/js?id='+encodeURIComponent(JD_GA4_MEASUREMENT_ID);
    script.dataset.jdGa4Fallback=JD_GA4_MEASUREMENT_ID;
    document.head.appendChild(script);

    window.gtag('js',new Date());
    // Pas de page_view supplémentaire : MonsterInsights s'en charge déjà.
    window.gtag('config',JD_GA4_MEASUREMENT_ID,{send_page_view:false});
  }
};

const jdPushAnalyticsEvent=(name,params={})=>{
  if(typeof window.__gtagTracker==='function'){
    window.__gtagTracker('event',name,params);
    return 'monsterinsights';
  }

  if(typeof window.gtag==='function'){
    window.gtag('event',name,params);
    return 'gtag';
  }

  // Secours uniquement si le tracker MonsterInsights n'est pas disponible.
  jdEnsureGtagFallback();
  window.gtag('event',name,params);
  return 'fallback';
};

const jdQuery=new URLSearchParams(window.location.search);
const jdLeadToken=jdQuery.get('jd_lead');

if(jdQuery.get('jd_contact')==='success' && jdLeadToken){
  const key='jd_generate_lead:'+jdLeadToken;
  const sendLead=()=>{
    try{
      if(sessionStorage.getItem(key)==='1') return;
    }catch(error){}

    jdPushAnalyticsEvent('generate_lead',{
      currency:'EUR',
      value:1,
      lead_source:'website_form',
      event_id:jdLeadToken
    });

    try{sessionStorage.setItem(key,'1');}catch(error){}
  };

  // MonsterInsights est normalement déjà prêt, mais on laisse aussi passer le chargement différé.
  if(document.readyState==='complete') sendLead();
  else window.addEventListener('load',()=>setTimeout(sendLead,250),{once:true});
}

document.querySelectorAll('a[href*="wa.me/"],a[href*="whatsapp.com/"]').forEach(link=>{
  link.addEventListener('click',()=>{
    jdPushAnalyticsEvent('contact_whatsapp',{
      contact_method:'whatsapp',
      link_url:link.href
    });
  },{passive:true});
});

document.querySelectorAll('a[href^="tel:"]').forEach(link=>{
  link.addEventListener('click',()=>{
    jdPushAnalyticsEvent('contact_phone',{
      contact_method:'phone',
      link_url:link.href
    });
  },{passive:true});
});

/* Galerie signalétique dans la section "L’IA fait des merveilles". */
document.querySelectorAll('[data-jd-print-slider]').forEach(slider=>{
  const slides=[...slider.querySelectorAll('[data-jd-print-slide]')];
  const dots=[...slider.querySelectorAll('[data-jd-print-dot]')];
  const prev=slider.querySelector('[data-jd-print-prev]');
  const next=slider.querySelector('[data-jd-print-next]');
  const count=slides.length;
  if(!count) return;

  let index=0;
  let timer=null;
  let startX=null;

  const render=()=>{
    slides.forEach((slide,i)=>{
      const active=i===index;
      slide.classList.toggle('is-active',active);
      slide.setAttribute('aria-hidden',active?'false':'true');
    });
    dots.forEach((dot,i)=>{
      const active=i===index;
      dot.classList.toggle('is-active',active);
      if(active) dot.setAttribute('aria-current','true');
      else dot.removeAttribute('aria-current');
    });
  };

  const go=(nextIndex)=>{
    index=(nextIndex+count)%count;
    render();
  };

  const stop=()=>{
    if(timer){
      clearInterval(timer);
      timer=null;
    }
  };

  const start=()=>{
    stop();
    if(count>1 && !window.matchMedia('(prefers-reduced-motion: reduce)').matches){
      timer=setInterval(()=>go(index+1),5500);
    }
  };

  prev?.addEventListener('click',event=>{
    event.preventDefault();
    go(index-1);
    start();
  });
  next?.addEventListener('click',event=>{
    event.preventDefault();
    go(index+1);
    start();
  });
  dots.forEach((dot,i)=>dot.addEventListener('click',event=>{
    event.preventDefault();
    go(i);
    start();
  }));

  slider.addEventListener('pointerdown',e=>{
    startX=e.clientX;
    stop();
  },{passive:true});

  slider.addEventListener('pointerup',e=>{
    if(startX!==null){
      const dx=e.clientX-startX;
      if(Math.abs(dx)>45) go(index+(dx<0?1:-1));
    }
    startX=null;
    start();
  },{passive:true});

  slider.addEventListener('pointercancel',()=>{
    startX=null;
    start();
  },{passive:true});

  slider.addEventListener('mouseenter',stop);
  slider.addEventListener('mouseleave',start);
  slider.addEventListener('focusin',stop);
  slider.addEventListener('focusout',start);

  render();
  start();
});

})();