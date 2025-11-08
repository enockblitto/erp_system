<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ERP System - Solution Professionnelle de Gestion d'Entreprise</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #0d9488;
            --primary-light: #14b8a6;
            --primary-dark: #0f766e;
            --secondary: #f97316;
            --secondary-light: #fdba74;
            --dark: #1e293b;
            --gray-50: #f8fafc;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e1;
            --gray-600: #475569;
            --gray-700: #334155;
            --gray-900: #0f172a;
            --white: #ffffff;
            --success: #10b981;
            --warning: #f59e0b;
            --error: #ef4444;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-md: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-lg: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            --radius: 8px;
            --radius-lg: 12px;
            --transition: all 0.3s ease;
        }

        body {
            font-family: 'Inter', 'Segoe UI', system-ui, -apple-system, sans-serif;
            color: var(--gray-700);
            line-height: 1.6;
            overflow-x: hidden;
            background-color: var(--white);
        }

        /* Scrollbar personnalisée */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--gray-100);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--primary);
            border-radius: 4px;
        }

        /* Navbar */
        .navbar {
            background: var(--white);
            border-bottom: 1px solid var(--gray-200);
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            padding: 0 60px;
            transition: var(--transition);
        }

        .navbar.scrolled {
            box-shadow: var(--shadow-md);
        }

        .navbar-content {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 80px;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 24px;
            font-weight: 700;
            color: var(--primary);
            letter-spacing: -0.5px;
            text-decoration: none;
        }

        .logo-icon {
            width: 40px;
            height: 40px;
            background: var(--primary);
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
            transition: var(--transition);
        }

        .logo:hover .logo-icon {
            transform: rotate(-5deg);
        }

        .nav-links {
            display: flex;
            gap: 48px;
            list-style: none;
            align-items: center;
        }

        .nav-links a {
            text-decoration: none;
            color: var(--gray-600);
            font-weight: 500;
            font-size: 15px;
            transition: var(--transition);
            position: relative;
        }

        .nav-links a:hover {
            color: var(--primary);
        }

        .nav-links a:after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -4px;
            left: 0;
            background-color: var(--primary);
            transition: var(--transition);
        }

        .nav-links a:hover:after {
            width: 100%;
        }

        /* Menu Hamburger */
        .menu-toggle {
            display: none;
            flex-direction: column;
            justify-content: space-between;
            width: 30px;
            height: 21px;
            background: transparent;
            border: none;
            cursor: pointer;
            padding: 0;
            z-index: 1001;
        }

        .menu-toggle span {
            display: block;
            height: 3px;
            width: 100%;
            background-color: var(--gray-700);
            border-radius: 3px;
            transition: var(--transition);
        }

        .menu-toggle.active span:nth-child(1) {
            transform: rotate(45deg) translate(6px, 6px);
        }

        .menu-toggle.active span:nth-child(2) {
            opacity: 0;
        }

        .menu-toggle.active span:nth-child(3) {
            transform: rotate(-45deg) translate(6px, -6px);
        }

        /* Navigation Mobile */
        .nav-links-mobile {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100vh;
            background: var(--white);
            z-index: 999;
            padding: 100px 40px 40px;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            transform: translateX(-100%);
            transition: transform 0.3s ease;
            overflow-y: auto;
        }

        .nav-links-mobile.active {
            transform: translateX(0);
            display: flex;
        }

        .nav-links-mobile .nav-item {
            width: 100%;
            text-align: center;
            margin-bottom: 20px;
        }

        .nav-links-mobile .nav-link {
            display: block;
            padding: 20px;
            font-size: 18px;
            font-weight: 600;
            color: var(--gray-700);
            text-decoration: none;
            border-radius: var(--radius);
            transition: var(--transition);
            border: 2px solid transparent;
        }

        .nav-links-mobile .nav-link:hover {
            color: var(--primary);
            background: var(--gray-50);
            border-color: var(--primary);
        }

        .close-mobile-menu {
            position: absolute;
            top: 30px;
            right: 30px;
            background: none;
            border: none;
            font-size: 24px;
            color: var(--gray-600);
            cursor: pointer;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: var(--transition);
            z-index: 1001;
        }

        .close-mobile-menu:hover {
            background: var(--gray-100);
            color: var(--primary);
        }

        /* Overlay */
        .mobile-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 998;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .mobile-overlay.active {
            display: block;
            opacity: 1;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: var(--radius);
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
            box-shadow: var(--shadow);
        }

        .btn-primary:hover {
            background: var(--primary-light);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .btn-outline {
            background: transparent;
            color: var(--primary);
            border: 2px solid var(--primary);
        }

        .btn-outline:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        .btn-white {
            background: var(--white);
            color: var(--primary);
            box-shadow: var(--shadow);
        }

        .btn-white:hover {
            background: var(--gray-100);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        /* Hero Section */
        .hero {
            padding: 160px 60px 100px;
            background: linear-gradient(135deg, var(--white) 0%, var(--gray-50) 100%);
            position: relative;
            overflow: hidden;
        }

        .hero:before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 600px;
            height: 600px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-light) 0%, transparent 70%);
            opacity: 0.1;
            z-index: 0;
        }

        .hero-container {
            max-width: 1400px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 80px;
            align-items: center;
            position: relative;
            z-index: 1;
        }

        .hero-content h1 {
            font-size: 56px;
            font-weight: 800;
            color: var(--gray-900);
            line-height: 1.1;
            margin-bottom: 24px;
            letter-spacing: -1px;
        }

        .hero-content .highlight {
            color: var(--primary);
            position: relative;
            display: inline-block;
        }

        .hero-content .highlight:after {
            content: '';
            position: absolute;
            bottom: 5px;
            left: 0;
            width: 100%;
            height: 8px;
            background-color: var(--primary-light);
            opacity: 0.3;
            z-index: -1;
        }

        .hero-content p {
            font-size: 20px;
            color: var(--gray-600);
            margin-bottom: 40px;
            line-height: 1.7;
        }

        .hero-buttons {
            display: flex;
            gap: 16px;
            margin-bottom: 48px;
            flex-wrap: wrap;
        }

        .hero-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 32px;
            padding-top: 48px;
            border-top: 1px solid var(--gray-200);
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: 36px;
            font-weight: 700;
            color: var(--gray-900);
            display: block;
            margin-bottom: 8px;
        }

        .stat-label {
            font-size: 14px;
            color: var(--gray-600);
            font-weight: 500;
        }

        .hero-image {
            position: relative;
        }

        .dashboard-mockup {
            width: 100%;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--gray-200);
            transition: var(--transition);
            transform: perspective(1000px) rotateY(-5deg) rotateX(5deg);
        }

        .dashboard-mockup:hover {
            transform: perspective(1000px) rotateY(0) rotateX(0);
        }

        /* Trusted By Section */
        .trusted-by {
            padding: 80px 0;
            background: var(--white);
            overflow: hidden;
        }

        .trusted-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 60px;
        }

        .trusted-title {
            font-size: 14px;
            font-weight: 600;
            color: var(--gray-600);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 48px;
            text-align: center;
        }

        .marquee-container {
            margin-bottom: 32px;
        }

        .marquee {
            display: flex;
            overflow: hidden;
            user-select: none;
            gap: 80px;
            margin-bottom: 32px;
        }

        .marquee-content {
            flex-shrink: 0;
            display: flex;
            justify-content: space-around;
            gap: 80px;
            min-width: 100%;
            animation: scroll-left 25s linear infinite;
        }

        .marquee-content-reverse {
            animation: scroll-right 25s linear infinite;
        }

        @keyframes scroll-left {
            from {
                transform: translateX(0);
            }
            to {
                transform: translateX(-100%);
            }
        }

        @keyframes scroll-right {
            from {
                transform: translateX(-100%);
            }
            to {
                transform: translateX(0);
            }
        }

        .company-logo {
            font-size: 28px;
            font-weight: 700;
            color: var(--gray-600);
            opacity: 0.5;
            white-space: nowrap;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .company-logo:hover {
            opacity: 0.8;
            color: var(--primary);
            transform: translateY(-2px);
        }

        .company-logo i {
            font-size: 24px;
        }

        /* Key Benefits Section */
        .key-benefits {
            padding: 120px 60px;
            background: var(--gray-50);
            position: relative;
        }

        .benefits-container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .benefits-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
        }

        .benefits-content h2 {
            font-size: 42px;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 24px;
            line-height: 1.2;
        }

        .benefits-content p {
            font-size: 18px;
            color: var(--gray-600);
            line-height: 1.7;
            margin-bottom: 40px;
        }

        .benefits-list {
            list-style: none;
            margin-bottom: 40px;
        }

        .benefit-item {
            display: flex;
            align-items: start;
            gap: 16px;
            margin-bottom: 24px;
            padding: 20px;
            background: var(--white);
            border-radius: var(--radius);
            border: 1px solid var(--gray-200);
            transition: var(--transition);
            cursor: pointer;
        }

        .benefit-item:hover {
            border-color: var(--primary);
            box-shadow: var(--shadow-md);
            transform: translateX(8px);
        }

        .benefit-icon {
            width: 48px;
            height: 48px;
            background: var(--primary);
            color: white;
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            flex-shrink: 0;
            transition: var(--transition);
        }

        .benefit-item:hover .benefit-icon {
            transform: scale(1.1) rotate(5deg);
        }

        .benefit-text h4 {
            font-size: 18px;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 8px;
        }

        .benefit-text p {
            font-size: 15px;
            color: var(--gray-600);
            line-height: 1.6;
            margin: 0;
        }

        .benefits-visual {
            position: relative;
        }

        .visual-card {
            background: var(--white);
            border-radius: var(--radius-lg);
            padding: 32px;
            border: 1px solid var(--gray-200);
            box-shadow: var(--shadow);
            transition: var(--transition);
        }

        .visual-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .visual-stat {
            margin-bottom: 24px;
        }

        .visual-stat-label {
            font-size: 14px;
            color: var(--gray-600);
            margin-bottom: 8px;
            display: flex;
            justify-content: space-between;
        }

        .visual-stat-value {
            font-weight: 600;
        }

        .visual-stat-bar {
            height: 12px;
            background: var(--gray-100);
            border-radius: 6px;
            overflow: hidden;
            margin-bottom: 8px;
        }

        .visual-stat-fill {
            height: 100%;
            background: var(--primary);
            border-radius: 6px;
            width: 0;
            transition: width 1.5s ease-out;
        }

        .visual-card-primary {
            margin-top: 24px;
            background: var(--primary);
            color: white;
        }

        .visual-card-primary:hover {
            transform: translateY(-5px);
        }

        /* Features Section */
        .features {
            padding: 120px 60px;
            background: var(--white);
        }

        .section-header {
            max-width: 800px;
            margin: 0 auto 80px;
            text-align: center;
        }

        .section-label {
            font-size: 14px;
            font-weight: 600;
            color: var(--primary);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 16px;
            display: inline-block;
            padding: 6px 16px;
            background: rgba(13, 148, 136, 0.1);
            border-radius: 20px;
        }

        .section-title {
            font-size: 42px;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 20px;
            letter-spacing: -0.5px;
        }

        .section-description {
            font-size: 18px;
            color: var(--gray-600);
            line-height: 1.7;
        }

        .features-grid {
            max-width: 1400px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 48px;
        }

        .feature-card {
            background: var(--white);
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-lg);
            padding: 40px;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .feature-card:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: var(--primary);
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.3s ease;
        }

        .feature-card:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-8px);
            border-color: var(--primary);
        }

        .feature-card:hover:before {
            transform: scaleX(1);
        }

        .feature-icon {
            width: 56px;
            height: 56px;
            background: var(--gray-50);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin-bottom: 24px;
            transition: var(--transition);
        }

        .feature-card:hover .feature-icon {
            background: var(--primary);
            color: white;
            transform: scale(1.1);
        }

        .feature-title {
            font-size: 20px;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 12px;
        }

        .feature-description {
            color: var(--gray-600);
            line-height: 1.7;
            font-size: 15px;
        }

        /* How It Works */
        .how-it-works {
            padding: 120px 60px;
            background: var(--gray-50);
            position: relative;
        }

        .steps-grid {
            max-width: 1400px;
            margin: 60px auto 0;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 40px;
            position: relative;
        }

        .steps-grid:before {
            content: '';
            position: absolute;
            top: 60px;
            left: 12.5%;
            width: 75%;
            height: 2px;
            background: var(--gray-300);
            z-index: 1;
        }

        .step-card {
            background: var(--white);
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-lg);
            padding: 32px;
            text-align: center;
            position: relative;
            z-index: 2;
            transition: var(--transition);
        }

        .step-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
            border-color: var(--primary);
        }

        .step-number {
            width: 60px;
            height: 60px;
            background: var(--primary);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: 700;
            margin: 0 auto 24px;
            transition: var(--transition);
            position: relative;
            z-index: 3;
        }

        .step-card:hover .step-number {
            transform: scale(1.1);
            box-shadow: 0 0 0 8px rgba(13, 148, 136, 0.2);
        }

        .step-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 12px;
        }

        .step-description {
            color: var(--gray-600);
            font-size: 14px;
            line-height: 1.6;
        }

        /* Statistics */
        .statistics {
            padding: 100px 60px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            position: relative;
            overflow: hidden;
        }

        .statistics:before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 600px;
            height: 600px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.05);
            z-index: 1;
        }

        .stats-container {
            max-width: 1400px;
            margin: 0 auto;
            position: relative;
            z-index: 2;
        }

        .stats-header {
            text-align: center;
            margin-bottom: 60px;
        }

        .stats-header h2 {
            font-size: 42px;
            font-weight: 700;
            margin-bottom: 16px;
        }

        .stats-header p {
            font-size: 18px;
            opacity: 0.9;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 48px;
        }

        .stat-box {
            text-align: center;
            padding: 32px;
            background: rgba(255,255,255,0.1);
            border-radius: var(--radius-lg);
            border: 1px solid rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
            transition: var(--transition);
        }

        .stat-box:hover {
            transform: translateY(-5px);
            background: rgba(255,255,255,0.15);
        }

        .stat-value {
            font-size: 48px;
            font-weight: 700;
            display: block;
            margin-bottom: 12px;
        }

        .stat-text {
            font-size: 16px;
            opacity: 0.9;
        }

        /* Testimonials */
        .testimonials {
            padding: 120px 60px;
            background: var(--white);
        }

        .testimonials-grid {
            max-width: 1400px;
            margin: 60px auto 0;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 40px;
        }

        .testimonial-card {
            background: var(--white);
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-lg);
            padding: 40px;
            transition: var(--transition);
            position: relative;
        }

        .testimonial-card:before {
            content: '"';
            position: absolute;
            top: 20px;
            right: 30px;
            font-size: 80px;
            color: var(--gray-200);
            font-family: Georgia, serif;
            line-height: 1;
            opacity: 0.5;
        }

        .testimonial-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
            border-color: var(--primary);
        }

        .testimonial-rating {
            color: #fbbf24;
            font-size: 20px;
            margin-bottom: 24px;
        }

        .testimonial-text {
            color: var(--gray-700);
            line-height: 1.7;
            margin-bottom: 32px;
            font-size: 16px;
            font-style: italic;
        }

        .testimonial-author {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .author-avatar {
            width: 48px;
            height: 48px;
            background: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 16px;
            flex-shrink: 0;
        }

        .author-name {
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 4px;
        }

        .author-role {
            font-size: 14px;
            color: var(--gray-600);
        }

        /* Pricing */
        .pricing {
            padding: 120px 60px;
            background: var(--gray-50);
        }

        .pricing-grid {
            max-width: 1200px;
            margin: 60px auto 0;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 32px;
        }

        .pricing-card {
            background: var(--white);
            border: 2px solid var(--gray-200);
            border-radius: var(--radius-lg);
            padding: 48px;
            position: relative;
            transition: var(--transition);
        }

        .pricing-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-lg);
        }

        .pricing-card.featured {
            border-color: var(--primary);
            box-shadow: var(--shadow-lg);
            transform: scale(1.05);
        }

        .pricing-card.featured:hover {
            transform: scale(1.05) translateY(-8px);
        }

        .pricing-badge {
            position: absolute;
            top: -12px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--primary);
            color: white;
            padding: 6px 20px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .pricing-name {
            font-size: 18px;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 8px;
        }

        .pricing-description {
            font-size: 14px;
            color: var(--gray-600);
            margin-bottom: 32px;
        }

        .pricing-price {
            margin-bottom: 32px;
        }

        .price-amount {
            font-size: 48px;
            font-weight: 700;
            color: var(--gray-900);
        }

        .price-period {
            font-size: 16px;
            color: var(--gray-600);
        }

        .pricing-features {
            list-style: none;
            margin-bottom: 32px;
        }

        .pricing-features li {
            padding: 12px 0;
            border-bottom: 1px solid var(--gray-100);
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--gray-700);
            font-size: 15px;
        }

        .pricing-features li:before {
            content: '✓';
            color: var(--success);
            font-weight: 700;
        }

        /* CTA */
        .cta {
            padding: 100px 60px;
            background: linear-gradient(135deg, var(--gray-900) 0%, var(--dark) 100%);
            color: white;
            position: relative;
            overflow: hidden;
        }

        .cta:before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 600px;
            height: 600px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.05);
            z-index: 1;
        }

        .cta-container {
            max-width: 900px;
            margin: 0 auto;
            text-align: center;
            position: relative;
            z-index: 2;
        }

        .cta h2 {
            font-size: 42px;
            font-weight: 700;
            margin-bottom: 24px;
        }

        .cta p {
            font-size: 20px;
            margin-bottom: 40px;
            opacity: 0.9;
        }

        .cta-buttons {
            display: flex;
            justify-content: center;
            gap: 16px;
            flex-wrap: wrap;
        }

        /* Footer */
        .footer {
            background: var(--gray-900);
            color: white;
            padding: 80px 60px 40px;
        }

        .footer-content {
            max-width: 1400px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 64px;
            margin-bottom: 48px;
        }

        .footer-brand h3 {
            font-size: 24px;
            margin-bottom: 16px;
        }

        .footer-brand p {
            color: rgba(255,255,255,0.7);
            line-height: 1.7;
            margin-bottom: 24px;
        }

        .social-links {
            display: flex;
            gap: 16px;
        }

        .social-links a {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            transition: var(--transition);
        }

        .social-links a:hover {
            background: var(--primary);
            transform: translateY(-2px);
        }

        .footer-section h4 {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .footer-section ul {
            list-style: none;
        }

        .footer-section li {
            margin-bottom: 12px;
        }

        .footer-section a {
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            font-size: 15px;
            transition: var(--transition);
        }

        .footer-section a:hover {
            color: white;
            padding-left: 4px;
        }

        .footer-bottom {
            max-width: 1400px;
            margin: 0 auto;
            padding-top: 32px;
            border-top: 1px solid rgba(255,255,255,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: rgba(255,255,255,0.6);
            font-size: 14px;
        }

        .footer-links {
            display: flex;
            gap: 24px;
        }

        .footer-links a {
            color: rgba(255,255,255,0.6);
            text-decoration: none;
            transition: var(--transition);
        }

        .footer-links a:hover {
            color: white;
        }

        /* Back to top button */
        .back-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            background: var(--primary);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            box-shadow: var(--shadow-md);
            transition: var(--transition);
            opacity: 0;
            visibility: hidden;
            z-index: 999;
        }

        .back-to-top.visible {
            opacity: 1;
            visibility: visible;
        }

        .back-to-top:hover {
            background: var(--primary-light);
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
        }

        /* Payment Modal Styles */
        .payment-modal {
            display: none;
            position: fixed;
            z-index: 10000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
        }

        .payment-modal-content {
            background-color: var(--white);
            margin: 2% auto;
            border-radius: var(--radius-lg);
            width: 90%;
            max-width: 600px;
            box-shadow: var(--shadow-lg);
            animation: modalSlideIn 0.3s ease;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .payment-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 24px 32px;
            border-bottom: 1px solid var(--gray-200);
        }

        .payment-header h3 {
            margin: 0;
            color: var(--gray-900);
            font-size: 24px;
        }

        .close-modal {
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            color: var(--gray-600);
            transition: var(--transition);
        }

        .close-modal:hover {
            color: var(--error);
        }

        .payment-body {
            padding: 32px;
            max-height: 70vh;
            overflow-y: auto;
        }

        .plan-summary {
            background: var(--gray-50);
            border-radius: var(--radius);
            padding: 20px;
            margin-bottom: 24px;
            border: 1px solid var(--gray-200);
        }

        .selected-plan h4 {
            margin: 0 0 8px 0;
            color: var(--gray-900);
            font-size: 18px;
        }

        .plan-price {
            font-size: 24px;
            font-weight: 700;
            color: var(--primary);
        }

        .form-section {
            margin-bottom: 32px;
        }

        .form-section h5 {
            margin: 0 0 16px 0;
            color: var(--gray-900);
            font-size: 16px;
            font-weight: 600;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--gray-700);
            font-weight: 500;
            font-size: 14px;
        }

        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid var(--gray-300);
            border-radius: var(--radius);
            font-size: 15px;
            transition: var(--transition);
            background: var(--white);
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.1);
        }

        .form-group input:invalid:not(:focus):not(:placeholder-shown) {
            border-color: var(--error);
        }

        .payment-methods {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin-bottom: 24px;
        }

        .payment-method {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px;
            border: 2px solid var(--gray-200);
            border-radius: var(--radius);
            cursor: pointer;
            transition: var(--transition);
            background: var(--white);
        }

        .payment-method:hover {
            border-color: var(--gray-300);
        }

        .payment-method.active {
            border-color: var(--primary);
            background: rgba(13, 148, 136, 0.05);
        }

        .payment-method i {
            font-size: 24px;
            color: var(--gray-600);
        }

        .payment-method.active i {
            color: var(--primary);
        }

        .payment-details {
            background: var(--gray-50);
            padding: 20px;
            border-radius: var(--radius);
            border: 1px solid var(--gray-200);
        }

        .payment-instructions {
            text-align: center;
            padding: 20px;
        }

        .payment-instructions p {
            margin: 0;
            color: var(--gray-600);
        }

        .terms-agreement {
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .terms-agreement input[type="checkbox"] {
            margin-top: 4px;
        }

        .terms-agreement label {
            font-size: 14px;
            color: var(--gray-600);
            line-height: 1.5;
        }

        .terms-agreement a {
            color: var(--primary);
            text-decoration: none;
        }

        .terms-agreement a:hover {
            text-decoration: underline;
        }

        .payment-submit {
            position: relative;
        }

        .loading-spinner {
            width: 20px;
            height: 20px;
            border: 2px solid transparent;
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-left: 8px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .hero-content h1 {
                font-size: 48px;
            }
            
            .section-title {
                font-size: 36px;
            }
            
            .benefits-content h2 {
                font-size: 36px;
            }
        }

        @media (max-width: 1024px) {
            .hero-container,
            .benefits-grid,
            .features-grid,
            .testimonials-grid,
            .pricing-grid {
                grid-template-columns: 1fr;
            }

            .steps-grid,
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .footer-content {
                grid-template-columns: 1fr 1fr;
            }
            
            .steps-grid:before {
                display: none;
            }
        }

        @media (max-width: 768px) {
            .navbar {
                padding: 0 20px;
            }

            /* Cache la navigation desktop */
            .nav-links {
                display: none;
            }

            /* Affiche le bouton hamburger */
            .menu-toggle {
                display: flex;
            }

            /* Cache le bouton "Se connecter" sur mobile */
            .navbar .btn-primary {
                display: none;
            }

            /* Ajuste le contenu de la navbar */
            .navbar-content {
                justify-content: space-between;
            }

            .hero {
                padding: 120px 20px 60px;
            }

            .hero-content h1 {
                font-size: 36px;
            }
            
            .hero-buttons {
                flex-direction: column;
                align-items: flex-start;
            }

            .features,
            .how-it-works,
            .testimonials,
            .pricing,
            .statistics,
            .cta,
            .key-benefits {
                padding: 60px 20px;
            }

            .steps-grid,
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .footer {
                padding: 60px 20px 32px;
            }

            .footer-content {
                grid-template-columns: 1fr;
                gap: 40px;
            }

            .footer-bottom {
                flex-direction: column;
                gap: 16px;
                text-align: center;
            }
            
            .cta-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }

            .payment-modal-content {
                margin: 5% auto;
                width: 95%;
            }
            
            .payment-body {
                padding: 20px;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .payment-methods {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Back to top button -->
    <a href="#" class="back-to-top">
        <i class="fas fa-arrow-up"></i>
    </a>

    <!-- Navbar -->
    <nav class="navbar">
        <div class="navbar-content">
            <a href="index.php" class="logo">
                <div class="logo-icon">
                    <i class="fas fa-building"></i>
                </div>
                <span>ERP System</span>
            </a>
            
            <!-- Navigation Desktop (visible sur grands écrans) -->
            <ul class="nav-links">
                <li><a href="#accueil">Accueil</a></li>
                <li><a href="#fonctionnalites">Fonctionnalités</a></li>
                <li><a href="#tarifs">Tarifs</a></li>
                <li><a href="#temoignages">Témoignages</a></li>
                <li><a href="#contact">Contact</a></li>
            </ul>
            
            <div style="display: flex; align-items: center; gap: 20px;">
                <a href="login.php" class="btn btn-primary" style="display: flex; align-items: center;">
                    <i class="fas fa-sign-in-alt"></i>
                    <span style="margin-left: 8px;">Se connecter</span>
                </a>
                
                <!-- Bouton Hamburger (visible sur mobiles) -->
                <button class="menu-toggle" id="menuToggle">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </div>
    </nav>

    <!-- Navigation Mobile -->
    <div class="nav-links-mobile" id="mobileMenu">
        <button class="close-mobile-menu" id="closeMobileMenu">
            <i class="fas fa-times"></i>
        </button>
        
        <div class="nav-item">
            <a href="#accueil" class="nav-link">Accueil</a>
        </div>
        <div class="nav-item">
            <a href="#fonctionnalites" class="nav-link">Fonctionnalités</a>
        </div>
        <div class="nav-item">
            <a href="#tarifs" class="nav-link">Tarifs</a>
        </div>
        <div class="nav-item">
            <a href="#temoignages" class="nav-link">Témoignages</a>
        </div>
        <div class="nav-item">
            <a href="#contact" class="nav-link">Contact</a>
        </div>
    </div>

    <!-- Overlay pour mobile -->
    <div class="mobile-overlay" id="mobileOverlay"></div>

    <!-- Hero Section -->
    <section class="hero" id="accueil">
        <div class="hero-container">
            <div class="hero-content">
                <h1>La solution ERP <span class="highlight">professionnelle</span> pour votre entreprise</h1>
                <p>Optimisez la gestion de vos employés, clients, projets et factures avec une plateforme complète et intuitive conçue pour les PME.</p>
                <div class="hero-buttons">
                    <a href="login.php" class="btn btn-primary">
                        <i class="fas fa-rocket"></i>
                        Commencer gratuitement
                    </a>
                    <a href="#fonctionnalites" class="btn btn-outline">
                        <i class="fas fa-search"></i>
                        Découvrir les fonctionnalités
                    </a>
                </div>
                <div class="hero-stats">
                    <div class="stat-item">
                        <span class="stat-number">500+</span>
                        <span class="stat-label">Entreprises clientes</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">10,000+</span>
                        <span class="stat-label">Projets gérés</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">99.9%</span>
                        <span class="stat-label">Disponibilité</span>
                    </div>
                </div>
            </div>
            <div class="hero-image">
                <svg class="dashboard-mockup" viewBox="0 0 600 400" xmlns="http://www.w3.org/2000/svg">
                    <rect width="600" height="400" fill="#f8fafc"/>
                    <rect width="600" height="60" fill="#0d9488"/>
                    <rect x="20" y="20" width="120" height="20" rx="4" fill="white" opacity="0.9"/>
                    
                    <rect x="20" y="80" width="170" height="100" rx="8" fill="white"/>
                    <rect x="35" y="95" width="60" height="60" rx="8" fill="#0d9488" opacity="0.1"/>
                    <rect x="35" y="165" width="140" height="8" rx="4" fill="#e2e8f0"/>
                    
                    <rect x="210" y="80" width="170" height="100" rx="8" fill="white"/>
                    <rect x="225" y="95" width="60" height="60" rx="8" fill="#f97316" opacity="0.1"/>
                    <rect x="225" y="165" width="140" height="8" rx="4" fill="#e2e8f0"/>
                    
                    <rect x="400" y="80" width="180" height="100" rx="8" fill="white"/>
                    <rect x="415" y="95" width="60" height="60" rx="8" fill="#16a34a" opacity="0.1"/>
                    <rect x="415" y="165" width="150" height="8" rx="4" fill="#e2e8f0"/>
                    
                    <rect x="20" y="200" width="370" height="180" rx="8" fill="white"/>
                    <polyline points="50,340 100,310 150,330 200,290 250,310 300,270 350,290" fill="none" stroke="#0d9488" stroke-width="3"/>
                    
                    <rect x="410" y="200" width="170" height="180" rx="8" fill="white"/>
                    <rect x="425" y="220" width="140" height="12" rx="4" fill="#f1f5f9"/>
                    <rect x="425" y="245" width="140" height="12" rx="4" fill="#f1f5f9"/>
                    <rect x="425" y="270" width="140" height="12" rx="4" fill="#f1f5f9"/>
                    <rect x="425" y="295" width="140" height="12" rx="4" fill="#f1f5f9"/>
                    <rect x="425" y="320" width="140" height="12" rx="4" fill="#f1f5f9"/>
                    <rect x="425" y="345" width="140" height="12" rx="4" fill="#f1f5f9"/>
                </svg>
            </div>
        </div>
    </section>

    <!-- Trusted By -->
    <section class="trusted-by">
        <div class="trusted-container">
            <div class="trusted-title">Ils nous font confiance</div>
            
            <!-- Premier défilement: gauche vers droite -->
            <div class="marquee">
                <div class="marquee-content">
                    <div class="company-logo"><i class="fas fa-building"></i> TechCorp</div>
                    <div class="company-logo"><i class="fas fa-laptop-code"></i> Digital Solutions</div>
                    <div class="company-logo"><i class="fas fa-lightbulb"></i> Innovate</div>
                    <div class="company-logo"><i class="fas fa-chart-line"></i> Growth Partners</div>
                    <div class="company-logo"><i class="fas fa-industry"></i> IndustryPro</div>
                    <div class="company-logo"><i class="fas fa-store"></i> Retail Masters</div>
                </div>
                <div class="marquee-content" aria-hidden="true">
                    <div class="company-logo"><i class="fas fa-building"></i> TechCorp</div>
                    <div class="company-logo"><i class="fas fa-laptop-code"></i> Digital Solutions</div>
                    <div class="company-logo"><i class="fas fa-lightbulb"></i> Innovate</div>
                    <div class="company-logo"><i class="fas fa-chart-line"></i> Growth Partners</div>
                    <div class="company-logo"><i class="fas fa-industry"></i> IndustryPro</div>
                    <div class="company-logo"><i class="fas fa-store"></i> Retail Masters</div>
                </div>
            </div>

            <!-- Deuxième défilement: droite vers gauche -->
            <div class="marquee">
                <div class="marquee-content marquee-content-reverse">
                    <div class="company-logo"><i class="fas fa-rocket"></i> StartUp CI</div>
                    <div class="company-logo"><i class="fas fa-users"></i> BizGroup</div>
                    <div class="company-logo"><i class="fas fa-globe"></i> Global Connect</div>
                    <div class="company-logo"><i class="fas fa-cube"></i> Cube Solutions</div>
                    <div class="company-logo"><i class="fas fa-network-wired"></i> NetWork Pro</div>
                    <div class="company-logo"><i class="fas fa-shield-alt"></i> Secure Systems</div>
                </div>
                <div class="marquee-content marquee-content-reverse" aria-hidden="true">
                    <div class="company-logo"><i class="fas fa-rocket"></i> StartUp CI</div>
                    <div class="company-logo"><i class="fas fa-users"></i> BizGroup</div>
                    <div class="company-logo"><i class="fas fa-globe"></i> Global Connect</div>
                    <div class="company-logo"><i class="fas fa-cube"></i> Cube Solutions</div>
                    <div class="company-logo"><i class="fas fa-network-wired"></i> NetWork Pro</div>
                    <div class="company-logo"><i class="fas fa-shield-alt"></i> Secure Systems</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Key Benefits Section -->
    <section class="key-benefits">
        <div class="benefits-container">
            <div class="benefits-grid">
                <div class="benefits-content">
                    <h2>Pourquoi choisir ERP System ?</h2>
                    <p>Une solution complète qui s'adapte à vos besoins et évolue avec votre entreprise. Gagnez en efficacité et prenez de meilleures décisions grâce à nos outils avancés.</p>
                    
                    <div class="benefits-list">
                        <div class="benefit-item">
                            <div class="benefit-icon"><i class="fas fa-bolt"></i></div>
                            <div class="benefit-text">
                                <h4>Mise en place rapide</h4>
                                <p>Déployez votre solution en moins de 24h avec notre processus d'onboarding guidé et notre support dédié.</p>
                            </div>
                        </div>
                        
                        <div class="benefit-item">
                            <div class="benefit-icon"><i class="fas fa-chart-line"></i></div>
                            <div class="benefit-text">
                                <h4>Gain de productivité immédiat</h4>
                                <p>Automatisez vos tâches répétitives et libérez jusqu'à 40% du temps de vos équipes pour les activités à forte valeur.</p>
                            </div>
                        </div>
                        
                        <div class="benefit-item">
                            <div class="benefit-icon"><i class="fas fa-shield-alt"></i></div>
                            <div class="benefit-text">
                                <h4>Sécurité garantie</h4>
                                <p>Vos données sont cryptées et sauvegardées automatiquement avec une infrastructure conforme aux standards internationaux.</p>
                            </div>
                        </div>
                        
                        <div class="benefit-item">
                            <div class="benefit-icon"><i class="fas fa-brain"></i></div>
                            <div class="benefit-text">
                                <h4>Insights intelligents</h4>
                                <p>Bénéficiez d'analyses en temps réel et de recommandations pour optimiser vos performances et votre rentabilité.</p>
                            </div>
                        </div>
                    </div>

                    <a href="login.php" class="btn btn-primary">
                        <i class="fas fa-external-link-alt"></i>
                        Découvrir la plateforme
                    </a>
                </div>

                <div class="benefits-visual">
                    <div class="visual-card">
                        <h3 style="font-size: 20px; color: var(--gray-900); margin-bottom: 24px; font-weight: 600;">Performance en temps réel</h3>
                        
                        <div class="visual-stat">
                            <div class="visual-stat-label">
                                Taux de complétion des projets
                                <span class="visual-stat-value">94%</span>
                            </div>
                            <div class="visual-stat-bar">
                                <div class="visual-stat-fill" data-width="94"></div>
                            </div>
                        </div>

                        <div class="visual-stat">
                            <div class="visual-stat-label">
                                Satisfaction client
                                <span class="visual-stat-value">98%</span>
                            </div>
                            <div class="visual-stat-bar">
                                <div class="visual-stat-fill" data-width="98"></div>
                            </div>
                        </div>

                        <div class="visual-stat">
                            <div class="visual-stat-label">
                                Gain de temps moyen
                                <span class="visual-stat-value">87%</span>
                            </div>
                            <div class="visual-stat-bar">
                                <div class="visual-stat-fill" data-width="87"></div>
                            </div>
                        </div>

                        <div class="visual-stat" style="margin-bottom: 0;">
                            <div class="visual-stat-label">
                                ROI sur 12 mois
                                <span class="visual-stat-value">320%</span>
                            </div>
                            <div class="visual-stat-bar">
                                <div class="visual-stat-fill" data-width="100"></div>
                            </div>
                        </div>
                    </div>

                    <div class="visual-card visual-card-primary">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                            <div>
                                <div style="font-size: 14px; opacity: 0.9; margin-bottom: 8px;">Temps économisé ce mois</div>
                                <div style="font-size: 36px; font-weight: 700;">2,450h</div>
                            </div>
                            <div style="font-size: 48px;"><i class="fas fa-stopwatch"></i></div>
                        </div>
                        <div style="padding-top: 16px; border-top: 1px solid rgba(255,255,255,0.2); font-size: 14px; opacity: 0.9;">
                            Soit l'équivalent de 306 jours de travail économisés
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features -->
    <section class="features" id="fonctionnalites">
        <div class="section-header">
            <div class="section-label">Fonctionnalités</div>
            <h2 class="section-title">Tout pour gérer votre entreprise efficacement</h2>
            <p class="section-description">Une plateforme complète qui centralise toutes vos opérations dans un seul système intégré</p>
        </div>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-users"></i></div>
                <h3 class="feature-title">Gestion des Employés</h3>
                <p class="feature-description">Gérez votre équipe, suivez les performances et organisez les rôles avec un système CRUD complet et intuitif.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-handshake"></i></div>
                <h3 class="feature-title">Gestion des Clients</h3>
                <p class="feature-description">Centralisez les informations clients, suivez l'historique des interactions et améliorez la satisfaction.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-tasks"></i></div>
                <h3 class="feature-title">Suivi des Projets</h3>
                <p class="feature-description">Planifiez et suivez vos projets en temps réel avec des indicateurs de progression précis.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-file-invoice-dollar"></i></div>
                <h3 class="feature-title">Facturation</h3>
                <p class="feature-description">Créez des factures professionnelles, suivez les paiements et gérez votre trésorerie efficacement.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-chart-bar"></i></div>
                <h3 class="feature-title">Tableaux de Bord</h3>
                <p class="feature-description">Visualisez vos KPIs en temps réel avec des graphiques et rapports détaillés pour prendre de meilleures décisions.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-lock"></i></div>
                <h3 class="feature-title">Sécurité Avancée</h3>
                <p class="feature-description">Protection maximale de vos données avec cryptage, authentification sécurisée et sauvegardes automatiques.</p>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="how-it-works">
        <div class="section-header">
            <div class="section-label">Comment ça marche</div>
            <h2 class="section-title">Démarrez en 4 étapes simples</h2>
            <p class="section-description">Un processus d'onboarding rapide pour être opérationnel en quelques minutes</p>
        </div>
        <div class="steps-grid">
            <div class="step-card">
                <div class="step-number">1</div>
                <h3 class="step-title">Créez votre compte</h3>
                <p class="step-description">Inscription gratuite en 2 minutes sans carte bancaire requise</p>
            </div>
            <div class="step-card">
                <div class="step-number">2</div>
                <h3 class="step-title">Configurez votre espace</h3>
                <p class="step-description">Ajoutez vos employés et paramétrez le système selon vos besoins</p>
            </div>
            <div class="step-card">
                <div class="step-number">3</div>
                <h3 class="step-title">Lancez vos projets</h3>
                <p class="step-description">Créez vos projets et assignez les équipes en quelques clics</p>
            </div>
            <div class="step-card">
                <div class="step-number">4</div>
                <h3 class="step-title">Analysez et optimisez</h3>
                <p class="step-description">Utilisez les rapports pour améliorer vos performances</p>
            </div>
        </div>
    </section>

    <!-- Statistics -->
    <section class="statistics">
        <div class="stats-container">
            <div class="stats-header">
                <h2>Des chiffres qui parlent</h2>
                <p>Notre plateforme aide des centaines d'entreprises à croître</p>
            </div>
            <div class="stats-grid">
                <div class="stat-box">
                    <span class="stat-value">500+</span>
                    <span class="stat-text">Entreprises</span>
                </div>
                <div class="stat-box">
                    <span class="stat-value">10K+</span>
                    <span class="stat-text">Projets gérés</span>
                </div>
                <div class="stat-box">
                    <span class="stat-value">99.9%</span>
                    <span class="stat-text">Disponibilité</span>
                </div>
                <div class="stat-box">
                    <span class="stat-value">24/7</span>
                    <span class="stat-text">Support</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="testimonials" id="temoignages">
        <div class="section-header">
            <div class="section-label">Témoignages</div>
            <h2 class="section-title">Ce que disent nos clients</h2>
            <p class="section-description">Des entreprises satisfaites qui ont transformé leur gestion avec notre solution</p>
        </div>
        <div class="testimonials-grid">
            <div class="testimonial-card">
                <div class="testimonial-rating">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
                <p class="testimonial-text">"ERP System a complètement transformé notre façon de gérer nos projets. L'interface est claire, intuitive et nos équipes ont gagné en productivité dès la première semaine."</p>
                <div class="testimonial-author">
                    <div class="author-avatar">AK</div>
                    <div>
                        <div class="author-name">Aya KOFFI</div>
                        <div class="author-role">CEO, TechCorp CI</div>
                    </div>
                </div>
            </div>
            <div class="testimonial-card">
                <div class="testimonial-rating">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
                <p class="testimonial-text">"La gestion des factures est devenue simple et efficace. Plus d'erreurs, plus d'oublis. Tout est centralisé et nous avons une visibilité totale sur notre trésorerie."</p>
                <div class="testimonial-author">
                    <div class="author-avatar">MT</div>
                    <div>
                        <div class="author-name">Moussa TRAORE</div>
                        <div class="author-role">Directeur Financier, Digital Solutions</div>
                    </div>
                </div>
            </div>
            <div class="testimonial-card">
                <div class="testimonial-rating">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
                <p class="testimonial-text">"Un outil professionnel qui répond parfaitement à nos besoins. Le support client est réactif et les mises à jour régulières améliorent constamment l'expérience."</p>
                <div class="testimonial-author">
                    <div class="author-avatar">EK</div>
                    <div>
                        <div class="author-name">Emma KOUASSI</div>
                        <div class="author-role">Directrice Générale, Innovate SARL</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing -->
    <section class="pricing" id="tarifs">
        <div class="section-header">
            <div class="section-label">Tarifs</div>
            <h2 class="section-title">Des plans adaptés à votre croissance</h2>
            <p class="section-description">Choisissez la formule qui correspond à vos besoins actuels et évoluez à votre rythme</p>
        </div>
        <div class="pricing-grid">
            <!-- Starter Plan -->
            <div class="pricing-card">
                <div class="pricing-name">Starter</div>
                <div class="pricing-description">Pour découvrir la plateforme</div>
                <div class="pricing-price">
                    <span class="price-amount">Gratuit</span>
                </div>
                <ul class="pricing-features">
                    <li>Jusqu'à 5 utilisateurs</li>
                    <li>5 projets maximum</li>
                    <li>Gestion de base</li>
                    <li>Support par email</li>
                    <li>Stockage 1 GB</li>
                    <li>Rapports standards</li>
                </ul>
                <button class="btn btn-outline select-plan" data-plan="starter" data-amount="0" style="width: 100%;">
                    <i class="fas fa-play-circle"></i>
                    Commencer gratuitement
                </button>
            </div>

            <!-- Professional Plan -->
            <div class="pricing-card featured">
                <div class="pricing-badge">Le plus populaire</div>
                <div class="pricing-name">Professional</div>
                <div class="pricing-description">Pour les PME en croissance</div>
                <div class="pricing-price">
                    <span class="price-amount">49,000</span>
                    <span class="price-period">FCFA/mois</span>
                </div>
                <ul class="pricing-features">
                    <li>Utilisateurs illimités</li>
                    <li>Projets illimités</li>
                    <li>Toutes les fonctionnalités</li>
                    <li>Support prioritaire 24/7</li>
                    <li>Stockage 50 GB</li>
                    <li>Rapports avancés</li>
                    <li>Exports PDF/Excel</li>
                    <li>Intégrations API</li>
                    <li>Formation en ligne</li>
                </ul>
                <button class="btn btn-primary select-plan" data-plan="professional" data-amount="49000" style="width: 100%;">
                    <i class="fas fa-trophy"></i>
                    Essayer 30 jours
                </button>
            </div>

            <!-- Enterprise Plan -->
            <div class="pricing-card">
                <div class="pricing-name">Enterprise</div>
                <div class="pricing-description">Pour les grandes structures</div>
                <div class="pricing-price">
                    <span class="price-amount">Sur mesure</span>
                </div>
                <ul class="pricing-features">
                    <li>Tout de Professional</li>
                    <li>Serveur dédié</li>
                    <li>Formation sur site</li>
                    <li>Gestionnaire de compte</li>
                    <li>Stockage illimité</li>
                    <li>Intégrations personnalisées</li>
                    <li>SLA garanti 99.9%</li>
                    <li>Sécurité renforcée</li>
                    <li>Support téléphonique</li>
                </ul>
                <button class="btn btn-outline select-plan" data-plan="enterprise" data-amount="custom" style="width: 100%;">
                    <i class="fas fa-headset"></i>
                    Nous contacter
                </button>
            </div>
        </div>
    </section>

    <!-- Payment Modal -->
    <div id="paymentModal" class="payment-modal">
        <div class="payment-modal-content">
            <div class="payment-header">
                <h3 id="paymentTitle">Finaliser votre abonnement</h3>
                <span class="close-modal">&times;</span>
            </div>
            <div class="payment-body">
                <div class="plan-summary">
                    <div class="selected-plan">
                        <h4 id="selectedPlanName">Starter</h4>
                        <div class="plan-price" id="selectedPlanPrice">Gratuit</div>
                    </div>
                </div>

                <!-- Formulaire de paiement -->
                <form id="paymentForm" class="payment-form">
                    <div class="form-section">
                        <h5>Informations personnelles</h5>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="firstName">Prénom</label>
                                <input type="text" id="firstName" name="firstName" required>
                            </div>
                            <div class="form-group">
                                <label for="lastName">Nom</label>
                                <input type="text" id="lastName" name="lastName" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="company">Entreprise</label>
                            <input type="text" id="company" name="company">
                        </div>
                    </div>

                    <div class="form-section">
                        <h5>Informations de paiement</h5>
                        <div class="payment-methods">
                            <div class="payment-method active" data-method="card">
                                <i class="fab fa-cc-visa"></i>
                                <span>Carte de crédit</span>
                            </div>
                            <div class="payment-method" data-method="paypal">
                                <i class="fab fa-paypal"></i>
                                <span>PayPal</span>
                            </div>
                            <div class="payment-method" data-method="orange">
                                <i class="fas fa-mobile-alt"></i>
                                <span>Orange Money</span>
                            </div>
                            <div class="payment-method" data-method="mtn">
                                <i class="fas fa-mobile-alt"></i>
                                <span>MTN Money</span>
                            </div>
                        </div>

                        <!-- Carte de crédit -->
                        <div id="cardPayment" class="payment-details">
                            <div class="form-group">
                                <label for="cardNumber">Numéro de carte</label>
                                <input type="text" id="cardNumber" name="cardNumber" placeholder="1234 5678 9012 3456" maxlength="19">
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="expiryDate">Date d'expiration</label>
                                    <input type="text" id="expiryDate" name="expiryDate" placeholder="MM/AA" maxlength="5">
                                </div>
                                <div class="form-group">
                                    <label for="cvv">CVV</label>
                                    <input type="text" id="cvv" name="cvv" placeholder="123" maxlength="3">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="cardName">Nom sur la carte</label>
                                <input type="text" id="cardName" name="cardName">
                            </div>
                        </div>

                        <!-- Autres méthodes de paiement -->
                        <div id="otherPayment" class="payment-details" style="display: none;">
                            <div class="payment-instructions">
                                <p id="paymentInstructions">Vous serez redirigé vers le service de paiement après confirmation.</p>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <div class="terms-agreement">
                            <input type="checkbox" id="terms" name="terms" required>
                            <label for="terms">J'accepte les <a href="#">conditions générales</a> et la <a href="#">politique de confidentialité</a></label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary payment-submit" style="width: 100%;">
                        <i class="fas fa-lock"></i>
                        <span id="submitText">Payer maintenant</span>
                        <div class="loading-spinner" style="display: none;"></div>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- CTA -->
    <section class="cta">
        <div class="cta-container">
            <h2>Prêt à transformer votre gestion d'entreprise ?</h2>
            <p>Rejoignez des centaines d'entreprises qui font confiance à ERP System pour optimiser leurs opérations</p>
            <div class="cta-buttons">
                <a href="login.php" class="btn btn-primary">
                    <i class="fas fa-rocket"></i>
                    Commencer gratuitement
                </a>
                <a href="#contact" class="btn btn-white">
                    <i class="fas fa-calendar-alt"></i>
                    Demander une démo
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer" id="contact">
        <div class="footer-content">
            <div class="footer-brand">
                <h3>ERP System</h3>
                <p>Solution professionnelle de gestion d'entreprise conçue pour les PME et startups. Simplifiez vos opérations et boostez votre croissance.</p>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
            <div class="footer-section">
                <h4>Produit</h4>
                <ul>
                    <li><a href="#fonctionnalites">Fonctionnalités</a></li>
                    <li><a href="#tarifs">Tarifs</a></li>
                    <li><a href="login.php">Démo</a></li>
                    <li><a href="#">Mises à jour</a></li>
                    <li><a href="#">Documentation</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Entreprise</h4>
                <ul>
                    <li><a href="#">À propos</a></li>
                    <li><a href="#">Blog</a></li>
                    <li><a href="#">Carrières</a></li>
                    <li><a href="#">Presse</a></li>
                    <li><a href="#contact">Contact</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Support</h4>
                <ul>
                    <li><a href="#">Centre d'aide</a></li>
                    <li><a href="#">FAQ</a></li>
                    <li><a href="#">Tutoriels</a></li>
                    <li><a href="#">API</a></li>
                    <li><a href="#">Statut</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <div>&copy; 2025 ERP System. Tous droits réservés.</div>
            <div class="footer-links">
                <a href="#">Confidentialité</a>
                <a href="#">Conditions</a>
                <a href="#">Cookies</a>
            </div>
        </div>
    </footer>

    <script>
        // ============================================
        // MOBILE MENU - VERSION SIMPLIFIÉE ET FONCTIONNELLE
        // ============================================
        
        let isMenuOpen = false;
        const menuToggle = document.getElementById('menuToggle');
        const mobileMenu = document.getElementById('mobileMenu');
        const mobileOverlay = document.getElementById('mobileOverlay');
        const closeMobileMenuBtn = document.getElementById('closeMobileMenu');

        function openMobileMenu() {
            mobileMenu.style.display = 'flex';
            setTimeout(() => {
                mobileMenu.classList.add('active');
                mobileOverlay.classList.add('active');
                menuToggle.classList.add('active');
            }, 10);
            document.body.style.overflow = 'hidden';
            isMenuOpen = true;
        }

        function closeMobileMenu() {
            mobileMenu.classList.remove('active');
            mobileOverlay.classList.remove('active');
            menuToggle.classList.remove('active');
            
            setTimeout(() => {
                mobileMenu.style.display = 'none';
                document.body.style.overflow = 'auto';
            }, 300);
            isMenuOpen = false;
        }

        function toggleMobileMenu() {
            if (isMenuOpen) {
                closeMobileMenu();
            } else {
                openMobileMenu();
            }
        }

        // Événements
        if (menuToggle) {
            menuToggle.addEventListener('click', toggleMobileMenu);
        }
        
        if (closeMobileMenuBtn) {
            closeMobileMenuBtn.addEventListener('click', closeMobileMenu);
        }
        
        if (mobileOverlay) {
            mobileOverlay.addEventListener('click', closeMobileMenu);
        }

        // Fermer le menu en cliquant sur les liens
        document.querySelectorAll('.nav-links-mobile .nav-link').forEach(link => {
            link.addEventListener('click', closeMobileMenu);
        });

        // ============================================
        // AUTRES FONCTIONNALITÉS
        // ============================================

        // Smooth scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    const offsetTop = target.offsetTop - 80;
                    window.scrollTo({
                        top: offsetTop,
                        behavior: 'smooth'
                    });
                    closeMobileMenu();
                }
            });
        });

        // Navbar shadow on scroll
        window.addEventListener('scroll', () => {
            const navbar = document.querySelector('.navbar');
            const currentScroll = window.pageYOffset;
            
            if (currentScroll > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
            
            // Back to top button visibility
            const backToTop = document.querySelector('.back-to-top');
            if (currentScroll > 300) {
                backToTop.classList.add('visible');
            } else {
                backToTop.classList.remove('visible');
            }
        });

        // Payment System
        document.addEventListener('DOMContentLoaded', function() {
            const paymentModal = document.getElementById('paymentModal');
            const selectPlanButtons = document.querySelectorAll('.select-plan');
            const closeModal = document.querySelector('.close-modal');
            const paymentForm = document.getElementById('paymentForm');
            const paymentMethods = document.querySelectorAll('.payment-method');
            const cardPayment = document.getElementById('cardPayment');
            const otherPayment = document.getElementById('otherPayment');
            const paymentInstructions = document.getElementById('paymentInstructions');
            const submitText = document.getElementById('submitText');
            const loadingSpinner = document.querySelector('.loading-spinner');
            
            let selectedPlan = '';
            let selectedAmount = 0;

            // Ouvrir le modal de paiement
            selectPlanButtons.forEach(button => {
                button.addEventListener('click', function() {
                    selectedPlan = this.getAttribute('data-plan');
                    selectedAmount = this.getAttribute('data-amount');
                    
                    updatePaymentModal(selectedPlan, selectedAmount);
                    paymentModal.style.display = 'block';
                    document.body.style.overflow = 'hidden';
                });
            });

            // Fermer le modal
            closeModal.addEventListener('click', function() {
                paymentModal.style.display = 'none';
                document.body.style.overflow = 'auto';
            });

            // Fermer en cliquant à l'extérieur
            window.addEventListener('click', function(event) {
                if (event.target === paymentModal) {
                    paymentModal.style.display = 'none';
                    document.body.style.overflow = 'auto';
                }
            });

            // Changer la méthode de paiement
            paymentMethods.forEach(method => {
                method.addEventListener('click', function() {
                    paymentMethods.forEach(m => m.classList.remove('active'));
                    this.classList.add('active');
                    
                    const methodType = this.getAttribute('data-method');
                    updatePaymentMethod(methodType);
                });
            });

            // Formatage des inputs de carte
            document.getElementById('cardNumber').addEventListener('input', function(e) {
                let value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
                let matches = value.match(/\d{4,16}/g);
                let match = matches && matches[0] || '';
                let parts = [];
                
                for (let i = 0; i < match.length; i += 4) {
                    parts.push(match.substring(i, i + 4));
                }
                
                if (parts.length) {
                    e.target.value = parts.join(' ');
                } else {
                    e.target.value = value;
                }
            });

            document.getElementById('expiryDate').addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length >= 2) {
                    e.target.value = value.substring(0, 2) + '/' + value.substring(2, 4);
                }
            });

            document.getElementById('cvv').addEventListener('input', function(e) {
                e.target.value = e.target.value.replace(/\D/g, '').substring(0, 3);
            });

            // Soumission du formulaire
            paymentForm.addEventListener('submit', function(e) {
                e.preventDefault();
                processPayment();
            });

            function updatePaymentModal(plan, amount) {
                const planName = document.getElementById('selectedPlanName');
                const planPrice = document.getElementById('selectedPlanPrice');
                const paymentTitle = document.getElementById('paymentTitle');
                
                let planTitle = '';
                let priceText = '';
                
                switch(plan) {
                    case 'starter':
                        planTitle = 'Starter - Gratuit';
                        priceText = 'Gratuit';
                        paymentTitle.textContent = 'Activer votre compte gratuit';
                        submitText.textContent = 'Activer le compte';
                        break;
                    case 'professional':
                        planTitle = 'Professional';
                        priceText = '49,000 FCFA/mois';
                        paymentTitle.textContent = 'Souscrire à l\'offre Professional';
                        submitText.textContent = 'Payer 49,000 FCFA';
                        break;
                    case 'enterprise':
                        planTitle = 'Enterprise';
                        priceText = 'Sur mesure';
                        paymentTitle.textContent = 'Demander un devis Enterprise';
                        submitText.textContent = 'Envoyer la demande';
                        break;
                }
                
                planName.textContent = planTitle;
                planPrice.textContent = priceText;
            }

            function updatePaymentMethod(method) {
                if (method === 'card') {
                    cardPayment.style.display = 'block';
                    otherPayment.style.display = 'none';
                } else {
                    cardPayment.style.display = 'none';
                    otherPayment.style.display = 'block';
                    
                    let instructions = '';
                    switch(method) {
                        case 'paypal':
                            instructions = 'Vous serez redirigé vers PayPal pour finaliser votre paiement.';
                            break;
                        case 'orange':
                            instructions = 'Un code de paiement Orange Money vous sera envoyé par SMS.';
                            break;
                        case 'mtn':
                            instructions = 'Un code de paiement MTN Money vous sera envoyé par SMS.';
                            break;
                    }
                    paymentInstructions.textContent = instructions;
                }
            }

            function processPayment() {
                const submitButton = paymentForm.querySelector('.payment-submit');
                const formData = new FormData(paymentForm);
                
                // Afficher le loading
                submitButton.disabled = true;
                submitText.style.display = 'none';
                loadingSpinner.style.display = 'block';
                
                // Simuler un traitement de paiement
                setTimeout(() => {
                    loadingSpinner.style.display = 'none';
                    submitText.style.display = 'inline';
                    submitButton.disabled = false;
                    
                    // Afficher un message de succès
                    showPaymentSuccess(selectedPlan);
                    
                }, 2000);
            }

            function showPaymentSuccess(plan) {
                paymentModal.style.display = 'none';
                
                let message = '';
                switch(plan) {
                    case 'starter':
                        message = 'Votre compte gratuit a été activé avec succès !';
                        break;
                    case 'professional':
                        message = 'Paiement accepté ! Votre abonnement Professional est maintenant actif.';
                        break;
                    case 'enterprise':
                        message = 'Votre demande a été envoyée. Notre équipe vous contactera dans les 24h.';
                        break;
                }
                
                // Créer une notification de succès
                const notification = document.createElement('div');
                notification.style.cssText = `
                    position: fixed;
                    top: 100px;
                    right: 30px;
                    background: var(--success);
                    color: white;
                    padding: 20px;
                    border-radius: var(--radius);
                    box-shadow: var(--shadow-lg);
                    z-index: 10000;
                    max-width: 400px;
                    animation: slideInRight 0.3s ease;
                `;
                notification.innerHTML = `
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <i class="fas fa-check-circle" style="font-size: 24px;"></i>
                        <div>
                            <strong>Succès !</strong>
                            <div style="margin-top: 4px; font-size: 14px;">${message}</div>
                        </div>
                    </div>
                `;
                
                document.body.appendChild(notification);
                
                // Rediriger vers le dashboard après succès
                setTimeout(() => {
                    window.location.href = 'dashboard.php';
                }, 3000);
                
                // Supprimer la notification après 5 secondes
                setTimeout(() => {
                    notification.remove();
                }, 5000);
            }
        });

        // Log page load
        console.log('%c🏢 ERP System - Page professionnelle chargée', 'color: #0d9488; font-size: 16px; font-weight: bold;');
        
        // Add loading animation
        window.addEventListener('load', function() {
            document.body.style.opacity = '0';
            document.body.style.transition = 'opacity 0.5s ease';
            
            setTimeout(function() {
                document.body.style.opacity = '1';
            }, 100);
        });
    </script>
</body>
</html>