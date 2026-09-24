<?php
if (!defined('ABSPATH')) exit;

function jd_core_contact_email(){
    $email=sanitize_email(get_option('jd_contact_email','jonathan@johndesign.net'));
    return is_email($email)?$email:'jonathan@johndesign.net';
}
function jd_core_contact_copy_email(){
    $email=sanitize_email(get_option('jd_contact_copy_email',''));
    return is_email($email)?$email:'';
}
function jd_core_site_from_email(){
    $host=(string)wp_parse_url(home_url('/'),PHP_URL_HOST);
    $host=preg_replace('/^www\./i','',$host);
    $candidate='wordpress@'.$host;
    return is_email($candidate)?$candidate:jd_core_contact_email();
}
function jd_core_mail_diag($status,$data=[]){
    $diag=array_merge([
        'status'=>$status,
        'checked_at'=>current_time('mysql'),
    ],$data);
    update_option('jd_core_mail_diag',$diag,false);
}
add_action('wp_mail_failed',function($error){
    if(!is_wp_error($error)) return;
    jd_core_mail_diag('error',[
        'message'=>$error->get_error_message(),
        'code'=>$error->get_error_code(),
    ]);
});
add_action('wp_mail_succeeded',function($mail_data){
    jd_core_mail_diag('success',[
        'message'=>'Message accepté par le système d’envoi WordPress.',
        'to'=>is_array($mail_data['to']??null)?implode(', ',$mail_data['to']):($mail_data['to']??''),
        'subject'=>$mail_data['subject']??'',
    ]);
});

function jd_core_contact_redirect($return,$status){
    $return=wp_validate_redirect($return,home_url('/'));
    $return=remove_query_arg('jd_contact',$return);
    wp_safe_redirect(add_query_arg('jd_contact',$status,$return).'#contact');
    exit;
}
function jd_core_contact_form($variant='home'){
    $full=$variant==='full';
    $uid=wp_unique_id('jd-form-');
    $action=esc_url(admin_url('admin-post.php'));
    $start=time();
    $return=esc_url((is_ssl()?'https':'http').'://'.($_SERVER['HTTP_HOST']??'').($_SERVER['REQUEST_URI']??'/'));
    ob_start(); ?>
    <form class="jd-contact-form" method="post" action="<?php echo $action; ?>" autocomplete="on" novalidate>
      <input type="hidden" name="action" value="jd_contact_submit">
      <input type="hidden" name="jd_variant" value="<?php echo esc_attr($variant); ?>">
      <input type="hidden" name="jd_started" value="<?php echo esc_attr($start); ?>">
      <input type="hidden" name="jd_return" value="<?php echo esc_attr($return); ?>">
      <?php wp_nonce_field('jd_contact','jd_nonce'); ?>

      <div class="jd-form-grid">
        <div class="jd-field"><label for="<?php echo $uid; ?>name">Votre nom *</label><input id="<?php echo $uid; ?>name" autocomplete="name" name="name" required maxlength="120" type="text"></div>
        <div class="jd-field"><label for="<?php echo $uid; ?>email">Votre e-mail *</label><input id="<?php echo $uid; ?>email" autocomplete="email" name="email" required maxlength="190" type="email"></div>
        <?php if($full): ?><div class="jd-field"><label for="<?php echo $uid; ?>company">Votre activité / entreprise</label><input id="<?php echo $uid; ?>company" autocomplete="organization" name="company" maxlength="160" type="text"></div><?php endif; ?>
        <div class="jd-field"><label for="<?php echo $uid; ?>phone">Téléphone</label><input id="<?php echo $uid; ?>phone" autocomplete="tel" name="phone" maxlength="40" type="tel"></div>
      </div>

      <div class="jd-field"><label for="<?php echo $uid; ?>project">Votre projet *</label>
        <select id="<?php echo $uid; ?>project" name="project" required>
          <option value="">Choisir</option>
          <option>Création / refonte de site</option>
          <option>Identité visuelle / logo</option>
          <option>Print / signalétique</option>
          <option>Projet global</option>
          <option>Autre</option>
        </select>
      </div>

      <?php if($full): ?><div class="jd-form-grid">
        <div class="jd-field"><label for="<?php echo $uid; ?>budget">Budget envisagé</label><input id="<?php echo $uid; ?>budget" name="budget" maxlength="100" placeholder="Une fourchette, même indicative"></div>
        <div class="jd-field"><label for="<?php echo $uid; ?>deadline">Échéance souhaitée</label><input id="<?php echo $uid; ?>deadline" name="deadline" maxlength="100" placeholder="Une date ou une période"></div>
      </div><?php endif; ?>

      <div class="jd-field"><label for="<?php echo $uid; ?>message"><?php echo $full?'Parlez-moi de votre besoin *':'En quelques mots *'; ?></label><textarea id="<?php echo $uid; ?>message" name="message" required maxlength="6000" rows="<?php echo $full?5:4; ?>" placeholder="Expliquez-moi ce que vous voulez lancer, améliorer ou créer."></textarea></div>

      <div class="jd-form-hp" aria-hidden="true"><label for="<?php echo $uid; ?>website">Ne pas remplir</label><input id="<?php echo $uid; ?>website" name="website" autocomplete="off" tabindex="-1" type="text"></div>
      <label class="jd-consent"><input name="consent" required type="checkbox" value="1"> J’accepte que ces informations soient utilisées pour répondre à ma demande.</label>
      <button type="submit">Envoyer ma demande</button>
      <p class="jd-caption">Réponse directement par e-mail. Vos informations ne sont utilisées que pour traiter votre demande.</p>

      <?php if(isset($_GET['jd_contact'])):
        $status=sanitize_key(wp_unslash($_GET['jd_contact']));
        $messages=[
          'success'=>'Merci, votre message a bien été envoyé.',
          'invalid'=>'Merci de vérifier les champs obligatoires.',
          'spam'=>'Votre envoi n’a pas pu être validé. Réessayez dans quelques instants.',
          'rate'=>'Trop de tentatives rapprochées. Réessayez un peu plus tard.',
          'error'=>'Le message n’a pas pu être envoyé. Réessayez ou écrivez directement à '.jd_core_contact_email().'.',
        ];
        $ok=$status==='success';
        $msg=$messages[$status]??$messages['error']; ?>
        <p class="jd-form-status <?php echo $ok?'':'is-error'; ?>" role="status"><?php echo esc_html($msg); ?></p>
      <?php endif; ?>
    </form>
    <?php return ob_get_clean();
}

function jd_core_admin_notification_html($data){
    $name=esc_html($data['name']??'');
    $email=esc_html($data['email']??'');
    $phone=esc_html($data['phone']??'');
    $company=esc_html($data['company']??'');
    $project=esc_html($data['project']??'');
    $budget=esc_html($data['budget']??'');
    $deadline=esc_html($data['deadline']??'');
    $message=nl2br(esc_html($data['message']??''));
    $origin=esc_html($data['origin']??'');
    $reply_href='mailto:'.rawurlencode($data['email']??'').'?subject='.rawurlencode('Re: votre demande — John Design');

    $rows='';
    $items=[
        'Nom'=>$name,
        'E-mail'=>$email,
        'Téléphone'=>$phone,
        'Entreprise / activité'=>$company,
        'Projet'=>$project,
        'Budget envisagé'=>$budget,
        'Échéance souhaitée'=>$deadline,
    ];
    foreach($items as $label=>$value){
        if($value==='') continue;
        $rows.='<tr>'.
            '<td style="padding:10px 0;color:#6b6870;font-size:14px;vertical-align:top;width:40%;">'.esc_html($label).'</td>'.
            '<td style="padding:10px 0;color:#191919;font-size:14px;font-weight:700;vertical-align:top;">'.$value.'</td>'.
        '</tr>';
    }

    return '<!doctype html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Nouvelle demande John Design</title>
</head>
<body style="margin:0;padding:0;background:#f5f2fa;font-family:Arial,Helvetica,sans-serif;color:#191919;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f5f2fa;padding:28px 12px;">
<tr><td align="center">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:700px;background:#ffffff;border-radius:28px;overflow:hidden;box-shadow:0 12px 34px rgba(25,25,25,.08);">
<tr><td style="background:#191919;padding:30px 34px;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"><tr>
<td><div style="display:inline-block;color:#ffffff;font-weight:900;font-size:27px;line-height:.82;letter-spacing:-1.5px;">john<br>design</div><span style="display:inline-block;color:#c9b4ef;font-size:30px;line-height:1;vertical-align:top;margin-left:7px;">✳</span></td>
<td align="right" style="color:#c9b4ef;font-size:13px;font-weight:700;">Nouvelle demande</td>
</tr></table>
</td></tr>

<tr><td style="padding:40px 38px 18px;">
<p style="margin:0 0 10px;color:#6b6870;font-size:13px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;">Formulaire John Design</p>
<h1 style="margin:0 0 14px;font-size:32px;line-height:1.1;letter-spacing:-1px;">'.$name.' vous contacte<br>pour « '.$project.' ».</h1>
<p style="margin:0;color:#4b4750;font-size:16px;line-height:1.6;">Voici toutes les informations envoyées depuis le site.</p>
</td></tr>

<tr><td style="padding:12px 38px 0;">
<div style="background:#f1ecfb;border-radius:20px;padding:24px 26px;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">'.$rows.'</table>
<div style="height:1px;background:#d9cdef;margin:12px 0 18px;"></div>
<p style="margin:0 0 8px;color:#6b6870;font-size:14px;">Message</p>
<p style="margin:0;font-size:15px;line-height:1.65;color:#191919;">'.$message.'</p>
</div>
</td></tr>

<tr><td style="padding:26px 38px 40px;">
<table role="presentation" cellspacing="0" cellpadding="0" border="0"><tr><td style="background:#191919;border-radius:999px;">
<a href="'.$reply_href.'" style="display:inline-block;padding:14px 22px;color:#ffffff;text-decoration:none;font-size:14px;font-weight:800;">Répondre à '.$name.'&nbsp; ↗</a>
</td></tr></table>
'.($origin!==''?'<p style="margin:24px 0 0;color:#8a858f;font-size:11px;line-height:1.55;">Page d’origine : '.$origin.'</p>':'').'
</td></tr>
</table>
</td></tr>
</table>
</body>
</html>';
}

function jd_core_customer_confirmation_html($data){
    $name=esc_html($data['name']??'');
    $email=esc_html($data['email']??'');
    $phone=esc_html($data['phone']??'');
    $company=esc_html($data['company']??'');
    $project=esc_html($data['project']??'');
    $budget=esc_html($data['budget']??'');
    $deadline=esc_html($data['deadline']??'');
    $message=nl2br(esc_html($data['message']??''));

    $rows='';
    $items=[
        'Projet'=>$project,
        'Entreprise / activité'=>$company,
        'Téléphone'=>$phone,
        'Budget envisagé'=>$budget,
        'Échéance souhaitée'=>$deadline,
    ];
    foreach($items as $label=>$value){
        if($value==='') continue;
        $rows.='<tr>'.
            '<td style="padding:10px 0;color:#6b6870;font-size:14px;vertical-align:top;width:42%;">'.esc_html($label).'</td>'.
            '<td style="padding:10px 0;color:#191919;font-size:14px;font-weight:700;vertical-align:top;">'.$value.'</td>'.
        '</tr>';
    }

    $site=esc_url(home_url('/'));
    $contact_email=esc_html(jd_core_contact_email());

    return '<!doctype html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Votre demande a bien été reçue</title>
</head>
<body style="margin:0;padding:0;background:#f5f2fa;font-family:Arial,Helvetica,sans-serif;color:#191919;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f5f2fa;padding:28px 12px;">
<tr><td align="center">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:680px;background:#ffffff;border-radius:28px;overflow:hidden;box-shadow:0 12px 34px rgba(25,25,25,.08);">

<tr><td style="background:#191919;padding:30px 34px;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
<tr>
<td style="font-size:0;line-height:1;">
<div style="display:inline-block;color:#ffffff;font-weight:900;font-size:27px;line-height:.82;letter-spacing:-1.5px;">john<br>design</div>
<span style="display:inline-block;color:#c9b4ef;font-size:30px;line-height:1;vertical-align:top;margin-left:7px;">✳</span>
</td>
<td align="right" style="color:#c9b4ef;font-size:13px;font-weight:700;">Demande reçue ✓</td>
</tr>
</table>
</td></tr>

<tr><td style="padding:42px 38px 16px;">
<p style="margin:0 0 10px;color:#6b6870;font-size:13px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;">Merci pour votre message</p>
<h1 style="margin:0 0 20px;font-size:34px;line-height:1.08;letter-spacing:-1.2px;color:#191919;">Bonjour '.$name.',<br>votre demande est bien arrivée.</h1>
<p style="margin:0;font-size:17px;line-height:1.65;color:#3e3b43;">Je l’ai bien reçue et je reviendrai vers vous le plus rapidement possible pour échanger sur votre projet.</p>
</td></tr>

<tr><td style="padding:18px 38px 0;">
<div style="background:#f1ecfb;border-radius:20px;padding:24px 26px;">
<p style="margin:0 0 14px;font-size:15px;font-weight:800;color:#191919;">Récapitulatif de votre demande</p>
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">'.$rows.'</table>
<div style="height:1px;background:#d9cdef;margin:12px 0 18px;"></div>
<p style="margin:0 0 8px;color:#6b6870;font-size:14px;">Votre message</p>
<p style="margin:0;font-size:15px;line-height:1.65;color:#191919;">'.$message.'</p>
</div>
</td></tr>

<tr><td style="padding:28px 38px 40px;">
<table role="presentation" cellspacing="0" cellpadding="0" border="0">
<tr><td style="background:#191919;border-radius:999px;">
<a href="'.$site.'" style="display:inline-block;padding:14px 22px;color:#ffffff;text-decoration:none;font-size:14px;font-weight:800;">Voir John Design&nbsp; ↗</a>
</td></tr>
</table>
<p style="margin:28px 0 0;color:#77727d;font-size:12px;line-height:1.6;">Ce message est une confirmation automatique envoyée après votre demande sur le site John Design. Vous pouvez répondre directement à cet e-mail ou écrire à <a href="mailto:'.$contact_email.'" style="color:#191919;">'.$contact_email.'</a>.</p>
</td></tr>

</table>
</td></tr>
</table>
</body>
</html>';
}

function jd_core_send_customer_confirmation($data){
    $to=sanitize_email($data['email']??'');
    if(!is_email($to)) return false;

    $subject='Votre demande a bien été reçue — John Design';
    $headers=[
        'Content-Type: text/html; charset=UTF-8',
        'From: John Design <'.jd_core_site_from_email().'>',
        'Reply-To: John Design <'.jd_core_contact_email().'>',
    ];

    return wp_mail($to,$subject,jd_core_customer_confirmation_html($data),$headers);
}

function jd_core_contact_submit(){
    if(!isset($_POST['jd_nonce'])||!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['jd_nonce'])),'jd_contact')) wp_die('Requête invalide.',403);

    $return=esc_url_raw(wp_unslash($_POST['jd_return']??home_url('/contact/')));
    $return=wp_validate_redirect($return,home_url('/contact/'));

    // Honeypot : les visiteurs réels ne voient jamais ce champ.
    if(!empty($_POST['website'])) jd_core_contact_redirect($return,'success');

    // Un robot remplit généralement le formulaire beaucoup trop vite.
    $started=absint($_POST['jd_started']??0);
    if(!$started||time()-$started<3) jd_core_contact_redirect($return,'spam');

    // Limite simple par IP : 5 envois / heure.
    $ip=sanitize_text_field($_SERVER['REMOTE_ADDR']??'unknown');
    $key='jd_rate_'.md5(wp_salt('nonce').'|'.$ip);
    $count=(int)get_transient($key);
    if($count>=5) jd_core_contact_redirect($return,'rate');
    set_transient($key,$count+1,HOUR_IN_SECONDS);

    $name=sanitize_text_field(wp_unslash($_POST['name']??''));
    $email=sanitize_email(wp_unslash($_POST['email']??''));
    $phone=sanitize_text_field(wp_unslash($_POST['phone']??''));
    $company=sanitize_text_field(wp_unslash($_POST['company']??''));
    $project=sanitize_text_field(wp_unslash($_POST['project']??''));
    $budget=sanitize_text_field(wp_unslash($_POST['budget']??''));
    $deadline=sanitize_text_field(wp_unslash($_POST['deadline']??''));
    $message=sanitize_textarea_field(wp_unslash($_POST['message']??''));

    $allowed_projects=['Création / refonte de site','Identité visuelle / logo','Print / signalétique','Projet global','Autre'];
    if(!$name||mb_strlen($name)>120||!is_email($email)||!in_array($project,$allowed_projects,true)||!$message||mb_strlen($message)>6000||empty($_POST['consent'])){
        jd_core_contact_redirect($return,'invalid');
    }

    // Filtre anti-abus supplémentaire : un formulaire commercial normal n’a pas besoin de nombreux liens.
    preg_match_all('~https?://|www\.~i',$message,$links);
    if(count($links[0])>4) jd_core_contact_redirect($return,'spam');

    $subject='[John Design] Nouvelle demande — '.$project;
    $origin=esc_url_raw(wp_get_referer()?:$return);
    $mail_data=[
        'name'=>$name,
        'email'=>$email,
        'phone'=>$phone,
        'company'=>$company,
        'project'=>$project,
        'budget'=>$budget,
        'deadline'=>$deadline,
        'message'=>$message,
        'origin'=>$origin,
    ];
    $body=jd_core_admin_notification_html($mail_data);
    $from=jd_core_site_from_email();
    $headers=[
      'Content-Type: text/html; charset=UTF-8',
      'From: John Design <'.$from.'>',
      'Reply-To: '.$name.' <'.$email.'>',
    ];

    $sent=wp_mail(jd_core_contact_email(),$subject,$body,$headers);

    // Optional direct copy (e.g. personal Gmail) avoids forwarding the message
    // through the mailbox, which can damage SPF alignment.
    $copy=jd_core_contact_copy_email();
    if($sent && $copy && strtolower($copy)!==strtolower(jd_core_contact_email())){
        wp_mail($copy,$subject,$body,$headers);
    }

    // Only acknowledge receipt to the client after the John Design notification
    // has successfully been handed to WordPress' mail system.
    if($sent){
        jd_core_send_customer_confirmation([
            'name'=>$name,
            'email'=>$email,
            'phone'=>$phone,
            'company'=>$company,
            'project'=>$project,
            'budget'=>$budget,
            'deadline'=>$deadline,
            'message'=>$message,
        ]);
    }

    jd_core_contact_redirect($return,$sent?'success':'error');
}
add_action('admin_post_nopriv_jd_contact_submit','jd_core_contact_submit');
add_action('admin_post_jd_contact_submit','jd_core_contact_submit');

function jd_core_send_test_email(){
    if(!current_user_can('manage_options')) return;
    check_admin_referer('jd_test_mail');
    $to=jd_core_contact_email();
    $subject='[John Design] Test du formulaire';
    $body="Ceci est un e-mail de test envoyé par John Design Core.\n\n".
          "Si vous recevez ce message, WordPress a réussi à transmettre l’e-mail au système d’envoi configuré sur le site.\n".
          "Date : ".current_time('mysql')."\n".
          "Site : ".home_url('/')."\n";
    $sent=wp_mail($to,$subject,$body,['Content-Type: text/plain; charset=UTF-8']);
    jd_core_mail_diag($sent?'success':'error',[
      'message'=>$sent?'Test accepté par WordPress. Vérifiez également sa réception dans votre boîte e-mail.':'WordPress n’a pas réussi à transmettre le message au système d’envoi.',
      'to'=>$to,
      'subject'=>$subject,
    ]);
    wp_safe_redirect(admin_url('admin.php?page=jd-core&mailtest='.($sent?'1':'0').'#jd-mail'));
    exit;
}
add_action('admin_post_jd_test_mail','jd_core_send_test_email');
