/* global YoastSEO, JD_SECTIONS, JD_YOAST_CONTEXT, wp, jQuery */
(function($,wp){
'use strict';

function textFromHtml(value){
    var div=document.createElement('div');
    div.innerHTML=String(value||'').replace(/<br\s*\/?\s*>/gi,' ');
    return (div.textContent||div.innerText||'').replace(/\s+/g,' ').trim();
}
function safeRich(value){
    var raw=String(value||'').replace(/<script[\s\S]*?<\/script>/gi,' ');
    // Keep inline anchors so Yoast can analyze actual content links.
    return raw.replace(/<(?!\/?(?:a|strong|em|br)\b)[^>]+>/gi,' ');
}
function tagFor(label){
    label=String(label||'');
    if(/^Titre H1/.test(label)) return 'h1';
    if(/^Titre H2/.test(label)) return 'h2';
    if(/^Titre H3/.test(label)) return 'h3';
    return 'p';
}
function resolveUrl(value){
    var v=String(value||'').replace(/\{\{SITE_URL\}\}/g,(window.JD_YOAST_CONTEXT||{}).home||'/');
    if(/^\{\{THEME_URI\}\}/.test(v)) return '';
    return v;
}
function blockHtml(block){
    if(!block || block.name!=='johndesign/section') return '';
    var attrs=block.attributes||{}, sid=attrs.sectionId||'';
    var def=(window.JD_SECTIONS||{})[sid];
    if(!def || !Array.isArray(def.fields)) return '';
    var values=attrs.fields||{}, used=Array.isArray(def.usedKeys)?def.usedKeys:null, chunks=[];

    def.fields.forEach(function(field){
        if(used && used.indexOf(field.key)===-1) return;
        var raw=Object.prototype.hasOwnProperty.call(values,field.key)?values[field.key]:(field.default||'');
        if(!raw) return;
        if(field.type==='image'){
            var src=resolveUrl(raw);
            if(src) chunks.push('<img src="'+src.replace(/"/g,'&quot;')+'" alt="'+textFromHtml((window.JD_YOAST_CONTEXT||{}).focus||'John Design')+'">');
            return;
        }
        if(field.type==='url'){
            var href=resolveUrl(raw);
            if(href) chunks.push('<a href="'+href.replace(/"/g,'&quot;')+'">En savoir plus</a>');
            return;
        }
        var rich=safeRich(raw), plain=textFromHtml(rich);
        if(!plain) return;
        var tag=tagFor(field.label);
        chunks.push('<'+tag+'>'+rich+'</'+tag+'>');
    });
    return chunks.join('\n');
}
function collect(){
    try{
        var store=wp&&wp.data&&wp.data.select('core/block-editor');
        if(!store) return '';
        return (store.getBlocks()||[]).map(blockHtml).filter(Boolean).join('\n');
    }catch(e){ return ''; }
}
var registered=false;
function registerBridge(){
    if(registered || typeof YoastSEO==='undefined' || !YoastSEO.app || !YoastSEO.analysis || !YoastSEO.analysis.worker) return;
    try{
        YoastSEO.app.registerPlugin('JohnDesignYoastBridge',{status:'ready'});
        YoastSEO.app.registerModification('content',function(data){
            var extra=collect();
            return extra ? data+'\n'+extra : data;
        },'JohnDesignYoastBridge',10);
        registered=true;
        if(YoastSEO.app.refresh) YoastSEO.app.refresh();
    }catch(e){}
}
function boot(){ registerBridge(); }
if(typeof YoastSEO!=='undefined' && YoastSEO.app){ boot(); }
$(window).on('YoastSEO:ready',boot);
$(document).on('YoastSEO:ready',boot);

// Yoast may initialize after our footer script without replaying the event.
var tries=0, timer=setInterval(function(){
    tries++;
    registerBridge();
    if(registered || tries>40) clearInterval(timer);
},250);

if(wp&&wp.data&&wp.data.subscribe){
    var last='';
    wp.data.subscribe(function(){
        if(!registered) return;
        var now=collect();
        if(now!==last){
            last=now;
            if(typeof YoastSEO!=='undefined' && YoastSEO.app && YoastSEO.app.refresh){
                clearTimeout(window.__jdYoastRefresh);
                window.__jdYoastRefresh=setTimeout(function(){YoastSEO.app.refresh();},350);
            }
        }
    });
}
})(jQuery,window.wp);
