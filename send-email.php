<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

// Load dependencies
require_once __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;

try {
    // Load environment variables
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->load();
    
    // Get and sanitize form data
    $name = isset($_POST['name']) ? trim(strip_tags($_POST['name'])) : '';
    $email = isset($_POST['email']) ? trim(strip_tags($_POST['email'])) : '';
    $company = isset($_POST['company']) ? trim(strip_tags($_POST['company'])) : '';
    $message = isset($_POST['message']) ? trim(strip_tags($_POST['message'])) : '';
    
    // Validate required fields
    if (empty($name)) {
        throw new Exception('Le nom est requis');
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Une adresse email valide est requise');
    }
    
    // Create PHPMailer instance
    $mail = new PHPMailer(true);
    
    // Server settings
    $mail->isSMTP();
    $mail->Host = $_ENV['BREVO_SMTP_HOST'];
    $mail->SMTPAuth = true;
    $mail->Username = $_ENV['BREVO_SMTP_USERNAME'];
    $mail->Password = $_ENV['BREVO_SMTP_PASSWORD'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = $_ENV['BREVO_SMTP_PORT'];
    $mail->CharSet = 'UTF-8';
    
    // Recipients
    $mail->setFrom($_ENV['BREVO_FROM_EMAIL'], $_ENV['BREVO_FROM_NAME']);
    $mail->addAddress($_ENV['CONTACT_EMAIL']);
    $mail->addReplyTo($email, $name);
    
    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Nouveau message depuis Itinera - ' . $name;
    
    // Email body
    $htmlBody = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #2d5016; color: white; padding: 20px; text-align: center; }
            .content { background: #f9f9f9; padding: 20px; margin-top: 20px; }
            .field { margin-bottom: 15px; }
            .label { font-weight: bold; color: #2d5016; }
            .footer { margin-top: 20px; padding-top: 20px; border-top: 1px solid #ddd; font-size: 12px; color: #666; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>Nouveau contact depuis Itinera</h2>
            </div>
            <div class='content'>
                <div class='field'>
                    <div class='label'>Nom :</div>
                    <div>" . htmlspecialchars($name) . "</div>
                </div>
                <div class='field'>
                    <div class='label'>Email :</div>
                    <div><a href='mailto:" . htmlspecialchars($email) . "'>" . htmlspecialchars($email) . "</a></div>
                </div>";
    
    if (!empty($company)) {
        $htmlBody .= "
                <div class='field'>
                    <div class='label'>Entreprise :</div>
                    <div>" . htmlspecialchars($company) . "</div>
                </div>";
    }
    
    if (!empty($message)) {
        $htmlBody .= "
                <div class='field'>
                    <div class='label'>Message :</div>
                    <div>" . nl2br(htmlspecialchars($message)) . "</div>
                </div>";
    }
    
    $htmlBody .= "
            </div>
            <div class='footer'>
                <p>Ce message a été envoyé depuis le formulaire de contact d'Itinera.</p>
                <p>Date : " . date('d/m/Y à H:i:s') . "</p>
            </div>
        </div>
    </body>
    </html>";
    
    $mail->Body = $htmlBody;
    
    // Plain text version
    $textBody = "Nouveau contact depuis Itinera\n\n";
    $textBody .= "Nom : " . $name . "\n";
    $textBody .= "Email : " . $email . "\n";
    if (!empty($company)) {
        $textBody .= "Entreprise : " . $company . "\n";
    }
    if (!empty($message)) {
        $textBody .= "\nMessage :\n" . $message . "\n";
    }
    $textBody .= "\n---\n";
    $textBody .= "Date : " . date('d/m/Y à H:i:s') . "\n";
    
    $mail->AltBody = $textBody;
    
    // Send email
    $mail->send();
    
    echo json_encode([
        'success' => true,
        'message' => 'Merci pour votre message ! Nous vous contacterons bientôt.'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Une erreur est survenue lors de l\'envoi du message. Veuillez réessayer.',
        'error' => $e->getMessage()
    ]);
}
