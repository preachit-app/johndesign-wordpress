<?php
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
function jd_core_render_form($variant='home'){return jd_core_contact_form($variant);}
function jd_core_render_section($attrs){
 $all=jd_core_sections();$id=sanitize_key($attrs['sectionId']??'');if(!$id||empty($all[$id]))return '';$def=$all[$id];$html=$def['template'];$fields=is_array($attrs['fields']??null)?$attrs['fields']:[];
 $site_url=rtrim(home_url(),'/');$theme_uri=rtrim(get_stylesheet_directory_uri(),'/');
 foreach($def['fields'] as $f){$key=$f['key'];$val=array_key_exists($key,$fields)?$fields[$key]:($f['default']??'');$val=str_replace(['{{SITE_URL}}','{{THEME_URI}}'],[$site_url,$theme_uri],$val);if($f['type']==='url'||$f['type']==='image')$safe=esc_url($val);else $safe=wp_kses_post($val);$html=str_replace('{{'.$key.'}}',$safe,$html);}
 $html=preg_replace('/<section class="([^"]*\\bjd-reviews\\b[^"]*)"(?![^>]*\\bid=)/','<section class="$1" id="avis"',$html,1);\n $html=str_replace('{{CONTACT_FORM_HOME}}',jd_core_render_form('home'),$html);$html=str_replace('{{CONTACT_FORM_FULL}}',jd_core_render_form('full'),$html);$html=str_replace('{{SITE_URL}}',$site_url,$html);$html=str_replace('{{THEME_URI}}',$theme_uri,$html);return $html;
}
function jd_core_register_block(){
 wp_register_script('jd-section-editor',JD_CORE_URL.'assets/editor.js',['wp-blocks','wp-element','wp-components','wp-block-editor','wp-server-side-render','wp-i18n'],JD_CORE_VERSION,true);
 wp_register_style('jd-section-editor-style',JD_CORE_URL.'assets/editor.css',[],JD_CORE_VERSION);
 wp_localize_script('jd-section-editor','JD_SECTIONS',array_map(function($d){$used=[];if(!empty($d['template'])){preg_match_all('/\{\{(f\d+)\}\}/',$d['template'],$m);$used=array_values(array_unique($m[1]??[]));}return ['title'=>$d['title'],'fields'=>$d['fields'],'usedKeys'=>$used];},jd_core_sections()));
 register_block_type('johndesign/section',['api_version'=>3,'editor_script'=>'jd-section-editor','editor_style'=>'jd-section-editor-style','render_callback'=>'jd_core_render_section','attributes'=>['sectionId'=>['type'=>'string','default'=>''],'fields'=>['type'=>'object','default'=>[]]],'supports'=>['html'=>false,'align'=>false]]);
}
add_action('init','jd_core_register_block');
