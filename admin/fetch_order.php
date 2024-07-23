<?php
require_once '../includes/config.php'; // Adjust the path as necessary to match your project structure

if (isset($_GET['table_id'])) {
    $table_id = $_GET['table_id'];

    try {
        $stmt = $pdo->prepare("
            SELECT o.id, o.status, o.total_price, o.order_date, GROUP_CONCAT(m.name SEPARATOR ', ') AS menu_items
            FROM orders o
            JOIN order_line_items oli ON o.id = oli.order_id
            JOIN menus m ON oli.menu_id = m.id
            WHERE o.table_id = ?
            GROUP BY o.id
            ORDER BY o.order_date DESC
        ");
        $stmt->execute([$table_id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($order) {
            echo "<h5>Order #" . htmlspecialchars($order['id']) . "</h5>";
            echo "<p>Status: " . htmlspecialchars($order['status']) . "</p>";
            echo "<p>Total Price: $" . number_format($order['total_price'], 2) . "</p>";
            echo "<p>Order Date: " . htmlspecialchars($order['order_date']) . "</p>";
            echo "<p>Menu Items: " . htmlspecialchars($order['menu_items']) . "</p>";
        } else {
            echo "<p>No order found for this table.</p>";
        }
    } catch (PDOException $e) {
        echo "<p>Error fetching order details: " . $e->getMessage() . "</p>";
    }
}
?>
