<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PAM JAYA - Penyedia Air Minum Jakarta</title>
    <!-- Link ke Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="website icon" type="jpeg" href="image/logo.png">
    <style>
    :root {
        --primary-color: #003366;
        --secondary-color: #0099cc;
        --accent-color: #ff6b00;
        --light-color: #f8f9fa;
        --dark-color: #1c1c1c;
        --text-color: #333;
        --text-light: #b9b9b9;
    }
    
    /* Custom CSS */
    body {
        font-family: 'Poppins', sans-serif;
        background-color: var(--light-color);
        margin: 0;
        padding: 0;
        line-height: 1.6;
        color: var(--text-color);
        overflow-x: hidden;
    }

    /* Preloader */
    .preloader {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: var(--primary-color);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        transition: opacity 0.5s ease;
    }

    .preloader.fade-out {
        opacity: 0;
    }

    .loader {
        width: 70px;
        height: 70px;
        border: 5px solid rgba(255,255,255,0.3);
        border-radius: 50%;
        border-top-color: white;
        animation: spin 1s ease-in-out infinite;
        position: relative;
    }

    .loader::after {
        content: '';
        position: absolute;
        top: -10px;
        left: -10px;
        right: -10px;
        bottom: -10px;
        border: 3px solid rgba(255,255,255,0.1);
        border-radius: 50%;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    /* Logo Container */
    .logo-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 40px;
        background: linear-gradient(135deg, var(--primary-color) 0%, #005792 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        position: relative;
        z-index: 1000;
    }

    .logo {
        width: 80px;
        height: 80px;
        transition: all 0.3s ease;
        filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));
    }

    .logo:hover {
        transform: scale(1.1) rotate(5deg);
        filter: drop-shadow(0 4px 8px rgba(0,0,0,0.3));
    }

    .judul {
        font-size: 28px;
        font-weight: 700;
        text-align: center;
        letter-spacing: 1px;
        animation: fadeIn 1.2s ease-in-out;
        text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.3);
        background: linear-gradient(to right, #ffffff, #d4eaff);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        padding: 0 20px;
        position: relative;
    }

    .judul::after {
        content: '';
        position: absolute;
        bottom: -10px;
        left: 50%;
        transform: translateX(-50%);
        width: 60%;
        height: 3px;
        background: linear-gradient(90deg, transparent, var(--secondary-color), transparent);
        border-radius: 3px;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Carousel Container */
    .carousel-container {
        position: relative;
        margin-top: 0;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        border-bottom: 5px solid var(--accent-color);
    }

    .carousel-item {
        transition: transform 1.5s ease-in-out;
        height: 600px;
        background-attachment: fixed;
        background-position: center;
        background-repeat: no-repeat;
        background-size: cover;
    }

    .carousel-item img {
        object-fit: cover;
        height: 100%;
        width: 100%;
        filter: brightness(0.7);
        transition: transform 10s linear;
    }

    .carousel-item:hover img {
        transform: scale(1.05);
    }

    .carousel-caption {
        background-color: rgba(0, 51, 102, 0.8);
        padding: 30px;
        border-radius: 10px;
        max-width: 80%;
        margin: 0 auto;
        bottom: 100px;
        backdrop-filter: blur(5px);
        border: 1px solid rgba(255,255,255,0.2);
        text-align: left;
        left: 10%;
        right: auto;
        transform: translateX(0);
    }

    .carousel-caption h3 {
        font-weight: 600;
        margin-bottom: 15px;
        font-size: 2.5rem;
        text-shadow: 2px 2px 5px rgba(0,0,0,0.5);
        color: white;
        position: relative;
        padding-bottom: 10px;
    }

    .carousel-caption h3::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 60px;
        height: 3px;
        background-color: var(--accent-color);
    }

    .carousel-caption p {
        font-size: 1.2rem;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
        max-width: 80%;
        line-height: 1.8;
    }

    .carousel-control-prev,
    .carousel-control-next {
        width: 5%;
        opacity: 0.8;
    }

    .carousel-control-prev-icon,
    .carousel-control-next-icon {
        background-color: rgba(0, 0, 0, 0.6);
        border-radius: 50%;
        padding: 15px;
        transition: all 0.3s ease;
        width: 40px;
        height: 40px;
        background-size: 1.5rem;
    }

    .carousel-control-prev-icon:hover,
    .carousel-control-next-icon:hover {
        background-color: var(--accent-color);
        transform: scale(1.1);
    }

    .carousel-indicators button {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        margin: 0 8px;
        background-color: rgba(255,255,255,0.5);
        transition: all 0.3s ease;
    }

    .carousel-indicators button.active {
        background-color: var(--accent-color);
        transform: scale(1.2);
    }

    /* Search Box Styling */
    .search-container {
        display: flex;
        justify-content: center;
        padding: 30px;
        margin: 40px auto;
        max-width: 800px;
        background-color: white;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(0,0,0,0.05);
        position: relative;
        z-index: 10;
    }


    .search-box {
        position: relative;
        width: 80%;
    }

    .search-box input {
        width: 100%;
        padding: 18px;
        font-size: 16px;
        border: 2px solid #eaeaea;
        border-radius: 50px;
        padding-left: 60px;
        transition: all 0.3s ease;
        font-family: 'Poppins', sans-serif;
        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    }

    .search-box input:focus {
        border-color: var(--secondary-color);
        box-shadow: 0 0 15px rgba(0, 153, 204, 0.3);
        outline: none;
    }

    .search-box i {
        position: absolute;
        left: 25px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 22px;
        color: var(--primary-color);
        pointer-events: none;
        transition: all 0.3s ease;
    }

    .search-box input:focus + i {
        color: var(--accent-color);
        transform: translateY(-50%) scale(1.1);
    }

    /* Section Title */
    .section-title {
        text-align: center;
        margin: 60px 0 40px;
        position: relative;
        padding-bottom: 15px;
    }

    .section-title h2 {
        font-size: 36px;
        font-weight: 700;
        color: var(--primary-color);
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 15px;
    }

    .section-title p.subtitle {
        color: #666;
        font-size: 18px;
        max-width: 700px;
        margin: 0 auto 20px;
    }

    .section-title::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 100px;
        height: 4px;
        background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
        border-radius: 2px;
    }

    /* Inspection Boxes */
    .inspection-container {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        padding: 20px;
        gap: 30px;
        max-width: 1200px;
        margin: 0 auto;
    }

    .inspection-box {
        position: relative;
        background: linear-gradient(135deg, var(--primary-color) 0%, #004d8c 100%);
        color: white;
        width: 300px;
        min-height: 220px;
        padding: 30px;
        border-radius: 15px;
        text-align: center;
        cursor: pointer;
        transition: all 0.4s ease;
        box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        z-index: 1;
    }

    .inspection-box::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: 0.5s;
        z-index: -1;
    }

    .inspection-box:hover::before {
        left: 100%;
    }

    .inspection-box::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        opacity: 0;
        transition: all 0.4s ease;
        z-index: -2;
    }

    .inspection-box:hover::after {
        opacity: 1;
    }

    .inspection-box i {
        font-size: 48px;
        margin-bottom: 20px;
        color: white;
        transition: all 0.3s ease;
    }

    .inspection-box h4 {
        font-size: 22px;
        font-weight: 600;
        margin-bottom: 15px;
        position: relative;
        padding-bottom: 10px;
    }

    .inspection-box h4::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 50px;
        height: 3px;
        background-color: rgba(255,255,255,0.5);
        transition: all 0.3s ease;
    }

    .inspection-box:hover h4::after {
        width: 80px;
        background-color: var(--accent-color);
    }

    .inspection-box p {
        font-size: 16px;
        color: rgba(255, 255, 255, 0.8);
        margin: 0;
        transition: all 0.3s ease;
    }

    .inspection-box:hover {
        transform: translateY(-10px) scale(1.03);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
    }

    .inspection-box:hover i {
        transform: scale(1.2) rotate(5deg);
        color: white;
    }

    .inspection-box:hover p {
        color: white;
    }

    .inspection-link {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 10;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-decoration: none;
        color: inherit;
        padding: 30px;
    }

    /* Quick Info Section */
    .quick-info {
        background-color: #f2f7fb;
        padding: 80px 0;
        margin: 70px 0;
        position: relative;
        overflow: hidden;
    }

    .quick-info::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: url('image/water-texture.jpg') center/cover;
        opacity: 0.03;
        z-index: 0;
    }

    .info-card {
        background-color: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        transition: all 0.4s ease;
        height: 100%;
        position: relative;
        z-index: 1;
        border: 1px solid rgba(0,0,0,0.05);
    }

    .info-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
    }

    .info-card-img {
        height: 220px;
        overflow: hidden;
        position: relative;
    }

    .info-card-img img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }

    .info-card:hover .info-card-img img {
        transform: scale(1.1);
    }

    .info-card-img .date-badge {
        position: absolute;
        top: 15px;
        right: 15px;
        background-color: var(--accent-color);
        color: white;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 14px;
        font-weight: 500;
        box-shadow: 0 3px 10px rgba(0,0,0,0.2);
    }

    .info-card-body {
        padding: 25px;
    }

    .info-card-title {
        font-size: 22px;
        font-weight: 600;
        color: var(--primary-color);
        margin-bottom: 15px;
        transition: color 0.3s ease;
    }

    .info-card:hover .info-card-title {
        color: var(--secondary-color);
    }

    .info-card-text {
        color: #666;
        margin-bottom: 20px;
        line-height: 1.7;
    }

    .info-card-link {
        display: inline-flex;
        align-items: center;
        color: var(--primary-color);
        font-weight: 500;
        text-decoration: none;
        position: relative;
        transition: all 0.3s ease;
    }

    .info-card-link i {
        margin-left: 8px;
        transition: transform 0.3s ease;
    }

    .info-card-link:hover {
        color: var(--accent-color);
    }

    .info-card-link:hover i {
        transform: translateX(5px);
    }

    /* No Results Message */
    .no-results {
        text-align: center;
        padding: 40px;
        font-size: 18px;
        color: #666;
        background-color: white;
        border-radius: 15px;
        margin: 40px auto;
        max-width: 600px;
        display: none;
        opacity: 0;
        transition: opacity 0.3s ease;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        border: 1px dashed #ddd;
    }

    .no-results i {
        font-size: 48px;
        color: var(--secondary-color);
        margin-bottom: 20px;
        display: block;
    }

    /* Stats Counter */
    .stats-section {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        padding: 80px 0;
        color: white;
        text-align: center;
        margin: 60px 0;
        position: relative;
        overflow: hidden;
    }

    .stats-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: url('image/water-drops.png') center/cover;
        opacity: 0.1;
        z-index: 0;
    }

    .stat-item {
        position: relative;
        z-index: 1;
        padding: 20px;
    }

    .stat-number {
        font-size: 48px;
        font-weight: 700;
        margin-bottom: 10px;
        color: white;
    }

    .stat-label {
        font-size: 18px;
        font-weight: 400;
        color: rgba(255,255,255,0.9);
    }

    /* Footer Styling */
    footer {
        background: linear-gradient(135deg, #1c1c1c 0%, #2c2c2c 100%);
        color: white;
        padding: 80px 0 30px;
        position: relative;
        margin-top: 70px;
        font-family: 'Poppins', sans-serif;
    }

    footer::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 5px;
        background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
    }

    .footer-content {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 40px;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px 40px;
        position: relative;
        z-index: 1;
    }

    .footer-column h3 {
        font-size: 22px;
        font-weight: 600;
        margin-bottom: 25px;
        position: relative;
        padding-bottom: 15px;
        color: white;
    }

    .footer-column h3::after {
        content: '';
        position: absolute;
        left: 0;
        bottom: 0;
        width: 50px;
        height: 3px;
        background: linear-gradient(90deg, var(--secondary-color), transparent);
        border-radius: 3px;
    }

    .footer-column p {
        color: var(--text-light);
        line-height: 1.8;
        margin-bottom: 20px;
    }

    .footer-column ul {
        list-style: none;
        padding: 0;
    }

    .footer-column ul li {
        margin-bottom: 15px;
        transition: transform 0.3s ease;
    }

    .footer-column ul li:hover {
        transform: translateX(5px);
    }

    .footer-column ul li a {
        color: var(--text-light);
        text-decoration: none;
        transition: color 0.3s ease;
        display: flex;
        align-items: center;
    }

    .footer-column ul li a i {
        margin-right: 12px;
        color: var(--secondary-color);
        font-size: 16px;
        transition: all 0.3s ease;
    }

    .footer-column ul li a:hover {
        color: var(--secondary-color);
    }

    .footer-column ul li:hover a i {
        transform: rotate(90deg);
        color: var(--accent-color);
    }

    .contact-info {
        display: flex;
        align-items: flex-start;
        margin-bottom: 20px;
    }

    .contact-info i {
        color: var(--secondary-color);
        font-size: 20px;
        margin-right: 15px;
        margin-top: 5px;
        min-width: 20px;
    }

    .contact-info span {
        flex: 1;
    }

    .footer-bottom {
        text-align: center;
        padding-top: 40px;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        margin-top: 30px;
        position: relative;
    }

    .footer-bottom::before {
        content: '';
        position: absolute;
        top: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 200px;
        height: 1px;
        background: linear-gradient(90deg, transparent, var(--secondary-color), transparent);
    }

    .social-media {
        display: flex;
        justify-content: center;
        gap: 20px;
        margin: 30px 0;
    }

    .social-icon {
        display: inline-flex;
        width: 45px;
        height: 45px;
        border-radius: 50%;
        background-color: #333;
        color: white;
        font-size: 20px;
        transition: all 0.4s ease;
        text-decoration: none;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }

    .social-icon::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: 0.5s;
    }

    .social-icon:hover::before {
        left: 100%;
    }

    .social-icon:hover {
        transform: translateY(-5px) scale(1.1);
    }

    .social-icon.instagram:hover {
        background: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%);
    }

    .social-icon.youtube:hover {
        background-color: #FF0000;
    }

    .social-icon.twitter:hover {
        background-color: #1DA1F2;
    }

    .social-icon.facebook:hover {
        background-color: #3b5998;
    }

    /* Back to Top Button */
    .back-to-top {
        position: fixed;
        bottom: 40px;
        right: 40px;
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        color: white;
        width: 60px;
        height: 60px;
        text-align: center;
        line-height: 60px;
        border-radius: 50%;
        cursor: pointer;
        opacity: 0;
        visibility: hidden;
        transition: all 0.4s ease;
        z-index: 999;
        box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 24px;
    }

    .back-to-top.show {
        opacity: 1;
        visibility: visible;
    }

    .back-to-top:hover {
        transform: translateY(-8px) scale(1.1);
        box-shadow: 0 12px 30px rgba(0,0,0,0.3);
        background: linear-gradient(135deg, var(--secondary-color) 0%, var(--accent-color) 100%);
    }

    /* Floating Water Drops */
    .water-drop {
        position: absolute;
        background: rgba(255,255,255,0.2);
        border-radius: 50%;
        filter: blur(5px);
        z-index: 0;
    }

    /* Animations */
    @keyframes float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-20px); }
    }

    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }

    .floating {
        animation: float 6s ease-in-out infinite;
    }

    .pulse {
        animation: pulse 3s ease infinite;
    }

    /* Responsive Design */
    @media (max-width: 1200px) {
        .carousel-item {
            height: 500px;
        }
        
        .carousel-caption {
            bottom: 80px;
        }
        
        .carousel-caption h3 {
            font-size: 2.2rem;
        }
    }

    @media (max-width: 992px) {
        .judul {
            font-size: 24px;
        }
        
        .carousel-caption {
            bottom: 60px;
            padding: 25px;
            max-width: 90%;
        }
        
        .carousel-caption h3 {
            font-size: 1.8rem;
        }
        
        .carousel-caption p {
            font-size: 1rem;
            max-width: 100%;
        }
        
        .inspection-box {
            width: calc(50% - 20px);
        }
        
        .section-title h2 {
            font-size: 32px;
        }
    }

    @media (max-width: 768px) {
        .logo-container {
            flex-direction: column;
            padding: 15px;
        }
        
        .logo {
            width: 70px;
            height: 70px;
            margin-bottom: 15px;
        }
        
        .judul {
            font-size: 22px;
            margin: 10px 0;
        }
        
        .carousel-item {
            height: 400px;
        }
        
        .carousel-caption {
            bottom: 40px;
            left: 50%;
            transform: translateX(-50%);
            width: 90%;
            text-align: center;
        }
        
        .carousel-caption h3 {
            font-size: 1.6rem;
        }
        
        .carousel-caption h3::after {
            left: 50%;
            transform: translateX(-50%);
        }
        
        .search-container {
            padding: 20px;
            margin: 30px auto;
        }
        
        .search-box {
            width: 100%;
        }
        
        .inspection-box {
            width: 100%;
            max-width: 350px;
        }
        
        .footer-content {
            grid-template-columns: 1fr;
        }
        
        .quick-info {
            padding: 60px 0;
        }
    }

    @media (max-width: 576px) {
        .judul {
            font-size: 20px;
        }
        
        .carousel-item {
            height: 350px;
        }
        
        .carousel-caption {
            bottom: 30px;
            padding: 20px;
        }
        
        .carousel-caption h3 {
            font-size: 1.4rem;
        }
        
        .section-title h2 {
            font-size: 28px;
        }
        
        .inspection-box {
            min-height: 200px;
            padding: 25px;
        }
        
        .inspection-box i {
            font-size: 40px;
        }
        
        .inspection-box h4 {
            font-size: 20px;
        }
        
        .back-to-top {
            width: 50px;
            height: 50px;
            line-height: 50px;
            font-size: 20px;
            bottom: 30px;
            right: 30px;
        }
    }
    </style>
</head>
<body>

<!-- Preloader -->
<div class="preloader">
    <div class="loader"></div>
</div>

<!-- Logo Container -->
<div class="logo-container">
    <img src="image/logo.png" alt="Logo PAM Jaya" class="logo">
    <div class="judul">PERUMDA AIR MINUM JAYA</div>
    <img src="image/Jakarta.png" alt="Logo Jakarta" class="logo">
</div>

<!-- Carousel Section -->
<div class="carousel-container">
    <div id="carouselExampleAutoplaying" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000">
        <div class="carousel-indicators">
            <button type="button" data-bs-target="#carouselExampleAutoplaying" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
            <button type="button" data-bs-target="#carouselExampleAutoplaying" data-bs-slide-to="1" aria-label="Slide 2"></button>
            <button type="button" data-bs-target="#carouselExampleAutoplaying" data-bs-slide-to="2" aria-label="Slide 3"></button>
            <button type="button" data-bs-target="#carouselExampleAutoplaying" data-bs-slide-to="3" aria-label="Slide 4"></button>
        </div>
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="image/pam-2.jpeg" class="d-block w-100" alt="Pelayanan Air">
                <div class="carousel-caption d-none d-md-block">
                    <h3>Komitmen Pelayanan</h3>
                    <p>Melayani kebutuhan air bersih untuk seluruh masyarakat Jakarta dengan standar kualitas tertinggi dan pelayanan prima.</p>
                </div>
            </div>
            <div class="carousel-item">
                <img src="image/pam-1.jpeg" class="d-block w-100" alt="Infrastruktur PAM">
                <div class="carousel-caption d-none d-md-block">
                    <h3>Infrastruktur Modern</h3>
                    <p>Menggunakan teknologi terkini untuk menjamin kualitas air dan distribusi yang merata ke seluruh wilayah.</p>
                </div>
            </div>
            <div class="carousel-item">
                <img src="image/pam-kerja1.jpeg" class="d-block w-100" alt="Proses Pengolahan">
                <div class="carousel-caption d-none d-md-block">
                    <h3>Proses Pengolahan Air</h3>
                    <p>Standar kualitas tinggi dalam setiap tahap pengolahan untuk memastikan air yang sehat dan aman.</p>
                </div>
            </div>
            <div class="carousel-item">
                <img src="image/pam-kerja.jpg" class="d-block w-100" alt="Tim Profesional">
                <div class="carousel-caption d-none d-md-block">
                    <h3>Tim Profesional</h3>
                    <p>Didukung oleh tenaga ahli berpengalaman di bidangnya yang siap melayani 24/7.</p>
                </div>
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleAutoplaying" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleAutoplaying" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>
</div>

<!-- Search Box -->
<div class="search-container" data-aos="fade-up">
    <div class="search-box">
        <input type="text" id="searchInput" placeholder="Cari kegiatan inspeksi...">
        <i class="fa fa-search"></i>
    </div>
</div>

<!-- Inspection Section -->
<div class="section-title" data-aos="fade-up">
    <h2>Kegiatan Inspeksi</h2>
    <p class="subtitle">Pilih jenis inspeksi yang ingin Anda lakukan</p>
</div>

<!-- Inspection Boxes -->
<div class="inspection-container" id="inspectionContainer">
    <div class="inspection-box" data-name="Inspeksi Motor dan Pompa" data-aos="fade-up">
        <a href="inspeksi-motor/inspeksi_motor.php" class="inspection-link">
            <i class="fas fa-cogs"></i>
            <h4>Inspeksi Motor dan Pompa</h4>
        </a>
    </div>
    <div class="inspection-box" data-name="Inspeksi Valve" data-aos="fade-up" data-aos-delay="100">
        <a href="inpeksi-valve/inspeksi_valve.php" class="inspection-link">
            <i class="fas fa-tint"></i>
            <h4>Inspeksi Valve</h4>
        </a>
    </div>
    <div class="inspection-box" data-name="Inspeksi Unit Air Conditioner" data-aos="fade-up" data-aos-delay="200">
        <a href="utility-inspection/inspeksi-ac.php" class="inspection-link">
            <i class="fas fa-snowflake"></i>
            <h4>Inspeksi Unit Air Conditioner</h4>
        </a>
    </div>
    <div class="inspection-box" data-name="cleaning Unit Air Conditioner" data-aos="fade-up" data-aos-delay="300">
        <a href="cleaning-ac/cleaning_ac.php" class="inspection-link">
            <i class="fa-solid fa-radiation"></i>
            <h4>Cleaning Unit Air Conditioner</h4>
        </a>
    </div>
    <div class="inspection-box" data-name="cleaning Unit Air Conditioner" data-aos="fade-up" data-aos-delay="300">
        <a href="pump-tuneUp/pumptuneup.php" class="inspection-link">
            <i class="fa-solid fa-radiation"></i>
            <h4>Mekanikal Pump Tune Up</h4>
        </a>
    </div>
    <div class="inspection-box" data-name="cleaning Unit Air Conditioner" data-aos="fade-up" data-aos-delay="300">
        <a href="valve-tuneup/valve_tuneup.php" class="inspection-link">
            <i class="fa-solid fa-tap"></i>
            <h4>Mekanikal Valve Tune Up</h4>
        </a>
    </div>
    <div class="inspection-box" data-name="cleaning Unit Air Conditioner" data-aos="fade-up" data-aos-delay="300">
        <a href="preventif-thermograph/preventif_thermograph.php" class="inspection-link">
            <i class="fa-solid fa-tap"></i>
            <h4>Preventif Thermograph</h4>
        </a>
    </div>
    <div class="inspection-box" data-name="cleaning Unit Air Conditioner" data-aos="fade-up" data-aos-delay="300">
        <a href="panel-listrik/panel_listrik.php" class="inspection-link">
            <i class="fa-solid fa-tap"></i>
            <h4>Perawatan Panel Listrik</h4>
        </a>
    </div>
    <div class="inspection-box" data-name="cleaning Unit Air Conditioner" data-aos="fade-up" data-aos-delay="300">
        <a href="pressure-indikator/pressure_indikator.php" class="inspection-link">
            <i class="fa-solid fa-tap"></i>
            <h4>Verifikasi Pressure Indikator</h4>
        </a>
    </div>
    <div class="inspection-box" data-name="cleaning Unit Air Conditioner" data-aos="fade-up" data-aos-delay="300">
        <a href="test-insulation/test_insulation.php" class="inspection-link">
            <i class="fa-solid fa-tap"></i>
            <h4>Test Insulation</h4>
        </a>
    </div>
</div>

<!-- No Results Message -->
<div class="no-results" id="noResults">
    <i class="fas fa-search"></i>
    <p>Tidak ada hasil yang sesuai dengan pencarian Anda.</p>
</div>

<!-- Quick Info Section -->
<div class="quick-info">
    <div class="container">
        <div class="section-title" data-aos="fade-up">
            <h2>Informasi Terkini</h2>
            <p class="subtitle">Berita dan pengumuman terbaru dari PAM Jaya</p>
        </div>
        <div class="row g-4">
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                <div class="info-card">
                    <div class="info-card-img">
                        <img src="image/berita1.jpg" alt="Berita PAM">
                        <span class="date-badge">15 Juni 2025</span>
                    </div>
                    <div class="info-card-body">
                        <h5 class="info-card-title">Peningkatan Kualitas Pelayanan</h5>
                        <p class="info-card-text">PAM Jaya terus berkomitmen meningkatkan kualitas layanan air bersih untuk seluruh wilayah Jakarta dengan sistem monitoring terbaru.</p>
                        <a href="#" class="info-card-link">Selengkapnya <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                <div class="info-card">
                    <div class="info-card-img">
                        <img src="image/berita2.jpg" alt="Program PAM">
                        <span class="date-badge">10 Juni 2025</span>
                    </div>
                    <div class="info-card-body">
                        <h5 class="info-card-title">Program Efisiensi Air</h5>
                        <p class="info-card-text">Mengenal program terbaru untuk mengoptimalkan penggunaan air dan mengurangi kebocoran dengan teknologi sensor canggih.</p>
                        <a href="#" class="info-card-link">Selengkapnya <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                <div class="info-card">
                    <div class="info-card-img">
                        <img src="image/berita3.jpg" alt="Jadwal PAM">
                        <span class="date-badge">5 Juni 2025</span>
                    </div>
                    <div class="info-card-body">
                        <h5 class="info-card-title">Jadwal Pemeliharaan</h5>
                        <p class="info-card-text">Informasi terkini tentang jadwal pemeliharaan dan perbaikan jaringan pipa air di wilayah Jakarta Pusat dan sekitarnya.</p>
                        <a href="#" class="info-card-link">Selengkapnya <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Footer -->
<footer>
    <div class="container">
        <div class="footer-content">
            <div class="footer-column" data-aos="fade-up">
                <h3>Tentang Kami</h3>
                <p>PERUMDA Air Minum Jaya adalah perusahaan yang bertanggung jawab atas penyediaan air bersih untuk masyarakat Jakarta, dengan komitmen memberikan layanan terbaik dan berkelanjutan.</p>
                <div class="social-media">
                    <a href="https://www.instagram.com" target="_blank" class="social-icon instagram"><i class="fab fa-instagram"></i></a>
                    <a href="https://www.youtube.com" target="_blank" class="social-icon youtube"><i class="fab fa-youtube"></i></a>
                    <a href="https://www.twitter.com" target="_blank" class="social-icon twitter"><i class="fab fa-twitter"></i></a>
                    <a href="https://www.facebook.com" target="_blank" class="social-icon facebook"><i class="fab fa-facebook-f"></i></a>
                </div>
            </div>
            <div class="footer-column" data-aos="fade-up" data-aos-delay="100">
                <h3>Tautan Cepat</h3>
                <ul>
                    <li><a href="#"><i class="fas fa-chevron-right"></i> Beranda</a></li>
                    <li><a href="#"><i class="fas fa-chevron-right"></i> Layanan</a></li>
                    <li><a href="#"><i class="fas fa-chevron-right"></i> Informasi</a></li>
                    <li><a href="#"><i class="fas fa-chevron-right"></i> Karir</a></li>
                    <li><a href="#"><i class="fas fa-chevron-right"></i> Kontak</a></li>
                </ul>
            </div>
            <div class="footer-column" data-aos="fade-up" data-aos-delay="200">
                <h3>Layanan Pelanggan</h3>
                <ul>
                    <li><a href="#"><i class="fas fa-chevron-right"></i> Pengaduan</a></li>
                    <li><a href="#"><i class="fas fa-chevron-right"></i> FAQ</a></li>
                    <li><a href="#"><i class="fas fa-chevron-right"></i> Panduan</a></li>
                    <li><a href="#"><i class="fas fa-chevron-right"></i> Pembayaran</a></li>
                    <li><a href="#"><i class="fas fa-chevron-right"></i> Permohonan</a></li>
                </ul>
            </div>
            <div class="footer-column" data-aos="fade-up" data-aos-delay="300">
                <h3>Kontak Kami</h3>
                <div class="contact-info">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>Jl. Penjernihan II No.1, Bendungan Hilir, Jakarta Pusat 10210</span>
                </div>
                <div class="contact-info">
                    <i class="fas fa-phone"></i>
                    <span>(021) 570-3606</span>
                </div>
                <div class="contact-info">
                    <i class="fas fa-envelope"></i>
                    <span>info@pamjaya.co.id</span>
                </div>
                <div class="contact-info">
                    <i class="fas fa-globe"></i>
                    <span>www.pamjaya.co.id</span>
                </div>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; 2025 Perumda PAM Jaya. All Rights Reserved.</p>
    </div>
</footer>

<!-- Back to Top Button -->
<a href="#" class="back-to-top" id="backToTop"><i class="fas fa-arrow-up"></i></a>

<!-- Link ke Bootstrap JS dan dependencies -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz4fnFO9gybD2vY9hh6b37d4QbHVqHZmE9D23tYVwVtWqM1oypB6+g8t5iJ" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

<script>
    // Preloader
    window.addEventListener('load', function() {
        setTimeout(function() {
            document.querySelector('.preloader').classList.add('fade-out');
            setTimeout(function() {
                document.querySelector('.preloader').style.display = 'none';
            }, 500);
        }, 1000);
    });

    // Animasi Scroll
    function initAOS() {
        const elements = document.querySelectorAll('[data-aos]');
        
        function checkPosition() {
            const windowHeight = window.innerHeight;
            const scrollPosition = window.scrollY || window.pageYOffset;
            
            elements.forEach(element => {
                const elementPosition = element.getBoundingClientRect().top + scrollPosition;
                const elementHeight = element.offsetHeight;
                
                if (scrollPosition > elementPosition - windowHeight + elementHeight * 0.2) {
                    element.classList.add('aos-animate');
                }
            });
        }
        
        window.addEventListener('scroll', checkPosition);
        window.addEventListener('resize', checkPosition);
        checkPosition();
    }

    // Back to Top Button
    window.addEventListener('scroll', function() {
        var backToTopBtn = document.getElementById('backToTop');
        if (window.pageYOffset > 300) {
            backToTopBtn.classList.add('show');
        } else {
            backToTopBtn.classList.remove('show');
        }
    });

    document.getElementById('backToTop').addEventListener('click', function(e) {
        e.preventDefault();
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });

    // Enhanced Search functionality
    document.getElementById('searchInput').addEventListener('input', function() {
        var searchValue = this.value.toLowerCase().trim();
        var inspectionBoxes = document.querySelectorAll('.inspection-box');
        var noResultsElement = document.getElementById('noResults');
        var visibleCount = 0;
        var delay = 0;

        inspectionBoxes.forEach(function(box, index) {
            var boxName = box.getAttribute('data-name').toLowerCase();
            if (searchValue === '' || boxName.includes(searchValue)) {
                visibleCount++;
                setTimeout(function() {
                    box.style.opacity = '0';
                    box.style.transform = 'translateY(20px)';
                    setTimeout(function() {
                        box.style.display = 'flex';
                        setTimeout(function() {
                            box.style.opacity = '1';
                            box.style.transform = 'translateY(0)';
                        }, 50);
                    }, 200);
                }, delay);
                delay += 100;
            } else {
                box.style.opacity = '0';
                box.style.transform = 'translateY(20px)';
                setTimeout(function() {
                    box.style.display = 'none';
                }, 200);
            }
        });

        // Show no results message if needed
        if (visibleCount === 0 && searchValue !== '') {
            noResultsElement.style.opacity = '0';
            noResultsElement.style.display = 'block';
            setTimeout(function() {
                noResultsElement.style.opacity = '1';
            }, 50);
        } else {
            noResultsElement.style.opacity = '0';
            setTimeout(function() {
                noResultsElement.style.display = 'none';
            }, 200);
        }
    });

    // Counter Animation
    function animateCounters() {
        const counters = document.querySelectorAll('.stat-number');
        const speed = 200;
        
        counters.forEach(counter => {
            const target = +counter.getAttribute('data-count');
            const count = +counter.innerText;
            const increment = target / speed;
            
            if(count < target) {
                counter.innerText = Math.ceil(count + increment);
                setTimeout(animateCounters, 1);
            } else {
                counter.innerText = target;
            }
        });
    }

    // Start counter animation when stats section is visible
    const statsSection = document.querySelector('.stats-section');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if(entry.isIntersecting) {
                animateCounters();
                observer.unobserve(entry.target);
            }
        });
    }, {threshold: 0.5});

    observer.observe(statsSection);

    // Create floating water drops
    function createWaterDrops() {
        const container = document.querySelector('body');
        const dropsCount = 15;
        
        for(let i = 0; i < dropsCount; i++) {
            const drop = document.createElement('div');
            drop.classList.add('water-drop');
            
            // Random properties
            const size = Math.random() * 100 + 50;
            const posX = Math.random() * 100;
            const posY = Math.random() * 100;
            const delay = Math.random() * 5;
            const duration = Math.random() * 10 + 10;
            
            drop.style.width = `${size}px`;
            drop.style.height = `${size}px`;
            drop.style.left = `${posX}%`;
            drop.style.top = `${posY}%`;
            drop.style.animationDelay = `${delay}s`;
            drop.style.animationDuration = `${duration}s`;
            
            if(i % 2 === 0) {
                drop.classList.add('floating');
            } else {
                drop.classList.add('pulse');
            }
            
            container.appendChild(drop);
        }
    }

    // Inisialisasi saat dokumen siap
    document.addEventListener('DOMContentLoaded', function() {
        initAOS();
        createWaterDrops();
    });
</script>
</body>
</html>