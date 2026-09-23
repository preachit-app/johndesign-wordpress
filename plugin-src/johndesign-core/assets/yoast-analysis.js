/* global YoastSEO, JD_SECTIONS, JD_YOAST_CONTEXT, wp, jQuery */
(function($,wp){
'use strict';

function replaceAllLiteral(haystack,needle,value){
    return String(haystack).split(String(needle)).join(String(value==null?'':value));
}
function cleanAnalysisHtml(html){
    var focus=String((window.JD_YOAST_CONTEXT||{}).focus||'John Design')
        .replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    html=String(html||'')
        .replace(/<script[\s\S]*?<\/script>/gi,' ')
        .replace(/<style[\s\S]*?<\/style>/gi,' ')
        .replace(/\{\{CONTACT_FORM_(?:HOME|FULL)\}\}/g,' ');

    // The front-end renderer supplies meaningful alt text when the template has an empty alt.
    html=html.replace(/<img\b([^>]*)>/gi,function(tag){
        if(/\balt\s*=\s*["']\s*["']/i.test(tag)){
            return tag.replace(/\balt\s*=\s*["']\s*["']/i,'alt="'+focus+'"');
        }
        if(!/\balt\s*=/i.test(tag)){
            return tag.replace(/<img\b/i,'<img alt="'+focus+'"');
        }
        return tag;
    });
    return html;
}
function renderBlock(block){
    if(!block || block.name!=='johndesign/section') return '';
    var attrs=block.attributes||{}, sid=attrs.sectionId||'';
    var def=(window.JD_SECTIONS||{})[sid];
    if(!def || !def.template || !Array.isArray(def.fields)) return '';

    var html=String(def.template), values=attrs.fields||{};
    def.fields.forEach(function(field){
        var raw=Object.prototype.hasOwnProperty.call(values,field.key)?values[field.key]:(field.default||'');
        html=replaceAllLiteral(html,'{{'+field.key+'}}',raw);
    });

    var ctx=window.JD_YOAST_CONTEXT||{};
    html=replaceAllLiteral(html,'{{SITE_URL}}',String(ctx.home||'').replace(/\/$/,''));
    html=replaceAllLiteral(html,'{{THEME_URI}}',String(ctx.theme||'').replace(/\/$/,''));
    return cleanAnalysisHtml(html);
}
function collect(){
    try{
        var store=wp&&wp.data&&wp.data.select('core/block-editor');
        if(!store) return '';
        return (store.getBlocks()||[]).map(renderBlock).filter(Boolean).join('\n');
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
