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
        $email->setSubject('Restablecer contraseña');
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
            <title>Restablecer contraseña</title>
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body { 
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; 
                    line-height: 1.6; 
                    color: #333;
                    background: linear-gradient(135deg, #2d5a3f 0%, #4a7c59 50%, #2d5a3f 100%);
                    min-height: 100vh;
                    padding: 40px 20px;
                }
                .email-container { 
                    max-width: 600px; 
                    margin: 0 auto; 
                    background: #ffffff;
                    border-radius: 20px;
                    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
                    overflow: hidden;
                }
                .header { 
                    background: linear-gradient(135deg, #2d5a3f 0%, #4a7c59 100%); 
                    color: white; 
                    padding: 40px 30px; 
                    text-align: center; 
                }
                .logo-circle {
                    width: 80px;
                    height: 80px;
                    background: #ffffff;
                    border-radius: 50%;
                    margin: 0 auto 20px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    border: 4px solid #f4b942;
                    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
                }
                .logo-icon {
                    font-size: 32px;
                    color: #4a7c59;
                }
                .brand-name {
                    font-size: 32px;
                    font-weight: 700;
                    color: #ffffff;
                    margin: 10px 0 5px;
                    letter-spacing: -0.5px;
                }
                .subtitle {
                    font-size: 16px;
                    color: rgba(255,255,255,0.9);
                    font-weight: 400;
                }
                .content { 
                    background: #ffffff; 
                    padding: 40px 30px; 
                }
                .greeting {
                    font-size: 24px;
                    color: #2d5a3f;
                    margin-bottom: 20px;
                    font-weight: 600;
                }
                .message {
                    font-size: 16px;
                    color: #555;
                    margin-bottom: 30px;
                    line-height: 1.6;
                }
                .button-container {
                    text-align: center;
                    margin: 30px 0;
                }
                .button { 
                    display: inline-block; 
                    background: linear-gradient(135deg, #4a7c59 0%, #2d5a3f 100%); 
                    color: white; 
                    padding: 16px 40px; 
                    text-decoration: none; 
                    border-radius: 12px; 
                    font-weight: 600;
                    font-size: 16px;
                    transition: all 0.3s ease;
                    box-shadow: 0 4px 12px rgba(74, 124, 89, 0.3);
                    border: 2px solid transparent;
                }
                .button:hover { 
                    transform: translateY(-2px);
                    box-shadow: 0 6px 20px rgba(74, 124, 89, 0.4);
                }
                .warning { 
                    background: linear-gradient(135deg, #fff8e1 0%, #fff3c4 100%); 
                    border: 2px solid #f4b942; 
                    padding: 20px; 
                    border-radius: 12px; 
                    margin: 25px 0; 
                }
                .warning-title {
                    color: #e65100;
                    font-weight: 600;
                    font-size: 16px;
                    margin-bottom: 10px;
                    display: flex;
                    align-items: center;
                    gap: 8px;
                }
                .warning ul {
                    margin-left: 20px;
                    color: #bf360c;
                }
                .warning li {
                    margin: 5px 0;
                }
                .link-container {
                    background: #f8f9fa;
                    border: 2px solid #e9ecef;
                    border-radius: 8px;
                    padding: 15px;
                    margin: 20px 0;
                    word-break: break-all;
                    font-family: 'Courier New', monospace;
                    font-size: 14px;
                    color: #495057;
                }
                .security-code {
                    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
                    border: 2px solid #4a7c59;
                    border-radius: 8px;
                    padding: 15px;
                    text-align: center;
                    margin: 20px 0;
                }
                .security-code-label {
                    color: #2d5a3f;
                    font-weight: 600;
                    font-size: 14px;
                    margin-bottom: 5px;
                }
                .security-code-value {
                    font-family: 'Courier New', monospace;
                    font-size: 18px;
                    color: #4a7c59;
                    font-weight: bold;
                    letter-spacing: 2px;
                }
                .footer { 
                    background: #f8f9fa;
                    text-align: center; 
                    padding: 30px; 
                    color: #6c757d; 
                    font-size: 14px; 
                    border-top: 1px solid #e9ecef;
                }
                .footer-brand {
                    color: #4a7c59;
                    font-weight: 600;
                    margin-bottom: 8px;
                }
                .divider {
                    height: 3px;
                    background: linear-gradient(90deg, #4a7c59 0%, #f4b942 50%, #4a7c59 100%);
                    margin: 0;
                    border: none;
                }
                @media only screen and (max-width: 600px) {
                    .email-container { margin: 0 10px; }
                    .content { padding: 30px 20px; }
                    .header { padding: 30px 20px; }
                    .brand-name { font-size: 28px; }
                    .greeting { font-size: 22px; }
                    .button { padding: 14px 30px; }
                }
            </style>
        </head>
        <body>
            <div class='email-container'>
                <div class='header'>
                    <div class='logo-circle'>
                        <div class='logo-icon'>📖</div>
                    </div>
                    <div class='brand-name'>Yorubas Latinos</div>
                    <div class='subtitle'>Restablecimiento de Contraseña</div>
                </div>
                
                <hr class='divider'>
                
                <div class='content'>
                    <div class='greeting'>Hola {$name},</div>
                    
                    <p class='message'>
                        Has solicitado restablecer tu contraseña para tu cuenta de <strong>Yorubas Latinos</strong>. 
                        Estamos aquí para ayudarte a recuperar el acceso a tu cuenta de forma segura.
                    </p>
                    
                    <p class='message'>
                        Haz clic en el siguiente botón para crear una nueva contraseña:
                    </p>
                    
                    <div class='button-container'>
                        <a href='{$resetUrl}' class='button'>🔒 Restablecer contraseña</a>
                    </div>
                    
                    <div class='warning'>
                        <div class='warning-title'>
                            ⚠️ Información importante
                        </div>
                        <ul>
                            <li>Este enlace <strong>expira en 1 hora</strong></li>
                            <li>Solo puede ser usado <strong>una vez</strong></li>
                            <li>Si no solicitaste este cambio, <strong>ignora este email</strong></li>
                            <li>Tu cuenta permanece segura hasta que uses este enlace</li>
                        </ul>
                    </div>
                    
                    <p class='message'>
                        Si el botón anterior no funciona, copia y pega este enlace en tu navegador:
                    </p>
                    
                    <div class='link-container'>{$resetUrl}</div>
                    
                    <div class='security-code'>
                        <div class='security-code-label'>Código de seguridad:</div>
                        <div class='security-code-value'>{$token}</div>
                    </div>
                </div>
                
                <div class='footer'>
                    <div class='footer-brand'>Yorubas Latinos</div>
                    <p>Este email fue enviado de forma automática y segura.</p>
                    <p>Si tienes problemas, contacta a nuestro equipo de soporte técnico.</p>
                    <p style='margin-top: 15px; font-size: 12px; color: #adb5bd;'>
                        © ".date('Y')." Yorubas Latinos. Todos los derechos reservados.
                    </p>
                </div>
            </div>
        </body>
        </html>
        ";
    }

    private function getPasswordResetEmailTextTemplate(string $name, string $resetUrl): string
    {
        return "
📖 YORUBAS LATINOS
==================
Restablecimiento de contraseña

Hola {$name},

Has solicitado restablecer tu contraseña para tu cuenta de Yorubas Latinos.
Estamos aquí para ayudarte a recuperar el acceso a tu cuenta de forma segura.

🔒 RESTABLECER CONTRASEÑA
Para crear una nueva contraseña, visita el siguiente enlace:
{$resetUrl}

⚠️ INFORMACIÓN IMPORTANTE:
• Este enlace EXPIRA EN 1 HORA
• Solo puede ser usado UNA VEZ
• Si no solicitaste este cambio, IGNORA este email
• Tu cuenta permanece segura hasta que uses este enlace

Si tienes problemas con el enlace, cópialo y pégalo directamente en tu navegador.

---
📧 Yorubas Latinos
Este email fue enviado de forma automática y segura.
Si tienes problemas, contacta a nuestro equipo de soporte técnico.

© ".date('Y')." Yorubas Latinos. Todos los derechos reservados.
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
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>¡Bienvenido a Yorubas Latinos!</title>
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body { 
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; 
                    line-height: 1.6; 
                    color: #333;
                    background: linear-gradient(135deg, #2d5a3f 0%, #4a7c59 50%, #2d5a3f 100%);
                    min-height: 100vh;
                    padding: 40px 20px;
                }
                .email-container { 
                    max-width: 600px; 
                    margin: 0 auto; 
                    background: #ffffff;
                    border-radius: 20px;
                    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
                    overflow: hidden;
                }
                .header { 
                    background: linear-gradient(135deg, #2d5a3f 0%, #4a7c59 100%); 
                    color: white; 
                    padding: 40px 30px; 
                    text-align: center; 
                }
                .logo-circle {
                    width: 80px;
                    height: 80px;
                    background: #ffffff;
                    border-radius: 50%;
                    margin: 0 auto 20px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    border: 4px solid #f4b942;
                    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
                }
                .logo-icon {
                    font-size: 32px;
                    color: #4a7c59;
                }
                .brand-name {
                    font-size: 32px;
                    font-weight: 700;
                    color: #ffffff;
                    margin: 10px 0 5px;
                    letter-spacing: -0.5px;
                }
                .subtitle {
                    font-size: 16px;
                    color: rgba(255,255,255,0.9);
                    font-weight: 400;
                }
                .content { 
                    background: #ffffff; 
                    padding: 40px 30px; 
                }
                .greeting {
                    font-size: 28px;
                    color: #2d5a3f;
                    margin-bottom: 20px;
                    font-weight: 700;
                    text-align: center;
                }
                .message {
                    font-size: 16px;
                    color: #555;
                    margin-bottom: 20px;
                    line-height: 1.6;
                    text-align: center;
                }
                .highlight {
                    background: linear-gradient(135deg, #fff8e1 0%, #fff3c4 100%);
                    border: 2px solid #f4b942;
                    padding: 20px;
                    border-radius: 12px;
                    margin: 25px 0;
                    text-align: center;
                }
                .blessing {
                    font-size: 18px;
                    color: #2d5a3f;
                    font-weight: 600;
                    text-align: center;
                    margin: 30px 0;
                    font-style: italic;
                }
                .footer { 
                    background: #f8f9fa;
                    text-align: center; 
                    padding: 30px; 
                    color: #6c757d; 
                    font-size: 14px; 
                    border-top: 1px solid #e9ecef;
                }
                .footer-brand {
                    color: #4a7c59;
                    font-weight: 600;
                    margin-bottom: 8px;
                }
                .divider {
                    height: 3px;
                    background: linear-gradient(90deg, #4a7c59 0%, #f4b942 50%, #4a7c59 100%);
                    margin: 0;
                    border: none;
                }
            </style>
        </head>
        <body>
            <div class='email-container'>
                <div class='header'>
                    <div class='logo-circle'>
                        <div class='logo-icon'>📖</div>
                    </div>
                    <div class='brand-name'>Yorubas Latinos</div>
                    <div class='subtitle'>¡Bienvenido a la comunidad!</div>
                </div>
                
                <hr class='divider'>
                
                <div class='content'>
                    <div class='greeting'>¡Bienvenido, {$toName}!</div>
                    
                    <p class='message'>
                        Tu cuenta ha sido creada exitosamente en <strong>Yorubas Latinos</strong>.
                    </p>
                    
                    <div class='highlight'>
                        <p style='margin: 0; color: #2d5a3f; font-weight: 600;'>
                            🎉 ¡Ya puedes acceder a la plataforma!
                        </p>
                        <p style='margin: 10px 0 0; color: #555;'>
                            Comienza a gestionar tu práctica religiosa y conecta con nuestra comunidad.
                        </p>
                    </div>
                    
                    <div class='blessing'>
                        Igboru, igboya, igbo sheshé
                    </div>
                </div>
                
                <div class='footer'>
                    <div class='footer-brand'>Yorubas Latinos</div>
                    <p>Gracias por unirte a nuestra comunidad.</p>
                    <p>Si tienes alguna pregunta, no dudes en contactarnos.</p>
                    <p style='margin-top: 15px; font-size: 12px; color: #adb5bd;'>
                        © ".date('Y')." Yorubas Latinos. Todos los derechos reservados.
                    </p>
                </div>
            </div>
        </body>
        </html>
        ";

        $textContent = "
📖 YORUBAS LATINOS
==================
¡Bienvenido a la comunidad!

¡Bienvenido, {$toName}!

Tu cuenta ha sido creada exitosamente en Yorubas Latinos.

🎉 ¡Ya puedes acceder a la plataforma!
Comienza a gestionar tu práctica religiosa y conecta con nuestra comunidad.

Igboru, igboya, igbo sheshé

---
📧 Yorubas Latinos
Gracias por unirte a nuestra comunidad.
Si tienes alguna pregunta, no dudes en contactarnos.

© ".date('Y')." Yorubas Latinos. Todos los derechos reservados.
        ";

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