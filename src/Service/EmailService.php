<?php

namespace App\Service;

use SendGrid;
use SendGrid\Mail\Mail;

class EmailService
{
    private string $sendGridApiKey;
    private string $fromEmail;
    private string $fromName;
    private string $frontendUrl;

    public function __construct()
    {
        $this->sendGridApiKey = $_ENV['SENDGRID_API_KEY'] ?? '';
        $this->fromEmail = $_ENV['SENDGRID_FROM_EMAIL'] ?? 'noreply@yorubaslatinos.com';
        $this->fromName = $_ENV['SENDGRID_FROM_NAME'] ?? 'Yorubas Latinos';
        $this->frontendUrl = $_ENV['FRONTEND_URL'] ?? 'https://app.yorubaslatinos.com';
    }

    public function sendPasswordResetEmail(string $toEmail, string $toName, string $resetToken): bool
    {
        if (empty($this->sendGridApiKey)) {
            throw new \Exception('SendGrid API key not configured');
        }

        $email = new Mail();
        $email->setFrom($this->fromEmail, $this->fromName);
        $email->setSubject('Restablecer Contraseña - Yorubas Latinos');
        $email->addTo($toEmail, $toName);

        $resetUrl = $this->frontendUrl . '/reset-password?token=' . $resetToken;

        $htmlContent = $this->getPasswordResetEmailTemplate($toName, $resetUrl, $resetToken);
        $textContent = $this->getPasswordResetEmailTextTemplate($toName, $resetUrl);

        $email->addContent("text/plain", $textContent);
        $email->addContent("text/html", $htmlContent);

        $sendgrid = new SendGrid($this->sendGridApiKey);

        try {
            $response = $sendgrid->send($email);
            
            if ($response->statusCode() >= 200 && $response->statusCode() < 300) {
                return true;
            }
            
            error_log('SendGrid error: ' . $response->body());
            return false;
            
        } catch (\Exception $e) {
            error_log('Email sending error: ' . $e->getMessage());
            return false;
        }
    }

    private function getPasswordResetEmailTemplate(string $name, string $resetUrl, string $token): string
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Restablecer Contraseña</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                .button { display: inline-block; background: #667eea; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                .button:hover { background: #5a6fd8; }
                .footer { text-align: center; margin-top: 30px; color: #666; font-size: 14px; }
                .warning { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🔮 Yorubas Latinos</h1>
                    <p>Restablecimiento de Contraseña</p>
                </div>
                <div class='content'>
                    <h2>Hola {$name},</h2>
                    <p>Has solicitado restablecer tu contraseña para tu cuenta de Yorubas Latinos.</p>
                    
                    <p>Haz clic en el siguiente botón para crear una nueva contraseña:</p>
                    
                    <div style='text-align: center;'>
                        <a href='{$resetUrl}' class='button'>Restablecer Contraseña</a>
                    </div>
                    
                    <div class='warning'>
                        <strong>⚠️ Importante:</strong>
                        <ul>
                            <li>Este enlace expira en <strong>1 hora</strong></li>
                            <li>Solo puede ser usado una vez</li>
                            <li>Si no solicitaste este cambio, ignora este email</li>
                        </ul>
                    </div>
                    
                    <p>Si el botón no funciona, copia y pega este enlace en tu navegador:</p>
                    <p style='word-break: break-all; background: #eee; padding: 10px; border-radius: 5px;'>{$resetUrl}</p>
                    
                    <p><strong>Código de seguridad:</strong> {$token}</p>
                </div>
                <div class='footer'>
                    <p>Este email fue enviado desde Yorubas Latinos</p>
                    <p>Si tienes problemas, contacta a soporte técnico</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }

    private function getPasswordResetEmailTextTemplate(string $name, string $resetUrl): string
    {
        return "
Hola {$name},

Has solicitado restablecer tu contraseña para tu cuenta de Yorubas Latinos.

Para crear una nueva contraseña, visita el siguiente enlace:
{$resetUrl}

IMPORTANTE:
- Este enlace expira en 1 hora
- Solo puede ser usado una vez
- Si no solicitaste este cambio, ignora este email

Si tienes problemas con el enlace, cópialo y pégalo directamente en tu navegador.

---
Yorubas Latinos
Equipo de Soporte Técnico
        ";
    }

    public function sendWelcomeEmail(string $toEmail, string $toName): bool
    {
        if (empty($this->sendGridApiKey)) {
            return false; // Fail silently for welcome emails
        }

        $email = new Mail();
        $email->setFrom($this->fromEmail, $this->fromName);
        $email->setSubject('¡Bienvenido a Yorubas Latinos!');
        $email->addTo($toEmail, $toName);

        $htmlContent = "
        <h1>¡Bienvenido a Yorubas Latinos, {$toName}!</h1>
        <p>Tu cuenta ha sido creada exitosamente.</p>
        <p>Ya puedes acceder a la plataforma y comenzar a gestionar tu práctica religiosa.</p>
        <p>¡Que Ifá te bendiga!</p>
        ";

        $textContent = "¡Bienvenido a Yorubas Latinos, {$toName}!\n\nTu cuenta ha sido creada exitosamente.\nYa puedes acceder a la plataforma y comenzar a gestionar tu práctica religiosa.\n\n¡Que Ifá te bendiga!";

        $email->addContent("text/plain", $textContent);
        $email->addContent("text/html", $htmlContent);

        $sendgrid = new SendGrid($this->sendGridApiKey);

        try {
            $response = $sendgrid->send($email);
            return $response->statusCode() >= 200 && $response->statusCode() < 300;
        } catch (\Exception $e) {
            error_log('Welcome email error: ' . $e->getMessage());
            return false;
        }
    }
} 