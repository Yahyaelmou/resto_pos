<?php
// Dummy data for demonstration - replace with real order data from DB!
$restaurant_name = "CosyPOS Restaurant";
$customer_name = "John Doe";
$order_items = [
    ["name" => "Pancakes", "qty" => 2, "price" => 5.00],
    ["name" => "Coffee", "qty" => 1, "price" => 2.00],
    ["name" => "Chocolate Cake", "qty" => 1, "price" => 3.00]
];
$total = 0;
foreach ($order_items as $item) {
    $total += $item['qty'] * $item['price'];
}
$date = date("Y-m-d H:i:s");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture - <?php echo htmlspecialchars($restaurant_name); ?></title>
    <link rel="stylesheet" href="assets/invoice.css">
</head>
<body>
    <div class="invoice-container" id="invoice-content">
        <h1 class="restaurant-name"><?php echo htmlspecialchars($restaurant_name); ?></h1>
        <hr>
        <div class="invoice-details">
            <div>Client : <strong><?php echo htmlspecialchars($customer_name); ?></strong></div>
            <div>Date : <strong><?php echo $date; ?></strong></div>
        </div>
        <table class="invoice-table">
            <thead>
                <tr>
                    <th>Article</th>
                    <th>Quantité</th>
                    <th>Prix Unitaire (€)</th>
                    <th>Sous-total (€)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($order_items as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                    <td><?php echo $item['qty']; ?></td>
                    <td><?php echo number_format($item['price'], 2); ?></td>
                    <td><?php echo number_format($item['qty'] * $item['price'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="total-label">Total</td>
                    <td class="total-value"><?php echo number_format($total, 2); ?> €</td>
                </tr>
            </tfoot>
        </table>
        <div class="thank-you">Merci pour votre visite !</div>
    </div>
    <div class="invoice-actions">
        <button onclick="window.print()">🖨️ Imprimer la facture</button>
        <form action="api/invoice_pdf.php" method="post" style="display:inline">
            <input type="hidden" name="restaurant_name" value="<?php echo htmlspecialchars($restaurant_name); ?>">
            <input type="hidden" name="customer_name" value="<?php echo htmlspecialchars($customer_name); ?>">
            <input type="hidden" name="date" value="<?php echo htmlspecialchars($date); ?>">
            <input type="hidden" name="order_items" value='<?php echo htmlspecialchars(json_encode($order_items)); ?>'>
            <button type="submit">📄 Télécharger en PDF</button>
        </form>
    </div>
</body>
</html>