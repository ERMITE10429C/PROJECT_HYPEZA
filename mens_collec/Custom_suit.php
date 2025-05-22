
<?php
// Import PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// Only process if this is a POST request with JSON content type
if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_SERVER['CONTENT_TYPE']) &&
    strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {

    // Set content type to JSON for AJAX response
    header('Content-Type: application/json');

    try {
        // Load Composer's autoloader
        require '../vendor/autoload.php';

        // Get and decode JSON data
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON: " . json_last_error_msg());
        }

        // Validate required fields for appointment form
        $requiredFields = ['name', 'email', 'phone', 'date', 'time'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                throw new Exception("Missing required field: $field");
            }
        }

        // Create a new PHPMailer instance
        $mail = new PHPMailer(true);

        // Configure SMTP
        $mail->isSMTP();
        $mail->Host       = 'smtp.titan.email';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'team@hypza.tech';
        $mail->Password   = 'azerty@123';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        // SSL Configuration
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
                'allow_self_signed' => true
            ]
        ];

        // Set sender and recipient
        $mail->setFrom('team@hypza.tech', 'HYPEZA');
        $mail->addReplyTo('bespoke@hypeza.com', 'HYPEZA Bespoke Service');
        $mail->addAddress($data['email'], $data['name']);

        // Email content formatting
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';
        $mail->XMailer = 'HYPEZA Mailer';
        $mail->isHTML(true);
        $mail->Priority = 3;

        // Subject
        $mail->Subject = 'Your Bespoke Consultation Appointment - HYPEZA';

        // Create a unique message ID
        $mail->MessageID = '<' . time() . '.' . md5($data['email'] . $data['date']) . '@hypza.tech>';

        // Add custom headers to improve deliverability
        $unsubscribeLink = 'https://hypza.tech/unsubscribe?email=' . urlencode($data['email']) . '&token=' . md5($data['email'] . 'some-secret-key');
        $mail->addCustomHeader('List-Unsubscribe', '<' . $unsubscribeLink . '>, <mailto:unsubscribe@hypza.tech?subject=unsubscribe>');
        $mail->addCustomHeader('List-Unsubscribe-Post', 'List-Unsubscribe=One-Click');
        $mail->addCustomHeader('Precedence', 'bulk');
        $mail->addCustomHeader('X-Auto-Response-Suppress', 'OOF, DR, RN, NRN, AutoReply');
        $mail->addCustomHeader('Feedback-ID', md5($data['email'] . $data['date']) . ':HYPEZA:appointment:gmail');

        // Format date for display
        $appointmentDate = date('l, F j, Y', strtotime($data['date']));

        // Format time for display
        $hour = intval(substr($data['time'], 0, 2));
        $appointmentTime = ($hour < 12) ? $data['time'] . ' AM' : (($hour === 12) ? $data['time'] . ' PM' : ($hour - 12) . ':00 PM');

        // Gold color for branding
        $goldColor = '#C89B3C';

        // Email body HTML
        $emailBody = "
<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Your Bespoke Consultation Appointment - HYPEZA</title>
</head>
<body style='font-family: Arial, sans-serif; margin: 0; padding: 0; color: #333333; background-color: #f9f9f9;'>
    <table width='100%' cellpadding='0' cellspacing='0' border='0'>
        <tr>
            <td align='center' style='padding: 20px 0;'>
                <table width='600' cellpadding='0' cellspacing='0' border='0' style='background-color: #ffffff; border-radius: 6px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);'>
                    <!-- Header -->
                    <tr>
                        <td align='center' style='background-color: #000000; padding: 30px; border-radius: 6px 6px 0 0;'>
                            <h1 style='color: {$goldColor}; padding: 15px; margin-top: 20px; font-size: 32px; letter-spacing: 2px;'>HYPEZA</h1>
                        </td>
                    </tr>
                    
                    <!-- Main Content -->
                    <tr>
                        <td style='padding: 30px;'>
                            <p style='font-size: 16px; color: #666666;'>Dear {$data['name']},</p>
                            
                            <p style='font-size: 16px; line-height: 1.6; margin: 20px 0;'>
                                Thank you for scheduling a bespoke consultation with HYPEZA. We are delighted to confirm your appointment details:
                            </p>

                            <!-- Appointment Details -->
                            <table width='100%' cellpadding='0' cellspacing='0' border='0' style='background-color: #f8f8f8; border-radius: 5px; margin: 25px 0;'>
                                <tr>
                                    <td style='padding: 20px;'>
                                        <h3 style='color: {$goldColor}; margin: 0 0 15px; font-size: 18px;'>Appointment Details</h3>
                                        <p style='margin: 5px 0; font-size: 14px; line-height: 1.6;'>
                                            <strong>Date:</strong> {$appointmentDate}<br>
                                            <strong>Time:</strong> {$appointmentTime}<br>
                                            <strong>Location:</strong> HYPEZA Atelier, 123 Fashion Avenue, New York<br>
                                            <strong>Contact:</strong> {$data['phone']}
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Special Requests -->
                            " . (!empty($data['notes']) ? "
                            <table width='100%' cellpadding='0' cellspacing='0' border='0' style='background-color: #f8f8f8; border-radius: 5px; margin: 25px 0;'>
                                <tr>
                                    <td style='padding: 20px;'>
                                        <h3 style='color: {$goldColor}; margin: 0 0 15px; font-size: 18px;'>Your Special Requests</h3>
                                        <p style='margin: 5px 0; font-size: 14px; line-height: 1.6;'>{$data['notes']}</p>
                                    </td>
                                </tr>
                            </table>
                            " : "") . "

                            <p style='font-size: 16px; line-height: 1.6; margin: 20px 0;'>
                                During your appointment, our master tailors will:
                            </p>

                            <ul style='font-size: 14px; line-height: 1.6; color: #666666; margin: 20px 0; padding-left: 20px;'>
                                <li>Discuss your style preferences and requirements</li>
                                <li>Take precise measurements for your custom garment</li>
                                <li>Guide you through fabric selections and design options</li>
                                <li>Provide expert advice on cuts, details, and finishes</li>
                            </ul>

                            <p style='font-size: 14px; line-height: 1.6; color: #666666; margin: 25px 0;'>
                                If you need to reschedule or cancel your appointment, please contact us at least 24 hours in advance at
                                <a href='mailto:bespoke@hypeza.com' style='color: {$goldColor}; text-decoration: none;'>bespoke@hypeza.com</a>
                                or call us at +1 (555) 123-4567.
                            </p>

                            <p style='font-size: 14px; line-height: 1.6; color: #666666; margin: 25px 0;'>
                                We look forward to crafting a bespoke experience as unique as you are.
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style='background-color: #f8f8f8; padding: 20px; border-radius: 0 0 6px 6px; border-top: 1px solid #eee;'>
                            <table width='100%' cellpadding='0' cellspacing='0' border='0'>
                                <tr>
                                    <td align='center'>
                                        <p style='margin: 0 0 15px; color: #666666; font-size: 14px;'>
                                            Thank you for choosing HYPEZA<br>
                                            <strong style='color: {$goldColor};'>The HYPEZA Tailoring Team</strong>
                                        </p>
                                        <p style='margin: 15px 0 0; font-size: 12px; color: #999999;'>
                                            This email was sent to {$data['email']}.<br>
                                            If you no longer wish to receive our emails, 
                                            <a href='{$unsubscribeLink}' style='color: #666666;'>click here to unsubscribe</a>.
                                        </p>
                                        
                                        <!-- Social Links -->
                                        <p style='margin-top: 20px;'>
                                            <a href='https://facebook.com/hypeza' style='color: #666; margin: 0 10px;'>Facebook</a>
                                            <a href='https://instagram.com/hypeza' style='color: #666; margin: 0 10px;'>Instagram</a>
                                            <a href='https://hypza.tech' style='color: #666; margin: 0 10px;'>Website</a>
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                
                <!-- Physical Address -->
                <table width='600' cellpadding='0' cellspacing='0' border='0'>
                    <tr>
                        <td align='center' style='padding: 20px 0; font-size: 12px; color: #999;'>
                            HYPEZA, 123 Fashion Avenue, New York, NY 10001, USA
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
";

        $mail->Body = $emailBody;
        $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $emailBody));

        // Send email
        if ($mail->send()) {
            echo json_encode([
                'success' => true,
                'message' => 'Your appointment request has been successfully submitted.'
            ]);
        } else {
            throw new Exception("Email could not be sent. Mailer Error: " . $mail->ErrorInfo);
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }

    // Important: exit here to prevent HTML content from being sent in JSON response
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>HYPEZA - Custom Suit Experience</title>
  <link rel="stylesheet" href="Custom_suit_css.css">
  <link rel="stylesheet" href="styles.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Prata&family=Cormorant+Garamond:wght@300;400;500&display=swap">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">


</head>
<body>

<div class="loading-overlay">
  <div class="loader-container">
    <div class="loader-logo">HYPEZA</div>
    <div class="loader-bar">
      <div class="loader-progress"></div>
    </div>
    <div class="loader-text">Please wait...</div>
  </div>
</div>

<!-- Header -->
  <header>
    <div class="header">
      <div class="middle-section">
        <p class="product">
          <a href="mens_collec.html" class="product-link">Products</a>
        </p>
        <p style="color: RGB(200, 155, 60);">|</p>
        <p class="contact">
          <a href="#contact" class="contact-link">Contact</a>
        </p>
      </div>
      <div class="right-section">
        <a href="">
          <img src="../svgs/favorite.svg" class="user" alt="favorite">
        </a>
        <p style="color: RGB(200, 155, 60);">|</p>
        <a href="">
          <img src="../svgs/panier.svg" class="panier" alt="Panier">
        </a>
        <p style="color: RGB(200, 155, 60);">|</p>
        <a href="../espace_client.php/">
          <img src="../svgs/profil_user.svg" class="user" alt="Profil">
        </a>
      </div>
    </div>
  </header>

  <!-- Hero Section -->
  <section class="custom-hero">
    <div class="custom-hero-content">
      <h1>Craft Your Legacy</h1>
      <p>Experience the art of bespoke tailoring with HYPEZA's custom suit service.</p>
    </div>
  </section>

  <!-- Introduction Section -->
  <section class="custom-intro">
    <div class="container">
      <div class="section-title">
        <h2>The HYPEZA Custom Experience</h2>
      </div>
      <div class="custom-intro-content">
        <div class="custom-intro-text">
          <p>At HYPEZA, we believe that true luxury lies in personalization. Our bespoke suit service combines centuries-old craftsmanship with modern precision to create garments that are uniquely yours.</p>
          <p>Every custom suit is meticulously handcrafted by our master tailors using the finest materials sourced from renowned mills across Italy and the United Kingdom.</p>
        </div>
        <div class="custom-intro-image">
          <img src="https://images.unsplash.com/photo-1507679799987-c73779587ccf?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=80" alt="Tailor measuring a client">
        </div>
      </div>
    </div>
  </section>

  <!-- Process Steps -->
  <section class="custom-process">
    <div class="container">
      <div class="section-title">
        <h2>Our Bespoke Process</h2>
      </div>
      <div class="process-steps">
        <div class="process-step">
          <div class="step-number">01</div>
          <h3>Consultation</h3>
          <p>Meet with our style consultant to discuss your preferences, lifestyle needs, and vision for your custom suit.</p>
        </div>
        <div class="process-step">
          <div class="step-number">02</div>
          <h3>Fabric Selection</h3>
          <p>Choose from over 3,000 premium fabrics from renowned mills like Loro Piana, Dormeuil, and Holland & Sherry.</p>
        </div>
        <div class="process-step">
          <div class="step-number">03</div>
          <h3>Measurements</h3>
          <p>Our master tailor will take over 20 precise measurements to ensure the perfect fit for your physique.</p>
        </div>
        <div class="process-step">
          <div class="step-number">04</div>
          <h3>First Fitting</h3>
          <p>Try on the initial construction of your suit to make adjustments and refinements.</p>
        </div>
        <div class="process-step">
          <div class="step-number">05</div>
          <h3>Final Delivery</h3>
          <p>Receive your handcrafted garment, perfectly tailored to your exact specifications.</p>
        </div>
      </div>
    </div>
  </section>

<!-- Customization Options -->
<section class="customization-options">
  <div class="container">
    <div class="section-title">
      <h2>Design Your Perfect Suit</h2>
    </div>

    <div class="options-container">
      <div class="option-category">
        <h3>Silhouette</h3>

        <div class="option-items">
          <div class="option-item" data-category="fit" data-value="classic">
            <img src="images/suits/default-suit.webp"  alt="Classic Fit">
            <h4>Classic Suit</h4>
          </div>

          <div class="option-item" data-category="fit" data-value="slim">
            <img src="images/suits/slim.webp"  alt="Slim Fit">
            <h4>Tuxedo</h4>
          </div>

        </div>
      </div>



      <div class="option-category">
        <h3>Button Configuration</h3>
        
        <div class="option-items">

          <div class="option-item" data-category="buttons" data-value="two">
            <img src="images/suits/2_buttons.webp" alt="Two Button">
            <h4>Two Button</h4>
          </div>

          <div class="option-item" data-category="buttons" data-value="three">
            <img src="images/suits/3_buttons.webp" alt="Three Button">
            <h4>Three Button</h4>
          </div>

        </div>

      </div>
    </div>
  </div>
</section>

<!-- Premium Fabrics -->
<section class="premium-fabrics">
  <div class="container">
    <div class="section-title">
      <h2>Exceptional Fabrics</h2>
    </div>
    <div class="fabrics-showcase">
      <div class="fabric-category">
        <h3>Superfine Wool</h3>
        <div class="fabric-swatches">
          <div class="fabric-swatch" data-fabric="wool" data-color="navy" style="background-color: #1A2B3C;"></div>
        </div>
        <p>Super 150s and Super 180s wool from the finest mills in Italy and the UK.</p>
      </div>

      <div class="fabric-category">
        <h3>Cashmere Blends</h3>
        <div class="fabric-swatches">
          <div class="fabric-swatch" data-fabric="cashmere" data-color="brown" style="background-color: #5D4037;"></div>
        </div>
        <p>Luxurious cashmere blends for unparalleled comfort and drape.</p>
      </div>

      <div class="fabric-category">
        <h3>Linen & Cotton</h3>
        <div class="fabric-swatches">
          <div class="fabric-swatch" data-fabric="linen" data-color="ivory" style="background-color: #ECEFF1;"></div>
        </div>
        <p>Perfect for warmer climates and seasonal versatility.</p>
      </div>
    </div>
  </div>
</section>

  <!-- Appointment Form -->
  <section class="appointment-section">
    <div class="container">
      <div class="section-title">
        <h2>Schedule Your Bespoke Consultation</h2>
      </div>
      <div class="appointment-container">
        <div class="appointment-info">
          <p>Begin your custom suit journey with a personal consultation at our atelier. Our master tailors will guide you through each step of the process.</p>
          <p>Appointments are available Monday through Saturday, 10:00 AM to 7:00 PM.</p>
          <p>For urgent inquiries, please contact us directly:</p>
          <p><i class="fas fa-phone"></i> +1 (555) 123-4567</p>
          <p><i class="fas fa-envelope"></i> bespoke@hypeza.com</p>
        </div>

        <form class="appointment-form">
          <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" required>
          </div>

          <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" required>
          </div>

          <div class="form-group">
            <label for="phone">Phone Number</label>
            <input type="tel" id="phone" name="phone" required>
          </div>

          <div class="form-group">
            <label for="date">Preferred Date</label>
            <input type="date" id="date" name="date" required>
          </div>

          <div class="form-group">
            <label for="time">Preferred Time</label>
            <select id="time" name="time" required>
              <option value="">Select a time</option>
              <option value="10:00">10:00 AM</option>
              <option value="11:00">11:00 AM</option>
              <option value="12:00">12:00 PM</option>
              <option value="13:00">1:00 PM</option>
              <option value="14:00">2:00 PM</option>
              <option value="15:00">3:00 PM</option>
              <option value="16:00">4:00 PM</option>
              <option value="17:00">5:00 PM</option>
              <option value="18:00">6:00 PM</option>
            </select>
          </div>

          <div class="form-group">
            <label for="notes">Special Requests</label>
            <textarea id="notes" name="notes" rows="4"></textarea>
          </div>

          <button type="submit" class="submit-btn shimmer">Request Appointment</button>
        </form>
      </div>
    </div>
  </section>

  <!-- Testimonials -->
  <section class="custom-testimonials">
    <div class="container">
      <div class="section-title">
        <h2>Client Experiences</h2>
      </div>
      <div class="testimonials-slider">
        <div class="testimonial-slide">
          <div class="testimonial-content">
            <p>"The bespoke experience at HYPEZA was transformative. My wedding suit was perfectly crafted to my specifications, and the attention to detail was remarkable."</p>
            <div class="testimonial-author">
              <img src="https://randomuser.me/api/portraits/men/32.webp" alt="Jonathan Pierce">
              <div>
                <h4>Jonathan Pierce</h4>
                <p>Finance Executive</p>
              </div>
            </div>
          </div>
        </div>

        <div class="testimonial-slide">
          <div class="testimonial-content">
            <p>"As someone who has always struggled to find suits that fit properly, HYPEZA's custom service has been revolutionary. The quality and fit are unmatched."</p>
            <div class="testimonial-author">
              <img src="https://randomuser.me/api/portraits/men/75.jpg" alt="Alexander Morgan">
              <div>
                <h4>Alexander Morgan</h4>
                <p>Architect</p>
              </div>
            </div>
          </div>
        </div>

        <div class="testimonial-slide">
          <div class="testimonial-content">
            <p>"The expertise of the tailors and the quality of fabrics available at HYPEZA are truly exceptional. My custom suit has become the cornerstone of my professional wardrobe."</p>
            <div class="testimonial-author">
              <img src="https://randomuser.me/api/portraits/men/54.jpg" alt="David Chen">
              <div>
                <h4>David Chen</h4>
                <p>Tech Entrepreneur</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- FAQ Section -->
  <section class="faq-section">
    <div class="container">
      <div class="section-title">
        <h2>Frequently Asked Questions</h2>
      </div>
      <div class="faq-container">
        <div class="faq-item">
          <div class="faq-question">
            <h3>How long does the custom suit process take?</h3>
            <span class="faq-toggle"><i class="fas fa-plus"></i></span>
          </div>
          <div class="faq-answer">
            <p>Typically, our bespoke suit process takes 6-8 weeks from initial consultation to final delivery. This includes the initial consultation, measurements, first fitting, and final adjustments.</p>
          </div>
        </div>

        <div class="faq-item">
          <div class="faq-question">
            <h3>What is the price range for a custom suit?</h3>
            <span class="faq-toggle"><i class="fas fa-plus"></i></span>
          </div>
          <div class="faq-answer">
            <p>Our bespoke suits start at $2,500 and can range up to $15,000+ depending on the fabric selection, customization options, and complexity of the design.</p>
          </div>
        </div>

        <div class="faq-item">
          <div class="faq-question">
            <h3>Do you keep my measurements on file?</h3>
            <span class="faq-toggle"><i class="fas fa-plus"></i></span>
          </div>
          <div class="faq-answer">
            <p>Yes, we maintain a secure file with your measurements and preferences, making future orders more streamlined. We recommend updating measurements every 12-18 months or after significant weight changes.</p>
          </div>
        </div>

        <div class="faq-item">
          <div class="faq-question">
            <h3>Can I bring in a design or photo for inspiration?</h3>
            <span class="faq-toggle"><i class="fas fa-plus"></i></span>
          </div>
          <div class="faq-answer">
            <p>Absolutely. We encourage clients to share inspiration images or specific design elements they'd like to incorporate. Our design consultants can help interpret these references into your custom garment.</p>
          </div>
        </div>

        <div class="faq-item">
          <div class="faq-question">
            <h3>Do you offer virtual consultations?</h3>
            <span class="faq-toggle"><i class="fas fa-plus"></i></span>
          </div>
          <div class="faq-answer">
            <p>Yes, we offer virtual consultations for initial discussions and fabric selections. However, for precise measurements and fittings, we recommend in-person appointments at our atelier.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="footer">
    <div class="footer-container">
      <div class="footer-top">
        <div class="footer-logo">
          <img src="../svgs/logo.svg" alt="HYPEZA Logo">
          <div class="tagline">Luxury Men's Fashion. Timeless. Elegant. Modern.</div>
        </div>
        <div class="footer-navigation">
          <div class="footer-col">
            <h3>Collections</h3>
            <ul>
              <li><a href="mens_collec.html#categories">Suits</a></li>
              <li><a href="mens_collec.html#categories">Outerwear</a></li>
              <li><a href="mens_collec.html#categories">Shirts</a></li>
              <li><a href="mens_collec.html#categories">Trousers</a></li>
            </ul>
          </div>
          <div class="footer-col">
            <h3>Customer Service</h3>
            <ul>
              <li><a href="#">Contact Us</a></li>
              <li><a href="#">Shipping & Returns</a></li>
              <li><a href="#">FAQ</a></li>
              <li><a href="#">Order Tracking</a></li>
            </ul>
          </div>
          <div class="footer-col">
            <h3>About HYPEZA</h3>
            <ul>
              <li><a href="#">Our Story</a></li>
              <li><a href="#">Sustainability</a></li>
              <li><a href="#">Careers</a></li>
              <li><a href="#">Press</a></li>
            </ul>
          </div>
          <div class="footer-col">
            <h3>Legal</h3>
            <ul>
              <li><a href="#">Privacy Policy</a></li>
              <li><a href="#">Terms & Conditions</a></li>
              <li><a href="#">Legal Notice</a></li>
              <li><a href="#">Site Map</a></li>
            </ul>
          </div>
        </div>
      </div>
      <div class="footer-middle">
        <div class="social-links">
          <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
          <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
          <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
          <a href="#" aria-label="Pinterest"><i class="fab fa-pinterest-p"></i></a>
        </div>
      </div>
      <div class="footer-bottom">
        <div class="footer-info">
          <p>&copy; 2024 HYPEZA. All rights reserved.</p>
        </div>
        <div class="footer-links">
          <a href="#">Legal Notice</a>
          <a href="#">Privacy Policy</a>
          <a href="#">Terms & Conditions</a>
          <a href="#">Site Map</a>
        </div>
      </div>
    </div>
  </footer>

  <!-- Shopping Cart -->
  <div class="cart">
    <div class="cart-header">
      <h3>Your Shopping Bag</h3>
      <button class="cart-close">
        <i class="fas fa-times"></i>
      </button>
    </div>
    <div class="cart-items">
      <!-- Cart items will be added here dynamically -->
    </div>
    <div class="cart-total">
      <span>Total:</span>
      <span class="total-amount">$0.00</span>
    </div>
    <a href="../checkout_1.php">
      <button class="checkout-btn shimmer">Checkout</button>
    </a>
  </div>



<script>
  document.addEventListener("DOMContentLoaded", function() {
    const loadingOverlay = document.querySelector('.loading-overlay');
    const loaderProgress = document.querySelector('.loader-progress');

    // Animate progress bar from 0 to 100% over 900ms
    let startTime = performance.now();
    let duration = 900; // slightly less than 1s to allow for fade out

    function animateProgress(currentTime) {
      let elapsed = currentTime - startTime;
      let progress = Math.min(elapsed / duration * 100, 100);

      loaderProgress.style.width = progress + '%';

      if (progress < 100) {
        requestAnimationFrame(animateProgress);
      } else {
        // Start fade out after progress reaches 100%
        setTimeout(() => {
          loadingOverlay.style.opacity = '0';
          setTimeout(() => {
            loadingOverlay.style.display = 'none';
          }, 100);
        }, 0);
      }
    }

    requestAnimationFrame(animateProgress);
  });



  document.querySelector('.appointment-form').addEventListener('submit', async function(e) {
    e.preventDefault();

    // Show loading state
    const submitBtn = this.querySelector('.submit-btn');
    const originalBtnText = submitBtn.textContent;
    submitBtn.textContent = 'Sending...';
    submitBtn.disabled = true;

    const formData = {
      name: document.getElementById('name').value,
      email: document.getElementById('email').value,
      phone: document.getElementById('phone').value,
      date: document.getElementById('date').value,
      time: document.getElementById('time').value,
      notes: document.getElementById('notes').value || ''
    };

    // Log the data being sent
    console.log('Sending appointment data:', formData);

    try {
      // Send to current page instead of separate PHP file
      const response = await fetch(window.location.href, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(formData),
        signal: AbortSignal.timeout(15000) // 15 second timeout
      });

      console.log('Response status:', response.status);
      console.log('Response headers:', Object.fromEntries(response.headers));

      const responseText = await response.text();
      console.log('Raw server response:', responseText);

      let result;
      try {
        result = JSON.parse(responseText);
        console.log('Parsed response:', result);
      } catch (e) {
        console.error('Invalid JSON response:', e);
        console.error('Raw response content:', responseText);
        throw new Error('Server returned invalid JSON. See console for details.');
      }

      if (result.success) {
        // Create success notification
        const successMessage = document.createElement('div');
        successMessage.className = 'success-notification';
        successMessage.innerHTML = `
          <div class="success-icon"><i class="fas fa-check-circle"></i></div>
          <h3>Appointment Scheduled!</h3>
          <p>Your bespoke consultation request has been received. A confirmation email has been sent to ${formData.email}</p>
        `;
        document.querySelector('.appointment-container').appendChild(successMessage);

        // Hide the form
        this.style.display = 'none';

        // Scroll to the success message
        successMessage.scrollIntoView({ behavior: 'smooth' });
      } else {
        throw new Error(result.message || 'Unknown error occurred');
      }
    } catch (error) {
      console.error('Detailed error:', error);

      // Create more descriptive error message for user
      let errorMessage = 'An error occurred while processing your request.';

      if (error.name === 'AbortError') {
        errorMessage = 'Request timed out. Please try again or contact us directly.';
      } else if (error.message) {
        errorMessage = `Error: ${error.message}`;
      }

      alert(errorMessage);
    } finally {
      // Restore button state
      submitBtn.textContent = originalBtnText;
      submitBtn.disabled = false;
    }
  });


</script>




<script src="Custom_suit.js"></script>


    <script src="/script.js/"></script>


</body>
</html>