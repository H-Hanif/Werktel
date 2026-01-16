<?php
/* =========================================================
 *  contact.php – Werk Tel (pagina handler)
 *  - GET: nooit wit scherm -> redirect naar /contact
 *  - POST: versturen mail + redirect met success/error
 * =======================================================*/

/* -------------------------------
   Basisinstellingen
--------------------------------*/
$toAdmin   = "yaser@easysolutions.nl";
$fromEmail = "info@werktel.nl";
$fromName  = "Werk Tel";
$logoUrl   = "https://werktel.nl/logo.png";

/* Base URL (werkt lokaal + live) */
$scheme  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host    = $_SERVER['HTTP_HOST'] ?? 'localhost';
$baseUrl = "{$scheme}://{$host}";

/* Waar je contactpagina zit (Symfony route) */
$contactPage = "{$baseUrl}/contact";

/* -------------------------------
   Helpers
--------------------------------*/
function clean($v){ return trim(filter_var($v, FILTER_SANITIZE_FULL_SPECIAL_CHARS)); }
function isEmail($v){ return (bool) filter_var($v, FILTER_VALIDATE_EMAIL); }

/* -------------------------------
   GET -> altijd terug naar contactpagina
--------------------------------*/
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header("Location: {$contactPage}");
    exit;
}

/* -------------------------------
   Honeypot
--------------------------------*/
if (!empty($_POST['_honey'] ?? '')) {
    header("Location: {$contactPage}?success=1");
    exit;
}

/* -------------------------------
   Velden
--------------------------------*/
$naam     = clean($_POST['Naam'] ?? '');
$bedrijf  = clean($_POST['Bedrijf'] ?? '');
$email    = clean($_POST['email'] ?? '');
$telefoon = clean($_POST['Telefoonnummer'] ?? '');
$bericht  = clean($_POST['Bericht'] ?? '');

/* -------------------------------
   Validatie (zelfde tekst als jij had)
--------------------------------*/
$errors = [];
if ($naam === '')     $errors[] = "Naam ontbreekt";
if (!isEmail($email)) $errors[] = "E-mailadres is ongeldig";
if ($bericht === '')  $errors[] = "Bericht ontbreekt";

/* Bij fouten: redirect terug naar /contact met fouttekst */
if (!empty($errors)) {
    $msg = rawurlencode("Fouten: " . implode(", ", $errors));
    header("Location: {$contactPage}?error={$msg}");
    exit;
}

/* -------------------------------
   HTML template
--------------------------------*/
function emailTemplate($logoUrl, $title, $intro, $fieldsHtml, $footerNote, $siteUrl) {
    $year = date('Y');
    return <<<HTML
<!doctype html>
<html lang="nl">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width">
  <title>{$title}</title>
  <style>
    body{margin:0;background:#f6f7fb;font-family:Arial,Helvetica,sans-serif;color:#222;}
    .wrapper{width:100%;padding:24px 12px;}
    .container{max-width:640px;margin:0 auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 6px 20px rgba(0,0,0,.06);}
    .header{padding:24px;text-align:center;background:#f0fff5;border-bottom:1px solid #e8f5ed;}
    .logo{max-width:200px;height:auto;display:block;margin:0 auto;}
    .brand{font-weight:700;color:#15803d;font-size:14px;margin-top:6px;text-decoration:none;display:inline-block;}
    .content{padding:24px;}
    h1{font-size:20px;margin:0 0 8px;color:#15803d;}
    p{margin:0 0 12px;line-height:1.5;}
    .card{border:1px solid #eee;border-radius:12px;padding:16px;background:#fff;}
    .table{width:100%;border-collapse:collapse;}
    .table th,.table td{padding:10px;border-bottom:1px solid #f0f0f0;text-align:left;font-size:14px;}
    .table th{color:#666;width:42%;}
    .footer{padding:18px 24px;color:#666;font-size:12px;text-align:center;border-top:1px solid #f3f4f6;background:#fafafa;}
    a{color:#16a34a;text-decoration:none;}
  </style>
</head>
<body>
  <div class="wrapper"><div class="container">
    <div class="header">
      <a href="{$siteUrl}" target="_blank" style="text-decoration:none;">
        <img class="logo" src="{$logoUrl}" alt="Werk Tel logo">
        <div class="brand">Werk Tel</div>
      </a>
    </div>
    <div class="content">
      <h1>{$title}</h1>
      <p>{$intro}</p>
      <div class="card" style="margin:16px 0;">{$fieldsHtml}</div>
      <p style="font-size:12px;color:#6b7280;">{$footerNote}</p>
    </div>
    <div class="footer">© {$year} Werk Tel · <a href="{$siteUrl}">{$siteUrl}</a></div>
  </div></div>
</body>
</html>
HTML;
}

$fieldsHtml = '
<table class="table">
  <tr><th>Naam</th><td>'.nl2br($naam).'</td></tr>
  <tr><th>Bedrijf</th><td>'.nl2br($bedrijf).'</td></tr>
  <tr><th>E-mail</th><td>'.$email.'</td></tr>
  <tr><th>Telefoon</th><td>'.nl2br($telefoon).'</td></tr>
  <tr><th>Bericht</th><td>'.nl2br($bericht).'</td></tr>
</table>
';

/* Admin mail */
$subjectAdmin = "📩 Nieuw contactbericht – {$naam}" . ($bedrijf ? " ({$bedrijf})" : "");
$introAdmin   = "Er is een nieuw bericht verstuurd via het contactformulier op WerkTel. Hieronder staan de details.";
$footerAdmin  = "Reageer gerust via 'Beantwoorden' (Reply-To staat op de inzender).";
$htmlAdmin    = emailTemplate($logoUrl, $subjectAdmin, $introAdmin, $fieldsHtml, $footerAdmin, $baseUrl);

/* User mail */
$subjectUser = "Bevestiging: we hebben je bericht ontvangen";
$introUser   = "Bedankt voor je bericht aan Werk Tel! We reageren doorgaans binnen 1 werkdag. Hieronder vind je een kopie van je bericht.";
$footerUser  = "Vragen of aanvullen? Antwoord gerust op deze e-mail.";
$htmlUser    = emailTemplate($logoUrl, $subjectUser, $introUser, $fieldsHtml, $footerUser, $baseUrl);

/* Headers */
$headersCommon  = "MIME-Version: 1.0\r\n";
$headersCommon .= "Content-Type: text/html; charset=UTF-8\r\n";
$headersCommon .= "From: {$fromName} <{$fromEmail}>\r\n";

$headersToAdmin = $headersCommon . "Reply-To: {$naam} <{$email}>\r\n";
$headersToUser  = $headersCommon . "Reply-To: {$fromName} <{$fromEmail}>\r\n";

/* Strato envelope sender */
$additionalParams = "-f {$fromEmail}";

/* Send */
$okAdmin = mail($toAdmin, $subjectAdmin, $htmlAdmin, $headersToAdmin, $additionalParams);
$okUser  = mail($email,   $subjectUser,  $htmlUser,  $headersToUser,  $additionalParams);

/* Redirects (geen wit scherm) */
if ($okAdmin) {
    header("Location: {$contactPage}?success=1");
    exit;
}

header("Location: {$contactPage}?error=" . rawurlencode("Verzenden mislukt. Probeer later opnieuw of neem contact op."));
exit;
