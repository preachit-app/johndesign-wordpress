<?php
// 2.13.3 launch marker: legacy anchors enabled.
if (!defined('ABSPATH')) exit;
function jd_core_sections(){
 static $data=null;
 if($data===null){
  $stored=get_option('jd_core_sections_library');
  if(is_array($stored)&&$stored){$data=$stored;}
  else{$file=JD_CORE_DIR.'data/sections.json';$data=file_exists($file)?(json_decode(file_get_contents($file),true)?:[]):[];}
 }
 return $data;
}
function jd_core_render_image_alt($html){
 $map=function_exists('jd_core_seo_map')?jd_core_seo_map():[];$key='';
 if(is_front_page())$key='home';elseif(is_page()){$p=get_queried_object();if($p instanceof WP_Post)$key=$p->post_name;}
 $alt=trim((string)($map[$key]['focus']??''));
 if(!$alt)return $html;
 return preg_replace_callback('/<img\b([^>]*)>/i',function($m)use($alt){
  $tag=$m[0];
  if(preg_match('/\balt\s*=\s*(["\'])[^"\']*\1/i',$tag)){
   return preg_replace('/\balt\s*=\s*(["\'])\s*\1/i','alt="'.esc_attr($alt).'"',$tag,1);
  }
  return preg_replace('/<img\b/i','<img alt="'.esc_attr($alt).'"',$tag,1);
 },$html);
}
function jd_core_get_truck_image_src(){
 $src=(string)get_option('jd_core_truck_image_src','');
 return $src;
}
function jd_core_render_form($variant='home'){return jd_core_contact_form($variant);}
function jd_core_render_section($attrs){
 $all=jd_core_sections();$id=sanitize_key($attrs['sectionId']??'');
 if($id==='home-03') return '';
 if(!$id||empty($all[$id]))return '';$def=$all[$id];$html=$def['template'];$fields=is_array($attrs['fields']??null)?$attrs['fields']:[];
 $site_url=rtrim(home_url(),'/');$theme_uri=rtrim(get_stylesheet_directory_uri(),'/');
 foreach($def['fields'] as $f){$key=$f['key'];$val=array_key_exists($key,$fields)?$fields[$key]:($f['default']??'');$val=str_replace(['{{SITE_URL}}','{{THEME_URI}}'],[$site_url,$theme_uri],$val);if($f['type']==='url'||$f['type']==='image')$safe=esc_url($val);else $safe=wp_kses_post($val);$html=str_replace('{{'.$key.'}}',$safe,$html);}
 $html=preg_replace('/<section class="([^"]*\\bjd-reviews\\b[^"]*)"(?![^>]*\\bid=)/','<section class="$1" id="avis"',$html,1);
 if($id==='home-04'){
  if(preg_match('/<section\\b[^>]*\\bclass="[^"]*"/i',$html)){
   $html=preg_replace('/(<section\\b[^>]*\\bclass=")([^"]*)"/i','$1$2 jd-home-work-feature"',$html,1);
  }else{
   $html=preg_replace('/<section\\b/i','<section class="jd-home-work-feature"',$html,1);
  }
 }
 $html=str_replace('{{CONTACT_FORM_HOME}}',jd_core_render_form('home'),$html);$html=str_replace('{{CONTACT_FORM_FULL}}',jd_core_render_form('full'),$html);$html=str_replace('{{SITE_URL}}',$site_url,$html);$html=str_replace('{{THEME_URI}}',$theme_uri,$html);
 $page_key='';
 if(is_front_page())$page_key='home';elseif(is_page()){$p=get_queried_object();if($p instanceof WP_Post)$page_key=$p->post_name;}
 if($page_key==='print-signaletique' && (stripos(wp_strip_all_tags($html),'IA fait des merveilles')!==false || stripos(wp_strip_all_tags($html),'stickers')!==false)){
  $truck=jd_core_get_truck_image_src();
  if($truck){
   $html=preg_replace_callback("/<img\\b([^>]*)\\bsrc=([\"'])(.*?)\\2([^>]*)>/i",function($m)use($truck){
    return '<img'.$m[1].'src="'.$truck.'"'.$m[4].'>';
   },$html,1);
  }
 }
 return jd_core_render_image_alt($html);
}
function jd_core_register_block(){
 wp_register_script('jd-section-editor',JD_CORE_URL.'assets/editor.js',['wp-blocks','wp-element','wp-components','wp-block-editor','wp-server-side-render','wp-i18n'],JD_CORE_VERSION,true);
 wp_register_style('jd-section-editor-style',JD_CORE_URL.'assets/editor.css',[],JD_CORE_VERSION);
 wp_localize_script('jd-section-editor','JD_SECTIONS',array_map(function($d){$used=[];if(!empty($d['template'])){preg_match_all('/\{\{(f\d+)\}\}/',$d['template'],$m);$used=array_values(array_unique($m[1]??[]));}return ['title'=>$d['title'],'fields'=>$d['fields'],'usedKeys'=>$used,'template'=>$d['template']??''];},jd_core_sections()));
 register_block_type('johndesign/section',['api_version'=>3,'editor_script'=>'jd-section-editor','editor_style'=>'jd-section-editor-style','render_callback'=>'jd_core_render_section','attributes'=>['sectionId'=>['type'=>'string','default'=>''],'fields'=>['type'=>'object','default'=>[]]],'supports'=>['html'=>false,'align'=>false]]);
}
add_action('init','jd_core_register_block');


function jd_core_home_services_snapshot_2150(){
 return '<section class="jd-home-services-snapshot" aria-label="Les services John Design">
   <div class="jd-home-services-top">
     <p class="jd-home-services-kicker">EN UN COUP D’ŒIL</p>
     <p class="jd-home-services-title">Un seul interlocuteur. Quatre terrains.</p>
   </div>
   <div class="jd-home-services-grid">
     <a class="jd-home-service-mini is-web" href="'.esc_url(home_url('/creation-site-internet/')).'">
       <span class="jd-home-service-icon" aria-hidden="true"><svg viewBox="0 0 32 32"><rect x="4" y="6" width="24" height="20" rx="3"></rect><path d="M4 11h24M10 8.5h.1M14 8.5h.1"></path></svg></span>
       <span>Sites internet</span>
     </a>
     <a class="jd-home-service-mini is-id" href="'.esc_url(home_url('/identite-visuelle/')).'">
       <span class="jd-home-service-icon" aria-hidden="true"><svg viewBox="0 0 32 32"><circle cx="16" cy="16" r="10"></circle><path d="M16 6v20M6 16h20M9 9l14 14M23 9L9 23"></path></svg></span>
       <span>Identité visuelle</span>
     </a>
     <a class="jd-home-service-mini is-print" href="'.esc_url(home_url('/print-signaletique/')).'">
       <span class="jd-home-service-icon" aria-hidden="true"><svg viewBox="0 0 32 32"><path d="M8 12V5h16v7M8 23H5V12h22v11h-3"></path><rect x="8" y="19" width="16" height="8" rx="1"></rect></svg></span>
       <span>Print</span>
     </a>
     <a class="jd-home-service-mini is-sign" href="'.esc_url(home_url('/print-signaletique/')).'">
       <span class="jd-home-service-icon" aria-hidden="true"><svg viewBox="0 0 32 32"><path d="M7 7h18v12H7zM16 19v8M11 27h10"></path><path d="M10 11h12M10 15h8"></path></svg></span>
       <span>Signalétique</span>
     </a>
   </div>
 </section>';
}

add_filter('render_block',function($block_content,$block){
 if(!is_front_page()) return $block_content;
 if(($block['blockName']??'')!=='johndesign/section') return $block_content;
 if(($block['attrs']['sectionId']??'')!=='home-01') return $block_content;
 return $block_content.jd_core_home_services_snapshot_2150();
},20,2);
