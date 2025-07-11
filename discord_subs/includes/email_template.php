<?php
// includes/email_template.php (NUEVO ARCHIVO)
// Plantilla de correo HTML profesional para KekaGamer
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
    body { margin: 0; padding: 0; background-color: #111827; }
    table { border-spacing: 0; }
    td { padding: 0; }
    img { border: 0; }
    .wrapper { width: 100%; table-layout: fixed; background-color: #111827; padding-bottom: 60px; }
    .main { background-color: #1f2937; margin: 0 auto; width: 100%; max-width: 600px; border-spacing: 0; font-family: sans-serif; color: #e5e7eb; border-radius: 8px; }
    .button { background-color: #8b5cf6; color: #ffffff; text-decoration: none; padding: 12px 20px; border-radius: 5px; font-weight: bold; }
</style>
</head>
<body>
    <center class="wrapper">
        <table class="main" width="100%">
            <tr>
                <td style="padding: 10px 0 20px 0; text-align: center;">
                    <a href="<?= SITE_URL ?>" target="_blank"><img src="<?= SITE_URL ?>assets/images/logo.png" alt="KekaGamer Logo" width="100" style="max-width: 100%;"></a>
                </td>
            </tr>
            <tr>
                <td style="padding: 30px 30px 30px 30px;">
                    <table width="100%" style="border-spacing: 0;">
                        <tr>
                            <td>
                                <h1 style="font-size: 24px; color: #c4b5fd; margin: 0 0 20px 0;"><?= $titulo_mensaje ?? 'Notificación de KekaGamer' ?></h1>
                                <p style="font-size: 16px; line-height: 24px; color: #d1d5db; margin: 0 0 20px 0;"><?= $cuerpo_mensaje ?? '' ?></p>
                                <?php if (isset($texto_boton) && isset($url_boton)): ?>
                                <a href="<?= $url_boton ?>" target="_blank" class="button">
                                    <?= $texto_boton ?>
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td style="background-color: #111827; padding: 20px 30px; text-align: center; border-radius: 0 0 8px 8px;">
                    <p style="font-size: 12px; color: #6b7280;">Este es un correo electrónico automático. No respondas a este mensaje.</p>
                    <p style="font-size: 12px; color: #6b7280;">&copy; <?= date('Y') ?> KekaGamer.cl. Todos los derechos reservados.</p>
                </td>
            </tr>
        </table>
    </center>
</body>
</html>