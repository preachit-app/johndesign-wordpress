<?php
if (!defined('ABSPATH')) exit;

/**
 * SEO minimal John Design.
 * Ne prend la main que si aucun plugin SEO majeur n'est actif.
 */
function jd_core_has_external_seo(){
    return defined('WPSEO_VERSION')
        || defined('RANK_MATH_VERSION')
        || defined('AIOSEO_VERSION')
        || class_exists('AIOSEO\\Plugin\\AIOSEO');
}

function jd_core_seo_map(){
    return [
        'home' => [
            'title' => 'John Design — Graphiste, sites internet, print & signalétique',
            'description' => 'John Design accompagne entreprises, associations et indépendants pour leur identité visuelle, site internet, print et signalétique. Un seul interlocuteur, de l’idée à la livraison.',
        ],
        'creation-site-internet' => [
            'title' => 'Création de site internet — John Design',
            'description' => 'Création et refonte de sites internet clairs, rapides et cohérents avec votre identité. John Design vous accompagne de la conception à la mise en ligne.',
        ],
        'identite-visuelle' => [
            'title' => 'Identité visuelle & logo — John Design',
            'description' => 'Logo, identité visuelle et univers de marque : John Design construit une image cohérente, reconnaissable et adaptée à tous vos supports.',
        ],
        'print-signaletique' => [
            'title' => 'Print, signalétique & marquage — John Design',
            'description' => 'Supports imprimés, panneaux, adhésifs, signalétique et marquage : John Design conçoit votre communication et peut accompagner sa fabrication et son installation.',
        ],
        'realisations' => [
            'title' => 'Réalisations — John Design',
            'description' => 'Découvrez une sélection de projets John Design : sites internet, identités visuelles, print, signalétique, marquage et supports de communication.',
        ],
        'a-propos' => [
            'title' => 'À propos — John Design',
            'description' => 'Découvrez John Design, studio créatif indépendant : un interlocuteur unique pour construire une communication cohérente, du premier concept à la livraison.',
        ],
        'methode' => [
            'title' => 'Méthode & accompagnement — John Design',
            'description' => 'Une méthode simple et claire pour avancer de l’idée au projet final : cadrage, création, ajustements, production et livraison.',
        ],
        'contact' => [
            'title' => 'Contact & devis — John Design',
            'description' => 'Parlez de votre projet à John Design : site internet, identité visuelle, print, signalétique ou besoin global. Demande de contact et devis.',
        ],
        'mentions-legales' => [
            'title' => 'Mentions légales — John Design',
            'description' => 'Mentions légales du site John Design.',
            'noindex' => true,
        ],
        'politique-de-confidentialite' => [
            'title' => 'Politique de confidentialité — John Design',
            'description' => 'Politique de confidentialité et traitement des données du site John Design.',
            'noindex' => true,
        ],
    ];
}

function jd_core_seo_current_key(){
    if (is_front_page()) return 'home';
    if (!is_page()) return '';
    $post=get_queried_object();
    return ($post instanceof WP_Post)?$post->post_name:'';
}
function jd_core_seo_current(){
    $key=jd_core_seo_current_key();
    $map=jd_core_seo_map();
    return $key && isset($map[$key]) ? $map[$key] : null;
}

add_filter('pre_get_document_title',function($title){
    if (is_admin() || jd_core_has_external_seo()) return $title;
    $seo=jd_core_seo_current();
    return !empty($seo['title']) ? $seo['title'] : $title;
},20);

add_filter('wp_robots',function($robots){
    $seo=jd_core_seo_current();
    if (!empty($seo['noindex'])) {
        $robots['noindex']=true;
        $robots['nofollow']=false;
    }
    return $robots;
},20);

add_action('wp_head',function(){
    if (is_admin() || jd_core_has_external_seo()) return;
    $seo=jd_core_seo_current();
    if (!$seo) return;

    $description=trim((string)($seo['description']??''));
    if ($description) echo "\n<meta name=\"description\" content=\"".esc_attr($description)."\">\n";

    $url=is_front_page()?home_url('/'):get_permalink();
    $title=(string)($seo['title']??wp_get_document_title());
    echo '<meta property="og:type" content="website">'."\n";
    echo '<meta property="og:locale" content="fr_FR">'."\n";
    echo '<meta property="og:site_name" content="John Design">'."\n";
    echo '<meta property="og:title" content="'.esc_attr($title).'">'."\n";
    if ($description) echo '<meta property="og:description" content="'.esc_attr($description).'">'."\n";
    echo '<meta property="og:url" content="'.esc_url($url).'">'."\n";
    echo '<meta name="twitter:card" content="summary_large_image">'."\n";

    if (is_front_page()) {
        $schema=[
            '@context'=>'https://schema.org',
            '@type'=>'ProfessionalService',
            'name'=>'John Design',
            'url'=>home_url('/'),
            'email'=>'mailto:'.jd_core_contact_email(),
            'description'=>$description,
            'areaServed'=>[
                '@type'=>'Country',
                'name'=>'France',
            ],
            'sameAs'=>[
                'https://www.instagram.com/johndesign_net/',
            ],
            'knowsAbout'=>[
                'Création de site internet',
                'Identité visuelle',
                'Logo',
                'Print',
                'Signalétique',
                'Marquage',
            ],
        ];
        echo '<script type="application/ld+json">'.wp_json_encode($schema,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES).'</script>'."\n";
    }
},5);
