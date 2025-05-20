<?php
// generate_receipt_pdf.php - Creates PDF receipts for HYPEZA orders

// Require TCPDF library
require_once('vendor/autoload.php');

// Function to generate PDF receipt
function generatePdfReceipt($orderData) {
    // Create new TCPDF instance
    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8');

    // Set document information
    $pdf->SetCreator('HYPEZA');
    $pdf->SetAuthor('HYPEZA');
    $pdf->SetTitle('Order Receipt #' . $orderData['orderNumber']);
    $pdf->SetSubject('HYPEZA Order Receipt');

    // Remove header and footer
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);

    // Set margins
    $pdf->SetMargins(15, 15, 15);
    $pdf->SetAutoPageBreak(true, 15);

    // Add a page
    $pdf->AddPage();

    // Set font
    $pdf->SetFont('helvetica', '', 12);

    // Define colors
    $goldColor = array(200, 155, 60); // RGB equivalent of #C89B3C
    $blackColor = array(0, 0, 0);
    $grayColor = array(120, 120, 120);

    // Logo and header
    $pdf->SetFillColor($blackColor[0], $blackColor[1], $blackColor[2]);
    $pdf->Rect(15, 15, 180, 30, 'F');

    $pdf->SetTextColor($goldColor[0], $goldColor[1], $goldColor[2]);
    $pdf->SetFont('helvetica', 'B', 24);
    $pdf->Cell(180, 30, 'HYPEZA', 0, 1, 'C');

    // Reset text color
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('helvetica', '', 12);

    // Order information
    $pdf->Ln(10);
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(180, 10, 'ORDER RECEIPT', 0, 1, 'C');

    $pdf->Ln(5);
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(90, 10, 'Order Number:', 0, 0);
    $pdf->SetFont('helvetica', '', 12);
    $pdf->SetTextColor($goldColor[0], $goldColor[1], $goldColor[2]);
    $pdf->Cell(90, 10, $orderData['orderNumber'], 0, 1);
    $pdf->SetTextColor(0, 0, 0);

    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(90, 10, 'Date:', 0, 0);
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(90, 10, date('F j, Y'), 0, 1);

    $pdf->Ln(5);

    // Customer information
    $pdf->SetFillColor(245, 245, 245);
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(180, 10, 'Customer Information', 0, 1, '', true);
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(45, 10, 'Name:', 0, 0);
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(135, 10, $orderData['firstName'] . ' ' . $orderData['lastName'], 0, 1);

    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(45, 10, 'Email:', 0, 0);
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(135, 10, $orderData['email'], 0, 1);

    // Shipping address
    $pdf->Ln(5);
    $pdf->SetFillColor(245, 245, 245);
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(180, 10, 'Shipping Address', 0, 1, '', true);
    $pdf->SetFont('helvetica', '', 12);
    $pdf->MultiCell(180, 10, $orderData['firstName'] . ' ' . $orderData['lastName'] . "\n" .
                     $orderData['address'] . "\n" .
                     $orderData['city'] . ', ' . $orderData['postalCode'] . "\n" .
                     $orderData['country'], 0, 'L');

    // Order summary
    $pdf->Ln(5);
    $pdf->SetFillColor(245, 245, 245);
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(180, 10, 'Order Summary', 0, 1, '', true);

    // Table header
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetFillColor(230, 230, 230);
    $pdf->Cell(130, 10, 'Description', 1, 0, 'C', true);
    $pdf->Cell(50, 10, 'Amount', 1, 1, 'C', true);

    // Items
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(130, 10, 'Subtotal', 1, 0, 'L');
    $pdf->Cell(50, 10, $orderData['subtotal'], 1, 1, 'R');

    $pdf->Cell(130, 10, 'Shipping', 1, 0, 'L');
    $pdf->Cell(50, 10, $orderData['shipping'], 1, 1, 'R');

    // Total
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetTextColor($goldColor[0], $goldColor[1], $goldColor[2]);
    $pdf->Cell(130, 10, 'Total', 1, 0, 'L');
    $pdf->Cell(50, 10, $orderData['total'], 1, 1, 'R');

    // Footer
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Ln(10);
    $pdf->SetFont('helvetica', '', 10);
    $pdf->MultiCell(180, 10, "Thank you for your purchase!\nIf you have any questions, please contact our customer service at service-client@hypza.tech", 0, 'C');

    // HYPEZA signature and address
    $pdf->Ln(10);
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetTextColor($goldColor[0], $goldColor[1], $goldColor[2]);
    $pdf->Cell(180, 10, 'HYPEZA', 0, 1, 'C');
    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetTextColor($grayColor[0], $grayColor[1], $grayColor[2]);
    $pdf->Cell(180, 10, '123 Rue Example, 75000 Paris, France', 0, 1, 'C');
    $pdf->Cell(180, 10, 'www.hypza.tech', 0, 1, 'C');

    // Generate the PDF
    $pdfFilePath = 'receipts/' . $orderData['orderNumber'] . '.pdf';

    // Create receipts directory if it doesn't exist
    if (!is_dir('receipts')) {
        mkdir('receipts', 0755, true);
    }

    // Save PDF to file
    $pdf->Output($pdfFilePath, 'F');

    // Return the file path
    return $pdfFilePath;
}
