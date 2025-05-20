<?php
// Import required TCPDF library
require_once 'vendor/autoload.php';

use TCPDF;

class ReceiptGenerator {
    private $pdf;
    private $orderData;
    private $goldColor = [200, 155, 60];

    public function __construct($orderData) {
        $this->orderData = $orderData;

        // Initialize PDF
        $this->pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

        // Set document information
        $this->pdf->SetCreator('HYPEZA');
        $this->pdf->SetAuthor('HYPEZA Shop');
        $this->pdf->SetTitle('Order Receipt #' . $orderData['orderNumber']);
        $this->pdf->SetSubject('HYPEZA Order Receipt');

        // Remove header and footer
        $this->pdf->setPrintHeader(false);
        $this->pdf->setPrintFooter(false);

        // Set margins
        $this->pdf->SetMargins(15, 15, 15);

        // Set auto page breaks
        $this->pdf->SetAutoPageBreak(true, 15);

        // Set font
        $this->pdf->SetFont('helvetica', '', 10);

        // Add a page
        $this->pdf->AddPage();
    }

    public function generate() {
        $this->addHeader();
        $this->addBillingInfo();
        $this->addOrderSummary();
        $this->addFooter();

        return $this->pdf;
    }

    private function addHeader() {
        // Logo & Title
        $this->pdf->SetFillColor(0, 0, 0);
        $this->pdf->Rect(15, 15, 180, 30, 'F');

        $this->pdf->SetY(25);
        $this->pdf->SetTextColor($this->goldColor[0], $this->goldColor[1], $this->goldColor[2]);
        $this->pdf->SetFont('helvetica', 'B', 24);
        $this->pdf->Cell(180, 10, 'HYPEZA', 0, 1, 'C');

        $this->pdf->SetY(50);
        $this->pdf->SetTextColor(0, 0, 0);
        $this->pdf->SetFont('helvetica', 'B', 16);
        $this->pdf->Cell(180, 10, 'ORDER RECEIPT', 0, 1, 'C');

        $this->pdf->SetFont('helvetica', '', 12);
        $this->pdf->Cell(180, 10, 'Order #: ' . $this->orderData['orderNumber'], 0, 1, 'C');
        $this->pdf->Cell(180, 10, 'Date: ' . date('F j, Y'), 0, 1, 'C');

        $this->pdf->Ln(10);
    }

    private function addBillingInfo() {
        $this->pdf->SetFont('helvetica', 'B', 12);
        $this->pdf->SetFillColor(240, 240, 240);
        $this->pdf->Cell(180, 10, 'Customer & Shipping Information', 0, 1, 'L', true);

        $this->pdf->Ln(5);

        $this->pdf->SetFont('helvetica', '', 10);

        // Customer info - left column
        $this->pdf->SetX(15);
        $this->pdf->Cell(90, 6, 'Name: ' . $this->orderData['firstName'] . ' ' . $this->orderData['lastName'], 0, 1);
        $this->pdf->Cell(90, 6, 'Email: ' . $this->orderData['email'], 0, 1);

        // Shipping info - right column
        $this->pdf->SetXY(105, $this->pdf->GetY() - 12);
        $this->pdf->Cell(90, 6, 'Shipping Address:', 0, 1);
        $this->pdf->SetX(105);
        $this->pdf->MultiCell(90, 6, $this->orderData['address'] . "\n" .
                               $this->orderData['city'] . ', ' . $this->orderData['postalCode'] . "\n" .
                               $this->orderData['country'], 0, 'L');

        $this->pdf->Ln(10);
    }

    private function addOrderSummary() {
        $this->pdf->SetFont('helvetica', 'B', 12);
        $this->pdf->SetFillColor(240, 240, 240);
        $this->pdf->Cell(180, 10, 'Order Summary', 0, 1, 'L', true);

        $this->pdf->Ln(5);

        // Table header
        $this->pdf->SetFont('helvetica', 'B', 10);
        $this->pdf->Cell(90, 10, 'Description', 1, 0, 'L');
        $this->pdf->Cell(30, 10, 'Quantity', 1, 0, 'C');
        $this->pdf->Cell(30, 10, 'Unit Price', 1, 0, 'R');
        $this->pdf->Cell(30, 10, 'Amount', 1, 1, 'R');

        // If we had item details, we would loop through them here
        // But since we only have totals, we'll add a placeholder row
        $this->pdf->SetFont('helvetica', '', 10);
        $this->pdf->Cell(90, 10, 'Order Items', 1, 0, 'L');
        $this->pdf->Cell(30, 10, '1', 1, 0, 'C');
        $this->pdf->Cell(30, 10, $this->orderData['subtotal'], 1, 0, 'R');
        $this->pdf->Cell(30, 10, $this->orderData['subtotal'], 1, 1, 'R');

        // Totals
        $this->pdf->SetFont('helvetica', 'B', 10);
        $this->pdf->Cell(150, 10, 'Subtotal:', 1, 0, 'R');
        $this->pdf->Cell(30, 10, $this->orderData['subtotal'], 1, 1, 'R');

        $this->pdf->Cell(150, 10, 'Shipping:', 1, 0, 'R');
        $this->pdf->Cell(30, 10, $this->orderData['shipping'], 1, 1, 'R');

        $this->pdf->SetTextColor($this->goldColor[0], $this->goldColor[1], $this->goldColor[2]);
        $this->pdf->Cell(150, 10, 'Total:', 1, 0, 'R');
        $this->pdf->Cell(30, 10, $this->orderData['total'], 1, 1, 'R');
        $this->pdf->SetTextColor(0, 0, 0);

        $this->pdf->Ln(10);
    }

    private function addFooter() {
        $this->pdf->SetY(-50);
        $this->pdf->SetFont('helvetica', 'I', 8);
        $this->pdf->Cell(180, 10, 'Thank you for shopping with HYPEZA!', 0, 1, 'C');
        $this->pdf->Cell(180, 10, 'For any questions regarding your order, please contact service-client@hypza.tech', 0, 1, 'C');
        $this->pdf->Cell(180, 10, 'HYPEZA - 123 Rue Example, 75000 Paris, France', 0, 1, 'C');
    }

    // Save PDF to file and return the filename
    public function saveToFile($directory = 'receipts/') {
        // Make sure the directory exists
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filename = $directory . 'receipt_' . $this->orderData['orderNumber'] . '.pdf';
        $this->pdf->Output($filename, 'F');

        return $filename;
    }

    // Return PDF as string for email attachment
    public function getOutputString() {
        return $this->pdf->Output('', 'S');
    }
}

// If directly accessed, check if we have order data
if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_SERVER['CONTENT_TYPE']) &&
    strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {

    // Get and decode JSON data
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (json_last_error() === JSON_ERROR_NONE) {
        // Initialize receipt generator
        $generator = new ReceiptGenerator($data);
        $generator->generate();

        // Decide whether to output PDF directly or save it
        if (isset($_GET['output']) && $_GET['output'] === 'download') {
            // Output PDF directly for download
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="receipt_' . $data['orderNumber'] . '.pdf"');
            echo $generator->getOutputString();
        } else {
            // Save PDF and return the filename
            $filename = $generator->saveToFile();

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'filename' => $filename,
                'url' => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") .
                        "://$_SERVER[HTTP_HOST]" . dirname($_SERVER['PHP_SELF']) . "/$filename"
            ]);
        }
    } else {
        // Invalid JSON
        header('Content-Type: application/json');
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid JSON data: ' . json_last_error_msg()
        ]);
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['orderNumber'])) {
    // For direct access with order number parameter (for download links)
    // In a real application, you'd fetch order details from the database here
    // For this example, we're just showing a demo

    $demoData = [
        'orderNumber' => $_GET['orderNumber'],
        'firstName' => 'Demo',
        'lastName' => 'User',
        'email' => 'demo@example.com',
        'address' => '123 Demo Street',
        'city' => 'Paris',
        'postalCode' => '75000',
        'country' => 'France',
        'subtotal' => '$99.99',
        'shipping' => '$9.99',
        'total' => '$109.98'
    ];

    $generator = new ReceiptGenerator($demoData);
    $generator->generate();

    // Output PDF for download
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="receipt_' . $_GET['orderNumber'] . '.pdf"');
    echo $generator->getOutputString();
}
?>