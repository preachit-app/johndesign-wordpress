(()=>{
'use strict';

/**
 * The global navigation is a <details> element so it can collapse on mobile.
 * Native closed <details> content is not rendered on desktop unless the open
 * state is explicitly set. Keep it open on desktop and closed on mobile.
 */
const menu=document.querySelector('.jd-preview-nav');
if(menu){
  const desktop=window.matchMedia('(min-width:768px)');
  const sync=()=>{
    if(desktop.matches){
      menu.open=true;
    }else if(!menu.matches(':focus-within')){
      menu.open=false;
    }
  };
  sync();
  if(typeof desktop.addEventListener==='function') desktop.addEventListener('change',sync);
  else if(typeof desktop.addListener==='function') desktop.addListener(sync);
}
})();
