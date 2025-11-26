<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

final class ContactController extends AbstractController
{
    // 👉 PAS DIT AAN NAAR JE EIGEN GEGEVENS
    private string $toAdmin   = 'yaser@easysolutions.nl'; // waar jij het bericht wilt ontvangen
    private string $fromEmail = 'info@zakelijkopjemobiel.nl'; // afzender (mailbox op je server)
    private string $fromName  = 'Zakelijk op je Mobiel';
    private string $siteUrl   = 'https://zakelijkopjemobiel.nl';
    private string $logoUrl   = 'https://zakelijkopjemobiel.nl/assets/zakelijkopjemobiel-high-resolution-logo-transparent.png';

    #[Route('/contact', name: 'app_contact', methods: ['GET', 'POST'])]
    public function index(Request $request, MailerInterface $mailer): Response
    {
        $formData = [
            'naam'     => '',
            'bedrijf'  => '',
            'email'    => '',
            'telefoon' => '',
            'bericht'  => '',
        ];
        $errors = [];

        if ($request->isMethod('POST')) {
            // honeypot
            if ($request->request->get('_honey')) {
                // bot: doe alsof het goed ging
                $this->addFlash('success', 'Dank je wel, je bericht is ontvangen.');
                return $this->redirectToRoute('app_contact');
            }

            // data uit formulier
            $formData['naam']     = $this->clean($request->request->get('Naam', ''));
            $formData['bedrijf']  = $this->clean($request->request->get('Bedrijf', ''));
            $formData['email']    = $this->clean($request->request->get('email', ''));
            $formData['telefoon'] = $this->clean($request->request->get('Telefoonnummer', ''));
            $formData['bericht']  = $this->clean($request->request->get('Bericht', ''));

            // validatie
            if ($formData['naam'] === '') {
                $errors[] = 'Naam ontbreekt';
            }
            if (!$this->isEmail($formData['email'])) {
                $errors[] = 'E-mailadres is ongeldig';
            }
            if ($formData['bericht'] === '') {
                $errors[] = 'Bericht ontbreekt';
            }

            if (!$errors) {
                // tabel HTML
                $fieldsHtml = '
<table class="table">
  <tr><th>Naam</th><td>' . nl2br($formData['naam']) . '</td></tr>
  <tr><th>Bedrijf</th><td>' . nl2br($formData['bedrijf']) . '</td></tr>
  <tr><th>E-mail</th><td>' . $formData['email'] . '</td></tr>
  <tr><th>Telefoon</th><td>' . nl2br($formData['telefoon']) . '</td></tr>
  <tr><th>Bericht</th><td>' . nl2br($formData['bericht']) . '</td></tr>
</table>';

                // e-mail naar admin
                $subjectAdmin = "📩 Nieuw contactbericht – {$formData['naam']}" .
                    ($formData['bedrijf'] ? " ({$formData['bedrijf']})" : "");
                $introAdmin  = "Er is een nieuw bericht verstuurd via het contactformulier. Details staan hieronder.";
                $footerAdmin = "Reageer gerust via 'Beantwoorden' (Reply-To staat op de inzender).";

                $htmlAdmin = $this->emailTemplate(
                    $this->logoUrl,
                    $subjectAdmin,
                    $introAdmin,
                    $fieldsHtml,
                    $footerAdmin,
                    $this->siteUrl
                );

                $emailAdmin = (new Email())
                    ->from(sprintf('%s <%s>', $this->fromName, $this->fromEmail))
                    ->to($this->toAdmin)
                    ->replyTo(sprintf('%s <%s>', $formData['naam'], $formData['email']))
                    ->subject($subjectAdmin)
                    ->html($htmlAdmin);

                // bevestiging naar gebruiker
                $subjectUser = "Bevestiging: we hebben je bericht ontvangen";
                $introUser   = "Bedankt voor je bericht aan Zakelijk op je Mobiel! We reageren doorgaans binnen 1 werkdag. Hieronder vind je een kopie van je bericht.";
                $footerUser  = "Vragen of aanvullen? Antwoord gerust op deze e-mail.";

                $htmlUser = $this->emailTemplate(
                    $this->logoUrl,
                    $subjectUser,
                    $introUser,
                    $fieldsHtml,
                    $footerUser,
                    $this->siteUrl
                );

                $emailUser = (new Email())
                    ->from(sprintf('%s <%s>', $this->fromName, $this->fromEmail))
                    ->to($formData['email'])
                    ->replyTo(sprintf('%s <%s>', $this->fromName, $this->fromEmail))
                    ->subject($subjectUser)
                    ->html($htmlUser);

                try {
                    $mailer->send($emailAdmin);
                    $mailer->send($emailUser);

                    $this->addFlash('success', 'Bedankt! Je bericht is succesvol verstuurd.');
                    return $this->redirectToRoute('app_contact');
                } catch (\Throwable $e) {
                    $errors[] = 'Verzenden mislukt. Probeer het later opnieuw.';
                }
            }
        }

        return $this->render('contact/index.html.twig', [
            'formData' => $formData,
            'errors'   => $errors,
        ]);
    }

    /* ---------- helpers ---------- */

    private function clean(?string $v): string
    {
        $v = $v ?? '';
        return trim(filter_var($v, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    }

    private function isEmail(?string $v): bool
    {
        if ($v === null) {
            return false;
        }
        return (bool) filter_var($v, FILTER_VALIDATE_EMAIL);
    }

    private function emailTemplate(
        string $logoUrl,
        string $title,
        string $intro,
        string $fieldsHtml,
        string $footerNote,
        string $siteUrl
    ): string {
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
    .brand{font-weight:700;color:#15803d;font-size:14px;margin-top:6px;text-decoration:none;display:inline-block}
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
      <a href="{$siteUrl}" target="_blank" style="text-decoration:none">
        <img class="logo" src="{$logoUrl}" alt="Zakelijkopjemobiel logo">
        <div class="brand">Zakelijkopjemobiel.nl</div>
      </a>
    </div>
    <div class="content">
      <h1>{$title}</h1>
      <p>{$intro}</p>
      <div class="card" style="margin:16px 0;">{$fieldsHtml}</div>
      <p style="font-size:12px;color:#6b7280;">{$footerNote}</p>
    </div>
    <div class="footer">© {$year} Zakelijk op je Mobiel · <a href="{$siteUrl}">{$siteUrl}</a></div>
  </div></div>
</body>
</html>
HTML;
    }
}
