// ========== CosyPOS Main JS ==========

const mainContent = document.getElementById("main-content");
const orderPanel = document.getElementById("order-panel");

let menuData = [];
let order = {}; // {menu_item_id: {id, name, price, qty}}
let paymentMethod = "Cash";
let taxRate = 0.10; // will be loaded from settings

// Navigation
document.querySelectorAll(".sidebar nav li").forEach(li => {
    li.onclick = function() {
        document.querySelectorAll(".sidebar nav li").forEach(li2 => li2.classList.remove("active"));
        this.classList.add("active");
        loadSection(this.dataset.section);
    };
});

// Initial Section: Menu
window.addEventListener("DOMContentLoaded", () => {
    loadTaxRate();
    loadSection("menu");
    setupOrderPanelEvents();
});

// ------------------- Section Loader -------------------
function loadSection(section) {
    orderPanel.classList.toggle("hidden", section !== "menu" && section !== "table_services");
    switch (section) {
        case "menu": loadMenuSection(); break;
        case "reservation": loadReservationSection(); break;
        case "table_services": loadTableServicesSection(); break;
        case "reports": loadReportsSection(); break;
        case "settings": loadSettingsSection(); break;
        default: mainContent.innerHTML = "<p>Unknown section</p>";
    }
}

// ------------------- Menu & Orders -------------------
function loadMenuSection() {
    fetch("api/get_menu.php")
        .then(res => res.json())
        .then(data => {
            menuData = data;
            renderMenu();
            renderOrderPanel();
        });
}
function renderMenu() {
    let html = "";
    menuData.forEach(category => {
        html += `<div class="category-block">
            <div class="category-title">${category.name}</div>
            <div class="menu-items-row">`;
        category.items.forEach(item => {
            html += `<div class="menu-card">
                <div class="item-name">${item.name}</div>
                <div class="item-price">$${item.price.toFixed(2)}</div>
                <button class="add-btn" onclick="addToOrder(${item.id}, '${item.name.replace(/'/g,"\\'")}', ${item.price})">+</button>
            </div>`;
        });
        html += `</div></div>`;
    });
    mainContent.innerHTML = html;
}
window.addToOrder = function(id, name, price) {
    if (!order[id]) order[id] = { id, name, price, qty: 1 };
    else order[id].qty++;
    renderOrderPanel();
};
function removeFromOrder(id) { delete order[id]; renderOrderPanel(); }
function updateOrderQty(id, delta) {
    if (!order[id]) return;
    order[id].qty += delta;
    if (order[id].qty <= 0) removeFromOrder(id);
    else renderOrderPanel();
}
function setupOrderPanelEvents() {
    orderPanel.addEventListener("click", function(e) {
        if (e.target.classList.contains("qty-btn")) {
            updateOrderQty(+e.target.dataset.id, +e.target.dataset.delta);
        }
        if (e.target.classList.contains("remove-btn")) {
            removeFromOrder(+e.target.dataset.id);
        }
        if (e.target.classList.contains("pay-btn")) {
            paymentMethod = e.target.dataset.method;
            document.querySelectorAll(".pay-btn").forEach(b => b.classList.remove("active"));
            e.target.classList.add("active");
        }
        if (e.target.id === "place-order") handlePlaceOrder();
    });
}
function renderOrderPanel() {
    let items = Object.values(order);
    let subtotal = items.reduce((s, i) => s + i.qty * i.price, 0);
    let tax = subtotal * taxRate;
    let total = subtotal + tax;
    orderPanel.innerHTML = `
        <h3>Order Summary</h3>
        <div class="order-items">
        ${items.map(item => `
            <div class="order-item-row">
                <div class="order-item-details">
                    <div class="order-item-name">${item.name}</div>
                    <div class="order-item-price">$${(item.price * item.qty).toFixed(2)}</div>
                </div>
                <div class="order-item-controls">
                    <button class="qty-btn" data-id="${item.id}" data-delta="-1">-</button>
                    <span class="order-item-qty">${item.qty}</span>
                    <button class="qty-btn" data-id="${item.id}" data-delta="1">+</button>
                    <button class="remove-btn" data-id="${item.id}">&times;</button>
                </div>
            </div>
        `).join("")}
        </div>
        <div class="order-totals">
            <div><span>Subtotal:</span> <span id="subtotal">$${subtotal.toFixed(2)}</span></div>
            <div><span>Tax (${Math.round(taxRate*100)}%):</span> <span id="tax">$${tax.toFixed(2)}</span></div>
            <div class="total-row"><span>Total:</span> <span id="total">$${total.toFixed(2)}</span></div>
        </div>
        <div class="payment-methods">
            <button class="pay-btn${paymentMethod=="Cash"?" active":""}" data-method="Cash">Cash</button>
            <button class="pay-btn${paymentMethod=="Debit Card"?" active":""}" data-method="Debit Card">Debit Card</button>
            <button class="pay-btn${paymentMethod=="E-Wallet"?" active":""}" data-method="E-Wallet">E-Wallet</button>
        </div>
        <button id="place-order" class="place-order-btn">Place Order</button>
    `;
}
function handlePlaceOrder() {
    if (!Object.keys(order).length) return alert("No items in order");
    let items = Object.values(order);
    let subtotal = items.reduce((s, i) => s + i.qty * i.price, 0);
    let tax = subtotal * taxRate;
    let total = subtotal + tax;
    fetch("api/create_order.php", {
        method: "POST",
        headers: {"Content-Type": "application/json"},
        body: JSON.stringify({
            table_id: 1, // Demo: Table 1
            user_id: 1,  // Demo: Alice
            payment_method: paymentMethod,
            total: total.toFixed(2),
            items: items.map(i => ({menu_item_id: i.id, quantity: i.qty}))
        })
    }).then(res => res.json())
    .then(data => {
        if (data.success) {
            alert("Order placed! Order ID: "+data.order_id);
            order = {};
            renderOrderPanel();
        } else {
            alert("Order failed: " + (data.error || "Unknown error"));
        }
    });
}

// ------------------- Reservation System -------------------
function loadReservationSection() {
    fetch("api/tables.php").then(r => r.json()).then(tables => {
        mainContent.innerHTML = `
            <form class="reservation-form" id="reservation-form">
                <label>Customer Name</label>
                <input type="text" name="customer_name" required>
                <label>Date & Time</label>
                <input type="datetime-local" name="datetime" required>
                <label>Table</label>
                <select name="table_id">${tables.map(t => `<option value="${t.id}">${t.name}</option>`)}</select>
                <label>Number of Guests</label>
                <input type="number" name="guests" min="1" value="1" required>
                <button type="submit">Book Reservation</button>
            </form>
            <h3>Reservations</h3>
            <div id="reservations-list"></div>
        `;
        document.getElementById("reservation-form").onsubmit = submitReservation;
        loadReservations();
    });
}
function submitReservation(e) {
    e.preventDefault();
    let form = e.target;
    let data = {
        customer_name: form.customer_name.value,
        table_id: Number(form.table_id.value),
        guests: Number(form.guests.value),
        datetime: form.datetime.value
    };
    fetch("api/reservations.php", {
        method: "POST",
        headers: {"Content-Type":"application/json"},
        body: JSON.stringify(data)
    }).then(res => res.json())
    .then(data => {
        if (data.success) {
            form.reset();
            loadReservations();
        } else alert("Reservation failed");
    });
}
function loadReservations() {
    fetch("api/reservations.php").then(r => r.json()).then(data => {
        let html = `<table class="reservations-table"><tr>
            <th>Customer</th><th>Date & Time</th><th>Table</th>
            <th>Guests</th><th>Status</th></tr>`;
        data.forEach(r => {
            html += `<tr>
                <td>${r.customer_name}</td>
                <td>${r.datetime.replace('T',' ').substring(0,16)}</td>
                <td>${r.table_name}</td>
                <td>${r.guests}</td>
                <td>${r.status}</td>
            </tr>`;
        });
        html += `</table>`;
        document.getElementById("reservations-list").innerHTML = html;
    });
}

// ------------------- Table Services -------------------
function loadTableServicesSection() {
    fetch("api/tables.php").then(r=>r.json()).then(tables => {
        let html = `<div class="tables-list">`;
        tables.forEach(t => {
            html += `<div class="table-card${t.status=="occupied"?" occupied":""}">
                <div class="table-name">${t.name}</div>
                <div class="table-status">${t.status}</div>
                <div class="table-actions">
                    ${t.active_order_id ? `<button onclick="viewOrder(${t.active_order_id})">View Order</button>
                    <button onclick="markOrderComplete(${t.active_order_id},${t.id})">Complete</button>` : ""}
                </div>
            </div>`;
        });
        html += `</div>`;
        mainContent.innerHTML = html;
    });
}
window.viewOrder = function(orderId) {
    alert("You can extend this to show order details for order #" + orderId);
};
window.markOrderComplete = function(orderId, tableId) {
    fetch("api/mark_order_complete.php", {
        method:"POST", headers:{"Content-Type":"application/json"},
        body: JSON.stringify({order_id:orderId, table_id:tableId})
    }).then(r=>r.json()).then(data=>{
        if(data.success) loadTableServicesSection();
        else alert("Failed to mark complete");
    });
}

// ------------------- Reports -------------------
function loadReportsSection() {
    mainContent.innerHTML = `
        <div class="report-block">
            <h4>Sales (Daily)</h4>
            <div id="report-sales"></div>
        </div>
        <div class="report-block">
            <h4>Top Items (Last 7 days)</h4>
            <div id="report-items"></div>
        </div>
        <div class="report-block">
            <h4>Payment Methods (Last 7 days)</h4>
            <div id="report-payments"></div>
        </div>
    `;
    fetch("api/reports.php?type=sales&range=day").then(r=>r.json()).then(showSalesReport);
    fetch("api/reports.php?type=items&range=week").then(r=>r.json()).then(showItemsReport);
    fetch("api/reports.php?type=payments&range=week").then(r=>r.json()).then(showPaymentsReport);
}
function showSalesReport(data) {
    let html = `<table class="report-table"><tr><th>Date</th><th>Total Sales</th></tr>`;
    data.forEach(r => { html += `<tr><td>${r.date}</td><td>$${parseFloat(r.total).toFixed(2)}</td></tr>`; });
    html += `</table>`;
    document.getElementById("report-sales").innerHTML = html;
}
function showItemsReport(data) {
    let html = `<table class="report-table"><tr><th>Item</th><th>Sold</th></tr>`;
    data.forEach(r => { html += `<tr><td>${r.name}</td><td>${r.count}</td></tr>`; });
    html += `</table>`;
    document.getElementById("report-items").innerHTML = html;
}
function showPaymentsReport(data) {
    let html = `<table class="report-table"><tr><th>Method</th><th>Count</th><th>Total</th></tr>`;
    data.forEach(r=>{html+=`<tr><td>${r.payment_method||"Unknown"}</td><td>${r.count}</td><td>$${parseFloat(r.total).toFixed(2)}</td></tr>`;});
    html += `</table>`;
    document.getElementById("report-payments").innerHTML = html;
}

// ------------------- Settings (Menu/Category CRUD, Tax) -------------------
function loadSettingsSection() {
    mainContent.innerHTML = `
    <div class="report-block">
        <h4>Menu Categories</h4>
        <div id="settings-categories"></div>
        <h4>Menu Items</h4>
        <div id="settings-items"></div>
        <h4>Tax Rate</h4>
        <div id="settings-tax"></div>
    </div>`;
    loadCategoriesSettings();
    loadItemsSettings();
    loadTaxSettings();
}
function loadCategoriesSettings() {
    fetch("api/menu_admin.php?type=categories").then(r=>r.json()).then(cats=>{
        let html = `<ul>`;
        cats.forEach(c=>{
            html += `<li>${c.name} 
                <button onclick="editCategory(${c.id},'${c.name.replace(/'/g,"\\'")}')">Edit</button>
                <button onclick="deleteCategory(${c.id})">Delete</button></li>`;
        });
        html += `</ul>
        <button onclick="addCategoryPrompt()">Add Category</button>`;
        document.getElementById("settings-categories").innerHTML = html;
    });
}
window.addCategoryPrompt = function() {
    let name = prompt("New category name:");
    if (name) fetch("api/menu_admin.php?type=categories", {
        method:"POST",headers:{"Content-Type":"application/json"},
        body:JSON.stringify({name})
    }).then(()=>loadCategoriesSettings());
};
window.editCategory = function(id, name) {
    let newName = prompt("Edit category name:", name);
    if (newName && newName !== name) fetch("api/menu_admin.php?type=categories", {
        method:"POST",headers:{"Content-Type":"application/json"},
        body:JSON.stringify({id, name:newName})
    }).then(()=>loadCategoriesSettings());
};
window.deleteCategory = function(id) {
    if (confirm("Delete category?")) fetch("api/menu_admin.php?type=categories", {
        method:"DELETE",body:"id="+id
    }).then(()=>loadCategoriesSettings());
};
function loadItemsSettings() {
    fetch("api/menu_admin.php?type=items").then(r=>r.json()).then(items=>{
        fetch("api/menu_admin.php?type=categories").then(r=>r.json()).then(cats=>{
            let catMap = {};
            cats.forEach(c=>catMap[c.id]=c.name);
            let html = `<table class="report-table"><tr>
                <th>Name</th><th>Category</th><th>Price</th><th>Actions</th></tr>`;
            items.forEach(i=>{
                html += `<tr>
                    <td>${i.name}</td>
                    <td>${catMap[i.category_id]}</td>
                    <td>$${parseFloat(i.price).toFixed(2)}</td>
                    <td>
                        <button onclick="editMenuItem(${i.id},'${i.name.replace(/'/g,"\\'")}',${i.category_id},${i.price})">Edit</button>
                        <button onclick="deleteMenuItem(${i.id})">Delete</button>
                    </td>
                </tr>`;
            });
            html += `</table><button onclick="addMenuItemPrompt()">Add Menu Item</button>`;
            document.getElementById("settings-items").innerHTML = html;
        });
    });
}
window.addMenuItemPrompt = function() {
    fetch("api/menu_admin.php?type=categories").then(r=>r.json()).then(cats=>{
        let name = prompt("Item name:");
        if(!name) return;
        let price = parseFloat(prompt("Price:"));
        if(isNaN(price)) return;
        let catStr = cats.map(c=>`${c.id}:${c.name}`).join(", ");
        let catid = parseInt(prompt("Category ID ("+catStr+"):",cats[0].id));
        if(!catid) return;
        fetch("api/menu_admin.php?type=items",{
            method:"POST",headers:{"Content-Type":"application/json"},
            body:JSON.stringify({name, price, category_id:catid})
        }).then(()=>loadItemsSettings());
    });
};
window.editMenuItem = function(id, name, catid, price) {
    fetch("api/menu_admin.php?type=categories").then(r=>r.json()).then(cats=>{
        let newName = prompt("Edit item name:", name);
        if(!newName) return;
        let newPrice = parseFloat(prompt("Edit price:", price));
        if(isNaN(newPrice)) return;
        let catStr = cats.map(c=>`${c.id}:${c.name}`).join(", ");
        let newCat = parseInt(prompt("Edit category ID ("+catStr+"):", catid));
        if(!newCat) return;
        fetch("api/menu_admin.php?type=items",{
            method:"POST",headers:{"Content-Type":"application/json"},
            body:JSON.stringify({id, name:newName, price:newPrice, category_id:newCat})
        }).then(()=>loadItemsSettings());
    });
};
window.deleteMenuItem = function(id) {
    if(confirm("Delete menu item?")) fetch("api/menu_admin.php?type=items",{
        method:"DELETE",body:"id="+id
    }).then(()=>loadItemsSettings());
};
function loadTaxSettings() {
    fetch("api/settings.php").then(r=>r.json()).then(settings=>{
        document.getElementById("settings-tax").innerHTML = `
            <label>Tax Rate: <input id="tax-rate-input" type="number" min="0" max="1" step="0.01" value="${settings.tax_rate}"></label>
            <button onclick="saveTaxRate()">Save</button>
        `;
        document.getElementById("tax-rate-input").addEventListener("input", e=>{
            taxRate = parseFloat(e.target.value);
        });
    });
}
window.saveTaxRate = function() {
    let val = parseFloat(document.getElementById("tax-rate-input").value);
    if(isNaN(val) || val<0 || val>1) return alert("Enter a value between 0 and 1");
    fetch("api/settings.php",{
        method:"POST",headers:{"Content-Type":"application/json"},
        body:JSON.stringify({key:"tax_rate", value:val})
    }).then(()=>{taxRate=val;alert("Saved!");});
};
function loadTaxRate() {
    fetch("api/settings.php").then(r=>r.json()).then(settings=>{
        taxRate = parseFloat(settings.tax_rate || 0.10);
    });
}

// ------------------- Extra: Mark order complete -------------------
/* Backend: mark_order_complete.php
<?php
header("Content-Type: application/json");
require_once "db.php";
$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data['order_id'], $data['table_id'])) { http_response_code(400); echo json_encode(["error"=>"Missing"]); exit; }
$conn->query("UPDATE orders SET status='completed' WHERE id=".(int)$data['order_id']);
$conn->query("UPDATE tables SET status='available' WHERE id=".(int)$data['table_id']);
echo json_encode(["success"=>true]);
*/