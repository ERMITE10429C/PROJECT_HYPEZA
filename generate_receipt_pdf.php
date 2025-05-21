<?php
// PDF Receipt Generator using DomPDF

require 'vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Generate a PDF receipt from order data
 *
 * @param array $orderData Array containing order information
 * @return string The PDF content as a string
 */
function generateReceiptPDF($orderData) {
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true);
    $options->set('defaultFont', 'Helvetica');

    $dompdf = new Dompdf($options);

    // Couleurs premium
    $primaryColor = '#1B1B1B';
    $accentColor = '#C89B3C';
    $borderColor = '#E5E5E5';

    $html = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>Reçu #{$orderData['orderNumber']}</title>
        <style>
            @page {
                margin: 0;
                padding: 0;
            }
            body {
                font-family: Helvetica, Arial, sans-serif;
                margin: 0;
                padding: 40px;
                color: $primaryColor;
                line-height: 1.6;
                background: linear-gradient(45deg, #FFFFFF 0%, #F9F9F9 100%);
            }
            .receipt {
                max-width: 800px;
                margin: 0 auto;
                border: 1px solid $borderColor;
                padding: 40px;
                box-shadow: 0 4px 24px rgba(0,0,0,0.05);
                background: white;
                border-radius: 12px;
            }
            .header {
                text-align: center;
                border-bottom: 3px solid $accentColor;
                padding-bottom: 20px;
                margin-bottom: 30px;
                position: relative;
            }
            .logo {
                font-size: 32px;
                font-weight: 800;
                color: $primaryColor;
                letter-spacing: 4px;
                margin-bottom: 15px;
            }
            .receipt-title {
                font-size: 18px;
                color: $accentColor;
                text-transform: uppercase;
                letter-spacing: 2px;
                margin: 15px 0;
            }
            .order-number {
                font-size: 16px;
                margin: 15px 0;
                color: $primaryColor;
            }
            .receipt-date {
                color: #666;
                font-size: 14px;
            }
            .section {
                margin: 30px 0;
                padding: 20px;
                background: #FAFAFA;
                border-radius: 8px;
            }
            .section-title {
                font-size: 18px;
                font-weight: bold;
                color: $primaryColor;
                border-bottom: 2px solid $accentColor;
                padding-bottom: 10px;
                margin-bottom: 20px;
            }
            .customer-details {
                display: flex;
                justify-content: space-between;
                gap: 40px;
            }
            .detail-block {
                flex: 1;
                padding: 20px;
                background: white;
                border-radius: 8px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            }
            .detail-block p {
                margin: 5px 0;
            }
            .order-summary table {
                width: 100%;
                border-collapse: separate;
                border-spacing: 0 8px;
            }
            .order-summary th {
                padding: 15px;
                text-align: left;
                background: $accentColor;
                color: white;
                font-weight: bold;
                text-transform: uppercase;
                font-size: 14px;
            }
            .order-summary td {
                padding: 15px;
                background: white;
                border-bottom: 1px solid $borderColor;
            }
            .order-summary th:first-child,
            .order-summary td:first-child {
                border-radius: 8px 0 0 8px;
            }
            .order-summary th:last-child,
            .order-summary td:last-child {
                border-radius: 0 8px 8px 0;
                text-align: right;
            }
            .total-row {
                font-weight: bold;
                font-size: 16px;
            }
            .total-row td {
                background: $primaryColor;
                color: white;
            }
            .footer {
                margin-top: 40px;
                text-align: center;
                color: #666;
                font-size: 13px;
                border-top: 1px solid $borderColor;
                padding-top: 30px;
            }
            .footer p {
                margin: 5px 0;
            }
            .contact-info {
                margin-top: 20px;
                padding: 15px;
                background: #F5F5F5;
                border-radius: 8px;
                font-size: 12px;
            }
            .watermark {
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%) rotate(-45deg);
                font-size: 150px;
                color: rgba(200, 155, 60, 0.03);
                z-index: 0;
                pointer-events: none;
            }
        </style>
    </head>
    <body>
        <div class='receipt'>
            <div class='watermark'>HYPEZA</div>
            <div class='header'>
                <div class='logo'>HYPEZA</div>
                <div class='receipt-title'>Confirmation d'Achat</div>
                <div class='order-number'>Commande N° {$orderData['orderNumber']}</div>
                <div class='receipt-date'>" . date('d/m/Y') . "</div>
            </div>
            
            <div class='section'>
                <div class='section-title'>Informations Client</div>
                <div class='customer-details'>
                    <div class='detail-block'>
                        <p><strong>Facturation</strong></p>
                        <p>{$orderData['firstName']} {$orderData['lastName']}</p>
                        <p>{$orderData['email']}</p>
                    </div>
                    <div class='detail-block'>
                        <p><strong>Livraison</strong></p>
                        <p>{$orderData['firstName']} {$orderData['lastName']}</p>
                        <p>{$orderData['address']}</p>
                        <p>{$orderData['city']}, {$orderData['postalCode']}</p>
                        <p>{$orderData['country']}</p>
                    </div>
                </div>
            </div>
            
            <div class='section order-summary'>
                <div class='section-title'>Détails de la Commande</div>
                <table>
                    <tr>
                        <th>Description</th>
                        <th>Montant</th>
                    </tr>
                    <tr>
                        <td>Sous-total</td>
                        <td>{$orderData['subtotal']} €</td>
                    </tr>
                    <tr>
                        <td>Frais de livraison</td>
                        <td>{$orderData['shipping']} €</td>
                    </tr>
                    <tr class='total-row'>
                        <td>Total TTC</td>
                        <td>{$orderData['total']} €</td>
                    </tr>
                </table>
            </div>
            
            <div class='footer'>
                <p>Nous vous remercions pour votre confiance</p>
                <p><strong>HYPEZA</strong> - L'Excellence du Luxe</p>
                <div class='contact-info'>
                    <p>123 Rue Example, 75000 Paris, France</p>
                    <p>Service Client: service-client@hypza.tech | +33 (0)1 23 45 67 89</p>
                    <p>SIRET: XX XXX XXX XXXXX | TVA: FRXXXXXXXXX</p>
                </div>
            </div>
        </div>
    </body>
    </html>";

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    return $dompdf->output();
}
