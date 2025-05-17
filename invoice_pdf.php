<?php
require __DIR__ . '/../vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;

// Get POST data
$restaurant_name = $_POST['restaurant_name'] ?? '';
$customer_name = $_POST['customer_name'] ?? '';
$date = $_POST['date'] ?? '';
$order_items = json_decode($_POST['order_items'] ?? '[]', true);

$total = 0;
foreach ($order_items as $item) {
    $total += $item['qty'] * $item['price'];
}

// Invoice HTML (inline styles for PDF)
$html = '
<style>
body { font-family: Arial, sans-serif; color: #222; }
.invoice-container { background: #fff; padding: 24px 20px; }
.restaurant-name { text-align: center; color: #ffbe57; font-size: 1.6rem; margin-bottom: 10px; }
.invoice-details { display: flex; justify-content: space-between; margin-bottom: 16px; font-size: 1rem; }
.invoice-table { width: 100%; border-collapse: collapse; margin-bottom: 16px; font-size: 0.97rem; }
.invoice-table th, .invoice-table td { border: 1px solid #eee; padding: 7px 8px; text-align: center; }
.invoice-table th { background: #ffbe5733; color: #23272f; font-weight: bold; }
.invoice-table tfoot td { font-weight: bold; background: #fdf6e3; }
.total-label { text-align: right; }
.total-value { color: #ff7357; }
.thank-you { text-align: center; margin-top: 10px; font-size: 1.05rem; color: #32b87a; font-weight: bold; }
</style>
<div class="invoice-container">
    <div class="restaurant-name">'.htmlspecialchars($restaurant_name).'</div>
    <hr>
    <div class="invoice-details">
        <div>Client : <strong>'.htmlspecialchars($customer_name).'</strong></div>
        <div>Date : <strong>'.$date.'</strong></div>
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
        <tbody>';
foreach ($order_items as $item) {
    $html .= '<tr>
        <td>'.htmlspecialchars($item['name']).'</td>
        <td>'.$item['qty'].'</td>
        <td>'.number_format($item['price'],2).'</td>
        <td>'.number_format($item['qty']*$item['price'],2).'</td>
    </tr>';
}
$html .= '
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="total-label">Total</td>
                <td class="total-value">'.number_format($total,2).' €</td>
            </tr>
        </tfoot>
    </table>
    <div class="thank-you">Merci pour votre visite !</div>
</div>
';

// Generate and stream PDF
$options = new Options();
$options->set('defaultFont', 'Arial');
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("facture.pdf", ["Attachment" => 1]);
exit;
?>