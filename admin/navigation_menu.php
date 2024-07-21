<?php
require_once '../includes/config.php';  // Make sure this path correctly points to your config.php

// Determine the current script to set active class appropriately
$current_page = basename($_SERVER['PHP_SELF']);

try {
    $stmt = $pdo->prepare("SELECT * FROM menu_items WHERE active = TRUE ORDER BY order_index ASC");
    $stmt->execute();
    $menuItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($menuItems)) {
        echo "<ul class='nav nav-pills nav-sidebar flex-column' data-widget='treeview' role='menu' data-accordion='false' style='padding-top:20px;'>";
        foreach ($menuItems as $item) {
            // Check if the current menu item's link matches the current page
            $isActive = (strpos($item['link'], $current_page) !== false) ? 'active' : '';
            echo "<li class='nav-item'>";
            echo "<a href='{$base_path}{$item['link']}' class='nav-link {$isActive}'>";
            if (!empty($item['icon'])) {
                echo "<i class='{$item['icon']}'></i>";
            }
            echo "<p> {$item['name']} </p>";
            echo "</a>";
            echo "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>No menu items available.</p>";
    }
} catch (PDOException $e) {
    error_log("Error fetching menu items: " . $e->getMessage());
    echo "<p>Error loading menu items. Please contact admin.</p>";
}
?>