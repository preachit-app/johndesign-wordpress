/* global YoastSEO, JD_SECTIONS, wp, jQuery */
(function($,wp){
'use strict';

function stripUnsafe(value){
    var div=document.createElement('div');
    div.innerHTML=String(value||'').replace(/<br\s*\/?\s*>/gi,' ');
    return (div.textContent||div.innerText||'').replace(/\s+/g,' ').trim();
}
function tagFor(label){
    label=String(label||'');
    if(/^Titre H1/.test(label)) return 'h1';
    if(/^Titre H2/.test(label)) return 'h2';
    if(/^Titre H3/.test(label)) return 'h3';
    return 'p';
}
function blockHtml(block){
    if(!block || block.name!=='johndesign/section') return '';
    var attrs=block.attributes||{};
    var sid=attrs.sectionId||'';
    var defs=(window.JD_SECTIONS||{});
    var def=defs[sid];
    if(!def || !Array.isArray(def.fields)) return '';

    var values=attrs.fields||{};
    var used=Array.isArray(def.usedKeys)?def.usedKeys:null;
    var chunks=[];

    def.fields.forEach(function(field){
        if(used && used.indexOf(field.key)===-1) return;
        if(field.type==='image' || field.type==='url') return;
        var raw=Object.prototype.hasOwnProperty.call(values,field.key)?values[field.key]:(field.default||'');
        var text=stripUnsafe(raw);
        if(!text) return;
        var tag=tagFor(field.label);
        chunks.push('<'+tag+'>'+text+'</'+tag+'>');
    });
    return chunks.join('\n');
}
function collectJohnDesignContent(){
    try{
        var store=wp && wp.data && wp.data.select('core/block-editor');
        if(!store) return '';
        var blocks=store.getBlocks()||[];
        return blocks.map(blockHtml).filter(Boolean).join('\n');
    }catch(e){
        return '';
    }
}

function JohnDesignYoastBridge(){
    if(typeof YoastSEO==='undefined' || !YoastSEO.app || !YoastSEO.analysis || !YoastSEO.analysis.worker) return;
    try{
        YoastSEO.app.registerPlugin('JohnDesignYoastBridge',{status:'ready'});
        YoastSEO.app.registerModification('content',function(data){
            var extra=collectJohnDesignContent();
            return extra ? data+'\n'+extra : data;
        },'JohnDesignYoastBridge',10);
    }catch(e){}
}
function boot(){
    if(typeof YoastSEO!=='undefined' && YoastSEO.app) JohnDesignYoastBridge();
}
if(typeof YoastSEO!=='undefined' && YoastSEO.app){
    boot();
}else{
    $(window).on('YoastSEO:ready',boot);
}
})(jQuery,window.wp);
