<?php
if (!defined('ABSPATH')) exit;

/**
 * John Design Core 2.16.0 — passe éditoriale ciblée.
 * La structure des pages reste inchangée, sauf suppression complète
 * du bloc de démonstration "Une idée du style / Hello vous" demandé.
 *
 * À conserver mot pour mot :
 * - On clarifie
 * - Je crée
 * - On lance.
 * - L’IA fait des merveilles. Mais elle ne pose pas vos stickers.
 */

function jd_core_copy_key_2160($value){
    $value=(string)$value;
    $value=str_replace(['<br>','<br/>','<br />'], ' ', $value);
    $value=wp_strip_all_tags($value);
    $value=html_entity_decode($value,ENT_QUOTES|ENT_HTML5,'UTF-8');
    $value=preg_replace('/\s+/u',' ',trim($value));
    return mb_strtolower($value);
}

function jd_core_copy_global_raw_2160(){
    return [
        // Accueil : compréhension immédiate.
        'Une bonne image. Partout où l’on vous voit.'=>'Site internet, logo, impression & signalétique.',
        "Une bonne image. Partout où l'on vous voit."=>'Site internet, logo, impression & signalétique.',
        'Sites internet, logos, impression & signalétique.'=>'Site internet, logo, impression & signalétique.',
        'Site internet, logo, impression & signalétique.'=>'Site internet, logo, impression & signalétique.',

        'Du premier regard au premier contact : une communication cohérente, pensée pour votre activité.'=>'Un seul interlocuteur pour votre site, votre logo et vos supports.',
        'Un seul interlocuteur pour votre site internet, votre logo, vos supports imprimés et votre signalétique.'=>'Un seul interlocuteur pour votre site, votre logo et vos supports.',

        'Un site qui vous fait choisir.'=>'Création ou refonte de votre site internet.',
        'Création ou refonte de votre site internet.'=>'Création ou refonte de votre site internet.',
        'Un site clair, rapide et agréable à parcourir. Pour présenter votre activité et faciliter les demandes.'=>'Un site professionnel, rapide et adapté au mobile, pour présenter votre activité et faciliter les contacts.',
        'Création ou refonte d’un site professionnel, rapide et adapté au mobile. Pour présenter clairement votre activité et faciliter les demandes de contact.'=>'Un site professionnel, rapide et adapté au mobile, pour présenter votre activité et faciliter les contacts.',
        'Un site professionnel, rapide et adapté au mobile, pour présenter votre activité et faciliter les contacts.'=>'Un site professionnel, rapide et adapté au mobile, pour présenter votre activité et faciliter les contacts.',

        'Une marque qui se remarque.'=>'Création de votre logo & identité visuelle.',
        'Création de logo & identité visuelle.'=>'Création de votre logo & identité visuelle.',
        'Création de votre logo & identité visuelle.'=>'Création de votre logo & identité visuelle.',
        'Un logo et un univers visuel reconnaissables. Pour faire bonne impression et rester en tête.'=>'Logo, couleurs, typographies et charte graphique, prêts pour tous vos supports.',
        'Logo, couleurs, typographies et charte graphique. Une identité claire, cohérente et facile à décliner.'=>'Logo, couleurs, typographies et charte graphique, prêts pour tous vos supports.',

        'Du concret. Du visible.'=>'Impression & signalétique.',
        'Des supports imprimés à la signalétique. Pour donner de la présence à votre activité, sur le terrain.'=>'Cartes de visite, flyers, affiches, panneaux, adhésifs et marquage véhicule.',
        'Cartes de visite, flyers, affiches, panneaux, adhésifs et marquage véhicule : de la création à la fabrication.'=>'Cartes de visite, flyers, affiches, panneaux, adhésifs et marquage véhicule.',
    ];
}

function jd_core_copy_page_raw_2160($page_key){
    $maps=[
        'creation-site-internet'=>[
            'Votre univers. Un site qui lui va bien.'=>'Création ou refonte de votre site internet.',
            'Pour votre création de site internet, John Design conçoit un site clair, responsive et cohérent avec votre activité.'=>'Je crée ou refonds votre site : professionnel, rapide, adapté au mobile et simple à faire évoluer.',
            'Je crée ou refonds votre site internet pour qu’il soit professionnel, rapide, responsive et simple à faire évoluer.'=>'Je crée ou refonds votre site : professionnel, rapide, adapté au mobile et simple à faire évoluer.',

            'Du sens derrière chaque choix.'=>'Un site clair, simple et efficace.',
            'Comprendre, tout de suite.'=>'Votre offre est claire.',
            'Une offre lisible, des informations au bon endroit et une hiérarchie qui accompagne le visiteur.'=>'Vos visiteurs comprennent rapidement ce que vous faites et comment vous contacter.',
            'Naviguer, sans effort.'=>'Simple sur ordinateur et mobile.',
            'Une interface adaptée aux mobiles, des repères simples et des boutons qui conduisent à une vraie prochaine étape.'=>'Une navigation claire, des repères simples et des boutons utiles.',
            'Garder la main.'=>'Facile à mettre à jour.',
            'Des contenus éditables dans WordPress et une prise en main prévue au périmètre, pour faire vivre votre site.'=>'Vos contenus restent modifiables dans WordPress, avec une prise en main prévue.',

            'Un périmètre clair. Une création sur mesure.'=>'Tout ce qu’il faut pour votre site professionnel.',
            'Cette base se précise avec vous. Le devis indique les livrables réellement retenus, le calendrier et les conditions du projet.'=>'Nous définissons ensemble ce dont votre site a réellement besoin.',

            'Vous pourriez le faire vous-même. Mais est-ce vraiment votre métier ?'=>'Créer un site seul est possible. Quand un problème arrive, mieux vaut avoir un interlocuteur.',
            'Mettre un site en ligne est facile. Le rendre fiable, clair et efficace demande plus de travail.'=>'Créer un site seul est possible. Quand un problème arrive, mieux vaut avoir un interlocuteur.',
            'Canva peut créer un visuel. Une IA peut générer un logo. Un constructeur peut mettre un site en ligne.'=>'L’IA peut créer vite. Pour les bugs, le mobile et les évolutions, vous avez un interlocuteur.',
            'Une IA ou un constructeur peut mettre un site en ligne rapidement. Mais quand il faut corriger un bug, améliorer le mobile, accélérer le site ou le faire évoluer, vous avez besoin d’un interlocuteur qui connaît le projet.'=>'L’IA peut créer vite. Pour les bugs, le mobile et les évolutions, vous avez un interlocuteur.',
            'C’est précisément mon métier.'=>'Je crée, je mets en ligne et je peux suivre le site ensuite.',
        ],

        'identite-visuelle'=>[
            'Être reconnu avant même de lire votre nom.'=>'Création de votre logo & identité visuelle.',
            'John Design construit une identité visuelle reconnaissable, cohérente et facile à décliner sur tous vos supports.'=>'Je crée votre logo, vos couleurs, vos typographies et votre charte graphique.',
            'Je crée votre logo et votre identité visuelle : couleurs, typographies et règles simples pour garder une image cohérente sur tous vos supports.'=>'Je crée votre logo, vos couleurs, vos typographies et votre charte graphique.',

            'Du sens derrière chaque choix.'=>'Une identité claire et facile à utiliser.',
            'Vous reconnaître.'=>'Un logo qui vous ressemble.',
            'Traduire votre positionnement et votre manière de travailler en choix visuels concrets.'=>'Une identité qui reflète votre activité et votre personnalité.',
            'Vous distinguer.'=>'Une image qui vous démarque.',
            'Trouver une direction singulière, adaptée à votre public, sans compliquer la lecture de votre activité.'=>'Des choix graphiques simples pour être reconnu plus facilement.',
            'Vous accompagner.'=>'Prête pour tous vos supports.',
            'Préparer les bonnes versions de votre identité pour ses usages réels : écran, papier et supports du quotidien.'=>'Des fichiers adaptés au web, à l’impression et à vos usages.',

            'Un périmètre clair. Une création sur mesure.'=>'Du logo à l’identité visuelle complète.',
            'Cette base se précise avec vous. Le devis indique les livrables réellement retenus, le calendrier et les conditions du projet.'=>'Logo, couleurs, typographies et déclinaisons : on construit ce dont vous avez besoin.',

            'Échange sur votre activité et vos publics'=>'Échange sur votre activité',
            'Recherche d’une direction graphique'=>'Recherche de pistes graphiques',
            "Recherche d'une direction graphique"=>'Recherche de pistes graphiques',
            'Création ou évolution du logo'=>'Création ou évolution du logo',
            'Choix des couleurs et typographies'=>'Choix des couleurs et typographies',
            'Déclinaisons retenues au devis'=>'Déclinaisons pour vos supports',
            'Guide d’utilisation de l’identité'=>'Guide simple d’utilisation',
            "Guide d'utilisation de l'identité"=>'Guide simple d’utilisation',
            'Fichiers adaptés aux supports convenus'=>'Fichiers prêts pour le web et l’impression',

            'Ensuite, votre identité visuelle peut se prolonger naturellement sur vos supports web, print et signalétique.'=>'Une identité prête pour votre site, vos cartes, affiches et panneaux.',
            'Votre identité est pensée pour fonctionner aussi bien sur votre site que sur vos cartes, affiches, panneaux ou véhicules.'=>'Une identité prête pour votre site, vos cartes, affiches et panneaux.',
        ],

        'print-signaletique'=>[
            'Du fichier à l’objet réel.'=>'Impression, signalétique & marquage.',
            "Du fichier à l'objet réel."=>'Impression, signalétique & marquage.',
            'John Design conçoit des supports visibles et cohérents avec votre identité, du fichier jusqu’à la fabrication.'=>'Je conçois vos cartes de visite, flyers, affiches et panneaux, jusqu’à la fabrication et à la pose.',
            'Je conçois vos cartes de visite, flyers, affiches, panneaux, adhésifs et marquages, puis je peux vous accompagner jusqu’à la fabrication.'=>'Je conçois vos cartes de visite, flyers, affiches et panneaux, jusqu’à la fabrication et à la pose.',

            'Du sens derrière chaque choix.'=>'Des supports clairs, visibles et bien préparés.',
            'Attirer le regard.'=>'Être vu rapidement.',
            'Donner envie de lire.'=>'Faire passer votre message.',
            'Préparer la fabrication.'=>'Prêt pour l’impression.',

            'Un périmètre clair. Une création sur mesure.'=>'De la carte de visite au panneau grand format.',
            'Cette base se précise avec vous. Le devis indique les livrables réellement retenus, le calendrier et les conditions du projet.'=>'Le support, le format et la fabrication sont définis selon votre besoin.',

            'Enfin, vos supports peuvent être déclinés sur panneaux, adhésifs, marquage véhicule et autres formats selon le projet.'=>'Création, fabrication et pose : un seul interlocuteur.',
            'Vous gardez un seul interlocuteur pour la création, la fabrication et, selon le projet, la pose.'=>'Création, fabrication et pose : un seul interlocuteur.',
        ],
    ];
    return $maps[$page_key]??[];
}

function jd_core_copy_exact_map_2160($page_key){
    $raw=array_merge(jd_core_copy_global_raw_2160(),jd_core_copy_page_raw_2160($page_key));
    $exact=[];
    foreach($raw as $from=>$to){
        $exact[jd_core_copy_key_2160($from)]=$to;
    }
    return $exact;
}

function jd_core_copy_replace_2160($value,$page_key=''){
    if(!is_string($value) || $value==='') return $value;
    $key=jd_core_copy_key_2160($value);
    $map=jd_core_copy_exact_map_2160($page_key);
    return array_key_exists($key,$map)?$map[$key]:$value;
}

function jd_core_identity_demo_block_2160($block){
    if(($block['blockName']??'')!=='johndesign/section') return false;
    $fields=$block['attrs']['fields']??[];
    $joined='';
    if(is_array($fields)){
        foreach($fields as $value){
            if(is_string($value)) $joined.=' '.jd_core_copy_key_2160($value);
        }
    }
    return strpos($joined,'une idée du style')!==false
        || strpos($joined,'une exploration graphique du studio')!==false
        || strpos($joined,'explorer la composition')!==false
        || strpos($joined,'hello vous')!==false
        || strpos($joined,'hello, vous')!==false;
}

function jd_core_clarity_h1_target_2160($page_key){
    $targets=[
        'creation-site-internet'=>'Création ou refonte de votre site internet.',
        'identite-visuelle'=>'Création de votre logo & identité visuelle.',
        'print-signaletique'=>'Impression, signalétique & marquage.',
    ];
    return $targets[$page_key]??'';
}

function jd_core_clarity_h1_is_clear_2160($page_key,$value){
    $plain=jd_core_copy_key_2160($value);
    $keywords=[
        'creation-site-internet'=>['site'],
        'identite-visuelle'=>['logo','identité'],
        'print-signaletique'=>['impression','signalétique','marquage','print'],
    ];
    if(empty($keywords[$page_key])) return true;
    foreach($keywords[$page_key] as $keyword){
        if(mb_strpos($plain,$keyword)!==false) return true;
    }
    return false;
}

function jd_core_clarity_force_service_h1_2160(&$blocks,$page_key){
    $target=jd_core_clarity_h1_target_2160($page_key);
    if(!$target) return false;

    $defs=jd_core_sections();
    foreach($blocks as &$block){
        if(($block['blockName']??'')!=='johndesign/section') continue;
        $sid=$block['attrs']['sectionId']??'';
        $def=$defs[$sid]??null;
        if(!$def || empty($def['fields'])) continue;
        $template=(string)($def['template']??'');

        foreach($def['fields'] as $field){
            $key=$field['key']??'';
            if(!$key || in_array($field['type']??'',['image','url'],true)) continue;
            $label=(string)($field['label']??'');
            $is_h1=false;
            if($template && preg_match('/<h1\b[^>]*>.*?\{\{'.preg_quote($key,'/').'\}\}.*?<\/h1>/is',$template)) $is_h1=true;
            if(!$is_h1 && preg_match('/\bH1\b|titre principal/i',$label)) $is_h1=true;
            if(!$is_h1) continue;

            $current=$block['attrs']['fields'][$key]??($field['default']??'');
            if(jd_core_clarity_h1_is_clear_2160($page_key,$current)){
                unset($block);
                return false;
            }
            if(!isset($block['attrs']['fields']) || !is_array($block['attrs']['fields'])) $block['attrs']['fields']=[];
            $block['attrs']['fields'][$key]=$target;
            unset($block);
            return true;
        }
    }
    unset($block);
    return false;
}

function jd_core_clarity_pass_2160(){
    if(get_option('jd_core_clarity_version')===JD_CORE_VERSION) return;

    $front=(int)get_option('page_on_front');
    $pages=get_posts([
        'post_type'=>'page',
        'post_status'=>['publish','draft','private'],
        'numberposts'=>-1,
        'suppress_filters'=>false,
    ]);

    $changed_pages=0;
    $replacement_count=0;
    $removed_blocks=0;

    foreach($pages as $page){
        $raw=(string)$page->post_content;
        if(!$raw || strpos($raw,'wp:johndesign/section')===false) continue;

        $page_key=((int)$page->ID===$front)?'home':(string)$page->post_name;
        $blocks=parse_blocks($raw);
        $next=[];
        $page_changed=false;

        foreach($blocks as $block){
            if($page_key==='identite-visuelle' && jd_core_identity_demo_block_2160($block)){
                $page_changed=true;
                $removed_blocks++;
                continue;
            }

            if(($block['blockName']??'')==='johndesign/section' && !empty($block['attrs']['fields']) && is_array($block['attrs']['fields'])){
                foreach($block['attrs']['fields'] as $key=>$value){
                    if(!is_string($value) || $value==='') continue;
                    $replacement=jd_core_copy_replace_2160($value,$page_key);
                    if($replacement!==$value){
                        $block['attrs']['fields'][$key]=$replacement;
                        $page_changed=true;
                        $replacement_count++;
                    }
                }
            }
            $next[]=$block;
        }

        if(jd_core_clarity_force_service_h1_2160($next,$page_key)) $page_changed=true;

        if($page_changed){
            wp_update_post([
                'ID'=>$page->ID,
                'post_content'=>wp_slash(serialize_blocks($next)),
            ]);
            $changed_pages++;
        }
    }

    update_option('jd_core_clarity_diag',[
        'changed_pages'=>$changed_pages,
        'replacements'=>$replacement_count,
        'removed_blocks'=>$removed_blocks,
        'checked_at'=>current_time('mysql'),
    ],false);
    update_option('jd_core_clarity_version',JD_CORE_VERSION,false);

    wp_cache_flush();
    if(function_exists('wp_cache_clear_cache')) wp_cache_clear_cache();
}
add_action('admin_init','jd_core_clarity_pass_2160',55);
add_action('wp_loaded','jd_core_clarity_pass_2160',5);

/**
 * Fallback immédiat au rendu : utile même avant l’écriture persistante
 * ou si une page est servie depuis une ancienne version de ses champs.
 */
function jd_core_clarity_render_2160($block_content,$block){
    if(($block['blockName']??'')!=='johndesign/section') return $block_content;

    $page_key='';
    if(is_front_page()) $page_key='home';
    elseif(is_page()){
        $page=get_queried_object();
        if($page instanceof WP_Post) $page_key=(string)$page->post_name;
    }

    if($page_key==='identite-visuelle'){
        $plain=jd_core_copy_key_2160($block_content);
        if(strpos($plain,'une idée du style')!==false
            || strpos($plain,'une exploration graphique du studio')!==false
            || strpos($plain,'explorer la composition')!==false
            || strpos($plain,'hello vous')!==false
            || strpos($plain,'hello, vous')!==false){
            return '';
        }
    }

    $plain_key=jd_core_copy_key_2160($block_content);

    // Les champs sont normalement déjà corrigés en base. Ce fallback traite
    // les titres/paragraphes encore rendus avec leur ancienne valeur.
    $raw=array_merge(jd_core_copy_global_raw_2160(),jd_core_copy_page_raw_2160($page_key));
    foreach($raw as $from=>$to){
        $from_key=jd_core_copy_key_2160($from);
        if($from_key!=='' && $plain_key===$from_key){
            return esc_html($to);
        }
    }

    return $block_content;
}
add_filter('render_block','jd_core_clarity_render_2160',60,2);
