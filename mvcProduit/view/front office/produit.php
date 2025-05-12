<?php
session_start();

// 1. Charger les produits depuis le contrôleur
require_once '../../Controller/produitcontroller.php';
require_once '../../Controller/AvisController.php';

$controller = new ProductController();
$avisController = new AvisController();
$allProducts = $controller->getAllProducts(); // Charger tous les produits
$bestSellers = $controller->getBestSellers(); // Charger les best-sellers
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>click'N'go - Produits</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Styles existants conservés */
        .trending { display: flex; overflow-x: auto; gap: 24px; margin-bottom: 40px; white-space: nowrap; padding-bottom: 10px; }
        .activity-card { flex: 0 0 240px; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 8px rgba(0,0,0,0.1); transition: transform 0.3s ease; cursor: pointer; text-align: center; display: flex; flex-direction: column; justify-content: space-between; }
        .activity-card:hover { transform: translateY(-5px); }
        .activity-card img { width: 100%; height: 180px; object-fit: cover; }
        .activity-card h4 { padding: 10px; margin: 0; font-size: 16px; font-weight: 500; color: #222; text-shadow: 1px 1px 1px rgba(0, 0, 0, 0.1); white-space: normal; word-wrap: break-word; flex: 1; display: flex; align-items: center; justify-content: center; min-height: 80px; }
        
        .category-products { display: flex; flex-wrap: wrap; gap: 25px; margin-top: 20px; padding: 0 20px; width: 100%; justify-content: center; }
        .product-item { width: 220px; display: flex; flex-direction: column; align-items: center; text-align: center; height: 450px; position: relative; padding: 10px; }
        .product-item .image-container { width: 100%; height: 280px; overflow: hidden; border-radius: 8px; margin-bottom: 5px; }
        .product-item .image-container img { width: 100%; height: 100%; object-fit: cover; }
        .product-item h4 { padding: 8px 0; margin: 0; font-size: 15px; min-height: 60px; height: auto; font-weight: 500; color: #333; line-height: 1.3; width: 100%; display: flex; align-items: center; justify-content: center; word-wrap: break-word; }
        .product-item p { padding: 0; color: #333; font-weight: bold; margin: 5px 0 15px; font-size: 16px; width: 100%; }
        .product-item .button-container { position: absolute; bottom: 15px; left: 0; right: 0; display: flex; justify-content: center; gap: 10px; padding: 5px 0; }
        .product-item .best-seller-btn { flex: 0 1 auto; text-align: center; background: linear-gradient(90deg, #FF6F91, #D86AD8); color: white; padding: 8px 15px; border-radius: 20px; text-decoration: none; transition: all 0.3s ease; font-size: 14px; font-weight: bold; min-width: 80px; margin: 0; box-shadow: 0 4px 15px rgba(216, 106, 216, 0.3); border: none; cursor: pointer; }
        .product-item .best-seller-btn:hover { background: linear-gradient(90deg, #D86AD8, #FF6F91); transform: translateY(-2px) scale(1.05); box-shadow: 0 6px 20px rgba(216, 106, 216, 0.4); }
        
        .category-title { font-size: 22px; font-weight: 600; margin: 30px 0 20px; color: #111; position: relative; padding-bottom: 10px; text-shadow: 1px 1px 1px rgba(0, 0, 0, 0.1); }
        .category-title::after { content: ''; position: absolute; bottom: 0; left: 0; width: 50px; height: 3px; background: #ff4d4d; }
        .error { color: red; font-weight: bold; text-align: center; }
        
        .reviews-section { margin: 40px 0; padding: 20px; background: linear-gradient(135deg, #f9f9f9, #ffffff); border-radius: 15px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); display: none; }
        .reviews-section .subtitle { text-align: center; margin-bottom: 30px; font-size: 28px; color: #222; position: relative; }
        .reviews-section .subtitle::after { content: ''; display: block; width: 60px; height: 3px; background: #ff4d4d; margin: 10px auto; }
        
        .review-form-container { max-width: 500px; margin: 0 auto; background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); }
        .review-form-container h3 { font-size: 24px; color: #333; margin-bottom: 20px; text-align: center; font-weight: 500; }
        .review-form select, .review-form input, .review-form textarea { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 5px; font-size: 14px; font-family: 'Poppins', sans-serif; box-sizing: border-box; }
        .review-form select:focus, .review-form input:focus, .review-form textarea:focus { border-color: #ff4d4d; outline: none; box-shadow: 0 0 5px rgba(255, 77, 77, 0.3); }
        .review-form textarea { height: 100px; resize: vertical; }
        .star-input { display: flex; justify-content: center; gap: 8px; margin-bottom: 15px; }
        .star-input span { font-size: 28px; cursor: pointer; color: #ccc; transition: color 0.3s ease, transform 0.2s ease; }
        .star-input span:hover, .star-input span.active { color: #ffcc00; transform: scale(1.1); }
        .review-form button { width: 100%; padding: 12px; background: linear-gradient(to left, #be3cf0, #dc46d7 17%, #ff50aa 68%, #ff6666); color: white; border: none; border-radius: 5px; font-size: 16px; font-family: 'Poppins', sans-serif; cursor: pointer; transition: opacity 0.3s ease; }
        .review-form button:hover { opacity: 0.9; }
        
        @media (max-width: 768px) { .review-form-container { padding: 15px; max-width: 90%; } }
        
        .product-cards { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; padding: 20px; }
        .card { background: #fff; border-radius: 8px; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); overflow: hidden; display: flex; flex-direction: column; height: 400px; width: 100%; max-width: 250px; margin: 0 auto; }
        .product-image { width: 100%; height: 200px; overflow: hidden; display: flex; align-items: center; justify-content: center; }
        .product-image img { width: 100%; height: 100%; object-fit: cover; }
        .product-details { padding: 15px; flex-grow: 1; display: flex; flex-direction: column; justify-content: space-between; }
        .card-buttons { display: flex; gap: 10px; margin-top: 10px; justify-content: center; }
        .btn { padding: 8px 16px; border-radius: 4px; cursor: pointer; text-align: center; color: white; border: none; transition: background-color 0.3s ease; width: 100%; }
        .rent-button { background-color: #2196F3; }
        .rent-button:hover { background-color: #1976D2; }
        .buy-button { background-color: #4CAF50; }
        .buy-button:hover { background-color: #388E3C; }
        
        @media (max-width: 1400px) { .product-item { width: 200px; } }
        @media (max-width: 1100px) { .product-item { width: 180px; } }
        @media (max-width: 800px) { .product-item { width: 160px; } }
        @media (max-width: 500px) { .product-item { width: 100%; max-width: 300px; } }
        
        .cart-icon { position: relative; display: inline-block; margin-left: 10px; }
        .cart-count { position: absolute; top: -10px; right: -10px; background-color: #ff4d4d; color: white; border-radius: 50%; padding: 2px 6px; font-size: 12px; font-weight: bold; }
        .stock-alert { background: #ff6b6b; color: white; padding: 10px; border-radius: 5px; margin: 10px 0; text-align: center; display: none; }

        /* Styles pour les boutons favoris et panier */
        .cart-buttons-container {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .favorites-icon {
            position: relative;
            display: inline-block;
        }

        .favorites-icon i, .cart-icon i {
            margin-right: 5px;
        }

        .favorites-icon:hover {
            background: linear-gradient(to right, #FF6F91, #D86AD8);
        }

        /* Styles pour la section conseils */
        .conseils-section {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .conseils-title {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 30px;
        }

        .conseils-title i {
            font-size: 32px;
            color: #FF69B4;
        }

        .conseils-title h2 {
            font-size: 24px;
            color: #333;
            margin: 0;
        }

        .conseils-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
            width: 100%;
        }

        .conseil-item {
            display: flex;
            align-items: flex-start;
            gap: 15px;
            padding: 20px;
            transition: transform 0.3s ease;
        }

        .conseil-icon {
            font-size: 24px;
            color: #FF69B4;
        }

        .conseil-content {
            flex: 1;
            text-align: center;
            padding: 0 10px;
        }

        .conseil-content h3 {
            font-size: 18px;
            color: #333;
            margin: 0 0 10px 0;
        }

        .conseil-content p {
            font-size: 14px;
            color: #666;
            margin: 0;
            line-height: 1.5;
        }

        @media (max-width: 768px) {
            .conseils-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 480px) {
            .conseils-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Styles pour la section promo */
        .promo-section {
            margin: 40px 0;
            padding: 20px;
        }

        .show-promo-btn {
            background: linear-gradient(90deg, #FF6F91, #D86AD8);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            margin: 20px auto;
            display: block;
            transition: all 0.3s ease;
        }

        .show-promo-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(216, 106, 216, 0.3);
        }

        .promo-container {
            opacity: 0;
            height: 0;
            overflow: hidden;
            transition: all 0.5s ease;
        }

        .promo-container.visible {
            opacity: 1;
            height: auto;
        }

        .promo-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .promo-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .promo-card:hover {
            transform: translateY(-5px);
        }

        .promo-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .promo-text {
            padding: 20px;
            text-align: center;
        }

        .promo-text h5 {
            font-size: 18px;
            margin: 0 0 10px;
            color: #333;
        }

        .old-price {
            text-decoration: line-through;
            color: #999;
            margin: 5px 0;
        }

        .new-price {
            font-size: 20px;
            color: #ff4d4d;
            font-weight: bold;
            margin: 5px 0 15px;
        }

        .hidden {
            display: none;
        }

        .quiz-arrow {
            animation: bounceDown 2s infinite;
        }

        @keyframes bounceDown {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(5px);
            }
            60% {
                transform: translateY(3px);
            }
        }

        .quiz-container.active {
            height: auto;
            padding: 30px;
            margin-top: 20px;
        }

        .quiz-arrow.active {
            transform: rotate(180deg);
        }

        .conseils-title {
            display: flex;
            align-items: center;
            gap: 15px;
            transition: all 0.3s ease;
        }

        .conseils-title:hover {
            transform: translateY(-2px);
        }

        .quiz-question {
            transition: all 0.3s ease;
            opacity: 1;
            transform: translateX(0);
        }

        /* Styles pour la barre latérale */
        .sidebar-toggle {
            position: fixed;
            right: 20px;
            top: 20px;
            z-index: 1000;
            background: linear-gradient(90deg, #FF6F91, #D86AD8);
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .right-sidebar {
            position: fixed;
            right: -300px;
            top: 0;
            width: 300px;
            height: 100vh;
            background: white;
            box-shadow: -2px 0 5px rgba(0,0,0,0.1);
            transition: right 0.3s ease;
            z-index: 999;
            padding: 20px;
        }

        .right-sidebar.active {
            right: 0;
        }

        .sidebar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .sidebar-header h3 {
            margin: 0;
            color: #333;
        }

        .close-sidebar {
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: #666;
        }

        .sidebar-links {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            color: #333;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .sidebar-link:hover {
            background-color: #f5f5f5;
        }

        .sidebar-link i {
            color: #FF69B4;
        }

        .favorite-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 24px;
            color: #ccc;
            transition: all 0.3s ease;
            z-index: 2;
        }
        .favorite-btn:hover {
            transform: scale(1.1);
        }
        .favorite-btn.active {
            color: #ff4d4d;
        }

        /* Styles responsive pour les boutons */
        .search-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
        }

        .search-bar {
            flex: 1;
            min-width: 250px;
        }

        @media (max-width: 768px) {
            .cart-buttons-container {
                width: 100%;
                justify-content: space-between;
            }

            .register-btn.favorites-icon,
            .register-btn.cart-icon {
                font-size: 14px;
                padding: 8px 15px;
            }
        }

        .seasonal-section {
            margin: 60px 0;
            padding: 30px;
            border-radius: 15px;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.8), rgba(255, 255, 255, 0.95));
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .season-header {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .season-title {
            font-size: 28px;
            color: #333;
            position: relative;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .season-title i {
            font-size: 32px;
        }

        .season-tabs {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            flex-wrap: wrap;
        }

        .season-tab {
            padding: 8px 15px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            text-decoration: none;
            color: #333;
            background: rgba(255, 255, 255, 0.7);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .season-tab.active {
            background: linear-gradient(90deg, #FF6F91, #D86AD8);
            color: white;
            box-shadow: 0 4px 8px rgba(216, 106, 216, 0.3);
        }

        .season-tab:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        }

        .season-description {
            font-size: 16px;
            color: #666;
            max-width: 600px;
            line-height: 1.6;
            margin-bottom: 30px;
            text-align: center;
            margin: 0 auto 30px;
        }

        .season-products {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 25px;
        }

        /* Style différent selon les saisons */
        .season-printemps .season-title i {
            color: #7bc043;
        }
        .season-été .season-title i {
            color: #ff9e00;
        }
        .season-automne .season-title i {
            color: #e76f51;
        }
        .season-hiver .season-title i {
            color: #4cc9f0;
        }

        .season-printemps {
            background: linear-gradient(135deg, rgba(123, 192, 67, 0.1), rgba(255, 255, 255, 0.9));
            border-left: 5px solid #7bc043;
        }
        .season-été {
            background: linear-gradient(135deg, rgba(255, 158, 0, 0.1), rgba(255, 255, 255, 0.9));
            border-left: 5px solid #ff9e00;
        }
        .season-automne {
            background: linear-gradient(135deg, rgba(231, 111, 81, 0.1), rgba(255, 255, 255, 0.9));
            border-left: 5px solid #e76f51;
        }
        .season-hiver {
            background: linear-gradient(135deg, rgba(76, 201, 240, 0.1), rgba(255, 255, 255, 0.9));
            border-left: 5px solid #4cc9f0;
        }

        .season-product {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .season-product:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        }

        .season-product-image {
            width: 100%;
            height: 200px;
            overflow: hidden;
        }

        .season-product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .season-product:hover .season-product-image img {
            transform: scale(1.1);
        }

        .season-product-info {
            padding: 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .season-product-name {
            font-size: 18px;
            font-weight: 500;
            color: #333;
            margin-bottom: 10px;
            height: 50px;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .season-product-price {
            font-size: 20px;
            font-weight: 600;
            color: #FF6F91;
            margin-bottom: 15px;
        }

        .season-cart-btn {
            background: linear-gradient(90deg, #FF6F91, #D86AD8);
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 25px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 3px 10px rgba(216, 106, 216, 0.2);
        }

        .season-cart-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(216, 106, 216, 0.4);
        }

        .no-products {
            text-align: center;
            color: #666;
            font-size: 16px;
            padding: 20px;
        }

        .weather-info {
            text-align: center;
            margin-bottom: 20px;
            padding: 15px;
            background-color: rgba(255,255,255,0.7);
            border-radius: 8px;
            font-size: 16px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        }

        .weather-info i {
            font-size: 24px;
            margin-right: 8px;
            vertical-align: middle;
        }

        .weather-recommendation {
            margin-top: 10px;
            padding: 10px;
            background-color: rgba(255, 255, 255, 0.5);
            border-radius: 6px;
            font-style: italic;
        }

        @media (max-width: 768px) {
            .season-products {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            }
            .season-title {
                font-size: 24px;
            }
            .seasonal-section {
                padding: 20px;
            }
            .season-header {
                flex-direction: column;
                align-items: center;
            }
            .season-tabs {
                justify-content: center;
                margin-top: 20px;
            }
        }

        /* Styles pour le menu déroulant */
        .dropdown {
            position: relative;
            display: inline-block;
        }
        .dropdown-content {
            display: none;
            position: absolute;
            background: transparent !important;
            min-width: 250px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1000;
            border-radius: 8px;
            overflow: hidden;
            backdrop-filter: none !important;
        }
        .dropdown-content a {
            color: #fff !important;
            font-weight: 700;
            text-shadow: 0 2px 8px rgba(0,0,0,0.18);
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            transition: background 0.3s, color 0.3s;
            font-size: 14px;
        }
        .dropdown-content a:hover {
            background-color: #fff;
            color: #9768D1 !important;
        }
        .dropdown-content a.active {
            background-color:  #9768D1;
            color: white;
            font-weight: bold;
        }
        .dropdown.open .dropdown-content {
            display: block;
            animation: fadeIn 0.3s;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .footer-wrapper {
            width: 100vw;
            margin-left: calc(-50vw + 50%);
            background-color: white;
            background-attachment: fixed;
            background-position: center;
            background-size: cover;
            color: #333;
        }
        .footer-content {
            background-color: #f4f4f4;
            padding: 100px 40px 40px;
            width: 100%;
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
        }
        .footer-main {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            margin-bottom: 20px;
        }
        .footer-main h2 {
            color: #ffffff;
            font-size: 1.6rem;
        }
        .footer-main p {
            color: #1c3f50;
            font-size: 0.8rem;
            line-height: 1.3rem;
        }
        .footer-bottom {
            background-color: #f4f4f4;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            font-size: 0.85rem;
            color: #666;
            padding: 20px 40px 20px;
        }
        .footer-links-bottom a {
            margin-left: 20px;
            color: #666;
            text-decoration: none;
        }
        .footer-links-bottom a:hover {
            text-decoration: underline;
        }
        .newsletter {
            
background: linear-gradient(90deg, #9768D1 0%, #D48DD8 100%);

            color: #fff;
            border-radius: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 40px 50px;
            margin-bottom: 40px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        }
        .newsletter-left h2, .newsletter-left h1 {
            color: #f4f4f4;
        }
        .newsletter-input {
            background: #f4f4f4;
            border-radius: 24px;
            display: flex;
            align-items: center;
            padding: 6px 8px;
        }
        .newsletter-input input {
            border: none;
            outline: none;
            background: transparent;
            padding: 12px 18px;
            font-size: 1rem;
            color: #222;
        }
        .fotter-btn {
        background: linear-gradient(90deg, #9768D1 0%, #D48DD8 100%);
        color: #fff;
        border: none;
        border-radius: 8px;
        padding: 12px 25px;
        font-size: 1rem;
        cursor: pointer;
        transition: transform 0.3s, box-shadow 0.3s;
        white-space: nowrap;
    }
        .fotter-btn:hover {
            background:  linear-gradient(135deg, #B19CD9, #9370DB);
            transform: scale(1.05);
        }
        .payment-methods {
    display: flex;
    align-items: center;
    gap: 25px;
    margin-top: 15px;
    padding: 10px 0;
}

.payment-icon {
    height: 30px;
    width: auto;
    object-fit: contain;
}

.quiz-container {
  background: linear-gradient(135deg, #fff1f9 0%, #f3e7ff 100%);
  border-radius: 24px;
  box-shadow: 0 8px 32px rgba(232, 89, 192, 0.12);
  padding: 40px 48px;
  max-width: 600px;
  margin: 40px auto;
  transition: box-shadow 0.3s;
}
.quiz-container:hover {
  box-shadow: 0 12px 40px rgba(232, 89, 192, 0.18);
}
.quiz-question h3 {
  font-size: 1.5em;
  font-weight: 700;
  color: #222;
  margin-bottom: 32px;
  font-family: 'Inter', 'Poppins', Arial, sans-serif;
}
.quiz-options {
  display: flex;
  flex-direction: column;
  gap: 18px;
}
.quiz-option {
  background: #fff;
  border: 2px solid #FF69B4;
  color: #222;
  border-radius: 14px;
  padding: 18px 0;
  font-size: 1.15em;
  font-family: 'Inter', 'Poppins', Arial, sans-serif;
  font-weight: 500;
  text-align: center;
  cursor: pointer;
  transition: background 0.2s, color 0.2s, box-shadow 0.2s, transform 0.1s, border-color 0.2s;
  box-shadow: 0 2px 8px rgba(255, 105, 180, 0.07);
  outline: none;
}
.quiz-option:hover, .quiz-option:focus {
  background: linear-gradient(90deg, #ff69b4 0%, #b19cd9 100%);
  color: #fff;
  box-shadow: 0 4px 16px rgba(255, 105, 180, 0.18);
  transform: translateY(-2px) scale(1.03);
  border-color: #9370DB;
}
.quiz-option:active {
  background: #e859c0;
  color: #fff;
  transform: scale(0.98);
  border-color: #b19cd9;
}
    </style>
</head>
<body>
    <header class="header header-produits" style="background-image: url('images/bd.jpg'); background-size: cover; background-position: center; padding-top: 10px; position: relative;">
        <div style="display: flex; align-items: center; justify-content: flex-start; width: 100%; padding: 0 40px 0 40px; min-height: 90px;">
            <img src="images/logo.png" class="logo" alt="Logo ClickNGo" style="filter: drop-shadow(0 0 8px rgba(255, 255, 255, 0.9)); margin-right: 32px; width: 180px; height: auto;">
            <nav style="flex: 1; display: flex; align-items: center;">
                <ul class="nav-links" style="display: flex; gap: 28px; align-items: center; margin: 0; padding: 0;">
                    <li><a href="/Projet Web/mvcUtilisateur/View/FrontOffice/index.php">Accueil</a></li><span>

                    </span>
                    <li><a href="activite.html">Activités</a></li>
                    <li><a href="/Projet Web/mvcEvent/View/FrontOffice/evenemant.php">Événements</a></li>
                    <li class="dropdown" id="produitsDropdown">
                        <a href="#" id="produitsMenuLink" onclick="toggleProduitsDropdown(event)">Produits</a>
                        <div class="dropdown-content" id="produitsDropdownContent">
                            <a href="#nos-produits" onclick="scrollToSection(event, 'nos-produits')">Nos Produits</a>
                            <a href="#best-sellers" onclick="scrollToSection(event, 'best-sellers-section')">Nos Best Sellers</a>
                            <a href="#quiz" onclick="scrollToSection(event, 'quiz-section')">Quiz</a>
                            <a href="#avis" onclick="scrollToSection(event, 'reviews-section')">Avis</a>
                            <a href="favoris.php">Mes Favoris</a>
                            <a href="meteo_suggestions.php">Suggestions Météo</a>
                        </div>
                    </li>
                    <li><a href="transports.html">Transports</a></li>
                    <span>
                        
                    </span>
                    <li><a href="/Projet%20Web/mvcSponsor/crud/view/front/index.php">Sponsors</a></li>
                   
                </ul>
            </nav>
            <a href="register.php" class="register-btn" style="margin-left: 32px; background: linear-gradient(90deg, #a259e6, #c084fc); color: #fff; padding: 6px 24px; border-radius: 32px; font-size: 1.1rem; font-weight: bold; text-decoration: underline; display: inline-block; vertical-align: middle; line-height: 1.2; height: auto; z-index: 1000; position: static; top: auto; right: auto;">Register</a>
        </div>
        <h1>Découvrez nos produits exclusifs !</h1>
    </header>

    <div class="container">
        <h2 class="subtitle" id="nos-produits">Nos Produits</h2>
        <div class="search-container">
            <div class="search-bar">
                <input type="text" id="searchInput" placeholder="Rechercher un produit...">
            </div>
            <div class="cart-buttons-container">
                <a href="favoris.php" class="register-btn favorites-icon" style="background: linear-gradient(90deg, #FF6F91, #D86AD8); color: white; border: none; padding: 12px 25px; border-radius: 25px; font-size: 16px; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(216, 106, 216, 0.3); text-decoration: none; display: flex; align-items: center; margin-right: 10px;">
                    <i class="fas fa-heart" style="margin-right: 8px;"></i>
                    <span>Wishlist</span>
                </a>
                <a href="panier.php" class="register-btn cart-icon" style="background: linear-gradient(90deg, #FF6F91, #D86AD8); color: white; border: none; padding: 12px 25px; border-radius: 25px; font-size: 16px; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(216, 106, 216, 0.3); text-decoration: none; display: flex; align-items: center; position: relative;">
                    <i class="fas fa-shopping-cart" style="margin-right: 8px;"></i>
                    <span>Mon Panier</span>
                    <span id="cartCount" class="cart-count" style="position: absolute; top: -8px; right: -8px; background-color: #FF6F91; color: white; border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; font-size: 12px;">0</span>
                </a>
            </div>
        </div>

        <div class="stock-alert" id="stockAlert">Produit en rupture de stock !</div>

        <div class="trending" id="productsContainer">
            <div class="activity-card">
                <img src="images/p1.jpg" alt="Produit 1" onclick="showCategory('Équipements Sportifs')">
                <h4>Équipements Sportifs</h4>
            </div>
            <div class="activity-card">
                <img src="images/p2.jpg" alt="Produit 2" onclick="showCategory('Vêtements et Accessoires')">
                <h4>Vêtements et Accessoires</h4>
            </div>
            <div class="activity-card">
                <img src="images/p3.jpg" alt="Produit 3" onclick="showCategory('Gadgets & Technologies')">
                <h4>Gadgets & Technologies</h4>
            </div>
            <div class="activity-card">
                <img src="images/p4.jpg" alt="Produit 4" onclick="showCategory('Articles de Bien-être & Récupération')">
                <h4>Articles de Bien-être & Récupération</h4>
            </div>
            <div class="activity-card">
                <img src="images/p5.jpg" alt="Produit 5" onclick="showCategory('Nutrition & Hydratation')">
                <h4>Nutrition & Hydratation</h4>
            </div>
            <div class="activity-card">
                <img src="images/p6.jpg" alt="Produit 6" onclick="showCategory('Accessoires de Voyage & Mobilité')">
                <h4>Accessoires de Voyage & Mobilité</h4>
            </div>
            <div class="activity-card">
                <img src="images/p7.jpeg" alt="Produit 7" onclick="showCategory('Supports et accessoires d\'atelier')">
                <h4>Supports et accessoires d'atelier</h4>
            </div>
            <div class="activity-card">
                <img src="images/p8.jpeg" alt="Produit 8" onclick="showCategory('Univers du cerveau')">
                <h4>Univers du cerveau</h4>
            </div>
        </div>

        <div id="category-display"></div>

        <!-- Section "Produits Best Sellers" dynamisée -->
        <div class="best-sellers-section">
            <h2 class="subtitle"><i class="fas fa-heart"></i> Nos Best Sellers</h2>
            <div class="best-seller-grid">
                <?php foreach ($bestSellers as $product): ?>
                    <div class="best-seller-card">
                        <div class="best-seller-image">
                            <img src="<?php echo '../../' . htmlspecialchars($product->getPhoto(), ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($product->getName(), ENT_QUOTES, 'UTF-8'); ?>" onerror="this.src='../../images/default-product.jpg'">
                            <span class="best-seller-label">Best Seller</span>
                        </div>
                        <div class="best-seller-info">
                            <h4><?php echo htmlspecialchars($product->getName(), ENT_QUOTES, 'UTF-8'); ?></h4>
                            <p><?php echo htmlspecialchars($product->getFormattedPrice(), ENT_QUOTES, 'UTF-8'); ?></p>
                            <button class="best-seller-btn" onclick="addToCart('<?php echo $product->getId(); ?>', '<?php echo addslashes($product->getName()); ?>', '<?php echo $product->getPrice(); ?>', '<?php echo '../../' . htmlspecialchars($product->getPhoto(), ENT_QUOTES, 'UTF-8'); ?>')">Ajouter au panier</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <!-- Section Conseils & Guides -->
        <div class="conseils-section">
            <div class="conseils-title" style="justify-content: flex-start;">
                
                
            </div>
            <div class="conseils-grid">
                <div class="conseil-item">
                    <i class="fas fa-suitcase conseil-icon"></i>
                    <div class="conseil-content">
                        <h3>Comment préparer son sac ?</h3>
                        <p>Découvrez nos astuces pour un sac bien organisé et ne rien oublier lors de vos activités sportives.</p>
                    </div>
                </div>

                <div class="conseil-item">
                    <i class="fas fa-tshirt conseil-icon"></i>
                    <div class="conseil-content">
                        <h3>Choisir ses vêtements</h3>
                        <p>Guide pour sélectionner les vêtements adaptés à votre activité et aux conditions météo.</p>
                    </div>
                </div>

                <div class="conseil-item">
                    <i class="fas fa-shoe-prints conseil-icon"></i>
                    <div class="conseil-content">
                        <h3>Bien choisir son équipement</h3>
                        <p>Conseils pour sélectionner le matériel adapté à votre niveau et à vos besoins.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section Quiz -->
        <div class="quiz-section" style="margin: 40px 0; padding: 20px;">
            <div class="conseils-title" style="margin-bottom: 30px;">
                <i class="fas fa-question-circle" style="font-size: 32px; color: #FF69B4;"></i>
                <h2>Quiz - Gagnez un code promo !</h2>
            </div>
            <div class="quiz-container" id="quizContainer" style="max-width: 600px; margin: 0 auto; background: white; border-radius: 15px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); height: auto; overflow: visible; transition: none; visibility: visible; padding: 40px;">
                <div id="quiz-questions" style="min-height: 300px;">
                    <div class="quiz-question" data-question="1">
                        <h3 style="color: #333; margin-bottom: 15px;">Quel équipement est essentiel pour la pratique du yoga ?</h3>
                        <div class="quiz-options" style="display: flex; flex-direction: column; gap: 10px;">
                            <button onclick="nextQuestion(1, true)" class="quiz-option" style="padding: 10px 20px; border: 2px solid #FF69B4; border-radius: 8px; background: white; cursor: pointer; transition: all 0.3s ease;">Tapis de yoga</button>
                            <button onclick="nextQuestion(1, false)" class="quiz-option" style="padding: 10px 20px; border: 2px solid #FF69B4; border-radius: 8px; background: white; cursor: pointer; transition: all 0.3s ease;">Haltères</button>
                            <button onclick="nextQuestion(1, false)" class="quiz-option" style="padding: 10px 20px; border: 2px solid #FF69B4; border-radius: 8px; background: white; cursor: pointer; transition: all 0.3s ease;">Corde à sauter</button>
                        </div>
                    </div>

                    <div class="quiz-question" data-question="2" style="display: none;">
                        <h3 style="color: #333; margin-bottom: 15px;">Quelle est la durée de vie moyenne d'une paire de chaussures de running ?</h3>
                        <div class="quiz-options" style="display: flex; flex-direction: column; gap: 10px;">
                            <button onclick="nextQuestion(2, false)" class="quiz-option" style="padding: 10px 20px; border: 2px solid #FF69B4; border-radius: 8px; background: white; cursor: pointer; transition: all 0.3s ease;">2000-3000 km</button>
                            <button onclick="nextQuestion(2, true)" class="quiz-option" style="padding: 10px 20px; border: 2px solid #FF69B4; border-radius: 8px; background: white; cursor: pointer; transition: all 0.3s ease;">500-800 km</button>
                            <button onclick="nextQuestion(2, false)" class="quiz-option" style="padding: 10px 20px; border: 2px solid #FF69B4; border-radius: 8px; background: white; cursor: pointer; transition: all 0.3s ease;">100-200 km</button>
                        </div>
                    </div>

                    <div class="quiz-question" data-question="3" style="display: none;">
                        <h3 style="color: #333; margin-bottom: 15px;">Quel accessoire est recommandé pour la récupération musculaire après le sport ?</h3>
                        <div class="quiz-options" style="display: flex; flex-direction: column; gap: 10px;">
                            <button onclick="nextQuestion(3, false)" class="quiz-option" style="padding: 10px 20px; border: 2px solid #FF69B4; border-radius: 8px; background: white; cursor: pointer; transition: all 0.3s ease;">Montre connectée</button>
                            <button onclick="nextQuestion(3, false)" class="quiz-option" style="padding: 10px 20px; border: 2px solid #FF69B4; border-radius: 8px; background: white; cursor: pointer; transition: all 0.3s ease;">Écouteurs sans fil</button>
                            <button onclick="nextQuestion(3, true)" class="quiz-option" style="padding: 10px 20px; border: 2px solid #FF69B4; border-radius: 8px; background: white; cursor: pointer; transition: all 0.3s ease;">Rouleau de massage</button>
                        </div>
                    </div>
                </div>
                
                <div id="quiz-result-success" style="display: none; text-align: center; padding: 40px;">
                    <h3 style="color: #333; margin-bottom: 20px; font-size: 24px;">Félicitations !</h3>
                    <p style="color: #666; margin-bottom: 25px; font-size: 16px;">Vous avez répondu correctement aux 3 questions !</p>
                    <p style="color: #666; margin-bottom: 25px; font-size: 16px;">Voici votre code promo :</p>
                    <div style="background: linear-gradient(90deg, #FF6F91, #D86AD8); padding: 20px; border-radius: 8px; color: white; font-size: 28px; font-weight: bold; margin: 30px auto; max-width: 600px;">
                        eya120
                    </div>
                    <p style="color: #666; font-size: 16px; margin-top: 25px;">Utilisez ce code lors de votre prochain achat pour bénéficier d'une réduction !</p>
                </div>

                <div id="quiz-result-failure" style="display: none; text-align: center; padding: 40px;">
                    <h3 style="color: #333; margin-bottom: 20px; font-size: 24px;">Malheureusement...</h3>
                    <p style="color: #666; margin-bottom: 25px; font-size: 16px;">Vous n'avez pas répondu correctement à toutes les questions.</p>
                    <div style="background: linear-gradient(90deg, #ff4d4d, #ff6666); padding: 20px; border-radius: 8px; color: white; font-size: 20px; font-weight: bold; margin: 30px auto; max-width: 600px;">
                        Vous avez manqué votre chance d'obtenir le code promo
                    </div>
                    <p style="color: #666; font-size: 16px; margin-top: 25px;">N'hésitez pas à participer à nos prochains quiz pour gagner d'autres réductions !</p>
                </div>
            </div>
        </div>

        <!-- Section Affichage des Avis -->
        <div class="avis-display-section" style="margin: 40px 0; padding: 20px;">
            <h2 class="subtitle" style="text-align: center; margin-bottom: 30px;">Avis de nos clients</h2>
            <div class="trending" style="margin-bottom: 40px;">
                <?php
                $approvedReviews = $avisController->getApprovedReviews();
                if ($approvedReviews['success'] && !empty($approvedReviews['avis'])) {
                    foreach ($approvedReviews['avis'] as $review) {
                        $stars = str_repeat('★', $review['stars']) . str_repeat('☆', 5 - $review['stars']);
                        $productImage = !empty($review['product_photo']) ? '../../' . $review['product_photo'] : 'images/default-product.jpg';
                        echo "
                        <div class='activity-card' style='flex: 0 0 350px;'>
                            <div style='width: 100%; height: 200px; overflow: hidden;'>
                                <img src='" . htmlspecialchars($productImage) . "' alt='Image du produit' style='width: 100%; height: 100%; object-fit: cover;'>
                            </div>
                            <div style='padding: 15px;'>
                                <div style='display: flex; justify-content: space-between; margin-bottom: 10px;'>
                                    <span style='color: #666;'>" . htmlspecialchars($review['email']) . "</span>
                                    <span style='color: #999; font-size: 0.9em;'>" . date('d/m/Y', strtotime($review['created_at'])) . "</span>
                                </div>
                                <div style='color: #FFD700; margin-bottom: 10px; font-size: 18px;'>{$stars}</div>
                                <p style='color: #333; line-height: 1.5;'>" . htmlspecialchars($review['comment']) . "</p>
                            </div>
                        </div>";
                    }
                } else {
                    echo "<p style='text-align: center; color: #666;'>Aucun avis pour le moment.</p>";
                }
                ?>
            </div>
            <div style="text-align: center; margin-top: 30px;">
                <!-- Suppression du bouton "Voir les favoris" au bas de la page -->
            </div>
        </div>
        <!-- Bouton Avis Clients -->
        <div style="text-align: center; margin: 20px 0;">
            <button onclick="toggleReviewForm()" style="background: linear-gradient(90deg, #FF6F91, #D86AD8); color: white; border: none; padding: 12px 25px; border-radius: 25px; font-size: 16px; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(216, 106, 216, 0.3);">
                <i class="fas fa-star" style="margin-right: 8px;"></i>
                Votre Avis
            </button>
        </div>
        <!-- Section Avis Clients (Formulaire) -->
        <div class="reviews-section" id="reviewsSection" style="display: none; max-width: 800px; margin: 0 auto; background: white; border-radius: 15px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); padding: 30px; transition: all 0.3s ease;">
            <h2 class="subtitle" style="text-align: center; margin-bottom: 30px;">Donnez votre avis</h2>
            <form id="reviewForm" style="display: flex; flex-direction: column; gap: 20px;">
                <select id="productSelect" name="product_id" required style="padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 16px;">
                    <option value="" disabled selected>Choisissez un produit</option>
                    <?php if ($allProducts['success'] && !empty($allProducts['products'])): ?>
                        <?php foreach ($allProducts['products'] as $product): ?>
                            <option value="<?php echo $product['id']; ?>">
                                <?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <div class="star-input" id="starInput" style="display: flex; justify-content: center; gap: 10px; font-size: 24px;">
                    <span data-value="1" style="cursor: pointer; color: #ccc;">★</span>
                    <span data-value="2" style="cursor: pointer; color: #ccc;">★</span>
                    <span data-value="3" style="cursor: pointer; color: #ccc;">★</span>
                    <span data-value="4" style="cursor: pointer; color: #ccc;">★</span>
                    <span data-value="5" style="cursor: pointer; color: #ccc;">★</span>
                </div>
                <input type="email" id="reviewerEmail" name="email" placeholder="Votre email" required style="padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 16px;">
                <textarea id="reviewText" name="comment" placeholder="Votre commentaire" required style="padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 16px; height: 120px; resize: vertical;"></textarea>
                <button type="submit" style="background: linear-gradient(90deg, #FF6F91, #D86AD8); color: white; border: none; padding: 12px 25px; border-radius: 25px; font-size: 16px; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(216, 106, 216, 0.3);">Envoyer l'avis</button>
            </form>
        </div>
        <!-- Footer -->
        <div class="footer-wrapper">
            <div class="newsletter">
                <div class="newsletter-left">
                    <h2>Abonnez-vous à notre</h2>
                    <h1>Click'N'Go</h1>
                </div>
                <div class="newsletter-right">
                    <div class="newsletter-input">
                        <input type="text" placeholder="Entrez votre adresse e-mail" />
                        <button class="fotter-btn">Valider</button>
                    </div>
                </div>
            </div>
            <div class="footer-content">
                <div class="footer-main">
                    <div class="footer-brand">
                    <img src="images/logo.png" class="logo" alt="Logo ClickNGo" style="filter: drop-shadow(0 0 8px rgba(255, 255, 255, 0.9)); margin-right: 32px; width: 180px; height: auto;">
           
                    </div>
                    <p>Rejoignez nous aussi sur :</p>
                    <div class="social-icons">
                        <a href="#" style="--color: #0072b1" class="icon"><i class="fa-brands fa-linkedin"></i></a>
                        <a href="#" style="--color: #E1306C" class="icon"><i class="fa-brands fa-instagram"></i></a>
                        <a href="#" style="--color: #FF0050" class="icon"><i class="fa-brands fa-tiktok"></i></a>
                        <a href="#" style="--color: #4267B2" class="icon"><i class="fa-brands fa-facebook"></i></a>
                    </div>
                </div>
                <div class="links">
                    <p>Moyens de paiement</p>
                    <div class="payment-methods">
                <img src="images/visa.gif" alt="Visa" class="payment-icon">
                <img src="images/mastercard-v2.gif" alt="Mastercard" class="payment-icon">
                <img src="images/logo-cb.gif" alt="CB" class="payment-icon">
                <img src="images/paypal.gif" alt="PayPal" class="payment-icon">
            </div>
                </div>
                <div class="links">
                    <p>À propos</p>
                    <a href="/clickngo/view/about.php">À propos </a>
                    <a href="#">Presse</a>
                    <a href="#">Nous rejoindre</a>
                </div>
                <div class="links">
                    <p>Liens utiles</p>
                    <a href="#">Devenir partenaire</a>
                    <a href="#">FAQ - Besoin d'aide ?</a>
                    <a href="#">Tous les avis click'N'go</a>
                </div>
            </div>
            <div class="footer-section">
                <hr>
                <div class="footer-separator"></div>
                <div class="footer-bottom">
                    <p>© click'N'go 2025 - tous droits réservés</p>
                    <div class="footer-links-bottom">
                        <a href="#">Conditions générales</a>
                        <a href="#">Mentions légales</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentCategory = null;
        let correctAnswers = 0;

        function updateCartCount() {
            let panier = JSON.parse(localStorage.getItem('panier')) || [];
            let totalItems = panier.reduce((sum, item) => sum + item.quantite, 0);
            document.getElementById('cartCount').textContent = totalItems;
        }

        async function checkStock(productId, quantity) {
            try {
                const response = await fetch(`../../Controller/produitcontroller.php?action=get_one&id=${productId}`);
                const data = await response.json();
                if (data.success && data.product.stock >= quantity) {
                    return true;
                }
                return false;
            } catch (error) {
                console.error('Erreur lors de la vérification du stock:', error);
                return false;
            }
        }

        async function addToCart(productId, productName, productPrice, productImage) {
            const isAvailable = await checkStock(productId, 1);
            if (!isAvailable) {
                alert('Produit en rupture de stock !');
                return;
            }

            let panier = JSON.parse(localStorage.getItem('panier')) || [];
            const produitExistantIndex = panier.findIndex(p => p.id === productId);
            
            if (produitExistantIndex !== -1) {
                const newQuantity = panier[produitExistantIndex].quantite + 1;
                const canIncrement = await checkStock(productId, newQuantity);
                if (!canIncrement) {
                    alert('Stock insuffisant pour ajouter une unité supplémentaire !');
                    return;
                }
                panier[produitExistantIndex].quantite = newQuantity;
            } else {
                const produit = {
                    id: productId,
                    nom: productName,
                    prix: parseFloat(productPrice),
                    image: productImage,
                    quantite: 1
                };
                panier.push(produit);
            }
            
            localStorage.setItem('panier', JSON.stringify(panier));
            updateCartCount();
            alert(`${productName} a été ajouté au panier !`);
        }

        function showCategory(categoryName) {
            const categoryDisplay = document.getElementById('category-display');
            if (currentCategory === categoryName) {
                categoryDisplay.innerHTML = '';
                currentCategory = null;
                return;
            }

            currentCategory = categoryName;
            fetch('../../Controller/produitcontroller.php?category=' + encodeURIComponent(categoryName))
                .then(response => {
                    if (!response.ok) throw new Error('Erreur réseau');
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        categoryDisplay.innerHTML = `
                            <h2 class="category-title">${categoryName}</h2>
                            <p class="error">${data.error}</p>
                        `;
                        return;
                    }
                    if (!Array.isArray(data)) {
                        console.error('Données invalides reçues:', data);
                        throw new Error('Format de données inattendu');
                    }
                    if (data.length === 0) {
                        categoryDisplay.innerHTML = `
                            <h2 class="category-title">${categoryName}</h2>
                            <p>Aucun produit disponible dans cette catégorie.</p>
                        `;
                        return;
                    }
                    
                    let html = `<h2 class="category-title">${categoryName}</h2><div class="category-products">`;
                    data.forEach(product => {
                        const imagePath = product.image && product.image !== 'images/products/logo.png' 
                            ? `../../${product.image}` 
                            : 'images/products/logo.png';
                        html += `
                            <div class="product-item">
                                <button class="favorite-btn" onclick="toggleFavorite('${product.id}')">
                                    <i class="fas fa-heart"></i>
                                </button>
                                <div class="image-container">
                                    <img src="${imagePath}" alt="${product.name}" onerror="this.src='images/products/logo.png'">
                                </div>
                                <h4>${product.name}</h4>
                                <p>${product.price}</p>
                                <div class="button-container">
                                    <button class="best-seller-btn" onclick="addToCart('${product.id}', '${product.name.replace(/'/g, "\\'")}', '${product.price.split(' ')[0]}', '${imagePath}')">Ajouter</button>
<button class="best-seller-btn" onclick="redirigerVersLouer('${product.id}', '${product.name}', '${product.price.split(' ')[0]}', '${imagePath}')">Louer</button>

                                </div>
                            </div>
                        `;
                    });
                    html += '</div>';
                    categoryDisplay.innerHTML = html;

                    // Appliquer le filtre de recherche actuel aux nouveaux produits
                    const searchTerm = document.getElementById('searchInput').value.toLowerCase().trim();
                    if (searchTerm !== '') {
                        document.getElementById('searchInput').dispatchEvent(new Event('input'));
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    categoryDisplay.innerHTML = `
                        <h2 class="category-title">${categoryName}</h2>
                        <p class="error">Erreur lors du chargement: ${error.message}</p>
                    `;
                });
        }

        // Gestion des étoiles
        const starInput = document.getElementById('starInput');
        let selectedRating = 0;

        starInput.querySelectorAll('span').forEach(star => {
            star.addEventListener('click', () => {
                selectedRating = parseInt(star.getAttribute('data-value'));
                starInput.querySelectorAll('span').forEach(s => {
                    s.style.color = parseInt(s.getAttribute('data-value')) <= selectedRating ? '#FFD700' : '#ccc';
                });
            });
        });

        document.getElementById('reviewForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const productId = document.getElementById('productSelect').value;
            const email = document.getElementById('reviewerEmail').value.trim();
            const comment = document.getElementById('reviewText').value.trim();

            if (!productId || !email || selectedRating === 0) {
                alert("Veuillez remplir tous les champs obligatoires et sélectionner une note !");
                return;
            }

            const formData = new FormData();
            formData.append('action', 'add');
            formData.append('product_id', productId);
            formData.append('stars', selectedRating);
            formData.append('email', email);
            formData.append('comment', comment);

            try {
                const response = await fetch('../../Controller/AvisController.php', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) {
                    throw new Error('Erreur réseau lors de l\'envoi de l\'avis');
                }

                const result = await response.json();
                if (result.success) {
                    alert("Avis envoyé ! Merci pour votre retour.");
                    this.reset();
                    starInput.querySelectorAll('span').forEach(s => s.style.color = '#ccc');
                    selectedRating = 0;
                    toggleReviewForm(); // Masquer le formulaire après soumission
                } else {
                    alert("Erreur lors de l'envoi de l'avis : " + result.message);
                }
            } catch (error) {
                console.error('Erreur:', error);
                alert("Erreur lors de l'envoi de l'avis : " + error.message);
            }
        });

        function toggleReviewForm() {
            const reviewsSection = document.getElementById('reviewsSection');
            if (reviewsSection.style.display === 'none') {
                reviewsSection.style.display = 'block';
                setTimeout(() => {
                    reviewsSection.style.opacity = '1';
                    reviewsSection.style.transform = 'translateY(0)';
                }, 10);
            } else {
                reviewsSection.style.opacity = '0';
                reviewsSection.style.transform = 'translateY(-20px)';
                setTimeout(() => {
                    reviewsSection.style.display = 'none';
                }, 300);
            }
        }

        window.onload = function() {
            const target = document.getElementById('nos-produits');
            target.scrollIntoView({ behavior: 'smooth' });
            updateCartCount();
        };

        document.getElementById('searchInput').addEventListener('input', function () {
            const searchTerm = this.value.toLowerCase().trim();
            const productItems = document.querySelectorAll('.product-item');
            
            if (productItems.length > 0) {
                productItems.forEach(item => {
                    const title = item.querySelector('h4').textContent.toLowerCase();
                    if (searchTerm === '') {
                        item.style.display = ''; // Afficher tous les produits si la recherche est vide
                    } else {
                        // Vérifier si le titre du produit commence par le terme de recherche
                        const words = title.split(' ');
                        const hasMatch = words.some(word => word.startsWith(searchTerm));
                        item.style.display = hasMatch ? '' : 'none';
                    }
                });

                // Afficher un message si aucun résultat n'est trouvé
                const visibleProducts = document.querySelectorAll('.product-item[style="display: "]').length;
                const noResultsMessage = document.getElementById('noResultsMessage') || (() => {
                    const msg = document.createElement('p');
                    msg.id = 'noResultsMessage';
                    msg.style.textAlign = 'center';
                    msg.style.color = '#666';
                    msg.style.margin = '20px 0';
                    msg.style.fontSize = '16px';
                    document.querySelector('#category-display').appendChild(msg);
                    return msg;
                })();

                if (searchTerm !== '' && visibleProducts === 0) {
                    noResultsMessage.textContent = `Aucun produit ne commence par "${searchTerm}"`;
                    noResultsMessage.style.display = 'block';
                } else {
                    noResultsMessage.style.display = 'none';
                }
            }
        });

        function nextQuestion(currentQuestion, isCorrect) {
            if (!isCorrect) {
                // Si la réponse est incorrecte, afficher directement l'échec
                document.getElementById('quiz-questions').style.display = 'none';
                document.getElementById('quiz-result-success').style.display = 'none';
                document.getElementById('quiz-result-failure').style.display = 'block';
                return;
            }

            correctAnswers++;
            
            // Masquer la question actuelle avec une transition
            const currentQ = document.querySelector(`.quiz-question[data-question="${currentQuestion}"]`);
            currentQ.style.opacity = '0';
            currentQ.style.transform = 'translateX(-100%)';
            
            setTimeout(() => {
                currentQ.style.display = 'none';
                
                if (currentQuestion < 3) {
                    // Afficher la question suivante avec une transition
                    const nextQ = document.querySelector(`.quiz-question[data-question="${currentQuestion + 1}"]`);
                    nextQ.style.display = 'block';
                    setTimeout(() => {
                        nextQ.style.opacity = '1';
                        nextQ.style.transform = 'translateX(0)';
                    }, 50);
                } else {
                    // Afficher le résultat final (succès car toutes les réponses sont correctes)
                    document.getElementById('quiz-questions').style.display = 'none';
                    document.getElementById('quiz-result-success').style.display = 'block';
                    document.getElementById('quiz-result-failure').style.display = 'none';
                }
            }, 300);
        }

        function scrollToSection(event, sectionId) {
            event.preventDefault();
            let section;
            if (sectionId === 'best-sellers-section') {
                section = document.querySelector('.best-sellers-section');
            } else if (sectionId === 'quiz-section') {
                section = document.querySelector('.quiz-section');
            } else if (sectionId === 'reviews-section') {
                section = document.querySelector('.reviews-section');
                toggleReviewForm(); // Afficher le formulaire d'avis si cliqué depuis le menu
            } else {
                section = document.getElementById(sectionId);
            }
            if (section) {
                section.scrollIntoView({ behavior: 'smooth', block: 'start' });
                document.getElementById('produitsDropdownContent').style.display = 'none';
            }
        }

        function toggleProduitsDropdown(event) {
            event.preventDefault();
            const dropdown = document.getElementById('produitsDropdown');
            dropdown.classList.toggle('open');
            // Fermer au clic extérieur
            document.addEventListener('click', function handler(e) {
                if (!dropdown.contains(e.target)) {
                    dropdown.classList.remove('open');
                    document.removeEventListener('click', handler);
                }
            });
        }

        // Gestion des favoris
        function toggleFavorite(productId) {
            let favorites = JSON.parse(localStorage.getItem('favorites')) || [];
            const index = favorites.indexOf(productId);
            
            if (index === -1) {
                favorites.push(productId);
            } else {
                favorites.splice(index, 1);
            }
            
            localStorage.setItem('favorites', JSON.stringify(favorites));
            updateFavoriteButton(productId);
        }

        function updateFavoriteButton(productId) {
            const button = document.querySelector(`.favorite-btn[onclick="toggleFavorite('${productId}')"]`);
            const favorites = JSON.parse(localStorage.getItem('favorites')) || [];
            
            if (favorites.includes(productId)) {
                button.classList.add('active');
            } else {
                button.classList.remove('active');
            }
        }

        // Mettre à jour l'état des boutons favoris au chargement de la page
        window.addEventListener('load', function() {
            const favorites = JSON.parse(localStorage.getItem('favorites')) || [];
            favorites.forEach(productId => {
                updateFavoriteButton(productId);
            });
        });
    </script>

    <script>
  const isLoggedIn = <?php echo isset($_SESSION['user']['id_user']) ? 'true' : 'false'; ?>;

  function redirigerVersLouer(id, produit, prix, image) {
  if (!isLoggedIn) {
    window.location.href = '/Projet Web/mvcUtilisateur/View/BackOffice/login/login.php';
  } else {
    const url = `louer.php?id=${id}&produit=${encodeURIComponent(produit)}&prix=${prix}&image=${encodeURIComponent(image)}`;
    window.location.href = url;
  }
}

</script> 
</body>
</html>