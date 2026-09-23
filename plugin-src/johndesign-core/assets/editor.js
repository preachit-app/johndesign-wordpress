(function(wp){
'use strict';
const el=wp.element.createElement;
const Fragment=wp.element.Fragment;
const {registerBlockType}=wp.blocks;
const {InspectorControls,MediaUpload,MediaUploadCheck,useBlockProps}=wp.blockEditor;
const {PanelBody,TextControl,TextareaControl,Button,BaseControl}=wp.components;
const {select,dispatch,subscribe}=wp.data;
const ServerSideRender=wp.serverSideRender||wp.components.ServerSideRender;

function annotateFields(fields){
  const used=(fields||[]).map(f=>Object.assign({},f));
  const count=(needle)=>used.filter(f=>(f.label||'').includes(needle)).length;
  const eyebrowTotal=count('jd-eyebrow');
  const h3Total=used.filter(f=>/^Titre H3/.test(f.label||'')).length;
  const imageTotal=used.filter(f=>f.type==='image').length;
  const urlTotal=used.filter(f=>f.type==='url').length;
  let eyebrowSeen=0,h3Seen=0,imageSeen=0,urlSeen=0,cardIndex=0,stepIndex=0,reviewIndex=0;
  used.forEach(f=>{
    const l=(f.label||'').trim();
    if(f.type==='image'){
      imageSeen++; f._uiLabel=imageTotal>1?'Réalisation '+imageSeen+' — image':'Image'; return;
    }
    if(f.type==='url'){
      urlSeen++;
      if(l.includes('jd-home-work-item')) f._uiLabel='Réalisation '+urlSeen+' — lien externe';
      else if(l.includes('wp-block-button__link')) f._uiLabel='Lien du bouton';
      else if(l.includes('jd-site-url')) f._uiLabel='Adresse du site';
      else f._uiLabel=urlTotal>1?'Lien '+urlSeen:'Lien / URL';
      return;
    }
    if(l.includes('jd-step-number')){stepIndex++;f._uiLabel='Étape '+stepIndex+' — numéro';return;}
    if(l.includes('jd-review-source')){reviewIndex++;f._uiLabel='Avis '+reviewIndex+' — source';return;}
    if(l.includes('jd-review-stars')){f._uiLabel='Avis '+Math.max(reviewIndex,1)+' — étoiles';return;}
    if(l.includes('jd-review-quote')){f._uiLabel='Avis '+Math.max(reviewIndex,1)+' — citation';return;}
    if(l.includes('jd-review-context')){f._uiLabel='Avis '+Math.max(reviewIndex,1)+' — contexte';return;}
    if(l.includes('jd-review-person')){f._uiLabel='Avis '+Math.max(reviewIndex,1)+' — auteur';return;}
    if(l.includes('jd-review-position')){f._uiLabel='Indicateur du carrousel';return;}
    if(l.includes('jd-google-stars')){f._uiLabel='Étoiles Google';return;}
    if(l.includes('jd-google-number')){f._uiLabel='Note Google';return;}
    if(l.includes('jd-google-meta')){f._uiLabel='Nombre d’avis Google';return;}
    if(l.includes('jd-location')){f._uiLabel='Localisation';return;}
    if(l.includes('jd-eyebrow')){
      eyebrowSeen++;
      if(eyebrowSeen===1) f._uiLabel='Surtitre';
      else {cardIndex++;f._uiLabel='Carte '+cardIndex+' — catégorie';}
      return;
    }
    if(/^Titre H1/.test(l)){f._uiLabel='Titre principal';return;}
    if(/^Titre H2/.test(l)){f._uiLabel='Titre de section';return;}
    if(/^Titre H3/.test(l)){
      h3Seen++;
      if(stepIndex) f._uiLabel='Étape '+stepIndex+' — titre';
      else if(cardIndex) f._uiLabel='Carte '+cardIndex+' — titre';
      else f._uiLabel=h3Total>1?'Sous-titre '+h3Seen:'Sous-titre';
      return;
    }
    if(l.includes('jd-card-link')){f._uiLabel=cardIndex?'Carte '+cardIndex+' — bouton':'Bouton';return;}
    if(/^Libellé \/ lien/.test(l)){
      if(l.includes('wp-block-button__link')) f._uiLabel='Texte du bouton';
      else f._uiLabel='Lien / bouton';
      return;
    }
    if(l.includes('jd-lead')){f._uiLabel='Texte d’introduction';return;}
    if(l.includes('jd-caption')){f._uiLabel='Légende';return;}
    if(/^Texte ·/.test(l)){
      if(stepIndex) f._uiLabel='Étape '+stepIndex+' — description';
      else if(cardIndex) f._uiLabel='Carte '+cardIndex+' — description';
      else f._uiLabel='Texte';
    }
  });
  return used;
}
function cleanLabel(f){
  if(f._uiLabel) return f._uiLabel;
  let l=(f.label||'').trim();
  const parts=l.split('·').map(s=>s.trim()).filter(Boolean);
  if(parts.length>=2){
    const last=parts[parts.length-1];
    if(last.length>3 && !/^jd-/.test(last)) return last.length>70?last.slice(0,67)+'…':last;
  }
  return l||'Contenu';
}
function stripForInput(v){return String(v||'').replace(/<br\s*\/?\s*>/gi,'\n').replace(/<[^>]+>/g,'').replace(/&amp;/g,'&');}
function isShortRich(f,val){
  const label=f.label||'';
  if(/Titre H[123]|jd-eyebrow|jd-step-number|Libellé \/ lien|jd-location/.test(label)) return true;
  return stripForInput(val).length < 90 && !String(val).includes('<a ');
}
function setEditorManagedClass(){
  try{
    const content=(select('core/editor')&&select('core/editor').getEditedPostContent())||'';
    const managed=content.indexOf('wp:johndesign/section')!==-1;
    document.body.classList.toggle('jd-managed-page-editor',managed);
  }catch(e){}
}
wp.domReady(function(){
  setEditorManagedClass();
  let last='';
  subscribe(function(){
    let c='';try{c=(select('core/editor')&&select('core/editor').getEditedPostContent())||'';}catch(e){}
    if(c!==last){last=c;setEditorManagedClass();}
  });
});

registerBlockType('johndesign/section',{
 title:'Module John Design',icon:'layout',category:'design',
 attributes:{sectionId:{type:'string',default:''},fields:{type:'object',default:{}}},supports:{html:false},
 edit:function(props){
   const a=props.attributes;const def=(window.JD_SECTIONS||{})[a.sectionId];
   const blockProps=useBlockProps({className:'jd-module-editor-shell'});
   if(!def)return el('div',blockProps,el('div',{className:'components-placeholder'},'Module John Design non configuré.'));
   const setField=(k,v)=>props.setAttributes({fields:Object.assign({},a.fields||{}, {[k]:v})});
   const current=(f)=> (a.fields&&Object.prototype.hasOwnProperty.call(a.fields,f.key))?a.fields[f.key]:(f.default||'');
   const usedKeys=Array.isArray(def.usedKeys)?def.usedKeys:null;
   const fields=annotateFields((def.fields||[]).filter(f=>!usedKeys||usedKeys.indexOf(f.key)!==-1));
   function isThemeImage(v){return /^\{\{THEME_URI\}\}/.test(String(v||''));}
   function panelLabel(f){
     return cleanLabel(f).replace(/^(Carte|Étape|Avis|Réalisation)\s+\d+\s+—\s+/,'');
   }
   function control(f){
     const val=current(f), label=panelLabel(f);
     if(f.type==='image'){
       const themeImage=isThemeImage(val);
       return el('div',{key:f.key,className:'jd-inspector-image'},
         el(BaseControl,{label:label},
           (!themeImage&&val)?el('img',{src:val,className:'jd-inspector-image-preview'}):
             el('div',{className:'jd-inspector-image-empty'},themeImage?'Image actuelle du thème':'Aucune image'),
           el(MediaUploadCheck,{},el(MediaUpload,{onSelect:m=>setField(f.key,m.url),allowedTypes:['image'],render:o=>el(Button,{variant:'secondary',onClick:o.open},(!themeImage&&val)?'Remplacer l’image':'Choisir une image')})),
           (!themeImage&&val)?el(Button,{variant:'tertiary',isDestructive:true,onClick:()=>setField(f.key,'')},'Retirer'):null
         ));
     }
     if(f.type==='url') return el(TextControl,{key:f.key,label:label,type:'url',value:val,onChange:v=>setField(f.key,v),help:'Adresse complète, par exemple https://…'});
     if((f.label||'').includes('jd-review-person')){
       const raw=String(val||'');
       const m=raw.match(/^([\s\S]*?)(?:<span>([\s\S]*?)<\/span>)?$/i);
       const name=stripForInput(m&&m[1]||raw), meta=stripForInput(m&&m[2]||'');
       return el('div',{key:f.key,className:'jd-compound-control'},
         el(TextControl,{label:'Auteur',value:name,onChange:v=>setField(f.key,v+(meta?'<span>'+meta+'</span>':''))}),
         el(TextControl,{label:'Projet / contexte',value:meta,onChange:v=>setField(f.key,name+(v?'<span>'+v+'</span>':''))})
       );
     }
     if(isShortRich(f,val)){
       return el(TextControl,{key:f.key,label:label,value:stripForInput(val),onChange:function(v){setField(f.key,String(v).replace(/\n/g,'<br/>'));}});
     }
     return el(TextareaControl,{key:f.key,label:label,value:stripForInput(val),onChange:function(v){setField(f.key,String(v).replace(/\n/g,'<br/>'))},rows:4});
   }
   function panel(title,items,open){
     if(!items||!items.length)return null;
     return el(PanelBody,{key:title,title:title,initialOpen:!!open},items.map(control));
   }
   function visibleFields(list){
     return list.filter(f=>{
       const v=current(f), l=f.label||'';
       if(f.type!=='url' && /<a\s/i.test(String(v||'')) && (l.includes('jd-card-link')||l.includes('jd-home-email'))) return false;
       return true;
     });
   }
   function buildPanels(list){
     const fs=visibleFields(list);
     const out=[];
     const byLabel=(needle)=>fs.filter(f=>(f.label||'').includes(needle));
     const stepFields=byLabel('jd-step-number');
     const reviewFields=byLabel('jd-review-source');
     const itemUrls=fs.filter(f=>f.type==='url'&&(f.label||'').includes('jd-home-work-item'));
     const itemImages=fs.filter(f=>f.type==='image'&&(f.label||'').includes('jd-home-work-item'));
     const eyebrowIndexes=fs.map((f,i)=>(f.label||'').includes('jd-eyebrow')?i:-1).filter(i=>i>=0);

     if(reviewFields.length){
       const firstReview=fs.findIndex(f=>(f.label||'').includes('jd-review-source'));
       const before=fs.slice(0,firstReview);
       const google=before.filter(f=>/jd-google-(stars|number|meta)/.test(f.label||''));
       const main=before.filter(f=>google.indexOf(f)===-1);
       out.push(panel('Contenu principal',main,true));
       out.push(panel('Résumé Google',google,false));
       let n=0, group=[];
       fs.slice(firstReview).forEach(f=>{
         if((f.label||'').includes('jd-review-source')){
           if(group.length){
             n++; const person=group.find(x=>(x.label||'').includes('jd-review-person'));
             const author=person?String(current(person)).split('<span>')[0].replace(/<[^>]+>/g,'').trim():'';
             out.push(panel('Avis '+n+(author?' — '+author:''),group,false));
           }
           group=[f];
         }else if((f.label||'').includes('jd-review-position')||((f.label||'').includes('wp-block-button__link'))||f.type==='url'){
           if(group.length){
             n++; const person=group.find(x=>(x.label||'').includes('jd-review-person'));
             const author=person?String(current(person)).split('<span>')[0].replace(/<[^>]+>/g,'').trim():'';
             out.push(panel('Avis '+n+(author?' — '+author:''),group,false)); group=[];
           }
           out.push(panel('Navigation et lien Google',[f],false));
         }else group.push(f);
       });
       if(group.length){
         n++; const person=group.find(x=>(x.label||'').includes('jd-review-person'));
         const author=person?String(current(person)).split('<span>')[0].replace(/<[^>]+>/g,'').trim():'';
         out.push(panel('Avis '+n+(author?' — '+author:''),group,false));
       }
       return out.filter(Boolean);
     }

     if(stepFields.length){
       const first=fs.findIndex(f=>(f.label||'').includes('jd-step-number'));
       out.push(panel('Contenu principal',fs.slice(0,first),true));
       let n=0, group=[];
       fs.slice(first).forEach(f=>{
         if((f.label||'').includes('jd-step-number')){
           if(group.length){n++;const h=group.find(x=>/^Titre H3/.test(x.label||''));out.push(panel('Étape '+n+(h?' — '+stripForInput(current(h)):''),group,false));}
           group=[f];
         }else group.push(f);
       });
       if(group.length){n++;const h=group.find(x=>/^Titre H3/.test(x.label||''));out.push(panel('Étape '+n+(h?' — '+stripForInput(current(h)):''),group,false));}
       return out.filter(Boolean);
     }

     if(itemImages.length||itemUrls.length){
       const items=new Set(itemImages.concat(itemUrls));
       out.push(panel('Contenu principal',fs.filter(f=>!items.has(f)),true));
       const max=Math.max(itemImages.length,itemUrls.length);
       for(let i=0;i<max;i++) out.push(panel('Réalisation '+(i+1),[itemImages[i],itemUrls[i]].filter(Boolean),false));
       return out.filter(Boolean);
     }

     if(eyebrowIndexes.length>1){
       const firstCard=eyebrowIndexes[1];
       out.push(panel('Contenu principal',fs.slice(0,firstCard),true));
       let n=0, group=[];
       fs.slice(firstCard).forEach(f=>{
         if((f.label||'').includes('jd-eyebrow')){
           if(group.length){n++;const cat=stripForInput(current(group[0])).replace(/^\d+\s*\/\s*/,'');out.push(panel('Carte '+n+(cat?' — '+cat:''),group,false));}
           group=[f];
         }else group.push(f);
       });
       if(group.length){n++;const cat=stripForInput(current(group[0])).replace(/^\d+\s*\/\s*/,'');out.push(panel('Carte '+n+(cat?' — '+cat:''),group,false));}
       return out.filter(Boolean);
     }

     const text=fs.filter(f=>f.type!=='image'&&f.type!=='url');
     const images=fs.filter(f=>f.type==='image');
     const urls=fs.filter(f=>f.type==='url');
     out.push(panel('Contenu',text,true));
     out.push(panel('Images',images,false));
     out.push(panel('Liens',urls,false));
     return out.filter(Boolean);
   }
   const inspector=el(InspectorControls,{},
     buildPanels(fields),
     el(PanelBody,{title:'Informations du module',initialOpen:false},
       el('p',{className:'jd-inspector-help'},'Module : ',el('code',{},a.sectionId)),
       el('p',{className:'jd-inspector-help'},'Les champs sont regroupés par carte, étape, réalisation ou avis pour éviter les réglages techniques.'),
       el('p',{className:'jd-inspector-help'},'Vous pouvez déplacer, dupliquer ou supprimer ce module avec la barre d’outils Gutenberg.'))
   );
   function openSettings(){
     try{dispatch('core/edit-post').openGeneralSidebar('edit-post/block');}catch(e){}
   }
   return el(Fragment,{},inspector,
     el('div',blockProps,
       el('div',{className:'jd-module-editor-bar'},
         el('div',{className:'jd-module-editor-id'},el('span',{className:'jd-module-dot'}),el('strong',{},def.title),el('small',{},a.sectionId)),
         el(Button,{variant:'secondary',size:'compact',onClick:openSettings},'Modifier le contenu')
       ),
       el('div',{className:'jd-module-editor-preview'},el(ServerSideRender,{block:'johndesign/section',attributes:a}))
     ));
 },save:function(){return null;}
});
})(window.wp);
